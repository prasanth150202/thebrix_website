# Site map — every page, for a Claude instance new to this repo

This is a reference index, not a tutorial. Read [CLAUDE.md](../CLAUDE.md)
first for the stack, the colour rules and the asset-version convention —
this file only maps out what each page *is* and why it exists.

Routing: URLs are extensionless (`/pricing`, not `/pricing.php`). `.htaccess`
rewrites `/slug` to `slug.php` when that file exists, redirects the old
`.html`/`.php`/slash forms permanently (301) to keep search rankings, and
routes `/blog/<slug>` and `/case-study/<slug>` through `article.php?slug=...`.
See `.htaccess` itself for the full rule order — it's short and worth
reading directly rather than summarizing further here.

Every page includes `includes/header.php`, which reads a contract of
`$page_*` variables set before the include (title, description, canonical,
robots, nav highlight, chrome level, JSON-LD schema — the full list is
documented in the comment at the top of that file). Set the ones that
differ from the defaults; don't touch header.php itself for a new page.

## Core marketing pages (indexed, full nav)

| Page | Purpose |
|---|---|
| [index.php](../index.php) | Homepage. Split hero with the live cart-mock demo, SoftwareApplication JSON-LD kept in sync with pricing.php by hand. |
| [features.php](../features.php) | Feature breakdown: AI upsells, Frequently Bought Together, Bundle Builder, coupon sliders, reward progress bars. Runs the dark `#aiChat` typing demo. |
| [pricing.php](../pricing.php) | Free / $29 Starter / $79 Pro plans, feature comparison, FAQ list (the FAQ content is reused on /why-brix). |
| [case-studies.php](../case-studies.php) | Index of case-study articles (`post_type = case_study`, pulled from the DB via `includes/posts.php`). |
| [blog.php](../blog.php) | Index of blog articles (`post_type = blog`), same DB source. |
| [how-to.php](../how-to.php) | Setup guides, anchor-linked sections. |
| [tutorials.php](../tutorials.php) | Video tutorials; the lesson list lives in `includes/tutorials.php` and is shared with /walkthrough. |
| [contact.php](../contact.php) | General contact form → `contact_submissions` table via `includes/lead-form.php`. |
| [privacy.php](../privacy.php) / [terms.php](../terms.php) | Legal boilerplate, yearly-priority in the sitemap. |
| [404.php](../404.php) | `.htaccess` sets this as `ErrorDocument 404`. |

Articles (both blog posts and case studies) render through
[article.php](../article.php), keyed by the `slug` query param the rewrite
rule builds (`blog-<slug>` / `case-study-<slug>` — the DB still stores the
old flat filename stem). Shared rendering lives in
[includes/article-view.php](../includes/article-view.php); markdown → HTML
conversion is [includes/markdown.php](../includes/markdown.php).

## Calculator / AI-demo pages (unindexed for now)

Both are `noindex, nofollow` — new, not yet linked from nav or sitemap, and
waiting on a real traffic destination before `$page_robots` flips.

- **[revenue-calculator.php](../revenue-calculator.php)** — a homepage
  *mirror*: every section is the homepage, copied as-is, with the "Why
  Brix" section's visual swapped for an AOV/revenue calculator
  (`js/calculator.js`) and a lead form added right after it. Full nav/footer
  chrome, unlike the pages below.
- **[book-a-demo.php](../book-a-demo.php)** — a focused, *not*
  homepage-mirrored funnel. Minimal chrome, four sections only: a hero
  where a Brix AI chat exchange cross-fades into the cart it was working on
  (`js/hero-ai-demo.js` + the `.ai-stage`/`.ai-phase` CSS — both phases
  share one CSS Grid cell so the box never resizes when the active phase
  swaps), a step-by-step AOV wizard (`js/wizard.js`), a lead form pre-filled
  from the wizard's result, and a short why-Brix recap. No pricing, no
  testimonials — one job only.

Both post to `brix_lead_handle()` (`includes/lead-form.php`) tagged with
their own `source` (`'revenue-calculator'` / `'book-a-demo'`), so leads are
distinguishable in `/admin/submissions.php`.

## Campaign landing pages (unlisted: no nav link, absent from sitemap.php,
## silent in robots.txt — the URL itself is the only way in)

Four pages, same lead-form pattern, each written for a different kind of
traffic and answering a different question:

