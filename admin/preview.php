<?php
/**
 * Draft preview.
 *
 * Renders the real article template rather than an approximation, so
 * what you sign off on is exactly what gets published. Only reachable
 * while signed in, and marked noindex in case a link ever leaks.
 */

declare(strict_types=1);

require_once __DIR__ . '/_boot.php';

require_admin();

$post = get_post((int) ($_GET['id'] ?? 0));

if ($post === null) {
    flash('That post no longer exists.', 'err');
    redirect('index.php');
}

// Show the staged version. For a live post that is the pending edit,
// not what the public currently sees, which is the whole point of
// previewing before publishing.
$preview_pending = post_has_pending_changes($post);
$post = post_with_draft($post);

$preview_banner = true;

// The article template and everything it pulls in (nav, footer, card
// links) build URLs relative to the site root, but this script is
// served from /admin/. A <base> in the head fixes them all at once.
$page_base = '/';

require BRIX_INCLUDES . '/article-view.php';
