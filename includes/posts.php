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
