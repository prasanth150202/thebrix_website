<?php
/**
 * Shared page head and navigation.
 *
 * Set these before including:
 *   $page_title       <title> text
 *   $page_description meta description
 *   $page_canonical   path relative to the site root, e.g. "pricing.html"
 *   $page_nav         which nav item is active: features|pricing|case-studies|blog|how-to|tutorials|null
 *   $page_robots      optional meta robots value
 *   $page_body_class  optional extra class on <body>
 *   $page_chrome      'full' (default) or 'minimal'. A campaign landing
 *                     page sets 'minimal': the bar keeps the logo and the
 *                     install button and drops the nav links, the burger
 *                     and the drawer, so the only way off the page is the
 *                     one the campaign is paying for.
 *
 * The markup below is the header that was already on every page, with
 * only the varying parts pulled out into those variables.
 */

declare(strict_types=1);

$page_title       = $page_title       ?? 'Brix | AI Cart Upsell & AOV Booster App for Shopify';
$page_description = $page_description ?? '';
$page_canonical   = $page_canonical   ?? '';
$page_nav         = $page_nav         ?? null;
$page_robots      = $page_robots      ?? null;
$page_body_class  = $page_body_class  ?? '';
$page_chrome      = $page_chrome      ?? 'full';

/**
 * Mirror the robots directive as a header as well as a meta tag. The tag
 * only works on a crawler that parses the HTML; the header is read
 * before that, and it still applies if the page is fetched some other
 * way. Guarded because the admin preview has already sent output.
 */
if ($page_robots !== null && !headers_sent()) {
    header('X-Robots-Tag: ' . $page_robots);
}

$nav_items = [
    'features'     => ['href' => '/features',     'label' => 'Features'],
    'pricing'      => ['href' => '/pricing',      'label' => 'Pricing'],
    'case-studies' => ['href' => '/case-studies', 'label' => 'Case studies'],
    'blog'         => ['href' => '/blog',         'label' => 'Blog'],
    'how-to'       => ['href' => '/how-to',       'label' => 'Guides'],
    'tutorials'    => ['href' => '/tutorials',    'label' => 'Tutorials'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php if (!empty($page_base)): ?>
  <!-- The admin preview lives at /admin/preview.php but renders the
       public template, whose asset and nav paths are root-relative.
       This has to come before the first relative URL in the head. -->
  <base href="<?= e($page_base) ?>">
<?php endif; ?>
  <!-- Google Tag Manager -->
  <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
  new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
  j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
  'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
  })(window,document,'script','dataLayer','GTM-M27VLZJ4');</script>
  <!-- End Google Tag Manager -->
  <!-- Google tag (gtag.js) -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-23RTZ99F2K"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-23RTZ99F2K');
  </script>
  <script src="/js/utm.js?v=<?= ASSET_UTM_VER ?>"></script>
  <!-- Microsoft Clarity -->
  <script type="text/javascript">
    (function(c,l,a,r,i,t,y){
        c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
        t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
        y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
    })(window, document, "clarity", "script", "xprwd9oi74");
  </script>
  <!-- End Microsoft Clarity -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="canonical" href="<?= e(SITE_URL . '/' . ltrim($page_canonical, '/')) ?>">
  <title><?= e($page_title) ?></title>
  <meta name="description" content="<?= e($page_description) ?>">
<?php if ($page_robots !== null): ?>
  <meta name="robots" content="<?= e($page_robots) ?>">
<?php endif; ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/css/styles.css?v=<?= ASSET_CSS_VER ?>">
  <link rel="icon" type="image/png" href="/assets/favicon.png?v=2">
</head>
<body<?= $page_body_class !== '' ? ' class="' . e($page_body_class) . '"' : '' ?>>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-M27VLZJ4"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<header class="nav<?= $page_chrome === 'minimal' ? ' nav-lite' : '' ?>" id="nav">
  <div class="container nav-in">
    <a class="logo" href="/" aria-label="Brix home">
      <img class="logo-img" src="/assets/brix-logo-dark.png" alt="Brix">
    </a>
<?php if ($page_chrome === 'minimal'): ?>
    <div class="nav-actions">
      <a class="btn btn-primary btn-sm" href="<?= e(SHOPIFY_APP_URL) ?>" target="_blank" rel="noopener">Install free</a>
    </div>
  </div>
</header>
<?php else: ?>
    <nav class="nav-links" aria-label="Main">
<?php foreach ($nav_items as $key => $item): ?>
      <a href="<?= $item['href'] ?>"<?= $page_nav === $key ? ' class="active"' : '' ?>><?= $item['label'] ?></a>
<?php endforeach; ?>
    </nav>
    <div class="nav-actions">
      <a class="btn btn-primary btn-sm" href="<?= e(SHOPIFY_APP_URL) ?>" target="_blank" rel="noopener">Install free</a>
      <button class="nav-burger" id="navBurger" aria-label="Open menu" aria-expanded="false" aria-controls="navDrawer">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
  <div class="nav-drawer" id="navDrawer">
<?php foreach ($nav_items as $item): ?>
    <a href="<?= $item['href'] ?>"><?= $item['label'] ?></a>
<?php endforeach; ?>
    <a class="btn btn-primary" href="<?= e(SHOPIFY_APP_URL) ?>" target="_blank" rel="noopener">Install free</a>
  </div>
</header>
<?php endif; ?>

<main>
