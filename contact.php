<?php
/**
 * Contact page.
 *
 * Submissions are stored in MySQL and read in the admin panel; no
 * mail is sent, so there is no SMTP dependency to go wrong quietly.
 *
 * Spam handling is deliberately low-friction (no CAPTCHA): a hidden
 * honeypot field, a minimum time-on-page, and a per-IP rate limit
 * together stop the drive-by bots that make up nearly all of it.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once BRIX_INCLUDES . '/posts.php';

/**
 * Start the session before a single byte of HTML goes out.
 *
 * The CSRF token is rendered inside the form, which is well past the
 * point where a Set-Cookie header can still be sent. Without this the
 * token would be written to a session the browser never receives, and
 * every submission would fail validation on the next request.
 */
brix_session_start();

$page_title       = 'Contact Brix: talk to us about your Shopify store';
$page_description = 'Questions about Brix, your setup, pricing or a custom requirement? Send us a message and we will get back to you within one business day.';
$page_canonical   = 'contact';
$page_nav         = null;
$footer_col3      = 'case-studies';

$errors = [];
$sent   = false;

$values = ['name' => '', 'email' => '', 'store_url' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $k => $_) {
        $values[$k] = trim((string) ($_POST[$k] ?? ''));
    }

    if (!csrf_check($_POST['csrf'] ?? null)) {
        $errors[] = 'Your session expired. Please send that again.';
    }

    // Honeypot: a real person never sees this field, so anything in
    // it is a bot. Accept the submission silently and bin it.
    $trapped = trim((string) ($_POST['website'] ?? '')) !== '';

    // Anything submitted within two seconds of the page loading was
    // not typed by a human.
    $startedAt = (int) ($_POST['t'] ?? 0);
    $tooFast   = $startedAt > 0 && (time() - $startedAt) < 2;

    if ($values['name'] === '') {
        $errors[] = 'Please tell us your name.';
    }
    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'That email address does not look right.';
    }
    if (mb_strlen($values['message']) < 10) {
        $errors[] = 'Please add a little more detail to your message.';
    }
    if (mb_strlen($values['message']) > 5000) {
        $errors[] = 'That message is too long. Please keep it under 5,000 characters.';
    }
    if ($values['store_url'] !== '' && !preg_match('#^(https?://)?[\w.-]+\.[a-z]{2,}#i', $values['store_url'])) {
        $errors[] = 'That store URL does not look right. Leave it blank if you would rather not say.';
    }

    // Rate limit: five messages an hour from one address.
    if (!$errors && !$trapped && !$tooFast) {
        try {
            $recent = db()->prepare(
                'SELECT COUNT(*) FROM contact_submissions
                 WHERE ip = :ip AND created_at > (NOW() - INTERVAL 1 HOUR)'
            );
            $recent->execute([':ip' => client_ip()]);

            if ((int) $recent->fetchColumn() >= 5) {
                $errors[] = 'You have sent several messages already. Please give us a little time to reply.';
            }
        } catch (Throwable) {
            $errors[] = 'Something went wrong at our end. Please email us directly.';
        }
    }

    if (!$errors) {
        if ($trapped || $tooFast) {
            // Look successful to the bot without storing anything.
            $sent = true;
        } else {
            try {
                $url = $values['store_url'];
                if ($url !== '' && !preg_match('#^https?://#i', $url)) {
                    $url = 'https://' . $url;
                }

                /* One row per person. Writing in a second time replaces
                   what they said rather than queueing a duplicate, and
                   clears read_at so the updated enquiry comes back as
                   unread. created_at is left alone: it is when they
                   first got in touch. */
                $sql = 'INSERT INTO contact_submissions (name, email, store_url, message, ip, user_agent)
                        VALUES (:n, :e, :u, :m, :ip, :ua)
                        ON DUPLICATE KEY UPDATE
                           name       = :n2,
                           store_url  = :u2,
                           message    = :m2,
                           ip         = :ip2,
                           user_agent = :ua2,
                           updated_at = NOW(),
                           read_at    = NULL';

                $name      = mb_substr($values['name'], 0, 120);
                $storeUrl  = mb_substr($url, 0, 255);
                $message   = $values['message'];
                $ip        = client_ip();
                $userAgent = mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);

                $params = [
                    ':n'   => $name,
                    ':e'   => mb_substr($values['email'], 0, 190),
                    ':u'   => $storeUrl,
                    ':m'   => $message,
                    ':ip'  => $ip,
                    ':ua'  => $userAgent,
                    ':n2'  => $name,
                    ':u2'  => $storeUrl,
                    ':m2'  => $message,
                    ':ip2' => $ip,
                    ':ua2' => $userAgent,
                ];

                try {
                    db()->prepare($sql)->execute($params);
                } catch (PDOException) {
                    /* The columns this needs are added by the upgrade
                       that runs when an admin next signs in. Between a
                       deploy and that moment the enquiry would simply be
                       lost, which is the one thing a lead form must not
                       do, so bring the schema forward and try once more.
                       The prepare has to be inside the retry: emulated
                       prepares are off, so an unknown column fails there
                       rather than at execute. */
                    require_once BRIX_INCLUDES . '/schema.php';
                    brix_upgrade_schema(db());
                    db()->prepare($sql)->execute($params);
                }

                /* Mirrored to the Sheet only now the row is safely in the
                   database, and outside nothing that could fail the
                   submission: brix_lead_to_sheet() swallows its own
                   errors, so a bad day at Google cannot turn into "we
                   could not save your message". */
                require_once BRIX_INCLUDES . '/sheets.php';
                brix_lead_to_sheet([
                    'name'      => $name,
                    'email'     => $values['email'],
                    'store_url' => $storeUrl,
                    'message'   => $message,
                    'source'    => '',
                ]);

                $sent   = true;
                $values = ['name' => '', 'email' => '', 'store_url' => '', 'message' => ''];
            } catch (Throwable) {
                $errors[] = 'We could not save your message. Please try again in a moment.';
            }
        }
    }
}

