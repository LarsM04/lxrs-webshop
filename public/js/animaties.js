/* LXRS2004 — animaties met GSAP (https://gsap.com)

   Proefversie: elk onderdeel staat los aan of uit in AAN hieronder. Zet er
   een op false, sla op en ververs de pagina om te zien hoe het zonder is.

   Alles hier is extra. Zonder JavaScript, of als GSAP niet laadt, werkt de
   site gewoon met de CSS-animaties uit style.css. En wie "minder beweging"
   heeft ingesteld, krijgt niets van dit alles: gsap.matchMedia zet het dan
   niet aan (en ruimt het op als je de instelling verandert). */

(() => {
  'use strict';

  const AAN = {
    vloeiendScrollen: true, // tabjes en #-links glijden naar hun plek
    heroIntro: true,        // woorden komen op, logo zoomt in, cijfers tellen op
    scrollAnimaties: true,  // koppen en kaarten komen binnen, parallax, snellere tekstband
    paginaOvergang: true,   // zachte overgang tussen pagina's
    muis: true,             // kaarten kantelen mee, knoppen zijn magnetisch
  };

  const { gsap, ScrollTrigger, ScrollToPlugin, SplitText } = window;
  if (!gsap) return;
  gsap.registerPlugin(ScrollTrigger, ScrollToPlugin, SplitText);

  const html = document.documentElement;
  const kop = document.querySelector('.kop');
  const SCROLL_DOEL = 'lxrs-scroll-naar';

  /* De sticky header staat over de bovenkant van de pagina heen, dus we
     stoppen net zoveel pixels eerder. */
  const kopHoogte = () => (kop ? kop.offsetHeight : 0);

  const scrollNaar = (doel, duur = 1.1) =>
    gsap.to(window, {
      duration: duur,
      ease: 'power3.inOut',
      scrollTo: { y: doel, offsetY: doel === 0 ? 0 : kopHoogte(), autoKill: true },
    });

  const elementVoorHash = (hash) => (hash ? document.getElementById(decodeURIComponent(hash.slice(1))) : null);

  const mm = gsap.matchMedia();

  mm.add('(prefers-reduced-motion: no-preference)', () => {
    /* met-gsap: GSAP doet het scrollen, dus niet ook nog de CSS (style.css).
       lxrsScrollAnimaties: main.js slaat dan zijn eigen binnenschuiven over. */
    html.classList.add('met-gsap');
    window.lxrsScrollAnimaties = AAN.scrollAnimaties;

    if (AAN.vloeiendScrollen) vloeiendScrollen();
    if (AAN.heroIntro) heroIntro();
    if (AAN.scrollAnimaties) scrollAnimaties();

    return () => {
      html.classList.remove('met-gsap', 'gsap-intro');
      window.lxrsScrollAnimaties = false;
    };
  });

  mm.add('(prefers-reduced-motion: no-preference) and (hover: hover) and (pointer: fine)', () => {
    if (AAN.muis) muis();
  });

  /* Pagina-overgangen doet de browser zelf (View Transitions, in style.css).
     Staat het onderdeel hier uit, of wil iemand minder beweging, dan slaan we
     de overgang over. */
  const overgangOverslaan = (e) => {
    const rustig = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (e.viewTransition && (!AAN.paginaOvergang || rustig)) e.viewTransition.skipTransition();
  };
  window.addEventListener('pageswap', overgangOverslaan);
  window.addEventListener('pagereveal', overgangOverslaan);

  /* ------------------------------------------------------------------
     Vloeiend scrollen
     - Link naar een stuk van deze pagina (#werk, of Home op de homepage):
       niet herladen, maar erheen glijden.
     - Link naar een stuk van een ándere pagina: die pagina laden zonder
       #, en daar aangekomen vloeiend naar het stuk scrollen. Anders springt
       de browser er in één keer heen.
     ------------------------------------------------------------------ */
  function vloeiendScrollen() {
    const pad = (url) => url.pathname.replace(/\/$/, '') || '/';

    const klik = (e) => {
      const link = e.target.closest('a[href]');
      if (!link || e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
      if (link.target === '_blank' || link.hasAttribute('download') || link.classList.contains('skiplink')) return;

      const url = new URL(link.href, location.href);
      if (url.origin !== location.origin) return;

      const zelfdePagina = pad(url) === pad(location) && url.search === location.search;

      if (zelfdePagina) {
        const doel = elementVoorHash(url.hash);
        if (url.hash && !doel) return;

        e.preventDefault();
        scrollNaar(doel || 0);
        history.pushState(null, '', url.hash || url.pathname);

        /* Toetsenbord en schermlezer gaan mee naar het nieuwe stuk. */
        if (doel) {
          if (!doel.hasAttribute('tabindex')) doel.setAttribute('tabindex', '-1');
          doel.focus({ preventScroll: true });
        }
        return;
      }

      if (url.hash) {
        e.preventDefault();
        try { sessionStorage.setItem(SCROLL_DOEL, url.hash); } catch { /* dan springt hij gewoon */ }
        location.href = url.pathname + url.search + (sessionStorageWerkt() ? '' : url.hash);
      }
    };

    document.addEventListener('click', klik);

    /* Net aangekomen van een andere pagina met een doel? Dan erheen glijden,
       zodra de pagina-overgang klaar is. */
    let doelHash = null;
    try {
      doelHash = sessionStorage.getItem(SCROLL_DOEL);
      sessionStorage.removeItem(SCROLL_DOEL);
    } catch { /* geen sessionStorage, niets te doen */ }

    const doel = elementVoorHash(doelHash);
    if (doel) {
      gsap.delayedCall(0.45, () => {
        scrollNaar(doel, 1.4);
        history.replaceState(null, '', doelHash);
      });
    }

    return () => document.removeEventListener('click', klik);
  }

  function sessionStorageWerkt() {
    try {
      sessionStorage.setItem('lxrs-test', '1');
      sessionStorage.removeItem('lxrs-test');
      return true;
    } catch {
      return false;
    }
  }

  /* ------------------------------------------------------------------
     Hero-intro
     De onderdelen hebben data-reveal; met .gsap-intro staat hun
     CSS-animatie uit en zijn ze nog onzichtbaar. Deze tijdlijn laat ze
     binnenkomen.
     ------------------------------------------------------------------ */
  function heroIntro() {
    const hero = document.querySelector('.hero');
    if (!hero) return;

    html.classList.add('gsap-intro');

    const pil = hero.querySelector('.oogje--pil');
    const h1 = hero.querySelector('h1');
    const lead = hero.querySelector('.lead');
    const knoppen = hero.querySelectorAll('.hero__acties .knop');
    const logo = hero.querySelector('.hero__logo');
    const cijfers = hero.querySelector('.hero__cijfers');
    const getallen = hero.querySelectorAll('.hero__cijfer strong');

    gsap.set(hero.querySelectorAll('[data-reveal]'), { opacity: 1 });

    /* Elk woord schuift omhoog uit een onzichtbaar masker. De woorden in
       "niet stilstaan" krijgen zelf het kleurverloop, want in losse stukjes
       werkt het verloop van de omringende span niet meer. Na afloop zet
       revert() de kop terug zoals hij was. */
    const split = SplitText.create(h1, { type: 'words', mask: 'words' });
    split.words.forEach((woord) => {
      if (woord.closest('.verloop-tekst')) woord.classList.add('verloop-tekst');
    });

    gsap.timeline({ defaults: { ease: 'power3.out' }, onComplete: () => split.revert() })
      .from(pil, { y: 20, opacity: 0, duration: 0.6 })
      .from(split.words, { yPercent: 110, duration: 0.9, stagger: 0.08 }, '-=0.3')
      .from(lead, { y: 24, opacity: 0, duration: 0.7 }, '-=0.55')
      .from(knoppen, { y: 20, opacity: 0, duration: 0.6, stagger: 0.1, clearProps: 'transform' }, '-=0.5')
      .from(logo, { scale: 0.6, rotation: -12, opacity: 0, duration: 1.4, ease: 'expo.out' }, 0.25)
      .from(cijfers, { y: 16, opacity: 0, duration: 0.6 }, '-=0.9')
      .from(getallen, { textContent: 0, duration: 1.6, ease: 'power2.out', snap: { textContent: 1 }, stagger: 0.12 }, '<');
  }

  /* ------------------------------------------------------------------
     Scroll-animaties
     ------------------------------------------------------------------ */
  function scrollAnimaties() {
    /* Sectiekoppen: oogje, kop en tekst komen na elkaar binnen. */
    document.querySelectorAll('main .sectiekop').forEach((kopBlok) => {
      gsap.from(kopBlok.querySelectorAll(':scope > div > *, :scope > p'), {
        y: 40,
        opacity: 0,
        duration: 0.9,
        stagger: 0.1,
        ease: 'power3.out',
        scrollTrigger: { trigger: kopBlok, start: 'top 85%', once: true },
      });
    });

    /* Kaarten, tegels en edits: per rij, een tikje na elkaar. Tijdens het
       binnenkomen staat hun CSS-transition uit, anders loopt die achter
       GSAP aan en hapert het. */
    const blokken = gsap.utils.toArray('.raster .kaart, .tegels .tegel, .vertrouwen > div, .reels .reel');
    gsap.set(blokken, { opacity: 0, y: 50 });
    ScrollTrigger.batch(blokken, {
      start: 'top 92%',
      once: true,
      onEnter: (rij) =>
        gsap.to(rij, {
          opacity: 1,
          y: 0,
          duration: 0.8,
          stagger: 0.09,
          ease: 'power3.out',
          startAt: { transition: 'none' },
          clearProps: 'transform,transition',
        }),
    });

    /* Parallax: het logo beweegt trager mee dan de pagina. */
    const logo = document.querySelector('.hero__logo');
    if (logo) {
      gsap.to(logo, {
        yPercent: 22,
        ease: 'none',
        scrollTrigger: { trigger: '.hero', start: 'top top', end: 'bottom top', scrub: true },
      });
    }

    /* Tekstbanden: hoe sneller je scrolt, hoe sneller ze lopen; daarna
       remmen ze weer af. De banden zelf zijn CSS-animaties, dus we
       veranderen alleen hun afspeelsnelheid. */
    const banden = gsap.utils.toArray('.band__rij').flatMap((rij) => rij.getAnimations());
    if (banden.length) {
      const tempo = { snelheid: 1 };
      const zet = () => banden.forEach((a) => { a.playbackRate = tempo.snelheid; });
      let remmen;

      ScrollTrigger.create({
        onUpdate: (self) => {
          const doel = 1 + Math.min(Math.abs(self.getVelocity()) / 350, 6);
          if (doel < tempo.snelheid) return;
          remmen?.kill();
          remmen = gsap.timeline({ onUpdate: zet })
            .to(tempo, { snelheid: doel, duration: 0.15 })
            .to(tempo, { snelheid: 1, duration: 1.4, ease: 'power2.out' });
        },
      });
    }
  }

  /* ------------------------------------------------------------------
     Muis
     ------------------------------------------------------------------ */
  function muis() {
    const opruimen = [];

    /* Kaarten kantelen een beetje mee met de muis, en komen iets omhoog. */
    document.querySelectorAll('.kaart').forEach((kaart) => {
      kaart.classList.add('kantelt');
      gsap.set(kaart, { transformPerspective: 900 });
      const rx = gsap.quickTo(kaart, 'rotationX', { duration: 0.5, ease: 'power3' });
      const ry = gsap.quickTo(kaart, 'rotationY', { duration: 0.5, ease: 'power3' });
      const y = gsap.quickTo(kaart, 'y', { duration: 0.5, ease: 'power3' });

      const beweeg = (e) => {
        const r = kaart.getBoundingClientRect();
        const px = (e.clientX - r.left) / r.width - 0.5;
        const py = (e.clientY - r.top) / r.height - 0.5;
        ry(px * 8);
        rx(-py * 8);
        y(-4);
      };
      const weg = () => { rx(0); ry(0); y(0); };

      kaart.addEventListener('mousemove', beweeg);
      kaart.addEventListener('mouseleave', weg);
      opruimen.push(() => {
        kaart.removeEventListener('mousemove', beweeg);
        kaart.removeEventListener('mouseleave', weg);
        kaart.classList.remove('kantelt');
      });
    });

    /* Magnetische knoppen: ze worden licht naar je cursor toe getrokken.
       Niet de knoppen in formulieren en niet de brede Bestellen-knop. */
    document.querySelectorAll('main a.knop:not(.knop--breed)').forEach((knop) => {
      knop.classList.add('magnetisch');
      const x = gsap.quickTo(knop, 'x', { duration: 0.4, ease: 'power3' });
      const y = gsap.quickTo(knop, 'y', { duration: 0.4, ease: 'power3' });

      const beweeg = (e) => {
        const r = knop.getBoundingClientRect();
        x((e.clientX - (r.left + r.width / 2)) * 0.3);
        y((e.clientY - (r.top + r.height / 2)) * 0.4);
      };
      const weg = () => {
        gsap.to(knop, { x: 0, y: 0, duration: 0.7, ease: 'elastic.out(1, 0.4)', overwrite: true });
      };

      knop.addEventListener('mousemove', beweeg);
      knop.addEventListener('mouseleave', weg);
      opruimen.push(() => {
        knop.removeEventListener('mousemove', beweeg);
        knop.removeEventListener('mouseleave', weg);
        knop.classList.remove('magnetisch');
      });
    });

    return () => opruimen.forEach((f) => f());
  }
})();
