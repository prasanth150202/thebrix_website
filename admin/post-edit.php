<?php
/**
 * Create and edit posts.
 *
 * Saving works the way staging and committing does. Everything is a
 * draft until Publish is pressed:
 *
 *   - a new post starts as a draft and is never public,
 *   - autosave (every minute, and whenever Preview is opened) writes
 *     to the draft only,
 *   - editing a post that is already live does NOT change the live
 *     page. The edits are held in draft_payload until Publish.
 *
 * That means you can leave a half-finished rewrite of a published
 * article sitting in the editor for a week and the public page carries
 * on showing the last published version.
 *
 * The markdown body can arrive three ways, all of which end up in the
 * same textarea before saving:
 *   - typed or pasted directly,
 *   - loaded from a .md file by the browser, which then clears the
 *     file input so a later edit in the textarea is not overwritten,
 *   - read from the upload server-side, for the no-JavaScript case.
 */

declare(strict_types=1);

require_once __DIR__ . '/_boot.php';

$user = require_admin();

$id   = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$post = $id > 0 ? get_post($id) : null;

if ($id > 0 && $post === null) {
    flash('That post no longer exists.', 'err');
    redirect('index.php');
}

// Work against the staged version, so reopening the editor shows what
// you last typed rather than what the public currently sees.
$editing = $post !== null ? post_with_draft($post) : null;
$hasPending = $post !== null && post_has_pending_changes($post);

$errors = [];

/** Defaults for a new post. */
$form = [
    'type'             => 'blog',
    'title'            => '',
    'slug'             => '',
    'author'           => $user['display_name'] ?: 'Admin',
    'category'         => '',
    'hero_subtitle'    => '',
    'hero_image'       => '',
    'hero_blur'        => '0',
    'excerpt'          => '',
    'body_md'          => '',
    'read_minutes'     => '',
    'date_published'   => date('Y-m-d'),
    'card_gradient'    => 'bshot-1',
    'card_icon'        => 'cart',
    'cta_heading'      => '',
    'cta_sub'          => '',
    'meta_title'       => '',
    'meta_description' => '',
    'status'           => 'draft',
];

