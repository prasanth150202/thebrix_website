<?php
/**
 * Delete, restore and permanently erase posts.
 *
 * Deleting is two-stage on purpose. A normal delete only sets
 * deleted_at, so the post drops off the site and the sitemap but can
 * be brought back. Erasing for good is a separate action from the
 * "recently deleted" list.
 *
 * The confirmation asks for the post title to be typed out. A live
 * article represents real work and real search traffic, so a stray
 * click on a button should not be enough to remove it.
 */

declare(strict_types=1);

require_once __DIR__ . '/_boot.php';

$user = require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        flash('Your session expired. Nothing was deleted.', 'err');
        redirect('index.php');
    }

    $id     = (int) ($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? 'delete';
    $post   = get_post($id);

    if ($post === null) {
        flash('That post no longer exists.', 'err');
        redirect('index.php');
    }

    if ($action === 'restore') {
        // Comes back as a draft rather than silently reappearing on the
        // live site the moment it is restored.
        $stmt = db()->prepare(
            'UPDATE posts SET deleted_at = NULL, status = "draft" WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);

        flash('Restored "' . $post['title'] . '" as a draft.');
        redirect('index.php');
    }

    if ($action === 'purge') {
        db()->prepare('DELETE FROM posts WHERE id = :id AND deleted_at IS NOT NULL')
            ->execute([':id' => $id]);
        // Nothing left to redirect to, and holding the rows would keep
        // those addresses reserved against every future post.
        forget_slug_redirects($id);
        flash('Erased "' . $post['title'] . '" for good.');
        redirect('index.php');
    }

    // Standard delete: require the typed title to match.
    $typed = trim((string) ($_POST['confirm_title'] ?? ''));
    if (mb_strtolower($typed) !== mb_strtolower(trim($post['title']))) {
        flash('The title you typed did not match, so nothing was deleted.', 'err');
        redirect('post-delete.php?id=' . $id);
    }

    $stmt = db()->prepare('UPDATE posts SET deleted_at = NOW(), status = "draft" WHERE id = :id');
    $stmt->execute([':id' => $id]);

    flash('Deleted "' . $post['title'] . '". You can restore it from the list.');
    redirect('index.php');
}

$id   = (int) ($_GET['id'] ?? 0);
$post = get_post($id);

if ($post === null) {
    flash('That post no longer exists.', 'err');
    redirect('index.php');
}

admin_head('Delete post', $user, 'posts');
?>
<div class="ad-wrap ad-narrow">
  <?php render_flash(); ?>

  <div class="ad-card ad-card-danger">
    <h1>Delete this post?</h1>

    <div class="ad-delsummary">
      <strong><?= e($post['title']) ?></strong>
      <span class="ad-slug"><?= e($post['slug']) ?></span>
      <span class="ad-sub">
        <?= $post['type'] === 'blog' ? 'Blog post' : 'Case study' ?>
        &middot; <?= e($post['author']) ?>
        &middot; <?= e(format_post_date($post['date_published'])) ?>
        &middot; <?= $post['status'] === 'published' ? 'currently live' : 'draft' ?>
      </span>
    </div>

    <?php if ($post['status'] === 'published'): ?>
      <div class="ad-flash ad-flash-warn">
        <strong>This post is live.</strong> Deleting it means
        <code>thebrix.io/<?= e($post['slug']) ?></code> starts returning
        &ldquo;not found&rdquo;. Any inbound link or search result pointing at it will break.
      </div>
    <?php endif; ?>

    <p>It moves to <em>Recently deleted</em>, where you can restore it. Nothing is erased
       until you erase it from there.</p>

    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
      <input type="hidden" name="action" value="delete">

      <label class="ad-field">
        <span class="ad-label">Type the post title to confirm</span>
        <input type="text" name="confirm_title" required autocomplete="off"
               placeholder="<?= e($post['title']) ?>">
      </label>

      <div class="ad-head-actions">
        <a class="ad-btn" href="index.php">Keep it</a>
        <button class="ad-btn ad-btn-danger" type="submit">Delete this post</button>
      </div>
    </form>
  </div>
</div>
<?php admin_foot(); ?>
