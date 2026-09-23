<?php
/**
 * /book-a-demo: focused demo-booking funnel, not a homepage mirror.
 *
 * Four sections only, minimal chrome (no nav, no full footer) so the
 * only ways off the page are the two CTAs, both pointing at the same
 * form:
 *
 *   1. Hero: the pitch, left, and a real recreation of the Brix cart
 *      drawer, right, driven by one synchronized timeline in
 *      js/hero-ai-demo.js. See the CSS block above .rc-mock in
 *      css/styles.css for how it stays exactly the same size while it
 *      plays.
 *   2. AOV calculator, asked one question at a time (currency, AOV,
 *      monthly revenue, ad spend) rather than as a single form. See
 *      js/wizard.js for the step logic and the math.
 *   3. "Get in touch": the lead form. Reaching the calculator's result
 *      step fills its hidden fields with the visitor's own numbers, so
 *      whoever answers already has the context.
 *   4. A short "why Brix" recap with a compact feature list, then the
 *      page ends. No pricing, no testimonials, no case studies here:
 *      this page has one job.
 *
 * Unindexed for now, same reasoning as the other campaign landing pages:
 * flip $page_robots once this has a real destination (an ad, an email).
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once BRIX_INCLUDES . '/lead-form.php';

/* Before a byte of HTML: the CSRF token is rendered inside the form,
   long past the point where a session cookie can still be sent. */
brix_session_start();

$lead = brix_lead_handle('book-a-demo', [
    'currency'             => 'Currency',
    'aov'                  => 'Average order value',
    'monthly_revenue'      => 'Monthly revenue',
    'ad_spend'             => 'Monthly ad spend',
    'est_missing_revenue'  => 'Estimated missing revenue',
]);

$page_title       = 'Book a Free Brix Demo: Shopify Cart Upsell App';
$page_description = 'See what a Shopify cart upsell app could add to your store, then book a free demo with the team that built Brix.';
$page_canonical   = 'book-a-demo';
$page_robots      = 'noindex, nofollow, noarchive, nosnippet, noimageindex';
$page_chrome      = 'minimal';
$page_scripts     = '<script src="/js/hero-ai-demo.js?v=' . ASSET_HERO_AI_VER . '"></script>'
                    . '<script src="/js/wizard.js?v=' . ASSET_WIZARD_VER . '"></script>';

require BRIX_INCLUDES . '/header.php';
?>

