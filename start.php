<?php
/**
 * /start: the campaign landing page.
 *
 * Where Meta ads and the email list point. It is deliberately not the
 * homepage: one argument, and two ways to act on it. The nav links, the
 * burger and the whole footer are gone ($page_chrome), so the only ways
 * off the page are the App Store listing the campaign is paying to reach
 * and the lead form directly under the hero.
 *
 * That form is the one /demo and /try already run, from
 * includes/lead-form.php, writing to contact_submissions tagged 'start'.
 * It is here because an ad click that is not ready to install today is
 * not the same as a lost one, and until now this page had nothing to
 * offer that visitor but the same button they had already declined.
 *
 * Unlisted. Nothing on the site links here, it is deliberately absent
 * from sitemap.php, and the robots directive below keeps it out of every
 * search index, archive and snippet. The URL is the only way in.
 *
 * It is deliberately NOT disallowed in robots.txt, which would be the
 * wrong tool twice over: a crawler blocked from fetching the page never
 * reads the noindex and can still list the bare URL, and robots.txt is
 * itself public, so naming the path there would publish the address we
 * are trying to keep quiet.
 *
 * Worth being straight about the limit: this is unlisted, not
 * authenticated. Anyone holding the link can open it or pass it on.
 *
 * Every CTA points at SHOPIFY_APP_URL, which js/utm.js rewrites with the
 * campaign the visitor actually arrived on, so ad spend is attributed
 * without anything being hardcoded here.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once BRIX_INCLUDES . '/lead-form.php';

/* Before a byte of HTML: the CSRF token is rendered inside the form,
   long past the point where a session cookie can still be sent. */
brix_session_start();

/* Same qualifying question /demo asks, so a lead from either page reads
   the same way in the admin. */
$lead = brix_lead_handle('start', [
    'orders' => 'Monthly orders',
]);

$page_title       = 'Brix: turn your Shopify cart into your best salesperson';
$page_description = 'Your cart is the last screen before checkout and the cheapest place to grow revenue. Brix adds AI upsells, Frequently Bought Together, reward tiers and coupon banners inside the cart drawer. Free plan, no credit card.';
$page_canonical   = 'start';
$page_robots      = 'noindex, nofollow, noarchive, nosnippet, noimageindex';
$page_chrome      = 'minimal';
$page_body_class  = 'lp lp-bar';
$page_scripts     = '<script src="/js/landing.js?v=' . ASSET_LANDING_VER . '"></script>';

require BRIX_INCLUDES . '/header.php';
?>

