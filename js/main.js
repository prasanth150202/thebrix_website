/* ============================================================
   BRIX — site behavior
   ============================================================ */

const REDUCED = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/* GSAP drives reveals, count-ups, deep-dive scrub and parallax when present;
   every consumer below falls back to the vanilla path if it isn't */
const HAS_GSAP = !REDUCED && typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined';
if (HAS_GSAP) {
  gsap.registerPlugin(ScrollTrigger);
  document.body.classList.add('gsap-on');
}

/* ---------- nav: blur on scroll, burger drawer, active link ---------- */

const nav = document.getElementById('nav');
const onScroll = () => nav.classList.toggle('scrolled', window.scrollY > 8);
onScroll();
window.addEventListener('scroll', onScroll, { passive: true });

const burger = document.getElementById('navBurger');
const drawer = document.getElementById('navDrawer');
if (burger && drawer) {
  burger.addEventListener('click', () => {
    const open = drawer.classList.toggle('open');
    burger.classList.toggle('open', open);
    burger.setAttribute('aria-expanded', open);
    document.body.classList.toggle('drawer-open', open);
  });
  const closeDrawer = () => {
    drawer.classList.remove('open');
    burger.classList.remove('open');
    burger.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('drawer-open');
  };

  drawer.querySelectorAll('a').forEach(a => a.addEventListener('click', closeDrawer));

  // floating menu: tap outside or press Escape to dismiss
  document.addEventListener('click', e => {
    if (!drawer.classList.contains('open')) return;
    if (drawer.contains(e.target) || burger.contains(e.target)) return;
    closeDrawer();
  });
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && drawer.classList.contains('open')) closeDrawer();
  });
}

const here = location.pathname.split('/').pop() || 'index.html';
document.querySelectorAll('.nav-links a').forEach(a => {
  if (a.getAttribute('href') === here) a.classList.add('active');
});

/* ---------- scroll reveals + hero entrance ---------- */

const revealEls = document.querySelectorAll('.reveal');
if (REDUCED) {
  revealEls.forEach(el => el.classList.add('in'));
} else if (HAS_GSAP) {
  const heroText = [...document.querySelectorAll('.hero-copy .reveal, .page-hero .reveal')];
  const heroVisual = document.querySelector('.hero-visual');
  const inHero = new Set(heroVisual ? [...heroText, heroVisual] : heroText);
  const rest = [...revealEls].filter(el => !inHero.has(el));

  ScrollTrigger.batch(rest, {
    start: 'top 88%',
    once: true,
    onEnter: els => gsap.to(els, {
      opacity: 1, y: 0, duration: .8, ease: 'power3.out', stagger: .08, overwrite: true
    })
  });

  const tl = gsap.timeline({ defaults: { ease: 'power3.out', duration: .85 } });
  if (heroText.length) tl.to(heroText, { opacity: 1, y: 0, stagger: .1 }, .05);
  if (heroVisual) tl.to(heroVisual, { opacity: 1, y: 0, duration: 1.05 }, .35);
} else {
  const ro = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add('in');
        ro.unobserve(e.target);
      }
    });
  }, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });
  revealEls.forEach(el => ro.observe(el));
}

/* ---------- parallax / ambient drift (GSAP only) ---------- */

if (HAS_GSAP) {
  const glow = document.querySelector('.hero-glow');
  if (glow) {
    gsap.to(glow, {
      yPercent: 16, ease: 'none',
      scrollTrigger: { trigger: glow.parentElement, start: 'top top', end: 'bottom top', scrub: true }
    });
  }
  document.querySelectorAll('.hero-chip').forEach((chip, i) => {
    gsap.to(chip, { y: i ? 9 : -9, duration: 2.6 + i * .5, yoyo: true, repeat: -1, ease: 'sine.inOut' });
    gsap.to(chip, {
      x: i ? -26 : 26, ease: 'none',
      scrollTrigger: { trigger: '.hero', start: 'top top', end: 'bottom top', scrub: true }
    });
  });
  const aiGrid = document.querySelector('#ai-chat .container');
  if (aiGrid) {
    gsap.fromTo(aiGrid, { yPercent: 5 }, {
      yPercent: -5, ease: 'none',
      scrollTrigger: { trigger: '#ai-chat', start: 'top bottom', end: 'bottom top', scrub: true }
    });
  }
  const ctaIn = document.querySelector('.cta-in');
  if (ctaIn) {
    gsap.fromTo(ctaIn, { yPercent: 10 }, {
      yPercent: -10, ease: 'none',
      scrollTrigger: { trigger: '.cta-final', start: 'top bottom', end: 'bottom top', scrub: true }
    });
  }
}

