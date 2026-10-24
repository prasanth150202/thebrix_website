<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once BRIX_INCLUDES . '/posts.php';

$page_title       = 'About Brix: AI Cart Upsell App for Shopify';
$page_description = 'Brix is a Shopify app that helps stores raise average order value from the cart. What we build, who it is for, and how to reach the team.';
$page_canonical   = 'about';
$page_nav         = NULL;
$page_robots      = 'index, follow';
$footer_col3      = 'case-studies';

// AboutPage markup pointing at the Organization already declared on
// every page, so search and AI engines connect this page to the brand.
$page_schema = [[
    '@context'   => 'https://schema.org',
    '@type'      => 'AboutPage',
    'url'        => SITE_URL . '/about',
    'name'       => 'About Brix',
    'mainEntity' => ['@id' => SITE_URL . '/#organization'],
]];

require BRIX_INCLUDES . '/header.php';
?>

<section class="page-hero">
  <div class="hero-glow" aria-hidden="true"></div>
  <div class="container">
    <p class="eyebrow reveal">About</p>
    <h1 class="reveal" style="--d:.06s">About <em>Brix</em></h1>
    <p class="hero-sub reveal" style="--d:.12s">Brix is a Shopify app that helps online stores raise average order value from the cart, with upsells, rewards, coupons and bundles in one place.</p>
  </div>
</section>

<section class="legal">
  <div class="container legal-grid">

    <aside class="legal-toc">
      <p class="legal-toc-h">On this page</p>
      <nav aria-label="About sections">
        <a href="#what">1. What Brix is</a>
        <a href="#does">2. What it does</a>
        <a href="#for">3. Who it is for</a>
        <a href="#writing">4. How we write</a>
        <a href="#find">5. Find us</a>
        <a href="#contact">6. Contact</a>
      </nav>
    </aside>

    <div class="legal-doc">
      <p class="legal-intro"><strong>Brix</strong> is built for Shopify merchants and the agencies that run Shopify stores. The idea is simple: the cart is where the buying decision happens, so that is where a store can add the most value to each order.</p>

      <div class="legal-block" id="what">
        <h2><span class="legal-num">01</span>What Brix is</h2>
        <p>Brix is an app for Shopify. Instead of installing separate tools for upsells, rewards, coupons and bundles, merchants manage them from one dashboard and one customizable cart drawer. It installs from the <a href="<?= e(SHOPIFY_APP_STORE_URL) ?>" target="_blank" rel="noopener">Shopify App Store</a> and bills through Shopify.</p>
      </div>

      <div class="legal-block" id="does">
        <h2><span class="legal-num">02</span>What it does</h2>
        <ul class="legal-list">
          <li><strong>Cart drawer:</strong> a customizable slide-out cart.</li>
          <li><strong>Cart upsells:</strong> relevant add-on suggestions, in AI mode or with your own rules.</li>
          <li><strong>Frequently Bought Together:</strong> product-page pairings that shoppers add in one tap.</li>
          <li><strong>Rewards progress bar:</strong> shows how close a shopper is to free shipping or a gift.</li>
          <li><strong>Coupon Slider:</strong> shows your active discount codes inside the cart.</li>
          <li><strong>Combo Forge:</strong> a builder for bundle pages.</li>
          <li><strong>Brix AI assistant:</strong> sets things up from a plain-language request and asks for confirmation before it changes anything.</li>
          <li><strong>Analytics:</strong> revenue by feature, so you can see what is working.</li>
        </ul>
        <p>See the full detail on the <a href="features">features page</a> and the <a href="pricing">pricing page</a>.</p>
      </div>

      <div class="legal-block" id="for">
        <h2><span class="legal-num">03</span>Who it is for</h2>
        <p>Shopify merchants who already have traffic and want more value from each order, from small direct-to-consumer stores to high-volume brands. Agencies and freelancers who manage cart experiences for several client stores use Brix to keep that work in one app.</p>
      </div>

      <div class="legal-block" id="writing">
        <h2><span class="legal-num">04</span>How we write</h2>
        <p>The guides and case studies on this site are written for Shopify merchants and agencies who want practical, plain-language advice. We do not promise guaranteed results, and case studies are published only with the merchant's permission. Articles carry the name of their author, and you can read more about them on the <a href="/author/irshaad">author page</a>.</p>
        <p>See something that looks wrong or out of date? Tell us at <a href="mailto:support@thebrix.io">support@thebrix.io</a> and we will fix it.</p>
      </div>

      <div class="legal-block" id="find">
        <h2><span class="legal-num">05</span>Find us</h2>
        <ul class="legal-list">
          <li><a href="<?= e(SHOPIFY_APP_STORE_URL) ?>" target="_blank" rel="noopener">Shopify App Store listing</a></li>
          <li><a href="https://www.linkedin.com/company/the-brix-io" target="_blank" rel="noopener">LinkedIn</a></li>
          <li><a href="https://x.com/app_thebrix" target="_blank" rel="noopener">X</a></li>
          <li><a href="https://www.youtube.com/@TheBrixApp" target="_blank" rel="noopener">YouTube</a></li>
          <li><a href="https://www.instagram.com/thebrix.io/" target="_blank" rel="noopener">Instagram</a></li>
        </ul>
      </div>

      <div class="legal-block" id="contact">
        <h2><span class="legal-num">06</span>Contact</h2>
        <p>Questions about Brix, setup or pricing? Message the team and we reply within one business day.</p>
        <div class="legal-contact">
          <div class="legal-contact-txt">
            <h3>Talk to the Brix team</h3>
            <p>Use the contact form, or email us directly.</p>
          </div>
          <a class="btn btn-white" href="contact">Contact us</a>
        </div>
      </div>
    </div>

  </div>
</section>
<?php require BRIX_INCLUDES . '/footer.php'; ?>