<!-- ============ HERO ============ -->
<section class="hero lp-hero">
  <div class="hero-glow" aria-hidden="true"></div>
  <div class="container hero-grid">
    <div class="hero-copy">
      <a class="hero-badge reveal" href="<?= e(SHOPIFY_APP_URL) ?>" target="_blank" rel="noopener" style="--d:.02s">
        <span class="hero-badge-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</span> <b>4.9</b> on the Shopify App Store
      </a>
      <h1 class="reveal" style="--d:.08s">You already paid for the click. Your cart decides <em>what it&rsquo;s worth.</em></h1>
      <p class="hero-sub reveal" style="--d:.16s">Brix works inside the cart drawer your shoppers already opened, asking for the second item while they are still deciding. Upsells, Frequently Bought Together, reward tiers and coupon codes, with no theme code to write.</p>
      <div class="hero-ctas reveal" style="--d:.24s">
        <a class="btn btn-primary btn-lg" href="<?= e(SHOPIFY_APP_URL) ?>" target="_blank" rel="noopener">Install free on Shopify</a>
      </div>
      <p class="lp-trust reveal" style="--d:.32s">
        <span>Free plan, free forever</span>
        <span>No credit card</span>
        <span>Live in a few minutes</span>
      </p>
    </div>

    <!-- The product doing the thing the headline claims. Same demo as the
         homepage, animated by the heroCart routine in js/main.js. -->
    <div class="hero-visual reveal" style="--d:.2s">
      <div class="cart-mock" id="heroCart">
        <div class="cm-head">
          <span class="cm-title">Your cart</span>
          <span class="cm-badge" id="cmCount">1 item</span>
        </div>
        <div class="cm-goal">
          <p class="cm-msg" id="cmMsg">You&rsquo;re <b>$27.00</b> away from <b>free shipping</b></p>
          <div class="cm-track">
            <div class="cm-fill" id="cmFill" style="width:37%"></div>
            <div class="cm-node" id="nodeShip" style="left:57.7%">
              <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor" aria-hidden="true"><path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg>
            </div>
            <div class="cm-node" id="nodeGift" style="left:92.3%">
              <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor" aria-hidden="true"><path d="M20 6h-2.18c.11-.31.18-.65.18-1 0-1.66-1.34-3-3-3-1.05 0-1.96.54-2.5 1.35l-.5.67-.5-.68C10.96 2.54 10.05 2 9 2 7.34 2 6 3.34 6 5c0 .35.07.69.18 1H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-5-2c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zM9 4c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm11 15H4v-2h16v2zm0-5H4V8h5.08L7 10.83 8.62 12 11 8.76l1-1.36 1 1.36L15.38 12 17 10.83 14.92 8H20v6z"/></svg>
            </div>
          </div>
          <div class="cm-tiers">
            <span>Free shipping &middot; $75</span>
            <span>Free gift &middot; $120</span>
          </div>
        </div>
        <ul class="cm-items" id="cmItems">
          <li class="cm-item is-in">
            <span class="cm-thumb th-a" aria-hidden="true"></span>
            <span class="cm-item-info"><b>Alpine hoodie</b><small>Moss &middot; M</small></span>
            <span class="cm-price">$48.00</span>
          </li>
          <li class="cm-item" id="item2">
            <span class="cm-thumb th-b" aria-hidden="true"></span>
            <span class="cm-item-info"><b>Trail beanie</b><small>Charcoal</small></span>
            <span class="cm-price">$34.00</span>
          </li>
          <li class="cm-item" id="item3">
            <span class="cm-thumb th-c" aria-hidden="true"></span>
            <span class="cm-item-info"><b>Camp mug set</b><small>Set of 2</small></span>
            <span class="cm-price">$42.00</span>
          </li>
          <li class="cm-item cm-item-gift" id="itemGift">
            <span class="cm-thumb th-gift" aria-hidden="true"><svg viewBox="0 0 24 24" width="20" height="20" fill="#2E7D53"><path d="M20 6h-2.18c.11-.31.18-.65.18-1 0-1.66-1.34-3-3-3-1.05 0-1.96.54-2.5 1.35l-.5.67-.5-.68C10.96 2.54 10.05 2 9 2 7.34 2 6 3.34 6 5c0 .35.07.69.18 1H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-5-2c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zM9 4c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm11 15H4v-2h16v2zm0-5H4V8h5.08L7 10.83 8.62 12 11 8.76l1-1.36 1 1.36L15.38 12 17 10.83 14.92 8H20v6z"/></svg></span>
            <span class="cm-item-info"><b>Sherpa socks</b><small>Your free gift</small></span>
            <span class="cm-price cm-price-free">FREE</span>
          </li>
        </ul>
        <div class="cm-upsell" id="cmUpsell">
          <span class="cm-thumb th-b" aria-hidden="true"></span>
          <span class="cm-item-info"><b>Trail beanie</b><small>Pairs well with your hoodie</small></span>
          <button class="cm-add" id="cmAdd" tabindex="-1">+ Add &middot; $34</button>
        </div>
        <div class="cm-foot">
          <span>Subtotal</span>
          <b id="cmTotal">$48.00</b>
        </div>
        <button class="cm-checkout" tabindex="-1">Checkout</button>
        <canvas class="confetti-canvas" id="cmConfetti" aria-hidden="true"></canvas>
      </div>
      <div class="hero-chip hero-chip-1" aria-hidden="true">+32% AOV this month</div>
      <div class="hero-chip hero-chip-2" aria-hidden="true">Brix AI &middot; 3 changes live</div>
    </div>
  </div>
</section>

<!-- ============ THE FORM ============ -->
<!-- The second way to act on the argument, and deliberately the second
     block: the hero makes the case, and the ask follows it before the
     page has spent any of the visitor's patience. Everything below is
     for whoever wants more before they answer.

     Plain .section rather than section-soft, because the hero above and
     the numbers strip below are both paper-soft and a third soft band
     between them would read as one undivided stretch of page.

     Same form, same table and same admin row as /demo and /try, tagged
     'start'. -->
