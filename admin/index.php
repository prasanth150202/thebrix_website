<?php
declare(strict_types=1);

require_once __DIR__ . '/_boot.php';

if (!admin_account_exists()) {
    redirect('setup.php');
}

$user = require_admin();

$filter = $_GET['type'] ?? 'all';
$posts  = get_all_posts(isset(POST_TYPES[$filter]) ? $filter : null);
$binned = get_deleted_posts();

$counts = ['all' => 0, 'blog' => 0, 'case_study' => 0, 'draft' => 0];
foreach (get_all_posts() as $p) {
    $counts['all']++;
    $counts[$p['type']]++;
    if ($p['status'] === 'draft') {
        $counts['draft']++;
    }
}

admin_head('Posts', $user, 'posts');
?>
<div class="ad-wrap">
  <?php render_flash(); ?>

  <div class="ad-head">
    <div>
      <h1>Posts</h1>
      <p class="ad-sub"><?= $counts['all'] ?> live and draft
        &middot; <?= $counts['blog'] ?> blog
        &middot; <?= $counts['case_study'] ?> case
        <?= $counts['draft'] > 0 ? '&middot; <strong>' . $counts['draft'] . ' draft</strong>' : '' ?>
      </p>
    </div>
    <a class="ad-btn ad-btn-primary" href="post-edit.php">New post</a>
  </div>

  <div class="ad-filters">
    <a href="?type=all"        class="<?= $filter === 'all' ? 'on' : '' ?>">All</a>
    <a href="?type=blog"       class="<?= $filter === 'blog' ? 'on' : '' ?>">Blog</a>
    <a href="?type=case_study" class="<?= $filter === 'case_study' ? 'on' : '' ?>">Case studies</a>
  </div>

  <?php if (!$posts): ?>
    <div class="ad-empty">
      <p>No posts yet.</p>
      <p class="ad-sub">If this is a fresh install, <a href="migrate.php">import the eight
         existing articles</a> first.</p>
    </div>
  <?php else: ?>
    <table class="ad-table">
      <thead>
        <tr>
          <th>Title</th>
          <th>Type</th>
          <th>Author</th>
          <th>Date</th>
          <th>Status</th>
          <th class="ad-r">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($posts as $p): ?>
        <tr>
          <td>
            <a class="ad-title" href="post-edit.php?id=<?= (int) $p['id'] ?>"><?= e($p['title']) ?></a>
            <span class="ad-slug"><?= e($p['slug']) ?></span>
          </td>
          <td><span class="ad-pill ad-pill-<?= $p['type'] === 'blog' ? 'blog' : 'case' ?>">
              <?= $p['type'] === 'blog' ? 'Blog' : 'Case study' ?></span></td>
          <td><?= e($p['author']) ?></td>
          <td class="ad-nowrap"><?= e(format_post_date($p['date_published'])) ?></td>
          <td>
            <?php if ($p['status'] === 'published'): ?>
              <span class="ad-dot ad-dot-live"></span> Live
              <?php if (post_has_pending_changes($p)): ?>
                <span class="ad-pending" title="Edited since it was published. The live page still shows the published version.">edits pending</span>
              <?php endif; ?>
            <?php else: ?>
              <span class="ad-dot ad-dot-draft"></span> Draft
            <?php endif; ?>
          </td>
          <td class="ad-r ad-actions">
            <?php if ($p['status'] === 'published'): ?>
              <a href="../<?= e($p['slug']) ?>" target="_blank" rel="noopener">View</a>
            <?php endif; ?>
            <a href="preview.php?id=<?= (int) $p['id'] ?>" target="_blank" rel="noopener">Preview</a>
            <a href="post-edit.php?id=<?= (int) $p['id'] ?>">Edit</a>
            <a href="post-download.php?id=<?= (int) $p['id'] ?>">.md</a>
            <a class="ad-danger" href="post-delete.php?id=<?= (int) $p['id'] ?>">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <?php if ($binned): ?>
    <details class="ad-bin">
      <summary>Recently deleted (<?= count($binned) ?>)</summary>
      <p class="ad-sub">Deleted posts are kept so a mistake can be undone. They are not
         published and not in the sitemap.</p>
      <table class="ad-table">
        <tbody>
        <?php foreach ($binned as $p): ?>
          <tr>
            <td><?= e($p['title']) ?><span class="ad-slug"><?= e($p['slug']) ?></span></td>
            <td class="ad-nowrap"><?= e(format_post_date($p['deleted_at'])) ?></td>
            <td class="ad-r ad-actions">
              <form method="post" action="post-delete.php" class="ad-inline">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                <input type="hidden" name="action" value="restore">
                <button class="ad-linkbtn" type="submit">Restore</button>
              </form>
              <form method="post" action="post-delete.php" class="ad-inline"
                    onsubmit="return confirm('Permanently erase this post? This cannot be undone.')">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                <input type="hidden" name="action" value="purge">
                <button class="ad-linkbtn ad-danger" type="submit">Erase</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </details>
  <?php endif; ?>
</div>
<?php admin_foot(); ?>
