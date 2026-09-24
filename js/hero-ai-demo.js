/**
 * Hero AI demo on /book-a-demo: one screen, chat cross-fading into cart.
 *
 * main.js already ships two separate demos that this borrows the look of:
 * heroCart() (the live cart drawer) and chatDemo() (the dark #aiChat typing
 * loop on /features). Those run as two independent loops with no
 * relationship to each other. The whole point here is causality: the
 * visitor types into Brix AI, reads the reply, and that same box then
 * turns into the cart it was working on in the background — already
 * carrying the rewards bar and the upsell it added — rather than a second
 * panel appearing next to the first. This is one merged cycle on its own
 * ids (#aiDemo, #aiStage, ...) so it never collides with either of those
 * existing demos if they ever share a page.
 *
 * The cart phase (.rc-*, in css/styles.css) is a recreation of the real
 * Brix Cart Editor preview, but it only ever renders its finished state —
 * there is no before/after inside the cart itself, since the chat phase
 * already told that story. .ai-phase-chat and .ai-phase-cart share one
 * grid cell (.ai-stage) and simply cross-fade; the stage's height is
 * always the taller of the two, so swapping the active phase never moves
 * anything around it.
 *
 * Relies on `burstConfetti` and `REDUCED`, both defined at the top level of
 * main.js, which loads before this file.
 */
(function heroAiDemo() {
  const wrap = document.getElementById('aiDemo');
  if (!wrap) return;

  const el = id => document.getElementById(id);
  const promptText = el('aiPromptText');
  const caret = el('aiPromptCaret');
  const typing = el('aiTyping');
  const reply = el('aiReply');
  const result = el('aiResult');
  const phaseChat = el('aiPhaseChat');
  const phaseCart = el('aiPhaseCart');
  const nodeShip = el('aiNodeShip');
  const canvas = el('aiCmConfetti');

  const PROMPT = 'Add a progress bar and upsells to my cart';

  const nodeBurst = (node, n, power) => {
    const cr = canvas.getBoundingClientRect();
    const nr = node.getBoundingClientRect();
    burstConfetti(canvas, nr.left - cr.left + nr.width / 2, nr.top - cr.top + nr.height / 2, n, power);
  };

  const finalState = () => {
    promptText.textContent = PROMPT;
    caret.style.display = 'none';
    reply.classList.add('show');
    phaseChat.classList.remove('is-active');
    phaseCart.classList.add('is-active');
    result.classList.add('show');
  };

  if (REDUCED) { finalState(); return; }

  let timers = [];
  const at = (ms, fn) => timers.push(setTimeout(fn, ms));

  const reset = () => {
    phaseCart.classList.remove('is-active');
    phaseChat.classList.add('is-active');
    result.classList.remove('show');
    promptText.textContent = '';
    caret.style.display = '';
    typing.classList.remove('show');
    reply.classList.remove('show');
  };

  const cycle = () => {
    timers = [];

    // type the prompt into Brix AI
    PROMPT.split('').forEach((ch, i) => {
      at(300 + i * 32, () => { promptText.textContent += ch; });
    });
    const doneTyping = 300 + PROMPT.length * 32;

    at(doneTyping + 300, () => {
      caret.style.display = 'none';
      typing.classList.add('show');
    });

    const replyAt = doneTyping + 1200;
    at(replyAt, () => {
      typing.classList.remove('show');
      reply.classList.add('show');
    });

    // Brix AI works in the background while the reply sits on screen,
    // then the whole box turns into the cart it just finished.
    const flipAt = replyAt + 1400;
    at(flipAt, () => {
      phaseChat.classList.remove('is-active');
      phaseCart.classList.add('is-active');
      nodeBurst(nodeShip, 34, 0.9);
    });
    at(flipAt + 500, () => result.classList.add('show'));

    const resetAt = flipAt + 4200;
    at(resetAt, () => wrap.classList.add('is-resetting'));
    at(resetAt + 400, () => {
      reset();
      wrap.classList.remove('is-resetting');
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
