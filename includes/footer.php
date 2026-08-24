<?php
/**
 * Shared footer.
 *
 * The third column changes with context, exactly as it did when each
 * page carried its own copy of this markup:
 *   'case-studies' (default)  list of case studies
 *   'blog'                    list of articles
 *   'howto'                   the how-to anchors
 *   'tutorials'               the video course
 *
 * The case study and blog lists are now read from the database, so
 * publishing a post updates the footer on every page with no edit.
 * Titles are shortened to fit the column.
 *
 * Set $footer_col3 before including. Set $page_scripts for any extra
 * script tags a page needs.
 *
 * $page_chrome = 'minimal' replaces the whole thing with a legal line.
 * The closing scripts are shared by both, so a minimal page still gets
 * the same tracking and behaviour as every other page.
 */

declare(strict_types=1);

$footer_col3   = $footer_col3   ?? 'case-studies';
$page_scripts  = $page_scripts  ?? '';
$page_chrome   = $page_chrome   ?? 'full';

/** Fail soft: a database hiccup should not blank the whole footer. */
$footer_posts = [];
if ($page_chrome !== 'minimal' && ($footer_col3 === 'blog' || $footer_col3 === 'case-studies')) {
    try {
        $footer_posts = get_footer_links($footer_col3 === 'blog' ? 'blog' : 'case_study', 4);
    } catch (Throwable) {
        $footer_posts = [];
    }
}

/** The tutorials column is a hard-coded list, so it needs no database. */
$footer_lessons = [];
if ($page_chrome !== 'minimal' && $footer_col3 === 'tutorials') {
    require_once BRIX_INCLUDES . '/tutorials.php';
    $footer_lessons = brix_tutorials();
}
?>
</main>

<?php if ($page_chrome === 'minimal'): ?>
<footer class="footer-lite">
  <div class="container footer-lite-in">
    <span>&copy; <?= date('Y') ?> Brix. Built for Shopify.</span>
    <span class="footer-lite-legal">
      <a href="/privacy">Privacy</a> &middot;
      <a href="/terms">Terms</a> &middot;
      <a href="/contact">Contact</a>
    </span>
  </div>
</footer>
<?php else: ?>
<footer class="footer">
  <div class="container footer-grid">
    <div class="footer-brand">
      <a class="logo logo-light" href="/">
        <img class="logo-img" src="/assets/brix-logo-light.png" alt="Brix">
      </a>
      <p>Bigger orders for Shopify stores, on autopilot.</p>
      <a class="footer-shopify" href="<?= e(SHOPIFY_APP_URL) ?>" target="_blank" rel="noopener" aria-label="View Brix on the Shopify App Store">
        <img class="footer-shopify-badge" src="/assets/badge-shopify-app-store-dark.svg" alt="Available on the Shopify App Store" width="196" height="52">
      </a>
      <div class="footer-social">
        <a href="https://x.com/app_thebrix" target="_blank" rel="noopener" aria-label="X (Twitter)"><svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M18.24 2.25h3.31l-7.23 8.26 8.5 11.24h-6.66l-5.21-6.82L5 21.75H1.68l7.73-8.84L1.25 2.25h6.83l4.71 6.23zm-1.16 17.52h1.83L7.08 4.13H5.12z"/></svg></a>
        <a href="https://www.linkedin.com/company/the-brix-io" target="_blank" rel="noopener" aria-label="LinkedIn"><svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.32 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.79M6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z"/></svg></a>
        <a href="https://www.youtube.com/@TheBrixApp" target="_blank" rel="noopener" aria-label="YouTube"><svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M10 15l5.19-3L10 9v6m11.56-7.83c.13.47.22 1.1.28 1.9.07.8.1 1.49.1 2.09L22 12c0 2.19-.16 3.8-.44 4.83-.25.9-.83 1.48-1.73 1.73-.47.13-1.33.22-2.65.28-1.3.07-2.49.1-3.59.1L12 19c-4.19 0-6.8-.16-7.83-.44-.9-.25-1.48-.83-1.73-1.73-.13-.47-.22-1.1-.28-1.9-.07-.8-.1-1.49-.1-2.09L2 12c0-2.19.16-3.8.44-4.83.25-.9.83-1.48 1.73-1.73.47-.13 1.33-.22 2.65-.28 1.3-.07 2.49-.1 3.59-.1L12 5c4.19 0 6.8.16 7.83.44.9.25 1.48.83 1.73 1.73z"/></svg></a>
        <a href="https://www.instagram.com/thebrix.io/" target="_blank" rel="noopener" aria-label="Instagram"><svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M7.8 2h8.4C19.4 2 22 4.6 22 7.8v8.4a5.8 5.8 0 0 1-5.8 5.8H7.8C4.6 22 2 19.4 2 16.2V7.8A5.8 5.8 0 0 1 7.8 2m-.2 2A3.6 3.6 0 0 0 4 7.6v8.8C4 18.39 5.61 20 7.6 20h8.8a3.6 3.6 0 0 0 3.6-3.6V7.6C20 5.61 18.39 4 16.4 4H7.6m9.65 1.5a1.25 1.25 0 0 1 1.25 1.25A1.25 1.25 0 0 1 17.25 8 1.25 1.25 0 0 1 16 6.75a1.25 1.25 0 0 1 1.25-1.25M12 7a5 5 0 0 1 5 5 5 5 0 0 1-5 5 5 5 0 0 1-5-5 5 5 0 0 1 5-5m0 2a3 3 0 0 0-3 3 3 3 0 0 0 3 3 3 3 0 0 0 3-3 3 3 0 0 0-3-3z"/></svg></a>
      </div>
    </div>
    <div class="footer-col">
      <p class="footer-h">Product</p>
      <a href="/features">Features</a>
      <a href="/pricing">Pricing</a>
      <a href="/case-studies">Case studies</a>
      <a href="/blog">Blog</a>
      <a href="/how-to">Guides</a>
      <a href="/tutorials">Tutorials</a>
      <a href="/features#ai-chat">Brix AI</a>
      <a href="/contact">Contact us</a>
    </div>
    <div class="footer-col">