<section class="section dm-form-sec" id="cart-review">
  <div class="container">
    <div class="dm-form-grid">
      <div class="dm-form-copy reveal">
        <p class="eyebrow">Not installing today</p>
        <h2>Then let us look at your cart first</h2>
        <p class="dm-form-sub">Tell us where your store is and we will come back within one business day with what we would turn on first, and roughly what it is worth. Free, whether or not you ever install anything.</p>
        <ul class="dm-form-points">
          <li>A real look at your cart, not a sales call</li>
          <li>Written by the team that built Brix</li>
          <li>No obligation and nothing to cancel</li>
        </ul>
      </div>

      <?php /* No .reveal on the panel. The rest of the page can afford to
               fade in on scroll; the one thing that must never be sitting
               at opacity 0 because a script did not arrive is the form. */ ?>
      <div class="dm-form-panel">
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
        <form class="dm-form" method="post" action="#cart-review" novalidate>
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
            <label for="st-name">Your name</label>
            <input type="text" id="st-name" name="name" required autocomplete="name"
                   value="<?= e($lead['values']['name']) ?>">
          </div>

          <div class="dm-field">
            <label for="st-email">Email</label>
            <input type="email" id="st-email" name="email" required autocomplete="email"
                   placeholder="you@store.com" value="<?= e($lead['values']['email']) ?>">
          </div>

          <div class="dm-field">
            <label for="st-store">Store URL <span>optional</span></label>
            <input type="text" id="st-store" name="store_url" autocomplete="url"
                   placeholder="yourstore.com" value="<?= e($lead['values']['store_url']) ?>">
          </div>

          <div class="dm-field">
            <label for="st-orders">Orders a month <span>optional</span></label>
            <select id="st-orders" name="orders">
              <option value="">Prefer not to say</option>
              <option value="Under 50">Under 50</option>
              <option value="50 to 500">50 to 500</option>
              <option value="500 to 2,000">500 to 2,000</option>
              <option value="Over 2,000">Over 2,000</option>
            </select>
          </div>

          <button class="btn btn-primary btn-lg dm-form-send" type="submit">Send me the notes</button>
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
      <li class="reveal"><b><span data-count="2" data-prefix="$" data-suffix="M+">0</span></b><small>in extra cart revenue optimized</small></li>
      <li class="reveal" style="--d:.06s"><b><span data-count="500000" data-suffix="+">0</span></b><small>upsell recommendations served</small></li>
      <li class="reveal" style="--d:.12s"><b><span data-count="1000" data-suffix="+">0</span></b><small>merchant hours saved</small></li>
      <li class="reveal" style="--d:.18s"><b>4.9</b><small>average App Store rating</small></li>
    </ul>
  </div>
</section>

<!-- ============ THE LEAK ============ -->
<section class="section">
  <div class="container">
    <div class="section-head reveal">
      <p class="eyebrow">Where the money goes</p>
      <h2>Traffic is not your problem. The last screen is.</h2>
      <p class="section-sub">You can buy more visitors, or you can be worth more per visitor. Three things quietly cap what every cart is worth.</p>
    </div>
    <div class="lp-leaks">
      <div class="lp-leak reveal">
        <span class="lp-leak-fig" aria-hidden="true">1 item</span>
        <h3>Most carts hold one thing</h3>
        <p>Shoppers add what the ad promised and go straight to checkout. Nothing in the cart ever asks for the second item, so nobody adds one.</p>
      </div>
      <div class="lp-leak reveal" style="--d:.07s">
        <span class="lp-leak-fig" aria-hidden="true">Promo code</span>
        <h3>The discount box sends people away</h3>
        <p>An empty code field is an instruction to go and look for one. They open a new tab, and some of them never come back to finish.</p>
      </div>
      <div class="lp-leak reveal" style="--d:.14s">
        <span class="lp-leak-fig" aria-hidden="true">$75</span>
        <h3>Free shipping stays a secret</h3>
        <p>You set the threshold, but the cart never mentions it. Shoppers who were eight dollars away check out without ever knowing.</p>
      </div>
    </div>
  </div>
</section>

