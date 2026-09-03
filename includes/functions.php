<?php
/**
 * Small helpers shared by the public site and the admin panel.
 */

declare(strict_types=1);

/** Escape for HTML text and attribute contexts. */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Turn a title into a URL-safe slug body.
 *
 * The type prefix (blog- / case-study-) is added separately, because
 * the existing live URLs already carry it and the slug column stores
 * the complete filename stem.
 */
function slugify(string $text): string
{
    $text = trim($text);

    // Transliterate accented characters where the extension is available.
    if (function_exists('iconv')) {
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($converted !== false) {
            $text = $converted;
        }
    }

    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';

    return trim($text, '-');
}

/**
 * The stored-slug prefix and the public path segment of each type.
 *
 * Declared once because three copies of this mapping is precisely how
 * an address and the slug behind it drift apart. The editor, the
 * router and the sitemap all reach it through the helpers below.
 */
function post_sections(): array
{
    return [
        'case_study' => ['prefix' => 'case-study-', 'path' => '/case-study/'],
        'blog'       => ['prefix' => 'blog-',       'path' => '/blog/'],
    ];
}

/**
 * Build the public filename stem for a post, preserving the naming
 * convention the site already uses.
 */
function post_slug_prefix(string $type): string
{
    $sections = post_sections();

    return ($sections[$type] ?? $sections['blog'])['prefix'];
}

/** The path segment a type's articles are published under. */
function section_path(string $type): string
{
    $sections = post_sections();

    return ($sections[$type] ?? $sections['blog'])['path'];
}

/**
 * Public URL for a post, e.g. blog/cart-upsell-examples
 *
 * The slug column still stores the original filename stem
 * (blog-cart-upsell-examples), because that stem is the post's identity
 * everywhere else: it is what migrate.php matches on, what the admin
 * shows, and what .htaccess hands back to article.php. Only the public
 * address is nested, and only here, so nothing else has to know that the
 * address and the slug have parted ways.
 *
 * Both older forms of the address, the flat one and the .html one it had
 * as a static page, are permanently redirected to this by .htaccess.
 */
function post_url(array $post): string
{
    return slug_path((string) $post['slug']);
}

/**
 * The public address a stored slug resolves to.
 *
 * Separate from post_url() because the admin needs this before there is
 * a post to pass: while a new one is being written, the slug exists only
 * as a candidate string. Wherever the panel shows someone an address, it
 * has to be the address they will actually get.
 */
function slug_path(string $slug): string
{
    // Root-relative, because an article now sits a level down: a link
    // written relative to /blog/cart-upsell-examples would resolve
    // against /blog/ and point at a page that does not exist.
    foreach (post_sections() as $section) {
        if (str_starts_with($slug, $section['prefix'])) {
            return $section['path'] . substr($slug, strlen($section['prefix']));
        }
    }

    // A slug that carries neither prefix should not exist, but if one is
    // ever hand-written it is better linked flat than linked wrongly.
    return '/' . $slug;
}

/**
 * The half of a slug an author actually types.
 *
 * The stored slug carries the section as a prefix, but the address
 * shows the section as a path segment. The editor therefore offers only
 * what comes after it, which is what stops a hand-written address from
 * putting a post in a section its type does not match.
 */
function slug_tail(string $slug): string
{
    foreach (post_sections() as $section) {
        if (str_starts_with($slug, $section['prefix'])) {
            return substr($slug, strlen($section['prefix']));
        }
    }

    return $slug;
}

/**
 * Turn what someone typed into the Web address box into a stored slug.
 *
 * A bare segment, a path and a whole pasted URL are all things an
 * author reasonably types when they mean "put it here", so all three
 * are accepted. Only the last segment survives, and the prefix for the
 * post's current type is put back on: an address can never contradict
 * the list the post appears on, and changing a blog entry into a case
 * study moves its address with it.
 *
 * Returns '' when nothing usable is left, which the caller reports
 * rather than saving.
 */
