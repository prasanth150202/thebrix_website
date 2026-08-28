# Brix website

A PHP site (no framework, no build step) served by Apache. Pages are
top-level `.php` files reached at extensionless URLs via `.htaccess`;
shared chrome lives in `includes/`; articles come out of MySQL and are
edited in `/admin`.

## Colour

**The site uses one palette and only that palette. See
[docs/brand-palette.md](docs/brand-palette.md) before touching any
colour.**

- Brand `#7C3AED` · second gradient stop `#8B5CF6` · gradient tail
  `#A78BFA` · pressed/hover `#6D28D9` · AI Blue `#38BDF8` · Growth
  Green `#22C55E` · Light Background `#F8F6FF` · Card `#FFFFFF` ·
  Dark `#140B2D` · Heading `#120A24` · Body `#5F5570`.
- The violet ramp is deliberately one step lighter than the brand
  sheet prints it, and cannot go lighter: white on `#7C3AED` is
  5.7:1 (AA); the next step down fails at 4.2:1.
- New CSS must use the tokens in the `:root` blocks of
  `css/styles.css` and `admin/assets/admin.css` — `var(--brand)`,
  `var(--ink)`, `var(--paper-soft)`, `var(--grad-strong)` and so on.
  Do not hardcode a brand hex in a rule.
- Green is for a result going up (unlocked, free, added, saved, ✓), not
  for decoration. Blue is for Brix AI. Everything else is violet.
- The exceptions that must stay off-palette (status colours, the Google
  SERP preview, macOS chrome dots, product thumbnails, Shopify's own
  badges) are listed at the end of `docs/brand-palette.md`.

## Assets

`ASSET_CSS_VER` and `ASSET_JS_VER` in `includes/bootstrap.php` are the
cache-busting query strings on `css/styles.css` and `js/main.js`. Bump
the relevant one whenever you change either file, or returning visitors
keep the old copy.
