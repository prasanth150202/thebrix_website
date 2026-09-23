/**
 * Hero AI demo on /book-a-demo: one prompt, one cart, one shared timeline.
 *
 * main.js already ships two separate demos that this borrows the look of:
 * heroCart() (the live cart drawer) and chatDemo() (the dark #aiChat typing
 * loop on /features). Those run as two independent loops with no
 * relationship to each other. The whole point here is causality: the
 * visitor should see typing INTO Brix AI cause the cart to change, so this
 * is one merged cycle instead, on its own ids (#aiDemo, #aiCart, ...) so it
 * never collides with either of those existing demos if they ever share a
 * page.
 *
 * The cart itself (.rc-*, in css/styles.css) is a recreation of the real
 * Brix Cart Editor preview: shipping band, timer, rewards bar, two coupon
 * cards, item rows and a checkout button. Every element the cycle touches
 * is already the size it will ever be. Nothing here ever toggles `display`
 * or collapses a height, only fill width, colour, text and small icon
 * states, specifically so the page around this card never shifts while it
 * plays.
 *
 * Relies on `burstConfetti` and `REDUCED`, both defined at the top level of
 * main.js, which loads before this file.
 */
(function heroAiDemo() {
  const wrap = document.getElementById('aiDemo');
  const cart = document.getElementById('aiCart');
  if (!wrap || !cart) return;

  const el = id => document.getElementById(id);
  const promptText = el('aiPromptText');
  const caret = el('aiPromptCaret');
  const typing = el('aiTyping');
  const reply = el('aiReply');
  const result = el('aiResult');

  const msg = el('aiCmMsg'), fill = el('aiCmFill'), nodeShip = el('aiNodeShip');
  const subtotal = el('aiCmSubtotal'), total = el('aiCmTotal');
  const count = el('aiCmCount'), itemsCount = el('aiItemsCount');
  const upsell = el('aiCmUpsell'), addBtn = el('aiCmAdd'), canvas = el('aiCmConfetti');

  const PROMPT = 'Increase my AOV';
  const START_MSG = 'You’re <b>$18.00</b> away from unlocking <b>Free Shipping</b>!';
  const UNLOCK_MSG = '<b>Free shipping unlocked!</b> Nice work.';
  const START_FILL = '55%';
  const END_FILL = '88%';

  const setMsg = html => {
    msg.innerHTML = html;
    msg.classList.remove('msg-pop');
    void msg.offsetWidth;
    msg.classList.add('msg-pop');
  };

  const nodeBurst = (node, n, power) => {
    const cr = canvas.getBoundingClientRect();
    const nr = node.getBoundingClientRect();
    burstConfetti(canvas, nr.left - cr.left + nr.width / 2, nr.top - cr.top + nr.height / 2, n, power);
  };

  const finalState = () => {
    promptText.textContent = PROMPT;
    caret.style.display = 'none';
    reply.classList.add('show');
    upsell.classList.add('is-added');
    addBtn.textContent = 'Added';
    nodeShip.classList.add('is-unlocked');
    fill.style.width = END_FILL;
    subtotal.textContent = '$124.00';
    total.textContent = '$124.00';
    count.textContent = '3';
    itemsCount.textContent = '3 ITEMS';
    msg.innerHTML = UNLOCK_MSG;
    result.classList.add('show');
  };

  if (REDUCED) { finalState(); return; }

  let timers = [];
  const at = (ms, fn) => timers.push(setTimeout(fn, ms));

  const reset = () => {
    promptText.textContent = '';
    caret.style.display = '';
    typing.classList.remove('show');
    reply.classList.remove('show');
    result.classList.remove('show');
    upsell.classList.remove('is-added');
    addBtn.classList.remove('pressed');
    addBtn.textContent = 'Add';
    nodeShip.classList.remove('is-unlocked');
    fill.style.width = START_FILL;
    subtotal.textContent = '$82.00';
    total.textContent = '$82.00';
    count.textContent = '2';
    itemsCount.textContent = '2 ITEMS';
    msg.innerHTML = START_MSG;
  };

  const cycle = () => {
    timers = [];

    // type the prompt into Brix AI
    PROMPT.split('').forEach((ch, i) => {
      at(300 + i * 45, () => { promptText.textContent += ch; });
    });
    const doneTyping = 300 + PROMPT.length * 45;

    at(doneTyping + 300, () => {
      caret.style.display = 'none';
      typing.classList.add('show');
    });

    const replyAt = doneTyping + 1200;
    at(replyAt, () => {
      typing.classList.remove('show');
      reply.classList.add('show');
    });

    // Brix AI acts on the cart: adds the Frequently Bought Together pick,
    // which is what pushes the subtotal past the shipping tier
    at(replyAt + 200, () => addBtn.classList.add('pressed'));
    at(replyAt + 600, () => {
      addBtn.classList.remove('pressed');
      upsell.classList.add('is-added');
      addBtn.textContent = 'Added';
      subtotal.textContent = '$124.00';
      total.textContent = '$124.00';
      count.textContent = '3';
      itemsCount.textContent = '3 ITEMS';
      fill.style.width = END_FILL;
    });
    at(replyAt + 1700, () => {
      nodeShip.classList.add('is-unlocked');
      nodeBurst(nodeShip, 34, 0.9);
      setMsg(UNLOCK_MSG);
    });
    at(replyAt + 2200, () => result.classList.add('show'));

    const resetAt = replyAt + 4600;
    at(resetAt, () => cart.classList.add('is-resetting'));
    at(resetAt + 400, () => {
      reset();
      cart.classList.remove('is-resetting');
    });
    at(resetAt + 900, cycle);
  };

  // run only while visible
  let running = false;
  const io = new IntersectionObserver(entries => {
    if (entries[0].isIntersecting && !running) {
      running = true;
      reset();
      cycle();
    } else if (!entries[0].isIntersecting && running) {
      running = false;
      timers.forEach(clearTimeout);
    }
  }, { threshold: 0.35 });
  io.observe(wrap);
})();
