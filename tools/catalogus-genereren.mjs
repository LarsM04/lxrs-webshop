#!/usr/bin/env node
/**
 * Genereert app/Support/PresetCatalog.php uit de mappenstructuur van het
 * echte edit pack.
 *
 *   node tools/catalogus-genereren.mjs "<pad naar de uitgepakte map>"
 *
 * De .ffx-bestanden zelf blijven buiten dit project: dat is het product.
 * Alleen de namen en aantallen komen hierin terecht.
 */

import { readdir, writeFile, stat } from 'node:fs/promises';
import path from 'node:path';

const PACK = process.argv[2];
if (!PACK) {
  console.error('Gebruik: node tools/catalogus-genereren.mjs "<pad naar pack>"');
  process.exit(1);
}

/* Welke map hoort bij welke categorie. De losse audio-presets staan in het
   pack in de hoofdmap; die krijgen hier hun eigen categorie. */
const CATEGORIEEN = [
  { id: 1, map: 'CC', slug: 'color-corrections', name: 'Color Corrections', afkorting: "CC's",
    omschrijving: 'Kleurgrades die je hele edit in een klap laten kloppen.',
    losPrijs: 4.0, packPrijs: 14.0 },
  { id: 2, map: 'Text', slug: 'text-presets', name: 'Text Presets', afkorting: 'Text',
    omschrijving: 'Titels, glows en fades die op de beat binnenkomen.',
    losPrijs: 3.0, packPrijs: 18.0 },
  { id: 3, map: 'Zooms', slug: 'zooms', name: 'Zooms', afkorting: 'Zooms',
    omschrijving: 'Smooth zooms en punch-ins, klaar om te slepen.',
    losPrijs: 3.0, packPrijs: 13.0 },
  { id: 4, map: 'Shakes', slug: 'shakes', name: 'Shakes', afkorting: 'Shakes',
    omschrijving: 'Camera shakes met gewicht, van subtiel tot een dreun.',
    losPrijs: 3.0, packPrijs: 10.0 },
  { id: 5, map: 'Effects', slug: 'effects', name: 'Effects', afkorting: 'FX',
    omschrijving: 'Halftone, panning, motion blur en transities.',
    losPrijs: 3.0, packPrijs: 8.0 },
  { id: 6, map: 'Twixtor', slug: 'twixtor', name: 'Twixtor', afkorting: 'Twixtor',
    omschrijving: 'Mijn twixtor-instellingen voor slow motion zonder artefacten.',
    losPrijs: 5.0, packPrijs: 9.0 },
  { id: 7, map: '.', slug: 'audio', name: 'Audio', afkorting: 'Audio',
    omschrijving: 'Audio spectrum, wiggle en fades voor je intro en outro.',
    losPrijs: 2.5, packPrijs: 6.0, alleenRoot: true },
  { id: 8, map: null, slug: 'bundels', name: 'Bundels', afkorting: 'Bundel',
    omschrijving: 'Alles bij elkaar, voor de beste prijs per preset.' },
];

/* Losse bestanden uit de hoofdmap die niet bij audio horen. */
const VERHUIZEN = { 'LXRS blur transition.ffx': 5 };

/* Woorden die hun eigen schrijfwijze houden. */
const ACRONIEMEN = { cc: 'CC', rgb: 'RGB', rsmb: 'RSMB', y: 'Y', fx: 'FX' };

/* Kleine woorden blijven klein, behalve vooraan. */
const KLEIN = new Set(['and', 'en', 'of', 'the']);

/* Typefouten in de bestandsnamen die je niet op een productpagina wilt. */
const CORRECTIES = { aestetic: 'aesthetic', settin: 'setting', setttings: 'settings' };

function nettNaam(bestand) {
  let n = bestand.replace(/\.ffx$/i, '');
  n = n.replace(/^lxrs[\s_-]*/i, '');
  n = n.replace(/_/g, ' / ');
  n = n.trim();

  return n
    .split(/\s+/)
    .map((w, i) => {
      const kaal = w.toLowerCase();
      if (i > 0 && KLEIN.has(kaal)) return kaal;
      if (CORRECTIES[kaal]) w = CORRECTIES[kaal];
      const k2 = w.toLowerCase();
      if (ACRONIEMEN[k2]) return ACRONIEMEN[k2];
      if (w === '/') return w;
      if (/^\d+$/.test(w)) return w;
      return w.charAt(0).toUpperCase() + w.slice(1).toLowerCase();
    })
    .join(' ')
    .replace(/\s+/g, ' ')
    .trim();
}

const slugify = (s) =>
  s.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '')
    .replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');

