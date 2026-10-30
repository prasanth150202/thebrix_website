<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once BRIX_INCLUDES . '/posts.php';

$page_title       = 'Brix Pricing: Free & $9 to $199 Plans for Shopify';
$page_description = 'Brix pricing for Shopify: Free, then $9 to $199/mo. Every plan includes every feature; paid plans remove the watermark. No credit card needed.';
$page_canonical   = 'pricing';
$page_nav         = 'pricing';
$footer_col3      = 'case-studies';

require BRIX_INCLUDES . '/header.php';
?>

<section class="page-hero">
  <div class="hero-glow" aria-hidden="true"></div>
  <div class="container">
    <p class="eyebrow reveal">Pricing</p>
    <h1 class="reveal" style="--d:.06s">Start free. <em>Scale when you do.</em></h1>
    <p class="hero-sub reveal" style="--d:.12s">Every plan includes every feature, so you only choose the order volume that fits your store. Free is free forever and shows a “Powered by BRIX” watermark. Paid plans remove it and start with a 7-day free trial, then bill monthly through Shopify, with no contracts. Cancel anytime.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="bill-toggle-wrap" data-bill-toggle hidden>
      <div class="bill-toggle" role="group" aria-label="Billing period">
        <button type="button" class="bill-opt is-on" data-bill="month" aria-pressed="true">Monthly</button>
        <button type="button" class="bill-opt" data-bill="year" aria-pressed="false">Yearly <span class="bill-save">2 months free</span></button>
      </div>
    </div>
    <div class="price-grid price-grid-full">
      <div class="price-card reveal">
        <span class="price-flag price-flag-ghost">Free forever</span>
        <h3>Free</h3>
        <p class="price"><b>$0</b><span>/month</span></p>
        <p class="price-year">No credit card needed</p>
        <p class="price-note">Launch &amp; learn · up to 50 orders/month</p>
        <ul class="price-list">
          <li>Zero Commissions &amp; No Extra Charges</li>
          <li>COD Checkouts - Prepaid Order Booster</li>
          <li>AI Cart Upsell - Increase your Store AOV</li>
          <li>AI Bundles - Smart Bundles &amp; Packs</li>
          <li>AI Based FBT - For Product &amp; Cart Pages</li>
          <li>AI prompt based feature update - 10 credits</li>
          <li>Rewards Progress Bar &amp; Announcement Bar</li>
          <li>Coupon Slider, Countdown Timer</li>
          <li class="is-off">Shows a “Powered by BRIX” watermark</li>
        </ul>
        <a class="btn btn-outline price-cta" href="https://apps.shopify.com/thebrix-io?utm_source=Brix-Website&amp;utm_medium=Organic&amp;utm_campaign=Website_Tracking&amp;utm_id=Website" target="_blank" rel="noopener">Install free ↗</a>
      </div>
      <div class="price-card reveal" style="--d:.06s">
        <h3>Lite</h3>
        <p class="price" data-monthly="$9" data-yearly="$90"><b>$9</b><span>/month</span></p>
        <p class="price-year" data-monthly="or $90 billed yearly" data-yearly="Save $18 a year">or $90 billed yearly</p>
        <p class="price-note">Start growing · 51–250 orders/month</p>
        <ul class="price-list">
          <li>Zero Commissions &amp; No Extra Charges</li>
          <li>COD Checkouts - Prepaid Order Booster</li>
          <li>AI Cart Upsell - Increase your Store AOV</li>
          <li>AI Bundles - Smart Bundles &amp; Packs</li>
          <li>AI Based FBT - For Product &amp; Cart Pages</li>
          <li>AI prompt based feature update - 20 credits</li>
          <li>Rewards Progress Bar &amp; Announcement Bar</li>
          <li>Coupon Slider, Countdown Timer</li>
          <li>No “Powered by BRIX” watermark</li>
        </ul>
        <a class="btn btn-outline price-cta" href="https://apps.shopify.com/thebrix-io?utm_source=Brix-Website&amp;utm_medium=Organic&amp;utm_campaign=Website_Tracking&amp;utm_id=Website" target="_blank" rel="noopener">Start free trial ↗</a>
      </div>
      <div class="price-card price-card-hot reveal" style="--d:.12s">
        <span class="price-flag">Most popular</span>
        <h3>Starter</h3>
        <p class="price" data-monthly="$29" data-yearly="$290"><b>$29</b><span>/month</span></p>
        <p class="price-year" data-monthly="or $290 billed yearly" data-yearly="Save $58 a year">or $290 billed yearly</p>
        <p class="price-note">Grow your AOV · 251–500 orders/month</p>
        <ul class="price-list">
          <li>Zero Commissions &amp; No Extra Charges</li>
          <li>COD Checkouts - Prepaid Order Booster</li>
          <li>AI Cart Upsell - Increase your Store AOV</li>
          <li>AI Bundles - Smart Bundles &amp; Packs</li>
          <li>AI Based FBT - For Product &amp; Cart Pages</li>
          <li>AI prompt based feature update - 30 credits</li>
          <li>Rewards Progress Bar &amp; Announcement Bar</li>
          <li>Coupon Slider, Countdown Timer</li>
          <li>No “Powered by BRIX” watermark</li>
        </ul>
        <a class="btn btn-primary price-cta" href="https://apps.shopify.com/thebrix-io?utm_source=Brix-Website&amp;utm_medium=Organic&amp;utm_campaign=Website_Tracking&amp;utm_id=Website" target="_blank" rel="noopener">Start free trial ↗</a>
      </div>
      <div class="price-card reveal" style="--d:.18s">
        <h3>Growth</h3>
        <p class="price" data-monthly="$59" data-yearly="$590"><b>$59</b><span>/month</span></p>
        <p class="price-year" data-monthly="or $590 billed yearly" data-yearly="Save $118 a year">or $590 billed yearly</p>
        <p class="price-note">Scale your AOV · 501–2,000 orders/month</p>
        <ul class="price-list">
          <li>Zero Commissions &amp; No Extra Charges</li>
          <li>COD Checkouts - Prepaid Order Booster</li>
          <li>AI Cart Upsell - Increase your Store AOV</li>
          <li>AI Bundles - Smart Bundles &amp; Packs</li>
          <li>AI Based FBT - For Product &amp; Cart Pages</li>
          <li>AI prompt based feature update - 60 credits</li>
          <li>Rewards Progress Bar &amp; Announcement Bar</li>
          <li>Coupon Slider, Countdown Timer</li>
          <li>No “Powered by BRIX” watermark</li>
        </ul>
        <a class="btn btn-outline price-cta" href="https://apps.shopify.com/thebrix-io?utm_source=Brix-Website&amp;utm_medium=Organic&amp;utm_campaign=Website_Tracking&amp;utm_id=Website" target="_blank" rel="noopener">Start free trial ↗</a>
      </div>
      <div class="price-card reveal" style="--d:.24s">
        <span class="price-flag price-flag-ghost">Best value</span>
        <h3>Pro</h3>
        <p class="price" data-monthly="$79" data-yearly="$790"><b>$79</b><span>/month</span></p>
        <p class="price-year" data-monthly="or $790 billed yearly" data-yearly="Save $158 a year">or $790 billed yearly</p>
        <p class="price-note">High-volume brands · 2,001–5,000 orders/month</p>
        <ul class="price-list">
          <li>Zero Commissions &amp; No Extra Charges</li>
          <li>COD Checkouts - Prepaid Order Booster</li>
          <li>AI Cart Upsell - Increase your Store AOV</li>
          <li>AI Bundles - Smart Bundles &amp; Packs</li>
          <li>AI Based FBT - For Product &amp; Cart Pages</li>
          <li>AI prompt based feature update - 90 credits</li>
          <li>Rewards Progress Bar &amp; Announcement Bar</li>
          <li>Coupon Slider, Countdown Timer</li>
          <li>No “Powered by BRIX” watermark</li>
        </ul>
        <a class="btn btn-outline price-cta" href="https://apps.shopify.com/thebrix-io?utm_source=Brix-Website&amp;utm_medium=Organic&amp;utm_campaign=Website_Tracking&amp;utm_id=Website" target="_blank" rel="noopener">Start free trial ↗</a>
      </div>
      <div class="price-card reveal" style="--d:.30s">
        <h3>Scale</h3>
        <p class="price" data-monthly="$99" data-yearly="$990"><b>$99</b><span>/month</span></p>
        <p class="price-year" data-monthly="or $990 billed yearly" data-yearly="Save $198 a year">or $990 billed yearly</p>
        <p class="price-note">Built for big stores · 5,001–10,000 orders/month</p>
        <ul class="price-list">
          <li>Zero Commissions &amp; No Extra Charges</li>
          <li>COD Checkouts - Prepaid Order Booster</li>
          <li>AI Cart Upsell - Increase your Store AOV</li>
          <li>AI Bundles - Smart Bundles &amp; Packs</li>
          <li>AI Based FBT - For Product &amp; Cart Pages</li>
          <li>AI prompt based feature update - 120 credits</li>
          <li>Rewards Progress Bar &amp; Announcement Bar</li>
          <li>Coupon Slider, Countdown Timer</li>
          <li>No “Powered by BRIX” watermark</li>
        </ul>
        <a class="btn btn-outline price-cta" href="https://apps.shopify.com/thebrix-io?utm_source=Brix-Website&amp;utm_medium=Organic&amp;utm_campaign=Website_Tracking&amp;utm_id=Website" target="_blank" rel="noopener">Start free trial ↗</a>
      </div>
      <div class="price-card price-card-wide reveal" style="--d:.36s">
        <h3>Unlimited</h3>
        <p class="price" data-monthly="$199" data-yearly="$1,990"><b>$199</b><span>/month</span></p>
        <p class="price-year" data-monthly="or $1,990 billed yearly" data-yearly="Save $398 a year">or $1,990 billed yearly</p>
        <p class="price-note">No limits · Unlimited orders/month</p>
        <ul class="price-list">
          <li>Zero Commissions &amp; No Extra Charges</li>
          <li>COD Checkouts - Prepaid Order Booster</li>
          <li>AI Cart Upsell - Increase your Store AOV</li>
          <li>AI Bundles - Smart Bundles &amp; Packs</li>
          <li>AI Based FBT - For Product &amp; Cart Pages</li>
          <li>AI prompt based feature update - 200 credits</li>
          <li>Rewards Progress Bar &amp; Announcement Bar</li>
          <li>Coupon Slider, Countdown Timer</li>
          <li>No “Powered by BRIX” watermark</li>
        </ul>
        <a class="btn btn-outline price-cta" href="https://apps.shopify.com/thebrix-io?utm_source=Brix-Website&amp;utm_medium=Organic&amp;utm_campaign=Website_Tracking&amp;utm_id=Website" target="_blank" rel="noopener">Start free trial ↗</a>
      </div>
    </div>

  </div>
