<?php
/**
 * /walkthrough: the long campaign landing page.
 *
 * The talkative one. Where /cart-that-sells makes a single argument
 * and sends you to the App Store, this page teaches: it opens on the
 * overview video, walks through the features with the tutorials
 * already on the channel, and ends by asking for an email rather than
 * an install. It suits colder traffic and the mail list, where somebody
 * needs to understand the thing before they will install it.
 *
 * Videos come from includes/tutorials.php, the same list /tutorials
 * runs on, so a video added to the tutorials appears here too.
 *
 * Unlisted, for the same reasons as /cart-that-sells: nothing links
 * here, it is absent from sitemap.php, and robots.txt is deliberately
 * silent about it rather than advertising the path.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once BRIX_INCLUDES . '/tutorials.php';
require_once BRIX_INCLUDES . '/lead-form.php';

/* Before a byte of HTML: the CSRF token is rendered inside the form,
   long past the point where a session cookie can still be sent. */
brix_session_start();

$lead = brix_lead_handle('walkthrough', [
    'orders' => 'Monthly orders',
]);

$page_title       = 'See Brix work: a guided tour of the Shopify cart that sells';
$page_description = 'Watch Brix work in seven short videos, then ask us to walk through your own cart. AI upsells, Frequently Bought Together, reward tiers and coupon banners inside your Shopify cart drawer.';
$page_canonical   = 'walkthrough';
$page_robots      = 'noindex, nofollow, noarchive, nosnippet, noimageindex';
$page_chrome      = 'minimal';
$page_body_class  = 'lp';
$page_scripts     = '<script src="/js/landing-walkthrough.js?v=' . ASSET_WALKTHROUGH_VER . '"></script>';

$lessons = brix_tutorials();
$feature = $lessons[0];                 // the overview, which opens the page
$wall    = array_slice($lessons, 1);    // the rest become the video wall

require BRIX_INCLUDES . '/header.php';
?>

<div id="demoPage">

<!-- ============ HERO ============ -->
<section class="hero dm-hero">
  <div class="hero-glow" aria-hidden="true"></div>
  <div class="container dm-hero-grid">
    <div class="dm-hero-copy">
      <a class="hero-badge" data-a="hero" href="<?= e(SHOPIFY_APP_URL) ?>" target="_blank" rel="noopener">
        <span class="hero-badge-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</span> <b>4.9</b> on the Shopify App Store
      </a>
      <h1 data-a="hero">Two minutes to see it. <em>A few more to run it.</em></h1>
      <p class="hero-sub" data-a="hero">Brix turns the cart drawer your shoppers already opened into the place they add one more thing. Start with the overview on the right, then watch whichever part you would set up first.</p>
      <div class="hero-ctas" data-a="hero">
        <a class="btn btn-primary btn-lg" href="<?= e(SHOPIFY_APP_URL) ?>" target="_blank" rel="noopener">Install free on Shopify</a>
        <a class="btn btn-ghost btn-lg" href="#walkthrough">Book a walkthrough</a>
      </div>
      <p class="lp-trust" data-a="hero">
        <span>Free plan, free forever</span>
        <span>No credit card</span>
        <span>Works with your theme</span>
      </p>
    </div>

    <!-- The overview video carries the hero, because this is the page
         for somebody who wants to be shown rather than told. -->
    <div class="dm-hero-video" data-a="hero-video">
      <button class="dm-play" type="button" data-video="<?= e($feature['id']) ?>">
        <img class="dm-play-img" src="https://i.ytimg.com/vi/<?= e($feature['id']) ?>/maxresdefault.jpg"
             alt="" width="1280" height="720" fetchpriority="high">
        <span class="dm-play-veil" aria-hidden="true"></span>
        <span class="dm-play-btn" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13l11-6.5z"/></svg>
        </span>
        <span class="dm-play-meta" aria-hidden="true">
          <b><?= e($feature['title']) ?></b>
          <span><?= e(brix_tutorial_clock($feature['seconds'])) ?></span>
        </span>
        <span class="lp-sr">Play the overview: <?= e($feature['title']) ?></span>
      </button>
    </div>
  </div>
</section>

<!-- ============ THE FORM ============ -->
<!-- Second block, straight after the overview video the hero opens on.
     Somebody who has watched that already knows enough to answer, and
     the seven tutorials below are for whoever wants more first. The
     hero's own "Book a walkthrough" button still lands here; it now
     travels a short way, which is the point. -->
