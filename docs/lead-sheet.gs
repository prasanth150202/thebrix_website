/**
 * BRIX — leads mirrored into a Google Sheet
 *
 * Receives one lead per POST from includes/sheets.php and keeps a tab in
 * step with the contact_submissions table.
 *
 * Upsert by email, not append. The site stores one row per person -
 * writing in a second time replaces what they said rather than queueing a
 * duplicate - and this mirrors that, so somebody who fills the form in
 * twice appears once here too. It also makes a re-send harmless, which is
 * what lets a lead missed during an outage be pushed through again later.
 *
 * Setup: see docs/lead-sheet.md
 */

// ---------------------------------------------------------------------------
// Configuration
// ---------------------------------------------------------------------------

// Must match BRIX_SHEETS_WEBHOOK_SECRET in the site's .env, byte for byte.
// Stored in Script Properties rather than written here, so the secret is not
// sitting in a file that gets copied around. See setSecret() at the bottom.
const SECRET_PROPERTY = 'BRIX_SHEETS_WEBHOOK_SECRET';

const TAB_NAME = 'Leads';

// The order of these is the order of the columns. Adding one here adds it to
// the sheet on the next run; the header is rewritten to match, and existing
// rows keep their values because lookup is by email, not by position.
const COLUMNS = [
  'Received At',
  'Name',
  'Email',
  'Store URL',
  'Source',
  'Message',
  'Updated At',
];

// Column the upsert matches on. 1-based, and must point at Email above.
const EMAIL_COL = 3;

// ---------------------------------------------------------------------------
// Web app entry point
// ---------------------------------------------------------------------------

/**
 * POST from the site. Returns 200 with {ok:true} on success.
 *
 * Anything that is not a valid, correctly signed lead gets a 200 with
 * {ok:false} and a reason rather than an exception: Apps Script renders an
 * uncaught error as an HTML page, which tells an attacker more than it tells
 * us, and the site logs the body either way.
 */
function doPost(e) {
  try {
    if (!e || !e.postData || !e.postData.contents) {
      return reply_(false, 'empty body');
    }

    const raw = e.postData.contents;

    /* Three different problems used to look identical from the outside.
       They are named separately because the fix for each is different:
       set the Script Property, redeploy the site, or correct the secret. */
    const secret = PropertiesService.getScriptProperties().getProperty(SECRET_PROPERTY);
    if (!secret) {
      return reply_(false, 'secret not set — run setSecret in the Apps Script editor');
    }

    const provided = headerSignature_(e);
    if (!provided) {
      return reply_(false, 'no signature received — the site is sending it as a header, which Apps Script cannot see');
    }

    if (!signatureValid_(raw, provided, secret)) {
      return reply_(false, 'bad signature — the secret here and the one in the site .env differ');
    }

    const lead = JSON.parse(raw);
    if (!lead.email) {
      return reply_(false, 'no email');
    }

    upsertLead_(lead);
    return reply_(true, 'ok');
  } catch (err) {
    // Logged to the Apps Script execution log, where the setup doc says to look.
    console.error('lead sheet: ' + (err && err.stack ? err.stack : err));
    return reply_(false, 'error');
  }
}

/**
 * A web app cannot set a status code, so success is carried in the body and
 * the site treats any non-200 or transport failure as "not mirrored".
 */
function reply_(ok, message) {
  return ContentService
    .createTextOutput(JSON.stringify({ ok: ok, message: message }))
    .setMimeType(ContentService.MimeType.JSON);
}

// ---------------------------------------------------------------------------
// Signature
// ---------------------------------------------------------------------------

/**
 * The signature off the query string.
 *
 * A deployed web app is handed parameter, postData and queryString and
 * nothing else - request headers are not exposed to Apps Script at all -
 * so the query string is the only place this can arrive from the site.
 * The header fallback below only ever fires for a hand-built event
 * object, which is what testRoundTrip passes in.
 */
function headerSignature_(e) {
  const q = (e && e.parameter && e.parameter.signature) ? e.parameter.signature : null;
  if (q) return q;

  const headers = (e && e.headers) ? e.headers : {};
  return headers['X-Brix-Signature'] || headers['x-brix-signature'] || '';
}

/**
 * HMAC-SHA256 over the exact bytes the site posted, compared in constant
 * time. The secret itself never travels.
 */
function signatureValid_(raw, provided, secret) {
  if (!secret || !provided) return false;

  const bytes = Utilities.computeHmacSha256Signature(raw, secret);
  const expected = bytes
    .map(function (b) { return ((b < 0 ? b + 256 : b) + 0x100).toString(16).slice(1); })
    .join('');

  return timingSafeEqual_(expected, String(provided).toLowerCase());
}