<!-- ============ THE SIGNATURE: TWO RECEIPTS ============ -->
<section class="section section-soft lp-receipts-sec">
  <div class="container">
    <div class="section-head reveal">
      <p class="eyebrow">One shopper, two carts</p>
      <h2>The same visitor. A different order.</h2>
      <p class="section-sub">Nothing about the traffic changed. The cart just did its job on the way past.</p>
    </div>

    <div class="lp-receipts">
      <figure class="lp-receipt reveal">
        <p class="lp-receipt-tag">Your cart today</p>
        <div class="lp-paper">
          <p class="lp-paper-h">Order confirmation</p>
          <dl class="lp-lines">
            <div class="lp-line"><dt>Alpine hoodie <span>Moss &middot; M</span></dt><dd>48.00</dd></div>
            <div class="lp-line lp-line-sub"><dt>Shipping</dt><dd>6.90</dd></div>
          </dl>
          <div class="lp-total"><span>Total</span><b>$54.90</b></div>
          <p class="lp-paper-note">One item. The shopper never saw a reason to add another.</p>
        </div>
      </figure>

      <figure class="lp-receipt lp-receipt-win reveal" style="--d:.12s">
        <p class="lp-receipt-tag lp-receipt-tag-win">The same cart, with Brix</p>
        <div class="lp-paper">
          <p class="lp-paper-h">Order confirmation</p>
          <dl class="lp-lines">
            <div class="lp-line"><dt>Alpine hoodie <span>Moss &middot; M</span></dt><dd>48.00</dd></div>
            <div class="lp-line lp-line-add"><dt>Trail beanie <span>Frequently Bought Together</span></dt><dd>34.00</dd></div>
            <div class="lp-line lp-line-sub lp-line-won"><dt>Shipping <span>Unlocked at $75</span></dt><dd>FREE</dd></div>
          </dl>
          <div class="lp-total lp-total-win"><span>Total</span><b>$82.00</b></div>
          <p class="lp-paper-note">The cart suggested the beanie, then showed how close free shipping was. The shopper did the rest.</p>
        </div>
      </figure>
    </div>

    <p class="lp-receipts-foot reveal">Merchants running Brix report average order values <b>23% to 52% higher</b>. Your numbers will be your own, which is why the analytics tell you which offer earned each extra dollar.</p>

    <div class="lp-cta-row reveal">
      <a class="btn btn-primary btn-lg" href="<?= e(SHOPIFY_APP_URL) ?>" target="_blank" rel="noopener">Install free on Shopify</a>
      <span class="lp-cta-note">Free forever plan &middot; No credit card</span>
    </div>
  </div>
</section>

<!-- ============ WHAT YOU GET ============ -->
<section class="section">
  <div class="container">
    <div class="section-head reveal">
      <p class="eyebrow">What you switch on</p>
      <h2>Six ways to make the cart earn more</h2>
      <p class="section-sub">Turn on what suits your store. Every one of them lives inside the cart your theme already has.</p>
    </div>
    <div class="lp-features">
      <div class="lp-feature reveal">
        <span class="lp-feature-ic gic-1"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M16.5 9.4 7.5 4.21"/><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg></span>
        <h3>Frequently Bought Together</h3>
        <p>Show the products that actually go with what is in the cart, picked by hand, by rules, or by AI.</p>
      </div>
      <div class="lp-feature reveal" style="--d:.05s">
        <span class="lp-feature-ic gic-2"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg></span>
        <h3>Build a Combo</h3>
        <p>Give bundles their own page so shoppers can pick a set in one go instead of hunting for the pieces.</p>
      </div>
      <div class="lp-feature reveal" style="--d:.1s">
        <span class="lp-feature-ic gic-3"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/><line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/></svg></span>
        <h3>Rewards progress bar</h3>
        <p>Tell shoppers how far they are from free shipping or a free gift, and watch the average basket move to meet it.</p>
      </div>
      <div class="lp-feature reveal" style="--d:.15s">
        <span class="lp-feature-ic gic-5"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg></span>
        <h3>Coupon banner</h3>
        <p>Put the code on the product page, next to Add to Cart, so nobody leaves your store to go looking for one.</p>
      </div>
      <div class="lp-feature reveal" style="--d:.2s">
        <span class="lp-feature-ic gic-1"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="3"/><path d="M8 15v-3M12 15V9M16 15v-5"/></svg></span>
        <h3>Cart Editor</h3>
        <p>Design the drawer, the announcement bar and the empty cart with a live preview. No theme code, no developer.</p>
      </div>
      <div class="lp-feature reveal" style="--d:.25s">
        <span class="lp-feature-ic gic-2"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M11 3l1.6 4.4L17 9l-4.4 1.6L11 15l-1.6-4.4L5 9l4.4-1.6L11 3z"/><path d="M18 13l.9 2.4L21 16.5l-2.1.8L18 20l-.9-2.7L15 16.5l2.1-1.1L18 13z"/></svg></span>
        <h3>AI analytics</h3>
        <p>See which tier, which upsell and which bundle earned each extra dollar, with suggestions for what to change next.</p>
      </div>
    </div>
  </div>
</section>

