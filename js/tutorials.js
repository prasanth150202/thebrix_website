/* ============================================================
   /tutorials: the tutorial player
   ------------------------------------------------------------
   Only this page loads this file, so main.js is untouched and no
   other page pays for it.

   Two things happen here. Picking a lesson swaps the video in place
   instead of loading a page, and ticking a lesson off records it and
   moves the player on to the next one you have not watched, which is
   what makes the list feel like a series rather than a playlist.

   Progress lives in localStorage, keyed by video id rather than by
   position, so reordering the tutorials later does not scramble what
   somebody has already watched.
   ============================================================ */

(function tutorials() {
  const series = document.getElementById('tutSeries');
  const dataEl = document.getElementById('tutData');
  if (!series || !dataEl) return;

  let LESSONS;
  try {
    LESSONS = JSON.parse(dataEl.textContent);
  } catch (err) {
    return; // leave the server-rendered first lesson and the links as they are
  }
  if (!Array.isArray(LESSONS) || !LESSONS.length) return;

  const STORE = 'brix.tutorials.v1';
  const softMotion = typeof REDUCED !== 'undefined' && REDUCED ? 'auto' : 'smooth';

  const el = id => document.getElementById(id);
  const screen        = el('tutScreen');
  const posterImg     = el('tutPosterImg');
  const posterDur     = el('tutPosterDur');
  const posterName    = el('tutPosterName');
  const nowEyebrow    = el('tutNowEyebrow');
  const nowTitle      = el('tutNowTitle');
  const nowBlurb      = el('tutNowBlurb');
  const guideLink     = el('tutGuide');
  const completeBtn   = el('tutComplete');
  const completeLabel = el('tutCompleteLabel');
  const prevBtn       = el('tutPrev');
  const nextBtn       = el('tutNext');
  const bar           = el('tutBar');
  const count         = el('tutCount');
  const note          = el('tutNote');
  const resetBtn      = el('tutReset');
  const live          = el('tutLive');

  const rows = [...series.querySelectorAll('.tut-row')];

  /* Once the visitor has asked for a video, every later lesson starts
     playing on its own. Before that first click nothing autoplays, so
     the page never makes noise the visitor did not ask for. */
  let playing = false;
  let iframe  = null;
  let current = 0;

  /* ---------- stored progress ---------- */

  /* Private browsing and blocked site data both throw on access, and a
     tutorials page is not worth an exception, so every read and write is
     wrapped and simply falls back to progress that lasts the visit. */
  const read = () => {
    try {
      const raw = localStorage.getItem(STORE);
      return raw ? JSON.parse(raw) : null;
    } catch (err) {
      return null;
    }
  };

  const saved = read() || {};
  const done  = new Set(Array.isArray(saved.done) ? saved.done : []);

  const save = () => {
    try {
      localStorage.setItem(STORE, JSON.stringify({
        done: [...done],
        last: LESSONS[current].id
      }));
    } catch (err) {
      /* progress stays in memory for this visit */
    }
  };

  /* ---------- the player ---------- */

  const embed = id =>
    'https://www.youtube-nocookie.com/embed/' + id +
    '?autoplay=1&rel=0&modestbranding=1&playsinline=1&color=white';

  function mountFrame(id) {
    if (!iframe) {
      iframe = document.createElement('iframe');
      iframe.className = 'tut-frame';
      iframe.title = 'Brix tutorial video';
      iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
      iframe.allowFullscreen = true;
      screen.replaceChildren(iframe);
    }
    iframe.src = embed(id);
    playing = true;
  }

  /**
   * Show lesson `i`. `start` means play it: picking a lesson from the
   * syllabus is as much a request for the video as pressing play on the
   * poster is. Only the first paint and a link arriving with a lesson in
   * it are silent, so nothing ever makes noise before a click.
   */
  function show(i, start) {
    current = Math.max(0, Math.min(i, LESSONS.length - 1));
    const lesson = LESSONS[current];

    if (start) {
      mountFrame(lesson.id);
    } else if (!playing) {
      posterImg.src = 'https://i.ytimg.com/vi/' + lesson.id + '/maxresdefault.jpg';
      posterDur.textContent = lesson.clock;
      posterName.textContent = 'Play: ' + lesson.title;
    }

    nowEyebrow.textContent = lesson.module + ' · Lesson ' + (current + 1) + ' of ' + LESSONS.length;
    nowTitle.textContent   = lesson.title;
    nowBlurb.textContent   = lesson.blurb;

    if (lesson.guide) {
      guideLink.href = lesson.guide.href;
      guideLink.textContent = lesson.guide.label + ' →';
      guideLink.hidden = false;
    } else {
      guideLink.hidden = true;
    }

    prevBtn.disabled = current === 0;
    nextBtn.disabled = current === LESSONS.length - 1;

    render();
    save();
    stampUrl();
  }

  /** The first lesson at or after `from` that has not been ticked off. */
  function nextUnwatched(from) {
    for (let i = from; i < LESSONS.length; i++) {
      if (!done.has(LESSONS[i].id)) return i;
    }
    for (let i = 0; i < from; i++) {
      if (!done.has(LESSONS[i].id)) return i;
    }
    return -1;
  }

  /* ---------- painting the rail ---------- */

  function render() {
    rows.forEach((row, i) => {
      const isDone = done.has(LESSONS[i].id);
      row.classList.toggle('is-done', isDone);
      row.classList.toggle('is-playing', i === current);
      row.querySelector('.tut-check').checked = isDone;
      row.querySelector('.tut-open').setAttribute('aria-current', i === current ? 'true' : 'false');
    });

    const n = done.size;
    const all = n === LESSONS.length;
    count.textContent = n + ' / ' + LESSONS.length;
    bar.style.width = Math.round((n / LESSONS.length) * 100) + '%';
    series.classList.toggle('is-complete', all);

    note.textContent = all
      ? 'All done. That is every feature in Brix covered.'
      : n === 0
        ? 'Tick a lesson off when you have watched it. Progress is remembered on this device.'
        : (LESSONS.length - n) + (LESSONS.length - n === 1 ? ' lesson left.' : ' lessons left.') +
          ' Progress is remembered on this device.';

    resetBtn.hidden = n === 0;

    const currentDone = done.has(LESSONS[current].id);
    completeLabel.textContent = currentDone
      ? (all ? 'All lessons complete' : 'Continue to the next lesson')
      : 'Mark complete & continue';
    completeBtn.classList.toggle('is-done', currentDone);
    completeBtn.disabled = currentDone && all;
  }

  /* Keep the address bar on the lesson being watched so it can be shared,
     with replaceState rather than a hash assignment: stepping through a
     seven-part series should not bury the Back button, and assigning the
     hash would re-enter this through hashchange. Held off until the first
     paint is done, so arriving at a bare /tutorials leaves it bare. */
  let ready = false;
  function stampUrl() {
    if (!ready || !history.replaceState) return;
    history.replaceState(null, '', location.pathname + '#lesson-' + (current + 1));
  }

  /** Announce the swap, which is otherwise a silent change of content. */
  function announce() {
    live.textContent = 'Now playing lesson ' + (current + 1) + ' of ' +
      LESSONS.length + ': ' + LESSONS[current].title + '.';
  }

  /** On a narrow screen the rail sits under the player, so bring it back. */
  function scrollToPlayer() {
    if (window.innerWidth > 1000) return;
    screen.scrollIntoView({ behavior: softMotion, block: 'center' });
  }

  /* ---------- events ---------- */

  const poster = el('tutPoster');
  if (poster) {
    poster.addEventListener('click', () => {
      playing = true;
      mountFrame(LESSONS[current].id);
      announce();
    });
  }

  series.querySelectorAll('[data-play]').forEach(btn => {
    btn.addEventListener('click', () => {
      show(Number(btn.dataset.play), true);
      announce();
      scrollToPlayer();
    });
  });

  /* Ticking a lesson off is also how you move through them: it
     records the lesson and hands the player the next one you still
     have to watch. Unticking only takes the mark back. */
  series.querySelectorAll('[data-check]').forEach(box => {
    box.addEventListener('change', () => {
      const i = Number(box.dataset.check);
      const id = LESSONS[i].id;

      if (!box.checked) {
        done.delete(id);
        render();
        save();
        return;
      }

      const wasComplete = done.size === LESSONS.length;
      done.add(id);

      const next = nextUnwatched(i + 1);
      if (next === -1) {
        render();
        save();
        if (!wasComplete) celebrate();
        return;
      }

      show(next, true);
      announce();
    });
  });

  completeBtn.addEventListener('click', () => {
    const id = LESSONS[current].id;
    const wasComplete = done.size === LESSONS.length;
    done.add(id);

    const next = nextUnwatched(current + 1);
    if (next === -1) {
      render();
      save();
      if (!wasComplete) celebrate();
      return;
    }

    show(next, true);
    announce();
    scrollToPlayer();
  });

  prevBtn.addEventListener('click', () => { show(current - 1, true); announce(); });
  nextBtn.addEventListener('click', () => { show(current + 1, true); announce(); });

  resetBtn.addEventListener('click', () => {
    done.clear();
    render();
    save();
    live.textContent = 'Progress cleared.';
  });

  /* The site's own confetti, if main.js loaded; it already sits out a
     prefers-reduced-motion visit, so no extra check is needed here. */
  function celebrate() {
    if (typeof globalBurst !== 'function') return;
    const r = completeBtn.getBoundingClientRect();
    globalBurst(r.left + r.width / 2, r.top + r.height / 2, 90, 1.4);
  }

  /* ---------- which lesson to open with ---------- */

  /* #lesson-3 wins over stored progress: somebody following a link to a
     particular lesson means that one, not the one they last watched. */
  function linkedLesson() {
    const m = /^#lesson-(\d+)$/.exec(location.hash);
    if (!m) return -1;
    const i = Number(m[1]) - 1;
    return i >= 0 && i < LESSONS.length ? i : -1;
  }

  const linked = linkedLesson();
  const lastIndex = LESSONS.findIndex(l => l.id === saved.last);
  show(linked > -1 ? linked : (lastIndex > -1 ? lastIndex : 0), false);
  ready = true;

  /* Links to /tutorials#lesson-N from elsewhere on the site, and the Back
     button after one of them. */
  window.addEventListener('hashchange', () => {
    const i = linkedLesson();
    if (i > -1 && i !== current) {
      show(i, true);
      announce();
      scrollToPlayer();
    }
  });

  if (linked > -1) scrollToPlayer();
})();
