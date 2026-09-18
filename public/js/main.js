/* LXRS2004 — front-end gedrag
   Alles hier is optioneel: zonder JavaScript werkt de site gewoon. */

(() => {
  'use strict';

  const minderBeweging = window.matchMedia('(prefers-reduced-motion: reduce)');

  /* ------------------------------------------------------------------
     Mobiel menu
     ------------------------------------------------------------------ */
  const navKnop = document.querySelector('[data-nav-knop]');
  const nav = document.getElementById('hoofdnav');

  if (navKnop && nav) {
    const zet = (open) => {
      nav.dataset.open = String(open);
      navKnop.setAttribute('aria-expanded', String(open));
      navKnop.querySelector('.visueel-verborgen').textContent = open ? 'Menu sluiten' : 'Menu openen';
    };

    navKnop.addEventListener('click', () => {
      zet(nav.dataset.open !== 'true');
    });

    // Sluiten na het kiezen van een link, en met Escape.
    nav.addEventListener('click', (e) => {
      if (e.target.closest('a')) zet(false);
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && nav.dataset.open === 'true') {
        zet(false);
        navKnop.focus();
      }
    });
  }

  /* ------------------------------------------------------------------
     Custom cursor — alleen met een echte muis, en niet bij
     prefers-reduced-motion. Op touch gebeurt hier dus niets.
     ------------------------------------------------------------------ */
  const heeftMuis = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
  const ring = document.querySelector('.cursor--ring');
  const stip = document.querySelector('.cursor--stip');

  if (heeftMuis && ring && stip && !minderBeweging.matches) {
    let mx = innerWidth / 2;
    let my = innerHeight / 2;
    let rx = mx;
    let ry = my;
    let frame = null;

    const beweeg = (e) => {
      mx = e.clientX;
      my = e.clientY;
      ring.style.opacity = stip.style.opacity = '1';

      const raakbaar = e.target.closest && e.target.closest('a, button, .kaart, .tegel, input');
      ring.style.width = ring.style.height = raakbaar ? '52px' : '30px';
      ring.style.margin = raakbaar ? '-26px 0 0 -26px' : '-15px 0 0 -15px';
      ring.style.backgroundColor = raakbaar ? 'rgba(139,116,255,0.16)' : 'transparent';
    };

    const verberg = () => {
      ring.style.opacity = stip.style.opacity = '0';
    };

    const lus = () => {
      rx += (mx - rx) * 0.22;
      ry += (my - ry) * 0.22;
      ring.style.transform = `translate3d(${rx}px, ${ry}px, 0)`;
      stip.style.transform = `translate3d(${mx}px, ${my}px, 0)`;
      frame = requestAnimationFrame(lus);
    };

    window.addEventListener('pointermove', beweeg, { passive: true });
    window.addEventListener('pointerdown', beweeg, { passive: true });
    document.addEventListener('mouseleave', verberg);
    lus();

    // Stoppen zodra iemand alsnog om minder beweging vraagt.
    minderBeweging.addEventListener('change', (e) => {
      if (e.matches && frame) {
        cancelAnimationFrame(frame);
        verberg();
      }
    });
  }

  /* ------------------------------------------------------------------
     Scroll-reveal: kaarten komen op zodra ze in beeld schuiven.
     Bij prefers-reduced-motion slaan we dit over; de CSS zet ze dan
     meteen zichtbaar.
     ------------------------------------------------------------------ */
  if (!minderBeweging.matches && 'IntersectionObserver' in window) {
    const kaarten = document.querySelectorAll('.raster .kaart, .tegels .tegel');

    const waarnemer = new IntersectionObserver(
      (items, obs) => {
        items.forEach((item) => {
          if (!item.isIntersecting) return;
          const index = [...item.target.parentElement.children].indexOf(item.target);
          item.target.style.animationDelay = `${Math.min(index, 5) * 70}ms`;
          item.target.setAttribute('data-reveal', '1');
          obs.unobserve(item.target);
        });
      },
      { rootMargin: '0px 0px -60px 0px', threshold: 0.05 }
    );

    kaarten.forEach((k) => waarnemer.observe(k));
  }
})();
