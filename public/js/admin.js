/* LXRS2004 — beheeromgeving
   Alles hier is optioneel: zonder JavaScript werken de formulieren gewoon. */

(() => {
  'use strict';

  /* ------------------------------------------------------------------
     Afbeelding in beeld zetten (presetformulier)
     De schuifjes zijn de echte formuliervelden. Hier komt bij: een live
     voorbeeld, slepen in het voorbeeld, en een nieuwe afbeelding meteen
     tonen voordat hij is opgeslagen.
     ------------------------------------------------------------------ */
  const blok = document.querySelector('[data-beeld-instellen]');
  if (!blok) return;

  const voorbeeld = blok.querySelector('.beeld-instellen__voorbeeld');
  const kader = blok.querySelector('[data-beeld-kader]');
  const x = blok.querySelector('[name="afbeelding_x"]');
  const y = blok.querySelector('[name="afbeelding_y"]');
  const zoom = blok.querySelector('[name="afbeelding_zoom"]');
  const passend = blok.querySelector('[name="afbeelding_passend"]');
  const bestand = document.getElementById('veld-afbeelding');

  const binnen = (waarde) => Math.min(100, Math.max(0, Math.round(waarde)));

  const toon = () => {
    kader.style.setProperty('--x', x.value + '%');
    kader.style.setProperty('--y', y.value + '%');
    kader.style.setProperty('--zoom', zoom.value / 100);
    kader.classList.toggle('beeld-kader--passend', passend.checked);
  };

  [x, y, zoom].forEach((schuif) => schuif.addEventListener('input', toon));
  passend.addEventListener('change', toon);

  blok.querySelector('[data-beeld-reset]').addEventListener('click', () => {
    x.value = 50;
    y.value = 50;
    zoom.value = 100;
    toon();
  });

  /* Nieuwe afbeelding gekozen: meteen in het voorbeeld, vanaf het midden. */
  bestand?.addEventListener('change', () => {
    const gekozen = bestand.files[0];
    if (!gekozen) return;
    const url = URL.createObjectURL(gekozen);
    kader.querySelectorAll('img').forEach((img) => { img.src = url; });
    x.value = 50;
    y.value = 50;
    zoom.value = 100;
    toon();
    blok.hidden = false;
  });

  /* Slepen: sleep je naar rechts, dan schuift de afbeelding mee naar rechts
     en zie je meer van de linkerkant (x wordt kleiner). Hoe verder
     ingezoomd, hoe kleiner de stapjes, zodat het gevoel gelijk blijft. */
  let start = null;

  voorbeeld.addEventListener('pointerdown', (e) => {
    start = { px: e.clientX, py: e.clientY, x: Number(x.value), y: Number(y.value) };
    voorbeeld.setPointerCapture(e.pointerId);
    voorbeeld.classList.add('is-slepen');
  });

  voorbeeld.addEventListener('pointermove', (e) => {
    if (!start) return;
    const maat = voorbeeld.getBoundingClientRect();
    const gevoel = 150 / (zoom.value / 100);
    x.value = binnen(start.x - ((e.clientX - start.px) / maat.width) * gevoel);
    y.value = binnen(start.y - ((e.clientY - start.py) / maat.height) * gevoel);
    toon();
  });

  const stop = () => {
    start = null;
    voorbeeld.classList.remove('is-slepen');
  };
  voorbeeld.addEventListener('pointerup', stop);
  voorbeeld.addEventListener('pointercancel', stop);

  /* Na een validatiefout staan de schuifjes op wat je had ingesteld;
     zet het voorbeeld daar meteen op. */
  toon();
})();
