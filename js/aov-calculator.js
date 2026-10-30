/**
 * Live AOV / revenue calculator on /aov-calculator.
 *
 * Same math as js/calculator.js and js/wizard.js: monthly revenue x 18.5%,
 * the midpoint of the 17-20% AOV lift Brix merchants typically report.
 * What differs is the interaction, which follows the calculator on /digital:
 * there is no button, every keystroke and every currency change re-runs it.
 *
 * The page ships with an example store already worked out in the markup
 * (PHP renders the same numbers this script would), so nothing is blank
 * before the script arrives. The first edit takes the "example" note off
 * the result.
 *
 * Nothing here is sent anywhere: no request, no storage.
 */
(function aovLiveCalculator() {
  const tool = document.getElementById('aovTool');
  if (!tool) return;

  const UPLIFT = 0.185;

  const aovEl = document.getElementById('aovInput');
  const revEl = document.getElementById('aovRevenue');
  const adEl = document.getElementById('aovAdSpend');
  const chips = [...tool.querySelectorAll('.calc-chip')];
  const symEls = tool.querySelectorAll('.calc-sym');

  const figWrap = document.getElementById('aovFig');
  const missingEl = document.getElementById('aovMissing');
  const newAovEl = document.getElementById('aovNewAov');
  const newRevEl = document.getElementById('aovNewRevenue');
  const noteEl = document.getElementById('aovNote');

  let sym = '$';
  let touched = false;

  const money = n => sym + Math.round(n).toLocaleString('en-US');
  const positive = n => Number.isFinite(n) && n > 0;

  // A symbol change relabels the numbers already typed in; it never
  // converts them. The visitor's own figures stay exactly as entered.
  function setCurrency(chip) {
    sym = chip.dataset.sym;
    chips.forEach(c => c.setAttribute('aria-pressed', String(c === chip)));
    symEls.forEach(el => { el.textContent = sym; });
  }

  function render() {
    const aov = parseFloat(aovEl.value);
    const revenue = parseFloat(revEl.value);
    const adSpend = parseFloat(adEl.value);

    if (!positive(aov) || !positive(revenue)) {
      missingEl.textContent = money(0);
      newAovEl.textContent = '—';
      newRevEl.textContent = '—';
      noteEl.textContent = 'Enter your average order value and monthly revenue to see your estimate.';
    } else {
      const missing = revenue * UPLIFT;

      missingEl.textContent = money(missing);
      newAovEl.textContent = money(aov * (1 + UPLIFT));
      newRevEl.textContent = money(revenue + missing);

      if (!touched) {
        noteEl.textContent = 'This is an example store. Change the numbers to see yours.';
      } else if (positive(adSpend)) {
        noteEl.textContent = 'That is on top of the ' + money(adSpend) + '/mo you already spend on ads. No increase needed to get it.';
      } else {
        noteEl.textContent = 'No extra ad spend needed to get there.';
      }
    }

    figWrap.classList.remove('calc-pop');
    void figWrap.offsetWidth;
    figWrap.classList.add('calc-pop');
  }

  [aovEl, revEl, adEl].forEach(el => el.addEventListener('input', () => {
    touched = true;
    render();
  }));

  chips.forEach(chip => chip.addEventListener('click', () => {
    touched = true;
    setCurrency(chip);
    render();
  }));
})();