<!-- ============ HERO ============ -->
<section class="hero lp-hero">
  <div class="hero-glow" aria-hidden="true"></div>
  <div class="container hero-grid hero-grid-top">
    <div class="hero-copy">
      <a class="hero-badge reveal" href="<?= e(SHOPIFY_APP_URL) ?>" target="_blank" rel="noopener" style="--d:.02s">
        <span class="hero-badge-stars">★★★★★</span> <b>4.9</b> on the Shopify App Store
      </a>
      <p class="eyebrow reveal" style="--d:.04s">Introducing Brix AI</p>
      <h1 class="reveal" style="--d:.08s">Increase your AOV in seconds with Brix AI</h1>
      <p class="hero-sub reveal" style="--d:.16s">Tell Brix AI what you want: more upsells, a better reward tier, a higher AOV. It plans the changes, ships them straight to your cart, and reports back. No developer, no guesswork.</p>
      <div class="hero-ctas reveal" style="--d:.24s">
        <a class="btn btn-primary btn-lg" href="#get-in-touch">Book a free demo</a>
      </div>
      <p class="hero-note reveal" style="--d:.32s">Free plan available · No credit card · Changes go live in seconds</p>
    </div>

    <!-- Signature: type a goal into Brix AI, watch it act on the cart.
         The cart itself is a recreation of the real Brix Cart Editor
         preview, not an illustration of one: the .ui-mock browser
         chrome already used on /how-to frames it, and .rc-* below
         rebuilds the shipping band, timer, rewards bar, coupon cards,
         item rows and checkout button it actually renders. Single
         synchronized timeline in js/hero-ai-demo.js, guarded on
         #aiDemo so it never touches the unrelated #heroCart / #aiChat
         demos main.js already drives on other pages. -->
    <div class="hero-visual reveal" style="--d:.2s">
      <div class="ai-demo" id="aiDemo">
        <div class="ai-demo-card">
          <div class="ai-demo-head">
            <span class="ai-demo-avatar" aria-hidden="true">
              <img src="assets/brix-mark-light.png" alt="" width="16" height="16">
            </span>
            <b>Brix AI</b>
            <span class="ai-demo-live" aria-hidden="true"></span>
          </div>
          <div class="ai-demo-line">
            <span id="aiPromptText"></span><span class="ai-demo-caret" id="aiPromptCaret"></span>
          </div>
          <div class="ai-demo-status">
            <div class="ai-demo-typing" id="aiTyping"><span></span><span></span><span></span></div>
            <p class="ai-demo-reply" id="aiReply">Done. I turned on your rewards bar and added a Frequently Bought Together pick.</p>
          </div>
        </div>

        <span class="ba-pict-arrow ai-demo-arrow" aria-hidden="true">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M6 13l6 6 6-6"/></svg>
        </span>

        <div class="ui-mock rc-mock">
          <div class="ui-bar"><i></i><i></i><i></i><span>yourstore.com</span></div>

          <!-- Both items already in the cart from the start, and the
               SAVE20 coupon is decorative throughout (a real feature,
               not part of this story). Everything Brix AI touches
               changes colour, fill or text only, never size. -->
          <div class="rc-cart" id="aiCart">
            <div class="rc-head">
              <b>Your Cart (<span id="aiCmCount">2</span>)</b>
              <span class="rc-x" aria-hidden="true">&times;</span>
            </div>
            <p class="rc-band">Free shipping on orders over $100!</p>
            <p class="rc-timer">Offer expires in <b>14:42</b> &middot; Use code <b>FLASH20</b></p>

            <div class="rc-body">
              <div class="rc-goal">
                <p class="rc-goal-msg" id="aiCmMsg">You’re <b>$18.00</b> away from unlocking <b>Free Shipping</b>!</p>
                <div class="rc-track">
                  <div class="rc-fill" id="aiCmFill" style="width:55%"></div>
                  <div class="rc-node" id="aiNodeShip" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="12" height="12" fill="currentColor"><path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg>
                  </div>
                </div>
                <div class="rc-tiers"><span>$100</span><span>Free Shipping</span></div>
              </div>

              <p class="rc-coupon-h">Apply Coupon</p>
              <div class="rc-coupons">
                <div class="rc-coupon rc-coupon-a">
                  <span class="rc-coupon-ic" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                  </span>
                  <b>SAVE20</b><small>20% off your order</small>
                  <button class="rc-coupon-btn" type="button" tabindex="-1">Apply</button>
                </div>
                <div class="rc-coupon rc-coupon-b" id="aiCmUpsell">
                  <span class="rc-coupon-ic" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="currentColor"><path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg>
                  </span>
                  <b>FBT PICK</b><small>Camp mug set &middot; $42</small>
                  <button class="rc-coupon-btn" id="aiCmAdd" type="button" tabindex="-1">Add</button>
                </div>
              </div>

              <div class="rc-items-h"><span>Items included</span><span id="aiItemsCount">2 ITEMS</span></div>
              <ul class="rc-items">
                <li class="rc-item">
                  <span class="cm-thumb th-a" aria-hidden="true"></span>
                  <span class="rc-item-info"><b>Alpine hoodie</b><small>$48.00 (1 &times; $48.00)</small></span>
                  <span class="rc-qty" aria-hidden="true"><button type="button" tabindex="-1">&minus;</button><span>1</span><button type="button" tabindex="-1">+</button></span>
                </li>
                <li class="rc-item">
                  <span class="cm-thumb th-b" aria-hidden="true"></span>
                  <span class="rc-item-info"><b>Trail beanie</b><small>$34.00 (1 &times; $34.00)</small></span>
                  <span class="rc-qty" aria-hidden="true"><button type="button" tabindex="-1">&minus;</button><span>1</span><button type="button" tabindex="-1">+</button></span>
                </li>
              </ul>

              <div class="rc-foot">
                <div class="rc-row"><span>Subtotal</span><b id="aiCmSubtotal">$82.00</b></div>
                <div class="rc-row rc-row-total"><span>Total</span><b id="aiCmTotal">$82.00</b></div>
              </div>
              <button class="rc-checkout" type="button" tabindex="-1">Checkout Now <span aria-hidden="true">&rarr;</span></button>
              <p class="rc-fine">Shipping and taxes calculated at checkout</p>
            </div>
            <canvas class="confetti-canvas" id="aiCmConfetti" aria-hidden="true"></canvas>
          </div>
        </div>

        <div class="ai-demo-result" id="aiResult">
          <span aria-hidden="true">✓</span> AOV up <b>+32%</b> this month
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ AOV CALCULATOR (STEP BY STEP) ============ -->
<section class="section section-soft">
  <div class="container container-narrow">
    <div class="section-head reveal">
      <p class="eyebrow">Free calculator</p>
      <h2>What is your cart leaving on the table?</h2>
      <p class="section-sub">Answer four quick questions and see the extra revenue Brix could add to your store.</p>
    </div>

    <div class="wiz-card reveal" id="aovWizard">
      <div class="wiz-progress"><div class="wiz-progress-fill" id="wizProgressFill"></div></div>
      <span class="wiz-count" id="wizCount">Question 1 of 4</span>

      <div class="wiz-step" data-step="1">
        <p class="wiz-q">Which currency do you sell in?</p>
        <select class="calc-select" id="wizCurrency">
          <option value="$" selected>USD – US Dollar</option>
          <option value="C$">CAD – Canadian Dollar</option>
          <option value="&#8364;">EUR – Euro</option>
          <option value="&#163;">GBP – British Pound</option>
          <option value="A$">AUD – Australian Dollar</option>
          <option value="S$">SGD – Singapore Dollar</option>
          <option value="&#8377;">INR – Indian Rupee</option>
        </select>
      </div>

      <div class="wiz-step" data-step="2">
        <p class="wiz-q">What is your average order value?</p>
        <div class="calc-input"><span class="calc-currency-sym">$</span><input type="number" id="wizAov" inputmode="decimal" min="0" step="0.01" placeholder="65"></div>
      </div>

      <div class="wiz-step" data-step="3">
        <p class="wiz-q">What is your monthly revenue?</p>
        <div class="calc-input"><span class="calc-currency-sym">$</span><input type="number" id="wizRevenue" inputmode="decimal" min="0" step="1" placeholder="40000"></div>
      </div>

      <div class="wiz-step" data-step="4">
        <p class="wiz-q">What do you spend on ads each month? <span>optional</span></p>
        <div class="calc-input"><span class="calc-currency-sym">$</span><input type="number" id="wizAdSpend" inputmode="decimal" min="0" step="1" placeholder="6000"></div>
      </div>

      <div class="wiz-step" data-step="5">
        <p class="calc-result-label">You could be missing</p>
        <p class="calc-result-fig"><span id="wizMissing">$0</span><small>/month</small></p>
        <div class="calc-result-row"><span>New AOV</span><b id="wizNewAov">$0</b></div>
        <div class="calc-result-row"><span>New monthly revenue</span><b id="wizNewRevenue">$0</b></div>
        <p class="calc-result-note" id="wizAdNote"></p>
        <a class="btn btn-primary btn-lg wiz-result-cta" href="#get-in-touch">Book a free demo</a>
      </div>

      <p class="wiz-err" id="wizErr"></p>
      <div class="wiz-nav">
        <button type="button" class="btn btn-ghost" id="wizBack" hidden>Back</button>
        <button type="button" class="btn btn-primary" id="wizNext">Next</button>
      </div>
    </div>
  </div>
