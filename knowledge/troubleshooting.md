# Troubleshooting

## The app is not working, or a feature is broken - how to fix it

Start here when something is wrong and it is not obvious which feature is at fault. Most
reports of a bug, or of BRIX being broken, buggy, or not loading at all, come down to one
of these four. They are worth checking in this order.

1. **The app embed is off.** Online Store → Themes → Customize → App embeds → turn BRIX
   on, then Save. This is by far the most common cause: settings save correctly inside
   BRIX and still do nothing on the storefront until the embed is enabled.
2. **The theme is not an Online Store 2.0 theme.** Legacy pre-2.0 themes are not
   supported, so BRIX cannot appear on them.
3. **The feature requires a paid plan.** On the Free plan most features can be fully
   configured and previewed, but they do not publish to the live store.
4. **The change was not saved, or you are looking at the preview.** Save in BRIX, then
   check the live storefront rather than the theme editor preview.

If none of those is the problem, the issue is more likely a genuine error than a setting.
Email support@thebrix.io describing what you expected to happen and what happened
instead — that is the fastest route to a fix.

## Nothing appears on my storefront after saving changes

Confirm the BRIX app embed is turned on in your theme editor (Online Store → Themes →
Customize → App embeds). This is the single most common cause; settings can be saved
correctly in BRIX and still not appear until the embed is enabled.

## A feature works in the preview but not on the live store

Check whether the feature requires a paid plan (see the Features/Pricing documentation).
Features on the Free plan can be fully configured and previewed, but only publish live on
Starter and above.

## My coupon isn't showing in the Coupon Slider

Only *active* Shopify discount codes appear in the "Your Store Coupons" list. Confirm the
code's status and active dates in your Shopify discounts, then add it under Manage Coupons.

## Reward products aren't being added at the right tier

Double-check the tier's minimum spend/count and confirm reward products were selected for
that specific tier (each tier has its own product picker).

## Brix AI says I'm out of credits

You've used your plan's monthly AI credit allowance. Continued use is billed automatically
at your plan's per-credit overage rate. No action is needed unless you want to upgrade for
a larger included allowance.

## My analytics numbers look locked or blurred

Full analytics detail requires the Starter plan or above; the Free plan shows only headline
totals.
