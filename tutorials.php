<?php
/**
 * The video tutorials course.
 *
 * A player on the left and the syllabus on the right: picking a lesson
 * swaps the video without a page load, and ticking one off keeps the
 * player moving through the course.
 *
 * The lessons themselves live in includes/tutorials.php, which is also
 * what the footer column and the VideoObject markup below read, so the
 * course is described in exactly one place.
 *
 * Progress is per-browser (localStorage, written by js/tutorials.js).
 * Nothing here needs the database, so this page stays up even when a
 * database problem would take the blog down.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once BRIX_INCLUDES . '/tutorials.php';

$page_title       = 'Brix tutorials: Learn the app in 11 minutes of video';
$page_description = 'A free seven-lesson video course for Brix on Shopify: the cart editor, Frequently Bought Together, bundle pages, Brix AI, coupon banners and analytics. Watch in order, tick each lesson off as you go.';
$page_canonical   = 'tutorials';
$page_nav         = 'tutorials';
$footer_col3      = 'tutorials';
$page_scripts     = '<script src="/js/tutorials.js?v=' . ASSET_TUTORIALS_VER . '"></script>';

$lessons  = brix_tutorials();
$modules  = brix_tutorial_modules();
$total    = count($lessons);
$minutes  = brix_tutorial_total_minutes();
$first    = $lessons[0];

/** Lessons grouped for the rail, each keeping its position in the course. */
$grouped = [];
foreach ($lessons as $i => $lesson) {
    $grouped[$lesson['module']][] = ['i' => $i] + $lesson;
}

/** Newest upload, shown as the course's "last updated". */
$updated = max(array_column($lessons, 'published'));

/**
 * What js/tutorials.js needs to swap lessons. Emitted as a JSON block
 * rather than data- attributes so the blurbs keep their punctuation
 * without three rounds of escaping.
 */
$lesson_data = [];
foreach ($lessons as $i => $lesson) {
    $lesson_data[] = [
        'i'      => $i,
        'id'     => $lesson['id'],
        'title'  => $lesson['title'],
        'blurb'  => $lesson['blurb'],
        'clock'  => brix_tutorial_clock($lesson['seconds']),
        'module' => $modules[$lesson['module']]['label'],
        'guide'  => $lesson['guide'] ?? null,
    ];
}

/**
 * Video rich results. Google drops a VideoObject with no duration or
 * upload date, which is why includes/tutorials.php carries the real
 * numbers rather than approximations.
 */
$schema = [
    '@context'        => 'https://schema.org',
    '@type'           => 'ItemList',
    'name'            => 'Brix video tutorials',
    'itemListElement' => [],
];
foreach ($lessons as $i => $lesson) {
    $schema['itemListElement'][] = [
        '@type'    => 'ListItem',
        'position' => $i + 1,
        'item'     => [
            '@type'        => 'VideoObject',
            'name'         => $lesson['yt_title'],
            'description'  => $lesson['blurb'],
            'thumbnailUrl' => 'https://i.ytimg.com/vi/' . $lesson['id'] . '/maxresdefault.jpg',
            'uploadDate'   => $lesson['published'],
            'duration'     => brix_tutorial_iso_duration($lesson['seconds']),
            'contentUrl'   => 'https://www.youtube.com/watch?v=' . $lesson['id'],
            'embedUrl'     => 'https://www.youtube.com/embed/' . $lesson['id'],
            'publisher'    => [
                '@type' => 'Organization',
                'name'  => 'Brix',
                'url'   => SITE_URL,
            ],
        ],
    ];
}

require BRIX_INCLUDES . '/header.php';
?>

<section class="page-hero">
  <div class="hero-glow" aria-hidden="true"></div>
  <div class="container">
    <p class="eyebrow reveal">Brix video tutorials</p>
    <h1 class="reveal" style="--d:.06s">Learn Brix, <em>one short video at a time</em></h1>
    <p class="hero-sub reveal" style="--d:.12s">A free course covering every part of the app, from the cart editor to attribution. Watch it in order, or jump straight to the feature you are setting up. Every lesson is under two&nbsp;minutes.</p>
    <ul class="tut-facts reveal" style="--d:.18s">
      <li>
        <span class="tut-fact-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="15" rx="3"/><path d="m10 9 5 2.5-5 2.5z" fill="currentColor" stroke="none"/></svg></span>
        <b><?= $total ?></b> lessons
      </li>
      <li>
        <span class="tut-fact-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15.5 14"/></svg></span>
        <b><?= $minutes ?></b> minutes total
      </li>
      <li>
        <span class="tut-fact-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>
        Free &middot; no signup
      </li>
      <li>
        <span class="tut-fact-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4.5" width="18" height="16" rx="3"/><path d="M8 2.5v4M16 2.5v4M3 10h18"/></svg></span>
        Updated <?= e(date('F Y', strtotime($updated))) ?>
      </li>
    </ul>
  </div>
