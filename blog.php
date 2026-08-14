<?php
/**
 * Blog index. The grid is built from the posts table, so publishing
 * an article puts it here without an edit.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once BRIX_INCLUDES . '/posts.php';

$page_title       = 'Blog: Bigger Shopify orders, one tactic at a time · Brix';
$page_description = 'Practical playbooks for lifting Average Order Value on Shopify: cart upsells, product bundles, free-shipping thresholds, AI recommendations and the apps that make them work.';
$page_canonical   = 'blog';
$page_nav         = 'blog';
$footer_col3      = 'blog';

/**
 * A database problem should not blank the page. Render the normal
 * shell with an empty grid, but answer 503 with Retry-After so a
 * crawler treats it as a temporary outage and does not replace the
 * indexed listing with an empty one.
 */
$posts = [];
try {
    $posts = get_published_posts('blog');
} catch (Throwable) {
    http_response_code(503);
    header('Retry-After: 600');
}

require BRIX_INCLUDES . '/header.php';
?>

<section class="page-hero">
  <div class="hero-glow" aria-hidden="true"></div>
  <div class="container">
    <p class="eyebrow reveal">The Brix blog</p>
    <h1 class="reveal" style="--d:.06s">Bigger orders, <em>one tactic at a time</em></h1>
    <p class="hero-sub reveal" style="--d:.12s">Practical, no-fluff guides on lifting Average Order Value for Shopify stores: cart upsells, bundles, reward thresholds, AI recommendations and the apps that make them work.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="blog-grid">

<?php foreach ($posts as $i => $p): ?>
      <a class="blog-card reveal"<?= $i > 0 ? ' style="--d:.' . str_pad((string) ($i * 6), 2, '0', STR_PAD_LEFT) . 's"' : '' ?> href="<?= e($p['slug']) ?>">
        <div class="blog-shot <?= e($p['card_gradient']) ?>">
<?php if ($p['category'] !== ''): ?>
          <span class="blog-cat"><?= e($p['category']) ?></span>
<?php endif; ?>
          <span class="blog-glyph" aria-hidden="true"><?= card_icon_svg($p['card_icon']) ?></span>
        </div>
        <div class="blog-body">
          <h2 class="blog-title"><?= e($p['title']) ?></h2>
          <p class="blog-excerpt"><?= e((string) $p['excerpt']) ?></p>
          <span class="blog-more">Read article &rarr;</span>
        </div>
      </a>

<?php endforeach; ?>
<?php if (!$posts): ?>
      <p>No articles published yet.</p>
<?php endif; ?>
    </div>
  </div>
</section>

<section class="cta-final" id="install">
  <div class="cta-gradient" aria-hidden="true"></div>
  <div class="container cta-in">
    <h2 class="reveal">Turn these tactics into revenue</h2>
    <p class="reveal" style="--d:.08s">Install free, set your first reward tier, and watch your average order value climb.</p>
    <a class="btn btn-white btn-lg reveal" style="--d:.16s" href="<?= e(SHOPIFY_APP_URL) ?>" target="_blank" rel="noopener" id="ctaInstall">Install on Shopify for free</a>
    <p class="cta-note reveal" style="--d:.24s">Free plan available &middot; No credit card</p>
  </div>
</section>

<?php require BRIX_INCLUDES . '/footer.php'; ?>
