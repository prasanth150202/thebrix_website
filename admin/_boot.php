<?php
/**
 * Every admin page starts here.
 *
 * Loads the shared code, and if the site has not been installed yet
 * sends the visitor to the wizard instead of showing a stack trace.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

if (!brix_is_configured()) {
    if (basename($_SERVER['SCRIPT_NAME'] ?? '') !== 'setup.php') {
        redirect('setup.php');
    }
}

require_once BRIX_INCLUDES . '/posts.php';
require_once BRIX_INCLUDES . '/auth.php';
require_once BRIX_INCLUDES . '/markdown.php';
require_once BRIX_INCLUDES . '/schema.php';
require_once __DIR__ . '/_layout.php';

/**
 * A database created before a column was added upgrades itself the
 * first time an admin page is opened, so a deploy never needs hand-run
 * SQL. Once per session per schema version: the check is two cheap
 * queries, but there is no reason to repeat it on every page view.
 */
if (brix_is_configured()) {
    brix_session_start();
}

if (brix_is_configured() && ($_SESSION['brix_schema_checked'] ?? null) !== BRIX_SCHEMA_VERSION) {
    try {
        brix_upgrade_schema(db());
        $_SESSION['brix_schema_checked'] = BRIX_SCHEMA_VERSION;
    } catch (Throwable) {
        // An unreachable or read-only database is reported by the page
        // itself; it must not stop the login form from rendering.
    }
}
