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

### Tier 1 — register these now (12)

These cover all core reporting needs.

| Display name | Parameter name | What it tells you |
|---|---|---|
| Campaign Source | `utm_source` | Where the visitor came from (google, newsletter, facebook) |
| Campaign Medium | `utm_medium` | Traffic type (cpc, email, social, Organic) |
| Campaign Name | `utm_campaign` | The specific campaign |
| Campaign ID | `utm_id` | Campaign identifier |
| Has Campaign | `has_campaign` | `true` / `false` — whether the visit carried any campaign data at all |
| Landing Page | `landing_page` | The page they first arrived on |
| Google Click ID | `gclid` | Google Ads click identifier, needed for offline conversion import |
| First Touch Source | `first_utm_source` | The campaign that *originally* discovered this visitor |
| First Touch Medium | `first_utm_medium` | Medium of that original discovery |
| First Touch Campaign | `first_utm_campaign` | Campaign name of that original discovery |
| Click Placement | `click_placement` | Which on-page section the install click came from |
| Click Page | `click_page` | Which page the install click happened on |

### Tier 2 — optional, add if the team wants deeper reporting (5)

| Display name | Parameter name | What it tells you |
|---|---|---|
| Campaign Term | `utm_term` | Paid search keyword |
| Campaign Content | `utm_content` | Ad variant / creative |
| Click Text | `click_text` | The button label that was clicked |
| Facebook Click ID | `fbclid` | Meta click identifier |
| Referrer | `referrer` | Referring URL (partly duplicates GA4's built-in Page referrer) |

### Tier 3 — only on request

The remaining first-touch parameters (`first_utm_term`, `first_utm_content`,
`first_utm_id`, `first_gclid`, `first_fbclid`, `first_ttclid`, `first_msclkid`,
`first_li_fat_id`) and other platform click IDs (`ttclid`, `msclkid`,
`li_fat_id`) are all being collected and can be registered later if needed.

### Do NOT register

- **`click_destination`** — this is the full outgoing URL and routinely exceeds
  GA4's 100-character limit for parameter values, so it would be stored truncated
  and misleading. It remains available in GTM and the browser `dataLayer` for
  debugging. The same information is already captured accurately across the
  separate `utm_*` dimensions.

**Budget note:** a standard GA4 property allows **50** event-scoped custom
dimensions. Tier 1 + Tier 2 uses 17, leaving plenty of headroom.

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

1. Open the BRIX site with a test campaign appended, for example:
   `https://thebrix.io/?utm_source=test&utm_medium=paid&utm_campaign=setup_check`
2. In GA4 go to **Admin → DebugView**
3. Confirm `brix_campaign_landing` appears, and clicking it shows the parameters
   `utm_source=test`, `utm_medium=paid`, `utm_campaign=setup_check`
4. Click any "Install" button on the site and confirm `brix_app_store_click` fires

> DebugView only shows traffic from a device in debug mode. Easiest method: install
> the **Google Analytics Debugger** Chrome extension and switch it on.

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
