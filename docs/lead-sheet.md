# Leads → Google Sheets

Every enquiry that reaches `contact_submissions` is copied into a spreadsheet as
it arrives — the contact page and all four campaign landing pages.

Script: [lead-sheet.gs](lead-sheet.gs)
Site side: [`includes/sheets.php`](../includes/sheets.php)

---

## The important idea

**The database is the record. The sheet is a copy.**

The POST to Google happens only after the row is safely in MySQL, and every
failure it can hit is swallowed and written to the error log. A lead form has one
job, and an outage at Google is not a reason to tell somebody their message could
not be sent.

So the failure mode is a **missing row in the sheet, never a lost lead**. If the
two ever disagree, `/admin` is right.

**Not configured is a normal state.** With the two environment variables unset —
a local checkout, or a host that has not been given them — nothing is posted
anywhere and the site behaves exactly as it did before this existed.

---

## Do these in order

### 1. Make the spreadsheet

New Google Sheet, any name. The script creates and formats the `Leads` tab
itself on the first write, so do not make it by hand.

### 2. Generate a secret

Any long random string. One way:

```
openssl rand -hex 32
```

Keep it somewhere for the next two steps. It goes in exactly two places and
must match byte for byte.

### 3. Add the script

**Extensions → Apps Script** from inside the spreadsheet, delete the placeholder
`Code.gs` contents, and paste all of [lead-sheet.gs](lead-sheet.gs).

Store the secret:

1. Edit `setSecret()` at the bottom of the file and put the real secret in.
2. Select `setSecret` in the function dropdown and press **Run**. Approve the
   permissions prompt the first time.
3. **Blank the string again and save.** The value now lives in Script
   Properties, so it does not need to sit in the file.

### 4. Prove the two sides agree before going live

Run `testRoundTrip` from the same dropdown.

It signs a fake lead exactly the way `includes/sheets.php` does and pushes it
through `doPost`. A row for `test@example.com` appearing in the `Leads` tab means
the secret matches and both sides agree on the signature — which is the one thing
worth proving before pointing the live site at it. Delete the test row after.

If it fails, **View → Executions** has the reason.

### 5. Deploy as a web app

**Deploy → New deployment → Web app.**

| Setting | Value |
|---|---|
| Execute as | **Me** |
| Who has access | **Anyone** |

"Anyone" is required — the site posts server to server with no Google login to
offer. That is what the signature is for: without it, anybody who learned the URL
could write rows. The URL is not a credential.

Copy the `/exec` URL.

### 6. Point the site at it

In the site's `.env`:

```
BRIX_SHEETS_WEBHOOK_URL=https://script.google.com/macros/s/AKfy.../exec
BRIX_SHEETS_WEBHOOK_SECRET=the-same-secret-from-step-2
```

Both must be set. With either missing, nothing is posted.

### 7. Check it end to end

Submit the real form at `/contact`. A row should appear within a second or two.

---

## Columns

| Column | Notes |
|---|---|
| Received At | When they first got in touch. Never overwritten on a repeat submission. |
| Name | |
| Email | The key the upsert matches on. |
| Store URL | Normalised to include `https://` by the site. |
| Source | The landing page that captured it, or `Contact page`. |
| Message | For a landing page, the qualifying answers folded into one line. |
| Updated At | Rewritten on every submission. |

Adding a column to `COLUMNS` in the script adds it to the sheet on the next
write. Existing rows keep their values, because lookup is by email rather than by
position.

**IP address and user agent are deliberately not sent.** They are stored in the
database for spam handling, and a spreadsheet that gets shared around is the
wrong place for them.

---

## One row per person

The site stores one row per email — writing in a second time replaces what they
said rather than queueing a duplicate — and the sheet mirrors that. Somebody who
fills the form in twice appears once, with `Updated At` moved on.

This is also what makes a re-send harmless, so a lead missed during an outage can
be pushed through again without creating a double.

---

## When a row is missing

Check the site's PHP error log. Every failure is written there with the address
it belonged to:

```
brix sheet: lead not mirrored (status 0, Connection refused) for someone@example.com
```

Then look it up in `/admin` — the lead itself is not lost. There is **no
automatic retry**: a failed POST is not queued or attempted again. If the sheet
matters more than that, say so and it is worth adding a `synced_at` column and a
catch-up run.

---

## Timing

The visitor waits on this. `BRIX_SHEET_TIMEOUT` in `includes/sheets.php` caps
that at five seconds, with three to connect. An Apps Script web app that has gone
cold can take a second or two to answer; past about five, the right answer is to
stop waiting and let `/admin` be the record.
