<?php
/**
 * Admin chrome. Include admin_head() at the top of a page and
 * admin_foot() at the bottom.
 *
 * Kept separate from the public site's header/footer on purpose: the
 * panel should not load GTM, Clarity, GSAP or the chat widget.
 */

declare(strict_types=1);

function admin_head(string $title, ?array $user = null, string $active = ''): void
{
    // "New post" is deliberately not a tab: the Posts page already has
    // the button, and two routes to the same screen only adds noise.
    $tabs = [
        'posts'       => ['href' => 'index.php',       'label' => 'Posts'],
        'submissions' => ['href' => 'submissions.php', 'label' => 'Submissions'],
    ];
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> &middot; Brix admin</title>
<link rel="icon" type="image/png" href="../assets/favicon.png?v=2">
<link rel="stylesheet" href="assets/admin.css?v=5">
</head>
<body>
<?php if ($user !== null): ?>
<header class="ad-top">
  <div class="ad-top-in">
    <!-- Lockup: the wordmark plus ADMIN, sized and baselined off the
         "x" of Brix so the two read as one logo. -->
    <a class="ad-brand" href="index.php">
      <img src="assets/brix-admin-logo.png" alt="Brix">
      <span>Admin</span>
    </a>
    <nav class="ad-tabs">
      <?php foreach ($tabs as $key => $tab): ?>
        <a href="<?= $tab['href'] ?>"<?= $active === $key ? ' class="on"' : '' ?>><?= $tab['label'] ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="ad-who">
      <a href="/" target="_blank" rel="noopener">View site &#8599;</a>
      <span class="ad-sep"></span>
      <span><?= e($user['display_name'] ?: $user['username']) ?></span>
      <a class="ad-out" href="logout.php">Sign out</a>
    </div>
  </div>
</header>
<?php endif; ?>
<main class="ad-main">
<?php
}

function admin_foot(string $scripts = ''): void
{
    ?>
</main>
<?= $scripts ?>
</body>
</html>
<?php
}

/** One-shot status message carried across a redirect. */
function flash(?string $message = null, string $kind = 'ok'): ?array
{
    brix_session_start();

    if ($message !== null) {
        $_SESSION['flash'] = ['msg' => $message, 'kind' => $kind];
        return null;
    }

    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return $flash;
}

function render_flash(): void
{
    $f = flash();
    if ($f === null) {
        return;
    }

    printf(
        '<div class="ad-flash ad-flash-%s">%s</div>',
        e($f['kind']),
        e($f['msg'])
    );
}
