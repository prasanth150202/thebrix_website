<?php
/**
 * One-time installer.
 *
 * Asks for the database credentials and the admin password, creates
 * the tables, writes includes/config.php and then locks itself.
 *
 * It is reachable without a login, which is unavoidable for a first
 * run. Two things keep that safe:
 *
 *   1. it cannot do anything without working database credentials,
 *      which an attacker does not have, and
 *   2. the moment an admin account exists it refuses to run at all,
 *      so it cannot be replayed to plant a second account.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once BRIX_INCLUDES . '/schema.php';

$lockFile   = __DIR__ . '/setup.lock';
$configFile = BRIX_INCLUDES . '/config.php';

/** Refuse to run once the site is live. */
function setup_already_done(string $lockFile): bool
{
    if (file_exists($lockFile)) {
        return true;
    }

    if (!brix_is_configured()) {
        return false;
    }

    try {
        return (int) db()->query('SELECT COUNT(*) FROM admin_users')->fetchColumn() > 0;
    } catch (Throwable) {
        return false;
    }
}

$done    = setup_already_done($lockFile);
$errors  = [];
$manual  = null;
$success = false;

if (!$done && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = trim($_POST['db_host'] ?? 'localhost');
    $dbPort = (int) ($_POST['db_port'] ?? 3306);
    $dbName = trim($_POST['db_name'] ?? '');
    $dbUser = trim($_POST['db_user'] ?? '');
    $dbPass = (string) ($_POST['db_pass'] ?? '');

    $adminUser  = trim($_POST['admin_user'] ?? '');
    $adminName  = trim($_POST['admin_name'] ?? 'Admin');
    $adminPass  = (string) ($_POST['admin_pass'] ?? '');
    $adminPass2 = (string) ($_POST['admin_pass2'] ?? '');

    if ($dbName === '')  { $errors[] = 'Database name is required.'; }
    if ($dbUser === '')  { $errors[] = 'Database user is required.'; }
    if ($adminUser === '') { $errors[] = 'Admin username is required.'; }
    if (strlen($adminPass) < 10) {
        $errors[] = 'Admin password must be at least 10 characters.';
    }
    if ($adminPass !== $adminPass2) {
        $errors[] = 'The two admin passwords do not match.';
    }

    $pdo = null;
    if (!$errors) {
        try {
            $pdo = new PDO(
                sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $dbHost, $dbPort, $dbName),
                $dbUser,
                $dbPass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false]
            );
        } catch (PDOException $ex) {
            $errors[] = 'Could not connect: ' . $ex->getMessage();
        }
    }

    if (!$errors && $pdo instanceof PDO) {
        try {
            brix_install_schema($pdo);

            $exists = (int) $pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
            if ($exists > 0) {
                $errors[] = 'An admin account already exists in this database. Setup will not run again.';
            } else {
                $ins = $pdo->prepare(
                    'INSERT INTO admin_users (username, password_hash, display_name)
                     VALUES (:u, :h, :d)'
                );
                $ins->execute([
                    ':u' => $adminUser,
                    ':h' => password_hash($adminPass, PASSWORD_DEFAULT),
                    ':d' => $adminName !== '' ? $adminName : 'Admin',
                ]);
            }
        } catch (PDOException $ex) {
            $errors[] = 'Could not create the tables: ' . $ex->getMessage();
        }
    }

    if (!$errors) {
        $contents = "<?php\n"
            . "// Written by admin/setup.php. Not in version control.\n"
            . "return " . var_export([
                'db_host' => $dbHost,
                'db_port' => $dbPort,
                'db_name' => $dbName,
                'db_user' => $dbUser,
                'db_pass' => $dbPass,
            ], true) . ";\n";

        if (@file_put_contents($configFile, $contents, LOCK_EX) !== false) {
            @chmod($configFile, 0640);
            @file_put_contents($lockFile, date('c') . "\n");
            $success = true;
        } else {
            // Hosting sometimes has includes/ read-only. Hand the file
            // over rather than dead-ending.
            $manual = $contents;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Brix setup</title>
<style>
  :root { --ink:#0B1524; --ink-2:#4A5A6E; --line:#E3E9F0; --brand:#0E9BE5; --bad:#D64545; --good:#12A150; }
  * { box-sizing: border-box; }
  body { margin:0; font:15px/1.6 -apple-system,BlinkMacSystemFont,"Segoe UI",Inter,sans-serif;
         color:var(--ink); background:#F5F8FB; padding:40px 20px; }
  .wrap { max-width:620px; margin:0 auto; background:#fff; border:1px solid var(--line);
          border-radius:14px; padding:34px; }
  h1 { font-size:1.5rem; margin:0 0 6px; }
  .sub { color:var(--ink-2); margin:0 0 26px; }
  fieldset { border:1px solid var(--line); border-radius:10px; padding:18px; margin:0 0 20px; }
  legend { padding:0 8px; font-weight:600; font-size:.85rem; text-transform:uppercase;
           letter-spacing:.06em; color:var(--ink-2); }
  label { display:block; margin:0 0 14px; font-weight:600; font-size:.88rem; }
  input { width:100%; margin-top:6px; padding:10px 12px; border:1px solid var(--line);
          border-radius:8px; font:inherit; font-weight:400; }
  input:focus { outline:2px solid var(--brand); outline-offset:1px; border-color:var(--brand); }
  .row { display:flex; gap:14px; } .row > * { flex:1; }
  button { background:var(--brand); color:#fff; border:0; border-radius:9px; padding:12px 22px;
           font:inherit; font-weight:700; cursor:pointer; }
  .msg { padding:13px 16px; border-radius:9px; margin:0 0 20px; font-size:.92rem; }
  .err { background:#FDECEC; color:#8A2020; border:1px solid #F5C6C6; }
  .ok  { background:#E9F8EF; color:#0A5D30; border:1px solid #BBE6CC; }
  ul { margin:6px 0 0; padding-left:20px; }
  pre { background:#0B1524; color:#D6E4F0; padding:16px; border-radius:9px; overflow:auto;
        font-size:.8rem; line-height:1.5; }
  code { background:#EEF3F8; padding:2px 6px; border-radius:4px; font-size:.88em; }
  .hint { font-weight:400; color:var(--ink-2); font-size:.82rem; display:block; margin-top:3px; }
</style>
</head>
<body>
<div class="wrap">

<?php if ($done): ?>
  <h1>Setup is already complete</h1>
  <p class="sub">This installer has been locked and will not run again.</p>
  <p>Go to <a href="index.php">the admin login</a>.</p>
  <p class="sub" style="margin-top:24px">If you genuinely need to reinstall, delete
     <code>admin/setup.lock</code> and <code>includes/config.php</code> on the server first.</p>

<?php elseif ($success): ?>
  <div class="msg ok"><strong>Done.</strong> The database is ready and your admin account exists.</div>
  <h1>Two things left</h1>
  <ol>
    <li style="margin-bottom:10px">Import the eight existing articles by running
        <a href="migrate.php">the migration</a>. It reads the markdown in
        <code>content/migrated/</code> and does nothing if they are already imported.</li>
    <li>Delete <code>admin/setup.php</code> from the server. It is locked, but there is no
        reason to leave an installer in a public directory.</li>
  </ol>
  <p style="margin-top:26px"><a href="index.php">Continue to the admin login</a></p>

<?php else: ?>
  <h1>Brix setup</h1>
  <p class="sub">Runs once. Creates the tables, your admin account, and the credentials file.</p>

  <?php if ($errors): ?>
    <div class="msg err">
      <strong>Could not finish:</strong>
      <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <?php if ($manual !== null): ?>
    <div class="msg err">
      <strong>The tables and your account were created,</strong> but
      <code>includes/config.php</code> could not be written (the directory is read-only).
      Create that file by hand with exactly this content, then reload:
    </div>
    <pre><?= e($manual) ?></pre>
  <?php endif; ?>

  <form method="post" autocomplete="off">
    <fieldset>
      <legend>Database</legend>
      <div class="row">
        <label>Host
          <input name="db_host" value="<?= e($_POST['db_host'] ?? 'localhost') ?>" required>
        </label>
        <label style="max-width:130px">Port
          <input name="db_port" type="number" value="<?= e((string) ($_POST['db_port'] ?? '3306')) ?>" required>
        </label>
      </div>
      <label>Database name
        <input name="db_name" value="<?= e($_POST['db_name'] ?? '') ?>" required>
        <span class="hint">From hPanel &rarr; Databases &rarr; MySQL Databases.</span>
      </label>
      <label>Database user
        <input name="db_user" value="<?= e($_POST['db_user'] ?? '') ?>" required>
      </label>
      <label>Database password
        <input name="db_pass" type="password" value="">
      </label>
    </fieldset>

    <fieldset>
      <legend>Your admin account</legend>
      <label>Username
        <input name="admin_user" value="<?= e($_POST['admin_user'] ?? '') ?>" required>
      </label>
      <label>Display name
        <input name="admin_name" value="<?= e($_POST['admin_name'] ?? 'Admin') ?>">
        <span class="hint">Only shown inside the panel. The author on each post is a separate field.</span>
      </label>
      <label>Password
        <input name="admin_pass" type="password" required minlength="10">
        <span class="hint">At least 10 characters.</span>
      </label>
      <label>Repeat password
        <input name="admin_pass2" type="password" required minlength="10">
      </label>
    </fieldset>

    <button type="submit">Install</button>
  </form>
<?php endif; ?>

</div>
</body>
</html>
