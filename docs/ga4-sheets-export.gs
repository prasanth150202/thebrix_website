/**
 * BRIX — GA4 permanent archive in Google Sheets
 *
 * Every tab is append-only. Rows are upserted by their dimension values, so a
 * day that has already settled is never rewritten and never deleted — the
 * spreadsheet keeps data long after GA4's own retention window has discarded it.
 *
 * Website and App Store data live in separate tabs, prefixed "Web ·" and "Store ·".
 *
 * Setup: see docs/ga4-sheets-export.md
 * Requires the "Google Analytics Data API" advanced service (AnalyticsData,
 * v1beta). The access diagnostic additionally needs "Google Analytics Admin API"
 * (AnalyticsAdmin, v1beta).
 */

// ---------------------------------------------------------------------------
// Configuration
// ---------------------------------------------------------------------------

// Numeric GA4 Property IDs, NOT measurement IDs like G-23RTZ99F2K.
const PROPERTIES = {
  website:  { id: '546417307', label: 'Web',   name: 'Brix Site' },
  appStore: { id: '547828723', label: 'Store', name: 'App Store' },
};

const CONFIG = {
  // Hourly runs only re-pull the last few days. Anything older is already in the
  // archive and is deliberately left untouched.
  refreshWindowDays: 3,

  // One-off seeding window. GA4 can only return what its retention setting has
  // kept, so this is an upper bound, not a promise.
  backfillDays: 400,
};

const EVENTS = {
  landing: 'brix_campaign_landing',
  appStoreClick: 'brix_app_store_click',
};

// Separator for composite grouping keys. Written as an escape, never as a
// literal control character, so the file survives copy-paste into the editor.
const KEY_SEP = '\u001F';

// The Data API rejects requests above these limits. Note that a dimension named
// only in dimensionFilter still counts against MAX_DIMENSIONS, so an
// event-filtered report effectively gets 8 slots. See dimensionBudget_.
const MAX_DIMENSIONS = 9;
const MAX_METRICS = 10;

// Column appended to every archive tab. Sits after the metrics so it can never
// disturb the key, which is built from the leading dimension columns.
const UPDATED_AT = 'Updated At';

// Dashboard reporting windows.
const DASH_WINDOW = 28;
const DASH_TREND = 30;

// ---------------------------------------------------------------------------
// Lead journey configuration
// ---------------------------------------------------------------------------

// Tab the Smartlead webhook writes into. Read only; never modified here.
const SMARTLEAD_TAB = 'Smartlead Events';

// Smartlead webhook event names. Smartlead can send all of these; a campaign
// only receives the ones ticked in its webhook config, so absent types simply
// leave their columns at zero rather than breaking anything.
const SL = {
  SENT: 'EMAIL_SENT',
  OPEN: 'EMAIL_OPEN',
  CLICK: 'EMAIL_LINK_CLICK',
  REPLY: 'EMAIL_REPLY',
  UNTRACKED_REPLY: 'UNTRACKED_REPLIES',
  BOUNCE: 'EMAIL_BOUNCE',
  UNSUB: 'LEAD_UNSUBSCRIBED',
  CATEGORY: 'LEAD_CATEGORY_UPDATED',
};

// Website event meaning "left for the Shopify listing".
const WEB_STORE_CLICK_EVENT = 'brix_app_store_click';

// Shopify App Store listing events meaning install intent. "Add App button" is
// the listing's own install button; add_to_cart is Shopify's instrumentation of
// the same action, so either counts.
const STORE_INSTALL_EVENTS = ['Add App button', 'add_to_cart'];

// Journey ladder. Bounced is a terminal branch rather than a rung: a bounced
// address never had the chance to open, so counting it as "sent, never opened"
// would quietly depress the open rate.
const STAGE_NAMES = {
  0: 'Bounced',
  1: 'Sent',
  2: 'Opened',
  3: 'Clicked',
  4: 'Visited website',
  5: 'Reached listing',
  6: 'Clicked Add App',
};

// Tabs written by the pre-archive version of this script. They are no longer
// updated by anything, so they sit there going stale. deleteLegacyTabs() removes
// them, after showing you exactly what it is about to delete.
const LEGACY_TABS = [
  'Campaign Landings',
  'App Store Clicks',
  'First Touch',
  'Daily Summary',
  'Website Daily Summary',
  'App Store Traffic',
  'App Store Events',
  'Channel Performance',
  'Page Performance',
  'Audience',
];

// ---------------------------------------------------------------------------
// Report definitions
// ---------------------------------------------------------------------------
//
// Any dimension or metric a property does not support is dropped at runtime
// rather than failing the whole report, so unregistered custom dimensions are
// simply skipped and appear automatically once you register them in GA4.
//
// IMPORTANT — which events carry which parameters (see js/utm.js):
//
//   brix_campaign_landing  → utm_*, first_utm_*, has_campaign, landing_page
//   brix_app_store_click   → click_page, click_placement, click_text ONLY
//
// The click event carries NO campaign parameters, so click attribution uses
// GA4's built-in session dimensions instead of customEvent: ones.

