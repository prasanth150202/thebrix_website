<?php
/**
 * The video course behind /tutorials.
 *
 * One ordered list of lessons plus the modules they group into. The
 * page, the JSON-LD and the footer column all read from here, so
 * adding a video to the course is one entry in brix_tutorials() and
 * nothing else.
 *
 * `seconds` and `published` are the real values from YouTube. They are
 * what the VideoObject markup needs, and Google drops a video result
 * that has no duration or upload date, so keep them accurate when the
 * list changes.
 *
 * Nothing in here emits output.
 */

declare(strict_types=1);

/**
 * Module headings, in order. The keys are what a lesson's `module`
 * points at; a lesson naming a module that is not here would simply
 * never be rendered, so the two lists have to stay in step.
 */
function brix_tutorial_modules(): array
{
    return [
        'start'   => ['label' => 'Getting started'],
        'build'   => ['label' => 'Build your offers'],
        'measure' => ['label' => 'Measure and improve'],
    ];
}

/**
 * The lessons, in watch order.
 *
 *   id         YouTube video id
 *   title      short lesson name, used everywhere on the page
 *   yt_title   the video's real title on YouTube, used for VideoObject
 *   blurb      one sentence under the player, and the schema description
 *   seconds    runtime
 *   published  upload date, YYYY-MM-DD
 *   module     key from brix_tutorial_modules()
 *   guide      optional link to the written version of the same thing
 */
function brix_tutorials(): array
{
    return [
        [
            'id'        => 'Ap1wvi6FN9I',
            'title'     => 'How Brix works',
            'yt_title'  => 'How BRIX Works | Shopify Cart & AOV Optimization App',
            'blurb'     => 'A two-minute tour of the whole app: reward tiers, cart upsells, bundles and the analytics that tie them together. Start here if Brix is new to you.',
            'seconds'   => 130,
            'published' => '2026-07-11',
            'module'    => 'start',
            'guide'     => ['href' => '/features', 'label' => 'See all features'],
        ],
        [
            'id'        => 'QmiqbSUgDrw',
            'title'     => 'Customize your cart',
            'yt_title'  => 'How to Customize Your Shopify Cart with BRIX Cart Editor?',
            'blurb'     => 'Design how your Shopify cart drawer looks and works in the Cart Editor, with settings on the left and a live preview on the right.',
            'seconds'   => 59,
            'published' => '2026-07-31',
            'module'    => 'build',
            'guide'     => ['href' => '/how-to#cart', 'label' => 'Read the written guide'],
        ],
        [
            'id'        => 'o70b1FMW4v4',
            'title'     => 'Frequently Bought Together',
            'yt_title'  => 'How to Set Up Frequently Bought Together in BRIX | Shopify FBT Tutorial (2026)',
            'blurb'     => 'Recommend related products right inside the cart drawer: pick a template, choose manual, automatic or AI-powered picks, then enable the widget in your theme.',
            'seconds'   => 79,
            'published' => '2026-07-31',
            'module'    => 'build',
            'guide'     => ['href' => '/how-to#fbt', 'label' => 'Read the written guide'],
        ],
        [
            'id'        => 'f7RGtknTVO4',
            'title'     => 'Build a bundle page',
            'yt_title'  => 'How to Create a Bundle Page in BRIX | Shopify Bundle Builder Tutorial',
            'blurb'     => 'Create a dedicated Combo page so customers can pick several products together without hunting for the right combination.',
            'seconds'   => 87,
            'published' => '2026-07-31',
            'module'    => 'build',
            'guide'     => ['href' => '/how-to#combo', 'label' => 'Read the written guide'],
        ],
        [
            'id'        => 'xIogTy1RM-0',
            'title'     => 'Work with Brix AI',
            'yt_title'  => 'How to Use BRIX AI | Write & Work Smarter for Your Shopify Store',
            'blurb'     => 'Let Brix AI write your copy and suggest recommendations, right where you are already working.',
            'seconds'   => 114,
            'published' => '2026-07-31',
            'module'    => 'build',
            'guide'     => ['href' => '/how-to#ai', 'label' => 'Read the written guide'],
        ],
        [
            'id'        => 'l6_m_kGPx9M',
            'title'     => 'Set up a coupon banner',
            'yt_title'  => 'How to Set Up a Coupon Banner in BRIX | Shopify Coupon Banner Tutorial',
            'blurb'     => 'Show your promo codes on the product page, near the Add to Cart button, so nobody leaves to go hunting for a discount.',
            'seconds'   => 74,
            'published' => '2026-07-31',
            'module'    => 'build',
            'guide'     => ['href' => '/how-to#coupon', 'label' => 'Read the written guide'],
        ],
        [
            'id'        => 'FfVLZMmE1PM',
            'title'     => 'Read your analytics',
            'yt_title'  => 'How to Analyze BRIX Analytics | Track Revenue, AOV & Cart Performance',
            'blurb'     => 'Find out which tier, which upsell and which bundle earned each extra dollar, and what the insight cards say you should change next.',
            'seconds'   => 78,
            'published' => '2026-07-31',
            'module'    => 'measure',
            'guide'     => ['href' => '/features#analytics', 'label' => 'See how attribution works'],
        ],
    ];
}

/** 130 -> "2:10". Runtimes here are minutes, so no hour component. */
function brix_tutorial_clock(int $seconds): string
{
    return sprintf('%d:%02d', intdiv($seconds, 60), $seconds % 60);
}

/** 130 -> "PT2M10S", the form schema.org duration expects. */
function brix_tutorial_iso_duration(int $seconds): string
{
    return sprintf('PT%dM%dS', intdiv($seconds, 60), $seconds % 60);
}

/** Total runtime of the course, rounded up to whole minutes. */
function brix_tutorial_total_minutes(): int
{
    $total = array_sum(array_column(brix_tutorials(), 'seconds'));
    return (int) ceil($total / 60);
}