/* ---------- count-up stats ---------- */

const fmt = (n, dec) => n.toLocaleString('en-US', { minimumFractionDigits: dec, maximumFractionDigits: dec });

document.querySelectorAll('[data-count]').forEach(el => {
  const target = parseFloat(el.dataset.count);
  const dec = (el.dataset.count.split('.')[1] || '').length;
  const prefix = el.dataset.prefix || '';
  const suffix = el.dataset.suffix || '';
  const done = () => { el.textContent = prefix + fmt(target, dec) + suffix; };

  if (REDUCED) { done(); return; }

  if (HAS_GSAP) {
    const obj = { v: 0 };
    ScrollTrigger.create({
      trigger: el, start: 'top 85%', once: true,
      onEnter: () => gsap.to(obj, {
        v: target, duration: 1.5, ease: 'power2.out',
        onUpdate: () => { el.textContent = prefix + fmt(obj.v, dec) + suffix; },
        onComplete: done
      })
    });
    return;
  }

  const io = new IntersectionObserver(entries => {
    if (!entries[0].isIntersecting) return;
    io.disconnect();
    const t0 = performance.now();
    const dur = 1500;
    const tick = now => {
      const p = Math.min((now - t0) / dur, 1);
      const eased = 1 - Math.pow(1 - p, 3);
      el.textContent = prefix + fmt(target * eased, dec) + suffix;
      if (p < 1) requestAnimationFrame(tick); else done();
    };
    requestAnimationFrame(tick);
  }, { threshold: 0.5 });
  io.observe(el);
});

/* ---------- confetti engine ---------- */

const CONFETTI_COLORS = ['#0E9BE5', '#22C55E', '#14B8A6', '#FFB43C', '#5EEAD4'];

function sizeCanvas(canvas) {
  const dpr = Math.min(window.devicePixelRatio || 1, 2);
  const r = canvas.getBoundingClientRect();
  canvas.width = r.width * dpr;
  canvas.height = r.height * dpr;
  return { ctx: canvas.getContext('2d'), dpr, rect: r };
}

const confettiRuns = new WeakMap();

function burstConfetti(canvas, x, y, count = 42, power = 1) {
  if (REDUCED || !canvas) return;
  const { ctx, dpr } = sizeCanvas(canvas);
  const parts = [];
  for (let i = 0; i < count; i++) {
    const angle = -Math.PI / 2 + (Math.random() - 0.5) * Math.PI * 1.1;
    const speed = (2.4 + Math.random() * 4.4) * power;
    parts.push({
      x, y,
      vx: Math.cos(angle) * speed,
      vy: Math.sin(angle) * speed,
      g: 0.14 + Math.random() * 0.05,
      rot: Math.random() * Math.PI * 2,
      vr: (Math.random() - 0.5) * 0.3,
      color: CONFETTI_COLORS[i % CONFETTI_COLORS.length],
      w: 4 + Math.random() * 5,
      h: 3 + Math.random() * 4,
      circle: Math.random() < 0.3,
      life: 0,
      ttl: 65 + Math.random() * 35
    });
  }

  // one animation loop per canvas; new bursts join the active particle pool
  let pool = confettiRuns.get(canvas);
  if (pool) { pool.parts.push(...parts); return; }
  pool = { parts };
  confettiRuns.set(canvas, pool);

  const step = () => {
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    pool.parts = pool.parts.filter(p => p.life < p.ttl);
    if (!pool.parts.length) {
      confettiRuns.delete(canvas);
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      return;
    }
    pool.parts.forEach(p => {
      p.life++;
      p.x += p.vx;
      p.y += p.vy;
      p.vy += p.g;
      p.vx *= 0.985;
      p.rot += p.vr;
      const fade = 1 - Math.max(0, (p.life / p.ttl - 0.6) / 0.4);
      ctx.save();
      ctx.globalAlpha = fade;
      ctx.translate(p.x, p.y);
      ctx.rotate(p.rot);
      ctx.fillStyle = p.color;
      if (p.circle) {
        ctx.beginPath();
        ctx.arc(0, 0, p.w / 2, 0, Math.PI * 2);
        ctx.fill();
      } else {
        ctx.fillRect(-p.w / 2, -p.h / 2, p.w, p.h);
      }
      ctx.restore();
    });
    requestAnimationFrame(step);
  };
  requestAnimationFrame(step);
}

