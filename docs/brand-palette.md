# BRIX colour palette

The nine swatches on the brand sheet in `Colour Pallete/`. Every colour
on the site and in the admin panel comes from this list. Nothing new
gets added here without the sheet changing first.

| Role | Hex | Where it goes |
|---|---|---|
| Resting brand | `#7C3AED` | Buttons, links, active states, icons, the brand everywhere |
| Second gradient stop | `#8B5CF6` | The lighter half of every violet gradient |
| Gradient tail | `#A78BFA` | The third stop of the hero gradient; violet text on dark |
| Pressed / hover | `#6D28D9` | Button hover, link hover, text on a violet tint |
| AI Blue Accent | `#38BDF8` | Anything that is specifically Brix AI, and the second hue in ambient glows |
| Growth Green Accent | `#22C55E` | A result going up: unlocked, free, added, saved, ✓ |
| Light Background | `#F8F6FF` | Page and section backgrounds |
| Card Background | `#FFFFFF` | Cards, panels, form fields |
| Dark Background | `#140B2D` | Dark sections, the final CTA band, the footer |
| Heading Text | `#120A24` | H1–H3 and anything bold enough to be a heading |
| Body Text | `#5F5570` | Paragraphs, labels, captions |

Hero gradient: `#7C3AED → #8B5CF6 → #A78BFA`.

## Why the violets are one step lighter than the sheet

The sheet prints Primary Violet as `#6D28D9`. Across a full page that
read as too heavy, so the whole ramp moved one step lighter: the
sheet's Secondary `#7C3AED` is the resting brand colour, and its
Primary `#6D28D9` is now the pressed and hover tone. Both sheet
colours are still on the page, in a different order.

**This is as light as the brand colour can go.** White on `#7C3AED`
is 5.7:1, which passes AA. One more step (`#8B5CF6`) is 4.2:1 and
fails it, so buttons and links must not be lightened further.

## Where it lives

Both stylesheets open with the palette as CSS custom properties, then
express every role in terms of them:

- `css/styles.css` — the public site. `:root` holds the ten swatches
  under `--violet`, `--ai-blue`, `--bg-dark` and so on, then the role
  names the rest of the file uses (`--brand`, `--ink`, `--paper-soft`,
  `--grad-strong`, …) point at them.
- `admin/assets/admin.css` — the admin panel, which needs a subset.

**Changing a colour means editing those two `:root` blocks and nothing
else.** No rule below them hardcodes a brand hex.

## The colours that are deliberately not from the palette

These are all semantic or borrowed, and they must stay as they are:

- **Status colours** — `--red #D64545`, `--amber #E8912D`, `--green
  #12A150` in the admin, and the error/warning washes on the public
  forms. They mean danger, caution and success, not brand.
- **The cost/loss reds** — `#C2410C` and `rgba(228, 87, 61, …)` on the
  "why AOV matters" panel. They are the half of a pair whose other half
  is the growth green.
- **The Google SERP preview** in the post editor — `#1a0dab` title and
  `#1a7f37` URL. It is a picture of a search result, so it uses
  Google's colours or it stops being one.
- **The browser-chrome dots** on the UI mockups — `#FF5F57 #FEBC2E
  #28C840`, which is macOS.
- **Product thumbnails** in the cart mockups — the green hoodie, tan
  mug and charcoal beanie swatches. They stand in for merchandise
  photography, so they should not read as brand.
- **The Shopify App Store badges** in `assets/` and `shopify_assets/`,
  which are Shopify's artwork.