function normalise_slug_input(string $raw, string $type): string
{
    $raw = trim($raw);

    // A pasted address: drop the scheme and host, then any extension
    // left over from the static-page era.
    $raw = preg_replace('#^[a-z][a-z0-9+.-]*://[^/]+#i', '', $raw) ?? $raw;
    $raw = preg_replace('#\.(php|html?)$#i', '', $raw) ?? $raw;
    $raw = trim($raw, '/');

    // /blog/cart-tips and cart-tips mean the same thing here: the
    // section is decided by the post's type, never by what was typed.
    if (($cut = strrpos($raw, '/')) !== false) {
        $raw = substr($raw, $cut + 1);
    }

    $body = slugify($raw);

    // Typing the prefix as well is not a mistake worth refusing, and it
    // is what a pasted stem looks like. Take it off rather than
    // doubling it, unless it is the whole of what was typed.
    foreach (post_sections() as $section) {
        if (str_starts_with($body, $section['prefix'])
            && strlen($body) > strlen($section['prefix'])) {
            $body = substr($body, strlen($section['prefix']));
            break;
        }
    }

    return $body === '' ? '' : post_slug_prefix($type) . $body;
}

/** Listing page a post belongs to. */
function post_index_url(string $type): string
{
    // Root-relative for the same reason post_url() is: the back link is
    // rendered on an article, which sits a level down.
    return $type === 'case_study' ? '/case-studies' : '/blog';
}

/**
 * Estimate reading time the way the existing articles present it:
 * whole minutes, never zero.
 */
function estimate_read_minutes(string $markdown): int
{
    $words = str_word_count(strip_tags($markdown));

    return max(1, (int) round($words / 220));
}

/**
 * First real paragraph of a markdown document, flattened to plain
 * text. Used to pre-fill the excerpt field so the editor is not
 * starting from a blank box.
 */
function markdown_first_paragraph(string $markdown): string
{
    $lines = preg_split('/\R/', $markdown) ?: [];
    $buffer = [];

    foreach ($lines as $line) {
        $trimmed = trim($line);

        if ($trimmed === '') {
            if ($buffer !== []) {
                break;
            }
            continue;
        }

        // Skip headings, quotes, lists, images and fences while hunting
        // for the first body paragraph.
        if (preg_match('/^(#{1,6}\s|>|[-*+]\s|\d+\.\s|!\[|```|\||---)/', $trimmed)) {
            if ($buffer !== []) {
                break;
            }
            continue;
        }

        $buffer[] = $trimmed;
    }

    $text = implode(' ', $buffer);
    $text = preg_replace('/\*\*(.+?)\*\*/s', '$1', $text) ?? $text;
    $text = preg_replace('/\*(.+?)\*/s', '$1', $text) ?? $text;
    $text = preg_replace('/\[(.+?)\]\([^)]*\)/s', '$1', $text) ?? $text;
    $text = preg_replace('/`(.+?)`/s', '$1', $text) ?? $text;
    $text = str_replace('\\', '', $text);

    return trim($text);
}

/** First H1 in a markdown document, if there is one. */
function markdown_first_heading(string $markdown): ?string
{
    if (preg_match('/^\s*#\s+(.+)$/m', $markdown, $m)) {
        $title = trim($m[1]);
        $title = preg_replace('/\*\*(.+?)\*\*/', '$1', $title) ?? $title;
        $title = str_replace('\\', '', $title);

        return trim($title);
    }

    return null;
}

/**
 * Shorten to a whole word without cutting mid-word.
 */
function truncate_words(string $text, int $maxChars): string
{
    if (mb_strlen($text) <= $maxChars) {
        return $text;
    }

    $cut = mb_substr($text, 0, $maxChars);
    $lastSpace = mb_strrpos($cut, ' ');
    if ($lastSpace !== false) {
        $cut = mb_substr($cut, 0, $lastSpace);
    }

    return rtrim($cut, " \t\n\r\0\x0B.,;:") . '...';
}

/**
 * Normalise a pasted or uploaded markdown file.
 *
 * Files written on Windows arrive with CRLF and files exported from
 * some editors carry a BOM; both leak into the rendered output as
 * stray characters if they are not stripped here.
 */
function normalise_newlines(string $text): string
{
    $text = str_replace("\xEF\xBB\xBF", '', $text);
    $text = str_replace(["\r\n", "\r"], "\n", $text);

    return $text;
}

