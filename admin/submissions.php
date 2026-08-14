<?php
/**
 * Contact enquiries and newsletter subscribers.
 *
 * Both lists export to CSV, which is the practical way to get the
 * newsletter list into an email tool since nothing is sent from here.
 */

declare(strict_types=1);

require_once __DIR__ . '/_boot.php';

$user = require_admin();

$tab = ($_GET['tab'] ?? 'contact') === 'newsletter' ? 'newsletter' : 'contact';

/* ---------- CSV export ---------- */
if (($_GET['export'] ?? '') !== '') {
    $which = $_GET['export'] === 'newsletter' ? 'newsletter' : 'contact';

    $rows = $which === 'newsletter'
        ? db()->query(
            'SELECT email, source_page, utm_source, utm_medium, utm_campaign, created_at
             FROM newsletter_subscribers ORDER BY created_at DESC'
          )->fetchAll()
        : db()->query(
            'SELECT name, email, store_url, message, created_at
             FROM contact_submissions ORDER BY created_at DESC'
          )->fetchAll();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="brix-' . $which . '-' . date('Y-m-d') . '.csv"');

    $out = fopen('php://output', 'w');
    // BOM so Excel opens UTF-8 correctly rather than mangling accents.
    fwrite($out, "\xEF\xBB\xBF");

    if ($rows) {
        fputcsv($out, array_keys($rows[0]));
        foreach ($rows as $row) {
            fputcsv($out, $row);
        }
    }
    fclose($out);
    exit;
}

/* ---------- mark an enquiry read ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? null)) {
    $id = (int) ($_POST['id'] ?? 0);

    if (($_POST['action'] ?? '') === 'read') {
        db()->prepare('UPDATE contact_submissions SET read_at = NOW() WHERE id = :id AND read_at IS NULL')
            ->execute([':id' => $id]);
    } elseif (($_POST['action'] ?? '') === 'unread') {
        db()->prepare('UPDATE contact_submissions SET read_at = NULL WHERE id = :id')
            ->execute([':id' => $id]);
    }

    redirect('submissions.php?tab=contact');
}

$contacts = db()->query(
    'SELECT * FROM contact_submissions ORDER BY created_at DESC LIMIT 500'
)->fetchAll();

$subscribers = db()->query(
    'SELECT * FROM newsletter_subscribers ORDER BY created_at DESC LIMIT 1000'
)->fetchAll();

$unread = 0;
foreach ($contacts as $c) {
    if ($c['read_at'] === null) {
        $unread++;
    }
}

admin_head('Submissions', $user, 'submissions');
?>
<div class="ad-wrap ad-wide">
  <?php render_flash(); ?>

  <div class="ad-head">
    <div>
      <h1>Submissions</h1>
      <p class="ad-sub">
        <?= count($contacts) ?> enquir<?= count($contacts) === 1 ? 'y' : 'ies' ?>
        <?= $unread > 0 ? '(<strong>' . $unread . ' unread</strong>)' : '' ?>
        &middot; <?= count($subscribers) ?> subscriber<?= count($subscribers) === 1 ? '' : 's' ?>
      </p>
    </div>
    <a class="ad-btn" href="?export=<?= $tab ?>">Export <?= $tab ?> CSV</a>
  </div>

  <div class="ad-filters">
    <a href="?tab=contact"    class="<?= $tab === 'contact' ? 'on' : '' ?>">Contact<?= $unread ? ' (' . $unread . ')' : '' ?></a>
    <a href="?tab=newsletter" class="<?= $tab === 'newsletter' ? 'on' : '' ?>">Newsletter</a>
  </div>

<?php if ($tab === 'contact'): ?>
  <?php if (!$contacts): ?>
    <div class="ad-empty"><p>No enquiries yet.</p></div>
  <?php else: ?>
    <?php foreach ($contacts as $c): ?>
      <div class="ad-msg<?= $c['read_at'] === null ? ' ad-msg-new' : '' ?>">
        <div class="ad-msg-head">
          <div>
            <strong><?= e($c['name']) ?></strong>
            <a href="mailto:<?= e($c['email']) ?>"><?= e($c['email']) ?></a>
            <?php if ($c['store_url'] !== ''): ?>
              <a href="<?= e($c['store_url']) ?>" target="_blank" rel="noopener nofollow"><?= e($c['store_url']) ?></a>
            <?php endif; ?>
          </div>
          <div class="ad-msg-meta">
            <span><?= e(date('M j, Y g:ia', strtotime($c['created_at']))) ?></span>
            <form method="post" class="ad-inline">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
              <input type="hidden" name="action" value="<?= $c['read_at'] === null ? 'read' : 'unread' ?>">
              <button class="ad-linkbtn" type="submit"><?= $c['read_at'] === null ? 'Mark read' : 'Mark unread' ?></button>
            </form>
          </div>
        </div>
        <p class="ad-msg-body"><?= nl2br(e($c['message'])) ?></p>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

<?php else: ?>
  <?php if (!$subscribers): ?>
    <div class="ad-empty"><p>No subscribers yet.</p></div>
  <?php else: ?>
    <table class="ad-table">
      <thead>
        <tr><th>Email</th><th>Signed up from</th><th>Campaign</th><th>When</th></tr>
      </thead>
      <tbody>
      <?php foreach ($subscribers as $s): ?>
        <tr>
          <td><a href="mailto:<?= e($s['email']) ?>"><?= e($s['email']) ?></a></td>
          <td><?= e($s['source_page'] ?: '-') ?></td>
          <td>
            <?php
            $utm = array_filter([$s['utm_source'], $s['utm_medium'], $s['utm_campaign']]);
            echo $utm ? e(implode(' / ', $utm)) : '<span class="ad-sub">direct</span>';
            ?>
          </td>
          <td class="ad-nowrap"><?= e(date('M j, Y', strtotime($s['created_at']))) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
<?php endif; ?>
</div>
<?php admin_foot(); ?>
