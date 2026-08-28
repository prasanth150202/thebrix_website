<?php
/**
 * Renders one article.
 *
 * Shared by article.php (public) and admin/preview.php (drafts), so a
 * preview is the real page rather than an approximation of it.
 *
 * The markup mirrors the hand-built articles this replaced: a
 * page-hero, the .cs-article body with its back link and meta line, a
 * "keep reading" grid, and the closing CTA band.
 *
 * Expects $post and, optionally, $preview_banner.
 */

declare(strict_types=1);

$isCase   = $post['type'] === 'case_study';
$backHref = post_index_url($post['type']);
$backText = $isCase ? 'Back to case studies' : 'Back to blog';

$page_title       = $post['meta_title'] !== ''
    ? $post['meta_title']
    : $post['title'] . ' | Brix';
$page_description = $post['meta_description'] !== ''
    ? $post['meta_description']
    : (string) $post['excerpt'];
$page_canonical   = post_url($post);
$page_nav         = $isCase ? 'case-studies' : 'blog';
$footer_col3      = $isCase ? 'case-studies' : 'blog';

if (($preview_banner ?? false) === true) {
    $page_robots     = 'noindex, nofollow';
    $page_body_class = 'has-preview-bar';
}

require BRIX_INCLUDES . '/header.php';

$related = get_related_posts($post, 4);

// Optional hero background. Validated again on the way out: the column
// could have been filled in before the rule existed, or by hand.
$heroImage = safe_asset_path($post['hero_image'] ?? '');
$heroBlur  = clamp_hero_blur($post['hero_blur'] ?? 0);

$ctaHeading = $post['cta_heading'] !== ''
    ? $post['cta_heading']
    : 'Turn these tactics into revenue';
$ctaSub = $post['cta_sub'] !== ''
    ? $post['cta_sub']
    : 'Install free, set your first reward tier, and watch your average order value climb.';
?>

<?php if (($preview_banner ?? false) === true): ?>
<div class="preview-bar">
  <?php // the trailing clause is dropped on a narrow screen, so the bar stays one line ?>
  <span><?= ($preview_pending ?? false)
      ? 'Preview of unpublished changes<span class="preview-bar-note"> &middot; the live page still shows the published version</span>'
      : 'Draft preview<span class="preview-bar-note"> &middot; this post is not public</span>' ?></span>
  <a href="/admin/post-edit.php?id=<?= (int) $post['id'] ?>">Edit</a>
</div>
<?php endif; ?>

<section class="page-hero<?= $heroImage !== '' ? ' page-hero-img' : '' ?>">
  <?php if ($heroImage !== ''): ?>
    <?php // Decorative: the article title carries the meaning, so no alt text. ?>
    <div class="page-hero-bg" aria-hidden="true"
         style="background-image:url('<?= e($heroImage) ?>')<?=
           $heroBlur > 0 ? ';filter:blur(' . $heroBlur . 'px)' : '' ?>"></div>
  <?php endif; ?>
  <div class="hero-glow" aria-hidden="true"></div>
  <div class="container">
    <?php if ($post['category'] !== ''): ?>
      <p class="eyebrow reveal" style="--d:.04s"><?= e($post['category']) ?></p>
    <?php endif; ?>
    <h1 class="reveal" style="--d:.06s"><?= e($post['title']) ?></h1>
    <?php if ($post['hero_subtitle'] !== ''): ?>
      <p class="hero-sub reveal" style="--d:.12s"><?= e($post['hero_subtitle']) ?></p>
    <?php endif; ?>
  </div>
</section>

<section class="section">
  <div class="container">

    <a class="post-back" href="<?= e($backHref) ?>">&larr; <?= e($backText) ?></a>

    <!-- Body takes three quarters, the standing details sit in the
         remaining quarter and stick as you scroll on a wide screen. -->
    <div class="post-layout">
      <article class="cs-article">
        <?= render_markdown($post['body_md']) ?>
      </article>

      <aside class="post-aside">
        <div class="post-aside-in">
          <?php if ($post['author'] !== ''): ?>
            <div class="post-fact">
              <span class="post-fact-k">Written by</span>
              <span class="post-fact-v"><?= e($post['author']) ?></span>
            </div>
          <?php endif; ?>

          <div class="post-fact">
            <span class="post-fact-k">Published</span>
            <span class="post-fact-v"><?= e(format_post_date($post['date_published'])) ?></span>
          </div>

          <div class="post-fact">
            <span class="post-fact-k">Reading time</span>
            <span class="post-fact-v"><?= (int) $post['read_minutes'] ?> min</span>
          </div>

          <?php if ($post['category'] !== ''): ?>
            <div class="post-fact">
              <span class="post-fact-k">Topic</span>
              <span class="post-fact-v"><?= e($post['category']) ?></span>
            </div>
          <?php endif; ?>

          <?php if ($post['hero_subtitle'] !== ''): ?>
            <p class="post-aside-sub"><?= e($post['hero_subtitle']) ?></p>
          <?php endif; ?>

          <?php /* data-utm-lock, or js/utm.js rewrites this href back to
                   the site-wide Brix-Website campaign on load and the
                   blog and case-study tagging never reaches Shopify.
                   The click still reports: only the rewriting selector
                   skips locked links, the reporting one still matches. */ ?>
          <a class="btn btn-primary btn-sm post-aside-cta" href="<?= e(article_install_url($post['type'])) ?>"
             target="_blank" rel="noopener" data-utm-lock>Install Brix free</a>

          <?php if ($related): ?>
            <!-- Related reading lives in the sidebar rather than as a
                 grid below the article, so it is reachable at any point
                 while reading instead of only at the very end. -->
            <div class="post-more">
              <p class="post-fact-k"><?= $isCase ? 'More case studies' : 'More articles' ?></p>
              <?php foreach ($related as $r): ?>
                <a class="post-more-item" href="<?= e(post_url($r)) ?>">
                  <span class="post-more-title"><?= e($r['title']) ?></span>
                  <?php if ($r['category'] !== ''): ?>
                    <span class="post-more-meta"><?= e($r['category']) ?>
                      &middot; <?= (int) $r['read_minutes'] ?> min</span>
                  <?php endif; ?>
                </a>
              <?php endforeach; ?>
              <a class="post-more-all" href="<?= e($backHref) ?>">
                <?= $isCase ? 'All case studies' : 'All articles' ?> &rarr;
              </a>
            </div>
          <?php endif; ?>
        </div>
      </aside>
    </div>
  </div>
</section>

<section class="cta-final" id="install">
  <div class="cta-gradient" aria-hidden="true"></div>
  <div class="container cta-in">
    <h2 class="reveal"><?= e($ctaHeading) ?></h2>
    <p class="reveal" style="--d:.08s"><?= e($ctaSub) ?></p>
    <a class="btn btn-white btn-lg reveal" style="--d:.16s" href="<?= e(SHOPIFY_APP_URL) ?>"
       target="_blank" rel="noopener" id="ctaInstall">Install on Shopify for free</a>
    <p class="cta-note reveal" style="--d:.24s">Free plan available &middot; No credit card</p>
  </div>
</section>

<?php require BRIX_INCLUDES . '/footer.php'; ?>
