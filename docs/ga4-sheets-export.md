# GA4 → Google Sheets permanent archive

Pulls both BRIX GA4 properties into one spreadsheet every hour and keeps the data
**forever**, including after GA4 itself deletes it.

Script: [ga4-sheets-export.gs](ga4-sheets-export.gs)
GA4 property setup: [ga4-utm-setup.md](ga4-utm-setup.md)

---

## The important idea

This is an **archive, not a mirror**.

Each run fetches only the last few days and merges them into the existing tabs by
matching on the dimension values. A day that has already settled is never
rewritten and never deleted. So when GA4 eventually discards data past its
retention limit, your spreadsheet still has it.

That is the whole reason for the design. An earlier version of this script cleared
each tab and rewrote it from a rolling window — under that model, data disappeared
from the sheet the moment GA4 dropped it.

| | Mirror (old) | Archive (now) |
|---|---|---|
| Each run | clears and rewrites every tab | merges recent days into what is there |
| History | only as far back as GA4 keeps it | unlimited, grows forever |
| Hourly run | pointless, refetches identical data | useful, corrects today as it fills in |

---

## Do these in order

### 1. Set GA4 data retention to 14 months — do this first

**GA4 → Admin → Data Settings → Data Retention → 14 months.** On **both**
properties.

The default is 2 months and the change is **not retroactive**. Whatever has
already aged out cannot be recovered by anything in this document. The backfill in
step 6 can only capture what GA4 still holds at the moment it runs, so every day
this is left on the default is a day permanently lost.

After today, the archive protects you regardless of this setting. Before today, it
is the only thing that decides how much history exists.

### 2. Create the spreadsheet and paste the script

1. New Google Sheet, named e.g. *BRIX — GA4 Archive*.
2. **Extensions → Apps Script**, delete the `myFunction` stub.
3. Paste all of [ga4-sheets-export.gs](ga4-sheets-export.gs) into `Code.gs`. Save.

Use a Google account with at least **Viewer** on both GA4 properties. The script
runs as whoever authorises it.

### 3. Enable the API services

Sidebar → **Services → +**:

- **Google Analytics Data API** — identifier `AnalyticsData`, version `v1beta`. Required.
- **Google Analytics Admin API** — identifier `AnalyticsAdmin`, version `v1beta`. Only needed by the access diagnostic, but add it now.

Missing the first one produces `AnalyticsData is not defined` on every run.

### 4. Authorise

Run `refreshArchive` from the function dropdown. Approve the prompts
(**Advanced → Go to (project) (unsafe) → Allow** — expected for a personal script).

### 5. Check `_Status`

`Status: OK` means every tab wrote. It also lists per-tab row counts and any
fields it skipped. If you see errors, jump to the troubleshooting section.

### 6. Backfill history, once

**GA4 → Backfill history (one-off)** in the spreadsheet menu.

Pulls up to 400 days in one go and merges it in. Safe to re-run — rows are
upserted, never duplicated — but there is no reason to run it twice.

### 7. Install the hourly trigger

**GA4 → Install hourly trigger.**

From here it maintains itself.

### 8. Delete the legacy tabs

**GA4 → Delete legacy tabs.**

If you upgraded from the earlier version of this script, its tabs are still in the
spreadsheet — `Campaign Landings`, `App Store Clicks`, `First Touch`,
`Channel Performance`, `Page Performance`, `Audience` and friends, all without a
`Web ·` or `Store ·` prefix.

**Nothing updates them any more.** They hold a frozen snapshot from the last time
the old script ran, which is worse than having no tab at all — the numbers look
current and are not. The prefixed tabs replace every one of them.

The menu item lists exactly what it will delete and asks before doing anything. It
only ever touches that known set of old names.

---

## What you get

Every tab is prefixed so the two properties can never be confused.

### `Web ·` — Brix Site, property 546417307

| Tab | What it holds |
|---|---|
| `Web · Landings Last Touch` | Landings by source, medium, campaign, campaign ID, content, term, landing page |
| `Web · Landings First Touch` | The same, by the campaign that *originally* found each visitor |
| `Web · Store Clicks` | Install clicks by page, placement, button text, channel, source, medium, campaign |
| `Web · Funnel Source` | Both events by channel/source/medium/campaign — feeds the funnel tabs |
| `Web · Events Daily` | **Every** event the property collects, not just the BRIX two |
| `Web · Channels` | Sessions, engaged sessions, engagement rate, avg duration, users, key events, views |
| `Web · Pages` | Views, sessions, engagement seconds, bounce rate, key events per page |
| `Web · Audience` | Country, region, device, OS, browser, new vs returning |
| `Web · Custom N` | Auto-discovered: every custom dimension registered on the property |

