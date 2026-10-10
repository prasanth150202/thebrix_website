<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once BRIX_INCLUDES . '/posts.php';

$page_title       = 'Brix Features: AI Cart Upsell Tools for Shopify';
$page_description = "Explore Brix's Shopify cart features: AI upsells, Frequently Bought Together, Bundle Builder, quantity Packs, Cash on Delivery checkout, coupon sliders, and reward progress bars.";
$page_canonical   = 'features';
$page_nav         = 'features';
$footer_col3      = 'case-studies';

require BRIX_INCLUDES . '/header.php';
?>

<section class="page-hero">
  <div class="hero-glow" aria-hidden="true"></div>
  <div class="container">
    <p class="eyebrow reveal">Features</p>
    <h1 class="reveal" style="--d:.06s">Every nudge, <em>explained</em></h1>
    <p class="hero-sub reveal" style="--d:.12s" >Eight tools that each lift your average order value, designed to work together and get sharper as Brix AI learns your store.</p>
  </div>
</section>

<!-- 1. Smart Cart Editor -->
<section class="f-section" id="cart-editor">
  <div class="container dd-grid">
    <div class="dd-copy">
      <p class="eyebrow reveal">01 · Smart Cart Editor</p>
      <h2 class="reveal">A cart drawer that earns its keep</h2>
      <p class="reveal">Replace your theme’s silent cart with the Shopify upsell app built to lift AOV: a cart upsell Shopify merchants actually feel in their numbers.</p>
      <div class="dd-step reveal" data-step="1">
        <h3>Reward tiers shoppers can see</h3>
        <p>Free shipping at $75, a gift at $120, 10% off at $180. The bar fills live and matches your theme automatically. It’s the cart progress bar Shopify brands rely on: a rewards bar Shopify app that turns checkout into a gamified cart Shopify flow, bringing true Shopify cart gamification to your store.</p>
      </div>
      <div class="dd-step reveal" data-step="2">
        <h3>Upsells that fit the cart</h3>
        <p>As a cart upsell app Shopify brands trust, Brix suggests the product most likely to close the gap to the next tier.</p>
      </div>
      <div class="dd-step reveal" data-step="3">
        <h3>Coupons inside the cart</h3>
        <p>A swipeable coupon slider applies deals in one tap, with no code hunting.</p>
      </div>
    </div>
    <div class="dd-visual">
      <div class="dd-sticky reveal">
        <div class="cart-mock cart-mock-static">
          <div class="cm-head"><span class="cm-title">Your cart</span><span class="cm-badge">2 items</span></div>
          <div class="cm-goal">
            <p class="cm-msg"><b>Free shipping unlocked!</b> $38.00 to a free gift</p>
            <div class="cm-track">
              <div class="cm-fill" style="width:63%"></div>
              <div class="cm-node is-unlocked" style="left:57.7%"><svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg></div>
              <div class="cm-node" style="left:92.3%"><svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M20 6h-2.18c.11-.31.18-.65.18-1 0-1.66-1.34-3-3-3-1.05 0-1.96.54-2.5 1.35l-.5.67-.5-.68C10.96 2.54 10.05 2 9 2 7.34 2 6 3.34 6 5c0 .35.07.69.18 1H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2z"/></svg></div>
            </div>
            <div class="cm-tiers"><span>Free shipping · $75</span><span>Free gift · $120</span></div>
          </div>
          <ul class="cm-items">
            <li class="cm-item is-in"><span class="cm-thumb th-a"></span><span class="cm-item-info"><b>Alpine hoodie</b><small>Moss · M</small></span><span class="cm-price">$48.00</span></li>
            <li class="cm-item is-in"><span class="cm-thumb th-b"></span><span class="cm-item-info"><b>Trail beanie</b><small>Charcoal</small></span><span class="cm-price">$34.00</span></li>
          </ul>
          <div class="cm-upsell is-on"><span class="cm-thumb th-c"></span><span class="cm-item-info"><b>Camp mug set</b><small>Closes the gap to your gift</small></span><button class="cm-add" tabindex="-1">+ Add · $42</button></div>
          <div class="cm-coupon-row">
            <span class="mini-coupon mc-active">SAVE10</span>
            <span class="mini-coupon">BUNDLE15</span>
          </div>
          <div class="cm-foot"><span>Subtotal</span><b>$82.00</b></div>
          <button class="cm-checkout" tabindex="-1">Checkout</button>
        </div>
        <div class="dd-callout dd-callout-1" data-for="1">Reward tiers</div>
        <div class="dd-callout dd-callout-2" data-for="2">Smart upsell</div>
        <div class="dd-callout dd-callout-3" data-for="3">Tap-to-apply coupons</div>
      </div>
    </div>
  </div>
