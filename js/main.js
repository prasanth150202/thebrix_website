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

/* ---------- why-brix before/after pictorial: gsap entrance ---------- */

(function beforeAfterPict() {
  const pict = document.querySelector('.ba-pict');
  if (!pict || !HAS_GSAP) return;

  const before = pict.querySelector('.is-before');
  const arrow = pict.querySelector('.ba-pict-arrow');
  const after = pict.querySelector('.is-after');
  const fill = pict.querySelector('.bp-fill');
  const pops = after ? after.querySelectorAll('.bp-upsell, .bp-coupons') : [];

  gsap.set(before, { opacity: 0, x: -26 });
  gsap.set(after, { opacity: 0, x: 26 });
  gsap.set(arrow, { opacity: 0, scale: 0 });
  gsap.set(pops, { opacity: 0, y: 8 });
  if (fill) gsap.set(fill, { width: '18%' });

  ScrollTrigger.create({
    trigger: pict,
    start: 'top 82%',
    once: true,
    onEnter: () => {
      const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });
      tl.to(before, { opacity: 1, x: 0, duration: .6 })
        .to(after, { opacity: 1, x: 0, duration: .6 }, '-=.42')
        .to(arrow, { opacity: 1, scale: 1, duration: .5, ease: 'back.out(2.2)' }, '-=.4')
        .to(fill, { width: '95%', duration: 1.1, ease: 'power2.out' }, '-=.25')
        .to(pops, { opacity: 1, y: 0, duration: .45, ease: 'back.out(1.7)', stagger: .12 }, '-=.85');
    }
  });
})();

/* ---------- countries world map: gsap draw-in + pin drop ---------- */

