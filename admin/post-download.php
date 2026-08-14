<?php
/**
 * Download a post as a .md file.
 *
 * The metadata is written out as a YAML front matter block so the
 * file is a complete record of the post, not just its prose. Editing
 * it offline and re-uploading through the editor keeps the body; the
 * front matter is there for reference and for the migration importer.
 */

declare(strict_types=1);

require_once __DIR__ . '/_boot.php';

require_admin();

$post = get_post((int) ($_GET['id'] ?? 0));

if ($post === null) {
    flash('That post no longer exists.', 'err');
    redirect('index.php');
}

$meta = [
    'title'          => $post['title'],
    'type'           => $post['type'],
    'slug'           => $post['slug'],
    'author'         => $post['author'],
    'category'       => $post['category'],
    'date'           => $post['date_published'],
    'read_minutes'   => $post['read_minutes'],
    'hero_subtitle'  => $post['hero_subtitle'],
    'hero_image'     => $post['hero_image'] ?? '',
    'hero_blur'      => $post['hero_blur'] ?? 0,
    'excerpt'        => $post['excerpt'],
    'card_gradient'  => $post['card_gradient'],
    'card_icon'      => $post['card_icon'],
];

$meta += [
    'cta_heading'      => $post['cta_heading'],
    'cta_sub'          => $post['cta_sub'],
    'meta_title'       => $post['meta_title'],
    'meta_description' => $post['meta_description'],
    'status'           => $post['status'],
];

/** Quote anything that would otherwise confuse a YAML reader. */
$yamlValue = static function ($value): string {
    $value = (string) $value;

    if ($value === '') {
        return '""';
    }
    if (preg_match('/[:#\n"\'\[\]{}|>&*!%@`]/', $value) || trim($value) !== $value) {
        return '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"';
    }

    return $value;
};

$front = "---\n";
foreach ($meta as $key => $value) {
    $front .= $key . ': ' . $yamlValue($value) . "\n";
}
$front .= "---\n\n";

$filename = $post['slug'] . '.md';

header('Content-Type: text/markdown; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('X-Content-Type-Options: nosniff');

echo $front . normalise_newlines($post['body_md']) . "\n";
