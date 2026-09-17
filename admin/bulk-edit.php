<?php
/**
 * Bulk-edit short text fields, and/or insert internal links into the
 * body, across many posts at once.
 *
 * post-edit.php is the right place for a single post; this is for the
 * other case - a title/description refresh across a dozen posts after
 * an SEO pass, or adding the same handful of internal links across a
 * content cluster - where opening each one individually is the slow
 * part, not deciding what to change.
 *
 * Paste is a JSON object: { "slug": { "field": "new value", ... } }.
 * "slug" is the full stored slug (the type prefix included - e.g.
 * "blog-cart-upsell-examples", not the public "/blog/cart-upsell-examples"
 * address), since that is what the posts table actually keys on.
 *
 * A slug's entry can also carry "link_insertions": a list of
 * {"anchor": "...", "url": "..."} pairs. Each one finds the first
 * occurrence of that exact anchor text in the post's body that isn't
 * already a link, and wraps it - never rewrites or invents text, so
 * the anchor has to already exist in the post.
 *
 * And/or "cta_insertions": a list of
 * {"after": "...", "text": "...", "url": "...", "align": "center"}
 * pairs. Each one drops a new install-CTA button right after the
 * first paragraph matching "after" - again anchored to text that has
 * to already exist, so nothing here invents surrounding content.
 *
 * Two-step by design: Preview never writes anything, only Apply does,
 * and Apply re-runs the exact same payload the preview showed rather
 * than trusting that nothing changed in between.
 */

declare(strict_types=1);

require_once __DIR__ . '/_boot.php';

$user = require_admin();

$errors  = [];
$report  = null;
$applied = false;
$payload = (string) ($_POST['payload'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $decoded = json_decode($payload, true);

        if (!is_array($decoded) || $decoded === [] || array_is_list($decoded)) {
            $errors[] = 'Could not read that as a JSON object of { "slug": { "field": "value" } } entries.';
        } else {
            $apply  = ($_POST['action'] ?? '') === 'apply';
            $report = bulk_apply_post_fields($decoded, dryRun: !$apply);
            $applied = $apply;
        }
    }
}

admin_head('Bulk Edit', $user, 'bulk-edit');
?>
<div class="ad-wrap ad-wide">
  <?php render_flash(); ?>

  <div class="ad-head">
    <div>
      <h1>Bulk Edit</h1>
      <p class="ad-sub">Update <?= implode(', ', bulk_editable_fields()) ?> across many posts in one action.</p>
    </div>
  </div>

  <?php foreach ($errors as $error): ?>
    <div class="ad-flash ad-flash-err"><?= e($error) ?></div>
  <?php endforeach; ?>

  <?php if ($report !== null): ?>
    <div class="ad-card">
      <h2 style="margin-top:0"><?= $applied ? 'Applied' : 'Preview' ?></h2>
      <table class="ad-table">
        <thead>
          <tr><th>Slug</th><th>Result</th><th>Changes</th></tr>
        </thead>
        <tbody>
          <?php foreach ($report as $slug => $row): ?>
            <tr>
              <td><code><?= e($slug) ?></code></td>
              <td<?= $row['ok'] ? '' : ' class="ad-danger"' ?>><?= e($row['message']) ?></td>
              <td>
                <?php foreach ($row['changed'] as $field => $diff): ?>
                  <div style="margin-bottom:8px">
                    <strong><?= e($field) ?>:</strong>
                    <div style="color:var(--red);text-decoration:line-through">&minus; <?= e($diff['before'] !== '' ? $diff['before'] : '(empty)') ?></div>
                    <div style="color:var(--green)">&plus; <?= e($diff['after']) ?></div>
                  </div>
                <?php endforeach; ?>
                <?php foreach ($row['links'] ?? [] as $link): ?>
                  <div style="margin-bottom:8px">
                    <strong>link &ldquo;<?= e($link['anchor']) ?>&rdquo; &rarr; <?= e($link['url']) ?>:</strong>
                    <div<?= $link['applied'] ? ' style="color:var(--green)"' : ' class="ad-danger"' ?>>
                      <?= $link['applied'] ? '&plus; ' : '' ?><?= e($link['note']) ?>
                    </div>
                  </div>
                <?php endforeach; ?>
                <?php foreach ($row['ctas'] ?? [] as $cta): ?>
                  <div style="margin-bottom:8px">
                    <strong>CTA &ldquo;<?= e($cta['text']) ?>&rdquo; after &ldquo;<?= e(mb_substr($cta['after'], 0, 50)) ?>&hellip;&rdquo;:</strong>
                    <div<?= $cta['applied'] ? ' style="color:var(--green)"' : ' class="ad-danger"' ?>>
                      <?= $cta['applied'] ? '&plus; ' : '' ?><?= e($cta['note']) ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if (!$applied): ?>
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="apply">
        <input type="hidden" name="payload" value="<?= e($payload) ?>">
        <button class="ad-btn ad-btn-primary" type="submit">Apply these changes</button>
        <a class="ad-btn" href="bulk-edit.php">Start over</a>
      </form>
    <?php else: ?>
      <a class="ad-btn" href="bulk-edit.php">Run another batch</a>
    <?php endif; ?>
  <?php endif; ?>

  <?php if ($report === null || !$applied): ?>
    <div class="ad-card">
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="preview">
        <label class="ad-field">
          <span class="ad-label">Paste JSON</span>
          <textarea name="payload" rows="16" style="font-family:monospace;font-size:13px"
                    placeholder='{
  "blog-cart-upsell-examples": {
    "meta_title": "New title here",
    "meta_description": "New description here",
    "link_insertions": [
      {"anchor": "Frequently Bought Together", "url": "/blog/frequently-bought-together-shopify"}
    ],
    "cta_insertions": [
      {"after": "Exact existing sentence to insert after.", "text": "Install Brix free", "url": "https://apps.shopify.com/thebrix-io?...", "align": "center"}
    ]
  }
}'><?= e($payload) ?></textarea>
          <span class="ad-hint">
            Keys are the full stored slug (type prefix included). Only
            fields you include are touched - anything not mentioned is
            left exactly as it is. "link_insertions" wraps the first
            not-already-linked occurrence of each anchor phrase as a
            link. "cta_insertions" drops a new install button right
            after the first paragraph matching "after". Both need the
            anchor text to already exist word-for-word in the post.
            Nothing is written until you click Apply on the next
            screen.
          </span>
        </label>
        <button class="ad-btn ad-btn-primary" type="submit">Preview</button>
      </form>
    </div>
  <?php endif; ?>
</div>
<?php admin_foot(); ?>
