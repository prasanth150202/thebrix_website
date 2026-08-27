/* ============================================================
   /demo: video playback and the scroll timeline
   ------------------------------------------------------------
   Two separate jobs, deliberately not tangled together.

   The video wall works with no GSAP and no animation at all, because a
   page whose videos depend on an animation library is a page that shows
   nothing when the CDN is slow.

   The motion is additive on top. Every animated element starts visible
   in the HTML and is hidden by gsap.set() only once GSAP is confirmed
   present, so a failed script or a reduced-motion setting leaves a
   perfectly readable page rather than an empty one. That is the reverse
   of the .reveal system in main.js, which hides in CSS first; on a page
   whose whole job is a form, content must never be able to vanish.
   ============================================================ */

(function demoPage() {
  const page = document.getElementById('demoPage');
  if (!page) return;

  /* ---------- video wall ---------- */

  const embed = id =>
    'https://www.youtube-nocookie.com/embed/' + id +
    '?autoplay=1&rel=0&modestbranding=1&playsinline=1&color=white';

  page.querySelectorAll('[data-video]').forEach(btn => {
    btn.addEventListener('click', () => {
      const frame = document.createElement('iframe');
      frame.className = 'dm-frame';
      frame.src = embed(btn.dataset.video);
      frame.title = btn.textContent.trim() || 'Brix video';
      frame.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
      frame.allowFullscreen = true;
      // Replaces the poster in place, so only the video asked for loads.
      btn.replaceWith(frame);
    });
  });

  /* ---------- motion ---------- */

  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduced || typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;

  gsap.registerPlugin(ScrollTrigger);

  const pick = sel => gsap.utils.toArray(page.querySelectorAll(sel));
  const ease = 'power3.out';

  /* Hero: one orchestrated entrance rather than a scattering of
     independent fades, so the page arrives as a single movement. */
  const heroCopy  = pick('[data-a="hero"]');
  const heroVideo = page.querySelector('[data-a="hero-video"]');

  if (heroCopy.length) gsap.set(heroCopy, { opacity: 0, y: 22 });
  if (heroVideo) gsap.set(heroVideo, { opacity: 0, y: 30, scale: .97 });

  const intro = gsap.timeline({ defaults: { ease, duration: .8 } });
  if (heroCopy.length) intro.to(heroCopy, { opacity: 1, y: 0, stagger: .09 }, .05);
  if (heroVideo) intro.to(heroVideo, { opacity: 1, y: 0, scale: 1, duration: 1 }, .3);

  /** Fade a set up into place as it arrives, in one batch per section. */
  const onEnter = (els, vars = {}) => {
    if (!els.length) return;
    gsap.set(els, { opacity: 0, y: vars.y ?? 26 });
    ScrollTrigger.batch(els, {
      start: 'top 88%',
      once: true,
      onEnter: batch => gsap.to(batch, {
        opacity: 1, y: 0, duration: .75, ease,
        stagger: vars.stagger ?? .08, overwrite: true
      })
    });
  };

  onEnter(pick('[data-a="head"]'), { y: 20, stagger: .06 });
  onEnter(pick('[data-a="num"]'), { y: 16, stagger: .07 });
  onEnter(pick('[data-a="card"]'), { y: 34, stagger: .07 });
  onEnter(pick('[data-a="step"]'), { y: 24, stagger: .12 });
  onEnter(pick('[data-a="form"]'), { y: 30 });

  /* Feature rows: copy and artwork come in from opposite sides, and the
     side swaps with the layout, so each row opens like a pair of doors
     rather than everything sliding the same way.

     The sideways offset only runs where the page has room for it. The
     container is 1160px wide with 24px of padding, so below about
     1180px of viewport a 34px shift starts outside the document and
     widens the page; body has overflow-x hidden, which hides the
     symptom but leaves a scrollable html element on a phone. Narrower
     than that the rows are stacked anyway, where a horizontal slide
     says nothing, so they simply rise instead. */
  const SIDE_ROOM = 1180;
  const roomy = window.innerWidth >= SIDE_ROOM;

  pick('[data-a="row"]').forEach(row => {
    const flipped = row.classList.contains('dm-row-flip');
    const copy = row.querySelector('.dm-row-copy');
    const art  = row.querySelector('.dm-row-art');
    if (!copy || !art) return;

    if (roomy) {
      gsap.set(copy, { opacity: 0, x: flipped ? 34 : -34 });
      gsap.set(art,  { opacity: 0, x: flipped ? -34 : 34, scale: .97 });
    } else {
      gsap.set(copy, { opacity: 0, y: 26 });
      gsap.set(art,  { opacity: 0, y: 26, scale: .98 });
    }

    gsap.timeline({
      scrollTrigger: { trigger: row, start: 'top 78%', once: true },
      defaults: { ease, duration: .8 }
    })
      .to(copy, { opacity: 1, x: 0, y: 0 })
      .to(art,  { opacity: 1, x: 0, y: 0, scale: 1 }, .12);
  });

  /* The line behind the three steps draws itself as you scroll past, so
     the sequence reads as a sequence. Decorative only: the numbers say
     the same thing without it. */
  const line = page.querySelector('.dm-steps-line i');
  if (line) {
    gsap.set(line, { scaleX: 0, transformOrigin: 'left center' });
    gsap.to(line, {
      scaleX: 1,
      ease: 'none',
      scrollTrigger: {
        trigger: '.dm-steps',
        start: 'top 72%',
        end: 'bottom 62%',
        scrub: .4
      }
    });
  }

  /* A slow drift on the hero glow, matching what main.js does on the
     other pages so this one does not feel mechanically still. */
  const glow = page.querySelector('.hero-glow');
  if (glow) {
    gsap.to(glow, {
      yPercent: 14,
      ease: 'none',
      scrollTrigger: { trigger: glow.parentElement, start: 'top top', end: 'bottom top', scrub: true }
    });
  }

  /* Sections above the fold settle before ScrollTrigger measures them. */
  ScrollTrigger.refresh();
})();