<!-- ============ PROOF ============ -->
<section class="section section-soft">
  <div class="container">
    <div class="section-head section-head-row reveal">
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
      <figure class="t-card reveal">
        <span class="t-lift">+41% AOV</span>
        <blockquote>&ldquo;Brix helped us turn our cart into a real sales channel. The upsell and rewards bar features made it easier for customers to add more before checkout.&rdquo;</blockquote>
        <figcaption><span class="t-avatar av-1">SR</span><span><b>Sana Rahim</b></span></figcaption>
      </figure>
      <figure class="t-card reveal" style="--d:.06s">
        <span class="t-lift">+52% AOV</span>
        <blockquote>&ldquo;Setup was simple, customization was clean, and the cart experience felt premium. Brix is a strong AOV booster for Shopify brands.&rdquo;</blockquote>
        <figcaption><span class="t-avatar av-4">MT</span><span><b>Marco Tan</b></span></figcaption>
      </figure>
      <figure class="t-card reveal" style="--d:.12s">
        <span class="t-lift">+28% AOV</span>
        <blockquote>&ldquo;Frequently Bought Together and the coupon slider solved two major problems for us. Customers could discover related products and apply discounts without leaving the cart.&rdquo;</blockquote>
        <figcaption><span class="t-avatar av-2">DK</span><span><b>Daniel Kim</b></span></figcaption>
      </figure>
      <figure class="t-card">
        <span class="t-lift">+35% AOV</span>
        <blockquote>&ldquo;Brix AI Analysis gave us clarity on what was working. We stopped guessing and started optimizing our cart based on real opportunities.&rdquo;</blockquote>
        <figcaption><span class="t-avatar av-3">AO</span><span><b>Amara Obi</b></span></figcaption>
      </figure>
      <figure class="t-card">
        <span class="t-lift">+23% AOV</span>
        <blockquote>&ldquo;The insight cards are like having a CRO consultant on retainer, except it costs $29 a month.&rdquo;</blockquote>
        <figcaption><span class="t-avatar av-5">LP</span><span><b>Lena Petrov</b></span></figcaption>
      </figure>
    </div>
  </div>
</section>

<!-- ============ HOW FAST ============ -->
<section class="section">
  <div class="container">
    <div class="section-head reveal">
      <p class="eyebrow">Getting live</p>
      <h2>Three steps, and none of them involve a developer</h2>
    </div>
    <ol class="lp-steps">
      <li class="lp-step reveal">
        <span class="lp-step-n">1</span>
        <h3>Install from the App Store</h3>
        <p>One click from your Shopify admin. Brix installs on the theme you already have.</p>
      </li>
      <li class="lp-step reveal" style="--d:.08s">
        <span class="lp-step-n">2</span>
        <h3>Switch on what you want</h3>
        <p>Pick your offers and set your reward tiers in the dashboard, then enable the widget from the theme editor.</p>
      </li>
      <li class="lp-step reveal" style="--d:.16s">
        <span class="lp-step-n">3</span>
        <h3>Read what it earned</h3>
        <p>The analytics attribute the extra revenue to the offer that caused it, so you know what to keep.</p>
      </li>
    </ol>
    <div class="lp-cta-row reveal">
      <a class="btn btn-primary btn-lg" href="<?= e(SHOPIFY_APP_URL) ?>" target="_blank" rel="noopener">Install free on Shopify</a>
      <span class="lp-cta-note">Works with your current theme</span>
    </div>
  </div>
</section>