const REPORTS = [
  // --- Website -------------------------------------------------------------
  {
    sheet: 'Landings Last Touch',
    property: 'website',
    event: EVENTS.landing,
    dimensions: [
      ['date', 'Date'],
      ['customEvent:utm_source', 'Source'],
      ['customEvent:utm_medium', 'Medium'],
      ['customEvent:utm_campaign', 'Campaign'],
      ['customEvent:utm_id', 'Campaign ID'],
      ['customEvent:utm_content', 'Content'],
      ['customEvent:utm_term', 'Term'],
      // has_campaign is deliberately omitted: the eventName filter consumes one
      // of the 9 dimension slots, and this column is fully derivable from
      // whether Source is (not set).
      ['customEvent:landing_page', 'Landing Page'],
    ],
    metrics: [
      ['eventCount', 'Landings'],
      ['totalUsers', 'Users'],
    ],
  },
  {
    sheet: 'Landings First Touch',
    property: 'website',
    event: EVENTS.landing,
    dimensions: [
      ['date', 'Date'],
      ['customEvent:first_utm_source', 'First Source'],
      ['customEvent:first_utm_medium', 'First Medium'],
      ['customEvent:first_utm_campaign', 'First Campaign'],
      ['customEvent:first_utm_id', 'First Campaign ID'],
      ['customEvent:first_utm_content', 'First Content'],
      ['customEvent:first_utm_term', 'First Term'],
      ['customEvent:landing_page', 'Landing Page'],
    ],
    metrics: [
      ['eventCount', 'Landings'],
      ['totalUsers', 'Users'],
    ],
  },
  {
    sheet: 'Store Clicks',
    property: 'website',
    event: EVENTS.appStoreClick,
    dimensions: [
      ['date', 'Date'],
      ['customEvent:click_page', 'Click Page'],
      ['customEvent:click_placement', 'Click Placement'],
      ['customEvent:click_text', 'Click Text'],
      ['sessionDefaultChannelGroup', 'Channel'],
      ['sessionSource', 'Source'],
      ['sessionMedium', 'Medium'],
      ['sessionCampaignName', 'Campaign'],
      // Device omitted for the same reason as above; it is already archived per
      // day in Web · Channels and Web · Audience.
    ],
    metrics: [
      ['eventCount', 'Clicks'],
      ['totalUsers', 'Users'],
    ],
  },
  {
    // Feeds the funnel tabs. Both events measured against the same session
    // attribution, so landings and clicks are directly comparable.
    sheet: 'Funnel Source',
    property: 'website',
    eventNameIn: [EVENTS.landing, EVENTS.appStoreClick],
    dimensions: [
      ['date', 'Date'],
      ['sessionDefaultChannelGroup', 'Channel'],
      ['sessionSource', 'Source'],
      ['sessionMedium', 'Medium'],
      ['sessionCampaignName', 'Campaign'],
      ['eventName', 'Event'],
    ],
    metrics: [
      ['eventCount', 'Event Count'],
      ['totalUsers', 'Users'],
    ],
  },
  {
    // Per-lead attribution. sessionManualTerm is the built-in session-scoped
    // dimension derived from utm_term, so it is attached to EVERY event in the
    // session, including brix_app_store_click which carries no parameters of its
    // own. No custom dimension registration needed on either property.
    sheet: 'Lead Sessions',
    property: 'website',
    dimensions: [
      ['date', 'Date'],
      ['sessionManualTerm', 'Token'],
      ['sessionManualCampaignName', 'Campaign'],
      ['sessionManualSource', 'Source'],
      ['sessionManualMedium', 'Medium'],
      ['eventName', 'Event'],
    ],
    metrics: [
      ['eventCount', 'Event Count'],
      ['sessions', 'Sessions'],
    ],
  },
  {
    // No event filter: every event the property collects, not just the BRIX two.
    sheet: 'Events Daily',
    property: 'website',
    dimensions: [
      ['date', 'Date'],
      ['eventName', 'Event'],
    ],
    metrics: [
      ['eventCount', 'Event Count'],
      ['totalUsers', 'Users'],
      ['sessions', 'Sessions'],
    ],
  },
  {
    sheet: 'Channels',
    property: 'website',
    dimensions: [
      ['date', 'Date'],
      ['sessionDefaultChannelGroup', 'Channel'],
      ['sessionSource', 'Source'],
      ['sessionMedium', 'Medium'],
      ['sessionCampaignName', 'Campaign'],
      ['deviceCategory', 'Device'],
    ],
    metrics: [
      ['sessions', 'Sessions'],
      ['engagedSessions', 'Engaged Sessions'],
      ['engagementRate', 'Engagement Rate'],
      ['averageSessionDuration', 'Avg Duration'],
      ['newUsers', 'New Users'],
      ['totalUsers', 'Users'],
      ['keyEvents', 'Key Events'],
      ['screenPageViews', 'Views'],
    ],
  },
  {
    sheet: 'Pages',
    property: 'website',
    dimensions: [
      ['date', 'Date'],
      ['pagePath', 'Page'],
      ['deviceCategory', 'Device'],
    ],
    metrics: [
      ['screenPageViews', 'Views'],
      ['sessions', 'Sessions'],
      ['userEngagementDuration', 'Engagement Seconds'],
      ['bounceRate', 'Bounce Rate'],
      ['keyEvents', 'Key Events'],
    ],
  },
  {
    sheet: 'Audience',
    property: 'website',
    dimensions: [
      ['date', 'Date'],
      ['country', 'Country'],
      ['region', 'Region'],
      ['deviceCategory', 'Device'],
      ['operatingSystem', 'OS'],
      ['browser', 'Browser'],
      ['newVsReturning', 'New vs Returning'],
    ],
    metrics: [
      ['sessions', 'Sessions'],
      ['totalUsers', 'Users'],
      ['newUsers', 'New Users'],
      ['keyEvents', 'Key Events'],
    ],
  },

  // --- App Store -----------------------------------------------------------
  // Built-in dimensions only. Whatever custom dimensions this property has are
  // picked up automatically by the auto-discovered "Custom N" tabs below.
  {
    sheet: 'Traffic',
    property: 'appStore',
    dimensions: [
      ['date', 'Date'],
      ['sessionDefaultChannelGroup', 'Channel'],
      ['sessionSource', 'Source'],
      ['sessionMedium', 'Medium'],
      ['sessionCampaignName', 'Campaign'],
      ['deviceCategory', 'Device'],
    ],
    metrics: [
      ['sessions', 'Sessions'],
      ['totalUsers', 'Users'],
      ['newUsers', 'New Users'],
      ['engagedSessions', 'Engaged Sessions'],
      ['engagementRate', 'Engagement Rate'],
      ['keyEvents', 'Key Events'],
      ['eventCount', 'Event Count'],
    ],
  },
  {
    // Catches the mail-straight-to-listing path, and Add App clicks per lead.
    sheet: 'Lead Sessions',
    property: 'appStore',
    dimensions: [
      ['date', 'Date'],
      ['sessionManualTerm', 'Token'],
      ['sessionManualCampaignName', 'Campaign'],
      ['sessionManualSource', 'Source'],
      ['sessionManualMedium', 'Medium'],
      ['eventName', 'Event'],
    ],
    metrics: [
      ['eventCount', 'Event Count'],
      ['sessions', 'Sessions'],
    ],
  },
  {
    sheet: 'Events',
    property: 'appStore',
    dimensions: [
      ['date', 'Date'],
      ['eventName', 'Event'],
    ],
    metrics: [
      ['eventCount', 'Event Count'],
      ['totalUsers', 'Users'],
    ],
  },
  {
    sheet: 'Pages',
    property: 'appStore',
    dimensions: [
      ['date', 'Date'],
      ['pagePath', 'Page'],
      ['deviceCategory', 'Device'],
    ],
    metrics: [
      ['screenPageViews', 'Views'],
      ['sessions', 'Sessions'],
    ],
  },
  {
    sheet: 'Audience',
    property: 'appStore',
    dimensions: [
      ['date', 'Date'],
      ['country', 'Country'],
      ['deviceCategory', 'Device'],
      ['newVsReturning', 'New vs Returning'],
    ],
    metrics: [
      ['sessions', 'Sessions'],
      ['totalUsers', 'Users'],
      ['keyEvents', 'Key Events'],
    ],
  },
];

// ---------------------------------------------------------------------------
// Entry points
// ---------------------------------------------------------------------------

function onOpen() {
  SpreadsheetApp.getUi()
    .createMenu('GA4')
    .addItem('Refresh now (recent days)', 'refreshArchive')
    .addItem('Backfill history (one-off)', 'backfillArchive')
    .addSeparator()
    .addItem('Install hourly trigger', 'installHourlyTrigger')
    .addItem('Remove triggers', 'removeTriggers')
    .addSeparator()
    .addItem('List available fields', 'listAvailableFields')
    .addItem('Diagnose access problems', 'whoAmIAndWhatCanISee')
    .addItem('Delete legacy tabs', 'deleteLegacyTabs')
    .addToUi();
}

/** Hourly job. Only touches the last few days; everything older is left alone. */
function refreshArchive() {
  runArchive_(CONFIG.refreshWindowDays);
}

/**
 * One-off history seed. Run this once after setting GA4 data retention to
 * 14 months. Safe to re-run: rows are upserted, never duplicated.
 */
function backfillArchive() {
  runArchive_(CONFIG.backfillDays);
}

// ---------------------------------------------------------------------------
// Main
// ---------------------------------------------------------------------------

function runArchive_(windowDays) {
  // An hourly trigger firing while a manual run is mid-write would corrupt an
  // archive tab, so only one run is ever allowed at a time.
  const lock = LockService.getScriptLock();
  if (!lock.tryLock(30000)) {
    Logger.log('Another run is in progress; skipping this one.');
    return;
  }

  try {
    const ss = SpreadsheetApp.getActiveSpreadsheet();
    const startDate = windowDays + 'daysAgo';
    const endDate = 'today';
    const stamp = Utilities.formatDate(
      new Date(), ss.getSpreadsheetTimeZone(), 'yyyy-MM-dd HH:mm:ss');

    const errors = [];
    const dropped = [];
    const notes = [];
    const rowCounts = {};
    const fieldCache = {};

    const reports = REPORTS.concat(discoverCustomReports_(fieldCache, errors));

    reports.forEach(function (report) {
      const property = PROPERTIES[report.property];
      const tab = tabName_(report.property, report.sheet);

      try {
        const available = availableFields_(property.id, fieldCache);

        let dimensions = keepAvailable_(report.dimensions, available.dimensions,
          tab, 'dimension', dropped);
        const metrics = keepAvailable_(report.metrics, available.metrics,
          tab, 'metric', dropped).slice(0, MAX_METRICS);

        // Trim to the API's ceiling rather than letting the request 400, and
        // record what was cut so it shows up in _Status instead of vanishing.
        const budget = dimensionBudget_(report, dimensions);
        if (dimensions.length > budget) {
          dimensions.slice(budget).forEach(function (d) {
            dropped.push(tab + ': dimension ' + d[0] + ' cut, over the '
              + budget + '-dimension budget');
          });
          dimensions = dimensions.slice(0, budget);
        }

        if (!dimensions.length || !metrics.length) {
          throw new Error('no supported fields remain for this property');
        }

        const request = {
          dateRanges: [{ startDate: startDate, endDate: endDate }],
          dimensions: dimensions.map(function (d) { return { name: d[0] }; }),
          metrics: metrics.map(function (m) { return { name: m[0] }; }),
          keepEmptyRows: false,
        };

        // Only set dimensionFilter when there is one; an explicit null is invalid.
        const filter = buildFilter_(report);
        if (filter) request.dimensionFilter = filter;

        const fetched = runReport_(property.id, request);
        const headers = dimensions.concat(metrics).map(function (pair) { return pair[1]; });

        rowCounts[tab] = upsertSheet_(ss, tab, headers, fetched.rows,
          dimensions.length, stamp);

        Logger.log('%s: %s fetched, %s total', tab, fetched.rows.length, rowCounts[tab]);
      } catch (err) {
        errors.push(tab + ': ' + err.message);
        Logger.log('FAILED %s — %s', tab, err.message);
      }
    });

    // Derived tabs are rebuilt from the whole archive, not from this run's
    // window, so they always cover the full history the sheet holds.
    try {
      buildFunnels_(ss, rowCounts);
    } catch (err) {
      errors.push('Funnels: ' + err.message);
      Logger.log('FAILED funnels — %s', err.message);
    }

    try {
      buildLeadJourney_(ss, rowCounts, notes);
    } catch (err) {
      errors.push('Lead journey: ' + err.message);
      Logger.log('FAILED lead journey — %s', err.message);
    }

    try {
      buildDashboard_(ss, stamp);
    } catch (err) {
      errors.push('Dashboard: ' + err.message);
      Logger.log('FAILED dashboard — %s', err.message);
    }

    writeStatus_(ss, {
      stamp: stamp,
      window: windowDays + ' days ending today',
      rowCounts: rowCounts,
      dropped: dropped,
      notes: notes,
      errors: errors,
    });

    if (errors.length) {
      throw new Error('Some reports failed:\n' + errors.join('\n'));
    }
  } finally {
    lock.releaseLock();
  }
}

