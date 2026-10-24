<?php
/**
 * Author profile page: /author/<slug>.
 *
 * Reached through .htaccess. Shows who an author is and lists what
 * they have written, so a byline on an article points somewhere real.
 * Profiles live in author_profiles() in includes/functions.php.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once BRIX_INCLUDES . '/posts.php';

$slug     = (string) ($_GET['slug'] ?? '');
$profiles = author_profiles();

if (!preg_match('/^[a-z0-9-]+$/', $slug) || !isset($profiles[$slug])) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$author = $profiles[$slug];

// Everything this author has published, newest first. A database
// problem should not break the profile: show it without the list.
$posts = [];
try {
    foreach (['blog', 'case_study'] as $type) {
        foreach (get_published_posts($type) as $p) {
            if (strcasecmp((string) $p['author'], $author['name']) === 0) {
                $posts[] = $p;
            }
        }
    }
    usort($posts, static fn (array $a, array $b): int => strcmp((string) $b['date_published'], (string) $a['date_published']));
} catch (Throwable) {
    $posts = [];
}

$page_title       = $author['name'] . ': Author at Brix';
$page_description = $author['bio'];
$page_canonical   = 'author/' . $slug;
$page_nav         = NULL;
$page_robots      = 'index, follow';
$footer_col3      = 'blog';

$person = [
    '@type'    => 'Person',
    '@id'      => SITE_URL . '/author/' . $slug . '#person',
    'name'     => $author['name'],
    'url'      => SITE_URL . '/author/' . $slug,
    'worksFor' => ['@id' => SITE_URL . '/#organization'],
];
if (!empty($author['sameAs'])) {
    $person['sameAs'] = array_values($author['sameAs']);
}
$page_schema = [[
    '@context'   => 'https://schema.org',
    '@type'      => 'ProfilePage',
    'url'        => SITE_URL . '/author/' . $slug,
    'mainEntity' => $person,
]];

require BRIX_INCLUDES . '/header.php';
?>

<section class="page-hero">
  <div class="hero-glow" aria-hidden="true"></div>
  <div class="container">
    <p class="eyebrow reveal">Author</p>
    <h1 class="reveal" style="--d:.06s"><?= e($author['name']) ?></h1>
    <p class="hero-sub reveal" style="--d:.12s"><?= e($author['bio']) ?></p>
  </div>
</section>

<section class="section">
  <div class="container">
<?php if ($posts): ?>
    <div class="section-head reveal">
      <h2>Written by <?= e($author['name']) ?></h2>
    </div>
    <div class="blog-grid">
<?php foreach ($posts as $i => $p): ?>
      <a class="blog-card reveal"<?= $i > 0 ? ' style="--d:.' . str_pad((string) min($i * 6, 36), 2, '0', STR_PAD_LEFT) . 's"' : '' ?> href="<?= e(post_url($p)) ?>">
        <div class="blog-shot <?= e($p['card_gradient']) ?>">
<?php if ($p['category'] !== ''): ?>
          <span class="blog-cat"><?= e($p['category']) ?></span>
<?php endif; ?>
          <span class="blog-glyph" aria-hidden="true"><?= card_icon_svg($p['card_icon']) ?></span>
        </div>
        <div class="blog-body">
          <h3 class="blog-title"><?= e($p['title']) ?></h3>
          <p class="blog-excerpt"><?= e((string) $p['excerpt']) ?></p>
          <div class="blog-meta">
            <span><?= e(date('M j, Y', strtotime((string) $p['date_published']))) ?></span>
            <span class="blog-dot"></span>
            <span><?= (int) $p['read_minutes'] ?> min read</span>
          </div>
          <span class="blog-more">Read article &rarr;</span>
        </div>
      </a>
<?php endforeach; ?>
    </div>
<?php endif; ?>
    <p class="reveal" style="text-align:center;margin-top:36px"><a class="btn btn-primary" href="about">About Brix &rarr;</a></p>
  </div>
</section>
<?php require BRIX_INCLUDES . '/footer.php'; ?>