</section>

<!-- 2. Coupon sliders -->
<section class="f-section" id="coupon-slider">
  <div class="container f-grid f-grid-flip">
    <div class="f-copy">
      <p class="eyebrow reveal">02 · Coupon Sliders</p>
      <h2 class="reveal">Deals shoppers don’t have to hunt for</h2>
      <p class="reveal">A swipeable row of coupon cards on your product pages and in the cart. One tap applies the code, the discount shows up instantly, and nobody leaves your store to search “brix coupon” in another tab.</p>
      <ul class="f-points reveal">
        <li><b>Tap to apply</b>: no copy-paste, no code field friction at checkout.</li>
        <li><b>Targeted offers</b>: different coupons by product, collection or customer tag.</li>
        <li><b>Urgency built in</b>: optional countdown timers and “almost gone” stock hints.</li>
      </ul>
      <p class="reveal" style="--d:.06s">No more missed discounts, no more copy-paste confusion, and no more shoppers leaving checkout to search for offers. It’s the coupon slider Shopify merchants use to keep every Shopify discount code in cart, so a Shopify coupon code in cart is never more than one tap away.</p>
    </div>
    <div class="f-visual reveal" style="--d:.1s">
      <div class="pp-mock">
        <div class="pp-img"><span class="pp-fav">♡</span></div>
        <p class="pp-title">Alpine hoodie</p>
        <p class="pp-price">$48.00 <small>· 4.8★ (212)</small></p>
        <div class="pp-coupons">
          <span class="pp-coupon applied"><b>SAVE10 ✓</b>10% off applied</span>
          <span class="pp-coupon"><b>FREESHIP</b>orders $75+</span>
          <span class="pp-coupon"><b>GIFT25</b>free gift $120+</span>
        </div>
        <button class="pp-atc" tabindex="-1">Add to cart · $43.20</button>
      </div>
    </div>
  </div>
</section>

<!-- 3. FBT -->
<section class="f-section" id="fbt">
  <div class="container f-grid">
    <div class="f-copy">
      <p class="eyebrow reveal">03 · Frequently Bought Together</p>
      <h2 class="reveal">Sell the set, not the item</h2>
      <p class="reveal">Brix mines your real order history to find the products that actually sell together, not just “related products” from the same collection. One tap adds the whole set, with an optional bundle discount for taking all of it.</p>
      <ul class="f-points reveal">
        <li><b>Learned from your orders</b>: pairings update weekly as buying patterns change.</li>
        <li><b>One-tap add all</b>: the whole set lands in the cart with a single click.</li>
        <li><b>Set pricing</b>: optional “take all three” discount to close the deal.</li>
      </ul>
      <p class="reveal" style="--d:.06s">A true Frequently Bought Together Shopify solution that doubles as a Shopify cross sell app: it increases average order value, improves product discovery, and turns simple purchases into higher-value orders.</p>
    </div>
    <div class="f-visual reveal" style="--d:.1s">
      <div class="fbt-mock">
        <p class="fbt-title">Frequently bought together</p>
        <div class="fbt-row">
          <div class="fbt-prod"><span class="mini-prod th-a"></span><small>Alpine hoodie<br>$48</small></div>
          <span class="fbt-plus">+</span>
          <div class="fbt-prod"><span class="mini-prod th-b"></span><small>Trail beanie<br>$34</small></div>
          <span class="fbt-plus">+</span>
          <div class="fbt-prod"><span class="mini-prod th-c"></span><small>Camp mug set<br>$42</small></div>
        </div>
        <div class="fbt-total"><span>Total for 3 items</span><span><span class="was">$124</span><b>$111.60</b></span></div>
        <button class="fbt-add" tabindex="-1">Add all 3 to cart</button>
      </div>
    </div>
  </div>
</section>