<section class="section dm-form-sec" id="walkthrough">
  <div class="container">
    <div class="dm-form-grid">
      <div class="dm-form-copy" data-a="head">
        <p class="eyebrow">Book a walkthrough</p>
        <h2>Or have us look at your cart</h2>
        <p class="dm-form-sub">Tell us where your store is and what you are trying to fix. We will come back within one business day with what we would turn on first, whether or not you install anything.</p>
        <ul class="dm-form-points">
          <li>A real look at your cart, not a sales call</li>
          <li>One business day, from a person</li>
          <li>No obligation and nothing to cancel</li>
        </ul>
      </div>

      <div class="dm-form-panel" data-a="form">
<?php if ($lead['sent']): ?>
        <div class="dm-form-done">
          <span class="dm-form-tick" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          </span>
          <h3>Got it. Talk soon.</h3>
          <p>We have your details and will come back within one business day. If you would rather not wait, Brix is free to install right now.</p>
          <a class="btn btn-primary" href="<?= e(SHOPIFY_APP_URL) ?>" target="_blank" rel="noopener">Install free on Shopify</a>
        </div>
<?php else: ?>
        <form class="dm-form" method="post" action="#walkthrough" novalidate>
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
            <label for="lf-name">Your name</label>
            <input type="text" id="lf-name" name="name" required autocomplete="name"
                   value="<?= e($lead['values']['name']) ?>">
          </div>

          <div class="dm-field">
            <label for="lf-email">Email</label>
            <input type="email" id="lf-email" name="email" required autocomplete="email"
                   placeholder="you@store.com" value="<?= e($lead['values']['email']) ?>">
          </div>

          <div class="dm-field">
            <label for="lf-store">Store URL <span>optional</span></label>
            <input type="text" id="lf-store" name="store_url" autocomplete="url"
                   placeholder="yourstore.com" value="<?= e($lead['values']['store_url']) ?>">
          </div>

          <div class="dm-field">
            <label for="lf-orders">Orders a month <span>optional</span></label>
            <select id="lf-orders" name="orders">
              <option value="">Prefer not to say</option>
              <option value="Under 50">Under 50</option>
              <option value="50 to 500">50 to 500</option>
              <option value="500 to 2,000">500 to 2,000</option>
              <option value="Over 2,000">Over 2,000</option>
            </select>
          </div>

          <div class="dm-field">
            <label for="lf-msg">Anything you want us to look at? <span>optional</span></label>
            <textarea id="lf-msg" name="message" rows="3"
                      placeholder="Most carts leave with one item and I cannot work out why."><?= e($lead['values']['message']) ?></textarea>
          </div>

          <button class="btn btn-primary btn-lg dm-form-send" type="submit">Ask for a walkthrough</button>
          <p class="dm-form-fine">We will only use this to reply. No list, no sequence.</p>
        </form>
<?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- ============ NUMBERS ============ -->
<section class="lp-numbers">
  <div class="container">
    <ul class="lp-numbers-row">
      <li data-a="num"><b><span data-count="2" data-prefix="$" data-suffix="M+">0</span></b><small>in extra cart revenue optimized</small></li>
      <li data-a="num"><b><span data-count="500000" data-suffix="+">0</span></b><small>upsell recommendations served</small></li>
      <li data-a="num"><b><span data-count="1000" data-suffix="+">0</span></b><small>merchant hours saved</small></li>
      <li data-a="num"><b>4.9</b><small>average App Store rating</small></li>
    </ul>
  </div>
</section>

<!-- ============ THE VIDEO WALL ============ -->
<section class="section" id="watch">
  <div class="container">
    <div class="section-head" data-a="head">
      <p class="eyebrow">Watch it work</p>
      <h2>Every part of Brix, in under two minutes each</h2>
      <p class="section-sub">These are the real tutorials, not a sizzle reel. Pick the one you would set up first.</p>
    </div>

    <div class="dm-wall">
<?php foreach ($wall as $lesson): ?>
      <article class="dm-card" data-a="card">
        <button class="dm-play dm-play-sm" type="button" data-video="<?= e($lesson['id']) ?>">
          <img class="dm-play-img" src="https://i.ytimg.com/vi/<?= e($lesson['id']) ?>/hqdefault.jpg"
               alt="" width="480" height="360" loading="lazy">
          <span class="dm-play-veil" aria-hidden="true"></span>
          <span class="dm-play-btn" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13l11-6.5z"/></svg>
          </span>
          <span class="dm-dur" aria-hidden="true"><?= e(brix_tutorial_clock($lesson['seconds'])) ?></span>
          <span class="lp-sr">Play: <?= e($lesson['title']) ?></span>
        </button>
        <div class="dm-card-body">
          <h3><?= e($lesson['title']) ?></h3>
          <p><?= e($lesson['blurb']) ?></p>
        </div>
      </article>
