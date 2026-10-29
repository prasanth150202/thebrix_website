<?php
/**
 * BRIX pre-built Q&A database.
 *
 * The classifier reads the merchant's question and replies with only the id
 * of the closest topic; the answer below is then returned verbatim. The model
 * never writes these words, which is what stops the curated path from going
 * off-message.
 *
 * Generated from the original answers.js so the wording is byte-identical.
 * Answers are nowdoc strings: no interpolation, so the "$9", "$29", "$59",
 * "$79" and "$99" in the pricing copy stay literal.
 */

declare(strict_types=1);

function brix_qa(): array
{
    static $qa = null;
    if ($qa !== null) {
        return $qa;
    }

    return $qa = [
    [
        'id'     => 1,
        'topic'  => "How to install BRIX / getting started / first steps",
        'answer' => <<<'BRIX_TXT'
Here's how to get started with BRIX:

1. Install the BRIX app from the Shopify App Store.
2. In your Shopify admin, go to Online Store → Themes → Customize.
3. Open App Embeds and turn on BRIX — this is the only manual step required.
4. Head back to the BRIX dashboard to configure your Cart Drawer, rewards, upsells, and more.

No code editing needed. Everything is configured through settings panels with a live preview.
BRIX_TXT,
    ],
    [
        'id'     => 2,
        'topic'  => "What is BRIX / what does BRIX do / overview",
        'answer' => <<<'BRIX_TXT'
BRIX is a Shopify app that helps merchants increase Average Order Value (AOV) by making the cart more persuasive — without writing a single line of code.

It includes:
- Smart Cart Drawer with rewards progress bar and upsells
- Coupon Slider to surface discount codes in the cart
- Frequently Bought Together (FBT) product recommendations
- Combo Forge bundle builder
- AI-powered Analytics
- Brix AI assistant to manage everything in plain English

All features are configured through a live-preview dashboard.
BRIX_TXT,
    ],
    [
        'id'     => 3,
        'topic'  => "How to enable app embed / theme editor / app not showing on store",
        'answer' => <<<'BRIX_TXT'
If BRIX features are not showing on your store, the most common fix is enabling the App Embed:

1. In Shopify admin, go to Online Store → Themes → Customize.
2. Click App Embeds in the left panel.
3. Toggle BRIX to ON.
4. Click Save.

This step is required before any BRIX feature can appear on your storefront. If it's already enabled and you're still seeing issues, check that your theme is an Online Store 2.0 theme — legacy themes are not supported.
BRIX_TXT,
    ],
    [
        'id'     => 4,
        'topic'  => "WooCommerce compatibility / does BRIX work with WooCommerce",
        'answer' => <<<'BRIX_TXT'
No. BRIX is a Shopify-only app and cannot be installed on WooCommerce.

If you're currently on WooCommerce and considering Shopify, we're happy to help. Reach out at support@thebrix.io.
BRIX_TXT,
    ],
    [
        'id'     => 5,
        'topic'  => "Non-Shopify platform compatibility / BigCommerce / Wix / Squarespace / Magento",
        'answer' => <<<'BRIX_TXT'
BRIX only works with Shopify stores. It is not available for BigCommerce, Wix, Squarespace, Magento, or any other platform.

For questions about switching to Shopify or anything else, email us at support@thebrix.io.
BRIX_TXT,
    ],
    [
        'id'     => 6,
        'topic'  => "Theme compatibility / which themes work / Online Store 2.0",
        'answer' => <<<'BRIX_TXT'
BRIX works with all Online Store 2.0 Shopify themes. This covers every modern theme on the Shopify Theme Store.

Legacy (pre-2.0) themes are not supported. To check your theme version, go to Shopify admin → Online Store → Themes. If your theme was published after 2021, it is almost certainly OS2.0 compatible.

For heavily customised themes, our team offers free setup assistance — email support@thebrix.io.
BRIX_TXT,
    ],
    [
        'id'     => 7,
        'topic'  => "Does BRIX slow down my store / page speed / Core Web Vitals",
        'answer' => <<<'BRIX_TXT'
No. BRIX is designed to have no impact on your store's page speed or Core Web Vitals.

The Cart Drawer and all BRIX widgets load asynchronously after your page renders. They don't replace your theme — they layer on top using Shopify's app embed system.
BRIX_TXT,
    ],
    [
        'id'     => 8,
        'topic'  => "Headless Shopify compatibility / custom storefront",
        'answer' => <<<'BRIX_TXT'
BRIX uses Shopify's standard app embed system, which is built for Online Store 2.0 themes. Headless or custom storefront setups may not be fully compatible.

Contact us at support@thebrix.io to discuss your specific setup and we'll advise on the best approach.
BRIX_TXT,
    ],
    [
        'id'     => 9,
        'topic'  => "Pricing overview / how much does BRIX cost / all plans",
        'answer' => <<<'BRIX_TXT'
BRIX has six plans, and every plan includes the same features:
- AI Cart Upsell
- FBT, AI Suggestion, Smart Recommendations
- Rewards Progress Bar
- Coupon Slider, Countdown Timer
- Bundle Builder, Packs
- COD Button + Prepaid Discount
- Mobile Swipe Checkout, AI Insights

What changes is how many orders and AI prompt credits you get each month:

 FREE — $0/month
- 50 orders/month
- 10 AI prompt credits to try Brix AI

 LITE — $9/month
- 100–200 orders/month
- 20 AI prompt credits for light tuning
- 14-day free trial

 STARTER — $29/month (most popular)
- 200–500 orders/month
- 30 AI prompt credits for regular tweaks
- 14-day free trial

 GROWTH — $59/month
- 500–1,000 orders/month
- 60 AI prompt credits for frequent edits
- 14-day free trial

 PRO — $79/month
- 1,000–2,000 orders/month
- 90 AI prompt credits for heavy AI use
- 14-day free trial

 ENTERPRISE — $99/month
- Unlimited orders
- Unlimited AI prompt credits
- 14-day free trial
BRIX_TXT,
    ],
    [
        'id'     => 10,
        'topic'  => "Free plan details / what is included in free",
        'answer' => <<<'BRIX_TXT'
The Free plan is free forever — no time limit, no credit card required.

What's included:
- AI Cart Upsell
- FBT, AI Suggestion, Smart Recommendations
- Rewards Progress Bar
- Coupon Slider, Countdown Timer
- Bundle Builder, Packs
- COD Button + Prepaid Discount
- Mobile Swipe Checkout, AI Insights
- 50 orders/month
- 10 AI prompt credits to try Brix AI

The paid plans don't unlock extra features. They give you more orders and more Brix AI credits each month.
BRIX_TXT,
    ],
    [
        'id'     => 11,
        'topic'  => "Lite plan details / \$9 plan / Starter plan details / \$29 plan features",
        'answer' => <<<'BRIX_TXT'
Lite costs $9/month and Starter costs $29/month. Both include a 14-day free trial.

LITE ($9/month)
- 100–200 orders/month
- 20 AI prompt credits for light tuning

STARTER ($29/month, most popular)
- 200–500 orders/month
- 30 AI prompt credits for regular tweaks

Both plans have the same features as every other plan, live on your storefront: AI Cart Upsell; FBT, AI Suggestion and Smart Recommendations; Rewards Progress Bar; Coupon Slider and Countdown Timer; Bundle Builder and Packs; COD Button + Prepaid Discount; Mobile Swipe Checkout and AI Insights.
BRIX_TXT,
    ],
    [
        'id'     => 12,
        'topic'  => "Growth plan details / \$59 plan / Pro plan details / \$79 plan / Enterprise plan details / \$99 plan / unlimited orders / high volume",
        'answer' => <<<'BRIX_TXT'
Growth costs $59/month, Pro costs $79/month and Enterprise costs $99/month. All three include a 14-day free trial.

GROWTH ($59/month)
- 500–1,000 orders/month
- 60 AI prompt credits for frequent edits

PRO ($79/month)
- 1,000–2,000 orders/month
- 90 AI prompt credits for heavy AI use

ENTERPRISE ($99/month)
- Unlimited orders
- Unlimited AI prompt credits

All three have the same features as every other plan (see the pricing overview for the full list). They are for stores with a higher order volume.
BRIX_TXT,
    ],
    [
        'id'     => 13,
        'topic'  => "Free trial / is there a trial period",
        'answer' => <<<'BRIX_TXT'
Yes! Every paid plan (Lite, Starter, Growth, Pro and Enterprise) includes a 14-day free trial — no credit card required upfront. You're billed monthly through Shopify after the trial ends.

The Free plan has no trial because it's free indefinitely — no time limit.
BRIX_TXT,
    ],
    [
        'id'     => 14,
        'topic'  => "Cancel subscription / cancel anytime / how to cancel",
        'answer' => <<<'BRIX_TXT'
Yes, you can cancel anytime from the Billing dashboard inside BRIX. There are no cancellation fees or lock-in periods.

If you uninstall BRIX, it removes itself cleanly from your theme with no leftover code. We recommend exporting your analytics data first.
BRIX_TXT,
    ],
    [
        'id'     => 15,
        'topic'  => "How to upgrade or downgrade plan / switch plans",
        'answer' => <<<'BRIX_TXT'
You can upgrade or downgrade your plan at any time from the Billing section inside your BRIX dashboard. Changes take effect immediately.
BRIX_TXT,
    ],
    [
        'id'     => 16,
        'topic'  => "Order limit / what happens if I exceed order limit / overage",
        'answer' => <<<'BRIX_TXT'
If you go over your monthly order allowance, your app is never blocked or paused. We'll email you to suggest the next plan, and we never switch your plan or charge you without your say-so.

Monthly order allowances:
- Free: 50 orders
- Lite: 100–200 orders
- Starter: 200–500 orders
- Growth: 500–1,000 orders
- Pro: 1,000–2,000 orders
- Enterprise: unlimited, so there is no order limit to go over
BRIX_TXT,
    ],
    [
        'id'     => 17,
        'topic'  => "Annual billing / yearly plan / discount for annual",
        'answer' => <<<'BRIX_TXT'
Our plans are billed monthly through Shopify: Lite is $9/month, Starter is $29/month, Growth is $59/month, Pro is $79/month and Enterprise is $99/month.

If you're interested in annual billing, email support@thebrix.io and we'll tell you what's available.
BRIX_TXT,
    ],
    [
        'id'     => 18,
        'topic'  => "What is the Cart Drawer / cart drawer overview",
        'answer' => <<<'BRIX_TXT'
The BRIX Cart Drawer replaces your theme's default cart with a fully-featured slide-out drawer that includes:

- Rewards Progress Bar — shows customers how close they are to free shipping, gifts, or discounts
- Smart Cart Upsell — suggests products to help close the gap to the next reward tier
- Tap-to-apply Coupon Slider — customers apply discount codes in one tap without leaving the cart
- Announcement Bar — custom message strip for promotions or shipping notes
- Auto-open on add to cart
- Full design control (colours, fonts, width, animation) with live preview
BRIX_TXT,
    ],
    [
        'id'     => 19,
        'topic'  => "How to customise the cart drawer / cart editor",
        'answer' => <<<'BRIX_TXT'
To customise your Cart Drawer:

1. In your BRIX dashboard, go to Cart Editor.
2. Settings are on the left, live preview on the right.
3. Toggle the drawer Active or Inactive from the top bar.
4. Customise design: width, border radius, shadow, animation.
5. Set header: title, close button, colours, border.
6. Enable body features: Announcement Bar, Rewards Progress Bar, Coupon Slider, Upsell Products, Empty Cart recommendations.
7. Customise the footer: checkout button colour, copy, custom CSS.
8. Click Save when the preview looks right.
BRIX_TXT,
    ],
    [
        'id'     => 20,
        'topic'  => "Auto-open cart / cart opens automatically on add to cart",
        'answer' => <<<'BRIX_TXT'
Yes. You can enable Auto-open Cart in the Cart Editor settings. When turned on, the BRIX Cart Drawer slides open automatically the moment a customer adds a product to their cart.
BRIX_TXT,
    ],
    [
        'id'     => 21,
        'topic'  => "Can I have multiple cart drawer designs / more than one cart drawer",
        'answer' => <<<'BRIX_TXT'
No. One Cart Drawer configuration applies storefront-wide. However, Combo Forge bundle pages each have their own separate design, so the bundle page experience can be customised independently.
BRIX_TXT,
    ],
    [
        'id'     => 22,
        'topic'  => "How to set up rewards / progress bar / reward tiers / free shipping threshold / gift threshold",
        'answer' => <<<'BRIX_TXT'
To set up Reward Tiers:

1. In your BRIX dashboard, open Cart Editor.
2. Enable the Rewards Progress Bar in the body features section.
3. Add tiers — for example:
   - $75: Free Shipping
   - $120: Free Gift
   - $180: 10% Off
4. The progress bar fills in real time and matches your theme automatically.
5. Click Save.

The bar shows customers exactly how much more they need to spend to unlock the next reward, which is one of the most effective AOV tactics.
BRIX_TXT,
    ],
    [
        'id'     => 23,
        'topic'  => "Can I set multiple reward tiers / how many tiers",
        'answer' => <<<'BRIX_TXT'
Yes, you can set multiple reward tiers — for example, free shipping at $75, a free gift at $120, and a percentage discount at $180. There is no hard limit on the number of tiers you can set.

Multiple tiers consistently outperform a single threshold because they give customers incremental milestones to aim for.
BRIX_TXT,
    ],
    [
        'id'     => 24,
        'topic'  => "Do reward products cost extra / reward product fees",
        'answer' => <<<'BRIX_TXT'
No. BRIX does not charge per reward given. You control which products are offered as rewards and at what spending threshold — there are no extra fees for this.
BRIX_TXT,
    ],
    [
        'id'     => 25,
        'topic'  => "What is FBT / Frequently Bought Together / product recommendations",
        'answer' => <<<'BRIX_TXT'
Frequently Bought Together (FBT) lets you recommend related products on product pages based on your real order history.

It works in three modes:
- Manual: You pick which products appear together
- Automatic: BRIX suggests related items based on your catalog
- AI-Powered: Smarter, personalised picks updated weekly from actual buying patterns

Customers can add the entire set to their cart in one click, with an optional bundle discount for taking all items.
BRIX_TXT,
    ],
    [
        'id'     => 26,
        'topic'  => "How to set up Frequently Bought Together / FBT setup guide",
        'answer' => <<<'BRIX_TXT'
To set up FBT:

1. In your BRIX dashboard, go to FBT in the left navigation.
2. Choose a layout: horizontal row, card grid, or featured single-product.
3. Pick your recommendation mode: Manual, Automatic, or AI-Powered.
4. Set the section title, button text, price visibility, and product limits, then click Save.
5. Click "Enable in theme editor" → in Shopify's Theme Editor, open App Embeds, turn on the FBT Widget from BRIX, and click Save.
6. Return to BRIX and refresh to confirm it's live.
BRIX_TXT,
    ],
    [
        'id'     => 27,
        'topic'  => "Difference between Cart Upsell and FBT / when does each trigger",
        'answer' => <<<'BRIX_TXT'
They serve different moments in the shopping journey:

- FBT (Frequently Bought Together): Appears on the product page. Recommends complementary products based on what page the shopper is viewing.

- Cart Upsell: Appears inside the Cart Drawer. Recommends a product based on what's already in the customer's cart — specifically the item most likely to close the gap to the next reward tier.

Both can run in AI mode or manual mode independently.
BRIX_TXT,
    ],
    [
        'id'     => 28,
        'topic'  => "What is the coupon slider / coupon banner / discount codes in cart",
        'answer' => <<<'BRIX_TXT'
The Coupon Slider is a swipeable row of coupon cards that can appear on your product pages and inside the Cart Drawer.

Customers tap a coupon to apply it instantly — no copy-pasting, no leaving the page to search for a discount code.

You can target specific coupons by product, collection, or customer tag, and add optional countdown timers for urgency.
BRIX_TXT,
    ],
    [
        'id'     => 29,
        'topic'  => "Does coupon slider create discount codes / how to add coupons",
        'answer' => <<<'BRIX_TXT'
No. The Coupon Slider displays discount codes that already exist in your Shopify admin. It does not generate discount logic itself.

To add a coupon: first create the discount code in Shopify (Discounts section), then go to BRIX → Coupon Banner → Coupon Selection and choose the codes you want to feature.

Brix AI can create the Shopify discount code for you if you ask it to.
BRIX_TXT,
    ],
    [
        'id'     => 30,
        'topic'  => "How to set up coupon banner / coupon slider setup",
        'answer' => <<<'BRIX_TXT'
To set up the Coupon Banner:

1. In your BRIX dashboard, go to Coupon Banner and toggle it to Active.
2. Choose a template: Classic Banner, Minimal Card, or Bold & Vibrant.
3. Open Coupon Selection and choose the active Shopify discount codes to display.
4. Set Display Condition: all product pages, specific products, collections, or product tags.
5. Customise heading, subtext, colours, button style, and optionally add a Countdown Timer.
6. Choose layout: list, carousel, or grid.
7. Set placement: above or below the Add to Cart button.
8. Click Save.
BRIX_TXT,
    ],
    [
        'id'     => 31,
        'topic'  => "Can shoppers stack multiple coupons / coupon stacking",
        'answer' => <<<'BRIX_TXT'
Coupon stacking follows your existing Shopify discount settings. BRIX displays and applies the codes, but it doesn't change Shopify's own discount-combination rules.

If you want to allow stacking, you'd configure that inside Shopify's Discounts section.
BRIX_TXT,
    ],
    [
        'id'     => 32,
        'topic'  => "What is Combo Forge / bundle builder / bundles",
        'answer' => <<<'BRIX_TXT'
Combo Forge is BRIX's bundle builder. It lets you create dedicated bundle pages where customers can mix and match products to build their own set.

Features:
- Collection rules: quantity breaks, percentage or fixed-amount discounts
- Three layout styles: Guided Architect, Velocity Stream, Editorial Split
- Inventory-aware: out-of-stock items automatically drop out of bundles
- Brix AI can write bundle copy (titles, descriptions, step names) for you

Combo Forge is included on every plan, Free too.
BRIX_TXT,
    ],
    [
        'id'     => 33,
        'topic'  => "How to create a bundle / how to build a Combo page",
        'answer' => <<<'BRIX_TXT'
To create a bundle with Combo Forge:

1. In your BRIX dashboard, open Build a Combo and click "Create a Bundle".
2. Choose a layout: Guided Architect (step-by-step), Velocity Stream (fast browsing), or Editorial Split (premium look).
3. Add steps/categories and select products from your Shopify catalog.
4. Attach a discount (percentage or fixed amount).
5. Customise colours, fonts, buttons, and header content. Use the Brix AI sparkle to generate copy.
6. Click Publish — BRIX automatically creates a dedicated Shopify page.
7. Track performance in BRIX Analytics.
BRIX_TXT,
    ],
    [
        'id'     => 34,
        'topic'  => "How many bundles can I create / bundle limit per plan",
        'answer' => <<<'BRIX_TXT'
Combo Forge is included on every plan, Free too, so your plan doesn't change which bundle features you get. Plans differ only in monthly orders and Brix AI credits.

If you need to know about a specific bundle volume, email support@thebrix.io.
BRIX_TXT,
    ],
    [
        'id'     => 35,
        'topic'  => "What is Brix AI / AI assistant / what can AI do",
        'answer' => <<<'BRIX_TXT'
Brix AI is your in-dashboard AI assistant. You can talk to it in plain English to:

- Ask how features work or get step-by-step help
- Write copy: coupon banner text, bundle descriptions, FBT section headings
- Create Shopify discount codes
- Get product recommendation suggestions
- Tune reward tier amounts based on your live cart data
- Review and approve or undo any changes it proposes

Brix AI always asks for your confirmation before creating or changing anything in your store.
BRIX_TXT,
    ],
    [
        'id'     => 36,
        'topic'  => "Does Brix AI act without my approval / is AI safe",
        'answer' => <<<'BRIX_TXT'
No. Brix AI never acts without your approval. It always shows you what it plans to do and waits for your confirmation before creating or changing anything in your store.

Every change is logged so you can review, approve, or undo anything at any time.
BRIX_TXT,
    ],
    [
        'id'     => 37,
        'topic'  => "How many AI credits do I get / Brix AI credit limit",
        'answer' => <<<'BRIX_TXT'
AI prompt credits per plan per month:
- Free: 10 credits, to try Brix AI
- Lite: 20 credits, for light tuning
- Starter: 30 credits, for regular tweaks
- Growth: 60 credits, for frequent edits
- Pro: 90 credits, for heavy AI use
- Enterprise: unlimited credits

Each credit covers one AI action: writing a piece of copy, creating a rule, generating a recommendation, answering a question, etc. For more credits each month, move up a plan from the BRIX Billing dashboard.
BRIX_TXT,
    ],
    [
        'id'     => 38,
        'topic'  => "How to use Brix AI / open AI panel in dashboard",
        'answer' => <<<'BRIX_TXT'
To use Brix AI inside the BRIX dashboard:

1. Click the Brix AI button in the bottom-right corner of any page in the app.
2. Type your request in plain English — ask about settings, get feature explanations, generate copy, or request changes.
3. In the Bundle Builder, click the sparkle icon next to any field to instantly generate titles, descriptions, or step names.
4. Review any suggestion and click to apply or edit it.
BRIX_TXT,
    ],
    [
        'id'     => 39,
        'topic'  => "What analytics does BRIX offer / analytics overview",
        'answer' => <<<'BRIX_TXT'
BRIX Analytics shows you exactly which features are driving revenue:

- Per-feature revenue: how much the progress bar, bundles, upsells, and coupons each earned
- Tier funnel: how many carts reach each reward tier and where they drop off
- Upsell attach rate: percentage of carts that added an upsell product
- AOV trend over time
- AI insight cards: specific suggestions with projected revenue impact
- Weekly email digest: your AOV story in three bullets every Monday

Every analytics view is available on every plan.
BRIX_TXT,
    ],
    [
        'id'     => 40,
        'topic'  => "Can I export analytics / download analytics data",
        'answer' => <<<'BRIX_TXT'
Analytics are viewable in-dashboard with date-range filtering and comparison views. Currently, analytics are available within the BRIX dashboard.

For export requirements or specific reporting needs, contact us at support@thebrix.io.
BRIX_TXT,
    ],
    [
        'id'     => 41,
        'topic'  => "What is the tier funnel / funnel analytics",
        'answer' => <<<'BRIX_TXT'
The Tier Funnel shows how many customer carts reach each of your reward tiers, and where carts drop off without unlocking the next reward.

For example, if you have tiers at $75 (free shipping) and $120 (free gift), the funnel shows the percentage of carts that unlocked each tier. This helps you spot gaps — if many carts sit just below $75, you might lower that first tier or add a smaller intermediate milestone.
BRIX_TXT,
    ],
    [
        'id'     => 42,
        'topic'  => "How to remove watermark / powered by BRIX / remove branding",
        'answer' => <<<'BRIX_TXT'
The "Powered by BRIX" watermark can be removed on any plan.

To remove it, go to footer settings in the Cart Editor and toggle off the watermark.
BRIX_TXT,
    ],
    [
        'id'     => 43,
        'topic'  => "Do I need a developer / do I need to edit theme code",
        'answer' => <<<'BRIX_TXT'
No. BRIX requires zero code editing or developer involvement. Every feature is configured through visual settings panels with a live preview inside the BRIX dashboard.

The only manual step is enabling the BRIX app embed once in Shopify's Theme Editor — which takes about 30 seconds and requires no coding knowledge.
BRIX_TXT,
    ],
    [
        'id'     => 44,
        'topic'  => "Custom CSS / advanced theme customisation",
        'answer' => <<<'BRIX_TXT'
Yes, BRIX includes a Custom CSS editor for pixel-perfect control over the cart drawer and widget appearance. This is available on every plan.

You can access it in the Cart Editor → Footer & Advanced Options.
BRIX_TXT,
    ],
    [
        'id'     => 45,
        'topic'  => "Does BRIX work with subscription apps / recurring orders / Recharge / Bold",
        'answer' => <<<'BRIX_TXT'
Yes. BRIX is compatible with Shopify Markets, multi-currency, and major subscription apps. Reward tiers automatically convert to each shopper's local currency.

If you use a specific subscription app and want to confirm compatibility, email support@thebrix.io with your setup details.
BRIX_TXT,
    ],
    [
        'id'     => 46,
        'topic'  => "Does BRIX work with multi-currency / Shopify Markets / international stores",
        'answer' => <<<'BRIX_TXT'
Yes. BRIX supports Shopify Markets and multi-currency. Reward tiers and prices automatically reflect each shopper's local currency as configured in your Shopify store.
BRIX_TXT,
    ],
    [
        'id'     => 47,
        'topic'  => "How to uninstall BRIX / what happens when I uninstall / remove BRIX",
        'answer' => <<<'BRIX_TXT'
To uninstall BRIX, go to Shopify Admin → Apps and remove it like any other app.

BRIX removes itself cleanly — no leftover code remains in your theme. We recommend:
1. Exporting your analytics data first
2. Noting any discount codes created through Brix AI that you want to keep

After uninstall, your store returns exactly to how it was before BRIX.
BRIX_TXT,
    ],
    [
        'id'     => 48,
        'topic'  => "What results do merchants get / case studies / AOV improvement / success stories",
        'answer' => <<<'BRIX_TXT'
Here are real results from BRIX merchants:

- DTC Brand: AOV increased from ₹1,250 to ₹1,540 (+23%). Monthly revenue up ₹3.9 Lakhs — without increasing ad spend.
- Fashion Brand: AOV up from ₹1,480 to ₹1,860 (+26%). Cross-sell click rate jumped from 9% to 27%.
- Rewards Bar campaign: Cart value up from ₹1,320 to ₹1,620 (+23%). Reward unlock rate went from 19% to 47%.
- FMCG Brand: Monthly revenue doubled from ₹7 Lakhs to ₹14 Lakhs.

The combination of FBT + rewards bar + cart upsells consistently delivers 20–26% AOV improvements.
BRIX_TXT,
    ],
    [
        'id'     => 49,
        'topic'  => "What features work on the free plan / free plan limitations / preview mode",
        'answer' => <<<'BRIX_TXT'
Every feature works on the Free plan, live on your storefront. Nothing is preview-only.

Features live on Free:
- Cart Drawer
- Announcement Bar
- Empty Cart customisation
- Rewards Progress Bar
- Cart Upsell
- FBT (Frequently Bought Together)
- Coupon Slider
- Countdown Timers
- Combo Forge

The Free plan's limits are 50 orders/month and 10 AI prompt credits/month. Paid plans raise those two limits; they don't unlock extra features.
BRIX_TXT,
    ],
    [
        'id'     => 50,
        'topic'  => "How to contact support / support email / get help",
        'answer' => <<<'BRIX_TXT'
You can reach the BRIX support team at:

support@thebrix.io

24/7 AI support is available inside the BRIX dashboard on every plan. For urgent issues, email is the fastest route to a human.
BRIX_TXT,
    ],
    [
        'id'     => 51,
        'topic'  => "Response time / how fast is support / SLA",
        'answer' => <<<'BRIX_TXT'
The BRIX team aims to respond to all support emails within one business day. Priority email support is included on every plan.

For general questions, the Brix AI assistant inside the dashboard is available 24/7 and can answer most product questions instantly.
BRIX_TXT,
    ],
    [
        'id'     => 52,
        'topic'  => "Multi-store support / can I use one BRIX account for multiple stores",
        'answer' => <<<'BRIX_TXT'
Yes. A single BRIX account can run multiple storefronts, on any plan.

For details on multi-store setup, email support@thebrix.io.
BRIX_TXT,
    ],
    [
        'id'     => 53,
        'topic'  => "What currency are prices shown in / INR / USD / currency",
        'answer' => <<<'BRIX_TXT'
The BRIX dashboard reflects your store's configured currency. Documentation examples use both ₹ (INR) and $ (USD) for illustration, but your dashboard will always show prices in your store's currency.
BRIX_TXT,
    ],
    [
        'id'     => 54,
        'topic'  => "How does the confetti effect work / confetti animation",
        'answer' => <<<'BRIX_TXT'
Confetti is a celebratory animation that fires in the cart when a customer unlocks a reward tier. It's a small but effective engagement moment that reinforces the reward.

You can enable or disable it in the Cart Editor under body features. It's available on every plan.
BRIX_TXT,
    ],
    [
        'id'     => 55,
        'topic'  => "Mobile checkout / swipe to checkout / mobile experience",
        'answer' => <<<'BRIX_TXT'
BRIX includes a Mobile Swipe Checkout feature — a swipe gesture on mobile that takes shoppers directly to the payment screen, reducing friction on mobile devices.

This is available on every plan. You can enable it in the Cart Editor settings.
BRIX_TXT,
    ],
    [
        'id'     => 56,
        'topic'  => "Empty cart customisation / empty cart page / empty cart upsell",
        'answer' => <<<'BRIX_TXT'
Yes. BRIX lets you customise what customers see when their cart is empty. Instead of a dead-end message, you can show featured products and promotions to keep shoppers engaged.

This is configured in the Cart Editor under the Empty Cart tab and is available on all plans.
BRIX_TXT,
    ],
    [
        'id'     => 57,
        'topic'  => "Announcement bar in cart / cart announcement strip",
        'answer' => <<<'BRIX_TXT'
The Announcement Bar is a customisable message strip inside the Cart Drawer. Use it to communicate shipping information, active promotions, deadlines, or any other message you want every shopper to see before checkout.

It's available on all plans and configured in the Cart Editor.
BRIX_TXT,
    ],
    [
        'id'     => 58,
        'topic'  => "Countdown timer / urgency / timer on coupons",
        'answer' => <<<'BRIX_TXT'
Yes. You can add countdown timers to your Coupon Banner offers to create urgency. When enabled, a visible clock counts down next to the offer.

Countdown timers are available on every plan and are configured inside the Coupon Banner settings.
BRIX_TXT,
    ],
    ];
}

/**
 * The topic list handed to the classifier, as "1. <topic>" lines.
 */
function brix_build_topic_list(): string
{
    $lines = [];
    foreach (brix_qa() as $entry) {
        $lines[] = $entry['id'] . '. ' . $entry['topic'];
    }

    return implode("\n", $lines);
}

/**
 * Look up a curated answer by id. Null when the id matches nothing, which
 * includes the 0 the classifier returns for "no topic fits".
 */
function brix_get_answer(int $id): ?array
{
    foreach (brix_qa() as $entry) {
        if ($entry['id'] === $id) {
            return $entry;
        }
    }

    return null;
}
