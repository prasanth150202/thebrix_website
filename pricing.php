<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once BRIX_INCLUDES . '/posts.php';

$page_title       = 'Pricing | Brix for Shopify';
$page_description = 'Brix pricing: Free forever, Starter at $29/mo, Pro at $79/mo for high-revenue brands. Compare every feature across plans.';
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
    <p class="hero-sub reveal" style="--d:.12s">Free is free forever. Starter and Pro start with a 14-day free trial, then bill monthly through Shopify, with no contracts. Cancel anytime.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="price-grid" style="margin-bottom:64px;">
      <div class="price-card reveal">
        <span class="price-flag price-flag-ghost">Free forever</span>
        <h3>Free</h3>
        <p class="price"><b>$0</b><span>/month</span></p>
        <p class="price-note">Launch &amp; learn · 50 orders/month</p>
        <ul class="price-list">
          <li>Cart Drawer, Announcement Bar &amp; Empty Cart Customization</li>
          <li>AI BRIX: 10 credits/month, then $0.09/credit</li>
          <li>Frequently Bought Together, Coupon Lock Pro &amp; Progress Bar: preview only</li>
          <li>Basic analytics (revenue, AOV, clicks)</li>
        </ul>
        <a class="btn btn-outline price-cta" href="https://apps.shopify.com/thebrix-io?utm_source=Brix-Website&amp;utm_medium=Organic&amp;utm_campaign=Website_Tracking&amp;utm_id=Website" target="_blank" rel="noopener">Install free ↗</a>
      </div>
      <div class="price-card price-card-hot reveal" style="--d:.06s">
        <span class="price-flag">Most popular</span>
        <h3>Starter</h3>
        <p class="price"><b>$29</b><span>/month</span></p>
        <p class="price-note">Grow your AOV · 500 orders/month, then $0.30/order</p>
        <ul class="price-list">
          <li>Everything in Free, fully unlocked</li>
          <li>AI Cart Upsell &amp; Confetti celebrations</li>
          <li>Analytics: Overview &amp; Build a Combo only</li>
          <li>Build a Combo (up to 3 templates), Custom CSS &amp; Open Countdown</li>
          <li>AI BRIX: 30 credits/month, then $0.03/credit</li>
          <li>Priority email support &amp; 24/7 AI support</li>
          <li>Removes the “Powered by BRIX” watermark</li>
        </ul>
        <a class="btn btn-primary price-cta" href="https://apps.shopify.com/thebrix-io?utm_source=Brix-Website&amp;utm_medium=Organic&amp;utm_campaign=Website_Tracking&amp;utm_id=Website" target="_blank" rel="noopener">Start free trial ↗</a>
      </div>
      <div class="price-card reveal" style="--d:.12s">
        <span class="price-flag price-flag-ghost">Best value</span>
        <h3>Pro</h3>
        <p class="price"><b>$79</b><span>/month</span></p>
        <p class="price-note">High-revenue brands · unlimited orders</p>
        <ul class="price-list">
          <li>Everything in Starter, plus Full Analytics unlocked</li>
          <li>AI Analytics, Advanced AI Analytics &amp; Unlimited AI Agents</li>
          <li>Unlimited Build a Combo templates</li>
          <li>AI BRIX: 90 credits/month, then $0.01/credit</li>
          <li>Removes the “Powered by BRIX” watermark</li>
        </ul>
        <a class="btn btn-outline price-cta" href="https://apps.shopify.com/thebrix-io?utm_source=Brix-Website&amp;utm_medium=Organic&amp;utm_campaign=Website_Tracking&amp;utm_id=Website" target="_blank" rel="noopener">Start free trial ↗</a>
      </div>
    </div>

    <div class="section-head reveal">
      <h2>Compare every feature</h2>
    </div>

    <div class="table-wrap reveal" style="--d:.08s">
      <table class="price-table">
        <thead>
          <tr>
            <th scope="col">Feature</th>
            <th scope="col">Free<span class="pt-price">$0/mo · free forever</span></th>
            <th scope="col" class="pt-hot">Starter<span class="pt-price">$29/mo</span></th>
            <th scope="col">Pro<span class="pt-price">$79/mo</span></th>
          </tr>
        </thead>
        <tbody>
          <tr class="pt-group"><td colspan="4">Usage</td></tr>
          <tr>
            <td>Orders per month</td>
            <td><span class="pt-text">50</span></td>
            <td><span class="pt-text">500, then $0.30/order</span></td>
            <td><span class="pt-text">Unlimited</span></td>
          </tr>
          <tr class="pt-group"><td colspan="4">Cart &amp; storefront</td></tr>
          <tr>
            <td>Cart Drawer</td>
            <td><span class="pt-yes">✓</span></td>
            <td><span class="pt-yes">✓</span></td>
            <td><span class="pt-yes">✓</span></td>
          </tr>
          <tr>
            <td>Announcement Bar</td>
            <td><span class="pt-yes">✓</span></td>
            <td><span class="pt-yes">✓</span></td>
            <td><span class="pt-yes">✓</span></td>
          </tr>
          <tr>
            <td>Empty Cart Customization</td>
            <td><span class="pt-yes">✓</span></td>
            <td><span class="pt-yes">✓</span></td>
            <td><span class="pt-yes">✓</span></td>
          </tr>
          <tr>
            <td>Progress Bar</td>
            <td><span class="pt-preview">Preview</span></td>
            <td><span class="pt-yes">✓</span></td>
            <td><span class="pt-yes">✓</span></td>
          </tr>
          <tr>
            <td>Mobile Swipe Checkout</td>
            <td><span class="pt-no">✕</span></td>
            <td><span class="pt-yes">✓</span></td>
            <td><span class="pt-yes">✓</span></td>
          </tr>
          <tr>
            <td>Open Countdown</td>
            <td><span class="pt-no">✕</span></td>
            <td><span class="pt-yes">✓</span></td>
            <td><span class="pt-yes">✓</span></td>
          </tr>
          <tr>
            <td>Custom CSS</td>
            <td><span class="pt-no">✕</span></td>
            <td><span class="pt-yes">✓</span></td>
            <td><span class="pt-yes">✓</span></td>
          </tr>
          <tr class="pt-group"><td colspan="4">Offers &amp; AI selling</td></tr>
          <tr>
            <td>AI BRIX</td>
            <td><span class="pt-yes">✓</span></td>
            <td><span class="pt-yes">✓</span></td>
            <td><span class="pt-yes">✓</span></td>
          </tr>
          <tr>
            <td>AI BRIX credits</td>
            <td><span class="pt-text">10/mo, then $0.09/credit</span></td>
            <td><span class="pt-text">30/mo, then $0.03/credit</span></td>
            <td><span class="pt-text">90/mo, then $0.01/credit</span></td>
          </tr>
          <tr>
            <td>Frequently Bought Together</td>
            <td><span class="pt-preview">Preview</span></td>
            <td><span class="pt-yes">✓</span></td>
            <td><span class="pt-yes">✓</span></td>
          </tr>
          <tr>
            <td>Coupon Lock Pro</td>
            <td><span class="pt-preview">Preview</span></td>
            <td><span class="pt-yes">✓</span></td>
            <td><span class="pt-yes">✓</span></td>
          </tr>
          <tr>
            <td>AI Cart Upsell</td>
            <td><span class="pt-no">✕</span></td>
            <td><span class="pt-yes">✓</span></td>
            <td><span class="pt-yes">✓</span></td>
          </tr>
          <tr>
            <td>Build a Combo</td>
            <td><span class="pt-no">✕</span></td>
            <td><span class="pt-yes">✓</span></td>
            <td><span class="pt-yes">✓</span></td>
          </tr>
          <tr>
            <td>Build a Combo templates</td>
            <td><span class="pt-no">✕</span></td>
            <td><span class="pt-text">Up to 3</span></td>
            <td><span class="pt-text">Unlimited</span></td>
          </tr>
          <tr>
            <td>Confetti celebrations</td>
            <td><span class="pt-no">✕</span></td>
            <td><span class="pt-yes">✓</span></td>
            <td><span class="pt-yes">✓</span></td>
          </tr>
          <tr class="pt-group"><td colspan="4">Analytics &amp; support</td></tr>
          <tr>
            <td>Full Analytics</td>
            <td><span class="pt-no">✕</span></td>
            <td><span class="pt-text">Overview &amp; Build a Combo only</span></td>
            <td><span class="pt-yes">✓</span></td>
          </tr>
          <tr>
            <td>AI Analytics</td>
            <td><span class="pt-no">✕</span></td>
            <td><span class="pt-no">✕</span></td>
            <td><span class="pt-yes">✓</span></td>
          </tr>
          <tr>
            <td>Advanced AI Analytics</td>
            <td><span class="pt-no">✕</span></td>
            <td><span class="pt-no">✕</span></td>
            <td><span class="pt-yes">✓</span></td>
          </tr>
          <tr>
            <td>Unlimited AI Agents</td>
            <td><span class="pt-no">✕</span></td>
            <td><span class="pt-no">✕</span></td>
            <td><span class="pt-yes">✓</span></td>
          </tr>
          <tr>
            <td>Priority email support</td>
            <td><span class="pt-no">✕</span></td>
            <td><span class="pt-yes">✓</span></td>
            <td><span class="pt-yes">✓</span></td>
          </tr>
          <tr>
            <td>24/7 AI support</td>
            <td><span class="pt-no">✕</span></td>
            <td><span class="pt-yes">✓</span></td>
            <td><span class="pt-yes">✓</span></td>
          </tr>
          <tr class="pt-group"><td colspan="4">Branding</td></tr>
          <tr>
            <td>Remove “Powered by BRIX” watermark</td>
            <td><span class="pt-no">✕</span></td>
            <td><span class="pt-yes">✓</span></td>
            <td><span class="pt-yes">✓</span></td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <td></td>
            <td><a class="btn btn-outline btn-sm price-cta" href="https://apps.shopify.com/thebrix-io?utm_source=Brix-Website&amp;utm_medium=Organic&amp;utm_campaign=Website_Tracking&amp;utm_id=Website" target="_blank" rel="noopener">Install free ↗</a></td>
            <td><a class="btn btn-primary btn-sm price-cta" href="https://apps.shopify.com/thebrix-io?utm_source=Brix-Website&amp;utm_medium=Organic&amp;utm_campaign=Website_Tracking&amp;utm_id=Website" target="_blank" rel="noopener">Start free trial ↗</a></td>
            <td><a class="btn btn-outline btn-sm price-cta" href="https://apps.shopify.com/thebrix-io?utm_source=Brix-Website&amp;utm_medium=Organic&amp;utm_campaign=Website_Tracking&amp;utm_id=Website" target="_blank" rel="noopener">Start free trial ↗</a></td>
          </tr>
        </tfoot>
      </table>
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
        <p>Nothing breaks. Orders keep processing past your plan's limit at a small per-order rate ($0.10/order on Free, $0.30/order on Starter), and we email you to suggest the next plan. Pro has no order cap. We never switch plans or charge you without your say-so.</p>
      </details>
      <details class="faq-item">
        <summary>Do I need a credit card to start?<span class="faq-ic"></span></summary>
        <p>Not for Free. It’s free forever. Starter and Pro start with a 14-day free trial and then bill monthly through Shopify Billing, so there’s no separate card to enter and you can cancel from your Shopify admin anytime.</p>
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