<?php endforeach; ?>
    </div>

    <div class="lp-cta-row" data-a="head">
      <a class="btn btn-primary btn-lg" href="<?= e(SHOPIFY_APP_URL) ?>" target="_blank" rel="noopener">Install free on Shopify</a>
      <span class="lp-cta-note">Free forever plan &middot; No credit card</span>
    </div>
  </div>
</section>

<!-- ============ FEATURE ROWS ============ -->
<section class="section section-soft">
  <div class="container">
    <div class="section-head" data-a="head">
      <p class="eyebrow">What it changes</p>
      <h2>Three things your cart starts doing</h2>
    </div>

    <div class="dm-rows">
      <div class="dm-row" data-a="row">
        <div class="dm-row-copy">
          <span class="dm-row-tag">Ask for the second item</span>
          <h3>The cart suggests what goes with it</h3>
          <p>Frequently Bought Together puts genuinely related products inside the drawer, chosen by hand, by rules, or by AI. The shopper never leaves the cart to find them.</p>
          <ul class="dm-row-list">
            <li>Three layouts, from a compact row to a featured card</li>
            <li>Manual, automatic or AI-powered picks</li>
            <li>Enabled from the theme editor, with no code</li>
          </ul>
        </div>
        <div class="dm-row-art" aria-hidden="true">
          <div class="dm-mock">
            <p class="dm-mock-h">Frequently bought together</p>
            <div class="dm-mock-row"><span class="cm-thumb th-a"></span><span><b>Vitamin C Serum</b><small>In your cart</small></span><em>$28</em></div>
            <div class="dm-mock-row is-add"><span class="cm-thumb th-c"></span><span><b>Hydrating Moisturizer</b><small>Pairs well</small></span><em>$24</em></div>
            <div class="dm-mock-row is-add"><span class="cm-thumb th-b"></span><span><b>Gentle Cleanser</b><small>Pairs well</small></span><em>$19</em></div>
            <div class="dm-mock-cta">Add 3 items &middot; $71</div>
          </div>
        </div>
      </div>

      <div class="dm-row dm-row-flip" data-a="row">
        <div class="dm-row-copy">
          <span class="dm-row-tag">Show the finish line</span>
          <h3>Reward tiers stop being a secret</h3>
          <p>You already offer free shipping at a number. The progress bar says how close this cart is to it, and the average basket moves to meet it.</p>
          <ul class="dm-row-list">
            <li>As many tiers as you like: shipping, gifts, discounts</li>
            <li>Live progress as items go in and out</li>
            <li>Styled in the Cart Editor with a preview beside you</li>
          </ul>
        </div>
        <div class="dm-row-art" aria-hidden="true">
          <div class="dm-mock">
            <p class="dm-mock-h">You are $27.00 from free shipping</p>
            <div class="dm-bar"><i style="width:64%"></i></div>
            <div class="dm-bar-tiers"><span>Free shipping &middot; $75</span><span>Free gift &middot; $120</span></div>
            <div class="dm-mock-row"><span class="cm-thumb th-a"></span><span><b>Alpine hoodie</b><small>Moss &middot; M</small></span><em>$48</em></div>
            <div class="dm-mock-cta">Checkout &middot; $48.00</div>
          </div>
        </div>
      </div>

      <div class="dm-row" data-a="row">
        <div class="dm-row-copy">
          <span class="dm-row-tag">Say where the money came from</span>
          <h3>Analytics that name the cause</h3>
          <p>Most apps hand you a dashboard. Brix tells you which tier, which upsell and which bundle earned each extra dollar, then suggests what to change next.</p>
          <ul class="dm-row-list">
            <li>Revenue split by feature, not one lump number</li>
            <li>Tier funnel: how many carts reach each reward</li>
            <li>Insight cards with the change already written</li>
          </ul>
        </div>
        <div class="dm-row-art" aria-hidden="true">
          <div class="dm-mock">
            <p class="dm-mock-h">Revenue by feature &middot; last 30 days</p>
            <div class="dm-stat"><span>Rewards progress bar</span><b>$5,240</b></div>
            <div class="dm-stat"><span>Frequently Bought Together</span><b>$4,110</b></div>
            <div class="dm-stat"><span>Build a Combo</span><b>$3,590</b></div>
            <div class="dm-mock-cta dm-mock-cta-soft">Average order value &middot; $86.40</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ STEPS ============ -->
