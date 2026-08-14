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
    $html = add_lede_class($html);
    $html = add_heading_anchors($html);
    $html = harden_links($html);

    return $html;
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
