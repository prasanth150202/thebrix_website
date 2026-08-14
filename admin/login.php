<?php
declare(strict_types=1);

require_once __DIR__ . '/_boot.php';

// Nothing to log into yet.
if (!admin_account_exists()) {
    redirect('setup.php');
}

if (admin_user() !== null) {
    redirect('index.php');
}

$error = null;

/**
 * Only ever redirect to a page inside the admin folder. Taking the
 * bare filename off the query string stops the login form being used
 * as an open redirect to somewhere else entirely.
 */
$next = basename((string) ($_GET['next'] ?? $_POST['next'] ?? 'index.php'));
if (!preg_match('/^[a-z0-9_-]+\.php(\?.*)?$/i', $next)) {
    $next = 'index.php';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $error = 'Your session expired. Please try again.';
    } else {
        $error = admin_login(
            trim($_POST['username'] ?? ''),
            (string) ($_POST['password'] ?? '')
        );

        if ($error === null) {
            redirect($next);
        }
    }
}

admin_head('Sign in');
?>
<div class="ad-login">
  <div class="ad-login-card">
    <span class="ad-brand ad-brand-lg">
      <img src="assets/brix-admin-logo.png" alt="Brix">
      <span>Admin</span>
    </span>
    <h1>Sign in</h1>
    <p class="ad-login-sub">Manage articles, case studies and enquiries.</p>

    <?php if ($error !== null): ?>
      <div class="ad-flash ad-flash-err"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
      <?= csrf_field() ?>
      <input type="hidden" name="next" value="<?= e($next) ?>">
      <label>Username
        <input type="text" name="username" required autofocus
               autocomplete="username" value="<?= e($_POST['username'] ?? '') ?>">
      </label>
      <label>Password
        <input type="password" name="password" required autocomplete="current-password">
      </label>
      <button class="ad-btn ad-btn-primary ad-btn-block" type="submit">Sign in</button>
    </form>
  </div>
</div>
<?php admin_foot(); ?>
