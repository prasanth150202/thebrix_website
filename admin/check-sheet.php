<?php
/**
 * The Sheet mirror diagnostic, retired.
 *
 * It did its job: it found that PHP could see the settings, that the URL
 * was the right one, and that the script was answering - which narrowed
 * a silent failure down to the signature in one run.
 *
 * Deleting it from the repository would not have removed it from the
 * server. This deploy replaces files but never deletes them, the same
 * reason admin/setup.php is a stub rather than gone, so the page would
 * have sat there indefinitely. Overwriting it achieves what deleting it
 * was meant to.
 *
 * It also had to go rather than be left switched off. It posted to
 * Google through its own copy of the request rather than through
 * brix_lead_to_sheet(), so when the signature moved to the query string
 * only the real path was fixed and this one kept reporting a failure
 * that no longer existed - a diagnostic disagreeing with the thing it
 * describes is worse than no diagnostic.
 *
 * If the mirror ever needs debugging again, do not restore this as it
 * was. Post through includes/sheets.php so the two cannot drift:
 *   git checkout 5277ee6 -- admin/check-sheet.php
 *
 * No auth check and nothing conditional. The page has nothing left to
 * protect, and index.php never sends anyone here, so this cannot loop.
 */

declare(strict_types=1);

require_once __DIR__ . '/_boot.php';

redirect('index.php');