| Page | For | Pitch style |
|---|---|---|
| [cart-that-sells.php](../cart-that-sells.php) | Meta ads, email list | Short. One argument, two CTAs (App Store install or the form). Old alias: `/start`. |
| [walkthrough.php](../walkthrough.php) | Cold traffic, mail list | Long. Teaches via the tutorial videos before asking for an email. Old alias: `/demo`. |
| [cart-review.php](../cart-review.php) | Warm/retargeting traffic | One screen, one form, nothing to read. Old alias: `/try`. |
| [why-brix.php](../why-brix.php) | Traffic that's heard the name, not what it is | Explains first, asks second; reuses existing homepage/pricing components, ships no new CSS/JS. |

All four set `$page_chrome = 'minimal'` and their own `noindex` robots
directive (mirrored as an `X-Robots-Tag` header by header.php). Every CTA
points at `SHOPIFY_APP_URL`, which `js/utm.js` rewrites with the actual
arrival campaign for attribution.

## Utility endpoints

- [memo.php](../memo.php) — newsletter signup POST target. Named after the
  memo, not "newsletter"/"subscribe", because ad blockers cancel requests
  to paths containing those words; `.htaccess` also maps the legacy
  `newsletter-subscribe.php` address here for browsers holding a cached
  old `main.js`.
- [sitemap.php](../sitemap.php) → served at `/sitemap.xml`. Static pages
  hardcoded with hand-set priorities; blog/case-study entries pulled live
  from the DB. Campaign/calculator pages are deliberately absent.

## Admin panel (`/admin`, session-authenticated, single account)

Entry point [admin/index.php](../admin/index.php); every admin page starts
with `admin/_boot.php` (loads shared code, runs pending schema migrations
once per session) and shares chrome via `admin/_layout.php`. Article
editing ([admin/post-edit.php](../admin/post-edit.php)) autosaves as a
draft; only Publish touches the public-facing row. Other notable ones:
`bulk-edit.php` (bulk field edits + link/CTA/text-replacement insertions
across posts, preview-then-apply), `submissions.php` (contact + newsletter
lists, CSV export), `upload-image.php`, `migrate.php` (one-time import of
the original eight articles, safe to rerun). `setup.php` and
`check-sheet.php` are retired — kept in the repo as a checkout-away
reference, not wired into any live flow.

## API (`/api`)

`api/chat.php` is the backend for the floating "Ask Brix AI" launcher —
see the `BRIX_CHAT_ENABLED` comment in `includes/bootstrap.php` for why
it currently runs in `'partial'` mode rather than `true`. It classifies
against the curated Q&A in `api/answers.php`, falls back to keyword
retrieval over `knowledge/*.md` (`api/retrieval.php`), and runs
prompt-injection/scope guardrails (`api/guardrails.php`) before anything
reaches the model.

## Shared includes worth knowing about

- `includes/bootstrap.php` — every entry point's first require. Config
  loading, lazy PDO, session setup, and the `ASSET_*_VER` cache-busting
  constants (bump the relevant one whenever you change that CSS/JS file —
  see CLAUDE.md).
- `includes/header.php` / `includes/footer.php` — shared chrome; footer's
  third column is contextual (`$footer_col3`: case-studies/blog/howto/tutorials).
- `includes/lead-form.php` — the form handler every landing page above uses.
- `includes/posts.php` — DB query helpers for the posts table (always
  filtered to `status = 'published' AND deleted_at IS NULL` on the public
  side).
- `includes/sheets.php` — mirrors new leads into a Google Sheet, best-effort
  and non-blocking; DB stays the source of truth (see `docs/lead-sheet.md`).
- `includes/schema.php` — `CREATE TABLE IF NOT EXISTS` schema plus additive
  migrations for columns/indexes added after initial install.
- `includes/seo.php` — JSON-LD builders (`brix_schema_organization()` etc.)
  consumed via `$page_schema`.

## Other docs in this folder

`brand-palette.md` (colour system — also linked from CLAUDE.md),
`brix-ai.md` (the chat launcher, its modes and OpenRouter setup),
`cms-setup.md`, `policies.md`, `website_content.md`, `lead-sheet.md` /
`.gs` (Google Sheets lead mirror), `ga4-*` (GA4 fix notes and the Sheets
export script).