function tabName_(propertyKey, sheetName) {
  return PROPERTIES[propertyKey].label + ' · ' + sheetName;
}

// ---------------------------------------------------------------------------
// Field discovery
// ---------------------------------------------------------------------------

/**
 * Everything a property supports, fetched once per run and cached.
 * This is what lets an unregistered custom dimension be skipped quietly instead
 * of failing the entire report.
 */
function availableFields_(propertyId, cache) {
  if (cache[propertyId]) return cache[propertyId];

  const metadata = AnalyticsData.Properties.getMetadata(
    'properties/' + propertyId + '/metadata');

  const dimensions = {};
  const custom = [];
  (metadata.dimensions || []).forEach(function (d) {
    dimensions[d.apiName] = true;
    if (d.customDefinition) custom.push({ apiName: d.apiName, uiName: d.uiName || '' });
  });

  const metrics = {};
  (metadata.metrics || []).forEach(function (m) { metrics[m.apiName] = true; });

  cache[propertyId] = { dimensions: dimensions, metrics: metrics, custom: custom };
  return cache[propertyId];
}

/**
 * How many dimensions this report may actually request.
 *
 * GA4 counts a dimension referenced in dimensionFilter against the same
 * 9-dimension ceiling as the ones being selected, so a report filtering on
 * eventName without also selecting it gets 8 slots, not 9. Requesting 9 returns
 * "Requests are limited to 9 dimensions in a nested request ... this request is
 * for 10 dimensions".
 */
function dimensionBudget_(report, dimensions) {
  if (!report.event && !report.eventNameIn) return MAX_DIMENSIONS;

  const selectsEventName = dimensions.some(function (d) { return d[0] === 'eventName'; });
  return selectsEventName ? MAX_DIMENSIONS : MAX_DIMENSIONS - 1;
}

/** Filters [apiName, label] pairs down to those the property actually supports. */
function keepAvailable_(pairs, supported, tab, kind, dropped) {
  return pairs.filter(function (pair) {
    if (supported[pair[0]]) return true;
    dropped.push(tab + ': ' + kind + ' ' + pair[0] + ' not available');
    return false;
  });
}

/**
 * Builds extra reports covering every custom dimension registered on each
 * property, chunked to fit the API's 9-dimension ceiling.
 *
 * This is what makes the archive self-maintaining: register a new custom
 * dimension in GA4 and it starts being archived on the next run, with no code
 * change. It also covers the App Store property, whose schema is not known here.
 */
function discoverCustomReports_(fieldCache, errors) {
  const reports = [];

  Object.keys(PROPERTIES).forEach(function (key) {
    let custom;
    try {
      custom = availableFields_(PROPERTIES[key].id, fieldCache).custom;
    } catch (err) {
      errors.push(PROPERTIES[key].label + ' metadata: ' + err.message);
      return;
    }
    if (!custom.length) return;

    const perTab = MAX_DIMENSIONS - 1; // date occupies one slot
    for (let i = 0; i < custom.length; i += perTab) {
      const chunk = custom.slice(i, i + perTab);
      reports.push({
        sheet: 'Custom ' + (Math.floor(i / perTab) + 1),
        property: key,
        dimensions: [['date', 'Date']].concat(chunk.map(function (d) {
          return [d.apiName, customLabel_(d.apiName)];
        })),
        metrics: [
          ['eventCount', 'Event Count'],
          ['totalUsers', 'Users'],
        ],
      });
    }
  });

  return reports;
}

/** "customEvent:utm_source" → "utm_source". Unique, so headers cannot collide. */
function customLabel_(apiName) {
  return apiName.replace(/^custom(Event|User):/, '');
}

// ---------------------------------------------------------------------------
// Archive storage
// ---------------------------------------------------------------------------

/**
 * Merges freshly fetched rows into an archive tab and returns the total row
 * count. Existing rows are matched on their dimension values and replaced;
 * unmatched existing rows are preserved untouched, which is what makes the
 * archive outlive GA4's retention window.
 *
 * Returns the number of rows in the tab after the merge.
 */
function upsertSheet_(ss, name, headers, rows, keyCount, stamp) {
  let sheet = ss.getSheetByName(name);
  if (!sheet) sheet = ss.insertSheet(name);

  const fullHeaders = headers.concat([UPDATED_AT]);
  const existing = readSheet_(ss, name);
  const merged = [];
  const index = {};

  // Carry existing rows over, remapped onto the current header layout so that
  // registering a new custom dimension later does not orphan the archive.
  if (existing.headers.length) {
    const map = fullHeaders.map(function (h) { return existing.headers.indexOf(h); });
    existing.rows.forEach(function (row) {
      const remapped = map.map(function (i) { return i === -1 ? '' : row[i]; });
      const key = rowKey_(remapped, keyCount);
      if (index[key] === undefined) {
        index[key] = merged.length;
        merged.push(remapped);
      }
    });
  }

  rows.forEach(function (row) {
    const record = row.concat([stamp]);
    const key = rowKey_(record, keyCount);
    if (index[key] === undefined) {
      index[key] = merged.length;
      merged.push(record);
    } else {
      merged[index[key]] = record;
    }
  });

  merged.sort(function (a, b) {
    const ka = rowKey_(a, keyCount);
    const kb = rowKey_(b, keyCount);
    return ka < kb ? -1 : ka > kb ? 1 : 0;
  });

  writeGrid_(sheet, fullHeaders, merged, true);
  return merged.length;
}

function rowKey_(row, keyCount) {
  const parts = [];
  for (let i = 0; i < keyCount; i++) parts.push(String(row[i]));
  return parts.join(KEY_SEP);
}

/** Reads a tab back, normalising anything Sheets may have coerced. */
function readSheet_(ss, name) {
  const sheet = ss.getSheetByName(name);
  if (!sheet || sheet.getLastRow() < 2) return { headers: [], rows: [] };

  const values = sheet.getDataRange().getValues();
  const tz = ss.getSpreadsheetTimeZone();
  const headers = values[0].map(String);
  const rows = [];

  for (let i = 1; i < values.length; i++) {
    if (String(values[i][0]).length === 0) continue;
    rows.push(values[i].map(function (v) { return normaliseCell_(v, tz); }));
  }
  return { headers: headers, rows: rows };
}

/**
 * Dates are written as yyyy-MM-dd text. If a cell ever comes back as a real Date
 * the key would no longer match, so coerce it back before comparing.
 */
function normaliseCell_(value, tz) {
  if (Object.prototype.toString.call(value) === '[object Date]') {
    return Utilities.formatDate(value, tz, 'yyyy-MM-dd');
  }
  return value;
}

/** Writes a header row plus body, trimming any leftover rows and columns. */
function writeGrid_(sheet, headers, rows, dateColumnAsText) {
  sheet.clearContents();

  const body = rows.length ? rows : [headers.map(function () { return ''; })];
  const data = [headers].concat(body);

  // Set the format before writing, otherwise Sheets parses "2026-08-07" into a
  // Date and the archive key stops matching on the next run.
  if (dateColumnAsText) {
    sheet.getRange(1, 1, data.length, 1).setNumberFormat('@');
  }

  sheet.getRange(1, 1, data.length, headers.length).setValues(data);
  sheet.getRange(1, 1, 1, headers.length).setFontWeight('bold').setBackground('#f1f3f4');
  sheet.setFrozenRows(1);

  const extraRows = sheet.getMaxRows() - data.length;
  if (extraRows > 0) sheet.deleteRows(data.length + 1, extraRows);
  const extraCols = sheet.getMaxColumns() - headers.length;
  if (extraCols > 0) sheet.deleteColumns(headers.length + 1, extraCols);
}