</section>

<!-- ============ GET IN TOUCH ============ -->
<section class="section dm-form-sec" id="get-in-touch">
  <div class="container">
    <div class="dm-form-grid">
      <div class="dm-form-copy reveal">
        <p class="eyebrow">Get in touch</p>
        <h2>Book your free demo</h2>
        <p class="dm-form-sub">Tell us a little about your store. We will come back within one business day with times that work and what we would turn on first.</p>
        <ul class="dm-form-points">
          <li>A real look at your cart, not a sales pitch</li>
          <li>Run by the team that built Brix</li>
          <li>No obligation and nothing to cancel</li>
        </ul>
      </div>

      <?php /* No .reveal on the panel: the form must never sit at
               opacity 0 because a script did not arrive. */ ?>
      <div class="dm-form-panel">
<?php if ($lead['sent']): ?>
        <div class="dm-form-done">
          <span class="dm-form-tick" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          </span>
          <h3>Got it. Talk soon.</h3>
          <p>We have your details and will come back within one business day with times for your demo.</p>
          <a class="btn btn-primary" href="<?= e(SHOPIFY_APP_URL) ?>" target="_blank" rel="noopener">Install free on Shopify</a>
        </div>
<?php else: ?>
        <form class="dm-form" method="post" action="#get-in-touch" novalidate>
          <?= csrf_field() ?>
          <input type="hidden" name="t" value="<?= time() ?>">
          <?php /* Honeypot: never shown, never filled by a person. */ ?>
          <div class="dm-hp" aria-hidden="true">
            <label for="bd-website">Website</label>
            <input type="text" id="bd-website" name="website" tabindex="-1" autocomplete="off">
          </div>

          <?php /* Filled by js/wizard.js once the calculator reaches its
                   result step. Blank and harmless if the visitor never
                   used the calculator. */ ?>
          <input type="hidden" name="currency" id="hfCurrency">
          <input type="hidden" name="aov" id="hfAov">
          <input type="hidden" name="monthly_revenue" id="hfRevenue">
          <input type="hidden" name="ad_spend" id="hfAdSpend">
          <input type="hidden" name="est_missing_revenue" id="hfMissing">