require BRIX_INCLUDES . '/header.php';
?>

<section class="page-hero">
  <div class="hero-glow" aria-hidden="true"></div>
  <div class="container">
    <p class="eyebrow reveal">Contact</p>
    <h1 class="reveal" style="--d:.06s">Talk to us about <em>your store</em></h1>
    <p class="hero-sub reveal" style="--d:.12s">Setup questions, pricing, theme compatibility or something custom. Tell us what you are trying to do and we will come back to you within one business day.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="contact-wrap">

<?php if ($sent): ?>
      <div class="contact-done reveal">
        <span class="contact-tick" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
               stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        </span>
        <h2>Message received</h2>
        <p>Thanks for getting in touch. We read everything that comes in and normally reply
           within one business day.</p>
        <p class="contact-done-links">
          <a href="how-to">Browse the setup guides</a> &middot;
          <a href="features">See all features</a> &middot;
          <a href="/">Back to home</a>
        </p>
      </div>
<?php else: ?>

  <?php if ($errors): ?>
      <div class="contact-errors reveal">
        <p><strong>Please check the following:</strong></p>
        <ul>
          <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
        </ul>
      </div>
  <?php endif; ?>

      <form class="contact-form reveal" method="post" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="t" value="<?= time() ?>">

        <!-- Left deliberately empty; only a bot fills this in. -->
        <div class="contact-trap" aria-hidden="true">
          <label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
        </div>

        <div class="contact-row">
          <label class="contact-field">
            <span>Your name</span>
            <input type="text" name="name" required maxlength="120"
                   value="<?= e($values['name']) ?>" placeholder="John Doe">
          </label>
          <label class="contact-field">
            <span>Email</span>
            <input type="email" name="email" required maxlength="190"
                   value="<?= e($values['email']) ?>" placeholder="you@store.com">
          </label>
        </div>

        <label class="contact-field">
          <span>Store URL <em>(optional)</em></span>
          <input type="text" name="store_url" maxlength="255"
                 value="<?= e($values['store_url']) ?>" placeholder="yourstore.myshopify.com">
        </label>

        <label class="contact-field">
          <span>How can we help?</span>
          <textarea name="message" rows="7" required maxlength="5000"
                    placeholder="Tell us about your store and what you are trying to improve."><?= e($values['message']) ?></textarea>
        </label>

        <div class="contact-actions">
          <button class="btn btn-primary btn-lg" type="submit">Send message</button>
          <p class="contact-fine">We reply within one business day. Your details are never shared.</p>
        </div>
      </form>

      <aside class="contact-aside reveal" style="--d:.1s">
        <div class="contact-card">
          <p class="contact-card-h">Already using Brix?</p>
          <p>The in-app chat inside your Shopify admin is the fastest route for anything
             account-specific, since we can see your setup.</p>
        </div>
        <div class="contact-card">
          <p class="contact-card-h">Looking for answers now?</p>
          <p><a href="how-to">Setup guides</a> cover Frequently Bought Together, bundles,
             the cart drawer and coupon banners step by step.
             <a href="pricing">Pricing</a> lists what each plan includes.</p>
        </div>
        <div class="contact-card">
          <p class="contact-card-h">Not installed yet?</p>
          <p>The free plan needs no card and takes a couple of minutes to set up.</p>
          <a class="btn btn-primary btn-sm" href="<?= e(SHOPIFY_APP_URL) ?>"
             target="_blank" rel="noopener">Install free</a>
        </div>
      </aside>
<?php endif; ?>

    </div>
  </div>
</section>

<?php require BRIX_INCLUDES . '/footer.php'; ?>