// ---------------------------------------------------------------------------
// Derived reports
// ---------------------------------------------------------------------------

/**
 * Rebuilds the funnel tabs from the archived "Funnel Source" data, so they span
 * the full history rather than this run's window. These two tabs are the only
 * ones that are cleared and rewritten, because they are entirely derived.
 */
function buildFunnels_(ss, rowCounts) {
  const source = readSheet_(ss, tabName_('website', 'Funnel Source'));
  if (!source.rows.length) return;

  const h = source.headers;
  const iDate = h.indexOf('Date');
  const iEvent = h.indexOf('Event');
  const iCount = h.indexOf('Event Count');
  if (iDate === -1 || iEvent === -1 || iCount === -1) return;

  // --- by day ---
  const byDate = {};
  source.rows.forEach(function (row) {
    const date = row[iDate];
    if (!byDate[date]) byDate[date] = { landings: 0, clicks: 0 };
    addEvent_(byDate[date], row[iEvent], row[iCount]);
  });

  const dayRows = Object.keys(byDate).sort().map(function (date) {
    const d = byDate[date];
    return [date, d.landings, d.clicks, rate_(d)];
  });

  writeDerived_(ss, 'Funnel by Day',
    ['Date', 'Landings', 'Store Clicks', 'Click Rate'], dayRows, 4);
  rowCounts['Funnel by Day'] = dayRows.length;

  // --- by campaign ---
  const keyCols = ['Channel', 'Source', 'Medium', 'Campaign']
    .map(function (name) { return h.indexOf(name); })
    .filter(function (i) { return i !== -1; });
  if (!keyCols.length) return;

  const byCampaign = {};
  source.rows.forEach(function (row) {
    const key = keyCols.map(function (i) { return row[i]; }).join(KEY_SEP);
    if (!byCampaign[key]) byCampaign[key] = { landings: 0, clicks: 0 };
    addEvent_(byCampaign[key], row[iEvent], row[iCount]);
  });

  const campaignRows = Object.keys(byCampaign).map(function (key) {
    const d = byCampaign[key];
    return key.split(KEY_SEP).concat([d.landings, d.clicks, rate_(d)]);
  });

  // Most clicks first: the order anyone opening this tab actually wants.
  const clicksCol = keyCols.length + 1;
  campaignRows.sort(function (a, b) { return b[clicksCol] - a[clicksCol]; });

  const campaignHeaders = keyCols.map(function (i) { return h[i]; })
    .concat(['Landings', 'Store Clicks', 'Click Rate']);

  writeDerived_(ss, 'Funnel by Campaign', campaignHeaders, campaignRows,
    campaignHeaders.length);
  rowCounts['Funnel by Campaign'] = campaignRows.length;
}

// ---------------------------------------------------------------------------
// Lead journey
// ---------------------------------------------------------------------------

/**
 * Joins the Smartlead webhook log to GA4 activity and writes two flat tables
 * built for Looker Studio: one row per lead per campaign, and a campaign rollup.
 *
 * The join key is the token in utm_term. It is read straight out of the
 * `Link Clicked` URL that Smartlead already records next to the lead's email, so
 * the HMAC key never needs to live in this file. Setting a `LEAD_TOKEN_KEY`
 * script property enables a fallback that computes tokens for leads whose click
 * row was never captured.
 */
function buildLeadJourney_(ss, rowCounts, notes) {
  const sl = readSheet_(ss, SMARTLEAD_TAB);
  if (!sl.rows.length) {
    notes.push('"' + SMARTLEAD_TAB + '" is empty or missing; lead journey skipped.');
    return;
  }

  const tz = ss.getSpreadsheetTimeZone();
  const h = sl.headers;
  const col = {
    ts: h.indexOf('Timestamp'),
    type: h.indexOf('Event Type'),
    email: h.indexOf('Lead Email'),
    campaignId: h.indexOf('Campaign ID'),
    campaignName: h.indexOf('Campaign Name'),
    link: h.indexOf('Link Clicked'),
    details: h.indexOf('Details'),
  };
  if (col.ts === -1 || col.type === -1 || col.email === -1) {
    notes.push('"' + SMARTLEAD_TAB + '" is missing Timestamp, Event Type or Lead Email.');
    return;
  }

  // --- fold the event log into one record per lead per campaign ---
  const leads = {};
  const campaignsPerEmail = {};
  const seenTypes = {};

  sl.rows.forEach(function (r) {
    const email = String(r[col.email] || '').trim().toLowerCase();
    if (!email) return;

    const campaignId = String(r[col.campaignId] || '').trim();
    const key = email + KEY_SEP + campaignId;
    let lead = leads[key];
    if (!lead) {
      lead = leads[key] = newLead_(email, campaignId,
        col.campaignName === -1 ? '' : String(r[col.campaignName] || ''));
      campaignsPerEmail[email] = (campaignsPerEmail[email] || 0) + 1;
    }

    const when = smartleadStamp_(r[col.ts], tz);
    const type = String(r[col.type] || '').trim().toUpperCase();
    seenTypes[type] = true;

    switch (type) {
      case SL.SENT:
        lead.sent++;
        lead.sentAt = earlier_(lead.sentAt, when);
        break;
      case SL.OPEN:
        lead.opens++;
        lead.openedAt = earlier_(lead.openedAt, when);
        break;
      case SL.CLICK:
        lead.clicks++;
        lead.clickedAt = earlier_(lead.clickedAt, when);
        if (col.link !== -1) applyClickUrl_(lead, r[col.link]);
        break;
      case SL.REPLY:
      case SL.UNTRACKED_REPLY:
        lead.replies++;
        lead.repliedAt = earlier_(lead.repliedAt, when);
        break;
      case SL.BOUNCE:
        lead.bounced = 1;
        lead.bouncedAt = earlier_(lead.bouncedAt, when);
        break;
      case SL.UNSUB:
        lead.unsubscribed = 1;
        break;
      case SL.CATEGORY:
        if (col.details !== -1) {
          const cat = extractCategory_(r[col.details]);
          if (cat) lead.category = cat;
        }
        break;
      default:
        break;
    }
  });

  // --- optional: fill in tokens for leads that never produced a click row ---
  const keyHex = PropertiesService.getScriptProperties().getProperty('LEAD_TOKEN_KEY');
  if (keyHex) {
    const keyBytes = hexToBytes_(keyHex.trim());
    Object.keys(leads).forEach(function (k) {
      if (!leads[k].token) leads[k].token = leadToken_(leads[k].email, keyBytes);
    });
  }

  // --- GA4 side ---
  const web = readLeadSessions_(ss, 'website', function (name) {
    return name === WEB_STORE_CLICK_EVENT;
  });
  const store = readLeadSessions_(ss, 'appStore', function (name) {
    return STORE_INSTALL_EVENTS.indexOf(name) !== -1;
  });

  const matchedTokens = {};

  Object.keys(leads).forEach(function (k) {
    const lead = leads[k];
    if (!lead.token) return;
    // Only fall back to a token-wide match when the lead belongs to exactly one
    // campaign; otherwise the same visit would be credited to every campaign.
    const soleCampaign = campaignsPerEmail[lead.email] === 1;

    const w = lookupSessions_(web, lead.token, lead.utmCampaign, soleCampaign);
    const s = lookupSessions_(store, lead.token, lead.utmCampaign, soleCampaign);

    if (w) {
      matchedTokens[lead.token] = true;
      lead.webSessions = w.bucket.sessions;
      lead.webEvents = w.bucket.events;
      lead.storeClicks = w.bucket.special;
      lead.firstVisit = w.bucket.first;
      lead.lastVisit = w.bucket.last;
      lead.match = w.how;
    }
    if (s) {
      matchedTokens[lead.token] = true;
      lead.listingSessions = s.bucket.sessions;
      lead.listingEvents = s.bucket.events;
      lead.addAppClicks = s.bucket.special;
      lead.match = lead.match || s.how;
    }
  });

  // --- what GA4 saw that we could not attribute ---
  const unmatched = [];
  [web, store].forEach(function (index) {
    Object.keys(index.byToken).forEach(function (token) {
      if (!matchedTokens[token] && unmatched.indexOf(token) === -1) unmatched.push(token);
    });
  });
  if (unmatched.length) {
    notes.push(unmatched.length + ' utm_term value(s) in GA4 matched no lead. '
      + 'Google Ads keywords land here too, so a non-zero count is normal.');
  }

  if (!seenTypes[SL.REPLY] && !seenTypes[SL.UNTRACKED_REPLY]) {
    notes.push('No reply events received. Tick EMAIL_REPLY and UNTRACKED_REPLIES '
      + 'in the Smartlead webhook config; the Replied column stays 0 until then.');
  }
  if (!seenTypes[SL.BOUNCE]) {
    notes.push('No bounce events received. Tick EMAIL_BOUNCE in Smartlead, '
      + 'otherwise open rate is measured against sent rather than delivered.');
  }

  // --- write ---
  writeLeadJourney_(ss, leads, rowCounts);
  writeCampaignSummary_(ss, leads, rowCounts);
}

