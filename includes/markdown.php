<?php
/**
 * Markdown to article HTML.
 *
 * The site's article CSS is written as bare-tag descendant selectors
 * (.cs-article h2, .cs-article p, .cs-article ul ...), so plain
 * markdown output already picks up the right typography once it sits
 * inside <article class="cs-article">. Only three things need help:
 *
 *   1. tables need the .cs-table-wrap / .cs-table scroll container,
 *   2. the opening paragraph needs .cs-lede to render as the intro,
 *   3. headings written as "## **Bold heading**" need the redundant
 *      bold stripped, otherwise some headings render heavier than
 *      others purely because of how the source file was authored.
 *
 * Raw HTML in the source is passed through on purpose. The only
 * person who can save a post is the logged-in admin, and it gives a
 * way to drop in a bespoke block (a stats band, say) when a page
 * needs something markdown cannot express.
 */

declare(strict_types=1);

require_once __DIR__ . '/vendor/Parsedown.php';

function brix_parsedown(): Parsedown
{
    static $parser = null;

    if ($parser === null) {
        $parser = new Parsedown();
        $parser->setBreaksEnabled(false);
        $parser->setUrlsLinked(false);
    }

    return $parser;
}

/**
 * Convert a post body to the HTML that goes inside .cs-article.
 *
 * The leading H1 is dropped: the title is already rendered as the
 * page's <h1> in the hero, and repeating it would give the article
 * two top-level headings.
 */
function render_markdown(string $markdown): string
{
    $markdown = normalise_markdown_headings($markdown);
    $markdown = strip_leading_h1($markdown);

    $html = brix_parsedown()->text($markdown);

    $html = style_tables($html);
    // Before add_lede_class(), so a post opening with a button does not
    // spend its .cs-lede on the button and leave the real first
    // paragraph unstyled.
    $html = render_body_ctas($html);
    $html = add_lede_class($html);
    $html = add_heading_anchors($html);
    $html = harden_links($html);

    return $html;
}

/**
 * Turn a [cta] shortcode in the body into the site's button.
 *
 *   [cta text="Install Brix free" url="/pricing" align="center"]
 *
 * Three attributes, in any order. `align` is optional and defaults to
 * left, which is where the body text around it sits. Any number of
 * them can appear in one post.
 *
 * UTM parameters go inside `url`, written out in full. That is why the
 * rendered link carries data-utm-lock: js/utm.js rewrites the href of
 * every App Store link on the page, and would otherwise throw away the
 * campaign the author just typed.
 *
 * Only a shortcode alone in its own paragraph is converted. One typed
 * mid-sentence is left as visible text on purpose: it shows up in the
 * draft preview as obviously wrong, which is easier to notice than a
 * button that silently failed to appear.
 *
 * Runs before harden_links(), so a CTA pointing off-site picks up
 * target="_blank" rel="noopener" like every other outbound link.
 */
function render_body_ctas(string $html): string
{
    return preg_replace_callback(
        '#<p>\s*\[cta\s+([^\]]*)\]\s*</p>#i',
        static function (array $m): string {
            $attrs = parse_shortcode_attrs($m[1]);

            $text = trim($attrs['text'] ?? '');
            $url  = cta_safe_url($attrs['url'] ?? '');

            // Nothing to click, or nowhere safe to send them: render
            // nothing at all rather than an empty or dangerous button.
            if ($text === '' || $url === null) {
                return '';
            }

            $align = strtolower(trim($attrs['align'] ?? 'left'));
            if (!in_array($align, ['left', 'center', 'right'], true)) {
                $align = 'left';
            }

            // href first: harden_links() below anchors its match on
            // "<a href=", and an off-site button should pick up
            // target="_blank" rel="noopener" from it like any other
            // outbound link in the article rather than rolling its own.
            return '<div class="cs-cta cs-cta-' . $align . '">'
                 . '<a href="' . e($url) . '" class="btn btn-primary btn-lg" data-utm-lock>'
                 . e($text)
                 . '</a></div>';
        },
        $html
    ) ?? $html;
}

/**
 * key="value" pairs out of a shortcode's attribute string.
 *
 * Parsedown has already run, so an & in a URL arrives as &amp; and the
 * quotes may have been curled by nothing at all - but the entities are
 * decoded here so the value is the URL the author actually typed.
 */
