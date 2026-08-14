<?php
/**
 * Not found.
 *
 * Also included directly by article.php when a slug does not resolve,
 * so guard against sending headers twice.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once BRIX_INCLUDES . '/posts.php';

if (!headers_sent()) {
    http_response_code(404);
}

$page_title       = 'Page not found | Brix';
$page_description = 'That page does not exist. Browse the Brix blog, case studies and setup guides instead.';
$page_canonical   = '404';
$page_nav         = null;
$page_robots      = 'noindex, follow';
$footer_col3      = 'case-studies';

$recent = [];
try {
    $recent = get_published_posts('blog', 3);
} catch (Throwable) {
    // A 404 page that itself errors is the worst outcome here.
}

require BRIX_INCLUDES . '/header.php';
?>

<section class="page-hero">
  <div class="hero-glow" aria-hidden="true"></div>
  <div class="container">
    <p class="eyebrow reveal">404</p>
    <h1 class="reveal" style="--d:.06s">That page has <em>moved on</em></h1>
    <p class="hero-sub reveal" style="--d:.12s">The link may be out of date, or the page may have been renamed. Here is where most people are heading.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="nf-links reveal">
      <a href="/">Home</a>
      <a href="features">Features</a>
      <a href="pricing">Pricing</a>
      <a href="blog">Blog</a>
      <a href="case-studies">Case studies</a>
      <a href="how-to">Guides</a>
      <a href="contact">Contact</a>
    </div>

<?php if ($recent): ?>
    <div class="cs-related">
      <div class="section-head reveal">
        <p class="eyebrow">Latest from the blog</p>
        <h2>Something else to read</h2>
      </div>
      <div class="blog-grid">
<?php foreach ($recent as $i => $p): ?>
        <a class="blog-card reveal"<?= $i > 0 ? ' style="--d:.0' . ($i * 6) . 's"' : '' ?> href="<?= e($p['slug']) ?>">
          <div class="blog-shot <?= e($p['card_gradient']) ?>">
<?php if ($p['category'] !== ''): ?>
            <span class="blog-cat"><?= e($p['category']) ?></span>
<?php endif; ?>
            <span class="blog-glyph" aria-hidden="true"><?= card_icon_svg($p['card_icon']) ?></span>
          </div>
          <div class="blog-body">
            <h3 class="blog-title"><?= e($p['title']) ?></h3>
            <p class="blog-excerpt"><?= e(truncate_words((string) $p['excerpt'], 140)) ?></p>
            <span class="blog-more">Read article &rarr;</span>
          </div>
        </a>
<?php endforeach; ?>
      </div>
    </div>
<?php endif; ?>
  </div>
</section>

<?php require BRIX_INCLUDES . '/footer.php'; ?>
