/* LXRS2004 — animaties en snel navigeren, met GSAP (https://gsap.com)

   Proefversie: elk onderdeel staat los aan of uit in AAN hieronder. Zet er
   een op false, sla op en ververs de pagina om te zien hoe het zonder is.

   Alles hier is extra. Zonder JavaScript, of als GSAP niet laadt, werkt de
   site gewoon met de CSS-animaties uit style.css. Wie "minder beweging"
   heeft ingesteld, krijgt geen animaties (gsap.matchMedia zet ze dan niet
   aan); snel navigeren werkt dan wel, alleen zonder overgang. */

(() => {
  'use strict';

  const AAN = {
    snelNavigeren: true,    // pagina's al ophalen bij muis-hover en zonder herladen wisselen
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
  const SCROLL_DOEL = 'lxrs-scroll-naar';
  const rustig = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* De hero meteen verbergen; de intro laat hem straks binnenkomen. */
  if (AAN.heroIntro && !rustig() && document.querySelector('.hero')) html.classList.add('gsap-intro');

  /* main.js draait direct na dit script; zo weet die al dat GSAP het
     binnenkomen van de kaarten doet. */
  window.lxrsScrollAnimaties = AAN.scrollAnimaties && !rustig();

  function starten() {
    eenmalig();
    paginaAnimaties();
  }

  /* ==================================================================
     Wat één keer gebeurt: klikken op links, terug- en vooruitknop, en
     pagina-overgangen bij gewoon laden. De header en footer blijven
     altijd staan, dus dit hoeft niet opnieuw na een wissel.
     ================================================================== */
  function eenmalig() {
    if (AAN.snelNavigeren) vooraf();

    document.addEventListener('click', klik);

    /* Terug- en vooruitknop. Verandert alleen het #-deel (bijvoorbeeld van
       #werk terug naar boven), dan blijven we op de pagina en scrollen we. */
    window.addEventListener('popstate', () => {
      const url = new URL(location.href);
      if (url.pathname + url.search === huidigAdres) {
        scrollNaar(elementVoorHash(url.hash));
      } else if (AAN.snelNavigeren) {
        ga(url, { nieuweStap: false });
      } else {
        location.reload();
      }
    });

    /* Pagina-overgang bij gewoon laden (bijvoorbeeld als snel navigeren uit
       staat) doet de browser zelf, met View Transitions uit style.css. */
    const overgangOverslaan = (e) => {
      if (e.viewTransition && (!AAN.paginaOvergang || rustig())) e.viewTransition.skipTransition();
    };
    window.addEventListener('pageswap', overgangOverslaan);
    window.addEventListener('pagereveal', overgangOverslaan);

    /* Gewoon geladen met een doel van een andere pagina (snel navigeren
       stond uit)? Dan erheen glijden. */
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
  }

  /* ==================================================================
     Klikken op een link
     - naar een stuk van deze pagina (#werk, of Home op de homepage):
       erheen glijden in plaats van herladen
     - naar een andere pagina van de site: snel navigeren
     ================================================================== */
  function klik(e) {
    const link = e.target.closest('a[href]');
    if (!link || e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    if (link.target === '_blank' || link.hasAttribute('download') || link.classList.contains('skiplink')) return;

    const url = new URL(link.href, location.href);
    if (url.origin !== location.origin) return;

    if (zelfdePagina(url)) {
      if (!AAN.vloeiendScrollen || rustig()) return;
      const doel = elementVoorHash(url.hash);
      if (url.hash && !doel) return;

      e.preventDefault();
      scrollNaar(doel || 0);
      history.pushState(null, '', url.hash || url.pathname);
      focusOp(doel);
      return;
    }

    if (AAN.snelNavigeren && hoortBijSite(url)) {
      e.preventDefault();
      ga(url);
      return;
    }

    /* Snel navigeren staat uit: de pagina laadt gewoon, en daar
       aangekomen glijden we naar het stuk in plaats van te springen. */
    if (url.hash && AAN.vloeiendScrollen && !rustig()) {
      try {
        sessionStorage.setItem(SCROLL_DOEL, url.hash);
        e.preventDefault();
        location.href = url.pathname + url.search;
      } catch { /* dan springt de browser er gewoon heen */ }
    }
  }

  const pad = (url) => url.pathname.replace(/\/$/, '') || '/';
  const zelfdePagina = (url) => pad(url) === pad(location) && url.search === location.search;
  const elementVoorHash = (hash) => (hash ? document.getElementById(decodeURIComponent(hash.slice(1))) : null);

  /* Alleen pagina's van de website zelf: niet de beheeromgeving (eigen
     layout) en geen bestanden zoals afbeeldingen. */
  const hoortBijSite = (url) =>
    url.origin === location.origin && !url.pathname.startsWith('/admin') && !/\.[a-z0-9]+$/i.test(url.pathname);

  const kopHoogte = () => document.querySelector('.kop')?.offsetHeight ?? 0;

  function scrollNaar(doel, duur = 1.1) {
    if (rustig()) {
      window.scrollTo(0, doel ? doel.getBoundingClientRect().top + scrollY - kopHoogte() : 0);
      return;
    }
    gsap.to(window, {
      duration: duur,
      ease: 'power3.inOut',
      scrollTo: { y: doel || 0, offsetY: doel ? kopHoogte() : 0, autoKill: true },
    });
  }

  /* Toetsenbord en schermlezer gaan mee naar het nieuwe stuk. */
  function focusOp(element) {
    if (!element) return;
    if (!element.hasAttribute('tabindex')) element.setAttribute('tabindex', '-1');
    element.focus({ preventScroll: true });
  }

  /* ==================================================================
     Snel navigeren
     Hang je even met je muis boven een link (of tik je erop), dan halen we
     die pagina alvast op. Bij de klik wisselen we alleen <main>: de header,
     footer, CSS en scripts staan er al. Zo is de nieuwe pagina er meteen.
     Gaat er iets mis, dan laden we de pagina gewoon zoals altijd.
     ================================================================== */
  let huidigAdres = location.pathname + location.search; // de pagina die nu in beeld is
  const opgehaald = new Map(); // adres → { wanneer, html (een Promise) }
  const VERS = 120_000;        // na 2 minuten halen we een pagina opnieuw op

  function ophalen(url) {
    const adres = url.pathname + url.search;
    const bewaard = opgehaald.get(adres);
    if (bewaard && Date.now() - bewaard.wanneer < VERS) return bewaard.html;

    const html = fetch(adres, { credentials: 'same-origin' }).then((antwoord) => {
      const isPagina = antwoord.ok && (antwoord.headers.get('content-type') || '').includes('text/html');
      if (!isPagina) throw new Error('geen pagina');
      return antwoord.text();
    });
    html.catch(() => opgehaald.delete(adres));
    opgehaald.set(adres, { wanneer: Date.now(), html });
    return html;
  }

  function vooraf() {
    let wachten;
    const kandidaat = (e) => {
      const link = e.target.closest?.('a[href]');
      if (!link || link.target === '_blank') return null;
      const url = new URL(link.href, location.href);
      return hoortBijSite(url) && !zelfdePagina(url) ? url : null;
    };

    /* De tabjes in het menu halen we alvast op zodra de pagina rustig is:
       daar klik je het vaakst op, en dan staan ze er altijd meteen. Na elke
       wissel opnieuw, want een gebruikte pagina gooien we weg. */
    const menuOphalen = () => {
      document.querySelectorAll('#hoofdnav a[href]').forEach((link) => {
        const url = new URL(link.href, location.href);
        if (hoortBijSite(url) && !zelfdePagina(url)) ophalen(url).catch(() => {});
      });
    };
    const alsHetRustigIs = window.requestIdleCallback || ((f) => setTimeout(f, 1500));
    alsHetRustigIs(menuOphalen);
    document.addEventListener('lxrs:pagina', () => alsHetRustigIs(menuOphalen));

    /* Terug op dit tabblad, bijvoorbeeld na iets aanpassen in de
       beheeromgeving? Dan alles opnieuw ophalen, zodat je nooit een oude
       versie van een pagina ziet. */
    document.addEventListener('visibilitychange', () => {
      if (document.visibilityState !== 'visible') return;
      opgehaald.clear();
      menuOphalen();
    });

    /* Een korte pauze, zodat we niet alles ophalen waar je muis langs schiet. */
    document.addEventListener('pointerover', (e) => {
      const url = kandidaat(e);
      clearTimeout(wachten);
      if (url) wachten = setTimeout(() => ophalen(url).catch(() => {}), 65);
    });
    document.addEventListener('pointerout', () => clearTimeout(wachten));
    document.addEventListener('touchstart', (e) => {
      const url = kandidaat(e);
      if (url) ophalen(url).catch(() => {});
    }, { passive: true });
    document.addEventListener('focusin', (e) => {
      const url = kandidaat(e);
      if (url) ophalen(url).catch(() => {});
    });
  }

  async function ga(url, { nieuweStap = true } = {}) {
    /* Nog niet binnen? Dan meteen een laadbalkje (zie .laadt in style.css),
       zodat je ziet dat de klik is aangekomen. Is hij er binnen 80 ms, dan
       zie je het balkje niet eens. */
    const laadbalk = setTimeout(() => html.classList.add('laadt'), 80);

    let nieuw;
    try {
      const tekst = await ophalen(url);
      nieuw = new DOMParser().parseFromString(tekst, 'text/html');
    } catch {
      location.href = url.href;
      return;
    } finally {
      clearTimeout(laadbalk);
      html.classList.remove('laadt');
    }

    const nieuweMain = nieuw.querySelector('main');
    if (!nieuweMain) {
      location.href = url.href;
      return;
    }
    opgehaald.delete(url.pathname + url.search); // de volgende keer weer vers

    const wissel = () => {
      /* De hero van de nieuwe pagina meteen verbergen voor de intro. */
      if (AAN.heroIntro && !rustig() && nieuweMain.querySelector('.hero')) html.classList.add('gsap-intro');

      document.querySelector('main').replaceWith(document.adoptNode(nieuweMain));
      document.title = nieuw.title;
      kopieerKop(nieuw);

      if (nieuweStap) history.pushState(null, '', url.pathname + url.search + url.hash);
      huidigAdres = url.pathname + url.search;
      window.scrollTo(0, 0);
    };

    paginaOpruimen();

    if (document.startViewTransition && AAN.paginaOvergang && !rustig()) {
      await document.startViewTransition(wissel).updateCallbackDone;
    } else {
      wissel();
    }

    paginaAnimaties();
    document.dispatchEvent(new CustomEvent('lxrs:pagina'));

    /* Met een # in het adres (Werk, Contact): erheen glijden. Anders gaat
       de focus naar de nieuwe inhoud, zodat een schermlezer hem voorleest. */
    const doel = elementVoorHash(url.hash);
    if (doel) {
      gsap.delayedCall(0.35, () => scrollNaar(doel, 1.3));
      focusOp(doel);
    } else {
      focusOp(document.querySelector('main h1') || document.querySelector('main'));
    }
  }

  /* De paginatitel staat al goed; dit doet de rest van de <head> en het
     actieve tabje in het menu. */
  function kopieerKop(nieuw) {
    for (const sleutel of ['meta[name="description"]', 'meta[property="og:title"]', 'meta[property="og:description"]', 'link[rel="canonical"]']) {
      const oud = document.head.querySelector(sleutel);
      const vers = nieuw.head.querySelector(sleutel);
      if (oud && vers) oud.replaceWith(vers.cloneNode(true));
    }

    const oudeLinks = document.querySelectorAll('#hoofdnav a');
    const nieuweLinks = nieuw.querySelectorAll('#hoofdnav a');
    oudeLinks.forEach((link, i) => {
      const actief = nieuweLinks[i]?.getAttribute('aria-current');
      if (actief) link.setAttribute('aria-current', actief);
      else link.removeAttribute('aria-current');
    });
  }

  /* ==================================================================
     Animaties van één pagina. Na een wissel ruimt paginaOpruimen() alles
     op (ScrollTriggers, tweens, luisteraars) en begint dit opnieuw.
     ================================================================== */
  let paginaMedia = null;

  function paginaOpruimen() {
    paginaMedia?.revert();
    paginaMedia = null;
  }

  function paginaAnimaties() {
    paginaOpruimen();
    paginaMedia = gsap.matchMedia();

    paginaMedia.add('(prefers-reduced-motion: no-preference)', () => {
      /* met-gsap: GSAP doet het scrollen, dus niet ook nog de CSS (style.css). */
      html.classList.add('met-gsap');

      if (AAN.heroIntro) heroIntro();
      if (AAN.scrollAnimaties) scrollAnimaties();

      return () => html.classList.remove('met-gsap');
    });

    paginaMedia.add('(prefers-reduced-motion: no-preference) and (hover: hover) and (pointer: fine)', () => {
      if (AAN.muis) return muis();
    });
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
    if (document.querySelector('.hero__logo')) {
      gsap.to('.hero__logo', {
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

  /* Starten. Onderaan, zodat alle functies en variabelen hierboven al
     bestaan. Een pagina kan door de browser vooraf geladen zijn: dan draait
     dit script terwijl de pagina nog onzichtbaar is. Pas starten als hij
     echt in beeld komt, anders mis je de intro. */
  if (document.prerendering) {
    document.addEventListener('prerenderingchange', starten, { once: true });
  } else {
    starten();
  }
})();
