<?php
/**
 * Autosave.
 *
 * Called every minute while the editor is open, and once more just
 * before a preview is opened. It only ever writes a draft:
 *
 *   - a post that does not exist yet is created with status = draft,
 *   - a post that is already a draft has its columns updated,
 *   - a post that is LIVE has the edit stored in draft_payload and its
 *     public columns left completely alone.
 *
 * So nothing typed here can reach the public site. Only the Publish
 * button in post-edit.php does that.
 */

declare(strict_types=1);

require_once __DIR__ . '/_boot.php';

header('Content-Type: application/json; charset=utf-8');

if (admin_user() === null) {
    json_response(['ok' => false, 'error' => 'Signed out.'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check($_POST['csrf'] ?? null)) {
    json_response(['ok' => false, 'error' => 'Session expired. Reload the page.'], 403);
}

$id   = (int) ($_POST['id'] ?? 0);
$post = $id > 0 ? get_post($id) : null;

if ($id > 0 && $post === null) {
    json_response(['ok' => false, 'error' => 'That post no longer exists.'], 404);
}

/* ---------- collect the editable fields ---------- */

$fields = [];
foreach (post_editable_fields() as $f) {
    $fields[$f] = is_string($_POST[$f] ?? null) ? trim($_POST[$f]) : '';
}

if (!isset(POST_TYPES[$fields['type']])) {
    $fields['type'] = 'blog';
}
if (!array_key_exists($fields['card_gradient'], card_gradients())) {
    $fields['card_gradient'] = 'bshot-1';
}
if (!array_key_exists($fields['card_icon'], card_icons())) {
    $fields['card_icon'] = 'cart';
}
if ($fields['date_published'] === '' || strtotime($fields['date_published']) === false) {
    $fields['date_published'] = date('Y-m-d');
}

$fields['hero_image'] = safe_asset_path($fields['hero_image']);
$fields['hero_blur']  = clamp_hero_blur($fields['hero_blur']);

$fields['read_minutes'] = (int) $fields['read_minutes'] > 0
    ? (int) $fields['read_minutes']
    : estimate_read_minutes($fields['body_md']);

// Nothing worth saving yet: an empty shell should not create a row.
if (trim($fields['title']) === '' && trim($fields['body_md']) === '') {
    json_response(['ok' => true, 'skipped' => true, 'id' => $id]);
}

if ($fields['title'] === '') {
    $fields['title'] = (string) (markdown_first_heading($fields['body_md']) ?? 'Untitled draft');
}

/**
 * The address, on the same terms as the editor: it follows the title
 * until the box has been typed into. It arrives as the last segment
 * only, so the section prefix goes back on here, and the draft row and
 * the staged payload then hold exactly what a real save would write.
 */
$slugAuto = ($_POST['slug_auto'] ?? '') === '1';

$fields['slug'] = normalise_slug_input(
    $slugAuto || $fields['slug'] === '' ? $fields['title'] : $fields['slug'],
    $fields['type']
);

// An autosave must never fail over an address. If the one on screen is
// taken, keep the address the row already has and let the editor's own
// save be the thing that reports the clash.
if ($fields['slug'] === '' || !slug_is_available($fields['slug'], $post['id'] ?? null)) {
    $fields['slug'] = $post !== null
        ? (string) $post['slug']
        : unique_slug($fields['slug'] !== ''
            ? $fields['slug']
            : post_slug_prefix($fields['type']) . 'untitled');
}

try {
    /* ---------- a live post: stage the edit, touch nothing public ---------- */
    if ($post !== null && $post['status'] === 'published') {
        db()->prepare(
            'UPDATE posts SET draft_payload = :p, draft_saved_at = NOW() WHERE id = :id'
        )->execute([':p' => json_encode($fields), ':id' => $post['id']]);

        json_response([
            'ok'      => true,
            'id'      => (int) $post['id'],
            'staged'  => true,
            'savedAt' => date('g:i a'),
        ]);
    }

    /* ---------- otherwise write the draft row itself ---------- */
    $cols = array_keys($fields);

    if ($post === null) {
        // `slug` is one of the editable fields now, so it is already in
        // $cols and must not be appended a second time.
        $insertCols = array_merge($cols, ['status', 'draft_saved_at']);
        $sql = 'INSERT INTO posts (' . implode(', ', $insertCols) . ') VALUES ('
             . implode(', ', array_map(static fn($c) => ':' . $c, $cols))
             . ', "draft", NOW())';

        $params = [];
        foreach ($fields as $k => $v) {
            $params[':' . $k] = $v;
        }

        db()->prepare($sql)->execute($params);
        $newId = (int) db()->lastInsertId();

        json_response(['ok' => true, 'id' => $newId, 'created' => true, 'savedAt' => date('g:i a')]);
    }

    $sets = implode(', ', array_map(static fn($c) => $c . ' = :' . $c, $cols));
    $params = [];
    foreach ($fields as $k => $v) {
        $params[':' . $k] = $v;
    }
    $params[':id'] = $post['id'];

    db()->prepare(
        'UPDATE posts SET ' . $sets . ', draft_saved_at = NOW() WHERE id = :id'
    )->execute($params);

    json_response(['ok' => true, 'id' => (int) $post['id'], 'savedAt' => date('g:i a')]);

} catch (Throwable $ex) {
    json_response(['ok' => false, 'error' => 'Could not autosave.'], 500);
}
