<?php
/**
 * /aov-calculator: the live AOV calculator and nothing else.
 *
 * The calculator is the one on /digital (js/aov-calculator.js carries the
 * same maths as js/calculator.js and js/wizard.js), rebuilt in the site's
 * own theme: a hero line, the calculator, then the footer. Full nav and
 * footer chrome, indexed, listed in sitemap.php, no lead form. The only
 * ask is the Shopify install button in the result panel.
 *
 * The example store is worked out here in PHP as well as in the script,
 * so the result panel is never blank before the script arrives.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once BRIX_INCLUDES . '/posts.php';

$page_title       = 'Free Shopify AOV Calculator: Find Your Missing Revenue';
$page_description = 'Free Shopify AOV calculator. Enter your average order value and monthly revenue to see the extra revenue a cart upsell app like Brix could add.';
$page_canonical   = 'aov-calculator';
$page_nav         = null;
$page_robots      = 'index, follow';
$footer_col3      = 'case-studies';
$page_scripts     = '<script src="/js/aov-calculator.js?v=' . ASSET_AOVCALC_VER . '"></script>';

/* The example store the calculator opens on. UPLIFT matches the constant
   in js/aov-calculator.js: the midpoint of the 17-20% lift. */
const AOV_UPLIFT = 0.185;
$example = ['aov' => 82, 'revenue' => 40000];

$missing    = $example['revenue'] * AOV_UPLIFT;
$new_aov    = $example['aov'] * (1 + AOV_UPLIFT);
$new_rev    = $example['revenue'] + $missing;
$money      = static fn (float|int $n): string => '$' . number_format(round($n));

/* (symbol, code) in the order the digital page lists them. */
$currencies = [
    ['$', 'USD'], ['C$', 'CAD'], ['€', 'EUR'], ['£', 'GBP'],
    ['A$', 'AUD'], ['S$', 'SGD'], ['₹', 'INR'],
];

/* Each block carries its own @context and is emitted as it stands, the
   same way about.php does it, so this page needs nothing beyond what
   header.php already provides. */
$page_schema = [
    [
        '@context'            => 'https://schema.org',
        '@type'               => 'WebApplication',
        'name'                => 'Brix AOV Calculator',
        'url'                 => SITE_URL . '/aov-calculator',
        'description'         => 'A free calculator that estimates the extra monthly revenue a Shopify store could add by raising its average order value.',
        'applicationCategory' => 'BusinessApplication',
        'operatingSystem'     => 'Any',
        'browserRequirements' => 'Requires JavaScript',
        'offers'              => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'USD'],
        'publisher'           => ['@id' => SITE_URL . '/#organization'],
    ],
    [
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => SITE_URL . '/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'AOV calculator'],
        ],
    ],
];

require BRIX_INCLUDES . '/header.php';
?>

<section class="page-hero">
  <div class="hero-glow" aria-hidden="true"></div>
  <div class="container">
    <p class="eyebrow reveal">Free AOV calculator</p>
    <h1 class="reveal" style="--d:.06s">What is your cart <em>leaving on the table?</em></h1>
    <p class="hero-sub reveal" style="--d:.12s">Enter your average order value and your monthly revenue. The calculator shows the extra revenue a smarter cart could add every month, from the shoppers you already have.</p>
  </div>
</section>

<!-- ============ THE CALCULATOR ============
     No .reveal on the card: the tool is the page, and it must never be
     sitting at opacity 0 because a script did not arrive. -->
<section class="section section-tight-top">
  <div class="container">
    <div class="calc-tool" id="aovTool">

      <div class="calc-tool-in">
        <div class="calc-tool-head">
          <h2>Your store numbers</h2>
          <span class="calc-tool-hint">Change any number and the estimate updates</span>
        </div>

        <div class="calc-field">
          <label id="aovCurLabel">Currency</label>
          <div class="calc-chips" role="group" aria-labelledby="aovCurLabel">
<?php foreach ($currencies as $i => [$sym, $code]): ?>
            <button type="button" class="calc-chip" data-sym="<?= e($sym) ?>" aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>"><?= e($sym) ?> <?= e($code) ?></button>
<?php endforeach; ?>
          </div>
        </div>

        <div class="calc-field">
          <label for="aovInput">Average order value</label>
          <div class="calc-input"><span class="calc-sym">$</span><input type="number" id="aovInput" inputmode="decimal" min="0" step="any" value="<?= (int) $example['aov'] ?>" placeholder="82"></div>
        </div>
        <div class="calc-field">
          <label for="aovRevenue">Monthly revenue</label>
          <div class="calc-input"><span class="calc-sym">$</span><input type="number" id="aovRevenue" inputmode="decimal" min="0" step="any" value="<?= (int) $example['revenue'] ?>" placeholder="40000"></div>
        </div>
        <div class="calc-field">
          <label for="aovAdSpend">Ad spend per month <span>optional</span></label>
          <div class="calc-input"><span class="calc-sym">$</span><input type="number" id="aovAdSpend" inputmode="decimal" min="0" step="any" placeholder="6000"></div>
        </div>
      </div>

      <div class="calc-out">
        <div class="calc-out-top">
          <span class="calc-out-label">You could be missing</span>
          <span class="calc-live">Live</span>
        </div>
        <p class="calc-out-fig" id="aovFig" aria-live="polite"><span id="aovMissing"><?= e($money($missing)) ?></span><small>/month</small></p>
        <div class="calc-out-row"><span>New AOV</span><b id="aovNewAov"><?= e($money($new_aov)) ?></b></div>
        <div class="calc-out-row"><span>New monthly revenue</span><b class="calc-up" id="aovNewRevenue"><?= e($money($new_rev)) ?></b></div>
        <p class="calc-out-note" id="aovNote">This is an example store. Change the numbers to see yours.</p>
        <a class="btn btn-white" href="<?= e(SHOPIFY_APP_URL) ?>" target="_blank" rel="noopener">Install Brix free on Shopify</a>
      </div>

    </div>
  </div>
</section>

<?php require BRIX_INCLUDES . '/footer.php'; ?>
