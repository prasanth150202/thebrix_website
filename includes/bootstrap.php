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
define('ASSET_CSS_VER', '42');
define('ASSET_JS_VER', '17');
define('ASSET_UTM_VER', '3');
// Only /tutorials loads this one, so it carries its own version and
// bumping it does not throw away everyone's cached main.js.
define('ASSET_TUTORIALS_VER', '2');
// Same idea for the campaign landing page at /cart-that-sells.
define('ASSET_LANDING_VER', '3');
// The long landing page at /walkthrough carries its own GSAP timeline.
define('ASSET_WALKTHROUGH_VER', '2');

/**
 * The floating "Ask Brix AI" launcher. Three settings:
 *
 *   true        Full assistant. A text box posts to /api/chat, which is
 *               api/chat.php: a classifier over the 58 curated answers in
 *               api/answers.php, falling back to retrieval over knowledge/
 *               for questions nobody pre-wrote. The common questions are
 *               offered once as an opener and do not return, because there
 *               a visitor who has started typing has moved past them.
 *
 *   'partial'   No text box. The common questions are the whole interface,
 *               so they come back after every answer. Every answer is
 *               written by hand in js/main.js and no request leaves the
 *               visitor's page, so this mode costs nothing to run and
 *               cannot answer wrongly.
 *
 *   false       No launcher at all.
 *
 * Currently 'partial'. The backend is finished and correct, but both of its
 * stages run on OpenRouter's free tier, which allows 50 requests a day per
 * account and 20 a minute; a live text box spent the day's allowance by
 * mid-morning and then told every visitor it was unavailable. Buying 10
 * credits once raises that to 1000 a day and is not consumed by these models,
 * which is the one step between here and true. See docs/brix-ai.md.
 */
define('BRIX_CHAT_ENABLED', 'partial');

define('SITE_URL', 'https://thebrix.io');
define('SHOPIFY_APP_URL', 'https://apps.shopify.com/thebrix-io?utm_source=Brix-Website&utm_medium=Organic&utm_campaign=Website_Tracking&utm_id=Website');

require_once BRIX_INCLUDES . '/functions.php';

/**
 * A .env in the project root is treated as environment variables, for
 * hosts where the credentials are uploaded as a file rather than set on
 * the process. Anything already in the real environment wins, so this
 * can never override what the host itself provides.
 *
 * Never web-readable: .htaccess denies .env by name.
 */
function brix_load_env(): void
{
    static $loaded = false;
    if ($loaded) {
        return;
    }
    $loaded = true;

    $file = BRIX_ROOT . '/.env';
    if (!is_readable($file)) {
        return;
    }

    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);

        // strip one matching pair of surrounding quotes, if present
        if (strlen($value) > 1 && ($value[0] === '"' || $value[0] === "'")
            && $value[strlen($value) - 1] === $value[0]) {
            $value = substr($value, 1, -1);
        }

        if ($key === '' || getenv($key) !== false) {
            continue;
        }

        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
    }
}

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

    brix_load_env();

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
