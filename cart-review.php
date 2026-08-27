<?php
/**
 * /cart-review: the short campaign landing page.
 *
 * One screen, one form, nothing to read. Where /walkthrough teaches
 * and /cart-that-sells argues, this asks. It suits warm traffic that
 * already knows what Brix is: a retargeting audience, or the second
 * email in a sequence.
 *
 * Deliberately thin. Every line on it earns its place or comes off,
 * because the only thing this page is measured on is whether the form
 * gets filled in.
 *
 * Unlisted, like the other two: nothing links here, it is absent from
 * sitemap.php, and robots.txt stays silent rather than naming the path.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once BRIX_INCLUDES . '/lead-form.php';

/* Before any output: the CSRF token is rendered inside the form. */
brix_session_start();

$lead = brix_lead_handle('cart-review');

$page_title       = 'Get a look at your Shopify cart, from the Brix team';
$page_description = 'Tell us where your store is and we will come back within one business day with what we would turn on first to lift your average order value.';
$page_canonical   = 'cart-review';
$page_robots      = 'noindex, nofollow, noarchive, nosnippet, noimageindex';
$page_chrome      = 'minimal';
$page_body_class  = 'lp lp-solo';

require BRIX_INCLUDES . '/header.php';
?>

<section class="ty-wrap">
  <div class="hero-glow" aria-hidden="true"></div>
  <div class="container ty-grid">

    <div class="ty-copy">
      <a class="hero-badge reveal" href="<?= e(SHOPIFY_APP_URL) ?>" target="_blank" rel="noopener" style="--d:.02s">
        <span class="hero-badge-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</span> <b>4.9</b> on the Shopify App Store
      </a>
      <h1 class="reveal" style="--d:.06s">Send us your store. <em>We will tell you what your cart is missing.</em></h1>
      <p class="ty-sub reveal" style="--d:.12s">One business day, from a person who has read your cart. What we would turn on first, and roughly what it is worth. Free, whether or not you install anything.</p>
      <ul class="ty-points reveal" style="--d:.18s">
        <li>A look at your actual cart, not a sales call</li>
        <li>Written by the team that built Brix</li>
        <li>Nothing to cancel and no list to leave</li>
      </ul>
    </div>

    <div class="ty-panel reveal" style="--d:.1s">
<?php if ($lead['sent']): ?>
      <div class="ty-done">
        <span class="ty-tick" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        </span>
        <h2>Got it. Talk soon.</h2>
        <p>We have your details and will come back within one business day. If you would rather not wait, Brix is free to install right now.</p>
        <a class="btn btn-primary btn-lg" href="<?= e(SHOPIFY_APP_URL) ?>" target="_blank" rel="noopener">Install free on Shopify</a>
      </div>
<?php else: ?>
      <h2 class="ty-panel-h">Where should we look?</h2>

      <form class="ty-form" method="post" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="t" value="<?= time() ?>">
        <?php /* Honeypot: never shown, never filled by a person. */ ?>
        <div class="dm-hp" aria-hidden="true">
          <label for="website">Website</label>
          <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
        </div>

<?php if ($lead['errors']): ?>
        <div class="dm-form-errs" role="alert">
<?php foreach ($lead['errors'] as $err): ?>
          <p><?= e($err) ?></p>
<?php endforeach; ?>
        </div>
<?php endif; ?>

        <div class="dm-field">
          <label for="ty-name">Your name</label>
          <input type="text" id="ty-name" name="name" required autocomplete="name"
                 value="<?= e($lead['values']['name']) ?>">
        </div>

        <div class="dm-field">
          <label for="ty-email">Email</label>
          <input type="email" id="ty-email" name="email" required autocomplete="email"
                 placeholder="you@store.com" value="<?= e($lead['values']['email']) ?>">
        </div>

        <div class="dm-field">
          <label for="ty-store">Store URL</label>
          <input type="text" id="ty-store" name="store_url" autocomplete="url"
                 placeholder="yourstore.com" value="<?= e($lead['values']['store_url']) ?>">
        </div>

        <button class="btn btn-primary btn-lg ty-send" type="submit">Send me the notes</button>
        <p class="ty-fine">We will only use this to reply. No list, no sequence.</p>
      </form>
<?php endif; ?>
    </div>

  </div>
</section>

<?php require BRIX_INCLUDES . '/footer.php'; ?>