</section>

<section class="section section-soft">
  <div class="container container-narrow">
    <div class="section-head reveal">
      <p class="eyebrow">Pricing questions</p>
      <h2>The fine print, in plain English</h2>
    </div>
    <div class="faq-list reveal" style="--d:.08s">
      <details class="faq-item">
        <summary>What counts as an “order”?<span class="faq-ic"></span></summary>
        <p>Any completed checkout while Brix is installed, whether or not a Brix offer touched it. If you’re between plan sizes, we count the trailing 30 days, not calendar months.</p>
      </details>
      <details class="faq-item">
        <summary>What happens if I go over my order limit?<span class="faq-ic"></span></summary>
        <p>Nothing breaks. Orders keep processing past your plan's limit, and we email you to suggest the next plan. Unlimited has no order limit. We never switch plans or charge you without your say-so.</p>
      </details>
      <details class="faq-item">
        <summary>What are AI prompt credits?<span class="faq-ic"></span></summary>
        <p>Each credit is one prompt to Brix AI: writing a piece of copy, setting up a rule or answering a question about your store. Your allowance renews every month: 10 on Free, 20 on Lite, 30 on Starter, 60 on Growth, 90 on Pro, 120 on Scale and 200 on Unlimited.</p>
      </details>
      <details class="faq-item">
        <summary>Do I need a credit card to start?<span class="faq-ic"></span></summary>
        <p>Not for Free. It’s free forever. Every paid plan starts with a 7-day free trial and then bills monthly through Shopify Billing, so there’s no separate card to enter and you can cancel from your Shopify admin anytime.</p>
      </details>
      <details class="faq-item">
        <summary>Can I pay yearly?<span class="faq-ic"></span></summary>
        <p>Yes. Every paid plan can be billed yearly: Lite $90, Starter $290, Growth $590, Pro $790, Scale $990, Unlimited $1,990 a year.</p>
      </details>
      <details class="faq-item">
        <summary>Can I switch plans later?<span class="faq-ic"></span></summary>
        <p>Yes, in either direction, from the Brix dashboard. Upgrades apply immediately; downgrades apply at the start of your next billing cycle. Your offers, bundles and analytics carry over either way.</p>
      </details>
    </div>
  </div>
</section>

<section class="cta-final" id="install">
  <div class="cta-gradient" aria-hidden="true"></div>
  <div class="container cta-in">
    <h2 class="reveal">Start free. Upgrade when the numbers say so.</h2>
    <p class="reveal" style="--d:.08s">Free is free forever, so you can see your first AOV lift before you spend a dollar.</p>
    <a class="btn btn-white btn-lg reveal" style="--d:.16s" href="https://apps.shopify.com/thebrix-io?utm_source=Brix-Website&amp;utm_medium=Organic&amp;utm_campaign=Website_Tracking&amp;utm_id=Website" target="_blank" rel="noopener" id="ctaInstall">Install on Shopify for free</a>
    <p class="cta-note reveal" style="--d:.24s">Free plan available · No credit card</p>
  </div>
</section>
<?php require BRIX_INCLUDES . '/footer.php'; ?>