function newLead_(email, campaignId, campaignName) {
  return {
    email: email,
    campaignId: campaignId,
    campaignName: campaignName,
    token: '',
    utmCampaign: '',
    sent: 0, sentAt: '',
    opens: 0, openedAt: '',
    clicks: 0, clickedAt: '',
    replies: 0, repliedAt: '',
    bounced: 0, bouncedAt: '',
    unsubscribed: 0,
    category: '',
    webSessions: 0, webEvents: 0, storeClicks: 0,
    firstVisit: '', lastVisit: '',
    listingSessions: 0, listingEvents: 0, addAppClicks: 0,
    match: '',
  };
}

/** Pulls the join token and the campaign it was sent under out of a click URL. */
function applyClickUrl_(lead, url) {
  const text = String(url || '');
  const token = queryParam_(text, 'utm_term');
  if (token) lead.token = token.toLowerCase();
  const campaign = queryParam_(text, 'utm_campaign');
  if (campaign) lead.utmCampaign = campaign.toLowerCase();
}

function queryParam_(url, name) {
  const m = url.match(new RegExp('[?&]' + name + '=([^&#\\s]+)', 'i'));
  if (!m) return '';
  try {
    return decodeURIComponent(m[1]).trim();
  } catch (e) {
    return m[1].trim();
  }
}

/** Best effort: Smartlead nests the category inside the Details JSON blob. */
function extractCategory_(details) {
  const text = String(details || '');
  const named = text.match(/"(?:lead_category|category|new_category)"\s*:\s*"([^"]+)"/i);
  if (named) return named[1];
  const described = text.match(/"description"\s*:\s*"([^"]+)"/);
  return described ? described[1] : '';
}

/**
 * Smartlead writes DD/MM/YYYY. Passing that to new Date() would read 11/08 as
 * 8 November and silently misalign every join against the yyyy-MM-dd archives.
 */
function smartleadStamp_(value, tz) {
  if (Object.prototype.toString.call(value) === '[object Date]') {
    return Utilities.formatDate(value, tz, 'yyyy-MM-dd HH:mm:ss');
  }
  const text = String(value || '').trim();
  const m = text.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})(?:[ T](\d{2}:\d{2}(?::\d{2})?))?/);
  if (!m) return text;
  const day = ('0' + m[1]).slice(-2);
  const month = ('0' + m[2]).slice(-2);
  return m[3] + '-' + month + '-' + day + (m[4] ? ' ' + m[4] : '');
}

function earlier_(current, candidate) {
  if (!candidate) return current;
  if (!current) return candidate;
  return candidate < current ? candidate : current;
}

/**
 * Indexes a Lead Sessions archive by token, and by token+campaign.
 *
 * Sessions are deliberately NOT summed across event rows: every event in a
 * session repeats that session's count, so adding them would multiply the
 * session total by the number of distinct events. The daily maximum is taken
 * instead, then summed across days.
 */
function readLeadSessions_(ss, propertyKey, isSpecialEvent) {
  const out = { byPair: {}, byToken: {} };
  const tab = readSheet_(ss, tabName_(propertyKey, 'Lead Sessions'));
  if (!tab.rows.length) return out;

  const h = tab.headers;
  const iDate = h.indexOf('Date');
  const iToken = h.indexOf('Token');
  const iCampaign = h.indexOf('Campaign');
  const iEvent = h.indexOf('Event');
  const iCount = h.indexOf('Event Count');
  const iSessions = h.indexOf('Sessions');
  if (iToken === -1 || iEvent === -1) return out;

  tab.rows.forEach(function (r) {
    const token = String(r[iToken] || '').trim().toLowerCase();
    if (!token || token === '(not set)') return;

    const campaign = iCampaign === -1 ? '' : String(r[iCampaign] || '').trim().toLowerCase();
    const date = iDate === -1 ? '' : String(r[iDate] || '');
    const events = num_(r[iCount]);
    const sessions = num_(r[iSessions]);
    const special = isSpecialEvent(String(r[iEvent] || '')) ? events : 0;

    [
      bucketFor_(out.byPair, token + KEY_SEP + campaign),
      bucketFor_(out.byToken, token),
    ].forEach(function (b) {
      b.events += events;
      b.special += special;
      if (date) {
        b.sessionsByDate[date] = Math.max(b.sessionsByDate[date] || 0, sessions);
        if (!b.first || date < b.first) b.first = date;
        if (!b.last || date > b.last) b.last = date;
      }
    });
  });

  [out.byPair, out.byToken].forEach(function (index) {
    Object.keys(index).forEach(function (k) {
      const b = index[k];
      b.sessions = Object.keys(b.sessionsByDate).reduce(function (t, d) {
        return t + b.sessionsByDate[d];
      }, 0);
    });
  });

  return out;
}

function bucketFor_(index, key) {
  if (!index[key]) {
    index[key] = { events: 0, special: 0, sessions: 0, sessionsByDate: {}, first: '', last: '' };
  }
  return index[key];
}

function lookupSessions_(index, token, utmCampaign, allowTokenOnly) {
  if (utmCampaign) {
    const exact = index.byPair[token + KEY_SEP + utmCampaign];
    if (exact) return { bucket: exact, how: 'token + campaign' };
  }
  if (allowTokenOnly && index.byToken[token]) {
    return { bucket: index.byToken[token], how: 'token only' };
  }
  return null;
}

/** Furthest rung reached. Bounce is checked last, after any real engagement. */
function stageOf_(lead) {
  if (lead.addAppClicks) return 6;
  if (lead.listingSessions) return 5;
  if (lead.webSessions) return 4;
  if (lead.clicks) return 3;
  if (lead.opens) return 2;
  if (lead.bounced) return 0;
  return 1;
}

function writeLeadJourney_(ss, leads, rowCounts) {
  const headers = [
    'Lead Email', 'Token', 'Campaign ID', 'Campaign Name', 'UTM Campaign',
    'Sent', 'Emails Sent', 'Sent At',
    'Bounced', 'Delivered',
    'Opened', 'Opens', 'Opened At',
    'Clicked', 'Clicks', 'Clicked At',
    'Replied', 'Replies', 'Replied At',
    'Unsubscribed', 'Lead Category',
    'Visited Website', 'Website Sessions', 'Website Events', 'First Visit', 'Last Visit',
    'Clicked To Store', 'Store Clicks',
    'Reached Listing', 'Listing Sessions',
    'Clicked Add App', 'Add App Clicks',
    'Stage', 'Stage No', 'Attribution',
  ];

  const rows = Object.keys(leads).map(function (k) {
    const l = leads[k];
    const stage = stageOf_(l);
    return [
      l.email, l.token, l.campaignId, l.campaignName, l.utmCampaign,
      l.sent ? 1 : 0, l.sent, l.sentAt,
      l.bounced, (l.sent && !l.bounced) ? 1 : 0,
      l.opens ? 1 : 0, l.opens, l.openedAt,
      l.clicks ? 1 : 0, l.clicks, l.clickedAt,
      l.replies ? 1 : 0, l.replies, l.repliedAt,
      l.unsubscribed, l.category,
      l.webSessions ? 1 : 0, l.webSessions, l.webEvents, l.firstVisit, l.lastVisit,
      l.storeClicks ? 1 : 0, l.storeClicks,
      l.listingSessions ? 1 : 0, l.listingSessions,
      l.addAppClicks ? 1 : 0, l.addAppClicks,
      STAGE_NAMES[stage], stage, l.match || (l.token ? 'no GA4 activity' : 'no token'),
    ];
  });

  rows.sort(function (a, b) {
    const byStage = b[33] - a[33];
    return byStage !== 0 ? byStage : (a[0] < b[0] ? -1 : 1);
  });

  writeDerived_(ss, 'Lead Journey', headers, rows, null);
  rowCounts['Lead Journey'] = rows.length;
}

