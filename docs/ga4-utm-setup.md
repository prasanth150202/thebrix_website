# GA4 setup for BRIX website attribution

Handoff instructions for whoever administers GA4 property **G-23RTZ99F2K**.

Estimated time: 20 minutes. No code changes required — the website side is already live.

---

## 1. Background: what the website now sends

The BRIX marketing site now captures campaign data on every visit and sends two
custom events to GA4. Both are already firing; nothing in GA4 is broken without
this setup, but the data will be **collected and not reportable** until the steps
below are done.

**Event 1 — `brix_campaign_landing`**
Fires once per session, on the first page a visitor lands on. It reports where
the visitor came from, including when they came from nowhere identifiable
(direct or organic).

**Event 2 — `brix_app_store_click`**
Fires every time a visitor clicks an "Install" button that sends them to the
Shopify App Store. It reports which page and which on-page section the click
came from.

Attribution is stored in a first-party cookie plus `localStorage` for **90 days**,
using a last-touch model that falls back to first-touch. So a visitor who arrives
from a Google ad in May and returns directly in August is still credited to the
May campaign.

---

## 2. Why registration is required

GA4 receives every custom parameter we send, but it will **not** display any of
them in reports until each one is registered as a Custom Dimension. Unregistered
parameters are silently dropped from the reporting interface.

**Critical timing note:** custom dimensions only populate from the moment they are
created. They are **not retroactive**. Any data collected before registration will
never appear in reports. Register these as early as possible.

---

## 3. Register the custom dimensions

For each row in the table below:

1. Open **GA4 → Admin** (gear icon, bottom left)
2. Under the *Property* column, click **Custom definitions**
3. Click **Create custom dimension**
4. Fill in:
   - **Dimension name** → the "Display name" column below
   - **Scope** → `Event` (for every row in this table)
   - **Event parameter** → the "Parameter name" column below, typed **exactly**, lowercase, underscores included
5. Click **Save**

> The **Event parameter** field must match character-for-character. A typo means the
> dimension silently stays empty forever with no error shown.

### About the "may negatively impact your reports" warning

GA4 shows this advisory on the custom dimension creation screen. It refers to
**cardinality** — how many distinct values a dimension can take.

GA4 standard reports hold a limited number of rows per dimension per day (roughly
500). Once a dimension exceeds that, GA4 stops listing individual values and dumps
the overflow into a single bucket labelled **`(other)`**. That bucket cannot be
split back apart, so the detail is lost permanently for the affected report.

For every dimension in the Tier 1 table below, the warning is **safe to dismiss**.
They are all low-cardinality: a handful of campaign names, five or six mediums, a
dozen page paths. They will never approach the limit.

The warning is **not** safe to ignore for click IDs, which is why they have been
excluded from the register list — see *Do NOT register* below.

### Tier 1 — register these now (11)

These cover all core reporting needs, and all are low-cardinality.

| Display name | Parameter name | What it tells you |
|---|---|---|
| Campaign Source | `utm_source` | Where the visitor came from (google, newsletter, facebook) |
| Campaign Medium | `utm_medium` | Traffic type (cpc, email, social, Organic) |
| Campaign Name | `utm_campaign` | The specific campaign |
| Campaign ID | `utm_id` | Campaign identifier |
| Has Campaign | `has_campaign` | `true` / `false` — whether the visit carried any campaign data at all |
| Landing Page | `landing_page` | The page they first arrived on |
| First Touch Source | `first_utm_source` | The campaign that *originally* discovered this visitor |
| First Touch Medium | `first_utm_medium` | Medium of that original discovery |
| First Touch Campaign | `first_utm_campaign` | Campaign name of that original discovery |
| Click Placement | `click_placement` | Which on-page section the install click came from |
| Click Page | `click_page` | Which page the install click happened on |

### Tier 2 — optional, add if the team wants deeper reporting (3)

| Display name | Parameter name | What it tells you |
|---|---|---|
| Campaign Content | `utm_content` | Ad variant / creative |
| Click Text | `click_text` | The button label that was clicked |
| Campaign Term | `utm_term` | Paid search keyword. Low-cardinality on exact-match keyword sets, but broad-match search terms can grow large — review before adding |

### Tier 3 — only on request

The remaining first-touch UTM parameters (`first_utm_term`, `first_utm_content`,
`first_utm_id`) are collected and can be registered later if needed. They are
low-cardinality and safe.

### Do NOT register

- **All click IDs** — `gclid`, `fbclid`, `ttclid`, `msclkid`, `li_fat_id`, and every
  `first_` variant of them. These are **unique per click**, so cardinality grows
  without limit and any report using them collapses into `(other)`. This is exactly
  what GA4's warning is about.

  Nothing is lost by skipping them. The values are still collected on the events and
  visible in DebugView, they are still forwarded to the Shopify App Store on the
  outgoing link, and GA4 handles Google Ads attribution natively once the Google Ads
  account is linked to the property. If raw click IDs are ever needed at scale, the
  correct route is the BigQuery export, not a custom dimension.