### `Store ·` — App Store, property 547828723

| Tab | What it holds |
|---|---|
| `Store · Traffic` | Sessions, users, new users, engagement, key events by channel and campaign |
| `Store · Events` | Every event by day |
| `Store · Pages` | Views and sessions per page |
| `Store · Audience` | Country, device, new vs returning |
| `Store · Custom N` | Auto-discovered: every custom dimension registered on that property |

### Lead journey

| Tab | What it holds |
|---|---|
| `Web · Lead Sessions` | Website activity keyed by `sessionManualTerm` (the token in `utm_term`) |
| `Store · Lead Sessions` | The same on the Shopify listing property |
| `Lead Journey` | **Looker Studio source.** One row per lead per campaign, every stage as a 0/1 column |
| `Campaign Summary` | One row per campaign, with rates at each step |

Fully documented in [Lead journey](#lead-journey-smartlead--ga4) below.

### Derived and meta

| Tab | What it holds |
|---|---|
| `Dashboard` | **Start here.** Headline KPIs with period-over-period change, top campaigns, channels, pages, click placements, and a 30-day trend |
| `Funnel by Campaign` | Landings, clicks and click rate per channel/source/medium/campaign, full history |
| `Funnel by Day` | The same, per day |
| `_Status` | Last run, window, property IDs, per-tab row counts, skipped fields, errors |
| `_Fields` | Every dimension and metric each property supports (on demand) |

These are the **only** tabs cleared and rewritten each run, because they are
entirely derived. They are rebuilt from the whole archive, so they span your full
history rather than the last few days. Every other tab is merge-only.

The Dashboard is moved to first position on each run, so opening the spreadsheet
lands on it.

### Why the Dashboard has no "Users" figure

Deliberate. `totalUsers` is **not additive** — the same person appears in many
dimension rows, so summing the column double counts them badly. GA4 can only
de-duplicate users at query time, and the archive stores rows already split by
dimension.

So the Dashboard totals only additive metrics: sessions, engaged sessions, page
views, event counts, landings, clicks. Rates are recomputed from their
components rather than averaged:

- **Engagement rate** = total engaged sessions ÷ total sessions
- **Avg session duration** = session-weighted, not an average of daily averages
- **Click rate** = total clicks ÷ total landings

Per-row user counts are still archived in the `Web ·` and `Store ·` tabs if you
need them for a single slice.

---

## Why click reports use different fields from landing reports

Worth knowing, because the two sets of numbers are attributed differently.

Looking at [../js/utm.js](../js/utm.js), the two events do not carry the same
parameters:

| Event | Parameters sent |
|---|---|
| `brix_campaign_landing` | `utm_*`, `first_utm_*`, `has_campaign`, `landing_page` |
| `brix_app_store_click` | `click_page`, `click_placement`, `click_text`, `click_destination` only |

**The click event carries no campaign data at all.** Asking GA4 for
`customEvent:utm_source` on it returns `(not set)` for every row.

So the landing tabs use your custom dimensions — that is the site's own 90-day
last-touch model, which is richer than GA4's, surviving across sessions via the
attribution cookie. The click tabs and both funnel tabs use GA4's built-in
`sessionSource` / `sessionMedium` / `sessionCampaignName` instead.

The funnel compares landings and clicks using the **same** session attribution on
both sides, so the ratio is valid. But a campaign's landing count in
`Web · Landings Last Touch` will not always equal its count in `Web · Funnel Source`,
because the two use different attribution models. That is expected, not a bug.

To make click reports use your custom dimensions instead, `js/utm.js` would need
to attach the UTM params to the click payload the way it already does for the
landing payload. That is a site change and is deliberately not part of this setup.

---

## Lead journey (Smartlead × GA4)

Joins the `Smartlead Events` webhook log to GA4 activity, so you can follow an
individual lead from send through to the Add App click on the Shopify listing.

### How the join works

Smartlead embeds a token in `utm_term` on every link. The token is
`HMAC-SHA256(key, lowercase(trimmed email))` in hex, which is **deterministic** —
the same lead gets the same token on every send, so a journey stitches together
across a whole sequence.

**The key is not in this repository and does not need to be.** The
`Smartlead Events` tab already records `Link Clicked` on the same row as
`Lead Email`, and that URL contains the token. The script reads the pair straight
out of your own sheet. A lead who never clicked has no website activity to
attribute, so nothing is lost.

*Optional fallback:* set a script property named `LEAD_TOKEN_KEY` to the hex key
(**Project Settings → Script Properties**, never in the file) and the script will
also compute tokens for leads whose click row was missed. Script properties are
not stored in the code and not committed.

On the GA4 side the join uses **`sessionManualTerm`**, the built-in session-scoped
dimension derived from `utm_term`. Being session-scoped, it attaches to *every*
event in the session — including `brix_app_store_click`, which carries no
parameters of its own, and `Add App button` on the listing property, which has no
custom dimensions registered at all. No site change and no new custom dimension
was needed for any of this.

Matching is on **token + campaign** where possible, falling back to token alone
only when a lead belongs to exactly one campaign. Otherwise a single visit would
be credited to every campaign that lead is in. The `Attribution` column records
which path was used.

### The stage ladder

| # | Stage | Source |
|---|---|---|
| 0 | Bounced | `EMAIL_BOUNCE` |
| 1 | Sent | `EMAIL_SENT` |
| 2 | Opened | `EMAIL_OPEN` |
| 3 | Clicked | `EMAIL_LINK_CLICK` |
| 4 | Visited website | any event in `Web · Lead Sessions` |
| 5 | Reached listing | any event in `Store · Lead Sessions` |
| 6 | Clicked Add App | `Add App button` or `add_to_cart` on the listing |

Stages can be **skipped**: a mail linking straight to the listing lands a lead at
5 without ever passing 4, which is handled.

**Bounced is a branch, not a rung.** A bounced address never had the chance to
open, so it is marked `Bounced` rather than counted as "sent but never opened" —
which would otherwise quietly depress your open rate. `Campaign Summary` measures
open and click rates against **delivered** (sent minus bounced) for the same
reason.

### Enable the missing webhook events

Smartlead can send ten event types. Your webhook is currently configured for four:
`EMAIL_SENT`, `EMAIL_OPEN`, `EMAIL_LINK_CLICK`, `LEAD_CATEGORY_UPDATED`.

In Smartlead → campaign → Webhooks, tick these on the existing endpoint:

- **`EMAIL_REPLY`** and **`UNTRACKED_REPLIES`** — replies. Both count; tracked
  replies alone undercount, because Smartlead files replies it cannot thread
  under the second name.
- **`EMAIL_BOUNCE`** — without it, every rate is measured against sent rather
  than delivered, and a bad list looks like bad copy.
- **`LEAD_UNSUBSCRIBED`** — unsubscribes.

The columns already exist and stay at zero until the events arrive. `_Status`
reminds you under **LEAD JOURNEY** while any are missing.

> One caveat: reply and bounce payloads carry different fields from send
> payloads. If new rows appear with a blank `Lead Email`, the Apps Script webhook
> receiver that writes this tab needs updating to map them.

### Connecting Looker Studio

`Lead Journey` is shaped for it deliberately: a flat rectangle, one header row,
every stage a 0/1 column.

1. Looker Studio → **Create → Data source → Google Sheets**.
2. Pick this spreadsheet, worksheet **`Lead Journey`**, tick *Use first row as headers*.
3. Build the funnel with **scorecards using SUM** on `Sent`, `Opened`, `Clicked`,
   `Replied`, `Visited Website`, `Reached Listing`, `Clicked Add App`. Because
   each is 0/1, the sum is the lead count at that stage.
4. Add a **drop-down filter control** on `Campaign Name` for per-campaign slicing.
5. `Stage No` sorts leads by how far they got; `Stage` is the readable label.

Use `Campaign Summary` as a second data source when you want ready-made rates
rather than recomputing them in Looker.

### Known limits

- **Completed installs are not visible.** `Clicked Add App` is the click on the
  listing's install button. What happens after, in the Shopify admin consent
  flow, appears only in the Shopify Partner Dashboard.
- **`utm_term` is shared with Google Ads.** Your ad keywords occupy the same
  field. They simply match no lead token and are counted under
  **LEAD JOURNEY** in `_Status` as unmatched. A non-zero count there is normal.
- **Pre-token history cannot be joined.** Landings from before you switched to
  hashed tokens carry either a plaintext address or a value GA4 redacted, so
  those visits cannot be attributed to a lead.
- **Sessions are not summed across event rows.** Each event in a session repeats
  that session's count, so the script takes the daily maximum instead. Event
  counts are additive and are summed.

---

## How the merge works

Each row's identity is its **dimension values**, which always include the date.

- Fetched row whose key already exists → that row is **replaced**.
- Fetched row with a new key → **appended**.
- Existing row not in this fetch → **left completely alone**.

That last rule is what makes the archive permanent. Hourly runs only request the
last 3 days (`refreshWindowDays`), so everything older is never even considered.

A trailing `Updated At` column records when each row last changed. It sits after
the metrics, so it can never interfere with the key.

**Today's row moves.** Runs cover through `today`, which is still filling in. Each
hourly pass corrects it. Once the day closes, the final value sticks permanently.
Do not quote today's number as final.

**Adding dimensions later works.** Register a new custom dimension in GA4 and it
appears as a new column on the next run, with existing rows remapped by column
name rather than dropped. One caveat: rows already archived for dates inside the
3-day window may briefly duplicate across the change, because a blank and a real
value are different keys. Older rows are unaffected.

---

## Adding more data

**You mostly do not have to.** The script reads each property's metadata at the
start of every run and:

- skips any dimension or metric that property does not support, instead of failing;
- generates `Custom N` tabs covering **every** custom dimension it finds.

So registering a new custom dimension in GA4 is enough — it starts being archived
on the next run with no code change.

To add a specific field to a specific tab, edit the `REPORTS` array. Entries are
`['apiName', 'Column header']` pairs plus a `property` key. Custom dimensions use
`customEvent:` and the exact parameter name from
[ga4-utm-setup.md](ga4-utm-setup.md). Built-ins like `country`, `pagePath` and
`sessionSource` need no prefix.

### The dimension budget

The API caps a request at **9 dimensions and 10 metrics**, and there is a trap in
how it counts:

> A dimension named only in the **filter** still counts against the 9.

So a report filtered to a single event, like `Web · Landings Last Touch`, may
select only **8** dimensions — the `eventName` it filters on eats the ninth slot.
Asking for 9 fails with *"this request is for 10 dimensions"*.
`Web · Funnel Source` gets the full 9 because it selects `eventName` itself, so
nothing extra is added.

`dimensionBudget_` works this out per report and trims the excess rather than
letting the request fail, recording anything it cut under **SKIPPED FIELDS** in
`_Status`. If you see a column you wanted listed there, drop a less useful one
from that report or split it into a second tab.

Two columns were deliberately cut for this reason:

- `has_campaign` from both landing tabs — fully derivable from whether **Source**
  is `(not set)`.
- `Device` from `Web · Store Clicks` — already archived daily in `Web · Channels`
  and `Web · Audience`.

### What can never be archived

Click IDs (`gclid`, `fbclid`, `ttclid`, `msclkid`, `li_fat_id`), `referrer` and
`click_destination` are collected by the site but deliberately **not registered**
as custom dimensions, for the cardinality reasons in
[ga4-utm-setup.md](ga4-utm-setup.md). Unregistered parameters are unreachable
through the Data API at any cost. If you ever need them at scale, the route is
GA4's BigQuery export, not this script.

---

## Troubleshooting: "User does not have sufficient permissions for this property"

GA4 returns this identical message whether the account lacks access **or** the
property ID does not exist, so the error cannot tell you which. Run the diagnostic
instead of guessing.

**GA4 → Diagnose access problems**, then **View → Logs** in the editor.

It prints the account the script is authorised as, then every GA4 property that
account can see, marking ones that match your config.

| What you see | What it means |
|---|---|
| Zero properties listed | Wrong Google account. Apps Script authorises as whichever account was signed in, which is often not the one holding GA4 access. |
| Properties listed, different IDs | Wrong IDs in `PROPERTIES`. The usual mix-up is the **Stream ID** from Admin → Data Streams instead of the **Property ID** from Admin → Property Settings. |
| IDs match but still failing | Real permissions gap. Check **Admin → Property access management** for that exact email. GA4 access is separate from Sheets access. |

---

## Other things worth knowing

**Custom dimensions are not retroactive, and are per property.** They only return
data for dates after they were created, and a dimension registered on the website
property does not exist on the store property.

**`keyEvents` needs the key event marked.** If `brix_app_store_click` has not been
marked as a key event (step 4 of [ga4-utm-setup.md](ga4-utm-setup.md)), those
columns return zeros while everything else populates.

**`(other)` rows.** A dimension exceeding GA4's cardinality limit collapses its
overflow into one `(other)` bucket. None of the registered Tier 1 dimensions
should approach this.

**Runtime, not quota, is the limit on frequency.** A standard property allows
roughly 200,000 Data API tokens/day; hourly runs use a tiny fraction. Apps Script
runtime is the real ceiling — 90 minutes/day on consumer Gmail, 6 hours on
Workspace. Hourly runs stay small precisely because they only touch 3 days. If you
ever hit the limit, lower `refreshWindowDays` or move to every 2 hours.

**Runs cannot overlap.** A script lock means an hourly trigger firing during a
manual run is skipped rather than corrupting a tab mid-write.

**Sheets cap at 10 million cells.** Far off, but if an archive ever approaches it,
the tab to watch is `Web · Pages` or a wide `Custom N` tab.
