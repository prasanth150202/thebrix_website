<?php
/**
 * /why-brix: the explainer landing page.
 *
 * For traffic that has heard the name and does not yet know what the
 * thing is. Where /cart-that-sells argues and /cart-review just asks,
 * this one explains first and asks second: what Brix is, what actually
 * changes in the cart, then the form, then the objections that stop
 * people filling it in.
 *
 * Four sections and nothing else. Every one of them is a component the
 * site already ships - the homepage's split hero and its live cart
 * demo, the before/after cart pair also from the homepage, the lead
 * form the other three landing pages run, and the FAQ list from
 * /pricing - so this page added no CSS and no JS.
 *
 * Unlisted, like the other three: nothing links here, it is absent from
 * sitemap.php, and robots.txt stays silent rather than naming the path.
 * The robots directive below is what keeps it out of search, and
 * header.php mirrors it as an X-Robots-Tag header for anything that
 * fetches the page without parsing the HTML.
 *
 * Worth being straight about the limit, as the others are: unlisted is
 * not authenticated. Anyone holding the link can open it or pass it on.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once BRIX_INCLUDES . '/lead-form.php';

/* Before a byte of HTML: the CSRF token is rendered inside the form,
   long past the point where a session cookie can still be sent. */
brix_session_start();

/* Same table and same admin screen as the other three, tagged
   'why-brix' so this page's leads can be told apart from theirs. */
$lead = brix_lead_handle('why-brix', [
    'orders' => 'Monthly orders',
]);

$page_title       = 'Why Brix: what changes when your Shopify cart starts selling';
$page_description = 'What Brix is, what it changes in your cart, and what it is worth. See the before and after, then tell us about your store.';
$page_canonical   = 'why-brix';
$page_robots      = 'noindex, nofollow, noarchive, nosnippet, noimageindex';
$page_chrome      = 'minimal';

require BRIX_INCLUDES . '/header.php';
?>

<!-- ============ 1 · WHY BRIX ============

     A split hero rather than the centred .page-hero the other landing
     pages use: centred, the copy left a wide empty band down both
     sides, and the one thing worth showing here is the product doing
     the thing the copy is describing.

     The right column is the homepage cart demo, markup unchanged. It
     needs no wiring: js/main.js binds the loop to #heroCart wherever it
     finds it, .hero-copy .reveal and .hero-visual are already the two
     halves of the GSAP hero entrance, and the chip drift is triggered
     off .hero - which is why this section carries that class and not
     .page-hero. Still no CSS of its own. -->
<section class="hero">
  <div class="hero-glow" aria-hidden="true"></div>
  <div class="container hero-grid">
    <div class="hero-copy">
      <p class="eyebrow reveal">Why Brix</p>
      <h1 class="reveal" style="--d:.06s">Your cart is the last thing they see. <em>Make it earn its place.</em></h1>
      <p class="hero-sub reveal" style="--d:.12s">Brix is not just a Shopify cart drawer. It is an AI-powered AOV platform that works out what to offer, where to show it, and when to stay quiet, inside the cart your shoppers have already opened.</p>
      <p class="hero-sub reveal" style="--d:.16s">Rewards progress bars, Frequently Bought Together, coupon sliders and AI upsell suggestions, in one app, with no theme code to write. You have already paid to get the shopper this far. This is the cheapest revenue left on the table.</p>

      <div class="wb-cta reveal" style="--d:.22s">
        <a class="btn btn-primary btn-lg" href="<?= e(SHOPIFY_APP_URL) ?>" target="_blank" rel="noopener">Install free on Shopify</a>
        <p class="wb-cta-note">Free plan, free forever &middot; No credit card &middot; Live in a few minutes</p>
      </div>
    </div>

    <!-- The cart, doing it: items in, tiers unlocking, gift added. -->
    <div class="hero-visual reveal" style="--d:.2s">
      <div class="cart-mock" id="heroCart">
        <div class="cm-head">
          <span class="cm-title">Your cart</span>
          <span class="cm-badge" id="cmCount">1 item</span>
        </div>
        <div class="cm-goal">
          <p class="cm-msg" id="cmMsg">You’re <b>$27.00</b> away from <b>free shipping</b></p>
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
            <span>Free shipping · $75</span>
            <span>Free gift · $120</span>
          </div>
        </div>
        <ul class="cm-items" id="cmItems">
          <li class="cm-item is-in">
            <span class="cm-thumb th-a" aria-hidden="true"></span>
            <span class="cm-item-info"><b>Alpine hoodie</b><small>Moss · M</small></span>
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
          <button class="cm-add" id="cmAdd" tabindex="-1">+ Add · $34</button>
        </div>
        <div class="cm-foot">
          <span>Subtotal</span>
          <b id="cmTotal">$48.00</b>
        </div>
        <button class="cm-checkout" tabindex="-1">Checkout</button>
        <canvas class="confetti-canvas" id="cmConfetti" aria-hidden="true"></canvas>
      </div>
      <div class="hero-chip hero-chip-1" aria-hidden="true">+32% AOV this month</div>
      <div class="hero-chip hero-chip-2" aria-hidden="true">Brix AI · 3 changes live</div>
    </div>
  </div>
</section>

<!-- ============ 2 · BEFORE AND AFTER ============

     The cart pair from the homepage, unchanged. It is the clearest
     thing on the site: two carts side by side, the same shopper, and
     the difference between them is the whole product.

     container-narrow, because .ba-pict caps itself at 500px: on the
     homepage it sits in one column of a split and fills it, and left
     in a full-width container here it would float in the middle of a
     very wide empty band. -->