function writeCampaignSummary_(ss, leads, rowCounts) {
  const byCampaign = {};

  Object.keys(leads).forEach(function (k) {
    const l = leads[k];
    const key = l.campaignId + KEY_SEP + l.campaignName;
    if (!byCampaign[key]) {
      byCampaign[key] = {
        id: l.campaignId, name: l.campaignName, leads: 0, sent: 0, bounced: 0,
        opened: 0, clicked: 0, replied: 0, unsub: 0,
        website: 0, storeClick: 0, listing: 0, addApp: 0,
      };
    }
    const c = byCampaign[key];
    c.leads++;
    if (l.sent) c.sent++;
    if (l.bounced) c.bounced++;
    if (l.opens) c.opened++;
    if (l.clicks) c.clicked++;
    if (l.replies) c.replied++;
    if (l.unsubscribed) c.unsub++;
    if (l.webSessions) c.website++;
    if (l.storeClicks) c.storeClick++;
    if (l.listingSessions) c.listing++;
    if (l.addAppClicks) c.addApp++;
  });

  const headers = [
    'Campaign ID', 'Campaign Name', 'Leads',
    'Sent', 'Bounced', 'Delivered', 'Bounce Rate',
    'Opened', 'Not Opened', 'Open Rate',
    'Clicked', 'Click Rate', 'Click To Open Rate',
    'Replied', 'Reply Rate', 'Unsubscribed',
    'Visited Website', 'Website Rate',
    'Clicked To Store', 'Reached Listing',
    'Clicked Add App', 'Add App Rate',
  ];

  const rows = Object.keys(byCampaign).map(function (k) {
    const c = byCampaign[k];
    // Open and click rates are measured against delivered, not sent: a bounced
    // address never had the opportunity to open.
    const delivered = c.sent - c.bounced;
    return [
      c.id, c.name, c.leads,
      c.sent, c.bounced, delivered, ratio_(c.bounced, c.sent),
      c.opened, Math.max(delivered - c.opened, 0), ratio_(c.opened, delivered),
      c.clicked, ratio_(c.clicked, delivered), ratio_(c.clicked, c.opened),
      c.replied, ratio_(c.replied, delivered), c.unsub,
      c.website, ratio_(c.website, c.clicked),
      c.storeClick, c.listing,
      c.addApp, ratio_(c.addApp, c.clicked),
    ];
  }).sort(function (a, b) { return b[3] - a[3]; });

  writeDerived_(ss, 'Campaign Summary', headers, rows, [7, 10, 12, 13, 15, 18, 22]);
  rowCounts['Campaign Summary'] = rows.length;
}

function ratio_(part, whole) {
  return whole ? part / whole : '';
}

// --- optional HMAC fallback --------------------------------------------------

/** Hex string to the signed byte array Apps Script's crypto helpers expect. */
function hexToBytes_(hex) {
  const bytes = [];
  for (let i = 0; i + 1 < hex.length; i += 2) {
    const b = parseInt(hex.substr(i, 2), 16);
    bytes.push(b > 127 ? b - 256 : b);
  }
  return bytes;
}

/** HMAC-SHA256 of the normalised email, hex encoded. Mirrors Smartlead's token. */
function leadToken_(email, keyBytes) {
  const value = Utilities.newBlob(String(email).trim().toLowerCase()).getBytes();
  const sig = Utilities.computeHmacSha256Signature(value, keyBytes);
  return sig.map(function (b) {
    const v = (b < 0 ? b + 256 : b).toString(16);
    return v.length === 1 ? '0' + v : v;
  }).join('');
}

// ---------------------------------------------------------------------------
// Dashboard
// ---------------------------------------------------------------------------

/**
 * Builds the summary tab from the archives. Entirely derived, so it is cleared
 * and rewritten each run.
 *
 * Only *additive* metrics are totalled. Sessions, event counts and page views
 * can be summed across dimension rows; user counts cannot, because the same
 * person appears in several rows and would be counted twice. That is why there
 * is no "Users" headline here even though the archives hold it.
 */
function buildDashboard_(ss, stamp) {
  const tz = ss.getSpreadsheetTimeZone();
  const curFrom = daysAgo_(DASH_WINDOW, tz);
  const prevFrom = daysAgo_(DASH_WINDOW * 2, tz);
  const trendFrom = daysAgo_(DASH_TREND, tz);

  const funnel = readSheet_(ss, tabName_('website', 'Funnel Source'));
  const channels = readSheet_(ss, tabName_('website', 'Channels'));
  const pages = readSheet_(ss, tabName_('website', 'Pages'));
  const clicks = readSheet_(ss, tabName_('website', 'Store Clicks'));
  const store = readSheet_(ss, tabName_('appStore', 'Traffic'));

  const grid = [];
  const percents = [];
  const headings = [];

  function put(cells) { grid.push(cells); return grid.length; }
  function gap() { put(['']); }
  function section(title) {
    gap();
    headings.push(put([title]));
  }

  headings.push(put(['BRIX GA4 DASHBOARD']));
  put(['Updated', stamp]);
  put(['Comparing', 'last ' + DASH_WINDOW + ' days vs the ' + DASH_WINDOW + ' before it']);

  // --- website headlines ---
  const cur = websiteTotals_(funnel, channels, curFrom, null);
  const prev = websiteTotals_(funnel, channels, prevFrom, curFrom);

  section('WEBSITE');
  put(['Metric', 'Last ' + DASH_WINDOW + 'd', 'Previous', 'Change']);
  [
    ['Sessions', cur.sessions, prev.sessions, false],
    ['Engaged sessions', cur.engaged, prev.engaged, false],
    ['Engagement rate', cur.engagementRate, prev.engagementRate, true],
    ['Avg session duration (s)', cur.avgDuration, prev.avgDuration, false],
    ['Page views', cur.views, prev.views, false],
    ['Key events', cur.keyEvents, prev.keyEvents, false],
    ['Landings', cur.landings, prev.landings, false],
    ['Store clicks', cur.clicks, prev.clicks, false],
    ['Click rate', cur.clickRate, prev.clickRate, true],
  ].forEach(function (m) {
    const r = put([m[0], m[1], m[2], change_(m[1], m[2])]);
    if (m[3]) percents.push({ row: r, col: 2, numCols: 2 });
    percents.push({ row: r, col: 4, numCols: 1 });
  });

  // --- store headlines ---
  const sCur = storeTotals_(store, curFrom, null);
  const sPrev = storeTotals_(store, prevFrom, curFrom);

  section('APP STORE PROPERTY');
  put(['Metric', 'Last ' + DASH_WINDOW + 'd', 'Previous', 'Change']);
  [
    ['Sessions', sCur.sessions, sPrev.sessions, false],
    ['Engaged sessions', sCur.engaged, sPrev.engaged, false],
    ['Engagement rate', sCur.engagementRate, sPrev.engagementRate, true],
    ['Key events', sCur.keyEvents, sPrev.keyEvents, false],
    ['Events', sCur.events, sPrev.events, false],
  ].forEach(function (m) {
    const r = put([m[0], m[1], m[2], change_(m[1], m[2])]);
    if (m[3]) percents.push({ row: r, col: 2, numCols: 2 });
    percents.push({ row: r, col: 4, numCols: 1 });
  });

  // --- top campaigns ---
  section('TOP CAMPAIGNS BY STORE CLICKS (last ' + DASH_WINDOW + ' days)');
  put(['Channel', 'Source', 'Medium', 'Campaign', 'Landings', 'Clicks', 'Click rate']);
  const campaigns = groupFunnel_(funnel, ['Channel', 'Source', 'Medium', 'Campaign'],
    curFrom, null);
  if (!campaigns.length) put(['No data yet']);
  campaigns.slice(0, 10).forEach(function (c) {
    const r = put(c.key.concat([c.landings, c.clicks, rate_(c)]));
    percents.push({ row: r, col: 7, numCols: 1 });
  });

  // --- top channels ---
  section('TOP CHANNELS (last ' + DASH_WINDOW + ' days)');
  put(['Channel', 'Landings', 'Clicks', 'Click rate']);
  const byChannel = groupFunnel_(funnel, ['Channel'], curFrom, null);
  if (!byChannel.length) put(['No data yet']);
  byChannel.slice(0, 10).forEach(function (c) {
    const r = put(c.key.concat([c.landings, c.clicks, rate_(c)]));
    percents.push({ row: r, col: 4, numCols: 1 });
  });

  // --- top pages ---
  section('TOP PAGES BY VIEWS (last ' + DASH_WINDOW + ' days)');
  put(['Page', 'Views', 'Sessions', 'Key events']);
  const topPages = groupSum_(pages, 'Page', ['Views', 'Sessions', 'Key Events'],
    curFrom, null, 'Views');
  if (!topPages.length) put(['No data yet']);
  topPages.slice(0, 10).forEach(function (p) {
    put([p.key, p.values[0], p.values[1], p.values[2]]);
  });

  // --- where clicks come from ---
  section('WHERE STORE CLICKS COME FROM (last ' + DASH_WINDOW + ' days)');
  put(['Click page', 'Placement', 'Clicks']);
  const placements = groupSum_(clicks, ['Click Page', 'Click Placement'], ['Clicks'],
    curFrom, null, 'Clicks');
  if (!placements.length) put(['No data yet']);
  placements.slice(0, 10).forEach(function (p) {
    put([p.key[0], p.key[1], p.values[0]]);
  });

  // --- trend ---
  section('DAILY TREND (last ' + DASH_TREND + ' days)');
  put(['Date', 'Landings', 'Store clicks', 'Click rate']);
  const trend = groupFunnel_(funnel, ['Date'], trendFrom, null)
    .sort(function (a, b) { return a.key[0] < b.key[0] ? -1 : 1; });
  if (!trend.length) put(['No data yet']);
  trend.forEach(function (t) {
    const r = put([t.key[0], t.landings, t.clicks, rate_(t)]);
    percents.push({ row: r, col: 4, numCols: 1 });
  });

  gap();
  put(['Note', 'User counts are deliberately excluded: they cannot be summed '
    + 'across dimension rows without double counting.']);

  // --- write it ---
  let sheet = ss.getSheetByName('Dashboard');
  if (!sheet) sheet = ss.insertSheet('Dashboard');
  sheet.clearContents();
  sheet.clearFormats();

  const width = grid.reduce(function (w, r) { return Math.max(w, r.length); }, 1);
  const padded = grid.map(function (r) {
    const copy = r.slice();
    while (copy.length < width) copy.push('');
    return copy;
  });

  sheet.getRange(1, 1, padded.length, width).setValues(padded);
  headings.forEach(function (r) {
    sheet.getRange(r, 1, 1, width).setFontWeight('bold').setBackground('#f1f3f4');
  });
  percents.forEach(function (p) {
    sheet.getRange(p.row, p.col, 1, p.numCols).setNumberFormat('0.0%');
  });
  sheet.setFrozenRows(1);
  sheet.autoResizeColumns(1, Math.min(width, 8));

  // Put it first, so opening the spreadsheet lands here.
  ss.setActiveSheet(sheet);
  ss.moveActiveSheet(1);
}