- **`referrer`** — full referring URLs are high-cardinality for the same reason, and
  GA4 already provides a built-in *Page referrer* dimension. Redundant and risky.

- **`click_destination`** — this is the full outgoing URL. It routinely exceeds
  GA4's 100-character limit for parameter values, so it would be stored truncated
  and misleading, and it becomes high-cardinality once click IDs are appended. It
  remains available in GTM and the browser `dataLayer` for debugging. The same
  information is already captured accurately across the separate `utm_*` dimensions.

**Budget note:** a standard GA4 property allows **50** event-scoped custom
dimensions. Tier 1 + Tier 2 uses 14, leaving plenty of headroom.

---

## 4. Mark the install click as a Key Event

This lets the App Store click be used as a conversion in reports and in Google Ads
bidding.

1. **Admin → Events** (under *Property*)
2. Find `brix_app_store_click` in the list
3. Toggle **Mark as key event** on

> If the event is not listed yet, it appears within roughly 24 hours of the first
> real click. You can create it ahead of time via **Admin → Key events → New key
> event** and entering the name `brix_app_store_click` exactly.

---

## 5. Verify it is working

**Immediate check (DebugView):**

> **Open a brand-new incognito window first.** `brix_campaign_landing` fires
> **once per browser session** by design, so that a visitor reading four pages is
> counted as one arrival rather than four. If the site has already been opened in
> the current session, the event will **not** fire again and the test will look
> like a failure. This is the single most common false alarm when checking this
> setup.
>
> To re-test in an already-used tab instead, run `sessionStorage.clear()` in the
> browser console and reload.

1. In a **fresh incognito window**, open the BRIX site with a test campaign
   appended:
   `https://thebrix.io/?utm_source=test&utm_medium=paid&utm_campaign=setup_check`
2. In GA4 go to **Admin → DebugView**
3. Confirm `brix_campaign_landing` appears, and clicking it shows the parameters
   `utm_source=test`, `utm_medium=paid`, `utm_campaign=setup_check`
4. Click any "Install" button on the site and confirm `brix_app_store_click` fires

> DebugView only shows traffic from a device in debug mode. Easiest method: install
> the **Google Analytics Debugger** Chrome extension and switch it on.

**Checking in the browser instead of GA4:** in DevTools → Network, filter on
`g/collect`. A correct fresh-session load produces a request containing
`en=brix_campaign_landing` along with `ep.utm_source`, `ep.utm_medium` and
`ep.landing_page`. Clicking an Install button produces a second request with
`en=brix_app_store_click`, `ep.click_page` and `ep.click_placement`.

> Note that `window.dataLayer` only reflects the **current page load**. Navigating
> to another page resets it, so a landing event that fired on the previous page will
> not be visible there. Inspect the `g/collect` requests rather than the dataLayer
> when in doubt.

**Same-day check:** **Reports → Realtime** will show both event names within seconds.

**Full reporting:** registered custom dimensions take **24–48 hours** to appear in
standard reports and Explorations. This delay is normal and is not a
misconfiguration.

---

## 6. Suggested first report

Once data has accumulated for 48 hours, build an Exploration
(**Explore → Blank**):

- **Dimensions:** Campaign Source, Campaign Medium, Click Placement
- **Metrics:** Event count
- **Filter:** Event name exactly matches `brix_app_store_click`

This answers: *which campaigns, and which sections of which pages, actually drive
people to install.*

---

## 7. Google Tag Manager — no action needed

Container **GTM-M27VLZJ4** already receives both events on the `dataLayer`
automatically. No tags, triggers, or variables need to be created for GA4 to work,
because the site also sends the events directly via `gtag()`.

GTM only becomes relevant if the team later wants to forward these same events to
other platforms (Meta Pixel, TikTok Pixel, LinkedIn Insight Tag). At that point the
events are already available as Custom Event triggers named `brix_campaign_landing`
and `brix_app_store_click`.

**Important — avoid double counting:** if anyone adds a GA4 event tag in GTM that
fires on these triggers and sends to G-23RTZ99F2K, every event will be counted
twice. The site already sends them directly, so no GA4 tag should be created in
GTM for these two events.

---

## 8. What this does not cover

The install conversion itself happens on Shopify, not on the BRIX website. GA4 will
show clicks *to* the App Store, but cannot see whether the install completed.

To close that loop, cross-reference in the **Shopify Partner Dashboard**, where the
UTM parameters arrive on the listing URL:

- Filter on `utm_id=Website` → every install that came via the BRIX website
- Then split by `utm_medium` → separates genuinely paid traffic (`cpc`,
  `paid_social`) from organic (`Organic`, `social`)