/** Compares every character regardless of where the first difference is. */
function timingSafeEqual_(a, b) {
  if (a.length !== b.length) return false;
  let diff = 0;
  for (let i = 0; i < a.length; i++) {
    diff |= a.charCodeAt(i) ^ b.charCodeAt(i);
  }
  return diff === 0;
}

// ---------------------------------------------------------------------------
// The sheet
// ---------------------------------------------------------------------------

/** The Leads tab, created with its header row if this is the first write. */
function sheet_() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  let sh = ss.getSheetByName(TAB_NAME);

  if (!sh) {
    sh = ss.insertSheet(TAB_NAME);
  }

  // Rewritten every time so a column added to COLUMNS appears without anyone
  // having to edit the sheet by hand.
  const header = sh.getRange(1, 1, 1, COLUMNS.length);
  if (String(header.getValues()[0].join('\t')) !== COLUMNS.join('\t')) {
    header.setValues([COLUMNS]);
    header.setFontWeight('bold');
    sh.setFrozenRows(1);
  }

  return sh;
}

/**
 * Update the row for this email, or append one.
 *
 * Runs under a script lock: two people submitting within the same second
 * would otherwise both read "no existing row" and append, leaving a
 * duplicate the site's own table does not have.
 */
function upsertLead_(lead) {
  const lock = LockService.getScriptLock();
  lock.waitLock(20000);

  try {
    const sh = sheet_();
    const now = new Date();

    const row = [
      lead.received_at ? new Date(lead.received_at) : now,
      lead.name || '',
      lead.email || '',
      lead.store_url || '',
      // Blank source means the contact page, which is worth naming rather
      // than leaving as an empty cell nobody can interpret.
      lead.source || 'Contact page',
      lead.message || '',
      now,
    ];

    const existing = findRowByEmail_(sh, lead.email);

    if (existing > 0) {
      // Received At is left as it was: that is when they first got in touch,
      // which is exactly how the database treats created_at.
      sh.getRange(existing, 2, 1, COLUMNS.length - 1)
        .setValues([row.slice(1)]);
    } else {
      sh.appendRow(row);
    }
  } finally {
    lock.releaseLock();
  }
}

/** 1-based row number for an email, or -1. */
function findRowByEmail_(sh, email) {
  const last = sh.getLastRow();
  if (last < 2) return -1;

  const wanted = String(email).trim().toLowerCase();
  const values = sh.getRange(2, EMAIL_COL, last - 1, 1).getValues();

  for (let i = 0; i < values.length; i++) {
    if (String(values[i][0]).trim().toLowerCase() === wanted) {
      return i + 2;
    }
  }
  return -1;
}

// ---------------------------------------------------------------------------
// Setup and diagnostics — run these by hand from the editor
//
// These two deliberately do NOT end in an underscore. Apps Script treats a
// trailing underscore as private and hides those functions from the Run
// dropdown, which is right for the helpers above and wrong for anything the
// setup asks you to run yourself. Do not add one here.
// ---------------------------------------------------------------------------

/**
 * Store the shared secret. Edit the string, run once, then blank it again
 * and save so the value is not left sitting in the file.
 */
function setSecret() {
  const secret = 'PASTE_THE_SAME_SECRET_AS_THE_SITE_ENV';

  if (secret.indexOf('PASTE_') === 0) {
    throw new Error('Edit setSecret and put the real secret in first.');
  }
  PropertiesService.getScriptProperties().setProperty(SECRET_PROPERTY, secret);
  console.log('Secret stored. Now blank the string in setSecret and save.');
}

/**
 * End-to-end check without involving the website.
 *
 * Signs a fake lead exactly the way includes/sheets.php does and pushes it
 * through doPost. If a row for test@example.com appears in the Leads tab,
 * the secret matches and the signature scheme agrees on both sides - which
 * is the one thing worth proving before pointing the live site at this.
 */
function testRoundTrip() {
  const secret = PropertiesService.getScriptProperties().getProperty(SECRET_PROPERTY);
  if (!secret) throw new Error('Run setSecret first.');

  const body = JSON.stringify({
    received_at: new Date().toISOString(),
    name: 'Test Merchant',
    email: 'test@example.com',
    store_url: 'example.myshopify.com',
    source: 'self-test',
    message: 'Written by testRoundTrip, safe to delete.',
  });

  const bytes = Utilities.computeHmacSha256Signature(body, secret);
  const signature = bytes
    .map(function (b) { return ((b < 0 ? b + 256 : b) + 0x100).toString(16).slice(1); })
    .join('');

  /* parameter, not headers. A deployed web app is never given request
     headers, so a test that passed the signature as one would prove the
     script works in a way the live site cannot reach it - which is
     exactly how the header-only version of this shipped and failed. */
  const result = doPost({
    postData: { contents: body },
    parameter: { signature: signature },
  });

  console.log(result.getContent());
}