<!-- 4. Bundle Builder -->
<section class="f-section" id="bundles">
  <div class="container f-grid f-grid-flip">
    <div class="f-copy">
      <p class="eyebrow reveal">04 · Bundle Builder</p>
      <h2 class="reveal">Combo deals without the discount-code maze</h2>
      <p class="reveal">Build offers like “buy 3 from the Winter collection, save $100” in a visual editor. Brix generates the combo page, the cart logic and the discount, and keeps them in sync when your catalog changes.</p>
      <ul class="f-points reveal">
        <li><b>Collection rules</b>: quantity breaks, fixed-amount and percentage discounts.</li>
        <li><b>Mix-and-match pages</b>: a dedicated page where shoppers build their own kit.</li>
        <li><b>Tiered kits</b>: starter / plus / complete versions priced to pull people up.</li>
        <li><b>Inventory aware</b>: out-of-stock items drop out of bundles automatically.</li>
      </ul>
    </div>
    <div class="f-visual reveal" style="--d:.1s">
      <div class="builder-mock">
        <div class="bm-head">Bundle Builder <span class="bm-status">● Live</span></div>
        <div class="bm-body">
          <div class="bm-col">
            <p class="bm-label">Your collections</p>
            <span class="bm-chip">Hoodies</span>
            <span class="bm-chip">Candles</span>
            <span class="bm-chip bm-chip-drag">Winter <em>⠿</em></span>
            <span class="bm-chip">Serums</span>
          </div>
          <div class="bm-col">
            <p class="bm-label">Bundle rule</p>
            <div class="bm-drop">
              <span class="bm-chip bm-chip-placed">Winter</span>
              <p class="bm-rule">Buy <b>3</b> items → save <b>$100</b></p>
            </div>
            <div class="bm-toggle-row"><span class="bm-toggle"><i></i></span> Combo page: on</div>
          </div>
        </div>
        <div class="bm-preview">
          <p class="bm-label">Live preview</p>
          <div class="bm-prods">
            <span class="mini-prod th-a"></span>
            <span class="mini-prod th-b"></span>
            <span class="mini-prod th-c"></span>
          </div>
          <p class="bm-price"><s>$297</s> <b>$197</b> <span class="bm-save-tag">You save $100</span></p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- 5. Packs -->
<section class="f-section" id="packs">
  <div class="container f-grid">
    <div class="f-copy">
      <p class="eyebrow reveal">05 · Brix Packs</p>
      <h2 class="reveal">“Buy 2, save 5%” where shoppers actually decide</h2>
      <p class="reveal">Show quantity offers as simple cards right on the product page. A shopper picks a pack, chooses the size or colour for each item, and adds it to the cart. The saving is applied at checkout on its own, so there’s no code to remember and nothing to type.</p>
      <ul class="f-points reveal">
        <li><b>Up to six offers per product</b>: pick the quantity, a percentage or fixed saving, and a badge like “Popular” or “Best value”.</li>
        <li><b>Mix and match</b>: shoppers can choose a different size or colour for every item in the pack, or stick with one.</li>
        <li><b>Three layouts</b>: a row of cards, a photo grid, or image rows. Colours, text and buttons are yours to style.</li>
        <li><b>Always the right price</b>: offers follow your live Shopify prices, so you never have to edit them by hand.</li>
        <li><b>Only promises what checkout delivers</b>: a pack is shown only once the discount is confirmed to work, so no one is offered a saving they won’t get.</li>
        <li><b>Try it before it’s live</b>: save a draft and preview it on your own store first.</li>
      </ul>
      <p class="reveal" style="--d:.06s">Shoppers can also hit “Buy now” to check out just the pack, without disturbing what’s already in their cart. Packs go live on the Starter and Pro plans, and on Free you can build drafts and preview them.</p>
    </div>
    <div class="f-visual reveal" style="--d:.1s">
      <div class="pk-mock">
        <p class="pk-title">Alpine hoodie <small>· choose your pack</small></p>
        <div class="pk-tiers">
          <div class="pk-tier">
            <b>Buy 1</b>
            <span class="pk-price">$48</span>
          </div>
          <div class="pk-tier is-on">
            <span class="pk-badge">Popular</span>
            <b>Buy 2</b>
            <small>Save 5%</small>
            <span class="pk-price"><s>$96</s> $91.20</span>
          </div>
          <div class="pk-tier">
            <span class="pk-badge">Best value</span>
            <b>Buy 3</b>
            <small>Save 10%</small>
            <span class="pk-price"><s>$144</s> $129.60</span>
          </div>
        </div>
        <div class="pk-slots">
          <div class="pk-slot"><span>Item 1</span><span class="pk-sel">Moss · M</span></div>
          <div class="pk-slot"><span>Item 2</span><span class="pk-sel">Charcoal · L</span></div>
        </div>
        <button class="pk-add" tabindex="-1">Add pack to cart · $91.20</button>
        <p class="pk-save">✓ You save $4.80 at checkout</p>
      </div>
    </div>
  </div>
