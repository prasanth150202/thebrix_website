<?php
/**
 * Mirror every new lead into a Google Sheet.
 *
 * The database stays the source of truth. This is a copy, for the people
 * who live in a spreadsheet rather than in /admin, and it is written that
 * way on purpose: the POST happens only after the row is safely in MySQL,
 * and nothing it does can fail the submission the visitor is waiting on.
 *
 * That is the whole design rule here. A lead form has exactly one job, and
 * an outage at Google is not a reason to tell somebody their message could
 * not be sent. Every failure below is swallowed and logged.
 *
 * Not configured is a normal state, not an error. With the two environment
 * variables unset - a local checkout, or a host that has not been given
 * them - brix_lead_to_sheet() returns immediately and the site behaves
 * exactly as it did before this file existed.
 *
 * Receiving end: docs/lead-sheet.gs, set up per docs/lead-sheet.md.
 */

declare(strict_types=1);

/**
 * How long to wait on Google before giving up, in seconds.
 *
 * The visitor is holding a submitted form for this long in the worst case,
 * so it is short. An Apps Script web app that has gone cold can take a
 * second or two to answer, and past about five the right answer is to stop
 * waiting and let the admin panel be the record.
 */
const BRIX_SHEET_TIMEOUT = 5;

/**
 * Post one lead to the Sheet. Never throws, never blocks for long.
 *
 * $lead expects: name, email, store_url, message, source. `source` is the
 * landing page that captured it, or '' for the contact page.
 */
function brix_lead_to_sheet(array $lead): void
{
    $url    = (string) (getenv('BRIX_SHEETS_WEBHOOK_URL') ?: '');
    $secret = (string) (getenv('BRIX_SHEETS_WEBHOOK_SECRET') ?: '');

    /* Unconfigured, or no cURL on the host. Both mean "there is no Sheet
       to write to", which is not a failure worth logging on every submit. */
    if ($url === '' || $secret === '' || !function_exists('curl_init')) {
        return;
    }

    $body = json_encode([
        'received_at' => gmdate('c'),
        'name'        => (string) ($lead['name'] ?? ''),
        'email'       => (string) ($lead['email'] ?? ''),
        'store_url'   => (string) ($lead['store_url'] ?? ''),
        'source'      => (string) ($lead['source'] ?? ''),
        'message'     => (string) ($lead['message'] ?? ''),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    if ($body === false) {
        error_log('brix sheet: could not encode lead');
        return;
    }

    /* The secret signs the body rather than travelling in it. An Apps
       Script web app has to be published to "Anyone" to accept a POST from
       a server at all, so the URL alone is not a credential: without this
       anybody who learned the address could write rows. */
    $signature = hash_hmac('sha256', $body, $secret);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => BRIX_SHEET_TIMEOUT,
        CURLOPT_CONNECTTIMEOUT => 3,
        /* Apps Script answers a web app with a 302 to script.googleusercontent.com
           and serves the real response from there. Without this every POST
           looks like a redirect and never reaches doPost(). */
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'X-Brix-Signature: ' . $signature,
        ],
    ]);

    $response = curl_exec($ch);
    $status   = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $error    = curl_error($ch);
    curl_close($ch);

    /* Logged, not raised. The lead is already in the database; the only
       thing lost is the copy, and the log is what makes that recoverable
       later rather than invisible. */
    if ($response === false || $status !== 200) {
        error_log(sprintf(
            'brix sheet: lead not mirrored (status %d%s) for %s',
            $status,
            $error !== '' ? ', ' . $error : '',
            (string) ($lead['email'] ?? 'unknown')
        ));
    }
}