<?php if ($footer_col3 === 'howto'): ?>
      <p class="footer-h">How-to guides</p>
      <a href="/how-to#fbt">Frequently Bought Together</a>
      <a href="/how-to#combo">Build a Combo page</a>
      <a href="/how-to#ai">Work with Brix AI</a>
      <a href="/how-to#cart">Customize your cart</a>
      <a href="/how-to#coupon">Set up a Coupon Banner</a>
<?php elseif ($footer_col3 === 'tutorials'): ?>
      <p class="footer-h">Video tutorials</p>
      <a href="/tutorials">The whole course</a>
<?php foreach (array_slice($footer_lessons, 0, 4) as $li => $fl): ?>
      <?php /* #lesson-N opens that lesson in the player; see js/tutorials.js */ ?>
      <a href="/tutorials#lesson-<?= $li + 1 ?>"><?= e($fl['title']) ?></a>
<?php endforeach; ?>
<?php elseif ($footer_col3 === 'blog'): ?>
      <p class="footer-h">From the blog</p>
      <a href="/blog">All articles</a>
<?php foreach ($footer_posts as $fp): ?>
      <a href="<?= e(post_url($fp)) ?>"><?= e(truncate_words($fp['title'], 34)) ?></a>
<?php endforeach; ?>
<?php else: ?>
      <p class="footer-h">Case studies</p>
      <a href="/case-studies">All case studies</a>
<?php foreach ($footer_posts as $fp): ?>
      <a href="<?= e(post_url($fp)) ?>"><?= e(truncate_words($fp['title'], 34)) ?></a>
<?php endforeach; ?>
<?php endif; ?>
    </div>
    <div class="footer-news">
      <p class="footer-h">The AOV memo</p>
      <p class="footer-news-sub">One tactic a week for bigger orders. No fluff.</p>
      <form class="news-form" id="newsForm">
        <input type="email" name="email" placeholder="you@store.com" aria-label="Email address" required>
        <button class="btn btn-primary btn-sm" type="submit">Subscribe</button>
      </form>
      <p class="news-fine">No spam. Just one AOV tactic a week. Unsubscribe anytime.</p>
      <p class="news-ok" id="newsOk">You&rsquo;re in. First memo lands Monday. &#10003;</p>
    </div>
  </div>
  <div class="container footer-base">
    <span>&copy; <?= date('Y') ?> Brix. Built for Shopify.</span>
    <span class="footer-legal"><a href="/contact">Contact us</a> &middot; <a href="/privacy">Privacy Policy</a> &middot; <a href="/terms">Terms &amp; Conditions</a></span>
  </div>
</footer>
<?php endif; ?>

<script src="/js/vendor/gsap.min.js"></script>
<script src="/js/vendor/ScrollTrigger.min.js"></script>
<?php /* true, false or "partial" - json_encode rather than a ternary, which
         would flatten the truthy "partial" into true. */ ?>
<script>window.BRIX_CHAT = <?= json_encode(BRIX_CHAT_ENABLED) ?>;</script>
<script src="/js/main.js?v=<?= ASSET_JS_VER ?>"></script>
<?= $page_scripts ?>
</body>
</html>