/**
 * Split a leading YAML front matter block off a markdown document.
 *
 * Returns [metadata, body]. Only the flat "key: value" subset is
 * supported, which is all post-download.php ever writes, so a post
 * can be downloaded, edited offline and uploaded again without the
 * metadata block ending up in the article text.
 *
 * A file with no front matter comes back unchanged with an empty
 * metadata array.
 */
function parse_front_matter(string $text): array
{
    $text = normalise_newlines($text);

    if (!preg_match('/\A---\n(.*?)\n---\n?/s', $text, $m)) {
        return [[], ltrim($text, "\n")];
    }

    $meta = [];
    foreach (explode("\n", $m[1]) as $line) {
        if (trim($line) === '' || str_starts_with(ltrim($line), '#')) {
            continue;
        }
        if (!preg_match('/^([A-Za-z0-9_-]+)\s*:\s*(.*)$/', $line, $kv)) {
            continue;
        }

        $value = trim($kv[2]);

        if (strlen($value) >= 2
            && ($value[0] === '"' || $value[0] === "'")
            && $value[strlen($value) - 1] === $value[0]) {
            $quote = $value[0];
            $value = substr($value, 1, -1);
            if ($quote === '"') {
                $value = str_replace(['\\"', '\\\\'], ['"', '\\'], $value);
            }
        }

        $meta[$kv[1]] = $value;
    }

    return [$meta, ltrim(substr($text, strlen($m[0])), "\n")];
}

/** Format a Y-m-d date the way the cards and post meta already do. */
function format_post_date(?string $date): string
{
    if (!$date) {
        return '';
    }

    $ts = strtotime($date);

    return $ts === false ? '' : date('M j, Y', $ts);
}

/**
 * CSRF token for the current session.
 *
 * A session cookie can only be sent before output begins. Since this
 * is normally called from inside a form, deep in the page body, the
 * page must have started the session already. If it has not, the
 * cookie is lost, the token is written to a session the browser never
 * receives, and every submission of that form fails validation.
 *
 * That is a silent, total breakage of a form, so complain loudly in
 * the error log rather than let it pass unnoticed.
 */
function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE && headers_sent($file, $line)) {
        error_log(sprintf(
            'Brix: csrf_token() reached after output started at %s:%d. '
            . 'Call brix_session_start() at the top of this page, before any HTML.',
            $file,
            $line
        ));
    }

    brix_session_start();

    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(?string $token): bool
{
    brix_session_start();

    return !empty($_SESSION['csrf'])
        && is_string($token)
        && hash_equals($_SESSION['csrf'], $token);
}

/** Client IP, taking one level of proxy into account. */
function client_ip(): string
{
    $forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
    if ($forwarded !== '') {
        $first = trim(explode(',', $forwarded)[0]);
        if (filter_var($first, FILTER_VALIDATE_IP)) {
            return $first;
        }
    }

    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function redirect(string $to): never
{
    header('Location: ' . $to);
    exit;
}

function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
}

/**
 * Reduce a stored image path to one this site is willing to serve.
 *
 * The only thing that ever legitimately lands in a field like
 * hero_image is a path returned by admin/upload-image.php, so the rule
 * is simply: inside assets/, ends in an image extension, no traversal.
 * An absolute URL is refused as well, because a hero that depends on a
 * host we do not control is a broken hero waiting to happen.
 *
 * Returns '' for anything that does not qualify, which the templates
 * already treat as "no image".
 */
function safe_asset_path(?string $path): string
{
    $path = ltrim(trim((string) $path), '/');

    if ($path === '' || str_contains($path, '..')) {
        return '';
    }

    return preg_match('#^assets/[A-Za-z0-9._/-]+\.(jpe?g|png|gif|webp|svg)$#i', $path) === 1
        ? $path
        : '';
}

/** Blur is stored in pixels. Anything outside the slider's range is clamped. */
function clamp_hero_blur(mixed $value): int
{
    return max(0, min(24, (int) $value));
}

