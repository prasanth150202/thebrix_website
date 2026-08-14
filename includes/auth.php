<?php
/**
 * Admin authentication.
 *
 * Single account, bcrypt hash in the database, PHP session. Failed
 * attempts are recorded per IP and per username so that a brute force
 * against /admin gets slow very quickly, while a legitimate person
 * who fat-fingers their password a couple of times is unaffected.
 */

declare(strict_types=1);

const LOGIN_MAX_ATTEMPTS  = 6;
const LOGIN_WINDOW_MIN    = 15;
const SESSION_IDLE_MIN    = 240;

function admin_user(): ?array
{
    brix_session_start();

    if (empty($_SESSION['admin_id'])) {
        return null;
    }

    // Expire an abandoned session rather than leaving the panel open
    // indefinitely on a shared machine.
    $last = $_SESSION['admin_seen'] ?? 0;
    if ($last && (time() - $last) > SESSION_IDLE_MIN * 60) {
        admin_logout();
        return null;
    }
    $_SESSION['admin_seen'] = time();

    static $user = null;
    if ($user !== null) {
        return $user;
    }

    $stmt = db()->prepare('SELECT id, username, display_name FROM admin_users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $_SESSION['admin_id']]);

    return $user = ($stmt->fetch() ?: null);
}

function require_admin(): array
{
    $user = admin_user();

    if ($user === null) {
        $target = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
        $query  = $_SERVER['QUERY_STRING'] ?? '';
        redirect('login.php?next=' . urlencode($target . ($query !== '' ? '?' . $query : '')));
    }

    return $user;
}

/** Count recent failures for this IP. */
function login_attempts_recent(string $ip): int
{
    // The window is a compile-time constant, not user input, so it is
    // interpolated directly. A placeholder in the INTERVAL position is
    // not portable across MySQL versions with real prepared statements.
    $stmt = db()->prepare(
        'SELECT COUNT(*) FROM login_attempts
         WHERE ip = :ip AND attempted_at > (NOW() - INTERVAL ' . (int) LOGIN_WINDOW_MIN . ' MINUTE)'
    );
    $stmt->execute([':ip' => $ip]);

    return (int) $stmt->fetchColumn();
}

function login_is_locked(string $ip): bool
{
    return login_attempts_recent($ip) >= LOGIN_MAX_ATTEMPTS;
}

function record_login_failure(string $ip, string $username): void
{
    $stmt = db()->prepare(
        'INSERT INTO login_attempts (ip, username, attempted_at) VALUES (:ip, :u, NOW())'
    );
    $stmt->execute([':ip' => $ip, ':u' => mb_substr($username, 0, 100)]);
}

function clear_login_failures(string $ip): void
{
    $stmt = db()->prepare('DELETE FROM login_attempts WHERE ip = :ip');
    $stmt->execute([':ip' => $ip]);
}

/**
 * Verify credentials and open a session.
 *
 * Returns an error string on failure, null on success. The message is
 * deliberately the same for "no such user" and "wrong password" so
 * the form cannot be used to discover the username.
 */
function admin_login(string $username, string $password): ?string
{
    $ip = client_ip();

    if (login_is_locked($ip)) {
        return 'Too many failed attempts. Try again in ' . LOGIN_WINDOW_MIN . ' minutes.';
    }

    $stmt = db()->prepare('SELECT * FROM admin_users WHERE username = :u LIMIT 1');
    $stmt->execute([':u' => $username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        record_login_failure($ip, $username);

        $left = LOGIN_MAX_ATTEMPTS - login_attempts_recent($ip);
        $suffix = $left > 0 && $left <= 3 ? ' ' . $left . ' attempt' . ($left === 1 ? '' : 's') . ' left.' : '';

        return 'Incorrect username or password.' . $suffix;
    }

    // Rehash if PHP's default cost has moved on since the account was
    // created.
    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
        $rehash = db()->prepare('UPDATE admin_users SET password_hash = :h WHERE id = :id');
        $rehash->execute([
            ':h'  => password_hash($password, PASSWORD_DEFAULT),
            ':id' => $user['id'],
        ]);
    }

    brix_session_start();
    session_regenerate_id(true);

    $_SESSION['admin_id']   = (int) $user['id'];
    $_SESSION['admin_seen'] = time();

    clear_login_failures($ip);

    $touch = db()->prepare('UPDATE admin_users SET last_login_at = NOW() WHERE id = :id');
    $touch->execute([':id' => $user['id']]);

    return null;
}

function admin_logout(): void
{
    brix_session_start();

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }

    session_destroy();
}

/** True when at least one admin account exists. */
function admin_account_exists(): bool
{
    try {
        return (int) db()->query('SELECT COUNT(*) FROM admin_users')->fetchColumn() > 0;
    } catch (Throwable) {
        return false;
    }
}
