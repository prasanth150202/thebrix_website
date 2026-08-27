<?php
/**
 * The lead form behind the campaign landing pages.
 *
 * Same table and same admin screen as the contact page, tagged with the
 * page that captured it so a campaign lead and a support enquiry can be
 * told apart. contact.php is deliberately left alone: it works, and a
 * shared abstraction over two forms with different fields would have
 * bought nothing but a way to break it.
 *
 * Spam handling matches the contact page, because the same bots find
 * both: a honeypot field, a minimum time on page, and a per-IP rate
 * limit. No CAPTCHA, which costs more conversions than it saves.
 *
 * Nothing here emits output.
 */

declare(strict_types=1);

/**
 * Blank values for a lead form, so a page can render its inputs before
 * anything has been posted.
 */
function brix_lead_blank(): array
{
    return ['name' => '', 'email' => '', 'store_url' => '', 'message' => ''];
}

/**
 * Handle a POST from a landing-page form.
 *
 * $source    the page that captured it, stored against the row
 * $questions extra POST keys to fold into the message, as key => label.
 *            A landing page asks one or two qualifying questions that
 *            are worth reading but not worth their own columns.
 *
 * Returns ['sent' => bool, 'errors' => string[], 'values' => array].
 * On success the values come back blank so the form renders empty.
 */
function brix_lead_handle(string $source, array $questions = []): array
{
    $values = brix_lead_blank();
    $errors = [];

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        return ['sent' => false, 'errors' => $errors, 'values' => $values];
    }

    foreach ($values as $k => $_) {
        $values[$k] = trim((string) ($_POST[$k] ?? ''));
    }

    if (!csrf_check($_POST['csrf'] ?? null)) {
        $errors[] = 'That form sat open too long. Please send it again.';
    }

    // A real person never sees this field, so anything in it is a bot.
    $trapped = trim((string) ($_POST['website'] ?? '')) !== '';

    // Nothing filled in within two seconds of the page loading was typed.
    $startedAt = (int) ($_POST['t'] ?? 0);
    $tooFast   = $startedAt > 0 && (time() - $startedAt) < 2;

    if ($values['name'] === '') {
        $errors[] = 'Please tell us your name.';
    }
    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'That email address does not look right.';
    }
    if ($values['store_url'] !== ''
        && !preg_match('#^(https?://)?[\w.-]+\.[a-z]{2,}#i', $values['store_url'])) {
        $errors[] = 'That store URL does not look right. Leave it blank if you would rather not say.';
    }
    if (mb_strlen($values['message']) > 5000) {
        $errors[] = 'That is a little long. Please keep it under 5,000 characters.';
    }

    // Five a hour from one address, same as the contact page.
    if (!$errors && !$trapped && !$tooFast) {
        try {
            $recent = db()->prepare(
                'SELECT COUNT(*) FROM contact_submissions
                 WHERE ip = :ip AND created_at > (NOW() - INTERVAL 1 HOUR)'
            );
            $recent->execute([':ip' => client_ip()]);

            if ((int) $recent->fetchColumn() >= 5) {
                $errors[] = 'You have sent this a few times already. Give us a little time to reply.';
            }
        } catch (Throwable) {
            $errors[] = 'Something went wrong at our end. Please email us directly.';
        }
    }

    if ($errors) {
        return ['sent' => false, 'errors' => $errors, 'values' => $values];
    }

    if ($trapped || $tooFast) {
        // Look successful to the bot without storing anything.
        return ['sent' => true, 'errors' => [], 'values' => brix_lead_blank()];
    }

    /* The qualifying answers are worth reading but not worth a column
       each, so they go into the message the admin already renders. */
    $lines = [];
    foreach ($questions as $key => $label) {
        $answer = trim((string) ($_POST[$key] ?? ''));
        if ($answer !== '') {
            $lines[] = $label . ': ' . mb_substr($answer, 0, 200);
        }
    }
    if ($values['message'] !== '') {
        $lines[] = $values['message'];
    }
    if (!$lines) {
        $lines[] = 'Asked to be shown around. No extra detail given.';
    }
    $message = implode("\n", $lines);

    $url = $values['store_url'];
    if ($url !== '' && !preg_match('#^https?://#i', $url)) {
        $url = 'https://' . $url;
    }

    /* One row per person, exactly as the contact page does it: writing
       in again replaces what they said rather than queueing a duplicate,
       and clears read_at so it comes back as unread. created_at is left
       alone, because that is when they first got in touch. */
    $sql = 'INSERT INTO contact_submissions (name, email, store_url, message, source, ip, user_agent)
            VALUES (:n, :e, :u, :m, :s, :ip, :ua)
            ON DUPLICATE KEY UPDATE
               name       = :n2,
               store_url  = :u2,
               message    = :m2,
               source     = :s2,
               ip         = :ip2,
               user_agent = :ua2,
               updated_at = NOW(),
               read_at    = NULL';

    $name      = mb_substr($values['name'], 0, 120);
    $storeUrl  = mb_substr($url, 0, 255);
    $src       = mb_substr($source, 0, 40);
    $ip        = client_ip();
    $userAgent = mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);

    $params = [
        ':n'   => $name,
        ':e'   => mb_substr($values['email'], 0, 190),
        ':u'   => $storeUrl,
        ':m'   => $message,
        ':s'   => $src,
        ':ip'  => $ip,
        ':ua'  => $userAgent,
        ':n2'  => $name,
        ':u2'  => $storeUrl,
        ':m2'  => $message,
        ':s2'  => $src,
        ':ip2' => $ip,
        ':ua2' => $userAgent,
    ];

    try {
        try {
            db()->prepare($sql)->execute($params);
        } catch (PDOException) {
            /* The source column arrives with the upgrade that runs when
               an admin next signs in. Between a deploy and that moment
               the lead would simply be lost, which is the one thing a
               lead form must not do, so bring the schema forward and try
               once more. The prepare has to be inside the retry:
               emulated prepares are off, so an unknown column fails
               there rather than at execute. */
            require_once BRIX_INCLUDES . '/schema.php';
            brix_upgrade_schema(db());
            db()->prepare($sql)->execute($params);
        }
    } catch (Throwable) {
        return [
            'sent'   => false,
            'errors' => ['We could not save that. Please try again in a moment.'],
            'values' => $values,
        ];
    }

    return ['sent' => true, 'errors' => [], 'values' => brix_lead_blank()];
}