</section>

<!-- 6. COD Checkout -->
<section class="f-section" id="cod-checkout">
  <div class="container f-grid f-grid-flip">
    <div class="f-copy">
      <p class="eyebrow reveal">06 · COD Checkout</p>
      <h2 class="reveal">Cash on Delivery, minus the drop-off</h2>
      <p class="reveal">Plenty of shoppers in India would rather pay when the parcel arrives. Brix adds a Cash on Delivery button to your cart, product pages and combo pages. A short popup takes them from phone number to placed order in a few taps. Anyone who wants to pay online carries on to your normal checkout, exactly as before.</p>
      <ul class="f-points reveal">
        <li><b>A quick, familiar flow</b>: phone number and OTP, then address, then a clear review of the total before they confirm.</li>
        <li><b>Show it where it helps</b>: switch it on in the cart drawer, on product pages or on combo pages, each one separately, and style the button to match your store.</li>
        <li><b>Fewer fake orders</b>: OTP checks, a daily order limit per phone number, blocked PIN codes, and minimum and maximum order values.</li>
        <li><b>Your rules on price</b>: add a COD fee, set shipping and a free-shipping level, and keep chosen products off COD.</li>
        <li><b>Nudge shoppers to pay online</b>: offer “Pay online” and “Cash on delivery” side by side on the product page, with an optional prepaid discount like “Save 5%”.</li>
        <li><b>Coupons and discounts still work</b>: shoppers can apply a code in the popup, and the totals always come straight from Shopify.</li>
        <li><b>Your ad numbers stay honest</b>: COD orders skip normal checkout, so Brix reports them to Google Analytics and Meta for you.</li>
      </ul>
      <p class="reveal" style="--d:.06s">Every COD order lands in Shopify tagged and ready to fulfil, and your Brix dashboard follows it from placed to shipped, delivered, paid or returned. Carts that contain a pack or a free reward gift use your normal checkout instead. COD Checkout is live on Starter and Pro, with a preview on Free.</p>
    </div>
    <div class="f-visual reveal" style="--d:.1s">
      <div class="cod-mock">
        <div class="cod-pay">
          <div class="cod-opt"><b>Pay online</b><small>Save 5% · ₹1,424</small></div>
          <div class="cod-opt is-on"><b>Cash on delivery</b><small>Pay when it arrives</small></div>
        </div>
        <div class="cod-sheet">
          <div class="cod-steps">
            <span class="is-done">✓ Phone</span>
            <span class="is-done">✓ Address</span>
            <span class="is-now">3 Review</span>
          </div>
          <ul class="cod-lines">
            <li><span>Alpine hoodie × 1</span><b>₹1,499</b></li>
            <li><span>Delivery</span><b>₹49</b></li>
            <li><span>COD fee</span><b>₹30</b></li>
            <li class="cod-total"><span>Total</span><b>₹1,578</b></li>
          </ul>
          <button class="cod-place" tabindex="-1">Place COD order</button>
          <p class="cod-foot">Secured &amp; powered by BRIX</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- 7. Analytics -->
