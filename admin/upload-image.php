<?php
/**
 * Image upload for the post editor.
 *
 * Validation is deliberately strict and does not trust the browser:
 * the MIME type the browser sends is ignored in favour of what the
 * bytes actually are, the extension is rewritten from the detected
 * type, and the filename is regenerated rather than sanitised. SVG is
 * accepted but stored as-is, so it is only offered because the only
 * person who can reach this endpoint is the signed-in admin.
 */

declare(strict_types=1);

require_once __DIR__ . '/_boot.php';

header('Content-Type: application/json; charset=utf-8');

if (admin_user() === null) {
    json_response(['ok' => false, 'error' => 'Not signed in.'], 401);
}

if (!csrf_check($_POST['csrf'] ?? null)) {
    json_response(['ok' => false, 'error' => 'Session expired. Reload the page.'], 403);
}

$file = $_FILES['image'] ?? null;

if (!$file || !is_uploaded_file($file['tmp_name'] ?? '')) {
    $code = $file['error'] ?? UPLOAD_ERR_NO_FILE;
    $why  = $code === UPLOAD_ERR_INI_SIZE || $code === UPLOAD_ERR_FORM_SIZE
        ? 'That file is larger than the server allows.'
        : 'No file was received.';
    json_response(['ok' => false, 'error' => $why], 400);
}

const MAX_IMAGE_BYTES = 4 * 1024 * 1024;

if ($file['size'] > MAX_IMAGE_BYTES) {
    json_response(['ok' => false, 'error' => 'Images must be under 4 MB.'], 400);
}

$allowed = [
    'image/jpeg'    => 'jpg',
    'image/png'     => 'png',
    'image/gif'     => 'gif',
    'image/webp'    => 'webp',
    'image/svg+xml' => 'svg',
];

$finfo    = new finfo(FILEINFO_MIME_TYPE);
$detected = (string) $finfo->file($file['tmp_name']);

// finfo reports SVG as plain XML or text, so confirm it separately.
if (!isset($allowed[$detected])) {
    $head = (string) file_get_contents($file['tmp_name'], false, null, 0, 512);
    if (stripos($head, '<svg') !== false) {
        $detected = 'image/svg+xml';
    }
}

if (!isset($allowed[$detected])) {
    json_response([
        'ok'    => false,
        'error' => 'That is not an image the site can use. Allowed: JPG, PNG, GIF, WebP, SVG.',
    ], 400);
}

// Raster images must actually decode, which rules out a script that
// merely starts with image magic bytes.
if ($detected !== 'image/svg+xml' && @getimagesize($file['tmp_name']) === false) {
    json_response(['ok' => false, 'error' => 'That image could not be read.'], 400);
}

$ext  = $allowed[$detected];
$base = slugify(pathinfo((string) $file['name'], PATHINFO_FILENAME));
$base = $base !== '' ? truncate_words($base, 60) : 'image';
$base = rtrim(str_replace('...', '', $base), '-');

$dir = BRIX_ROOT . '/assets/blog';
if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
    json_response(['ok' => false, 'error' => 'The upload folder does not exist.'], 500);
}
if (!is_writable($dir)) {
    json_response(['ok' => false, 'error' => 'The upload folder is not writable.'], 500);
}

$name = date('Y-m') . '-' . $base . '-' . bin2hex(random_bytes(3)) . '.' . $ext;

if (!@move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
    json_response(['ok' => false, 'error' => 'Could not save the file.'], 500);
}

@chmod($dir . '/' . $name, 0644);

json_response(['ok' => true, 'url' => 'assets/blog/' . $name]);
