# GA4 archive — what to do, in order

Two separate problems were found on 2026-08-27. The code changes for both are
already made in [ga4-sheets-export.gs](ga4-sheets-export.gs). The steps below are
the part that has to happen in Google, which I cannot do for you.

Work through them in order. Steps 1–4 are the fix; step 5 is verification; step 6
is optional.

---

## What was wrong

**Problem 1 — every campaign number was inflated, roughly 2×.**
GA4 back-fills attribution for a day or two after the fact, so a click first
arrives with `sessionSource` = `(not set)` and resolves to the real source later.
The old merge matched rows on their dimension values, so once the value changed
the row no longer matched anything and got inserted a second time — while the
stale `(not set)` row was kept for ever. Store clicks read 30 when the real
number was 21; landings read 498 when the real number was 289.

**Problem 2 — the lead journey could not see the store.**
`Reached Listing` and `Clicked Add App` both read 0. The visits are genuinely in
GA4 — One_Month_Free_Campaign has two store sessions carrying a real token — but
the script only ever learned a lead's token by parsing it out of a Smartlead
`Link Clicked` URL. That token never appeared in the Smartlead log, so no lead
record carried it and the visit had nothing to join to. **125 of your 150 leads
have no token at all** for the same reason.

---

## 1. Paste the updated script

1. Open the spreadsheet → **Extensions → Apps Script**.
2. Select everything in `Code.gs` and delete it.
3. Paste the whole of [ga4-sheets-export.gs](ga4-sheets-export.gs).
4. **Save** (disk icon, or Cmd-S).

Nothing runs yet. If the editor shows a red error on save, the paste was
truncated — select all, delete, and paste again.

---

## 2. Set `LEAD_TOKEN_KEY`

This is the step that fixes the 0s.

1. In the Apps Script editor, left sidebar → **Project Settings** (gear icon).
2. Scroll to **Script Properties** → **Add script property**.
3. Property: `LEAD_TOKEN_KEY`
4. Value: the **hex key** you use to build the `utm_term` tokens on your campaign
   links.
5. **Save script properties**.

### Which key is it

**Already identified and verified** — it is the hex key you supplied on
2026-08-27. Checked against all 40 tokens that appear in logged Smartlead click
URLs: **40 agree, 0 disagree.** Paste that value; do not go looking for another.

It is deliberately not written into this file, because this file is in the repo
and the key is a secret. Take it from your own records.

For reference, the token on every link is:

```
HMAC-SHA256(key, lowercase(trimmed email))   hex encoded
```

Script properties are not stored in the code and are never committed, which is
why the key goes there rather than in a file.

Do not substitute a different key. A wrong one produces perfectly well-formed
tokens that silently match nothing, which looks identical to the bug you are
fixing. Step 3 re-checks this for you on every run.

---

## 3. Run it once and check the key

1. In the Apps Script editor, pick **`refreshArchive`** from the function
   dropdown and press **Run**.
2. First run after a paste may re-ask for authorisation — approve it
   (**Advanced → Go to (project) (unsafe) → Allow**, expected for a personal
   script).
3. Go back to the spreadsheet and open the **`_Status`** tab.

Under **LEAD JOURNEY** you should see **`Key verified against 40 click URL(s).`**

The full set of lines it can print:

| Line | Meaning |
|---|---|
| `Key verified against N click URL(s).` | Correct key. Continue to step 4. |
| `KEY LOOKS WRONG: N of M click URL(s) disagree…` | **Stop.** Wrong key — every computed token is junk. Go back to step 2. |
| `No click URL carried a token, so the key is still unverified.` | Cannot check yet. Continue, but treat the results as unconfirmed. |
| `LEAD_TOKEN_KEY not set: only N of M lead(s) have a token…` | The property did not save. Redo step 2. |

The check works by comparing the key's output against tokens from click URLs that
Smartlead *did* log — those are ground truth, so a mismatch is conclusive.

---

## 4. Repair the duplicated history

The code change stops new duplicates. It does not remove the ones already in the
sheet — that needs one full re-fetch.

**Spreadsheet → GA4 menu → Backfill / repair history.**

It re-pulls up to 400 days and rewrites every date GA4 can still serve. Dates GA4
has already discarded are left exactly as archived, so nothing is lost. Takes a
few minutes.

When it finishes, `_Status` will show a **RETIRED ROWS** section listing how many
stale rows were cleared per tab.

---

## 5. Verify

Open **`_Status`** and confirm:

- `Status` = `OK`
- `RETIRED ROWS` present (the duplicates it cleared)
- Under `LEAD JOURNEY`, the key line from step 3

Then open **`Dashboard`**. The headline numbers should **drop**:

| | Before | Expected after |
|---|---|---|
| Store clicks (28d) | 30 | ~21 |
| Landings (28d) | 413 | ~289 |
| Click rate | 7.3% | ~7.3% (both sides corrected) |

Those are corrections, not losses. The old numbers were counting the same events
twice.

Then open **`Lead Journey`**. Against the current archive, expect exactly this:

| Column | Before | After | Why |
|---|---|---|---|
| `Visited Website` | 17 | 17 | Those 17 already had tokens from click URLs. No change. |
| `Reached Listing` | 0 | **1** | `soho@wearebraindead.com`, Aug 25 and 26, One_Month_Free_Campaign. |
| `Clicked Add App` | 0 | **0** | Correct — see below. |

**`Clicked Add App` staying 0 is not a failure.** All 16 `Add App button` /
`add_to_cart` events in the archive arrived on sessions with no token *and* no
campaign at all, so none of them can be tied to a lead by any means. Nobody
tokenised has clicked Add App yet. The column will populate the first time one
does.

The single `Reached Listing` is thin, but the plumbing is sound: both of the real
store sessions that One_Month_Free_Campaign produced carried their token
correctly. The number is small because the traffic is small, not because
anything is dropping it.

The bigger win is forward-looking. Every one of your 106 lead addresses now has a
token the script can recognise, instead of only the ~25 whose click Smartlead
happened to log, so future store visits match the moment they happen.

---

## 6. Optional — mark the install events as key events

`Store · Traffic` reports **0 key events** across the whole period even though the
listing recorded 14 `Add App button` and 11 `shopify_app_install` events. They are
simply not flagged as key events.

**GA4 → Admin → (App Store property) → Events** → toggle **Mark as key event** on:

- `Add App button`
- `add_to_cart`
- `shopify_app_install`

This does not affect the lead journey, which reads the event names directly. It
only makes the Key Events column meaningful. Not retroactive.

---

## Two things worth knowing

**Logged clicks will always outnumber store sessions.** Generic Campaign Aug1 V1
logged 10 store-bound clicks and produced about 2 store sessions. That ratio is
the normal cold-email signature of mail security gateways prefetching every link.
Those clicks are not people and will never appear in GA4. Do not read the gap as
a tracking failure.

**`Web · Events Daily` and `Store · Events` are the honest totals.** They are
keyed on `date + eventName`, which GA4 never revises, so they were correct
throughout. When a sliced tab disagrees with them, trust these.

---

## Still open, not addressed here

`js/utm.js:206` sends `click_destination` on every store click, but no report
archives it. Adding it would push `Web · Store Clicks` past the Data API's
9-dimension ceiling, so it needs a column traded out or a second report. Say the
word if you want it.
