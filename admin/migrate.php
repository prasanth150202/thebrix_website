<?php
/**
 * Import the eight original articles into the database.
 *
 * Reads content/migrated/*.md, which were produced from the static
 * pages before they were removed. Each file carries a front matter
 * block holding the metadata that used to be baked into the HTML:
 * category, excerpt, card gradient and icon, publish date, the case
 * study figures, and the closing CTA wording.
 *
 * Safe to run more than once. A slug that already exists is skipped,
 * never overwritten, so this can never clobber an edit made in the
 * admin panel afterwards.
 */

declare(strict_types=1);

require_once __DIR__ . '/_boot.php';

$user = require_admin();

$dir   = BRIX_ROOT . '/content/migrated';
$files = glob($dir . '/*.md') ?: [];

$report = [];
$ran    = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? null)) {
    $ran = true;

    foreach ($files as $file) {
        $slug = basename($file, '.md');
        $raw  = (string) file_get_contents($file);

        [$meta, $body] = parse_front_matter($raw);

        if (trim($body) === '') {
            $report[] = ['slug' => $slug, 'state' => 'skip', 'why' => 'file has no body'];
            continue;
        }

        $exists = db()->prepare('SELECT id FROM posts WHERE slug = :s LIMIT 1');
        $exists->execute([':s' => $meta['slug'] ?? $slug]);

        if ($exists->fetchColumn()) {
            $report[] = ['slug' => $slug, 'state' => 'skip', 'why' => 'already imported'];
            continue;
        }

        $type = ($meta['type'] ?? 'blog') === 'case_study' ? 'case_study' : 'blog';

        $row = [
            ':type'             => $type,
            ':slug'             => $meta['slug'] ?? $slug,
            ':title'            => $meta['title'] ?? markdown_first_heading($body) ?? $slug,
            ':author'           => $meta['author'] ?? 'Admin',
            ':category'         => $meta['category'] ?? '',
            ':hero_subtitle'    => $meta['hero_subtitle'] ?? '',
            ':hero_image'       => safe_asset_path($meta['hero_image'] ?? ''),
            ':hero_blur'        => clamp_hero_blur($meta['hero_blur'] ?? 0),
            ':excerpt'          => $meta['excerpt'] ?? '',
            ':body_md'          => $body,
            ':read_minutes'     => (int) ($meta['read_minutes'] ?? estimate_read_minutes($body)),
            ':date_published'   => $meta['date'] ?? date('Y-m-d'),
            ':card_gradient'    => $meta['card_gradient'] ?? 'bshot-1',
            ':card_icon'        => $meta['card_icon'] ?? 'cart',
            ':cta_heading'      => $meta['cta_heading'] ?? '',
            ':cta_sub'          => $meta['cta_sub'] ?? '',
            ':meta_title'       => $meta['meta_title'] ?? '',
            ':meta_description' => $meta['meta_description'] ?? '',
            ':status'           => ($meta['status'] ?? 'published') === 'draft' ? 'draft' : 'published',
        ];

        $cols = array_map(static fn($k) => ltrim($k, ':'), array_keys($row));

        try {
            $stmt = db()->prepare(
                'INSERT INTO posts (' . implode(', ', $cols) . ') VALUES ('
                . implode(', ', array_keys($row)) . ')'
            );
            $stmt->execute($row);

            $report[] = [
                'slug'  => $slug,
                'state' => 'ok',
                'why'   => $row[':status'] . ' &middot; ' . str_word_count(strip_tags($body)) . ' words',
            ];
        } catch (PDOException $ex) {
            $report[] = ['slug' => $slug, 'state' => 'fail', 'why' => $ex->getMessage()];
        }
    }
}

$already = 0;
try {
    $already = (int) db()->query('SELECT COUNT(*) FROM posts')->fetchColumn();
} catch (Throwable) {
    // Table missing means setup has not finished; the boot redirect
    // handles that case.
}

admin_head('Migrate', $user, 'posts');
?>
<div class="ad-wrap ad-narrow">
  <div class="ad-card">
    <h1>Import the original articles</h1>
    <p class="ad-sub">Reads the markdown in <code>content/migrated/</code>, which was
       generated from the eight pages that used to be static HTML.</p>

    <?php if ($ran): ?>
      <table class="ad-table">
        <tbody>
        <?php foreach ($report as $r): ?>
          <tr>
            <td>
              <?php if ($r['state'] === 'ok'): ?><span class="ad-dot ad-dot-live"></span>
              <?php elseif ($r['state'] === 'skip'): ?><span class="ad-dot ad-dot-draft"></span>
              <?php else: ?><span class="ad-dot ad-dot-bad"></span><?php endif; ?>
              <?= e($r['slug']) ?>
            </td>
            <td class="ad-sub"><?= $r['why'] ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <p style="margin-top:22px"><a class="ad-btn ad-btn-primary" href="index.php">Go to posts</a></p>

    <?php else: ?>
      <?php if (!$files): ?>
        <div class="ad-flash ad-flash-err">
          No markdown files found in <code>content/migrated/</code>. Nothing to import.
        </div>
      <?php else: ?>
        <p><strong><?= count($files) ?> file<?= count($files) === 1 ? '' : 's' ?></strong> ready to import:</p>
        <ul class="ad-filelist">
          <?php foreach ($files as $f): ?>
            <li><?= e(basename($f)) ?></li>
          <?php endforeach; ?>
        </ul>

        <?php if ($already > 0): ?>
          <div class="ad-flash ad-flash-warn">
            There <?= $already === 1 ? 'is' : 'are' ?> already <?= $already ?> post<?= $already === 1 ? '' : 's' ?>
            in the database. Anything with a matching URL is skipped, so running this
            again will not overwrite your edits.
          </div>
        <?php endif; ?>

        <form method="post">
          <?= csrf_field() ?>
          <button class="ad-btn ad-btn-primary" type="submit">Run the import</button>
        </form>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
<?php admin_foot(); ?>