/**
 * The App Store link for the CTA in an article's sidebar, tagged with
 * the kind of page the reader clicked from.
 *
 * Everywhere else on the site sends Brix-Website / Website_Tracking,
 * which lumps a reader who came through a blog post in with every
 * other visitor. Reading is a slower route to an install than a
 * landing page is, and it is worth being able to see that separately
 * in Shopify.
 *
 * Only the source and the campaign change. utm_medium stays Organic,
 * and utm_id stays Website because that is the parameter Shopify
 * filters website installs on: vary it and these installs drop out of
 * that view entirely.
 *
 * The address itself is taken from SHOPIFY_APP_URL rather than written
 * out again, so there is still one place the store URL is defined.
 */
function article_install_url(string $type): string
{
    [$source, $campaign] = $type === 'case_study'
        ? ['Brix-Case-Studies', 'Case_Studies_Tracking']
        : ['Brix-Blogs', 'Blogs_Tracking'];

    return strtok(SHOPIFY_APP_URL, '?') . '?' . http_build_query([
        'utm_source'   => $source,
        'utm_medium'   => 'Organic',
        'utm_campaign' => $campaign,
        'utm_id'       => 'Website',
    ]);
}

/**
 * The card gradients the site already ships. Keyed so the editor can
 * offer them as a choice rather than asking for a raw class name.
 */
function card_gradients(): array
{
    return [
        'bshot-1' => 'Violet',
        'bshot-2' => 'Dark to violet',
        'bshot-3' => 'Violet to AI blue',
        'bshot-4' => 'Growth green',
    ];
}

/**
 * Curated icon set for cards.
 *
 * Deliberately a fixed library rather than a free-text SVG field:
 * arbitrary markup on a card would be an easy way to inject script
 * into every listing page. These are the glyphs already in use across
 * the blog and case study grids, plus a few close cousins.
 */
function card_icons(): array
{
    return [
        'cart' => [
            'label' => 'Shopping cart',
            'svg'   => '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>',
        ],
        'grid' => [
            'label' => 'App grid',
            'svg'   => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>',
        ],
        'trend' => [
            'label' => 'Trend line',
            'svg'   => '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>',
        ],
        'search' => [
            'label' => 'Diagnostics',
            'svg'   => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-4.35-4.35"/><path d="M11 8v3"/><path d="M11 14h.01"/>',
        ],
        'box' => [
            'label' => 'Bundle box',
            'svg'   => '<path d="M12 2.5 3.5 7v10L12 21.5 20.5 17V7z"/><path d="M3.5 7 12 11.6 20.5 7"/><path d="M12 11.6v9.9"/>',
        ],
        'gift' => [
            'label' => 'Gift / reward',
            'svg'   => '<rect x="3" y="8" width="18" height="4.5" rx="1"/><path d="M5 12.5V20a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-7.5"/><path d="M12 8v13"/><path d="M12 8H7.8a2.4 2.4 0 1 1 0-4.8C11 3.2 12 8 12 8z"/><path d="M12 8h4.2a2.4 2.4 0 1 0 0-4.8C13 3.2 12 8 12 8z"/>',
        ],
        'shirt' => [
            'label' => 'Apparel',
            'svg'   => '<path d="M15.5 3 20 5.4v4.4h-2.9V21H6.9V9.8H4V5.4L8.5 3"/><path d="M8.5 3a3.5 3.5 0 0 0 7 0"/>',
        ],
        'tag' => [
            'label' => 'Price tag',
            'svg'   => '<path d="M20.6 13.4 12 22l-9-9V3h10l7.6 7.6a2 2 0 0 1 0 2.8z"/><circle cx="7.5" cy="7.5" r="1.5"/>',
        ],
        'spark' => [
            'label' => 'AI sparkle',
            'svg'   => '<path d="M12 2.6 13.9 9 20.4 11l-6.5 2L12 19.4 10.1 13 3.6 11 10.1 9z"/><path d="M18.5 3.2 19.2 5.4 21.4 6l-2.2.7-.7 2.2-.7-2.2L15.6 6l2.2-.6z"/>',
        ],
    ];
}

/** Render one card glyph by key, falling back to the cart icon. */
function card_icon_svg(?string $key): string
{
    $icons = card_icons();
    $icon = $icons[$key] ?? $icons['cart'];

    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" '
        . 'stroke-linecap="round" stroke-linejoin="round">' . $icon['svg'] . '</svg>';
}