<section class="section">
  <div class="container">
    <div class="section-head" data-a="head">
      <p class="eyebrow">Getting live</p>
      <h2>Three steps, none of them a developer</h2>
    </div>
    <ol class="dm-steps">
      <span class="dm-steps-line" aria-hidden="true"><i></i></span>
      <li class="dm-step" data-a="step">
        <span class="dm-step-n">1</span>
        <h3>Install from the App Store</h3>
        <p>One click from your Shopify admin, onto the theme you already have.</p>
      </li>
      <li class="dm-step" data-a="step">
        <span class="dm-step-n">2</span>
        <h3>Switch on what you want</h3>
        <p>Set your offers and reward tiers, then enable the widget from the theme editor.</p>
      </li>
      <li class="dm-step" data-a="step">
        <span class="dm-step-n">3</span>
        <h3>Read what it earned</h3>
        <p>The analytics attribute the extra revenue to the offer that caused it.</p>
      </li>
    </ol>
  </div>
</section>

<!-- ============ PROOF ============ -->
<section class="section section-soft">
  <div class="container">
    <div class="section-head section-head-row" data-a="head">
      <div>
        <p class="eyebrow">Merchants on Brix</p>
        <h2>What it did for their carts</h2>
      </div>
      <div class="carousel-nav">
        <button class="car-btn" id="carPrev" aria-label="Previous testimonials">&larr;</button>
        <button class="car-btn" id="carNext" aria-label="Next testimonials">&rarr;</button>
      </div>
    </div>
    <div class="carousel" id="carousel">
      <figure class="t-card"><span class="t-lift">+41% AOV</span><blockquote>&ldquo;Brix helped us turn our cart into a real sales channel. The upsell and rewards bar features made it easier for customers to add more before checkout.&rdquo;</blockquote><figcaption><span class="t-avatar av-1">SR</span><span><b>Sana Rahim</b></span></figcaption></figure>
      <figure class="t-card"><span class="t-lift">+52% AOV</span><blockquote>&ldquo;Setup was simple, customization was clean, and the cart experience felt premium. Brix is a strong AOV booster for Shopify brands.&rdquo;</blockquote><figcaption><span class="t-avatar av-4">MT</span><span><b>Marco Tan</b></span></figcaption></figure>
      <figure class="t-card"><span class="t-lift">+28% AOV</span><blockquote>&ldquo;Frequently Bought Together and the coupon slider solved two major problems for us. Customers could discover related products and apply discounts without leaving the cart.&rdquo;</blockquote><figcaption><span class="t-avatar av-2">DK</span><span><b>Daniel Kim</b></span></figcaption></figure>
      <figure class="t-card"><span class="t-lift">+35% AOV</span><blockquote>&ldquo;Brix AI Analysis gave us clarity on what was working. We stopped guessing and started optimizing our cart based on real opportunities.&rdquo;</blockquote><figcaption><span class="t-avatar av-3">AO</span><span><b>Amara Obi</b></span></figcaption></figure>
      <figure class="t-card"><span class="t-lift">+23% AOV</span><blockquote>&ldquo;The insight cards are like having a CRO consultant on retainer, except it costs $29 a month.&rdquo;</blockquote><figcaption><span class="t-avatar av-5">LP</span><span><b>Lena Petrov</b></span></figcaption></figure>
    </div>
  </div>
</section>

<!-- ============ FINAL CTA ============ -->
<section class="cta-final" id="install">
  <div class="cta-gradient" aria-hidden="true"></div>
  <div class="container cta-in">
    <h2 class="reveal">Seen enough?</h2>
    <p class="reveal" style="--d:.08s">Brix is free to install and free to keep on the first 50 orders a month. The fastest way to know is to switch it on.</p>
    <a class="btn btn-white btn-lg reveal" style="--d:.16s" href="<?= e(SHOPIFY_APP_URL) ?>" target="_blank" rel="noopener" id="ctaInstall">Install on Shopify for free</a>
    <p class="cta-note reveal" style="--d:.24s">Free plan available &middot; No credit card &middot; Cancel from your Shopify admin</p>
  </div>
</section>

</div>

<?php require BRIX_INCLUDES . '/footer.php'; ?>
