<?php
/**
 * Case studies index.
 *
 * The cards keep their headline figures: unlike the animated stat
 * bands and metric bars inside the old article pages, these are just
 * short strings on the post record, so they survive the move to
 * markdown intact and the grid looks exactly as it did.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once BRIX_INCLUDES . '/posts.php';

$page_title       = 'Brix Case Studies: Shopify AOV Results';
$page_description = 'See how Shopify merchants increased average order value with Brix: cart upsells, reward progress bars, and product bundle results.';
$page_canonical   = 'case-studies';
$page_nav         = 'case-studies';
$footer_col3      = 'case-studies';

// As on blog.php: degrade to an empty grid under 503 rather than
// serving a white screen if the database is unreachable.
$posts = [];
try {
    $posts = get_published_posts('case_study');
} catch (Throwable) {
    http_response_code(503);
    header('Retry-After: 600');
}

require BRIX_INCLUDES . '/header.php';
?>

<section class="page-hero">
  <div class="hero-glow" aria-hidden="true"></div>
  <div class="container">
    <p class="eyebrow reveal">Case studies</p>
    <h1 class="reveal" style="--d:.06s">Before Brix, <em>after Brix</em></h1>
    <p class="hero-sub reveal" style="--d:.12s">In-depth looks at how Shopify, FMCG and fashion brands used Brix to raise Average Order Value. No cherry-picked screenshots, just the metrics.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="cs-grid">

<?php foreach ($posts as $i => $p): ?>
      <a class="cs-card reveal"<?= $i > 0 ? ' style="--d:.' . str_pad((string) ($i * 6), 2, '0', STR_PAD_LEFT) . 's"' : '' ?> href="<?= e(post_url($p)) ?>">
        <div class="cs-shot <?= e($p['card_gradient']) ?>">
<?php if ($p['category'] !== ''): ?>
          <span class="cs-cat"><?= e($p['category']) ?></span>
<?php endif; ?>
          <span class="cs-glyph" aria-hidden="true"><?= card_icon_svg($p['card_icon']) ?></span>
        </div>
        <div class="cs-body">
          <h2 class="cs-name"><?= e($p['title']) ?></h2>
          <p class="cs-sum"><?= e((string) $p['excerpt']) ?></p>
          <span class="cs-go">Read the case study &rarr;</span>
        </div>
      </a>

<?php endforeach; ?>
<?php if (!$posts): ?>
      <p>No case studies published yet.</p>
<?php endif; ?>
    </div>
  </div>
</section>

<section class="cta-final" id="install">
  <div class="cta-gradient" aria-hidden="true"></div>
  <div class="container cta-in">
    <h2 class="reveal">Your store could be the next case study</h2>
    <p class="reveal" style="--d:.08s">Install free, set your first reward tier, and watch your average order value climb.</p>
    <a class="btn btn-white btn-lg reveal" style="--d:.16s" href="<?= e(SHOPIFY_APP_URL) ?>" target="_blank" rel="noopener" id="ctaInstall">Install on Shopify for free</a>
    <p class="cta-note reveal" style="--d:.24s">Free plan available &middot; No credit card</p>
  </div>
</section>

<?php require BRIX_INCLUDES . '/footer.php'; ?>
