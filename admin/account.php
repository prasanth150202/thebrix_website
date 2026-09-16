<?php
/**
 * The signed-in admin's own name and bio.
 *
 * admin_users.display_name defaults to 'Admin' and, until now, nothing
 * in the panel ever offered a way to change it - so every post's
 * author field, which post-edit.php seeds from this value, has been
 * saving as the literal string "Admin". This page is the fix: set it
 * once here and every post created afterwards picks it up on its own.
 *
 * It does not touch posts already published with "Admin" as their
 * author - see the backfill section below for those.
 */

declare(strict_types=1);

require_once __DIR__ . '/_boot.php';

$user = require_admin();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_check($_POST['csrf'] ?? null)) {
    $errors[] = 'Your session expired. Please try again.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_profile') {
    $displayName = trim((string) ($_POST['display_name'] ?? ''));
    $bio         = trim((string) ($_POST['bio'] ?? ''));

    if ($displayName === '') {
        $errors[] = 'Name cannot be empty.';
    } elseif (mb_strlen($displayName) > 120) {
        $errors[] = 'Name is too long (120 characters max).';
    } elseif (mb_strlen($bio) > 255) {
        $errors[] = 'Bio is too long (255 characters max).';
    } else {
        $stmt = db()->prepare('UPDATE admin_users SET display_name = :n, bio = :b WHERE id = :id');
        $stmt->execute([':n' => $displayName, ':b' => $bio, ':id' => $user['id']]);

        flash('Saved. New posts will credit "' . $displayName . '" from now on.');
        redirect('account.php');
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'backfill_authors') {
    // Only ever moves rows away from the literal 'Admin' default, and
    // only to whatever this account's display_name currently is - so
    // running it twice, or after later renaming yourself again, cannot
    // overwrite a byline someone has since set by hand on a specific
    // post via post-edit.php.
    if (($user['display_name'] ?: 'Admin') === 'Admin') {
        $errors[] = 'Set your name above first, then apply it to existing posts.';
    } else {
        $stmt = db()->prepare(
            "UPDATE posts SET author = :n WHERE author = 'Admin' AND deleted_at IS NULL"
        );
        $stmt->execute([':n' => $user['display_name']]);

        flash($stmt->rowCount() . ' post(s) updated to "' . $user['display_name'] . '".');
        redirect('account.php');
    }
}

// Re-fetch rather than trust $_POST on an error, so the count below and
// the name shown in the form always reflect what is actually saved.
$stmt = db()->prepare('SELECT display_name, bio FROM admin_users WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $user['id']]);
$current = $stmt->fetch() ?: ['display_name' => 'Admin', 'bio' => ''];

$adminAuthoredCount = (int) db()->query(
    "SELECT COUNT(*) FROM posts WHERE author = 'Admin' AND deleted_at IS NULL"
)->fetchColumn();

admin_head('Account', $user, 'account');
?>
<div class="ad-wrap">
  <?php render_flash(); ?>

  <div class="ad-head">
    <div>
      <h1>Account</h1>
      <p class="ad-sub">Your name and bio, used as the byline on every post.</p>
    </div>
  </div>

  <?php foreach ($errors as $error): ?>
    <div class="ad-flash ad-flash-err"><?= e($error) ?></div>
  <?php endforeach; ?>

  <div class="ad-card">
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save_profile">

      <label class="ad-field">
        <span class="ad-label">Username</span>
        <input type="text" value="<?= e($user['username']) ?>" disabled>
      </label>

      <label class="ad-field">
        <span class="ad-label">Name</span>
        <input type="text" name="display_name" maxlength="120" required
               value="<?= e($_POST['display_name'] ?? $current['display_name']) ?>"
               placeholder="e.g. Ayman Thahir">
        <span class="ad-hint">Shown as "Written by" on every post and case study.</span>
      </label>

      <label class="ad-field">
        <span class="ad-label">Bio</span>
        <input type="text" name="bio" maxlength="255"
               value="<?= e($_POST['bio'] ?? $current['bio']) ?>"
               placeholder="e.g. Runs cart CRO for Shopify stores">
        <span class="ad-hint">One line, shown under your name. Optional, but a real credential does more for a reader's trust than a name alone.</span>
      </label>

      <button class="ad-btn ad-btn-primary" type="submit">Save</button>
    </form>
  </div>

  <div class="ad-card">
    <h2 style="margin-top:0">Existing posts still crediting "Admin"</h2>
    <?php if ($adminAuthoredCount === 0): ?>
      <p class="ad-sub">None &mdash; every post already has a real author.</p>
    <?php else: ?>
      <p class="ad-sub">
        <?= $adminAuthoredCount ?> post(s) were published before a real name was set here
        and still show "Admin" as the author.
      </p>
      <form method="post" id="fBackfill"
            data-confirm="Set the author to &#8220;<?= e($current['display_name']) ?>&#8221; on <?= $adminAuthoredCount ?> post(s)? This only changes posts still literally authored Admin — anything already hand-edited is left alone.">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="backfill_authors">
        <?php if (($current['display_name'] ?: 'Admin') === 'Admin'): ?>
          <p class="ad-hint">Save a real name above first.</p>
        <?php else: ?>
          <button class="ad-btn" type="submit">
            Apply "<?= e($current['display_name']) ?>" to all <?= $adminAuthoredCount ?> of them
          </button>
        <?php endif; ?>
      </form>
      <script>
        // Read via .dataset rather than inlining the name into an
        // onsubmit string, so a name containing a quote or apostrophe
        // (O'Brien, "Ace" Patel) can never break out of it.
        document.getElementById('fBackfill')?.addEventListener('submit', function (e) {
          if (!confirm(this.dataset.confirm)) {
            e.preventDefault();
          }
        });
      </script>
    <?php endif; ?>
  </div>
</div>
<?php admin_foot(); ?>
