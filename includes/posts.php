<?php
/**
 * Query helpers for the posts table.
 *
 * Everything public-facing filters on status = 'published' AND
 * deleted_at IS NULL. Drafts and soft-deleted posts are only ever
 * reachable from inside the admin panel.
 */

declare(strict_types=1);

const POST_TYPES = [
    'blog'       => 'Blog post',
    'case_study' => 'Case study',
];

/**
 * The fields an author can edit.
 *
 * Declared once and used by the editor, the autosave endpoint and the
 * draft merge, so the three can never drift apart. `status` is excluded
 * on purpose: it is set by which button was pressed, not by a field.
 *
 * `slug` is one of them, so a changed address is staged in
 * draft_payload like every other edit and only becomes the live URL
 * when Publish is pressed.
 */
function post_editable_fields(): array
{
    return [
        'type', 'slug', 'title', 'author', 'category', 'hero_subtitle', 'hero_image',
        'hero_blur', 'excerpt', 'body_md', 'read_minutes', 'date_published',
        'card_gradient', 'card_icon',
        'cta_heading', 'cta_sub', 'meta_title', 'meta_description',
    ];
}

/**
 * A post as it should appear while being edited or previewed.
 *
 * A published post keeps its live values in the table and any pending
 * edits in draft_payload. Merging the two gives the version the author
 * is working on, which is what both the editor and the preview must
 * show. The public site never calls this.
 */
function post_with_draft(array $post): array
{
    if (empty($post['draft_payload'])) {
        return $post;
    }

    $draft = json_decode((string) $post['draft_payload'], true);
    if (!is_array($draft)) {
        return $post;
    }

    foreach (post_editable_fields() as $field) {
        if (array_key_exists($field, $draft)) {
            $post[$field] = $draft[$field];
        }
    }

    return $post;
}

/** True when a live post has edits staged that are not public yet. */
function post_has_pending_changes(array $post): bool
{
    return $post['status'] === 'published' && !empty($post['draft_payload']);
}

/** Published posts of one type, newest first. */
function get_published_posts(string $type, ?int $limit = null): array
{
    $sql = 'SELECT * FROM posts
            WHERE type = :type AND status = "published" AND deleted_at IS NULL
            ORDER BY date_published DESC, id DESC';

    if ($limit !== null) {
        $sql .= ' LIMIT ' . max(1, $limit);
    }

    $stmt = db()->prepare($sql);
    $stmt->execute([':type' => $type]);

    return $stmt->fetchAll();
}

/** A single published post by its URL slug. */
function get_published_post_by_slug(string $slug): ?array
{
    $stmt = db()->prepare(
        'SELECT * FROM posts
         WHERE slug = :slug AND status = "published" AND deleted_at IS NULL
         LIMIT 1'
    );
    $stmt->execute([':slug' => $slug]);

    return $stmt->fetch() ?: null;
}

/** Any post by id, including drafts. Admin and preview only. */
function get_post(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM posts WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);

    return $stmt->fetch() ?: null;
}