if ($editing !== null) {
    foreach ($form as $k => $_) {
        if (array_key_exists($k, $editing)) {
            $form[$k] = (string) $editing[$k];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $errors[] = 'Your session expired. Nothing was saved. Copy your text, reload, and try again.';
    }

    foreach ($form as $k => $_) {
        if (isset($_POST[$k])) {
            $form[$k] = is_string($_POST[$k]) ? trim($_POST[$k]) : $form[$k];
        }
    }

    // No-JavaScript path: take the body straight from the upload. A
    // file produced by post-download.php carries a front matter block,
    // so strip it and let it fill in anything left blank on the form.
    if (!empty($_FILES['md_file']['tmp_name']) && is_uploaded_file($_FILES['md_file']['tmp_name'])) {
        $uploaded = file_get_contents($_FILES['md_file']['tmp_name']);
        if ($uploaded !== false && trim($uploaded) !== '') {
            [$fm, $bodyOnly] = parse_front_matter($uploaded);
            $form['body_md'] = $bodyOnly;

            $fmMap = ['date' => 'date_published', 'read_minutes' => 'read_minutes'];
            foreach ($fm as $key => $value) {
                $field = $fmMap[$key] ?? $key;
                if (array_key_exists($field, $form) && $form[$field] === '' && $value !== '') {
                    $form[$field] = $value;
                }
            }
        }
    }

    if (!isset(POST_TYPES[$form['type']])) {
        $form['type'] = 'blog';
    }

    // Fill in anything the author left blank rather than refusing to
    // save. The markdown itself is the source for all three.
    if ($form['title'] === '') {
        $form['title'] = (string) (markdown_first_heading($form['body_md']) ?? '');
    }
    if ($form['excerpt'] === '') {
        $form['excerpt'] = truncate_words(markdown_first_paragraph($form['body_md']), 210);
    }
    if ($form['hero_subtitle'] === '') {
        $form['hero_subtitle'] = truncate_words(markdown_first_paragraph($form['body_md']), 240);
    }
    if ($form['read_minutes'] === '' || (int) $form['read_minutes'] < 1) {
        $form['read_minutes'] = (string) estimate_read_minutes($form['body_md']);
    }

    if ($form['title'] === '') {
        $errors[] = 'A title is required (or start the markdown with a "# Heading").';
    }
    if (trim($form['body_md']) === '') {
        $errors[] = 'The markdown body is empty.';
    }
    if ($form['date_published'] === '' || strtotime($form['date_published']) === false) {
        $errors[] = 'Publish date is not a valid date.';
    }
    // An unusable hero image is dropped rather than refused: it is
    // decoration, and losing a whole save over it would be worse.
    if ($form['hero_image'] !== '' && safe_asset_path($form['hero_image']) === '') {
        $errors[] = 'That hero image path is not a file in assets/, so it was cleared.';
    }
    $form['hero_image'] = safe_asset_path($form['hero_image']);
    $form['hero_blur']  = (string) clamp_hero_blur($form['hero_blur']);

    if (!array_key_exists($form['card_gradient'], card_gradients())) {
        $form['card_gradient'] = 'bshot-1';
    }
    if (!array_key_exists($form['card_icon'], card_icons())) {
        $form['card_icon'] = 'cart';
    }
    if (!in_array($form['status'], ['draft', 'published'], true)) {
        $form['status'] = 'draft';
    }

    /**
     * The URL is derived from the title and is never asked for.
     *
     * An existing post keeps the slug it already has, even if the
     * title is later reworded: the address is public, may be linked
     * to from elsewhere and is already indexed, so it must not move
     * silently underneath an edit.
     */
    if ($post !== null) {
        $form['slug'] = $post['slug'];
    } else {
        $prefix = post_slug_prefix($form['type']);
        $body   = slugify($form['title']);

        if ($body === '') {
            $errors[] = 'Could not build a web address from that title.';
        } else {
            $candidate = str_starts_with($body, rtrim($prefix, '-')) ? $body : $prefix . $body;
            $form['slug'] = unique_slug($candidate);
        }
    }

    $action = $_POST['action'] ?? 'draft';

    if (!$errors) {
        $columns = [
            'type', 'slug', 'title', 'author', 'category', 'hero_subtitle',
            'hero_image', 'hero_blur', 'excerpt',
            'body_md', 'read_minutes', 'date_published', 'card_gradient',
            'card_icon', 'cta_heading', 'cta_sub', 'meta_title', 'meta_description', 'status',
        ];

        $form['status'] = $action === 'publish' ? 'published' : 'draft';

        $writeLive = static function (array $form, array $columns, ?int $postId): int {
            $values = [];
            foreach ($columns as $col) {
                $values[':' . $col] = in_array($col, ['read_minutes', 'hero_blur'], true)
                    ? (int) $form[$col]
                    : $form[$col];
            }

            if ($postId === null) {
                $sql = 'INSERT INTO posts (' . implode(', ', $columns) . ') VALUES ('
                     . implode(', ', array_map(static fn($c) => ':' . $c, $columns)) . ')';
                db()->prepare($sql)->execute($values);

                return (int) db()->lastInsertId();
            }

            // Writing the live columns always clears the staged draft:
            // whatever was pending has just become the real thing.
            $sets = implode(', ', array_map(static fn($c) => $c . ' = :' . $c, $columns));
            $values[':id'] = $postId;
            db()->prepare(
                'UPDATE posts SET ' . $sets . ', draft_payload = NULL, draft_saved_at = NULL
                 WHERE id = :id'
            )->execute($values);

            return $postId;
        };

        if ($action === 'publish') {
            $newId = $writeLive($form, $columns, $post['id'] ?? null);
            flash('Published "' . $form['title'] . '". It is live now.');

        } elseif ($post !== null && $post['status'] === 'published') {
            // The post is live, so hold the edit back rather than
            // changing the public page behind the author's back.
            $payload = [];
            foreach (post_editable_fields() as $field) {
                $payload[$field] = in_array($field, ['read_minutes', 'hero_blur'], true)
                    ? (int) $form[$field]
                    : $form[$field];
            }

            db()->prepare(
                'UPDATE posts SET draft_payload = :p, draft_saved_at = NOW() WHERE id = :id'
            )->execute([':p' => json_encode($payload), ':id' => $post['id']]);

            flash('Saved as a draft. The live page still shows the published version until you publish.');

        } else {
            // Nothing public to protect, so write straight through.
            $newId = $writeLive($form, $columns, $post['id'] ?? null);
            flash($post === null
                ? 'Created "' . $form['title'] . '" as a draft.'
                : 'Saved "' . $form['title'] . '" as a draft.');
        }

        redirect('index.php');
    }
}

/* ---------- discard staged edits and go back to what is live ---------- */
if (($_GET['discard'] ?? '') !== '' && $post !== null) {
    if (!csrf_check($_GET['csrf'] ?? null)) {
        flash('Your session expired. Nothing changed.', 'err');
        redirect('post-edit.php?id=' . (int) $post['id']);
    }

    db()->prepare(
        'UPDATE posts SET draft_payload = NULL, draft_saved_at = NULL WHERE id = :id'
    )->execute([':id' => $post['id']]);

    flash('Discarded the unpublished changes.');
    redirect('post-edit.php?id=' . (int) $post['id']);
}

/* ---------- unpublish: take a live post back to draft ---------- */
if (($_GET['unpublish'] ?? '') !== '' && $post !== null) {
    if (!csrf_check($_GET['csrf'] ?? null)) {
        flash('Your session expired. Nothing changed.', 'err');
        redirect('post-edit.php?id=' . (int) $post['id']);
    }

    // Any staged edits become the working copy, since there is no
    // longer a live version they need to be held back from.
    $merged = post_with_draft($post);
    $sets = [];
    $vals = [':id' => $post['id']];
    foreach (post_editable_fields() as $f) {
        $sets[] = $f . ' = :' . $f;
        $vals[':' . $f] = $merged[$f];
    }

    db()->prepare(
        'UPDATE posts SET ' . implode(', ', $sets) . ', status = "draft",
         draft_payload = NULL, draft_saved_at = NULL WHERE id = :id'
    )->execute($vals);

    flash('Unpublished. It is a draft again and no longer public.');
    redirect('post-edit.php?id=' . (int) $post['id']);
}

admin_head($post ? 'Edit post' : 'New post', $user, 'posts');
?>
<div class="ad-wrap ad-wide">
  <?php render_flash(); ?>

  <?php if ($errors): ?>
    <div class="ad-flash ad-flash-err">
      <strong>Not saved:</strong>
      <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data" id="postForm">
    <?= csrf_field() ?>

    <div class="ad-head">
      <div>
        <h1><?= $post ? 'Edit post' : 'New post' ?></h1>
        <p class="ad-sub" id="saveState">
        <?php if ($post === null): ?>
          Not saved yet &middot; saves as a draft automatically
        <?php elseif ($post['status'] === 'published'): ?>
          Live at <a href="<?= e(post_url($post)) ?>" target="_blank" rel="noopener"><?= e(SITE_URL . post_url($post)) ?></a>
          &middot; <a href="post-download.php?id=<?= (int) $post['id'] ?>">download .md</a>
        <?php else: ?>
          Draft, not public
          &middot; <a href="post-download.php?id=<?= (int) $post['id'] ?>">download .md</a>
        <?php endif; ?>
        </p>
      </div>
      <div class="ad-head-actions">
        <a class="ad-btn" href="index.php">Cancel</a>
        <button class="ad-btn" type="button" id="btnPreview"
                <?= $post === null ? '' : 'data-id="' . (int) $post['id'] . '"' ?>>Preview</button>
        <button class="ad-btn" type="submit" name="action" value="draft">Save as draft</button>
        <button class="ad-btn ad-btn-primary" type="submit" name="action" value="publish"
                id="btnPublish">Publish</button>
      </div>
    </div>

    <?php if ($hasPending): ?>
      <div class="ad-flash ad-flash-warn">
        <strong>You have unpublished changes.</strong> The live page still shows the
        published version. Press <em>Publish</em> to make these edits public, or
        <a href="post-edit.php?id=<?= (int) $post['id'] ?>&amp;discard=1&amp;csrf=<?= e(csrf_token()) ?>">discard
        them</a> and go back to what is live.
      </div>
    <?php endif; ?>

    <?php if ($post !== null && $post['status'] === 'published'): ?>
      <p class="ad-sub" style="margin:-8px 0 18px">
        This post is live.
        <a class="ad-danger" href="post-edit.php?id=<?= (int) $post['id'] ?>&amp;unpublish=1&amp;csrf=<?= e(csrf_token()) ?>"
           onclick="return confirm('Unpublish this post? It will stop being public and go back to being a draft.')">Unpublish
        and return it to draft</a>.
      </p>
    <?php endif; ?>

    <div class="ad-cols">
      <!-- ---------------- main column ---------------- -->
      <div class="ad-col-main">
        <div class="ad-card">
          <label class="ad-field">
            <span class="ad-label">Title</span>
            <input type="text" name="title" id="fTitle" value="<?= e($form['title']) ?>"
                   placeholder="Leave blank to use the first # heading">
          </label>

          <div class="ad-field">
            <span class="ad-label">Web address</span>
            <p class="ad-permalink" id="fPermalink"><?= e(SITE_URL) ?><b><?= $form['slug'] !== '' ? e(slug_path($form['slug'])) : '/...' ?></b></p>
            <span class="ad-hint">
              <?php if ($post !== null): ?>
                Fixed once a post is created, so links and search results pointing at it
                keep working even if you reword the title.
              <?php else: ?>
                Built from the title automatically.
              <?php endif; ?>
            </span>
          </div>
        </div>

        <div class="ad-card">
          <div class="ad-card-head">
            <span class="ad-label">Markdown body</span>
            <span class="ad-toolbar">
              <label class="ad-btn ad-btn-sm ad-file">
                Load .md file
                <input type="file" name="md_file" id="fFile" accept=".md,.markdown,.txt">
              </label>
              <button class="ad-btn ad-btn-sm" type="button" id="btnAuto"
                      title="Reads the markdown and fills any empty field: title from the first # heading, hero subtitle and card excerpt from the first paragraph, read time from the word count. Fields you have already filled in are left alone.">Auto-fill fields</button>
              <button class="ad-btn ad-btn-sm" type="button" id="btnImage">Insert image</button>
            </span>
          </div>

          <textarea name="body_md" id="fBody" class="ad-code" rows="26"
                    placeholder="# Your heading&#10;&#10;Paste markdown here, or load a .md file."><?= e($form['body_md']) ?></textarea>

          <p class="ad-hint" id="fStats"></p>
          <p class="ad-hint">
            Headings, lists, tables, links, bold and quotes are styled to match the site
            automatically. The first <code>#</code> heading is dropped on the live page
            because the title is already shown in the hero. Raw HTML is passed through if
            you need a block markdown cannot express.
          </p>
          <p class="ad-hint">
            <b>Buttons.</b> Put a line of its own anywhere in the body:<br>
            <code>[cta text="Install Brix free" url="/pricing" align="center"]</code><br>
            <code>align</code> is optional and can be <code>left</code> (the default),
            <code>center</code> or <code>right</code>. Use as many as you like. Write any
            UTM parameters into <code>url</code> in full &mdash; they are kept exactly as
            you type them, including on App&nbsp;Store links. A button with no text, or a
            link that is not <code>https://</code>, <code>http://</code>, <code>/a-path</code>
            or <code>#an-anchor</code>, is left off the page entirely.
          </p>
        </div>

        <div class="ad-card">
          <span class="ad-label">Search engine listing</span>
          <p class="ad-hint" style="margin-bottom:14px">
            How this page appears in Google and in the sitemap. Leave the boxes empty to
            use the post's own title and excerpt; the preview always shows what will
            actually be published.
          </p>

          <!-- Shows the effective values, so an empty box is never a mystery. -->
          <div class="ad-serp">
            <span class="ad-serp-url"><?= e(SITE_URL) ?><?= $form['slug'] !== '' ? e(slug_path($form['slug'])) : '/...' ?></span>
            <span class="ad-serp-title" id="serpTitle"></span>
            <span class="ad-serp-desc" id="serpDesc"></span>
          </div>

          <label class="ad-field">
            <span class="ad-sublabel">Meta title <em>(blank uses the post title)</em></span>
            <input type="text" name="meta_title" id="fMetaTitle"
                   value="<?= e($form['meta_title']) ?>" maxlength="255">
          </label>
          <label class="ad-field">
            <span class="ad-sublabel">Meta description <em>(blank uses the excerpt)</em></span>
            <textarea name="meta_description" id="fMetaDesc" rows="2" maxlength="320"><?= e($form['meta_description']) ?></textarea>
          </label>
        </div>
      </div>

      <!-- ---------------- side column ---------------- -->
      <div class="ad-col-side">
        <div class="ad-card">
          <label class="ad-field">
            <span class="ad-label">Type</span>
            <select name="type" id="fType">
              <?php foreach (POST_TYPES as $val => $label): ?>
                <option value="<?= $val ?>"<?= $form['type'] === $val ? ' selected' : '' ?>><?= $label ?></option>
              <?php endforeach; ?>
            </select>
            <span class="ad-hint">Decides which listing page and URL prefix it uses.</span>
          </label>


          <label class="ad-field">
            <span class="ad-label">Author</span>
            <input type="text" name="author" value="<?= e($form['author']) ?>" required>
          </label>

          <div class="ad-field ad-two">
            <label>
              <span class="ad-label">Publish date</span>
              <input name="date_published" type="date" value="<?= e($form['date_published']) ?>" required>
            </label>
            <label>
              <span class="ad-label">Read time</span>
              <input name="read_minutes" id="fRead" type="number" min="1" max="120"
                     value="<?= e($form['read_minutes']) ?>" placeholder="auto">
            </label>
          </div>

          <label class="ad-field">
            <span class="ad-label">Category</span>
            <input type="text" name="category" value="<?= e($form['category']) ?>"
                   placeholder="Cart upsells" list="catList">
            <datalist id="catList">
              <?php
              try {
                  $cats = db()->query(
                      'SELECT DISTINCT category FROM posts WHERE category <> "" ORDER BY category'
                  )->fetchAll(PDO::FETCH_COLUMN);
                  foreach ($cats as $c) {
                      echo '<option value="' . e($c) . '">';
                  }
              } catch (Throwable) {
                  // A missing table here is not worth breaking the form over.
              }
              ?>
            </datalist>
            <span class="ad-hint">Shown on the card and in the post meta line.</span>
          </label>
        </div>

        <div class="ad-card">
          <span class="ad-label">Card &amp; hero</span>

          <label class="ad-field">
            <span class="ad-sublabel">Hero subtitle <em>(under the H1)</em></span>
            <textarea name="hero_subtitle" rows="3"><?= e($form['hero_subtitle']) ?></textarea>
          </label>

          <div class="ad-field">
            <span class="ad-sublabel">Hero background image <em>(optional)</em></span>

            <!-- Holds the uploaded path. Never typed into, so it is not a
                 text box the author can put anything in. -->
            <input type="hidden" name="hero_image" id="fHeroImage" value="<?= e($form['hero_image']) ?>">

            <div class="ad-heroshot" id="heroShot">
              <div class="ad-heroshot-img" id="heroShotImg"></div>
              <span class="ad-heroshot-empty" id="heroShotEmpty">No image &middot; plain gradient hero</span>
              <span class="ad-heroshot-type">Aa</span>
            </div>

            <div class="ad-heroshot-actions">
              <button class="ad-btn ad-btn-sm" type="button" id="btnHeroUp">Upload image</button>
              <button class="ad-btn ad-btn-sm" type="button" id="btnHeroClear">Remove</button>
            </div>

            <label class="ad-blurrow" id="blurRow">
              <span class="ad-sublabel">Blur <b id="fBlurVal">0px</b></span>
              <input type="range" name="hero_blur" id="fHeroBlur" min="0" max="24" step="1"
                     value="<?= (int) $form['hero_blur'] ?>">
            </label>

            <span class="ad-hint">
              Sits behind the title, under a light wash that keeps the text readable.
              Landscape images around 2000px wide work best. Blur is useful when the
              photo is busy enough to fight the heading.
            </span>
          </div>

          <label class="ad-field">
            <span class="ad-sublabel">Card excerpt <em>(on the listing grid)</em></span>
            <textarea name="excerpt" rows="3"><?= e($form['excerpt']) ?></textarea>
          </label>


          <div class="ad-field ad-two">
            <label>
              <span class="ad-sublabel">Gradient</span>
              <select name="card_gradient" id="fGrad">
                <?php foreach (card_gradients() as $val => $label): ?>
                  <option value="<?= $val ?>"<?= $form['card_gradient'] === $val ? ' selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
              </select>
            </label>
            <label>
              <span class="ad-sublabel">Icon</span>
              <select name="card_icon" id="fIcon">
                <?php foreach (card_icons() as $val => $icon): ?>
                  <option value="<?= $val ?>"<?= $form['card_icon'] === $val ? ' selected' : '' ?>><?= e($icon['label']) ?></option>
                <?php endforeach; ?>
              </select>
            </label>
          </div>

          <div class="ad-cardpreview">
            <div class="ad-cardpreview-shot" id="cardPrev"></div>
            <span class="ad-hint">Card preview</span>
          </div>
        </div>

        <div class="ad-card">
          <span class="ad-label">Closing call to action</span>
          <p class="ad-hint">Leave blank to use the site default.</p>
          <label class="ad-field">
            <span class="ad-sublabel">Heading</span>
            <input type="text" name="cta_heading" value="<?= e($form['cta_heading']) ?>"
                   placeholder="Turn these tactics into revenue">
          </label>
          <label class="ad-field">
            <span class="ad-sublabel">Sub-line</span>
            <input type="text" name="cta_sub" value="<?= e($form['cta_sub']) ?>"
                   placeholder="Install free, set your first reward tier...">
          </label>
        </div>
      </div>
    </div>
  </form>
</div>

<script>
(function () {
  var typeSel  = document.getElementById('fType');
  var body     = document.getElementById('fBody');
  var title    = document.getElementById('fTitle');
  var read     = document.getElementById('fRead');
  var stats    = document.getElementById('fStats');
  var fileIn   = document.getElementById('fFile');
  var grad     = document.getElementById('fGrad');
  var icon     = document.getElementById('fIcon');
  var prev     = document.getElementById('cardPrev');
  var permalink = document.getElementById('fPermalink');
  var metaTitle = document.getElementById('fMetaTitle');
  var metaDesc  = document.getElementById('fMetaDesc');
  var serpTitle = document.getElementById('serpTitle');
  var serpDesc  = document.getElementById('serpDesc');
  var excerpt   = document.querySelector('[name="excerpt"]');

  /* An existing post keeps the address it was created with. */
  var slugLocked = <?= $post !== null ? 'true' : 'false' ?>;
  var SITE = <?= json_encode(SITE_URL) ?>;

  var ICONS = <?php
      $iconJs = [];
      foreach (card_icons() as $k => $i) { $iconJs[$k] = $i['svg']; }
      echo json_encode($iconJs);
  ?>;

  /* The web address is derived from the title, never typed. */
  function slugifyJs(s) {
    return s.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '')
            .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
  }
  function syncSlug() {
    if (slugLocked) return;
    /* The section is a path segment in the address even though it is a
       prefix in the stored slug, so this previews /blog/x rather than
       the blog-x that goes into the database. */
    var section = typeSel.value === 'case_study' ? '/case-study/' : '/blog/';
    var base = slugifyJs(title.value || '');
    permalink.innerHTML = SITE + '<b>' + (base ? section + base : '/...') + '</b>';
    syncSerp();
  }
  typeSel.addEventListener('change', syncSlug);
  title.addEventListener('input', function () { syncSlug(); syncSerp(); });

  /* Search listing preview: always shows the values that will actually
     be published, so an empty meta box is never ambiguous. */
  function syncSerp() {
    if (!serpTitle) return;
    var t = (metaTitle.value || '').trim() || (title.value || '').trim() || 'Untitled';
    var d = (metaDesc.value || '').trim() || (excerpt ? excerpt.value.trim() : '')
            || 'No description yet.';
    serpTitle.textContent = t.length > 62 ? t.slice(0, 62) + '...' : t;
    serpDesc.textContent  = d.length > 165 ? d.slice(0, 165) + '...' : d;
  }
  [metaTitle, metaDesc, excerpt].forEach(function (el) {
    if (el) el.addEventListener('input', syncSerp);
  });

  /* Word count and read-time estimate, matching the server formula. */
  function countWords(t) {
    var m = t.replace(/[#>*`_\-\[\]()!]/g, ' ').match(/\b[\w'-]+\b/g);
    return m ? m.length : 0;
  }
  function syncStats() {
    var w = countWords(body.value);
    stats.textContent = w.toLocaleString() + ' words - about '
      + Math.max(1, Math.round(w / 220)) + ' min read';
  }
  body.addEventListener('input', syncStats);

  /* Load a .md file into the textarea, then clear the file input so a
     subsequent hand edit is not overwritten by the upload on save. */
  fileIn.addEventListener('change', function () {
    var f = fileIn.files && fileIn.files[0];
    if (!f) return;
    if (body.value.trim() && !confirm('Replace the current markdown with this file?')) {
      fileIn.value = '';
      return;
    }
    var r = new FileReader();
    r.onload = function () {
      var text = String(r.result).replace(/^﻿/, '').replace(/\r\n?/g, '\n');
      applyFrontMatter(text);
      fileIn.value = '';
      syncStats();
      autofill();
    };
    r.readAsText(f);
  });

  /* A file from "download .md" starts with a front matter block. Pull
     the values into the matching fields and keep only the prose. */
  function applyFrontMatter(text) {
    var m = text.match(/^---\n([\s\S]*?)\n---\n?/);
    if (!m) { body.value = text; return; }

    var alias = { date: 'date_published' };
    m[1].split('\n').forEach(function (line) {
      var kv = line.match(/^([A-Za-z0-9_-]+)\s*:\s*(.*)$/);
      if (!kv) return;
      var val = kv[2].trim().replace(/^"([\s\S]*)"$/, '$1').replace(/\\"/g, '"');
      if (!val) return;
      // 'slug' is intentionally ignored: the address is derived from
      // the title on a new post and fixed on an existing one.
      if (kv[1] === 'slug') return;
      var el = document.querySelector('[name="' + (alias[kv[1]] || kv[1]) + '"]');
      if (el) el.value = val;
    });

    body.value = text.slice(m[0].length).replace(/^\n+/, '');
    syncPreview();
    syncSlug();
    syncSerp();
    syncHero();
  }

  /* Derive title, read time and the two summaries from the markdown. */
  function autofill() {
    var text = body.value;
    var h1 = text.match(/^\s*#\s+(.+)$/m);
    if (h1 && !title.value.trim()) {
      title.value = h1[1].replace(/\*\*/g, '').replace(/\\/g, '').trim();
      syncSlug();
    }
    if (!read.value) {
      read.value = Math.max(1, Math.round(countWords(text) / 220));
    }
    var para = text.split(/\n\s*\n/).find(function (b) {
      return b.trim() && !/^\s*(#|>|[-*+]\s|\d+\.\s|!\[|```|\|)/.test(b.trim());
    });
    if (para) {
      var clean = para.replace(/\*\*(.+?)\*\*/g, '$1').replace(/\[(.+?)\]\([^)]*\)/g, '$1')
                      .replace(/\\/g, '').replace(/\s+/g, ' ').trim();
      ['hero_subtitle', 'excerpt'].forEach(function (n) {
        var el = document.querySelector('[name="' + n + '"]');
        if (el && !el.value.trim()) {
          el.value = clean.length > 210 ? clean.slice(0, 210).replace(/\s\S*$/, '') + '...' : clean;
        }
      });
    }
    syncStats();
  }
  document.getElementById('btnAuto').addEventListener('click', autofill);

  /* Card preview: same gradient classes and glyph set as the site. */
  function syncPreview() {
    prev.className = 'ad-cardpreview-shot ' + grad.value;
    prev.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" '
      + 'stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">'
      + (ICONS[icon.value] || '') + '</svg>';
  }
  grad.addEventListener('change', syncPreview);
  icon.addEventListener('change', syncPreview);

  /* ----------------------------------------------------------------
     Image uploads. Shared by the body's "Insert image" and the hero
     background, which differ only in what they do with the returned
     path.
     ---------------------------------------------------------------- */

  function pickImage(onUploaded) {
    var picker = document.createElement('input');
    picker.type = 'file';
    picker.accept = 'image/png,image/jpeg,image/gif,image/webp,image/svg+xml';
    picker.addEventListener('change', function () {
      var f = picker.files && picker.files[0];
      if (!f) return;

      var fd = new FormData();
      fd.append('image', f);
      fd.append('csrf', document.querySelector('[name="csrf"]').value);

      fetch('upload-image.php', { method: 'POST', body: fd })
        .then(function (r) { return r.json(); })
        .then(function (d) {
          if (!d.ok) { alert(d.error || 'Upload failed.'); return; }
          onUploaded(d.url, f);
        })
        .catch(function () { alert('Upload failed.'); });
    });
    picker.click();
  }

  /* Body image: drop the markdown reference at the cursor. */
  document.getElementById('btnImage').addEventListener('click', function () {
    pickImage(function (url, file) {
      var alt = file.name.replace(/\.[^.]+$/, '').replace(/[-_]+/g, ' ');
      var snippet = '\n\n![' + alt + '](' + url + ')\n\n';
      var at = body.selectionStart || body.value.length;
      body.value = body.value.slice(0, at) + snippet + body.value.slice(at);
      dirty = true;
      syncStats();
    });
  });

  /* ---------- hero background image ---------- */

  var heroPath  = document.getElementById('fHeroImage');
  var heroShot  = document.getElementById('heroShot');
  var heroImg   = document.getElementById('heroShotImg');
  var heroEmpty = document.getElementById('heroShotEmpty');
  var heroBlur  = document.getElementById('fHeroBlur');
  var blurVal   = document.getElementById('fBlurVal');
  var blurRow   = document.getElementById('blurRow');

  /* The thumbnail carries the same blur and the same light wash as the
     real hero, so the slider can be judged here rather than by opening
     a preview for every step. Paths are stored relative to the site
     root, and the admin panel is one folder down. */
  function syncHero() {
    var path = (heroPath.value || '').trim();
    var px   = parseInt(heroBlur.value, 10) || 0;

    blurVal.textContent = px + 'px';
    heroShot.classList.toggle('has-img', path !== '');
    heroEmpty.hidden = path !== '';

    // Nothing to blur without an image. A disabled input is not
    // submitted either, which is exactly right: no image means the
    // stored blur goes back to 0 rather than lingering.
    heroBlur.disabled = path === '';
    blurRow.classList.toggle('is-off', path === '');

    heroImg.style.backgroundImage = path ? 'url("../' + path + '")' : '';
    heroImg.style.filter = px ? 'blur(' + px + 'px)' : '';
  }

  document.getElementById('btnHeroUp').addEventListener('click', function () {
    pickImage(function (url) {
      heroPath.value = url;
      dirty = true;
      syncHero();
    });
  });

  document.getElementById('btnHeroClear').addEventListener('click', function () {
    if (!heroPath.value && !Number(heroBlur.value)) return;
    heroPath.value = '';
    heroBlur.value = '0';
    dirty = true;
    syncHero();
  });

  heroBlur.addEventListener('input', syncHero);

  /* ----------------------------------------------------------------
     Autosave.

     Runs a minute after the last edit, and again before a preview is
     opened. It only ever writes a draft, so nothing here can change
     the live page; that needs the Publish button.
     ---------------------------------------------------------------- */

  var form      = document.getElementById('postForm');
  var saveState = document.getElementById('saveState');
  var postId    = <?= $post !== null ? (int) $post['id'] : 0 ?>;
  var isLive    = <?= ($post !== null && $post['status'] === 'published') ? 'true' : 'false' ?>;
  var dirty     = false;
  var saving    = false;
  var AUTOSAVE_MS = 60000;

  form.addEventListener('input', function () { dirty = true; });
  form.addEventListener('change', function () { dirty = true; });

  function setState(text, kind) {
    if (!saveState) return;
    saveState.textContent = text;
    saveState.className = 'ad-sub' + (kind ? ' ad-sub-' + kind : '');
  }

  function autosave() {
    if (saving) return Promise.resolve(false);
    saving = true;
    setState('Saving draft...');

    var data = new FormData(form);
    data.set('id', String(postId));
    // The file input is irrelevant to an autosave and can be large.
    data.delete('md_file');

    return fetch('post-autosave.php', { method: 'POST', body: data })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        saving = false;
        if (!d.ok) { setState(d.error || 'Could not save draft.', 'bad'); return false; }
        if (d.skipped) { setState('Nothing to save yet.'); return false; }

        if (d.id && !postId) {
          postId = d.id;
          // Put the new id in the address bar so a reload keeps editing
          // the same draft rather than starting a second one.
          history.replaceState(null, '', 'post-edit.php?id=' + d.id);
          var pv = document.getElementById('btnPreview');
          if (pv) pv.setAttribute('data-id', String(d.id));
        }

        dirty = false;
        setState(isLive
          ? 'Draft saved ' + d.savedAt + '. The live page is unchanged until you publish.'
          : 'Draft saved ' + d.savedAt + '.');
        return true;
      })
      .catch(function () {
        saving = false;
        setState('Could not reach the server. Your text is still here.', 'bad');
        return false;
      });
  }

  setInterval(function () { if (dirty) autosave(); }, AUTOSAVE_MS);

  /* Preview: save first so what opens is what is on screen. */
  document.getElementById('btnPreview').addEventListener('click', function () {
    var open = function () {
      if (!postId) { alert('Add a title or some text first.'); return; }
      window.open('preview.php?id=' + postId, '_blank', 'noopener');
    };
    if (dirty || !postId) { autosave().then(open); } else { open(); }
  });

  /* Publishing is the only action that changes the public site. */
  document.getElementById('btnPublish').addEventListener('click', function (ev) {
    if (!confirm(isLive
      ? 'Publish these changes? They replace what is currently live.'
      : 'Publish this post? It will be public on the site straight away.')) {
      ev.preventDefault();
    }
  });

  /* A normal submit is an intentional save, so do not warn on it. */
  var submitting = false;
  form.addEventListener('submit', function () { submitting = true; });
  window.addEventListener('beforeunload', function (ev) {
    if (dirty && !submitting) { ev.preventDefault(); ev.returnValue = ''; }
  });

  syncStats();
  syncPreview();
  syncSlug();
  syncSerp();
  syncHero();
})();
</script>
<?php admin_foot(); ?>