/* global fixed canvas for bursts anywhere on the page */
let globalCanvas = null;
function globalBurst(clientX, clientY, count = 60, power = 1.25) {
  if (REDUCED) return;
  if (!globalCanvas) {
    globalCanvas = document.createElement('canvas');
    globalCanvas.className = 'confetti-canvas';
    globalCanvas.style.cssText = 'position:fixed;inset:0;width:100vw;height:100vh;pointer-events:none;z-index:9999;';
    document.body.appendChild(globalCanvas);
  }
  burstConfetti(globalCanvas, clientX, clientY, count, power);
}

/* pricing + CTA buttons celebrate */
document.querySelectorAll('.price-cta, #ctaInstall, .btn-primary.btn-lg').forEach(btn => {
  btn.addEventListener('click', e => {
    if (btn.getAttribute('href') === '#') e.preventDefault();
    const r = btn.getBoundingClientRect();
    globalBurst(r.left + r.width / 2, r.top + r.height / 2);
  });
});

/* ---------- hero cart demo loop ---------- */

(function heroCart() {
  const cart = document.getElementById('heroCart');
  if (!cart) return;

  const el = id => document.getElementById(id);
  const msg = el('cmMsg'), fill = el('cmFill'), total = el('cmTotal'), count = el('cmCount');
  const nodeShip = el('nodeShip'), nodeGift = el('nodeGift');
  const item2 = el('item2'), item3 = el('item3'), itemGift = el('itemGift');
  const upsell = el('cmUpsell'), addBtn = el('cmAdd'), canvas = el('cmConfetti');

  const setMsg = html => {
    msg.innerHTML = html;
    msg.classList.remove('msg-pop');
    void msg.offsetWidth;
    msg.classList.add('msg-pop');
  };

  const nodeBurst = (node, count, power) => {
    const cr = canvas.getBoundingClientRect();
    const nr = node.getBoundingClientRect();
    burstConfetti(canvas, nr.left - cr.left + nr.width / 2, nr.top - cr.top + nr.height / 2, count, power);
  };

  const finalState = () => {
    item2.classList.add('is-in');
    item3.classList.add('is-in');
    itemGift.classList.add('is-in');
    upsell.classList.add('is-away');
    nodeShip.classList.add('is-unlocked');
    nodeGift.classList.add('is-unlocked');
    fill.style.width = '95.4%';
    total.textContent = '$124.00';
    count.textContent = '3 items';
    msg.innerHTML = '<b>Free gift unlocked!</b> Sherpa socks added';
  };

  if (REDUCED) { finalState(); return; }

  const reset = () => {
    item2.classList.remove('is-in');
    item3.classList.remove('is-in');
    itemGift.classList.remove('is-in');
    upsell.classList.remove('is-away');
    addBtn.classList.remove('pressed');
    nodeShip.classList.remove('is-unlocked');
    nodeGift.classList.remove('is-unlocked');
    fill.style.width = '37%';
    total.textContent = '$48.00';
    count.textContent = '1 item';
    msg.innerHTML = 'You’re <b>$27.00</b> away from <b>free shipping</b>';
  };

  let timers = [];
  const at = (ms, fn) => timers.push(setTimeout(fn, ms));

  const cycle = () => {
    timers = [];
    at(1600, () => addBtn.classList.add('pressed'));
    at(2000, () => {
      item2.classList.add('is-in');
      upsell.classList.add('is-away');
      total.textContent = '$82.00';
      count.textContent = '2 items';
      fill.style.width = '63%';
    });
    at(2700, () => {
      nodeShip.classList.add('is-unlocked');
      nodeBurst(nodeShip, 34, 0.9);
      setMsg('<b>Free shipping unlocked!</b> $38.00 to a free gift');
    });
    at(4600, () => {
      item3.classList.add('is-in');
      total.textContent = '$124.00';
      count.textContent = '3 items';
      fill.style.width = '95.4%';
    });
    at(5300, () => {
      nodeGift.classList.add('is-unlocked');
      nodeBurst(nodeGift, 52, 1.15);
      setMsg('<b>Free gift unlocked!</b> Sherpa socks added');
      itemGift.classList.add('is-in');
    });
    at(8200, () => cart.classList.add('is-resetting'));
    at(8600, () => {
      reset();
      cart.classList.remove('is-resetting');
    });
    at(9100, cycle);
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
  io.observe(cart);
})();

/* ---------- Brix AI chat loop ---------- */

(function chatDemo() {
  const box = document.getElementById('aiChat');
  if (!box) return;

  const userText = document.getElementById('cbUserText');
  const caret = document.getElementById('cbCaret');
  const typing = document.getElementById('cbTyping');
  const ai = document.getElementById('cbAi');
  const action = document.getElementById('cbAction');
  const MESSAGE = 'Increase my AOV by 15% this month.';

  const finalState = () => {
    userText.textContent = MESSAGE;
    caret.style.display = 'none';
    ai.classList.add('show');
    action.classList.add('show');
  };

  if (REDUCED) { finalState(); return; }

  let timers = [];
  const at = (ms, fn) => timers.push(setTimeout(fn, ms));

  const reset = () => {
    userText.textContent = '';
    caret.style.display = '';
    typing.classList.remove('show');
    ai.classList.remove('show');
    action.classList.remove('show');
  };

  const cycle = () => {
    timers = [];
    reset();
    MESSAGE.split('').forEach((ch, i) => {
      at(500 + i * 42, () => { userText.textContent += ch; });
    });
    const doneTyping = 500 + MESSAGE.length * 42;
    at(doneTyping + 350, () => {
      caret.style.display = 'none';
      typing.classList.add('show');
    });
    at(doneTyping + 1900, () => {
      typing.classList.remove('show');
      ai.classList.add('show');
    });
    at(doneTyping + 2900, () => action.classList.add('show'));
    at(doneTyping + 7200, cycle);
  };

  let running = false;
  const io = new IntersectionObserver(entries => {
    if (entries[0].isIntersecting && !running) {
      running = true;
      cycle();
    } else if (!entries[0].isIntersecting && running) {
      running = false;
      timers.forEach(clearTimeout);
      reset();
    }
  }, { threshold: 0.4 });
  io.observe(box);
})();

/* ---------- why-brix chat panel: gsap entrance ---------- */

(function whyBrixChat() {
  const panel = document.getElementById('wbPanel');
  if (!panel || !HAS_GSAP) return;

  const typing = document.getElementById('wbTyping');
  const bubbles = document.querySelectorAll('#wbThread .wb-bubble');
  if (!bubbles.length) return;

  gsap.set(bubbles, { opacity: 0, y: 12 });

  ScrollTrigger.create({
    trigger: panel,
    start: 'top 75%',
    once: true,
    onEnter: () => {
      const tl = gsap.timeline();
      tl.call(() => typing.classList.add('show'))
        .to({}, { duration: .7 })
        .call(() => typing.classList.remove('show'))
        .to(bubbles, {
          opacity: 1, y: 0, duration: .5, ease: 'power2.out', stagger: .13
        });
    }
  });
})();

/* ---------- why-aov meters: gsap fill on scroll ---------- */

(function whyAovMeters() {
  const card = document.getElementById('waovCard');
  if (!card || !HAS_GSAP) return;

  const fills = card.querySelectorAll('.waov-meter-fill');
  const targets = [...fills].map(f => f.style.width);
  gsap.set(fills, { width: '0%' });

  ScrollTrigger.create({
    trigger: card,
    start: 'top 75%',
    once: true,
    onEnter: () => {
      fills.forEach((f, i) => {
        gsap.to(f, { width: targets[i], duration: 1.1, ease: 'power2.out', delay: i * .18 });
      });
    }
  });
})();

/* ---------- why-aov chips: gsap stagger pop-in ---------- */

(function whyAovChips() {
  const wrap = document.getElementById('waovChips');
  if (!wrap || !HAS_GSAP) return;

  const chips = wrap.querySelectorAll('.waov-chip');
  if (!chips.length) return;
  gsap.set(chips, { opacity: 0, y: 10, scale: .92 });

  ScrollTrigger.create({
    trigger: wrap,
    start: 'top 85%',
    once: true,
    onEnter: () => gsap.to(chips, {
      opacity: 1, y: 0, scale: 1, duration: .45, ease: 'back.out(1.7)', stagger: .05
    })
  });
})();

/* ---------- before/after: gsap directional stagger ---------- */

(function beforeAfterReveal() {
  const grid = document.querySelector('#before-after .ba-grid');
  if (!grid || !HAS_GSAP) return;

  const before = grid.querySelectorAll('.ba-before .ba-list li');
  const after = grid.querySelectorAll('.ba-after .ba-list li');
  if (!before.length && !after.length) return;
  gsap.set(before, { opacity: 0, x: -18 });
  gsap.set(after, { opacity: 0, x: 18 });

  ScrollTrigger.create({
    trigger: grid,
    start: 'top 72%',
    once: true,
    onEnter: () => {
      gsap.to(before, { opacity: 1, x: 0, duration: .5, ease: 'power2.out', stagger: .07 });
      gsap.to(after, { opacity: 1, x: 0, duration: .5, ease: 'power2.out', stagger: .07, delay: .18 });
    }
  });
})();

/* ---------- trusted-by: gsap country chip pop-in ---------- */

(function countriesReveal() {
  const wrap = document.querySelector('.countries-row');
  if (!wrap || !HAS_GSAP) return;

  const chips = wrap.querySelectorAll('.country-chip');
  if (!chips.length) return;
  gsap.set(chips, { opacity: 0, y: 8, scale: .85 });

  ScrollTrigger.create({
    trigger: wrap,
    start: 'top 82%',
    once: true,
    onEnter: () => gsap.to(chips, {
      opacity: 1, y: 0, scale: 1, duration: .4, ease: 'back.out(1.8)', stagger: .05
    })
  });
})();

/* ---------- case study teaser: gsap entrance ---------- */

(function csTeaserReveal() {
  const el = document.getElementById('csTeaser');
  if (!el || !HAS_GSAP) return;
  gsap.set(el, { opacity: 0, y: 16, scale: .97 });

  ScrollTrigger.create({
    trigger: el,
    start: 'top 82%',
    once: true,
    onEnter: () => gsap.to(el, { opacity: 1, y: 0, scale: 1, duration: .6, ease: 'power3.out' })
  });
})();

/* ---------- bundle builder: gsap parallax (mirrors brix-ai treatment) ---------- */

(function bundleParallax() {
  if (!HAS_GSAP) return;
  const mock = document.querySelector('#bundles .builder-mock');
  if (!mock) return;
  gsap.fromTo(mock, { yPercent: 4 }, {
    yPercent: -4, ease: 'none',
    scrollTrigger: { trigger: '#bundles', start: 'top bottom', end: 'bottom top', scrub: true }
  });
})();

/* ---------- analytics: gsap line-chart draw-in ---------- */

(function analyticsChartDraw() {
  if (!HAS_GSAP) return;
  const line = document.querySelector('#analytics .chart-line');
  if (!line) return;
  const len = line.getTotalLength();
  gsap.set(line, { strokeDasharray: len, strokeDashoffset: len });

  ScrollTrigger.create({
    trigger: '#analytics .dash-chart',
    start: 'top 75%',
    once: true,
    onEnter: () => gsap.to(line, { strokeDashoffset: 0, duration: 1.4, ease: 'power2.inOut' })
  });
})();

/* ---------- deep-dive: scroll-driven cart states ---------- */

if (HAS_GSAP) {
  const mm = gsap.matchMedia();
  mm.add('(min-width: 1021px)', () => {
    const grid = document.querySelector('#cart-editor .dd-grid');
    if (!grid) return;
    const steps = grid.querySelectorAll('.dd-step');
    const callouts = grid.querySelectorAll('.dd-callout');
    const msg = grid.querySelector('.dd-visual .cm-msg');
    const fill = grid.querySelector('.dd-visual .cm-fill');
    const nodes = grid.querySelectorAll('.dd-visual .cm-node');
    const MSGS = [
      'You’re <b>$27.00</b> away from <b>free shipping</b>',
      '<b>Free shipping unlocked!</b> $38.00 to a free gift',
      '<b>Free gift unlocked!</b> Sherpa socks added'
    ];
    let last = -1;

    const st = ScrollTrigger.create({
      trigger: grid,
      start: 'top 45%',
      end: 'bottom 60%',
      onUpdate(self) {
        const p = self.progress;
        const w = 37 + (95.4 - 37) * p;
        fill.style.width = w + '%';
        nodes[0]?.classList.toggle('is-unlocked', w >= 57.7);
        nodes[1]?.classList.toggle('is-unlocked', w >= 92.3);
        const idx = Math.min(2, Math.floor(p * 3));
        if (idx !== last) {
          last = idx;
          grid.classList.add('has-active');
          steps.forEach((s, i) => s.classList.toggle('active', i === idx));
          callouts.forEach(c => c.classList.toggle('active', c.dataset.for === String(idx + 1)));
          if (msg) msg.innerHTML = MSGS[idx];
        }
      }
    });
    return () => st.kill();
  });
} else {
  document.querySelectorAll('.dd-grid').forEach(grid => {
    const steps = grid.querySelectorAll('.dd-step');
    if (!steps.length) return;
    const callouts = grid.querySelectorAll('.dd-callout');

    const io = new IntersectionObserver(entries => {
      entries.forEach(e => {
        if (!e.isIntersecting) return;
        grid.classList.add('has-active');
        steps.forEach(s => s.classList.toggle('active', s === e.target));
        const n = e.target.dataset.step;
        callouts.forEach(c => c.classList.toggle('active', c.dataset.for === n));
      });
    }, { rootMargin: '-38% 0px -38% 0px' });

    steps.forEach(s => io.observe(s));
  });
}

/* ---------- AOV chart hover ---------- */

(function chartHover() {
  const wrap = document.getElementById('aovChart');
  if (!wrap) return;
  const hover = document.getElementById('chartHover');
  const cross = hover.querySelector('.chart-crosshair');
  const dot = hover.querySelector('.chart-dot');
  const tip = hover.querySelector('.chart-tip');
  const tipB = tip.querySelector('b');
  const tipS = tip.querySelector('small');

  const VB_W = 640, VB_H = 240;
  const pts = [
    { x: 64,  y: 156.3, label: '$58.10', month: 'January' },
    { x: 170, y: 143.9, label: '$61.30', month: 'February' },
    { x: 276, y: 119.1, label: '$67.20', month: 'March' },
    { x: 382, y: 98.4,  label: '$72.40', month: 'April' },
    { x: 488, y: 69.5,  label: '$79.10', month: 'May' },
    { x: 594, y: 40.5,  label: '$86.40', month: 'June' }
  ];

  wrap.addEventListener('pointermove', e => {
    const r = wrap.getBoundingClientRect();
    const vx = (e.clientX - r.left) / r.width * VB_W;
    let best = pts[0];
    pts.forEach(p => { if (Math.abs(p.x - vx) < Math.abs(best.x - vx)) best = p; });
    const px = best.x / VB_W * r.width;
    const py = best.y / VB_H * r.height;
    cross.style.left = px + 'px';
    dot.style.left = px + 'px';
    dot.style.top = py + 'px';
    tip.style.left = px + 'px';
    tip.style.top = py + 'px';
    tipB.textContent = best.label;
    tipS.textContent = best.month + ' · avg order';
    hover.classList.add('on');
  });
  wrap.addEventListener('pointerleave', () => hover.classList.remove('on'));
})();

/* ---------- testimonial carousel ---------- */

(function carousel() {
  const track = document.getElementById('carousel');
  if (!track) return;
  const step = () => {
    const card = track.querySelector('.t-card');
    return card ? card.getBoundingClientRect().width + 22 : 340;
  };
  document.getElementById('carPrev')?.addEventListener('click', () =>
    track.scrollBy({ left: -step(), behavior: REDUCED ? 'auto' : 'smooth' }));
  document.getElementById('carNext')?.addEventListener('click', () =>
    track.scrollBy({ left: step(), behavior: REDUCED ? 'auto' : 'smooth' }));
})();

/* ---------- newsletter ---------- */

document.getElementById('newsForm')?.addEventListener('submit', e => {
  e.preventDefault();
  const form = e.target;
  form.querySelector('input').disabled = true;
  form.querySelector('button').disabled = true;
  document.getElementById('newsOk')?.classList.add('show');
});