<section class="f-section section-soft" id="analytics">
  <div class="container">
    <div class="section-head reveal">
      <p class="eyebrow">07 · Analytics &amp; AI Insights</p>
      <h2>Attribution down to the nudge</h2>
      <p class="section-sub">Most apps show you a dashboard. Brix shows you cause and effect: which tier, which upsell, which bundle earned each extra dollar, and what the data says you should change next.</p>
    </div>
    <ul class="f-points reveal" style="max-width:700px;margin:0 auto 34px;">
      <li><b>Per-feature revenue</b>: see exactly what the progress bar vs. bundles earned.</li>
      <li><b>Tier funnel</b>: how many carts reach each reward, and where they stall.</li>
      <li><b>AI insight cards</b>: concrete suggestions with projected impact, one click to apply.</li>
      <li><b>Weekly email digest</b>: your AOV story in three bullets every Monday.</li>
    </ul>
    <div class="dash-mock reveal" style="--d:.1s">
      <div class="dash-stats">
        <div class="dash-stat">
          <small>Average order value</small>
          <b>$86.40</b>
          <span class="dash-delta">▲ 32% vs. before Brix</span>
        </div>
        <div class="dash-stat">
          <small>Revenue from Brix offers</small>
          <b>$12,940</b>
          <span class="dash-delta">▲ 18% this month</span>
        </div>
        <div class="dash-stat">
          <small>Upsell attach rate</small>
          <b>24%</b>
          <span class="dash-delta">▲ 6 pts</span>
        </div>
      </div>
      <div class="dash-chart">
        <div class="dash-chart-head">
          <span>Average order value with Brix (last 6 months)</span>
        </div>
        <div class="chart-wrap" id="aovChart">
          <svg viewBox="0 0 640 240" preserveAspectRatio="none" aria-label="Line chart: average order value rising from $58 in January to $86 in June">
            <g class="chart-grid">
              <line x1="48" y1="24"  x2="628" y2="24"/>
              <line x1="48" y1="86"  x2="628" y2="86"/>
              <line x1="48" y1="148" x2="628" y2="148"/>
              <line x1="48" y1="210" x2="628" y2="210"/>
            </g>
            <g class="chart-ylabels">
              <text x="40" y="28">$90</text>
              <text x="40" y="90">$75</text>
              <text x="40" y="152">$60</text>
              <text x="40" y="214">$45</text>
            </g>
            <defs>
              <linearGradient id="aovFill2" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#7C3AED" stop-opacity=".22"/>
                <stop offset="100%" stop-color="#7C3AED" stop-opacity="0"/>
              </linearGradient>
            </defs>
            <path class="chart-area" d="M64 156.3 L170 143.9 L276 119.1 L382 98.4 L488 69.5 L594 40.5 L594 210 L64 210 Z" fill="url(#aovFill2)"/>
            <path class="chart-line" d="M64 156.3 L170 143.9 L276 119.1 L382 98.4 L488 69.5 L594 40.5" fill="none"/>
            <g class="chart-xlabels">
              <text x="64" y="232">Jan</text>
              <text x="170" y="232">Feb</text>
              <text x="276" y="232">Mar</text>
              <text x="382" y="232">Apr</text>
              <text x="488" y="232">May</text>
              <text x="594" y="232">Jun</text>
            </g>
            <circle class="chart-end" cx="594" cy="40.5" r="5"/>
            <g class="chart-endlabel">
              <rect x="548" y="12" rx="6" width="58" height="22"/>
              <text x="577" y="27">$86.40</text>
            </g>
          </svg>
          <div class="chart-hover" id="chartHover" aria-hidden="true">
            <div class="chart-crosshair"></div>
            <div class="chart-dot"></div>
            <div class="chart-tip"><b></b><small></small></div>
          </div>
        </div>
      </div>
      <div class="dash-insights">
        <div class="insight-card">
          <span class="insight-tag">AI insight</span>
          <p>Carts between <b>$60–70</b> rarely reach your $100 tier. Adding a <b>$75 tier</b> could convert 340 carts/month.</p>
          <span class="insight-apply">Apply suggestion</span>
        </div>
        <div class="insight-card">
          <span class="insight-tag">AI insight</span>
          <p><b>Trail beanie</b> attaches to hoodies at 3× the average rate. Featuring it in the cart could add <b>~$1,900/mo</b>.</p>
          <span class="insight-apply">Apply suggestion</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- 8. Brix AI -->
