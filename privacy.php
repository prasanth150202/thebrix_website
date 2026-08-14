<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once BRIX_INCLUDES . '/posts.php';

$page_title       = 'Privacy Policy | Brix for Shopify';
$page_description = 'How The Brix collects, uses, and safeguards your data across our website and Shopify app: what we collect, how we use it, sharing, cookies, security and your rights.';
$page_canonical   = 'privacy';
$page_nav         = NULL;
$page_robots      = 'index, follow';
$footer_col3      = 'case-studies';

require BRIX_INCLUDES . '/header.php';
?>

<section class="page-hero">
  <div class="hero-glow" aria-hidden="true"></div>
  <div class="container">
    <p class="eyebrow reveal">Legal</p>
    <h1 class="reveal" style="--d:.06s">Privacy <em>Policy</em></h1>
    <p class="hero-sub reveal" style="--d:.12s">We value your privacy and are committed to protecting your personal information. Here's how we collect, use, and safeguard your data.</p>
    <div class="legal-switch reveal" style="--d:.18s" role="tablist" aria-label="Legal documents">
      <a href="terms">Terms &amp; Conditions</a>
      <a href="privacy" class="is-active" aria-current="page">Privacy Policy</a>
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
      <nav aria-label="Privacy sections">
        <a href="#collect">1. Information We Collect</a>
        <a href="#use">2. How We Use Your Information</a>
        <a href="#sharing">3. Data Sharing</a>
        <a href="#cookies">4. Cookies &amp; Tracking</a>
        <a href="#security">5. Data Security</a>
        <a href="#retention">6. Data Retention</a>
        <a href="#rights">7. Your Rights</a>
        <a href="#third-party">8. Third-Party Services</a>
        <a href="#changes">9. Changes to This Policy</a>
        <a href="#contact">10. Contact Us</a>
      </nav>
    </aside>

    <div class="legal-doc">
      <p class="legal-intro">At <strong>The Brix</strong>, we value your privacy and are committed to protecting your personal information. This Privacy Policy explains how we collect, use, and safeguard your data when you use our website and Shopify application.</p>

      <div class="legal-block" id="collect">
        <h2><span class="legal-num">01</span>Information We Collect</h2>
        <p>We may collect the following types of information:</p>
        <h3>a. Personal Information</h3>
        <ul class="legal-list">
          <li>Name</li>
          <li>Email address</li>
          <li>Contact details</li>
        </ul>
        <h3>b. Store &amp; Usage Data (for Shopify merchants)</h3>
        <ul class="legal-list">
          <li>Store name and URL</li>
          <li>Installed app data</li>
          <li>Cart interactions and usage behavior</li>
          <li>Analytics related to app performance</li>
        </ul>
        <h3>c. Technical Data</h3>
        <ul class="legal-list">
          <li>IP address</li>
          <li>Browser type and device information</li>
          <li>Cookies and tracking technologies</li>
        </ul>
      </div>

      <div class="legal-block" id="use">
        <h2><span class="legal-num">02</span>How We Use Your Information</h2>
        <p>We use your information to:</p>
        <ul class="legal-list">
          <li>Provide and operate our services.</li>
          <li>Improve app functionality and user experience.</li>
          <li>Analyze performance and usage trends.</li>
          <li>Communicate updates, support and important notices.</li>
          <li>Prevent fraud and ensure security.</li>
        </ul>
      </div>

      <div class="legal-block" id="sharing">
        <h2><span class="legal-num">03</span>Data Sharing</h2>
        <p>We do <strong>not sell your personal data</strong>. We may share information only in the following cases:</p>
        <ul class="legal-list">
          <li>With Shopify, as required for app functionality.</li>
          <li>With trusted third-party service providers (analytics, hosting).</li>
          <li>When required by law or legal processes.</li>
        </ul>
      </div>

      <div class="legal-block" id="cookies">
        <h2><span class="legal-num">04</span>Cookies &amp; Tracking</h2>
        <p>We use cookies and similar technologies to:</p>
        <ul class="legal-list">
          <li>Enhance user experience.</li>
          <li>Track usage and performance.</li>
          <li>Store preferences.</li>
        </ul>
        <p class="legal-note">You can disable cookies in your browser settings, but some features may not function properly.</p>
      </div>

      <div class="legal-block" id="security">
        <h2><span class="legal-num">05</span>Data Security</h2>
        <p>We implement industry-standard security measures to protect your data. However, no method of transmission over the internet is 100% secure.</p>
      </div>

      <div class="legal-block" id="retention">
        <h2><span class="legal-num">06</span>Data Retention</h2>
        <p>We retain your data only as long as necessary to provide our services or comply with legal obligations.</p>
      </div>

      <div class="legal-block" id="rights">
        <h2><span class="legal-num">07</span>Your Rights</h2>
        <p>Depending on your location, you may have the right to:</p>
        <ul class="legal-list">
          <li>Access your personal data.</li>
          <li>Request correction or deletion.</li>
          <li>Withdraw consent.</li>
        </ul>
        <p>To exercise these rights, contact us at <a href="mailto:support@thebrix.io">support@thebrix.io</a>.</p>
      </div>

      <div class="legal-block" id="third-party">
        <h2><span class="legal-num">08</span>Third-Party Services</h2>
        <p>Our app may integrate with third-party tools (like Shopify). We are not responsible for their privacy practices.</p>
      </div>

      <div class="legal-block" id="changes">
        <h2><span class="legal-num">09</span>Changes to This Policy</h2>
        <p>We may update this Privacy Policy from time to time. Changes will be posted on this page with an updated effective date.</p>
      </div>

      <div class="legal-block" id="contact">
        <h2><span class="legal-num">10</span>Contact Us</h2>
        <p>If you have any questions about this Privacy Policy, get in touch. We're happy to help.</p>
        <div class="legal-contact">
          <div class="legal-contact-txt">
            <h3>Questions about your privacy?</h3>
            <p>Reach our support team and we'll get back to you.</p>
          </div>
          <a class="btn btn-white" href="mailto:support@thebrix.io">support@thebrix.io</a>
        </div>
      </div>
    </div>

  </div>
</section>
<?php require BRIX_INCLUDES . '/footer.php'; ?>