/** yyyy-MM-dd for N days ago, matching how dates are stored in the archives. */
function daysAgo_(n, tz) {
  const d = new Date();
  d.setDate(d.getDate() - n);
  return Utilities.formatDate(d, tz, 'yyyy-MM-dd');
}

/** Period-over-period change, blank when there is no baseline to compare to. */
function change_(current, previous) {
  if (!previous) return '';
  return (current - previous) / previous;
}

function inWindow_(date, from, to) {
  if (!date) return false;
  if (date < from) return false;
  if (to && date >= to) return false;
  return true;
}

function websiteTotals_(funnel, channels, from, to) {
  const out = {
    sessions: 0, engaged: 0, views: 0, keyEvents: 0,
    durationWeighted: 0, landings: 0, clicks: 0,
  };

  const fh = funnel.headers;
  const fDate = fh.indexOf('Date');
  const fEvent = fh.indexOf('Event');
  const fCount = fh.indexOf('Event Count');
  if (fDate !== -1) {
    funnel.rows.forEach(function (r) {
      if (!inWindow_(r[fDate], from, to)) return;
      addEvent_(out, r[fEvent], r[fCount]);
    });
  }

  const ch = channels.headers;
  const cDate = ch.indexOf('Date');
  const cSessions = ch.indexOf('Sessions');
  const cEngaged = ch.indexOf('Engaged Sessions');
  const cDuration = ch.indexOf('Avg Duration');
  const cViews = ch.indexOf('Views');
  const cKey = ch.indexOf('Key Events');
  if (cDate !== -1) {
    channels.rows.forEach(function (r) {
      if (!inWindow_(r[cDate], from, to)) return;
      const sessions = num_(r[cSessions]);
      out.sessions += sessions;
      out.engaged += num_(r[cEngaged]);
      out.views += num_(r[cViews]);
      out.keyEvents += num_(r[cKey]);
      // Averages must be re-weighted by sessions, never averaged of averages.
      out.durationWeighted += num_(r[cDuration]) * sessions;
    });
  }

  out.engagementRate = out.sessions ? out.engaged / out.sessions : '';
  out.avgDuration = out.sessions ? Math.round(out.durationWeighted / out.sessions) : '';
  out.clickRate = out.landings ? out.clicks / out.landings : '';
  return out;
}

function storeTotals_(store, from, to) {
  const out = { sessions: 0, engaged: 0, keyEvents: 0, events: 0 };
  const h = store.headers;
  const iDate = h.indexOf('Date');
  if (iDate === -1) return Object.assign(out, { engagementRate: '' });

  const iSessions = h.indexOf('Sessions');
  const iEngaged = h.indexOf('Engaged Sessions');
  const iKey = h.indexOf('Key Events');
  const iEvents = h.indexOf('Event Count');

  store.rows.forEach(function (r) {
    if (!inWindow_(r[iDate], from, to)) return;
    out.sessions += num_(r[iSessions]);
    out.engaged += num_(r[iEngaged]);
    out.keyEvents += num_(r[iKey]);
    out.events += num_(r[iEvents]);
  });

  out.engagementRate = out.sessions ? out.engaged / out.sessions : '';
  return out;
}

/** Groups Funnel Source rows by the named columns, splitting the two events. */
function groupFunnel_(funnel, keyHeaders, from, to) {
  const h = funnel.headers;
  const iDate = h.indexOf('Date');
  const iEvent = h.indexOf('Event');
  const iCount = h.indexOf('Event Count');
  if (iDate === -1 || iEvent === -1) return [];

  const keyIdx = keyHeaders.map(function (n) { return h.indexOf(n); });
  if (keyIdx.indexOf(-1) !== -1) return [];

  const buckets = {};
  funnel.rows.forEach(function (r) {
    if (!inWindow_(r[iDate], from, to)) return;
    const key = keyIdx.map(function (i) { return r[i]; }).join(KEY_SEP);
    if (!buckets[key]) buckets[key] = { landings: 0, clicks: 0 };
    addEvent_(buckets[key], r[iEvent], r[iCount]);
  });

  return Object.keys(buckets).map(function (key) {
    return {
      key: key.split(KEY_SEP),
      landings: buckets[key].landings,
      clicks: buckets[key].clicks,
    };
  }).sort(function (a, b) { return b.clicks - a.clicks; });
}

/**
 * Groups any archive tab by one or more columns and sums the named metrics,
 * sorted by `sortBy` descending.
 */
function groupSum_(result, keyHeaders, valueHeaders, from, to, sortBy) {
  const single = typeof keyHeaders === 'string';
  const keys = single ? [keyHeaders] : keyHeaders;

  const h = result.headers;
  const iDate = h.indexOf('Date');
  const keyIdx = keys.map(function (n) { return h.indexOf(n); });
  const valIdx = valueHeaders.map(function (n) { return h.indexOf(n); });
  if (iDate === -1 || keyIdx.indexOf(-1) !== -1) return [];

  const buckets = {};
  result.rows.forEach(function (r) {
    if (!inWindow_(r[iDate], from, to)) return;
    const key = keyIdx.map(function (i) { return r[i]; }).join(KEY_SEP);
    if (!buckets[key]) buckets[key] = valIdx.map(function () { return 0; });
    valIdx.forEach(function (vi, n) {
      if (vi !== -1) buckets[key][n] += num_(r[vi]);
    });
  });

  const sortAt = Math.max(0, valueHeaders.indexOf(sortBy));
  return Object.keys(buckets).map(function (key) {
    const parts = key.split(KEY_SEP);
    return { key: single ? parts[0] : parts, values: buckets[key] };
  }).sort(function (a, b) { return b.values[sortAt] - a.values[sortAt]; });
}

function num_(v) {
  const n = Number(v);
  return isNaN(n) ? 0 : n;
}

function addEvent_(bucket, eventName, count) {
  const n = Number(count) || 0;
  if (eventName === EVENTS.landing) bucket.landings += n;
  if (eventName === EVENTS.appStoreClick) bucket.clicks += n;
}

/**
 * Blank rather than 0% when there is no denominator: clicks without a recorded
 * landing mean the landing event did not fire, not a bad conversion rate.
 */
function rate_(bucket) {
  return bucket.landings ? bucket.clicks / bucket.landings : '';
}

/** `percentColumns` accepts a single 1-based column or an array of them. */
function writeDerived_(ss, name, headers, rows, percentColumns) {
  let sheet = ss.getSheetByName(name);
  if (!sheet) sheet = ss.insertSheet(name);

  writeGrid_(sheet, headers, rows, false);

  if (!rows.length || !percentColumns) return;
  const columns = Array.isArray(percentColumns) ? percentColumns : [percentColumns];
  columns.forEach(function (c) {
    sheet.getRange(2, c, rows.length, 1).setNumberFormat('0.00%');
  });
}