<section class="section section-soft">
  <div class="container container-narrow">
    <div class="section-head reveal">
      <p class="eyebrow">Before and after</p>
      <h2>The same shopper. A very different cart.</h2>
      <p class="section-sub">Nothing about the traffic changes. What changes is what the cart does with it.</p>
    </div>

    <div class="ba-pict reveal" style="--d:.1s">
      <figure class="bp is-before">
        <figcaption class="bp-tag">Before Brix</figcaption>
        <div class="bp-card">
          <div class="bp-hd"><span class="bp-hd-title">Your cart</span><span class="bp-hd-count">1 item</span></div>
          <div class="bp-line">
            <span class="cm-thumb th-a" aria-hidden="true"></span>
            <span class="bp-info"><b>Alpine hoodie</b><small>Moss &middot; M</small></span>
            <span class="bp-price">$48</span>
          </div>
          <p class="bp-empty">No upsells &middot; no rewards &middot; offers missed</p>
          <div class="bp-foot"><span>Subtotal</span><b>$48.00</b></div>
          <span class="bp-checkout">Checkout</span>
        </div>
      </figure>

      <span class="ba-pict-arrow" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
      </span>

      <figure class="bp is-after">
        <figcaption class="bp-tag bp-tag-after">After Brix</figcaption>
        <div class="bp-card bp-card-smart">
          <div class="bp-hd"><span class="bp-hd-title">Your cart</span><span class="bp-hd-count bp-hd-count-on">3 items</span></div>
          <div class="bp-reward">
            <p class="bp-reward-msg"><b>Free gift unlocked!</b> Sherpa socks added</p>
            <div class="bp-track"><span class="bp-fill"></span></div>
          </div>
          <div class="bp-line">
            <span class="cm-thumb th-a" aria-hidden="true"></span>
            <span class="bp-info"><b>Alpine hoodie</b><small>Moss &middot; M</small></span>
            <span class="bp-price">$48</span>
          </div>
          <div class="bp-line">
            <span class="cm-thumb th-b" aria-hidden="true"></span>
            <span class="bp-info"><b>Trail beanie</b><small>Charcoal</small></span>
            <span class="bp-price">$34</span>
          </div>
          <div class="bp-upsell">
            <span class="cm-thumb th-c" aria-hidden="true"></span>
            <span class="bp-info"><b>Camp mug set</b><small>Closes the gap to your gift</small></span>
            <span class="bp-add">+ Add</span>
          </div>
          <div class="bp-coupons"><span class="bp-coupon bp-coupon-on">SAVE10</span><span class="bp-coupon">FREESHIP</span></div>
          <div class="bp-foot"><span>Subtotal</span><b class="bp-foot-up">$124.00</b></div>
          <span class="bp-checkout bp-checkout-on">Checkout</span>
        </div>
      </figure>
    </div>

    <p class="ba-pict-cap">From passive cart to smart cart: <b>+32% AOV</b></p>
  </div>
</section>

<!-- ============ 3 · TELL US ABOUT YOUR STORE ============

     Plain .section, not section-soft: the band above is already soft
     and a second one would read as a single undivided stretch.

     Same form, same table and same admin row as the other three
     landing pages, tagged 'why-brix'. -->
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
            <label for="wb-name">Your name</label>
            <input type="text" id="wb-name" name="name" required autocomplete="name"
                   value="<?= e($lead['values']['name']) ?>">
          </div>

          <div class="dm-field">
            <label for="wb-email">Email</label>
            <input type="email" id="wb-email" name="email" required autocomplete="email"
                   placeholder="you@store.com" value="<?= e($lead['values']['email']) ?>">
          </div>

          <div class="dm-field">
            <label for="wb-store">Store URL <span>optional</span></label>
            <input type="text" id="wb-store" name="store_url" autocomplete="url"
                   placeholder="yourstore.com" value="<?= e($lead['values']['store_url']) ?>">
          </div>

          <div class="dm-field">
            <label for="wb-orders">Orders a month <span>optional</span></label>
            <select id="wb-orders" name="orders">
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

<!-- ============ 4 · FAQ ============ -->
<section class="section section-soft">
  <div class="container container-narrow">
    <div class="section-head reveal">
      <p class="eyebrow">Before you install</p>
      <h2>The questions merchants ask first</h2>
    </div>
    <div class="faq-list reveal" style="--d:.08s">
      <details class="faq-item">
        <summary>What does Brix actually do?<span class="faq-ic"></span></summary>
        <p>It replaces the silent cart drawer your theme ships with one that asks for the second item. Reward tiers that fill as the basket grows, Frequently Bought Together suggestions, coupon codes applied in one tap, and AI that decides which of those to show a given shopper. All of it inside the cart, so nobody is sent to another page to accept an offer.</p>
      </details>
      <details class="faq-item">
        <summary>Do I need a developer or a theme edit?<span class="faq-ic"></span></summary>
        <p>No. Brix installs as a Shopify app embed, so you turn the widgets on from the theme editor the same way you would any other app block. Nothing is written into your theme files, and removing the app removes the widgets.</p>
      </details>
      <details class="faq-item">
        <summary>Will it work with my theme?<span class="faq-ic"></span></summary>
        <p>Brix works with the cart your theme already has and takes its styling cues from the Cart Editor, where you set the colours, copy and layout with a live preview beside you. If something looks off, the preview shows it before your shoppers do.</p>
      </details>
      <details class="faq-item">
        <summary>How long before I see anything?<span class="faq-ic"></span></summary>
        <p>Setup is minutes, not days: install, pick a reward tier, turn it on. After that it depends on your traffic. Stores with steady order volume usually have enough data to read the analytics inside a week.</p>
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

<?php require BRIX_INCLUDES . '/footer.php'; ?>