/** Every post for the admin list, optionally filtered. */
function get_all_posts(?string $type = null, bool $includeDeleted = false): array
{
    $sql = 'SELECT * FROM posts WHERE 1=1';
    $params = [];

    if (!$includeDeleted) {
        $sql .= ' AND deleted_at IS NULL';
    }

    if ($type !== null && isset(POST_TYPES[$type])) {
        $sql .= ' AND type = :type';
        $params[':type'] = $type;
    }

    $sql .= ' ORDER BY deleted_at IS NOT NULL, date_published DESC, id DESC';

    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

/** Soft-deleted posts, for the restore list. */
function get_deleted_posts(): array
{
    return db()
        ->query('SELECT * FROM posts WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC')
        ->fetchAll();
}

/**
 * Related posts for the bottom of an article: same type, excluding
 * the current one, newest first.
 */
function get_related_posts(array $post, int $limit = 2): array
{
    $stmt = db()->prepare(
        'SELECT * FROM posts
         WHERE type = :type AND id <> :id
           AND status = "published" AND deleted_at IS NULL
         ORDER BY date_published DESC, id DESC
         LIMIT ' . max(1, $limit)
    );
    $stmt->execute([':type' => $post['type'], ':id' => $post['id']]);

    return $stmt->fetchAll();
}

/**
 * Is this slug free?
 *
 * Also rejects slugs that collide with a real file in the web root,
 * because .htaccess serves existing files before it consults the
 * database. A post slugged "pricing" would be unreachable, and an
 * address another post has moved away from is not free either.
 */
function slug_is_available(string $slug, ?int $exceptId = null): bool
{
    $sql = 'SELECT COUNT(*) FROM posts WHERE slug = :slug';
    $params = [':slug' => $slug];

    if ($exceptId !== null) {
        $sql .= ' AND id <> :id';
        $params[':id'] = $exceptId;
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    if ((int) $stmt->fetchColumn() > 0) {
        return false;
    }

    // An address that still redirects to another post: handing it to a
    // second one would kill that redirect and leave anyone holding the
    // old link on the wrong article.
    $sql = 'SELECT COUNT(*) FROM post_redirects WHERE old_slug = :slug';
    $params = [':slug' => $slug];

    if ($exceptId !== null) {
        $sql .= ' AND post_id <> :id';
        $params[':id'] = $exceptId;
    }

    try {
        $stmt = db()->prepare($sql);
        $stmt->execute($params);

        if ((int) $stmt->fetchColumn() > 0) {
            return false;
        }
    } catch (Throwable) {
        // A database that predates the table must still be able to save.
    }

    return !file_exists(BRIX_ROOT . '/' . $slug . '.php')
        && !file_exists(BRIX_ROOT . '/' . $slug . '.html');
}

/** Make a slug unique by appending -2, -3 ... if needed. */
function unique_slug(string $base, ?int $exceptId = null): string
{
    $slug = $base;
    $n = 1;

    while (!slug_is_available($slug, $exceptId)) {
        $n++;
        $slug = $base . '-' . $n;
        if ($n > 50) {
            $slug = $base . '-' . bin2hex(random_bytes(3));
            break;
        }
    }

    return $slug;
}

/**
 * Remember an address a live post has just moved away from.
 *
 * The row points at the post rather than at its new slug, so renaming a
 * post a second time does not send a visitor through two redirects.
 * Renaming it back to an address it used to have deletes the row that
 * would otherwise point the new address at itself.
 *
 * A failure here is swallowed: the post itself saved correctly, and
 * losing the save over the redirect would be the worse outcome.
 */
function record_slug_redirect(int $postId, string $oldSlug, string $newSlug): void
{
    if ($oldSlug === '' || $oldSlug === $newSlug) {
        return;
    }

    try {
        db()->prepare('DELETE FROM post_redirects WHERE old_slug = :slug')
            ->execute([':slug' => $newSlug]);

        db()->prepare(
            'INSERT INTO post_redirects (old_slug, post_id) VALUES (:slug, :id)
             ON DUPLICATE KEY UPDATE post_id = VALUES(post_id)'
        )->execute([':slug' => $oldSlug, ':id' => $postId]);
    } catch (Throwable) {
        // Nothing to do: the post is saved, the old address just stops
        // resolving.
    }
}

/** Forget every address a post used to have. Called when it is erased. */
function forget_slug_redirects(int $postId): void
{
    try {
        db()->prepare('DELETE FROM post_redirects WHERE post_id = :id')
            ->execute([':id' => $postId]);
    } catch (Throwable) {
        // See record_slug_redirect().
    }
}

/**
 * The published post an old address belongs to, or null.
 *
 * article.php uses this to turn a request for a moved page into a
 * permanent redirect instead of a 404.
 */
function get_published_post_by_old_slug(string $slug): ?array
{
    try {
        $stmt = db()->prepare(
            'SELECT p.* FROM post_redirects r
             JOIN posts p ON p.id = r.post_id
             WHERE r.old_slug = :slug
               AND p.status = "published" AND p.deleted_at IS NULL
             LIMIT 1'
        );
        $stmt->execute([':slug' => $slug]);

        return $stmt->fetch() ?: null;
    } catch (Throwable) {
        return null;
    }
}

/** Every address a post used to have, oldest first. Admin only. */
function get_slug_redirects(int $postId): array
{
    try {
        $stmt = db()->prepare(
            'SELECT old_slug FROM post_redirects WHERE post_id = :id ORDER BY id'
        );
        $stmt->execute([':id' => $postId]);

        return $stmt->fetchAll();
    } catch (Throwable) {
        return [];
    }
}

/** Newest published posts across both types, for the footer column. */
function get_footer_links(string $type, int $limit = 4): array
{
    $stmt = db()->prepare(
        'SELECT slug, title FROM posts
         WHERE type = :type AND status = "published" AND deleted_at IS NULL
         ORDER BY date_published DESC, id DESC
         LIMIT ' . max(1, $limit)
    );
    $stmt->execute([':type' => $type]);

    return $stmt->fetchAll();
}

/**
 * Field-level fields safe to touch from the bulk editor: short text
 * fields where a wrong value is easy to see and easy to undo. Deliberately
 * excludes slug (has its own redirect machinery), body_md (needs its own
 * insertion-based tool, not a blind overwrite) and the structural fields
 * (type, dates, layout) that post-edit.php's form handles with its own
 * validation.
 */
function bulk_editable_fields(): array
{
    return ['title', 'author', 'category', 'excerpt', 'meta_title', 'meta_description'];
}

/**
 * Wrap the first plain-text occurrence of $anchor in $body as a
 * markdown link to $url, skipping an occurrence that is already the
 * text of a link (`[$anchor](...)`) or that falls inside a fenced
 * code block. Matching is exact and case-sensitive, since the anchor
 * has to be text that genuinely exists in the post already - this
 * never rewrites or paraphrases anything.
 *
 * Returns [newBody, applied, note]. note is a short context snippet
 * (~40 chars either side) when applied, or an explanation when not -
 * "already linked elsewhere" or "anchor phrase not found" - so a
 * caller can show why nothing happened rather than a silent no-op.
 */
function bx_insert_first_link(string $body, string $anchor, string $url): array
{
    if ($anchor === '' || $url === '') {
        return [$body, false, 'Missing anchor or url.'];
    }

    if (str_contains($body, '[' . $anchor . '](')) {
        return [$body, false, 'Already linked elsewhere in this post.'];
    }

    $searchFrom = 0;
    while (($pos = strpos($body, $anchor, $searchFrom)) !== false) {
        $before = substr($body, 0, $pos);
        $after  = substr($body, $pos + strlen($anchor));

        $alreadyLinkText = str_ends_with($before, '[') && str_starts_with($after, '](');
        $inFencedBlock   = substr_count($before, '```') % 2 === 1;

        if (!$alreadyLinkText && !$inFencedBlock) {
            $newBody = $before . '[' . $anchor . '](' . $url . ')' . $after;
            $context = trim(mb_substr($before, -40) . '⟦' . $anchor . '⟧' . mb_substr($after, 0, 40));

            return [$newBody, true, $context];
        }

        $searchFrom = $pos + 1;
    }

    return [$body, false, 'Anchor phrase not found in this post.'];
}

/**
 * The [cta text="..." url="..." align="..."] shortcode
 * render_body_ctas() (see includes/markdown.php) turns into the
 * button every post already uses for its mid-article install
 * prompts. Kept in one place so the attribute syntax has a single
 * source of truth instead of being retyped at every call site.
 *
 * Quotes are stripped rather than escaped: the shortcode's own parser
 * splits on the first unescaped `"`, so a literal quote in CTA copy
 * would truncate the attribute instead of rendering - safer to just
 * not allow one in text this short.
 */
function bx_cta_shortcode(string $text, string $url, string $align = 'center'): string
{
    $text  = str_replace('"', '', $text);
    $url   = str_replace('"', '', $url);
    $align = in_array($align, ['left', 'center', 'right'], true) ? $align : 'center';

    return '[cta text="' . $text . '" url="' . $url . '" align="' . $align . '"]';
}

/**
 * Insert $insertMarkdown as its own paragraph immediately after the
 * first occurrence of $afterText in $body. Skips if $afterText can't
 * be found, or if $insertMarkdown already appears shortly after that
 * anchor - so re-applying the same batch is a no-op, not a second
 * copy of the same CTA stacked under the first.
 *
 * Returns [newBody, applied, note].
 */
function bx_insert_after(string $body, string $afterText, string $insertMarkdown): array
{
    if ($afterText === '' || $insertMarkdown === '') {
        return [$body, false, 'Missing anchor text or content to insert.'];
    }

    $pos = strpos($body, $afterText);
    if ($pos === false) {
        return [$body, false, 'Anchor paragraph not found in this post.'];
    }

    $insertAt  = $pos + strlen($afterText);
    $lookahead = substr($body, $insertAt, 400);

    if (str_contains($lookahead, trim($insertMarkdown))) {
        return [$body, false, 'Already present after this anchor.'];
    }

    $before  = substr($body, 0, $insertAt);
    $after   = substr($body, $insertAt);
    $newBody = rtrim($before) . "\n\n" . trim($insertMarkdown) . "\n\n" . ltrim($after);
    $context = trim(mb_substr($before, -60)) . ' ⟦NEW: ' . trim($insertMarkdown) . '⟧';

    return [$newBody, true, $context];
}

/**
 * Replace the first verbatim occurrence of $find in $body with $replace.
 * Like bx_insert_first_link() and bx_insert_after(), this never rewrites
 * or paraphrases anything on its own - $find has to be text that already
 * exists in the post, so the caller is correcting a known passage, not
 * inventing new copy.
 *
 * If $find is no longer present but $replace already is, the batch is
 * treated as already applied rather than an error, so re-running the
 * same payload after it succeeded once is a safe no-op.
 *
 * Returns [newBody, applied, note].
 */
function bx_replace_text(string $body, string $find, string $replace): array
{
    if ($find === '') {
        return [$body, false, 'Missing text to find.'];
    }

    $pos = strpos($body, $find);
    if ($pos === false) {
        if ($replace !== '' && str_contains($body, $replace)) {
            return [$body, false, 'Already applied - the replacement text is already present.'];
        }
        return [$body, false, 'Text to replace not found in this post.'];
    }

    $before  = substr($body, 0, $pos);
    $after   = substr($body, $pos + strlen($find));
    $newBody = $before . $replace . $after;
    $context = trim(mb_substr($before, -40)) . ' ⟦' . trim(mb_substr($replace, 0, 60)) . '⟧';

    return [$newBody, true, $context];
}

/**
 * Run every requested body_md change - link insertions, CTA insertions
 * and text replacements alike - against one body string in a single
 * pass, so the live column and a pending draft's own copy are
 * transformed identically by calling this once for each.
 *
 * Returns [newBody, linkReport, ctaReport, textReport].
 */
function bx_apply_body_operations(string $body, array $linkRequests, array $ctaRequests, array $textRequests = []): array
{
    $linkReport = [];
    foreach ($linkRequests as $req) {
        $anchor = trim((string) ($req['anchor'] ?? ''));
        $url    = trim((string) ($req['url'] ?? ''));
        [$body, $applied, $note] = bx_insert_first_link($body, $anchor, $url);
        $linkReport[] = ['anchor' => $anchor, 'url' => $url, 'applied' => $applied, 'note' => $note];
    }

    $ctaReport = [];
    foreach ($ctaRequests as $req) {
        $after = trim((string) ($req['after'] ?? ''));
        $text  = trim((string) ($req['text'] ?? ''));
        $url   = trim((string) ($req['url'] ?? ''));
        $align = trim((string) ($req['align'] ?? 'center'));

        $shortcode = ($text !== '' && $url !== '') ? bx_cta_shortcode($text, $url, $align) : '';
        [$body, $applied, $note] = bx_insert_after($body, $after, $shortcode);
        $ctaReport[] = ['after' => $after, 'text' => $text, 'url' => $url, 'applied' => $applied, 'note' => $note];
    }

    $textReport = [];
    foreach ($textRequests as $req) {
        $find    = (string) ($req['find'] ?? '');
        $replace = (string) ($req['replace'] ?? '');
        [$body, $applied, $note] = bx_replace_text($body, $find, $replace);
        $textReport[] = ['find' => $find, 'replace' => $replace, 'applied' => $applied, 'note' => $note];
    }

    return [$body, $linkReport, $ctaReport, $textReport];
}

/**
 * Apply a batch of field-level edits, in-body link insertions, mid-
 * article CTA insertions, and/or body text corrections, to posts by
 * slug, in one pass.
 *
 * Built for exactly the situation post-edit.php is tedious for: a
 * title/description refresh across a dozen posts at once, or adding
 * the same handful of internal links across a content cluster, rather
 * than one post at a time. Only ever touches what's explicitly passed
 * and only when it actually changes something.
 *
 * Each slug's entry may carry any of bulk_editable_fields() (replaced
 * outright), a "link_insertions" list of {anchor, url} pairs (each one
 * wraps the first matching, not-already-linked occurrence - see
 * bx_insert_first_link()), a "cta_insertions" list of
 * {after, text, url, align} pairs (each one drops a new
 * [cta ...]-shortcode paragraph right after the first matching
 * existing paragraph - see bx_insert_after()), and/or a
 * "text_replacements" list of {find, replace} pairs (each one swaps
 * the first verbatim occurrence of "find" - see bx_replace_text()).
 * Deliberately not a blind body_md overwrite: every change is anchored
 * to text that has to already exist, so there's nothing here that can
 * silently replace a post's actual content with something invented.
 *
 * A published post with a pending, unpublished draft keeps a full
 * snapshot of every editable field - body_md included - in
 * draft_payload (see post_editable_fields()); if this only wrote the
 * live columns, the next time that draft was published it would
 * silently overwrite this change with whatever stale value the draft
 * still has. So every field touched here, and the same body
 * insertions against the draft's own body_md, are patched into
 * draft_payload too, when one exists and already carries that field.
 *
 * Returns one report row per requested slug: 'changed' for field
 * diffs, 'links' and 'ctas' for what happened with each requested
 * insertion - so a caller can show what happened (or didn't) rather
 * than a single pass/fail for the whole batch.
 */
function bulk_apply_post_fields(array $updatesBySlug, bool $dryRun = false): array
{
    $allowedFields = array_flip(bulk_editable_fields());
    $report        = [];

    foreach ($updatesBySlug as $slug => $entry) {
        if (!is_array($entry)) {
            $report[$slug] = ['ok' => false, 'message' => 'Entry must be an object.', 'changed' => [], 'links' => [], 'ctas' => [], 'texts' => []];
            continue;
        }

        $fields       = array_intersect_key($entry, $allowedFields);
        $linkRequests = is_array($entry['link_insertions'] ?? null) ? $entry['link_insertions'] : [];
        $ctaRequests  = is_array($entry['cta_insertions'] ?? null) ? $entry['cta_insertions'] : [];
        $textRequests = is_array($entry['text_replacements'] ?? null) ? $entry['text_replacements'] : [];

        if ($fields === [] && $linkRequests === [] && $ctaRequests === [] && $textRequests === []) {
            $report[$slug] = ['ok' => false, 'message' => 'No recognized fields, link_insertions, cta_insertions or text_replacements in this entry.', 'changed' => [], 'links' => [], 'ctas' => [], 'texts' => []];
            continue;
        }

        $stmt = db()->prepare('SELECT * FROM posts WHERE slug = :slug AND deleted_at IS NULL LIMIT 1');
        $stmt->execute([':slug' => $slug]);
        $post = $stmt->fetch();

        if ($post === false) {
            $report[$slug] = ['ok' => false, 'message' => 'No post with this slug.', 'changed' => [], 'links' => [], 'ctas' => [], 'texts' => []];
            continue;
        }

        $changed = [];
        foreach ($fields as $field => $value) {
            $value  = (string) $value;
            $before = (string) ($post[$field] ?? '');
            if ($before !== $value) {
                $changed[$field] = ['before' => $before, 'after' => $value];
            }
        }

        [$newBody, $linkReport, $ctaReport, $textReport] = bx_apply_body_operations((string) $post['body_md'], $linkRequests, $ctaRequests, $textRequests);
        $bodyChanged = $newBody !== (string) $post['body_md'];

        if ($changed === [] && !$bodyChanged) {
            $report[$slug] = ['ok' => true, 'message' => 'Already up to date.', 'changed' => [], 'links' => $linkReport, 'ctas' => $ctaReport, 'texts' => $textReport];
            continue;
        }

        if ($dryRun) {
            $parts = [];
            if ($changed !== []) {
                $parts[] = count($changed) . ' field(s)';
            }
            if ($bodyChanged) {
                $parts[] = 'body changes';
            }
            $report[$slug] = ['ok' => true, 'message' => 'Would update: ' . implode(', ', $parts) . '.', 'changed' => $changed, 'links' => $linkReport, 'ctas' => $ctaReport, 'texts' => $textReport];
            continue;
        }

        $sets   = [];
        $params = [':id' => $post['id']];
        foreach ($changed as $field => $diff) {
            $sets[]             = "`$field` = :$field";
            $params[":$field"] = $diff['after'];
        }
        if ($bodyChanged) {
            $sets[]            = 'body_md = :body_md';
            $params[':body_md'] = $newBody;
        }

        if (!empty($post['draft_payload'])) {
            $draft = json_decode((string) $post['draft_payload'], true);
            if (is_array($draft)) {
                $draftTouched = false;

                foreach ($changed as $field => $diff) {
                    if (array_key_exists($field, $draft) && $draft[$field] !== $diff['after']) {
                        $draft[$field] = $diff['after'];
                        $draftTouched  = true;
                    }
                }

                if ($bodyChanged && array_key_exists('body_md', $draft)) {
                    [$draftBody, , ,] = bx_apply_body_operations((string) $draft['body_md'], $linkRequests, $ctaRequests, $textRequests);
                    if ($draftBody !== $draft['body_md']) {
                        $draft['body_md'] = $draftBody;
                        $draftTouched     = true;
                    }
                }

                if ($draftTouched) {
                    $sets[]                   = 'draft_payload = :draft_payload';
                    $params[':draft_payload'] = json_encode($draft);
                }
            }
        }

        db()->prepare('UPDATE posts SET ' . implode(', ', $sets) . ' WHERE id = :id')->execute($params);

        $parts = [];
        if ($changed !== []) {
            $parts[] = count($changed) . ' field(s) updated';
        }
        if ($bodyChanged) {
            $parts[] = 'body updated';
        }
        $report[$slug] = ['ok' => true, 'message' => implode(', ', $parts) . '.', 'changed' => $changed, 'links' => $linkReport, 'ctas' => $ctaReport, 'texts' => $textReport];
    }

    return $report;
}
