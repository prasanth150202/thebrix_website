/**
 * AOV / revenue calculator on /revenue-calculator.
 *
 * Brix merchants typically report a 17-20% AOV lift (see the testimonial
 * carousel: +23% to +52%, this is the conservative end). The midpoint is
 * applied to the visitor's own numbers rather than showing a fixed range,
 * so the result reads as theirs, not a marketing claim.
 *
 * Orders/month = revenue / AOV cancels out of the missing-revenue figure
 * (it reduces to revenue * UPLIFT either way), but computing it lets the
 * card also show a new AOV, which is what makes the number feel earned
 * rather than asserted.
 */
(function aovCalculator() {
  const card = document.getElementById('aovCalc');
  if (!card) return;

  const UPLIFT = 0.185;

  const currencyEl = document.getElementById('calcCurrency');
  const symEls = card.querySelectorAll('.calc-currency-sym');
  const aovEl = document.getElementById('calcAov');
  const revEl = document.getElementById('calcRevenue');
  const adEl = document.getElementById('calcAdSpend');
  const btn = document.getElementById('calcBtn');
  const result = document.getElementById('calcResult');
  const missingEl = document.getElementById('calcMissing');
  const newAovEl = document.getElementById('calcNewAov');
  const newRevEl = document.getElementById('calcNewRevenue');
  const adNoteEl = document.getElementById('calcAdNote');

  const money = n => currencyEl.value + Math.round(n).toLocaleString('en-US');

  // A symbol change relabels the numbers already typed in; it never
  // converts them; the visitor's own figures stay exactly as entered.
  currencyEl.addEventListener('change', () => {
    symEls.forEach(el => { el.textContent = currencyEl.value; });
    if (!result.hidden) calculate();
  });

  function calculate() {
    const aov = parseFloat(aovEl.value);
    const revenue = parseFloat(revEl.value);
    const adSpend = parseFloat(adEl.value);

    if (!(aov > 0) || !(revenue > 0)) {
      (aov > 0 ? revEl : aovEl).focus();
      return;
    }

    const newAov = aov * (1 + UPLIFT);
    const missing = revenue * UPLIFT;
    const newRevenue = revenue + missing;

    missingEl.textContent = money(missing);
    newAovEl.textContent = money(newAov);
    newRevEl.textContent = money(newRevenue);

    adNoteEl.textContent = adSpend > 0
      ? 'That is on top of the ' + money(adSpend) + '/mo you already spend on ads. No increase needed to get it.'
      : 'No extra ad spend needed to get there.';

    result.hidden = false;
    result.classList.remove('calc-pop');
    void result.offsetWidth;
    result.classList.add('calc-pop');
  }

  btn.addEventListener('click', calculate);
  [aovEl, revEl, adEl].forEach(el => el.addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); calculate(); }
  }));
})();
