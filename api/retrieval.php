<?php
/**
 * Lightweight, dependency-free retrieval over the knowledge/*.md files.
 *
 * Each markdown file is split into sections on "## " headings (the "# " title
 * is kept as context for every section in that file). At request time the
 * question is scored against every section by keyword overlap and only the
 * top matches are returned; the full knowledge base is never sent in one
 * shot. Sections are parsed once per request and cached in a static.
 *
 * Ported from retrieval.js. The scoring is deliberately identical, so a
 * question that retrieved a given section under the Node handler retrieves
 * the same one here.
 */

declare(strict_types=1);

function brix_knowledge_dir(): string
{
    return dirname(__DIR__) . '/knowledge';
}

function brix_stopwords(): array
{
    static $stopwords = null;
    if ($stopwords !== null) {
        return $stopwords;
    }

    return $stopwords = array_flip([
        'the', 'a', 'an', 'and', 'or', 'but', 'is', 'are', 'was', 'were', 'be', 'been',
        'to', 'of', 'in', 'on', 'for', 'with', 'as', 'at', 'by', 'it', 'this', 'that',
        'my', 'your', 'i', 'you', 'we', 'do', 'does', 'did', 'can', 'how', 'what',
        'where', 'when', 'why', 'which', 'who', 'will', 'would', 'should', 'could',
        'have', 'has', 'had', 'not', 'no', 'yes', 'if', 'so', 'than', 'then', 'from',
        'about', 'into', 'up', 'out', 'get', 'me', 'am', 'its', 'their', 'there',
        'today', 'now', 'here', 'please', 'just', 'want', 'like', 'need', 'also',
        'any', 'some', 'all', 'one', 'two', 'more', 'much', 'many', 'other',
    ]);
}

/**
 * Lowercase, split on runs of [a-z0-9], drop stopwords and anything shorter
 * than three characters.
 */
function brix_tokenize(string $text): array
{
    preg_match_all('/[a-z0-9]+/', strtolower($text), $matches);

    $stopwords = brix_stopwords();
    $tokens    = [];

    foreach ($matches[0] as $word) {
        if (strlen($word) > 2 && !isset($stopwords[$word])) {
            $tokens[] = $word;
        }
    }

    return $tokens;
}

/**
 * Parse every knowledge/*.md file into sections. Token sets are stored as
 * array keys so membership is a hash lookup rather than a scan.
 */
function brix_load_sections(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $files = glob(brix_knowledge_dir() . '/*.md');
    if ($files === false) {
        error_log('[BRIX chat] could not read the knowledge/ directory');
        return $cache = [];
    }
    sort($files);

    $sections = [];

    foreach ($files as $path) {
        $raw = @file_get_contents($path);
        if ($raw === false) {
            continue;
        }

        $file    = basename($path);
        $title   = (string) preg_replace('/\.md$/', '', $file);
        $heading = $title;
        $buffer  = [];

        $flush = function () use (&$sections, &$buffer, &$title, &$heading, $file): void {
            $text = trim(implode("\n", $buffer));
            $buffer = [];

            if ($text === '') {
                return;
            }

            $sections[] = [
                'file'          => $file,
                'title'         => $title,
                'heading'       => $heading,
                'text'          => $text,
                'tokens'        => array_flip(brix_tokenize($heading . ' ' . $text)),
                'headingTokens' => array_flip(brix_tokenize($heading)),
            ];
        };

        foreach (explode("\n", $raw) as $line) {
            // "# " before "## ": a second hash is not whitespace, so an h2
            // line never matches the h1 pattern.
            if (preg_match('/^#\s+(.*)/', $line, $h1)) {
                $flush();
                $title   = trim($h1[1]);
                $heading = $title;
                continue;
            }
            if (preg_match('/^##\s+(.*)/', $line, $h2)) {
                $flush();
                $heading = trim($h2[1]);
                continue;
            }
            $buffer[] = $line;
        }

        $flush();
    }

    return $cache = $sections;
}

/**
 * Score every section against the query by keyword overlap. Heading matches
 * count double since a heading hit is a strong topical signal.
 */
function brix_score_sections(string $query): array
{
    $sections    = brix_load_sections();
    $queryTokens = brix_tokenize($query);
    $scored      = [];

    foreach ($sections as $section) {
        $score = 0;

        foreach ($queryTokens as $token) {
            if (isset($section['headingTokens'][$token])) {
                $score += 2;
            } elseif (isset($section['tokens'][$token])) {
                $score += 1;
            }
        }

        $scored[] = ['section' => $section, 'score' => $score];
    }

    return $scored;
}

/**
 * The top-K most relevant sections for a question. Falls back to a small
 * default set (overview + features) if nothing scores above zero, so a
 * legitimate but oddly-phrased BRIX question still gets grounded context.
 */
function brix_retrieve_relevant(string $query, int $topK = 5): array
{
    $scored = brix_score_sections($query);

    // Stable since PHP 8.0, so equal scores keep their knowledge-base order.
    usort($scored, static fn(array $a, array $b): int => $b['score'] <=> $a['score']);

    $hits = [];
    foreach ($scored as $entry) {
        if ($entry['score'] <= 0 || count($hits) >= $topK) {
            break;
        }
        $hits[] = $entry['section'];
    }

    if ($hits !== []) {
        return $hits;
    }

    return array_values(array_filter(
        brix_load_sections(),
        static fn(array $s): bool => $s['file'] === 'overview.md' || $s['file'] === 'features.md'
    ));
}

/** Highest score across all sections, used by the scope guard. */
function brix_best_score(string $query): int
{
    $best = 0;

    foreach (brix_score_sections($query) as $entry) {
        if ($entry['score'] > $best) {
            $best = $entry['score'];
        }
    }

    return $best;
}

function brix_format_context(array $sections): string
{
    $parts = [];

    foreach ($sections as $section) {
        $suffix = $section['heading'] !== $section['title'] ? ': ' . $section['heading'] : '';
        $parts[] = '### ' . $section['title'] . $suffix . "\n" . $section['text'];
    }

    return implode("\n\n---\n\n", $parts);
}
