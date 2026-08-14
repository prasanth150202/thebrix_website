<?php
/**
 * Public article page.
 *
 * Reached through .htaccess, which maps the original static URLs
 * (blog-*.html and case-study-*.html) onto this file. The slug in the
 * database is the full filename stem, so every URL that was live
 * before the migration resolves here unchanged.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once BRIX_INCLUDES . '/posts.php';
require_once BRIX_INCLUDES . '/markdown.php';

$slug = (string) ($_GET['slug'] ?? '');

// Belt and braces: the rewrite already constrains the shape, but this
// file is also reachable directly.
if (!preg_match('/^(blog|case-study)-[a-z0-9-]+$/', $slug)) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

try {
    $post = get_published_post_by_slug($slug);
} catch (Throwable $ex) {
    http_response_code(503);
    exit('The site is temporarily unavailable.');
}

if ($post === null) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

require BRIX_INCLUDES . '/article-view.php';