</section>

<section class="section tut-section">
  <div class="container">
    <div class="tut-layout" id="tutCourse">

      <div class="tut-main">

        <!-- The player starts as a still frame with a play button. The
             YouTube iframe is a third of a megabyte and three more
             connections, so it is built on the first real click rather
             than on every page view; js/tutorials.js swaps it in. -->
        <div class="tut-stage">
          <div class="tut-screen" id="tutScreen">
            <button class="tut-poster" id="tutPoster" type="button">
              <img class="tut-poster-img" id="tutPosterImg"
                   src="https://i.ytimg.com/vi/<?= e($first['id']) ?>/maxresdefault.jpg"
                   alt="" width="1280" height="720" fetchpriority="high">
              <span class="tut-poster-veil" aria-hidden="true"></span>
              <span class="tut-play" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13l11-6.5z"/></svg>
              </span>
              <span class="tut-poster-dur" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13l11-6.5z"/></svg>
                <span id="tutPosterDur"><?= e(brix_tutorial_clock($first['seconds'])) ?></span>
              </span>
              <?php /* the button's whole accessible name, kept in step by js/tutorials.js */ ?>
              <span class="tut-sr" id="tutPosterName">Play: <?= e($first['title']) ?></span>
            </button>
          </div>
        </div>

        <div class="tut-now">
          <div class="tut-now-head">
            <div>
              <p class="tut-now-eyebrow" id="tutNowEyebrow">
                <?= e($modules[$first['module']]['label']) ?> &middot; Lesson 1 of <?= $total ?>
              </p>
              <h2 class="tut-now-title" id="tutNowTitle"><?= e($first['title']) ?></h2>
            </div>
            <div class="tut-skip">
              <button class="tut-skip-btn" id="tutPrev" type="button" aria-label="Previous lesson" disabled>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 5 8 12 15 19"/></svg>
              </button>
              <button class="tut-skip-btn" id="tutNext" type="button" aria-label="Next lesson">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 5 16 12 9 19"/></svg>
              </button>
            </div>
          </div>

          <p class="tut-now-blurb" id="tutNowBlurb"><?= e($first['blurb']) ?></p>

          <div class="tut-now-actions">
            <button class="btn btn-primary" id="tutComplete" type="button">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg>
              <span id="tutCompleteLabel">Mark complete &amp; continue</span>
            </button>
<?php if (!empty($first['guide'])): ?>
            <a class="tut-now-link" id="tutGuide" href="<?= e($first['guide']['href']) ?>"><?= e($first['guide']['label']) ?> &rarr;</a>
<?php else: ?>
            <a class="tut-now-link" id="tutGuide" href="/how-to" hidden>Read the written guide &rarr;</a>
<?php endif; ?>
          </div>

          <!-- Announced to screen readers when the lesson changes, since
               swapping the player is otherwise a silent update. -->
          <p class="tut-live" id="tutLive" role="status" aria-live="polite"></p>
        </div>
      </div>

      <aside class="tut-rail">
        <div class="tut-rail-in">

          <div class="tut-progress">
            <div class="tut-progress-top">
              <p class="tut-progress-h">Course content</p>
              <span class="tut-progress-n" id="tutCount">0 / <?= $total ?></span>
            </div>
            <div class="tut-bar"><i id="tutBar" style="width:0%"></i></div>
            <p class="tut-progress-note" id="tutNote">Tick a lesson off when you have watched it. Progress is remembered on this device.</p>
          </div>

          <ol class="tut-modules">
<?php $mi = 0; foreach ($modules as $key => $module): ?>
<?php if (empty($grouped[$key])) { continue; } $mi++; ?>
            <li class="tut-module">
              <p class="tut-module-h">
                <span class="tut-module-n">Module <?= $mi ?></span>
                <?= e($module['label']) ?>
              </p>
              <ul class="tut-list">