<!-- ============ PRICING ============ -->
<section class="section section-soft">
  <div class="container">
    <div class="section-head reveal">
      <p class="eyebrow">Pricing</p>
      <h2>Start on free. Pay when it has already paid off.</h2>
      <p class="section-sub">Free is free forever, so you can see your first lift before you spend anything. Starter and Pro bill through Shopify, and you cancel from your Shopify admin.</p>
    </div>
    <div class="lp-price-grid">
      <div class="lp-price reveal">
        <h3>Free</h3>
        <p class="lp-price-fig"><b>$0</b><span>/month</span></p>
        <p class="lp-price-for">Up to 50 orders a month</p>
        <ul class="lp-price-list">
          <li>Cart drawer, announcement bar and empty cart</li>
          <li>10 AI credits a month</li>
          <li>Basic analytics: revenue, AOV, clicks</li>
        </ul>
        <a class="btn btn-ghost lp-price-cta" href="<?= e(SHOPIFY_APP_URL) ?>" target="_blank" rel="noopener">Install free</a>
      </div>
      <div class="lp-price lp-price-hot reveal" style="--d:.07s">
        <span class="lp-price-flag">Most merchants start here</span>
        <h3>Starter</h3>
        <p class="lp-price-fig"><b>$29</b><span>/month</span></p>
        <p class="lp-price-for">Up to 500 orders a month</p>
        <ul class="lp-price-list">
          <li>AI Cart Upsell and Frequently Bought Together, unlocked</li>
          <li>Build a Combo, up to 3 templates</li>
          <li>30 AI credits a month</li>
          <li>No &ldquo;Powered by BRIX&rdquo; watermark</li>
        </ul>
        <a class="btn btn-primary lp-price-cta" href="<?= e(SHOPIFY_APP_URL) ?>" target="_blank" rel="noopener">Start free trial</a>
      </div>
      <div class="lp-price reveal" style="--d:.14s">
        <h3>Pro</h3>
        <p class="lp-price-fig"><b>$79</b><span>/month</span></p>
        <p class="lp-price-for">Unlimited orders</p>
        <ul class="lp-price-list">
          <li>Unlimited Build a Combo templates</li>
          <li>Advanced AI analytics and unlimited AI agents</li>
          <li>90 AI credits a month</li>
        </ul>
        <a class="btn btn-ghost lp-price-cta" href="<?= e(SHOPIFY_APP_URL) ?>" target="_blank" rel="noopener">Start free trial</a>
      </div>
    </div>
  </div>
</section>

<!-- ============ OBJECTIONS ============ -->
<section class="section">
  <div class="container container-narrow">
    <div class="section-head reveal">
      <p class="eyebrow">Before you install</p>
      <h2>The questions merchants ask first</h2>
    </div>
    <div class="faq-list reveal" style="--d:.08s">
      <details class="faq-item">
        <summary>Do I need a developer or a theme edit?<span class="faq-ic"></span></summary>
        <p>No. Brix installs as a Shopify app embed, so you turn the widgets on from the theme editor the same way you would any other app block. Nothing is written into your theme files, and removing the app removes the widgets.</p>
      </details>
      <details class="faq-item">
        <summary>Will it work with my theme?<span class="faq-ic"></span></summary>
        <p>Brix works with the cart your theme already has and takes its styling cues from the Cart Editor, where you set the colours, copy and layout with a live preview beside you. If something looks off, the preview shows it before your shoppers do.</p>
      </details>
      <details class="faq-item">
        <summary>What if I only get a handful of orders a month?<span class="faq-ic"></span></summary>
        <p>Then start on Free, which covers 50 orders a month and stays free forever. There is no card to enter and no trial to remember to cancel. Move up when the order count makes the case for you.</p>
      </details>
      <details class="faq-item">
        <summary>Do I have to pay to find out if it works?<span class="faq-ic"></span></summary>
        <p>No. Free includes the analytics, so you can watch the revenue and AOV numbers move before you spend anything. Starter and Pro add a 14-day free trial on top of that.</p>
      </details>
      <details class="faq-item">
        <summary>How do I cancel?<span class="faq-ic"></span></summary>
        <p>From your Shopify admin, like any other app. Billing runs through Shopify Billing, so there is no separate account to close and no card of ours to remove.</p>
      </details>
    </div>
  </div>
</section>

<!-- ============ FINAL CTA ============ -->
<section class="cta-final" id="install">
  <div class="cta-gradient" aria-hidden="true"></div>
  <div class="container cta-in">
    <h2 class="reveal">Your next visitor is already on the way.</h2>
    <p class="reveal" style="--d:.08s">Install Brix free and let the cart ask for the second item. It takes a few minutes and costs nothing to find out.</p>
    <a class="btn btn-white btn-lg reveal" style="--d:.16s" href="<?= e(SHOPIFY_APP_URL) ?>" target="_blank" rel="noopener" id="ctaInstall">Install on Shopify for free</a>
    <p class="cta-note reveal" style="--d:.24s">Free plan available &middot; No credit card &middot; Cancel from your Shopify admin</p>
  </div>
</section>

<!-- Follows the visitor down the page on a phone, where most ad traffic
     lands and the hero button is long gone. Revealed by js/landing.js. -->
<div class="lp-sticky" id="lpSticky" hidden>
  <div class="lp-sticky-in">
    <span class="lp-sticky-copy"><b>Brix</b><small>Free plan &middot; no credit card</small></span>
    <a class="btn btn-primary" href="<?= e(SHOPIFY_APP_URL) ?>" target="_blank" rel="noopener">Install free</a>
  </div>
</div>

<?php require BRIX_INCLUDES . '/footer.php'; ?>
