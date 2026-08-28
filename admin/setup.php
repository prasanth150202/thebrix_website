<?php
/**
 * The installer, retired.
 *
 * setup.php created the tables and the first admin account, and has done
 * both. Deleting it from the repository did not remove it from the
 * server: this deploy replaces files but never deletes them, so the real
 * installer would have sat there indefinitely. Overwriting it with this
 * achieves what deleting it was meant to.
 *
 * The original is a checkout away if a second environment ever needs
 * installing:  git checkout a072781 -- admin/setup.php
 *
 * It cannot simply redirect: login.php, index.php and _boot.php all send
 * people here when the site reads as unconfigured, and bouncing them
 * straight back would spin. So a working site goes to the panel, and a
 * broken one gets told what is broken.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

if (brix_is_configured()) {
    redirect('index.php');
}

http_response_code(503);
header('Content-Type: text/html; charset=UTF-8');
header('X-Robots-Tag: noindex, nofollow');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Brix is not configured</title>
<style>
  body { margin:0; font:15px/1.6 -apple-system,BlinkMacSystemFont,"Segoe UI",Inter,sans-serif;
         color:#120A24; background:#F8F6FF; padding:40px 20px; }
  .wrap { max-width:560px; margin:0 auto; background:#FFFFFF; border:1px solid #E7E1F5;
          border-radius:14px; padding:34px; }
  h1 { font-size:1.35rem; margin:0 0 8px; }
  p { color:#5F5570; }
  code { background:#F8F6FF; border:1px solid #E7E1F5; border-radius:5px; padding:1px 5px;
         font:13px ui-monospace,Menlo,monospace; }
</style>
</head>
<body>
  <div class="wrap">
    <h1>Brix cannot reach its database</h1>
    <p>
      The site is installed, but this copy of it has no working database
      credentials, so there is nothing for the admin panel to open.
    </p>
    <p>
      Credentials come from <code>includes/config.php</code>, or from a
      <code>.env</code> in the site root holding <code>BRIX_DB_NAME</code>,
      <code>BRIX_DB_USER</code>, <code>BRIX_DB_PASS</code> and
      <code>BRIX_DB_HOST</code>. Check that one of the two is present and
      that the database is running.
    </p>
  </div>
</body>
</html>
