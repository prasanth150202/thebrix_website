<?php
/**
 * Newsletter signup endpoint, deliberately named after the memo rather
 * than after what it does.
 *
 * It used to be newsletter-subscribe.php, and paths containing
 * "newsletter" or "subscribe" are on the annoyance filter lists that
 * Brave Shields, uBlock and AdBlock ship with. The request was being
 * cancelled in the browser before it was ever sent, so signups from
 * anyone running a blocker were lost in silence. Keep this name boring.
 *
 * Until recently the footer form only pretended to work: main.js
 * disabled the input and showed the confirmation without sending
 * anything anywhere, so every address entered was lost. This stores
 * them.
 *
 * Re-subscribing with an address already on the list is treated as
 * success rather than an error, both because it is what the person
 * intended and because a "you are already subscribed" response would
 * turn the form into a way to test whether an address is on the list.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'Method not allowed.'], 405);
}

$email = trim((string) ($_POST['email'] ?? ''));

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
    json_response(['ok' => false, 'error' => 'Please enter a valid email address.'], 400);
}

// A bot filling every field on the page trips this.
if (trim((string) ($_POST['website'] ?? '')) !== '') {
    json_response(['ok' => true]);
}

try {
    $ip = client_ip();

    $recent = db()->prepare(
        'SELECT COUNT(*) FROM newsletter_subscribers
         WHERE ip = :ip AND created_at > (NOW() - INTERVAL 1 HOUR)'
    );
    $recent->execute([':ip' => $ip]);

    if ((int) $recent->fetchColumn() >= 10) {
        json_response(['ok' => false, 'error' => 'Too many signups from here. Try again later.'], 429);
    }

    $stmt = db()->prepare(
        'INSERT INTO newsletter_subscribers
            (email, source_page, utm_source, utm_medium, utm_campaign, ip)
         VALUES (:email, :page, :us, :um, :uc, :ip)
         ON DUPLICATE KEY UPDATE id = id'
    );

    $stmt->execute([
        ':email' => mb_strtolower($email),
        ':page'  => mb_substr((string) ($_POST['page'] ?? ''), 0, 190),
        ':us'    => mb_substr((string) ($_POST['utm_source'] ?? ''), 0, 120),
        ':um'    => mb_substr((string) ($_POST['utm_medium'] ?? ''), 0, 120),
        ':uc'    => mb_substr((string) ($_POST['utm_campaign'] ?? ''), 0, 120),
        ':ip'    => $ip,
    ]);

    json_response(['ok' => true]);
} catch (Throwable) {
    json_response(['ok' => false, 'error' => 'Could not sign you up just now. Please try again.'], 500);
}