function parse_shortcode_attrs(string $raw): array
{
    preg_match_all('/([a-z_]+)\s*=\s*"([^"]*)"/i', $raw, $pairs, PREG_SET_ORDER);

    $out = [];
    foreach ($pairs as $p) {
        $out[strtolower($p[1])] = html_entity_decode($p[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    return $out;
}

/**
 * A URL a CTA is allowed to point at, or null.
 *
 * An absolute http(s) address, a root-relative path, or an anchor on
 * the same page. Everything else is refused, which is what keeps
 * javascript: and data: out of a button rendered on a public page.
 *
 * "//host" is refused too. It reads like a path but a browser treats
 * it as protocol-relative, so it would send the reader to whatever
 * host follows the slashes.
 */
function cta_safe_url(string $url): ?string
{
    $url = trim($url);

    if ($url === '') {
        return null;
    }

    $ok = preg_match('#^https?://#i', $url)
       || (str_starts_with($url, '/') && !str_starts_with($url, '//'))
       || str_starts_with($url, '#');

    return $ok ? $url : null;
}

/**
 * "## **2\. Why...**" and "## 2. Why..." should look identical once
 * rendered. Strip a bold wrapper that spans the whole heading text.
 */
function normalise_markdown_headings(string $markdown): string
{
    return preg_replace_callback(
        '/^(#{1,6})\s+(.+?)\s*$/m',
        static function (array $m): string {
            $text = trim($m[2]);

            // Only unwrap when the emphasis covers the entire heading.
            if (preg_match('/^\*\*(.+)\*\*$/s', $text, $inner)
                && strpos($inner[1], '**') === false) {
                $text = trim($inner[1]);
            }

            return $m[1] . ' ' . $text;
        },
        $markdown
    ) ?? $markdown;
}

/** Remove the first H1 so it does not duplicate the hero title. */
function strip_leading_h1(string $markdown): string
{
    return preg_replace('/\A\s*#\s+[^\n]*\R+/', '', $markdown, 1) ?? $markdown;
}

/**
 * Wrap tables so they scroll horizontally on a phone instead of
 * pushing the page wide, matching the hand-built case study tables.
 */
function style_tables(string $html): string
{
    return preg_replace(
        '/<table>(.*?)<\/table>/s',
        '<div class="cs-table-wrap"><table class="cs-table">$1</table></div>',
        $html
    ) ?? $html;
}

/** Give the opening paragraph the intro treatment. */
function add_lede_class(string $html): string
{
    $pos = strpos($html, '<p>');
    if ($pos === false) {
        return $html;
    }

    // Only if the paragraph is genuinely the first block of the
    // article; if a heading or table comes first, there is no lede.
    $before = trim(substr($html, 0, $pos));
    if ($before !== '') {
        return $html;
    }

    return substr_replace($html, '<p class="cs-lede reveal">', $pos, strlen('<p>'));
}

/**
 * Give H2s a stable id so headings can be linked to directly. Useful
 * for the "how-to" style posts where people share a section.
 */
function add_heading_anchors(string $html): string
{
    $used = [];

    return preg_replace_callback(
        '/<h([23])>(.*?)<\/h\1>/s',
        static function (array $m) use (&$used): string {
            $text = trim(strip_tags($m[2]));
            $id = slugify($text);

            if ($id === '') {
                return $m[0];
            }

            $id = truncate_words($id, 60);
            $id = rtrim($id, '.');

            if (isset($used[$id])) {
                $used[$id]++;
                $id .= '-' . $used[$id];
            } else {
                $used[$id] = 1;
            }

            return '<h' . $m[1] . ' id="' . e($id) . '">' . $m[2] . '</h' . $m[1] . '>';
        },
        $html
    ) ?? $html;
}

/**
 * Outbound links open in a new tab with rel="noopener", the same way
 * they are written by hand in the existing articles. Internal links
 * are left alone.
 */
function harden_links(string $html): string
{
    return preg_replace_callback(
        '/<a href="([^"]+)"([^>]*)>/i',
        static function (array $m): string {
            $href = $m[1];
            $rest = $m[2];

            $isExternal = preg_match('#^https?://#i', $href)
                && stripos($href, 'thebrix.io') === false;

            if (!$isExternal || stripos($rest, 'target=') !== false) {
                return $m[0];
            }

            return '<a href="' . $href . '"' . $rest . ' target="_blank" rel="noopener">';
        },
        $html
    ) ?? $html;
}
