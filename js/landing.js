/* ============================================================
   /start: the sticky install bar
   ------------------------------------------------------------
   On a phone the hero button scrolls away in a second and most ad
   traffic is on a phone, so the offer follows the visitor down the
   page. It appears only once the hero button is actually gone, so it
   never sits on top of the button it duplicates, and it hides again
   over the closing CTA and over the lead form for the same reason.

   CSS decides whether the bar is ever shown at all; this only decides
   when. Everything below is inert on a wide screen.
   ============================================================ */

(function stickyInstall() {
  const bar = document.getElementById('lpSticky');
  const heroCta = document.querySelector('.lp-hero .hero-ctas');
  const finalCta = document.getElementById('install');
  if (!bar || !heroCta || !finalCta) return;

  /* Watched anchors, one visible flag: the bar is for the stretch of page
     between them, where there is no install button on screen. */
  const onScreen = new Map([[heroCta, true], [finalCta, false]]);

  /* The lead form counts as one of them. It is asking for something, and a
     fixed bar offering the install instead is a second call to action
     sitting on top of the first, on the screen size where it is hardest to
     fill a form in. Absent on a page without the form, which is fine. */
  const leadForm = document.querySelector('.dm-form-sec');
  if (leadForm) onScreen.set(leadForm, false);

  const apply = () => {
    const anchorVisible = [...onScreen.values()].some(Boolean);
    bar.hidden = anchorVisible;
    bar.classList.toggle('is-up', !anchorVisible);
    /* The Ask Brix AI launcher is fixed to the same corner, so it steps up
       out of the way for as long as the bar is there. */
    document.body.classList.toggle('lp-bar-up', !anchorVisible);
  };

  if (!('IntersectionObserver' in window)) {
    // No observer: show it and let the page's own buttons sit above it.
    bar.hidden = false;
    bar.classList.add('is-up');
    document.body.classList.add('lp-bar-up');
    return;
  }

  const io = new IntersectionObserver(entries => {
    entries.forEach(e => onScreen.set(e.target, e.isIntersecting));
    apply();
  }, { rootMargin: '-8px 0px -8px 0px' });

  io.observe(heroCta);
  io.observe(finalCta);
  if (leadForm) io.observe(leadForm);
  apply();
})();