(function worldMapReveal() {
  const svg = document.querySelector('.wm-svg');
  if (!svg || !HAS_GSAP) return;

  const land = svg.querySelectorAll('.wm-land path, .wm-land circle');
  const arcs = svg.querySelectorAll('.wm-arcs path');
  const pins = svg.querySelectorAll('.wm-pin');
  const legend = document.querySelectorAll('.wm-legend .country-chip');

  gsap.set(land, { opacity: 0 });
  gsap.set(arcs, { opacity: 0 });
  gsap.set(pins, { opacity: 0, y: -12 });
  gsap.set(legend, { opacity: 0, y: 8, scale: .85 });

  ScrollTrigger.create({
    trigger: svg,
    start: 'top 80%',
    once: true,
    onEnter: () => {
      const tl = gsap.timeline();
      tl.to(land, { opacity: 1, duration: .5, ease: 'power1.out', stagger: .06 })
        .to(arcs, { opacity: 1, duration: .6, ease: 'power1.out', stagger: .1 }, '-=.2')
        .to(pins, { opacity: 1, y: 0, duration: .5, ease: 'back.out(1.9)', stagger: .1 }, '-=.5')
        .to(legend, { opacity: 1, y: 0, scale: 1, duration: .4, ease: 'back.out(1.8)', stagger: .05 }, '-=.5');
    }
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

/* ---------- floating Brix AI chat widget (site-wide) ---------- */

(function brixChat() {
  if (document.querySelector('.bx-launcher')) return;

  const QA = [
    { q: 'Will Brix slow down my store?',
      a: 'No. Brix loads asynchronously after your page renders and weighs less than a single product image (~28&nbsp;KB), so it has no effect on your Core Web Vitals.' },
    { q: 'Does it work with my theme?',
      a: 'Yes — Brix works with every Online Store 2.0 theme out of the box and matches your fonts and colours automatically. For heavily customised themes our team does free setup.' },
    { q: 'What does Brix AI change in my store?',
      a: 'Only what you allow it to: reward-tier amounts, which offers are active, and which products appear in upsells and bundles. Every change is logged and reversible in one click.' },
    { q: 'Does it work with subscription or currency apps?',
      a: 'Yes. Brix supports Shopify Markets, multi-currency and major subscription apps. Reward tiers convert to each shopper’s local currency automatically.' },
    { q: 'Can I try the paid features first?',
      a: 'Starter and Pro both start with a 14-day free trial, billed monthly through Shopify afterwards — cancel anytime. Free is free forever, no trial needed.' },
    { q: 'What happens if I uninstall?',
      a: 'Brix removes itself cleanly with no leftover code in your theme. Export your discounts and analytics first, and your store is exactly as it was.' }
  ];

  const GREETING = 'Hi! I’m Brix AI 👋 Ask me anything about growing your Shopify AOV — or tap a common question below.';
  const FALLBACK = 'Great question! I’m a preview assistant for now — a smarter Brix AI is on the way. In the meantime, tap one of the common questions above, or email <b>support@thebrix.io</b> and a human will help.';

  const launcher = document.createElement('button');
  launcher.className = 'bx-launcher';
  launcher.type = 'button';
  launcher.setAttribute('aria-label', 'Open Brix AI chat');
  launcher.innerHTML =
    '<span class="bx-launcher-ic"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 3C6.48 3 2 6.94 2 11.8c0 2.6 1.28 4.94 3.32 6.55L4.5 21.5l3.94-1.64c1.1.35 2.31.54 3.56.54 5.52 0 10-3.94 10-8.8S17.52 3 12 3z"/></svg></span>' +
    '<span class="bx-launcher-label">Ask Brix AI</span>' +
    '<span class="bx-launcher-dot" aria-hidden="true"></span>';

  const panel = document.createElement('div');
  panel.className = 'bx-panel';
  panel.setAttribute('role', 'dialog');
  panel.setAttribute('aria-label', 'Brix AI chat');
  panel.innerHTML =
    '<div class="bx-head">' +
      '<span class="bx-head-ava"><img src="assets/brix-mark-light.png" alt=""></span>' +
      '<span class="bx-head-tt"><b>Brix AI</b><span><span class="bx-head-live" aria-hidden="true"></span>Online · replies instantly</span></span>' +
      '<button class="bx-close" type="button" aria-label="Close chat"><svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M19 6.41 17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg></button>' +
    '</div>' +
    '<div class="bx-body" id="bxBody"></div>' +
    '<div class="bx-foot">' +
      '<div class="bx-inputrow">' +
        '<input class="bx-input" id="bxInput" type="text" placeholder="Ask about AOV, upsells, pricing…" aria-label="Type your question">' +
        '<button class="bx-send" id="bxSend" type="button" aria-label="Send message"><svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M2.01 21 23 12 2.01 3 2 10l15 2-15 2z"/></svg></button>' +
      '</div>' +
      '<p class="bx-note">Preview assistant — full Brix AI coming soon</p>' +
    '</div>';

  document.body.appendChild(launcher);
  document.body.appendChild(panel);

  // gsap pop-in from the corner (falls back to instant appearance)
  if (HAS_GSAP) {
    gsap.set(launcher, { scale: 0, opacity: 0, transformOrigin: 'bottom right' });
    gsap.to(launcher, { scale: 1, opacity: 1, duration: .55, ease: 'back.out(2)', delay: 1.2 });
  }

  const body = panel.querySelector('#bxBody');
  const input = panel.querySelector('#bxInput');
  const sendBtn = panel.querySelector('#bxSend');
  const closeBtn = panel.querySelector('.bx-close');
  const scrollDown = () => { body.scrollTop = body.scrollHeight; };

  const addMsg = (html, who) => {
    const m = document.createElement('div');
    m.className = 'bx-msg bx-msg-' + who;
    m.innerHTML = html;
    body.appendChild(m);
    scrollDown();
  };

  let suggestsEl = null;
  const removeSuggests = () => { if (suggestsEl) { suggestsEl.remove(); suggestsEl = null; } };
  const renderSuggests = () => {
    removeSuggests();
    suggestsEl = document.createElement('div');
    suggestsEl.className = 'bx-suggests';
    let html = '<p class="bx-suggests-label">Common questions</p>';
    QA.forEach((item, i) => { html += '<button class="bx-chip" type="button" data-i="' + i + '">' + item.q + '</button>'; });
    suggestsEl.innerHTML = html;
    body.appendChild(suggestsEl);
    scrollDown();
    suggestsEl.querySelectorAll('.bx-chip').forEach(chip => {
      chip.addEventListener('click', () => ask(QA[+chip.dataset.i].q, QA[+chip.dataset.i].a));
    });
  };

  const aiReply = html => {
    const done = () => { addMsg(html, 'ai'); renderSuggests(); };
    if (REDUCED) { done(); return; }
    const t = document.createElement('div');
    t.className = 'bx-typing';
    t.innerHTML = '<span></span><span></span><span></span>';
    body.appendChild(t);
    scrollDown();
    setTimeout(() => { t.remove(); done(); }, 750);
  };

  const ask = (question, answer) => {
    removeSuggests();
    addMsg(question, 'user');
    aiReply(answer);
  };

  let seeded = false;
  const seed = () => {
    if (seeded) return;
    seeded = true;
    addMsg(GREETING, 'ai');
    renderSuggests();
  };

  const open = () => {
    document.body.classList.add('bx-open');
    launcher.querySelector('.bx-launcher-dot')?.remove();
    seed();
    setTimeout(() => input.focus(), 260);
  };
  const close = () => document.body.classList.remove('bx-open');

  launcher.addEventListener('click', open);
  closeBtn.addEventListener('click', close);
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && document.body.classList.contains('bx-open')) close();
  });

  const send = () => {
    const val = input.value.trim();
    if (!val) return;
    input.value = '';
    const v = val.toLowerCase();
    const hit = QA.find(item =>
      item.q.toLowerCase().replace(/[^a-z ]/g, '').split(' ')
        .filter(w => w.length > 3).some(w => v.includes(w))
    );
    ask(val, hit ? hit.a : FALLBACK);
  };
  sendBtn.addEventListener('click', send);
  input.addEventListener('keydown', e => { if (e.key === 'Enter') send(); });
})();