// ---------------------------------------------------------------------------
// Data API
// ---------------------------------------------------------------------------

/** Builds the eventName filter for a report, or null if it wants everything. */
function buildFilter_(report) {
  if (report.event) {
    return {
      filter: {
        fieldName: 'eventName',
        stringFilter: { matchType: 'EXACT', value: report.event },
      },
    };
  }
  if (report.eventNameIn) {
    return {
      filter: {
        fieldName: 'eventName',
        inListFilter: { values: report.eventNameIn },
      },
    };
  }
  return null;
}

/**
 * Runs a report and pages through the full result set.
 * The API caps a single response at 250k rows; we page in 100k chunks.
 */
function runReport_(propertyId, request) {
  const PAGE_SIZE = 100000;
  const rows = [];
  let offset = 0;

  while (true) {
    const page = Object.keys(request).reduce(function (acc, k) {
      acc[k] = request[k];
      return acc;
    }, {});
    page.limit = PAGE_SIZE;
    page.offset = offset;

    const response = AnalyticsData.Properties.runReport(page, 'properties/' + propertyId);
    const batch = response.rows || [];

    batch.forEach(function (row) {
      const dims = (row.dimensionValues || []).map(function (v) {
        return formatDimension_(v.value);
      });
      const mets = (row.metricValues || []).map(function (v) {
        const n = Number(v.value);
        return isNaN(n) ? v.value : n;
      });
      rows.push(dims.concat(mets));
    });

    offset += batch.length;
    if (batch.length < PAGE_SIZE) break;
    if (response.rowCount && offset >= response.rowCount) break;
  }

  return { rows: rows };
}

/** GA4 returns dates as YYYYMMDD. Turn that into a real sortable date string. */
function formatDimension_(value) {
  if (/^\d{8}$/.test(value)) {
    return value.slice(0, 4) + '-' + value.slice(4, 6) + '-' + value.slice(6, 8);
  }
  return value;
}

// ---------------------------------------------------------------------------
// Status and diagnostics
// ---------------------------------------------------------------------------

function writeStatus_(ss, info) {
  let sheet = ss.getSheetByName('_Status');
  if (!sheet) sheet = ss.insertSheet('_Status');

  const rows = [
    ['Last run', info.stamp],
    ['Refresh window', info.window],
  ];
  Object.keys(PROPERTIES).forEach(function (key) {
    const p = PROPERTIES[key];
    rows.push([p.label + ' property (' + p.name + ')', p.id]);
  });
  rows.push(['Status', info.errors.length ? 'ERRORS' : 'OK']);
  rows.push(['', '']);

  rows.push(['ARCHIVED ROWS', '']);
  Object.keys(info.rowCounts).sort().forEach(function (tab) {
    rows.push([tab, info.rowCounts[tab]]);
  });

  if (info.notes && info.notes.length) {
    rows.push(['', '']);
    rows.push(['LEAD JOURNEY', '']);
    info.notes.forEach(function (line) { rows.push(['', line]); });
  }

  if (info.dropped.length) {
    rows.push(['', '']);
    rows.push(['SKIPPED FIELDS', 'not registered on that property']);
    info.dropped.forEach(function (line) { rows.push(['', line]); });
  }

  if (info.errors.length) {
    rows.push(['', '']);
    rows.push(['ERRORS', '']);
    info.errors.forEach(function (line) { rows.push(['', line]); });
  }

  sheet.clearContents();
  sheet.getRange(1, 1, rows.length, 2).setValues(rows);
  sheet.getRange(1, 1, rows.length, 1).setFontWeight('bold');
  sheet.autoResizeColumn(1);
}

/**
 * Diagnostic for "User does not have sufficient permissions for this property".
 *
 * GA4 returns that error both when the account lacks access AND when the
 * property ID does not exist, so it cannot tell you which is wrong. This prints
 * the account the script actually runs as, then every property it can see.
 *
 * Needs the Google Analytics Admin API service (AnalyticsAdmin, v1beta).
 * Read the output in the editor: View → Logs.
 */
function whoAmIAndWhatCanISee() {
  Logger.log('Script is authorised as: %s', Session.getEffectiveUser().getEmail());
  Object.keys(PROPERTIES).forEach(function (key) {
    Logger.log('Configured %s: %s', PROPERTIES[key].label, PROPERTIES[key].id);
  });
  Logger.log('---');

  let summaries;
  try {
    summaries = AnalyticsAdmin.AccountSummaries.list({ pageSize: 200 });
  } catch (err) {
    Logger.log('Could not list properties: %s', err.message);
    Logger.log('If this says "AnalyticsAdmin is not defined", add the Google '
      + 'Analytics Admin API service (identifier AnalyticsAdmin) in the sidebar.');
    return;
  }

  const accounts = summaries.accountSummaries || [];
  if (!accounts.length) {
    Logger.log('This account has access to ZERO GA4 properties.');
    Logger.log('You authorised the script with the wrong Google account.');
    return;
  }

  const configured = Object.keys(PROPERTIES).map(function (k) { return PROPERTIES[k].id; });
  accounts.forEach(function (account) {
    Logger.log('Account: %s', account.displayName);
    (account.propertySummaries || []).forEach(function (p) {
      const id = p.property.split('/')[1];
      const mark = configured.indexOf(id) !== -1 ? '   <-- matches your config' : '';
      Logger.log('   %s  %s%s', id, p.displayName, mark);
    });
  });
}

/**
 * Writes every dimension and metric each property supports to a `_Fields` tab,
 * flagging which are custom definitions registered on that property.
 */
function listAvailableFields() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const rows = [];

  Object.keys(PROPERTIES).forEach(function (key) {
    const property = PROPERTIES[key];
    const metadata = AnalyticsData.Properties.getMetadata(
      'properties/' + property.id + '/metadata');

    (metadata.dimensions || []).forEach(function (d) {
      rows.push([property.label, property.id, 'Dimension', d.apiName,
        d.uiName || '', d.category || '', d.customDefinition ? 'CUSTOM' : 'built-in']);
    });
    (metadata.metrics || []).forEach(function (m) {
      rows.push([property.label, property.id, 'Metric', m.apiName,
        m.uiName || '', m.category || '', m.customDefinition ? 'CUSTOM' : 'built-in']);
    });
  });

  let sheet = ss.getSheetByName('_Fields');
  if (!sheet) sheet = ss.insertSheet('_Fields');
  writeGrid_(sheet,
    ['Property', 'Property ID', 'Type', 'API Name', 'Display Name', 'Category', 'Origin'],
    rows, false);

  SpreadsheetApp.getActive().toast(rows.length + ' fields listed on the _Fields tab.');
}

/**
 * Removes the tabs left behind by the pre-archive version of this script.
 *
 * Those tabs stopped updating when the script was replaced, so they hold a
 * frozen snapshot that looks current but is not. Only names in LEGACY_TABS are
 * ever touched, and nothing is deleted until you confirm the exact list.
 */
function deleteLegacyTabs() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const ui = SpreadsheetApp.getUi();

  const found = LEGACY_TABS.filter(function (name) {
    return ss.getSheetByName(name) !== null;
  });

  if (!found.length) {
    ui.alert('Nothing to remove', 'No legacy tabs found.', ui.ButtonSet.OK);
    return;
  }

  const answer = ui.alert(
    'Delete ' + found.length + ' stale tab(s)?',
    'These are from the old version of the script and are no longer updated:\n\n'
      + found.join('\n')
      + '\n\nThe new "Web ·" and "Store ·" tabs replace them. This cannot be undone.',
    ui.ButtonSet.YES_NO);

  if (answer !== ui.Button.YES) return;

  found.forEach(function (name) { ss.deleteSheet(ss.getSheetByName(name)); });
  ss.toast('Removed ' + found.length + ' legacy tab(s).');
}

// ---------------------------------------------------------------------------
// Triggers
// ---------------------------------------------------------------------------

function installHourlyTrigger() {
  removeTriggers();
  ScriptApp.newTrigger('refreshArchive').timeBased().everyHours(1).create();
  SpreadsheetApp.getActive().toast('Hourly refresh installed.');
}

function removeTriggers() {
  // 'refreshAllReports' is the pre-archive function name, cleaned up here so an
  // upgraded sheet does not keep running the old mirror job alongside this one.
  const ours = ['refreshArchive', 'backfillArchive', 'refreshAllReports'];
  ScriptApp.getProjectTriggers().forEach(function (t) {
    if (ours.indexOf(t.getHandlerFunction()) !== -1) ScriptApp.deleteTrigger(t);
  });
}