<?php foreach ($grouped[$key] as $lesson): ?>
                <li class="tut-row<?= $lesson['i'] === 0 ? ' is-playing' : '' ?>" data-row="<?= $lesson['i'] ?>">
                  <input class="tut-check" type="checkbox" id="tutChk<?= $lesson['i'] ?>" data-check="<?= $lesson['i'] ?>">
                  <label class="tut-check-box" for="tutChk<?= $lesson['i'] ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                    <span class="tut-sr">Mark &ldquo;<?= e($lesson['title']) ?>&rdquo; complete</span>
                  </label>
                  <button class="tut-open" type="button" data-play="<?= $lesson['i'] ?>"<?= $lesson['i'] === 0 ? ' aria-current="true"' : '' ?>>
                    <span class="tut-open-title"><?= e($lesson['title']) ?></span>
                    <span class="tut-open-meta">
                      <span class="tut-open-state" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13l11-6.5z"/></svg>
                      </span>
                      <?= e(brix_tutorial_clock($lesson['seconds'])) ?>
                    </span>
                  </button>
                </li>
<?php endforeach; ?>
              </ul>
            </li>
<?php endforeach; ?>
          </ol>

          <div class="tut-rail-foot">
            <button class="tut-reset" id="tutReset" type="button" hidden>Reset progress</button>
            <a class="tut-rail-yt" href="https://www.youtube.com/@TheBrixApp" target="_blank" rel="noopener">
              <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16" aria-hidden="true"><path d="M10 15l5.19-3L10 9v6m11.56-7.83c.13.47.22 1.1.28 1.9.07.8.1 1.49.1 2.09L22 12c0 2.19-.16 3.8-.44 4.83-.25.9-.83 1.48-1.73 1.73-.47.13-1.33.22-2.65.28-1.3.07-2.49.1-3.59.1L12 19c-4.19 0-6.8-.16-7.83-.44-.9-.25-1.48-.83-1.73-1.73-.13-.47-.22-1.1-.28-1.9-.07-.8-.1-1.49-.1-2.09L2 12c0-2.19.16-3.8.44-4.83.25-.9.83-1.48 1.73-1.73.47-.13 1.33-.22 2.65-.28 1.3-.07 2.49-.1 3.59-.1L12 5c4.19 0 6.8.16 7.83.44.9.25 1.48.83 1.73 1.73z"/></svg>
              More on YouTube
            </a>
          </div>
        </div>
      </aside>
    </div>

    <noscript>
      <div class="tut-noscript">
        <p><b>The player needs JavaScript.</b> Every lesson is on YouTube, so here they are as plain links:</p>
        <ul>
<?php foreach ($lessons as $lesson): ?>
          <li><a href="https://www.youtube.com/watch?v=<?= e($lesson['id']) ?>" target="_blank" rel="noopener"><?= e($lesson['title']) ?></a> &middot; <?= e(brix_tutorial_clock($lesson['seconds'])) ?></li>
<?php endforeach; ?>
        </ul>
      </div>
    </noscript>
  </div>
</section>

<section class="section section-soft">
  <div class="container">
    <div class="tut-read">
      <div class="tut-read-copy">
        <p class="eyebrow">Prefer to read?</p>
        <h2>The same steps, written down</h2>
        <p class="tut-read-sub">Every lesson has a written walkthrough with screenshots, so you can follow along with the app open in the next tab.</p>
      </div>
      <div class="tut-read-actions">
        <a class="btn btn-primary" href="/how-to">Open the how-to guides</a>
        <a class="btn btn-ghost" href="/features">Browse the features</a>
      </div>
    </div>
  </div>
</section>

<section class="cta-final" id="install">
  <div class="cta-gradient" aria-hidden="true"></div>
  <div class="container cta-in">
    <h2 class="reveal">Ready to try it yourself?</h2>
    <p class="reveal" style="--d:.08s">Install free, follow lesson one, and have your first offer live today.</p>
    <a class="btn btn-white btn-lg reveal" style="--d:.16s" href="<?= e(SHOPIFY_APP_URL) ?>" target="_blank" rel="noopener" id="ctaInstall">Install on Shopify for free</a>
    <p class="cta-note reveal" style="--d:.24s">Free plan available &middot; No credit card</p>
  </div>
</section>

<script type="application/json" id="tutData"><?= json_encode($lesson_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>

<?php require BRIX_INCLUDES . '/footer.php'; ?>
