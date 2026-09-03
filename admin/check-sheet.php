<?php
/**
 * Why is the Google Sheet mirror not working?
 *
 * Behind the admin login, because it reports whether the webhook
 * settings are visible and what Google says back. Signed in already, so
 * there is nothing extra to remember, and nothing here is readable by
 * anyone who is not.
 *
 * Diagnostic only. Nothing on the site links to it, and it can be
 * deleted once the mirror is working. See docs/lead-sheet.md
 */

declare(strict_types=1);

require_once __DIR__ . '/_boot.php';

$user = require_admin();

$url    = (string) (getenv('BRIX_SHEETS_WEBHOOK_URL') ?: '');
$secret = (string) (getenv('BRIX_SHEETS_WEBHOOK_SECRET') ?: '');
$envPath = BRIX_ROOT . '/.env';

$checks  = [];
$verdict = '';

/* ---- 1. can PHP see the settings at all ---- */
$checks[] = ['.env file', is_readable($envPath) ? 'readable at ' . $envPath : 'NOT READABLE at ' . $envPath, is_readable($envPath)];
$checks[] = ['BRIX_SHEETS_WEBHOOK_URL', $url !== '' ? $url : 'empty — PHP cannot see it', $url !== ''];
$checks[] = ['BRIX_SHEETS_WEBHOOK_SECRET', $secret !== ''
    ? 'set · ' . strlen($secret) . ' chars · starts "' . substr($secret, 0, 4) . '" · ends "' . substr($secret, -4) . '"'
    : 'empty — PHP cannot see it', $secret !== ''];

/* ---- 2. is it the right kind of URL ---- */
if ($url !== '') {
    $shapeOk = str_ends_with($url, '/exec');
    $checks[] = ['URL shape', $shapeOk
        ? 'ends /exec — correct'
        : (str_ends_with($url, '/dev')
            ? 'ends /dev — that is the editor-only URL, use the /exec one from Deploy'
            : 'does not end /exec — copy the Web app URL from Deploy'), $shapeOk];
    $checks[] = ['cURL available', function_exists('curl_init') ? 'yes' : 'NO — the site cannot post anywhere', function_exists('curl_init')];
}

/* ---- 3. actually post one ---- */
$status = null; $response = null; $curlErr = '';

if ($url !== '' && $secret !== '' && function_exists('curl_init')) {
    $body = json_encode([
        'received_at' => gmdate('c'),
        'name'        => 'Diagnostic',
        'email'       => 'diagnostic@example.com',
        'store_url'   => '',
        'source'      => 'admin/check-sheet.php',
        'message'     => 'Written by the diagnostic, safe to delete.',
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'X-Brix-Signature: ' . hash_hmac('sha256', $body, $secret),
        ],
    ]);
    $response = curl_exec($ch);
    $status   = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    $body200 = is_string($response) ? $response : '';

    if ($status === 0) {
        $verdict = 'Could not reach Google at all' . ($curlErr !== '' ? ' (' . $curlErr . ')' : '') . '. '
                 . 'Either the URL is wrong, or this host blocks outbound HTTPS — some shared hosts do. '
                 . 'If it is the host, ask them to allow outbound requests to script.google.com.';
    } elseif (str_contains($body200, '"ok":true')) {
        $verdict = 'WORKING. A row for diagnostic@example.com should be in the Leads tab now. '
                 . 'If it is not, the deployment is serving an older version of the script: '
                 . 'Deploy → Manage deployments → pencil → Version: New version.';
    } elseif (str_contains($body200, 'no signature received')) {
        $verdict = 'The script ran but got no signature. That is the old header-only version of '
                 . 'lead-sheet.gs: Apps Script never sees request headers. Repaste the current '
                 . 'docs/lead-sheet.gs, then Deploy → Manage deployments → pencil → Version: New version.';
    } elseif (str_contains($body200, 'secret not set')) {
        $verdict = 'The script ran, but no secret is stored on its side. Open the Apps Script editor, '
                 . 'put the secret into setSecret, and Run it once.';
    } elseif (str_contains($body200, 'bad signature')) {
        $verdict = 'Reached the script and it received the signature, but the two secrets differ. '
                 . 'The one stored by setSecret is not the string in .env — compare it against the '
                 . 'first and last four characters shown above, then redeploy a new version.';
    } elseif (stripos($body200, '<html') !== false || $status === 302 || $status === 401 || $status === 403) {
        $verdict = 'Google served a login or error page instead of running the script. The deployment '
                 . 'is almost certainly not open: Deploy → Manage deployments → pencil → '
                 . 'Who has access: Anyone.';
    } else {
        $verdict = 'Reached something, but not a reply this recognises. Check View → Executions in the '
                 . 'Apps Script editor for the matching run.';
    }
}

admin_head('Sheet mirror check', $user);
?>
<div class="ad-wrap">
  <h1>Google Sheet mirror</h1>
  <p class="ad-sub">Diagnostic. Delete <code>admin/check-sheet.php</code> once this is working.</p>

  <div class="ad-card">
    <table class="ad-table">
      <tbody>
<?php foreach ($checks as [$label, $value, $ok]): ?>
        <tr>
          <td style="width:230px"><b><?= e($label) ?></b></td>
          <td><?= $ok ? '' : '<span class="ad-sub-bad">✕</span> ' ?><code><?= e($value) ?></code></td>
        </tr>
<?php endforeach; ?>
<?php if ($status !== null): ?>
        <tr><td><b>HTTP status</b></td><td><code><?= (int) $status ?></code></td></tr>
        <tr><td><b>Response</b></td><td><code><?= e(mb_substr((string) $response, 0, 400)) ?></code></td></tr>
<?php endif; ?>
      </tbody>
    </table>
  </div>

<?php if ($verdict !== ''): ?>
  <div class="ad-card">
    <h2 style="font-size:1.05rem;margin:0 0 8px">What that means</h2>
    <p><?= e($verdict) ?></p>
  </div>
<?php elseif ($url === '' || $secret === ''): ?>
  <div class="ad-card ad-card-danger">
    <h2 style="font-size:1.05rem;margin:0 0 8px">What that means</h2>
    <p>PHP cannot see one or both settings, so the site posts nothing at all — which is exactly
       the symptom. The <code>.env</code> has to sit at <code><?= e($envPath) ?></code>, and the
       lines need no spaces around the <code>=</code> and no quotes around the value:</p>
    <p><code>BRIX_SHEETS_WEBHOOK_URL=https://script.google.com/macros/s/AKfy.../exec</code></p>
    <p>If the file is there and correct, this host may be ignoring it — some do. In that case set
       the two as real environment variables in the hosting panel instead.</p>
  </div>
<?php endif; ?>
</div>
<?php admin_foot();
