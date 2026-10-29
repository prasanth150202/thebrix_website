<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once BRIX_INCLUDES . '/posts.php';

$page_title       = 'Brix Pricing: Free & $9 to $99 Plans for Shopify';
$page_description = 'Brix pricing for Shopify: Free, then $9 to $99/mo. Every plan includes every feature; you just pick an order volume. No credit card needed.';
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
    <p class="hero-sub reveal" style="--d:.12s">Every plan includes every feature, so you only choose the order volume that fits your store. Free is free forever. Paid plans start with a 14-day free trial, then bill monthly through Shopify, with no contracts. Cancel anytime.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="price-grid">
      <div class="price-card reveal">
        <span class="price-flag price-flag-ghost">Free forever</span>
        <h3>Free</h3>
        <p class="price"><b>$0</b><span>/month</span></p>
        <p class="price-note">Launch &amp; learn · 50 orders/month</p>
        <ul class="price-list">
          <li>All features included</li>
          <li>10 AI prompt credits to try Brix AI</li>
        </ul>
        <a class="btn btn-outline price-cta" href="https://apps.shopify.com/thebrix-io?utm_source=Brix-Website&amp;utm_medium=Organic&amp;utm_campaign=Website_Tracking&amp;utm_id=Website" target="_blank" rel="noopener">Install free ↗</a>
      </div>
      <div class="price-card reveal" style="--d:.06s">
        <h3>Lite</h3>
        <p class="price"><b>$9</b><span>/month</span></p>
        <p class="price-note">Start growing · 100–200 orders/month</p>
        <ul class="price-list">
          <li>All features included</li>
          <li>20 AI prompt credits for light tuning</li>
        </ul>
        <a class="btn btn-outline price-cta" href="https://apps.shopify.com/thebrix-io?utm_source=Brix-Website&amp;utm_medium=Organic&amp;utm_campaign=Website_Tracking&amp;utm_id=Website" target="_blank" rel="noopener">Start free trial ↗</a>
      </div>
      <div class="price-card price-card-hot reveal" style="--d:.12s">
        <span class="price-flag">Most popular</span>
        <h3>Starter</h3>
        <p class="price"><b>$29</b><span>/month</span></p>
        <p class="price-note">Grow your AOV · 200–500 orders/month</p>
        <ul class="price-list">
          <li>All features included</li>
          <li>30 AI prompt credits for regular tweaks</li>
        </ul>
        <a class="btn btn-primary price-cta" href="https://apps.shopify.com/thebrix-io?utm_source=Brix-Website&amp;utm_medium=Organic&amp;utm_campaign=Website_Tracking&amp;utm_id=Website" target="_blank" rel="noopener">Start free trial ↗</a>
      </div>
      <div class="price-card reveal" style="--d:.18s">
        <h3>Growth</h3>
        <p class="price"><b>$59</b><span>/month</span></p>
        <p class="price-note">Scale your AOV · 500–1,000 orders/month</p>
        <ul class="price-list">
          <li>All features included</li>
          <li>60 AI prompt credits for frequent edits</li>
        </ul>
        <a class="btn btn-outline price-cta" href="https://apps.shopify.com/thebrix-io?utm_source=Brix-Website&amp;utm_medium=Organic&amp;utm_campaign=Website_Tracking&amp;utm_id=Website" target="_blank" rel="noopener">Start free trial ↗</a>
      </div>
      <div class="price-card reveal" style="--d:.24s">
        <span class="price-flag price-flag-ghost">Best value</span>
        <h3>Pro</h3>
        <p class="price"><b>$79</b><span>/month</span></p>
        <p class="price-note">High-volume brands · 1,000–2,000 orders/month</p>
        <ul class="price-list">
          <li>All features included</li>
          <li>90 AI prompt credits for heavy AI use</li>
        </ul>
        <a class="btn btn-outline price-cta" href="https://apps.shopify.com/thebrix-io?utm_source=Brix-Website&amp;utm_medium=Organic&amp;utm_campaign=Website_Tracking&amp;utm_id=Website" target="_blank" rel="noopener">Start free trial ↗</a>
      </div>
      <div class="price-card reveal" style="--d:.3s">
        <h3>Enterprise</h3>
        <p class="price"><b>$99</b><span>/month</span></p>
        <p class="price-note">For the biggest stores · Unlimited orders/month</p>
        <ul class="price-list">
          <li>All features included</li>
          <li>Unlimited AI prompt credits</li>
        </ul>
        <a class="btn btn-outline price-cta" href="https://apps.shopify.com/thebrix-io?utm_source=Brix-Website&amp;utm_medium=Organic&amp;utm_campaign=Website_Tracking&amp;utm_id=Website" target="_blank" rel="noopener">Start free trial ↗</a>
      </div>
    </div>

    <div class="price-includes reveal" style="--d:.1s">
      <h3>Every plan includes</h3>
      <ul class="price-list">
        <li>AI Cart Upsell</li>
        <li>FBT, AI Suggestion, Smart Recommendations</li>
        <li>Rewards Progress Bar</li>
        <li>Coupon Slider, Countdown Timer</li>
        <li>Bundle Builder, Packs</li>
        <li>COD Button + Prepaid Discount</li>
        <li>Mobile Swipe Checkout, AI Insights</li>
      </ul>
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
        <p>Nothing breaks. Orders keep processing past your plan's limit, and we email you to suggest the next plan. Enterprise has no order limit. We never switch plans or charge you without your say-so.</p>
      </details>
      <details class="faq-item">
        <summary>What are AI prompt credits?<span class="faq-ic"></span></summary>
        <p>Each credit is one prompt to Brix AI: writing a piece of copy, setting up a rule or answering a question about your store. Your allowance renews every month: 10 on Free, 20 on Lite, 30 on Starter, 60 on Growth, 90 on Pro and unlimited on Enterprise.</p>
      </details>
      <details class="faq-item">
        <summary>Do I need a credit card to start?<span class="faq-ic"></span></summary>
        <p>Not for Free. It’s free forever. Every paid plan starts with a 14-day free trial and then bills monthly through Shopify Billing, so there’s no separate card to enter and you can cancel from your Shopify admin anytime.</p>
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
