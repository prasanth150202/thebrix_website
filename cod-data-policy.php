<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once BRIX_INCLUDES . '/posts.php';

$page_title       = 'COD Order Data Policy | Brix for Shopify';
$page_description = 'How Brix handles the phone number and delivery address collected from cash on delivery (COD) orders on stores that use Brix, and how to request deletion.';
$page_canonical   = 'cod-data-policy';
$page_nav         = NULL;
$page_robots      = 'index, follow';
$footer_col3      = 'case-studies';

require BRIX_INCLUDES . '/header.php';
?>

<section class="page-hero">
  <div class="hero-glow" aria-hidden="true"></div>
  <div class="container">
    <p class="eyebrow reveal">Legal</p>
    <h1 class="reveal" style="--d:.06s">COD Order Data <em>Policy</em></h1>
    <p class="hero-sub reveal" style="--d:.12s">What we store when a shopper places a cash on delivery order on a store that uses Brix, and how that data can be deleted.</p>
    <div class="legal-switch reveal" style="--d:.18s" role="tablist" aria-label="Legal documents">
      <a href="terms">Terms &amp; Conditions</a>
      <a href="privacy">Privacy Policy</a>
      <a href="cod-data-policy" class="is-active" aria-current="page">COD Data Policy</a>
    </div>
    <div class="legal-meta reveal" style="--d:.24s">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"></rect><path d="M16 2v4M8 2v4M3 10h18"></path></svg>
      Effective October 2026
    </div>
  </div>
</section>

<section class="legal">
  <div class="container legal-grid">

    <aside class="legal-toc">
      <p class="legal-toc-h">On this page</p>
      <nav aria-label="COD data policy sections">
        <a href="#scope">1. Who This Applies To</a>
        <a href="#roles">2. Roles: Store and Brix</a>
        <a href="#collect">3. What We Store</a>
        <a href="#use">4. Why We Store It</a>
        <a href="#sharing">5. Sharing</a>
        <a href="#retention">6. How Long We Keep It</a>
        <a href="#delete">7. How to Delete Your Data</a>
        <a href="#merchants">8. Notes for Store Owners</a>
        <a href="#security">9. Security</a>
        <a href="#contact">10. Contact Us</a>
      </nav>
    </aside>

    <div class="legal-doc">
      <p class="legal-intro">This policy applies when a shopper chooses <strong>cash on delivery (COD)</strong> at checkout on an online store that uses the <strong>Brix</strong> Shopify app. It explains what details we save for that order and how they can be deleted. It sits alongside our <a href="privacy">Privacy Policy</a>.</p>

      <div class="legal-block" id="scope">
        <h2><span class="legal-num">01</span>Who This Applies To</h2>
        <p>This policy is for shoppers who place a COD order on a Shopify store that has installed Brix. If you are a store owner using Brix, our <a href="privacy">Privacy Policy</a> and <a href="terms">Terms &amp; Conditions</a> apply to your own account data.</p>
      </div>

      <div class="legal-block" id="roles">
        <h2><span class="legal-num">02</span>Roles: Store and Brix</h2>
        <p>The store you buy from decides why your details are collected and what happens to your order. Brix provides the software and saves the details on the store's behalf. In data protection terms, the store is the controller of your order data and Brix acts as its service provider (processor).</p>
        <p>This is why deletion requests are handled through the store first. See section 7.</p>
      </div>

      <div class="legal-block" id="collect">
        <h2><span class="legal-num">03</span>What We Store</h2>
        <p>When a COD order is placed, Brix saves the details needed to handle that order:</p>
        <ul class="legal-list">
          <li>Phone number</li>
          <li>Delivery address</li>
          <li>The order details needed to identify the COD order, such as order reference and order contents</li>
        </ul>
        <p class="legal-note">We do not collect card numbers or bank details for COD orders, because payment is made on delivery.</p>
      </div>

      <div class="legal-block" id="use">
        <h2><span class="legal-num">04</span>Why We Store It</h2>
        <p>We save this information only to:</p>
        <ul class="legal-list">
          <li>Record and process the COD order for the store.</li>
          <li>Let the store contact you about the delivery.</li>
          <li>Operate, secure and support the service, including preventing fraud and misuse.</li>
        </ul>
        <p>We do not use your phone number or address for our own advertising.</p>
      </div>

      <div class="legal-block" id="sharing">
        <h2><span class="legal-num">05</span>Sharing</h2>
        <p>We do <strong>not sell</strong> this data. It may be accessible to:</p>
        <ul class="legal-list">
          <li>The store you ordered from.</li>
          <li>Shopify, as needed for the app to work.</li>
          <li>Hosting and infrastructure providers that run our service for us.</li>
          <li>Authorities, where we are legally required to disclose it.</li>
        </ul>
      </div>

      <div class="legal-block" id="retention">
        <h2><span class="legal-num">06</span>How Long We Keep It</h2>
        <p>We keep COD order details for as long as the store uses Brix and needs them to run its orders, or until a verified deletion request is completed. When a store uninstalls Brix, we delete the data we hold for that store in line with Shopify's data deletion requirements.</p>
        <p>The store may keep its own copy of your order in Shopify, for example for tax, accounting or delivery records. That copy is under the store's control, not ours.</p>
      </div>

      <div class="legal-block" id="delete">
        <h2><span class="legal-num">07</span>How to Delete Your Data</h2>
        <p>If you want your COD phone number and address removed, follow these steps:</p>
        <ul class="legal-list">
          <li><strong>Step 1. Contact the store you ordered from.</strong> Tell them you want your COD order details deleted from the Brix app. Give them your name, phone number and order reference so they can find your order.</li>
          <li><strong>Step 2. The store sends us the request.</strong> The store emails <a href="mailto:support@thebrix.io">support@thebrix.io</a> with its store URL and the order reference or phone number to delete.</li>
          <li><strong>Step 3. We verify and delete.</strong> We confirm the request comes from the store, delete the matching data from Brix, and confirm to the store when it is done.</li>
        </ul>
        <p>If you email us directly, we cannot verify who you are on the store's behalf, so we will point you back to the store. We also act on customer data erasure requests sent to us through Shopify.</p>
        <p class="legal-note">Deleting your data from Brix does not delete the order from the store's own Shopify records. Ask the store about that separately.</p>
      </div>

      <div class="legal-block" id="merchants">
        <h2><span class="legal-num">08</span>Notes for Store Owners</h2>
        <p>If you use Brix to take COD orders, you are responsible for telling your shoppers what you collect and why, and for having a legal basis to collect it. Please link to this policy from your own privacy policy or checkout. When a shopper asks you to delete their COD details, send us the request at <a href="mailto:support@thebrix.io">support@thebrix.io</a> with the shopper's phone number or order reference and your store URL.</p>
      </div>

      <div class="legal-block" id="security">
        <h2><span class="legal-num">09</span>Security</h2>
        <p>We use industry-standard measures to protect the data we hold. No method of storing or sending data over the internet is completely secure, so we cannot guarantee absolute security.</p>
      </div>

      <div class="legal-block" id="contact">
        <h2><span class="legal-num">10</span>Contact Us</h2>
        <p>Questions about this policy, or a deletion request from a store? Get in touch.</p>
        <div class="legal-contact">
          <div class="legal-contact-txt">
            <h3>Need a COD data deletion?</h3>
            <p>Store owners can send the request to our support team.</p>
          </div>
          <a class="btn btn-white" href="mailto:support@thebrix.io">support@thebrix.io</a>
        </div>
      </div>
    </div>

  </div>
</section>
<?php require BRIX_INCLUDES . '/footer.php'; ?>