<section class="f-section section-dark" id="ai-chat">
  <div class="ai-gradient" aria-hidden="true"></div>
  <div class="container f-grid f-grid-flip" style="position:relative;">
    <div class="f-copy">
      <p class="eyebrow eyebrow-warm reveal">08 · Brix AI Chat</p>
      <h2 class="reveal">The employee who never clocks out</h2>
      <p class="reveal" style="color:rgba(255,255,255,.72);">Every feature above can be managed by hand, or you can just tell Brix AI what you want in plain English. It plans the changes, ships them, watches the results daily, and rolls back anything that underperforms. It is the AI upsell Shopify brands rely on to stay ahead.</p>
      <ul class="ai-points reveal">
        <li><span class="ai-ic">◆</span> Ask for an outcome in plain English, and Brix AI plans the changes and ships them in seconds</li>
        <li><span class="ai-ic">◆</span> Daily tuning of tier amounts based on live cart data</li>
        <li><span class="ai-ic">◆</span> A full change log, so you can review, approve or undo anything it does</li>
      </ul>
    </div>
    <div class="f-visual reveal" style="--d:.1s">
      <div class="chat-mock" id="aiChat">
        <div class="chat-head">
          <span class="chat-avatar" aria-hidden="true">
            <img src="assets/brix-mark-light.png" alt="Brix logo" style="width:19px;height:auto;">
          </span>
          <span><b>Brix AI</b><small>Optimizing your store</small></span>
        </div>
        <div class="chat-body">
          <div class="chat-bubble cb-user" id="cbUser"><span id="cbUserText"></span><span class="chat-caret" id="cbCaret"></span></div>
          <div class="chat-typing" id="cbTyping"><span></span><span></span><span></span></div>
          <div class="chat-bubble cb-ai" id="cbAi">On it. I’ve raised your free-shipping tier to <b>$80</b>, added a gift tier at <b>$130</b>, and turned on Frequently&nbsp;Bought&nbsp;Together for <b>214 products</b>.</div>
          <div class="chat-action" id="cbAction">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" aria-hidden="true"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
            3 changes live · monitoring results
          </div>
        </div>
        <div class="chat-input" aria-hidden="true"><span>Ask Brix AI anything…</span><i>↑</i></div>
      </div>
    </div>
  </div>
</section>

<!-- everything else in the box -->
<section class="section section-soft" id="extras">
  <div class="container">
    <div class="section-head reveal">
      <p class="eyebrow">And the rest</p>
      <h2>Everything else in the box</h2>
      <p class="section-sub">Smaller tools that round out the big eight, with no extra apps needed.</p>
    </div>
    <div class="xtra-grid">
      <div class="xtra reveal"><b>Auto-open cart</b><span>The drawer slides open the moment something is added.</span></div>
      <div class="xtra reveal" style="--d:.04s"><b>Announcement banner</b><span>A custom header strip in the cart for shipping notes and promos.</span></div>
      <div class="xtra reveal" style="--d:.08s"><b>Design controls</b><span>Width, animation and shadow settings to match your theme exactly.</span></div>
      <div class="xtra reveal" style="--d:.12s"><b>Empty-cart customisation</b><span>Turn a dead end into a shelf: featured products and offers.</span></div>
      <div class="xtra reveal"><b>Checkout button styling</b><span>Color, copy and shape of the most important button in your store.</span></div>
      <div class="xtra reveal" style="--d:.04s"><b>Coupon countdown timers</b><span>In-cart deals with a visible clock to close the sale now.</span></div>
      <div class="xtra reveal" style="--d:.08s"><b>Coupon banner targeting</b><span>Show offers only on the products or collections you pick.</span></div>
      <div class="xtra reveal" style="--d:.12s"><b>Swipe-to-checkout</b><span>A mobile gesture that takes shoppers straight to payment.</span></div>
      <div class="xtra reveal"><b>Custom CSS editor</b><span>Full control for the pixel-perfect brands.</span></div>
      <div class="xtra reveal" style="--d:.04s"><b>Funnels &amp; attribution</b><span>See the path from nudge to checkout, dollar by dollar.</span></div>
      <div class="xtra reveal" style="--d:.08s"><b>Multi-store support</b><span>Run every storefront from one Brix account on Pro.</span></div>
      <div class="xtra reveal" style="--d:.12s"><b>Branding removal</b><span>Remove the “Powered by BRIX” watermark on Pro.</span></div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-final" id="install">
  <div class="cta-gradient" aria-hidden="true"></div>
  <div class="container cta-in">
    <h2 class="reveal">See them working in your store</h2>
    <p class="reveal" style="--d:.08s">All eight features, live on your theme in five minutes.</p>
    <a class="btn btn-white btn-lg reveal" style="--d:.16s" href="https://apps.shopify.com/thebrix-io?utm_source=Brix-Website&amp;utm_medium=Organic&amp;utm_campaign=Website_Tracking&amp;utm_id=Website" target="_blank" rel="noopener" id="ctaInstall">Install on Shopify for free</a>
    <p class="cta-note reveal" style="--d:.24s">Free plan available · No credit card</p>
  </div>
</section>
<?php require BRIX_INCLUDES . '/footer.php'; ?>
