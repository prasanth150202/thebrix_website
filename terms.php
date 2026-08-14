<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once BRIX_INCLUDES . '/posts.php';

$page_title       = 'Terms & Conditions | Brix for Shopify';
$page_description = 'The terms and conditions for using The Brix website and Shopify app: acceptance, billing, acceptable use, liability and more.';
$page_canonical   = 'terms';
$page_nav         = NULL;
$page_robots      = 'index, follow';
$footer_col3      = 'case-studies';

require BRIX_INCLUDES . '/header.php';
?>

<section class="page-hero">
  <div class="hero-glow" aria-hidden="true"></div>
  <div class="container">
    <p class="eyebrow reveal">Legal</p>
    <h1 class="reveal" style="--d:.06s">Terms &amp; <em>Conditions</em></h1>
    <p class="hero-sub reveal" style="--d:.12s">The ground rules for using The Brix website and Shopify app. Please read them carefully.</p>
    <div class="legal-switch reveal" style="--d:.18s" role="tablist" aria-label="Legal documents">
      <a href="terms" class="is-active" aria-current="page">Terms &amp; Conditions</a>
      <a href="privacy">Privacy Policy</a>
    </div>
    <div class="legal-meta reveal" style="--d:.24s">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"></rect><path d="M16 2v4M8 2v4M3 10h18"></path></svg>
      Effective July 2026
    </div>
  </div>
</section>

<section class="legal">
  <div class="container legal-grid">

    <aside class="legal-toc">
      <p class="legal-toc-h">On this page</p>
      <nav aria-label="Terms sections">
        <a href="#acceptance">1. Acceptance of Terms</a>
        <a href="#service">2. Description of Service</a>
        <a href="#eligibility">3. Eligibility</a>
        <a href="#account">4. Account &amp; Installation</a>
        <a href="#billing">5. Subscription &amp; Billing</a>
        <a href="#acceptable-use">6. Acceptable Use</a>
        <a href="#ip">7. Intellectual Property</a>
        <a href="#liability">8. Limitation of Liability</a>
        <a href="#termination">9. Termination</a>
        <a href="#third-party">10. Third-Party Services</a>
        <a href="#changes">11. Changes to Terms</a>
        <a href="#governing-law">12. Governing Law</a>
        <a href="#contact">13. Contact Information</a>
      </nav>
    </aside>

    <div class="legal-doc">
      <p class="legal-intro">Welcome to <strong>The Brix</strong>. By using our website and Shopify app, you agree to the following terms and conditions.</p>

      <div class="legal-block" id="acceptance">
        <h2><span class="legal-num">01</span>Acceptance of Terms</h2>
        <p>By accessing or using The Brix, you agree to be bound by these Terms. If you do not agree, please do not use our services.</p>
      </div>

      <div class="legal-block" id="service">
        <h2><span class="legal-num">02</span>Description of Service</h2>
        <p>The Brix provides tools to enhance Shopify cart functionality, including upsells, rewards tracking, analytics, and discount management.</p>
      </div>

      <div class="legal-block" id="eligibility">
        <h2><span class="legal-num">03</span>Eligibility</h2>
        <p>You must be at least 18 years old and legally capable of entering into a binding agreement to use our services.</p>
      </div>

      <div class="legal-block" id="account">
        <h2><span class="legal-num">04</span>Account &amp; Installation</h2>
        <ul class="legal-list">
          <li>You must provide accurate information when installing the app.</li>
          <li>You are responsible for maintaining your account security.</li>
          <li>You agree not to misuse or attempt to disrupt the service.</li>
        </ul>
      </div>

      <div class="legal-block" id="billing">
        <h2><span class="legal-num">05</span>Subscription &amp; Billing</h2>
        <ul class="legal-list">
          <li>Some features may require a paid subscription.</li>
          <li>Billing is handled via Shopify's billing system.</li>
          <li>Charges are non-refundable unless otherwise stated.</li>
        </ul>
      </div>

      <div class="legal-block" id="acceptable-use">
        <h2><span class="legal-num">06</span>Acceptable Use</h2>
        <p>You agree <strong>not</strong> to:</p>
        <ul class="legal-list legal-list-no">
          <li>Use the app for illegal or unauthorized purposes.</li>
          <li>Interfere with app functionality or security.</li>
          <li>Attempt to reverse engineer or copy the app.</li>
        </ul>
      </div>

      <div class="legal-block" id="ip">
        <h2><span class="legal-num">07</span>Intellectual Property</h2>
        <p>All content, branding, and technology related to The Brix are the property of The Brix and are protected by applicable laws.</p>
      </div>

      <div class="legal-block" id="liability">
        <h2><span class="legal-num">08</span>Limitation of Liability</h2>
        <p>The Brix is provided "as is" without warranties of any kind. We are not liable for:</p>
        <ul class="legal-list">
          <li>Loss of revenue or profits.</li>
          <li>Data loss.</li>
          <li>Business interruptions.</li>
        </ul>
      </div>

      <div class="legal-block" id="termination">
        <h2><span class="legal-num">09</span>Termination</h2>
        <p>We reserve the right to suspend or terminate access to our services at any time if these terms are violated.</p>
      </div>

      <div class="legal-block" id="third-party">
        <h2><span class="legal-num">10</span>Third-Party Services</h2>
        <p>Our app integrates with Shopify and other tools. We are not responsible for third-party services or disruptions.</p>
      </div>

      <div class="legal-block" id="changes">
        <h2><span class="legal-num">11</span>Changes to Terms</h2>
        <p>We may update these Terms at any time. Continued use of the service constitutes acceptance of the updated terms.</p>
      </div>

      <div class="legal-block" id="governing-law">
        <h2><span class="legal-num">12</span>Governing Law</h2>
        <p>These Terms shall be governed by and interpreted in accordance with applicable laws.</p>
      </div>

      <div class="legal-block" id="contact">
        <h2><span class="legal-num">13</span>Contact Information</h2>
        <p>For any questions regarding these Terms, get in touch. We're happy to help.</p>
        <div class="legal-contact">
          <div class="legal-contact-txt">
            <h3>Questions about these terms?</h3>
            <p>Reach our support team and we'll get back to you.</p>
          </div>
          <a class="btn btn-white" href="mailto:support@thebrix.io">support@thebrix.io</a>
        </div>
      </div>
    </div>

  </div>
</section>
<?php require BRIX_INCLUDES . '/footer.php'; ?>
