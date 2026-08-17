<?php
/**
 * sitemap.xml, generated from the database.
 *
 * .htaccess maps /sitemap.xml onto this file, so the URL Google
 * already knows about is unchanged. Publishing a post adds it here
 * automatically; unpublishing or deleting one removes it.
 *
 * The section grouping from the hand-maintained file is kept, because
 * it makes the output readable when you open it yourself.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once BRIX_INCLUDES . '/posts.php';

header('Content-Type: application/xml; charset=utf-8');

/** Static pages, with the priorities the previous sitemap set. */
$static = [
    ['loc' => '',                 'freq' => 'weekly',  'pri' => '1.0'],
    ['loc' => 'features',         'freq' => 'monthly', 'pri' => '0.9'],
    ['loc' => 'pricing',          'freq' => 'monthly', 'pri' => '0.9'],
    ['loc' => 'case-studies'     ,'freq' => 'monthly', 'pri' => '0.8'],
    ['loc' => 'blog',             'freq' => 'weekly',  'pri' => '0.8'],
    ['loc' => 'how-to',           'freq' => 'monthly', 'pri' => '0.7'],
    ['loc' => 'contact',          'freq' => 'yearly',  'pri' => '0.5'],
    ['loc' => 'terms',            'freq' => 'yearly',  'pri' => '0.3'],
    ['loc' => 'privacy',          'freq' => 'yearly',  'pri' => '0.3'],
];

$blog = $cases = [];
try {
    $blog  = get_published_posts('blog');
    $cases = get_published_posts('case_study');
} catch (Throwable) {
    // Still emit the static half rather than serving a broken sitemap.
}

/** Most recent post date, used as lastmod for the listing pages. */
$latest = static function (array $posts): string {
    return $posts ? (string) $posts[0]['date_published'] : date('Y-m-d');
};

$lastmodFor = [
    ''                  => date('Y-m-d'),
    'blog'              => $latest($blog),
    'case-studies'      => $latest($cases),
];

$url = static function (string $loc, string $lastmod, string $freq, string $pri): void {
    echo "  <url>\n";
    echo '    <loc>' . e(SITE_URL . '/' . $loc) . "</loc>\n";
    echo '    <lastmod>' . e($lastmod) . "</lastmod>\n";
    echo '    <changefreq>' . $freq . "</changefreq>\n";
    echo '    <priority>' . $pri . "</priority>\n";
    echo "  </url>\n";
};

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n\n";

echo "  <!-- Core pages -->\n";
foreach ($static as $p) {
    $url($p['loc'], $lastmodFor[$p['loc']] ?? date('Y-m-d'), $p['freq'], $p['pri']);
}

if ($cases) {
    echo "\n  <!-- Case studies -->\n";
    foreach ($cases as $p) {
        $url(ltrim(post_url($p), '/'), (string) $p['date_published'], 'monthly', '0.7');
    }
}

if ($blog) {
    echo "\n  <!-- Blog -->\n";
    foreach ($blog as $p) {
        $url(ltrim(post_url($p), '/'), (string) $p['date_published'], 'monthly', '0.7');
    }
}

echo "\n</urlset>\n";
