/* ============================================================
   BRIX attribution: UTM capture, persistence, CTA decoration
   Loaded in <head> so a visit is recorded before the visitor
   can navigate away.
   ============================================================ */

(function brixAttribution() {

  const STORE_URL = 'https://apps.shopify.com/thebrix-io';

  /* This page loads GTM and gtag.js against the same measurement ID, so GTM
     claims the ID first and the inline gtag('config') registers no destination.
     Bare gtag('event') calls then have nowhere to route and are silently
     dropped, so every event has to name its destination explicitly */
  const MEASUREMENT_ID = 'G-23RTZ99F2K';

  /* Mirrors the params hardcoded into every CTA in the HTML. utm_id stays
     constant on every outgoing link so Shopify can always filter website
     installs; the other three are corrected once we know the real campaign */
  const BASE = {
    utm_source: 'Brix-Website',
    utm_medium: 'Organic',
    utm_campaign: 'Website_Tracking',
    utm_id: 'Website'
  };

  const UTM_KEYS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'utm_id'];
  const CLICK_IDS = ['gclid', 'fbclid', 'ttclid', 'msclkid', 'li_fat_id'];
  const TRACKED = UTM_KEYS.concat(CLICK_IDS);

  const FIRST_KEY = 'brix_attr_first';
  const LAST_KEY = 'brix_attr_last';
  const SESSION_KEY = 'brix_attr_session';
  const MAX_AGE = 90 * 24 * 60 * 60;

  const CTA_SELECTOR = 'a[href*="apps.shopify.com/thebrix-io"]';

  /* ---------- storage: cookie with a localStorage mirror ---------- */

  /* Safari's ITP caps JS-set cookies at 7 days no matter what expiry we ask
     for, so localStorage carries the value for the rest of the 90 days */

  const writeCookie = (name, value) => {
    try {
      document.cookie = name + '=' + encodeURIComponent(value) +
        ';path=/;max-age=' + MAX_AGE + ';SameSite=Lax' +
        (location.protocol === 'https:' ? ';Secure' : '');
    } catch (e) { /* cookies blocked */ }
  };

  const readCookie = name => {
    const m = document.cookie.match('(?:^|; )' + name + '=([^;]*)');
    return m ? decodeURIComponent(m[1]) : null;
  };

  const save = (key, obj) => {
    const raw = JSON.stringify(obj);
    writeCookie(key, raw);
    try { localStorage.setItem(key, raw); } catch (e) { /* storage blocked */ }
  };

  const load = key => {
    let raw = readCookie(key);
    if (!raw) {
      try { raw = localStorage.getItem(key); } catch (e) { /* storage blocked */ }
    }
    if (!raw) return null;
    try { return JSON.parse(raw); } catch (e) { return null; }
  };

  /* ---------- capture the current visit ---------- */

  const currentTouch = () => {
    const q = new URLSearchParams(location.search);
    const touch = {};
    let found = false;

    TRACKED.forEach(k => {
      const v = q.get(k);
      if (v) { touch[k] = v; found = true; }
    });

    if (!found) return null;

    touch.landing_page = location.pathname;
    touch.referrer = document.referrer || '';
    touch.ts = new Date().toISOString();
    return touch;
  };

  const touch = currentTouch();
  let first = load(FIRST_KEY);
  let last = load(LAST_KEY);

  if (touch) {
    if (!first) {
      first = touch;
      save(FIRST_KEY, first);
    }
    last = touch;
    save(LAST_KEY, last);
  }

  /* Last-touch wins; fall back to first-touch when this visit arrived with
     nothing, so a direct return visit still credits the campaign that found
     them rather than reporting Organic */
  const attr = last || first || null;

  /* ---------- reporting: dataLayer for GTM, gtag for GA4 ---------- */

  const report = payload => {
    /* snapshot first: GTM mutates the pushed object in place, and those
       internals would otherwise leak into the gtag parameters */
    const { event, ...params } = payload;

    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push(payload);

    if (typeof window.gtag === 'function') {
      window.gtag('event', event, { ...params, send_to: MEASUREMENT_ID });
    }
  };

  /* fires on every arrival, campaign or not, guarded against refreshes */
  const reportLanding = () => {
    try {
      if (sessionStorage.getItem(SESSION_KEY)) return;
      sessionStorage.setItem(SESSION_KEY, '1');
    } catch (e) { /* storage blocked: report anyway */ }

    const payload = {
      event: 'brix_campaign_landing',
      landing_page: location.pathname,
      referrer: document.referrer || '',
      has_campaign: attr ? 'true' : 'false'
    };

    TRACKED.forEach(k => {
      if (attr && attr[k]) payload[k] = attr[k];
      if (first && first[k]) payload['first_' + k] = first[k];
    });

    report(payload);
  };

  /* ---------- outgoing App Store links ---------- */

  /* Google and Microsoft stamp their click ID on paid clicks only, so cpc is
     safe to infer. fbclid rides on organic Facebook and Instagram links too,
     so it can only tell us the source, never that the click was paid */
  const CLICK_ID_HINTS = [
    ['gclid',     { utm_source: 'google',   utm_medium: 'cpc' }],
    ['msclkid',   { utm_source: 'bing',     utm_medium: 'cpc' }],
    ['ttclid',    { utm_source: 'tiktok',   utm_medium: 'paid_social' }],
    ['li_fat_id', { utm_source: 'linkedin', utm_medium: 'paid_social' }],
    ['fbclid',    { utm_source: 'facebook', utm_medium: 'social' }]
  ];

  /* An explicit utm_* param always wins. The hint only fills a gap that would
     otherwise misreport an ad click as Organic, which happens whenever a
     platform auto-tags a click without any utm_* params on it */
  const inferred = () => {
    if (!attr) return {};
    const hit = CLICK_ID_HINTS.find(([id]) => attr[id]);
    return hit ? hit[1] : {};
  };

  const resolvedUrl = () => {
    const q = new URLSearchParams();
    const guess = inferred();

    q.set('utm_source', attr?.utm_source || guess.utm_source || BASE.utm_source);
    q.set('utm_medium', attr?.utm_medium || guess.utm_medium || BASE.utm_medium);
    q.set('utm_campaign', attr?.utm_campaign || BASE.utm_campaign);
    q.set('utm_id', BASE.utm_id);

    if (attr) {
      if (attr.utm_term) q.set('utm_term', attr.utm_term);
      if (attr.utm_content) q.set('utm_content', attr.utm_content);
      CLICK_IDS.forEach(k => { if (attr[k]) q.set(k, attr[k]); });
    }

    return STORE_URL + '?' + q.toString();
  };

  /* rewritten on load rather than on click alone, so a copied or
     middle-clicked link carries the same attribution */
  const decorate = () => {
    const url = resolvedUrl();
    document.querySelectorAll(CTA_SELECTOR).forEach(a => a.setAttribute('href', url));
  };

  const placement = a => a.closest('[id]')?.id || 'unknown';

  document.addEventListener('click', e => {
    const a = e.target?.closest?.(CTA_SELECTOR);
    if (!a) return;

    a.setAttribute('href', resolvedUrl());

    report({
      event: 'brix_app_store_click',
      click_page: location.pathname,
      click_placement: placement(a),
      click_text: (a.textContent || '').trim().slice(0, 60),
      click_destination: a.getAttribute('href')
    });
  }, true);

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', decorate);
  } else {
    decorate();
  }

  reportLanding();

  /* read-only handle for QA in the console */
  window.brixAttr = { first, last, resolved: resolvedUrl };

})();
