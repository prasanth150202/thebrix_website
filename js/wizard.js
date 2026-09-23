/**
 * Step-by-step AOV calculator on /book-a-demo.
 *
 * Same math as js/calculator.js (revenue * 18.5%, the midpoint of Brix's
 * typical 17-20% AOV lift), just asked one question at a time instead of
 * as a single form. Reaching the result also copies the visitor's own
 * numbers into the hidden fields on the "get in touch" form below, so
 * whoever answers the enquiry already has the context without the
 * visitor typing it twice.
 */
(function aovWizard() {
  const wizard = document.getElementById('aovWizard');
  if (!wizard) return;

  const UPLIFT = 0.185;
  const steps = [...wizard.querySelectorAll('.wiz-step')];
  const total = steps.length;
  let current = 1;

  const progressFill = document.getElementById('wizProgressFill');
  const countEl = document.getElementById('wizCount');
  const errEl = document.getElementById('wizErr');
  const backBtn = document.getElementById('wizBack');
  const nextBtn = document.getElementById('wizNext');

  const currencyEl = document.getElementById('wizCurrency');
  const aovEl = document.getElementById('wizAov');
  const revEl = document.getElementById('wizRevenue');
  const adEl = document.getElementById('wizAdSpend');
  const symEls = wizard.querySelectorAll('.calc-currency-sym');

  const missingEl = document.getElementById('wizMissing');
  const newAovEl = document.getElementById('wizNewAov');
  const newRevEl = document.getElementById('wizNewRevenue');
  const adNoteEl = document.getElementById('wizAdNote');

  const money = n => currencyEl.value + Math.round(n).toLocaleString('en-US');

  function showStep(n) {
    steps.forEach(s => s.classList.toggle('is-active', Number(s.dataset.step) === n));
    progressFill.style.width = (n / total * 100) + '%';
    backBtn.hidden = n === 1;
    nextBtn.hidden = n === total;
    errEl.textContent = '';
    countEl.textContent = n < total ? 'Question ' + n + ' of ' + (total - 1) : 'Your result';
    const focusable = steps[n - 1].querySelector('input, select');
    if (focusable) focusable.focus({ preventScroll: true });
  }

  function validate(n) {
    if (n === 2 && !(parseFloat(aovEl.value) > 0)) return 'Enter your average order value to continue.';
    if (n === 3 && !(parseFloat(revEl.value) > 0)) return 'Enter your monthly revenue to continue.';
    return '';
  }

  function calculate() {
    const aov = parseFloat(aovEl.value);
    const revenue = parseFloat(revEl.value);
    const adSpend = parseFloat(adEl.value);

    const newAov = aov * (1 + UPLIFT);
    const missing = revenue * UPLIFT;
    const newRevenue = revenue + missing;

    missingEl.textContent = money(missing);
    newAovEl.textContent = money(newAov);
    newRevEl.textContent = money(newRevenue);
    adNoteEl.textContent = adSpend > 0
      ? 'That is on top of the ' + money(adSpend) + '/mo you already spend on ads. No increase needed to get it.'
      : 'No extra ad spend needed to get there.';

    syncEnquiryForm(aov, revenue, adSpend, missing);
  }

  /* Carries the visitor's own numbers into the "get in touch" form's
     hidden fields, if that form is present (it is absent once the
     lead has already been sent). Nothing here is required reading for
     the enquiry itself, so a missing field is never an error. */
  function syncEnquiryForm(aov, revenue, adSpend, missing) {
    const set = (id, value) => { const el = document.getElementById(id); if (el) el.value = value; };
    set('hfCurrency', currencyEl.options[currencyEl.selectedIndex].textContent);
    set('hfAov', money(aov));
    set('hfRevenue', money(revenue));
    set('hfAdSpend', adSpend > 0 ? money(adSpend) : '');
    set('hfMissing', money(missing));
  }

  nextBtn.addEventListener('click', () => {
    const msg = validate(current);
    if (msg) { errEl.textContent = msg; return; }
    if (current === total - 1) calculate();
    current = Math.min(current + 1, total);
    showStep(current);
  });

  backBtn.addEventListener('click', () => {
    current = Math.max(current - 1, 1);
    showStep(current);
  });

  currencyEl.addEventListener('change', () => {
    symEls.forEach(el => { el.textContent = currencyEl.value; });
  });

  wizard.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !nextBtn.hidden) { e.preventDefault(); nextBtn.click(); }
  });

  showStep(1);
})();
