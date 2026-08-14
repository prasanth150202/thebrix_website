<?php
/**
 * Shared bootstrap. Every entry point starts here.
 *
 * Loads configuration, opens the database connection lazily, and sets
 * up sessions. Nothing in here emits output, so it is safe to include
 * before headers are sent.
 */

declare(strict_types=1);

define('BRIX_ROOT', dirname(__DIR__));
define('BRIX_INCLUDES', __DIR__);

// Asset cache-busting versions. These match what the static pages were
// already using, so converting a page to PHP does not silently change
// which cached copy a returning visitor gets.
define('ASSET_CSS_VER', '28');
define('ASSET_JS_VER', '7');
define('ASSET_UTM_VER', '2');

define('SITE_URL', 'https://thebrix.io');
define('SHOPIFY_APP_URL', 'https://apps.shopify.com/thebrix-io?utm_source=Brix-Website&utm_medium=Organic&utm_campaign=Website_Tracking&utm_id=Website');

require_once BRIX_INCLUDES . '/functions.php';

/**
 * Configuration is written by admin/setup.php and is deliberately not
 * in version control. Fall back to environment variables so the same
 * code can run in a container or on a host where the file cannot be
 * written.
 */
function brix_config(): array
{
    static $config = null;
    if ($config !== null) {
        return $config;
    }

    $file = BRIX_INCLUDES . '/config.php';
    if (is_readable($file)) {
        /** @var array $loaded */
        $loaded = require $file;
        if (is_array($loaded) && isset($loaded['db_name'])) {
            return $config = $loaded;
        }
    }

    if (getenv('BRIX_DB_NAME') !== false) {
        return $config = [
            'db_host' => getenv('BRIX_DB_HOST') ?: 'localhost',
            'db_name' => getenv('BRIX_DB_NAME'),
            'db_user' => getenv('BRIX_DB_USER') ?: '',
            'db_pass' => getenv('BRIX_DB_PASS') ?: '',
            'db_port' => (int) (getenv('BRIX_DB_PORT') ?: 3306),
        ];
    }

    return $config = [];
}

function brix_is_configured(): bool
{
    $c = brix_config();
    return !empty($c['db_name']);
}

/**
 * PDO handle. Throws on failure so callers can decide whether to show
 * a friendly page or an error, rather than half-rendering a page with
 * missing content.
 */
function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $c = brix_config();
    if (empty($c['db_name'])) {
        throw new RuntimeException('Brix is not configured yet. Run /admin/setup.php');
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $c['db_host'] ?? 'localhost',
        (int) ($c['db_port'] ?? 3306),
        $c['db_name']
    );

    $pdo = new PDO($dsn, $c['db_user'] ?? '', $c['db_pass'] ?? '', [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    return $pdo;
}

/**
 * Sessions are only needed for the admin panel and for CSRF tokens on
 * the public forms, so start one on demand rather than on every page
 * view (a session cookie would otherwise defeat page caching).
 */
function brix_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_name('brixsess');
    session_start();
}