<?php if ($lead['errors']): ?>
          <div class="dm-form-errs" role="alert">
<?php foreach ($lead['errors'] as $err): ?>
            <p><?= e($err) ?></p>
<?php endforeach; ?>
          </div>
<?php endif; ?>

          <div class="dm-field">
            <label for="bd-name">Your name</label>
            <input type="text" id="bd-name" name="name" required autocomplete="name"
                   value="<?= e($lead['values']['name']) ?>">
          </div>

          <div class="dm-field">
            <label for="bd-email">Email</label>
            <input type="email" id="bd-email" name="email" required autocomplete="email"
                   placeholder="you@store.com" value="<?= e($lead['values']['email']) ?>">
          </div>

          <div class="dm-field">
            <label for="bd-store">Store URL <span>optional</span></label>
            <input type="text" id="bd-store" name="store_url" autocomplete="url"
                   placeholder="yourstore.com" value="<?= e($lead['values']['store_url']) ?>">
          </div>

          <button class="btn btn-primary btn-lg dm-form-send" type="submit">Book my free demo</button>
          <p class="dm-form-fine">We’ll only use this to reply. No list, no sequence.</p>
        </form>
<?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- ============ WHY BRIX + FEATURES ============ -->
<section class="section section-soft">
  <div class="container container-narrow">
    <div class="section-head reveal">
      <p class="eyebrow">Why Brix</p>
      <h2>Everything your cart needs to sell more</h2>
      <p class="section-sub">One app, installed in minutes, working on every cart your store already has.</p>
    </div>
    <div class="waov-chips">
      <span class="waov-chip"><span class="waov-chip-ic" aria-hidden="true">✓</span>AI cart upsells &amp; Frequently Bought Together</span>
      <span class="waov-chip"><span class="waov-chip-ic" aria-hidden="true">✓</span>Rewards progress bar</span>
      <span class="waov-chip"><span class="waov-chip-ic" aria-hidden="true">✓</span>Coupon slider</span>
      <span class="waov-chip"><span class="waov-chip-ic" aria-hidden="true">✓</span>Bundle builder</span>
      <span class="waov-chip"><span class="waov-chip-ic" aria-hidden="true">✓</span>Real-time AI analytics</span>
    </div>
    <div class="lp-cta-row reveal" style="--d:.06s">
      <a class="btn btn-primary btn-lg" href="#get-in-touch">Book a free demo</a>
      <span class="lp-cta-note">Free plan available · No credit card</span>
    </div>
  </div>
</section>

<?php require BRIX_INCLUDES . '/footer.php'; ?>