const php = (v) => {
  if (v === null) return 'null';
  if (typeof v === 'boolean') return v ? 'true' : 'false';
  if (typeof v === 'number') return Number.isInteger(v) ? String(v) : v.toFixed(2);
  if (Array.isArray(v)) return '[' + v.map(php).join(', ') + ']';
  return "'" + String(v).replace(/\\/g, '\\\\').replace(/'/g, "\\'") + "'";
};

async function ffxIn(map, alleenRoot = false) {
  const vol = path.join(PACK, map);
  const items = await readdir(vol, { withFileTypes: true, recursive: !alleenRoot }).catch(() => []);
  const ffx = items.filter((d) => d.isFile() && d.name.toLowerCase().endsWith('.ffx'));

  const met = [];
  for (const d of ffx) {
    const volPad = path.join(d.parentPath ?? d.path ?? vol, d.name);
    const s = await stat(volPad).catch(() => ({ size: 0 }));
    met.push({ naam: d.name, bytes: s.size });
  }
  return met.sort((a, b) => a.naam.localeCompare(b.naam, 'nl'));
}

/* 'Text Presets' + '-presets' leest als 'text presets-presets'. Daarom halen
   we een afsluitend 'presets' uit de categorienaam voordat we 'm gebruiken. */
const stam = (naam) => naam.toLowerCase().replace(/\s*presets$/, '');

/* Bestandsgroottes zoals een mens ze leest. */
function leesbaar(bytes) {
  if (bytes >= 1024 * 1024) return (bytes / (1024 * 1024)).toFixed(1).replace('.', ',') + ' MB';
  return Math.max(1, Math.round(bytes / 1024)) + ' KB';
}

const rijen = [];
let id = 0;
const perCategorie = {};
const inhoud = {};
const bytesPer = {};

for (const cat of CATEGORIEEN) {
  if (cat.map === null) continue;

  const bestanden = await ffxIn(cat.map, cat.alleenRoot);
  perCategorie[cat.id] = bestanden.length;

  for (const { naam: bestand, bytes } of bestanden) {
    const naam = nettNaam(bestand);
    const doelCat = VERHUIZEN[bestand] ?? cat.id;
    if (doelCat !== cat.id) {
      perCategorie[cat.id]--;
      perCategorie[doelCat] = (perCategorie[doelCat] ?? 0) + 1;
    }
    rijen.push({
      id: ++id,
      category_id: doelCat,
      slug: slugify(naam + '-' + (CATEGORIEEN.find((c) => c.id === doelCat) ?? cat).slug),
      name: naam,
      soort: 'los',
      aantal: 1,
      price: (CATEGORIEEN.find((c) => c.id === doelCat) ?? cat).losPrijs,
      tagline: `Losse ${stam((CATEGORIEEN.find((c) => c.id === doelCat) ?? cat).name)}-preset`,
      is_featured: false,
      bestandsgrootte: leesbaar(bytes),
      includes: ['1 preset als .ffx'],
      ae_version: 'After Effects 2020 of nieuwer',
      description: `Eén losse preset uit m'n eigen edit pack. Importeer 'm via de `
        + `Effects & Presets-map in After Effects en sleep 'm op je layer.`,
    });
    inhoud[doelCat] = inhoud[doelCat] ?? [];
    inhoud[doelCat].push(naam);
    bytesPer[doelCat] = (bytesPer[doelCat] ?? 0) + bytes;
  }
}

/* De packs per categorie. */
for (const cat of CATEGORIEEN) {
  if (cat.map === null) continue;
  const n = perCategorie[cat.id] ?? 0;
  if (n === 0) continue;

  rijen.push({
    id: ++id,
    category_id: cat.id,
    slug: slugify(cat.name + '-pack'),
    name: `${cat.name} Pack`,
    soort: 'pack',
    aantal: n,
    price: cat.packPrijs,
    tagline: `Alle ${n} ${stam(cat.name)}-presets in één keer`,
    is_featured: ['color-corrections', 'text-presets', 'zooms'].includes(cat.slug),
    bestandsgrootte: leesbaar(bytesPer[cat.id] ?? 0),
    includes: (inhoud[cat.id] ?? []).map((n2) => n2),
    ae_version: 'After Effects 2020 of nieuwer',
    description: `Alle ${n} presets uit m'n ${stam(cat.name)}-map, in één download. `
      + `Goedkoper dan ze los kopen.`,
  });
}

/* En alles bij elkaar. */
const totaal = Object.values(perCategorie).reduce((a, b) => a + b, 0);
rijen.push({
  id: ++id,
  category_id: 8,
  slug: 'complete-pack',
  name: 'LXRS Complete Pack',
  soort: 'bundel',
  aantal: totaal,
  price: 39.0,
  tagline: `Alle ${totaal} presets, plus m'n render- en export-instellingen`,
  is_featured: true,
  bestandsgrootte: leesbaar(Object.values(bytesPer).reduce((a, b) => a + b, 0)),
  includes: CATEGORIEEN.filter((c) => c.map !== null && (perCategorie[c.id] ?? 0) > 0)
    .map((c) => `${c.name} — ${perCategorie[c.id]} presets`)
    .concat(['M’n Handbrake-, Topaz- en render-instellingen']),
  ae_version: 'After Effects 2020 of nieuwer',
  description: `Alles wat in m'n edit pack zit: ${totaal} presets verdeeld over `
    + `${CATEGORIEEN.filter((c) => c.map !== null && (perCategorie[c.id] ?? 0) > 0).length} categorieën, `
    + `plus de instellingen waarmee ik render en exporteer.`,
});

const uit = [
  '<?php',
  '',
  '/*',
  ' * Gegenereerd met: node tools/catalogus-genereren.mjs "<pad naar het pack>"',
  ' * Handmatige wijzigingen gaan verloren als je dat opnieuw draait.',
  ' *',
  ` * ${totaal} losse presets, ${CATEGORIEEN.length - 1} packs en 1 bundel.`,
  ' */',
  '',
  'return [',
  ...rijen.map((r) => '    [' + Object.entries(r).map(([k, v]) => `'${k}' => ${php(v)}`).join(', ') + '],'),
  '];',
  '',
].join('\n');

const doel = path.resolve('app/Support/preset-data.php');
await writeFile(doel, uit, 'utf8');
console.log(`${rijen.length} producten weggeschreven naar app/Support/preset-data.php`);
console.log(`  ${totaal} losse presets`);
for (const cat of CATEGORIEEN.filter((c) => c.map !== null)) {
  console.log(`    ${cat.name.padEnd(20)} ${String(perCategorie[cat.id] ?? 0).padStart(2)}`);
}
