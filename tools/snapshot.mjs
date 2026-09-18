#!/usr/bin/env node
/**
 * Automatische snapshots voor het keuzedeel.
 *
 *   node tools/snapshot.mjs <tag> "beschrijving"
 *
 * Maakt screenshots van de pagina's die bij die milestone horen, zet een regel
 * in docs/VOORTGANG.md en maakt een commit met een git tag.
 *
 * Vlaggen:
 *   --push                 ook meteen naar GitHub pushen
 *   --no-git               alleen screenshots maken, niets committen
 *   --url <u> --name <n>   losse pagina vastleggen (zonder milestone-config)
 */

import { chromium } from 'playwright';
import { mkdir, writeFile, readFile } from 'node:fs/promises';
import { execFileSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const SNAPDIR = path.join(ROOT, 'docs', 'snapshots');
const LOGBOEK = path.join(ROOT, 'docs', 'VOORTGANG.md');

const DESKTOP = { width: 1440, height: 900 };
const MOBIEL = { width: 390, height: 844 };

/* ---------------------------------------------------------------------------
 * Welke pagina's horen bij welke milestone.
 * Pas de URL's aan zodra je weet hoe je routes heten.
 * ------------------------------------------------------------------------ */
const MILESTONES = {
  'v0.1-laravel': {
    fase: '0 · Setup',
    shots: [
      { name: 'laravel-welkom', url: 'http://localhost:8000', mobiel: false },
    ],
  },
  'v0.2-database': {
    fase: '1 · Database',
    shots: [
      { name: 'db-presets', url: 'http://localhost:8080/index.php?route=/sql&db=lxrs_webshop&table=presets', mobiel: false },
      { name: 'db-categories', url: 'http://localhost:8080/index.php?route=/sql&db=lxrs_webshop&table=categories', mobiel: false },
    ],
  },
  'v0.3-api': {
    fase: '2 · API',
    shots: [
      { name: 'api-presets', url: 'http://localhost:8000/api/presets', json: true },
      { name: 'api-categories', url: 'http://localhost:8000/api/categories', json: true },
    ],
  },
  'v0.4-frontend': {
    fase: '3 · Front-end',
    shots: [
      { name: 'home', url: 'http://localhost:8000', traag: true },
      { name: 'presets-overzicht', url: 'http://localhost:8000/presets' },
      { name: 'preset-detail', url: 'http://localhost:8000/presets/neon-grade' },
      { name: 'presets-gefilterd', url: 'http://localhost:8000/presets?categorie=shakes' },
    ],
  },
  'v0.5-edits': {
    fase: '3 · Front-end',
    shots: [
      { name: 'home-met-edits', url: 'http://localhost:8000', traag: true },
    ],
  },
  'v0.6-catalogus': {
    fase: '3 · Front-end',
    shots: [
      { name: 'shop-alles', url: 'http://localhost:8000/presets' },
      { name: 'shop-packs', url: 'http://localhost:8000/presets?soort=pack' },
      { name: 'product-complete-pack', url: 'http://localhost:8000/presets/complete-pack' },
      { name: 'product-los', url: 'http://localhost:8000/presets/midnight-cc-color-corrections' },
    ],
  },
  'v1.0-crud': {
    fase: '4 · Admin CRUD',
    shots: [
      { name: 'admin-presets', url: 'http://localhost:8000/admin/presets', login: true, mobiel: false },
      { name: 'website-na-wijziging', url: 'http://localhost:8000/presets' },
    ],
  },
  'v1.1-shop': {
    fase: '5 · Stretch goals',
    shots: [
      { name: 'winkelwagen', url: 'http://localhost:8000/winkelwagen' },
      { name: 'download-na-aankoop', url: 'http://localhost:8000/downloads' },
    ],
  },
  'v1.0-oplevering': {
    fase: '6 · Oplevering',
    shots: [
      { name: 'eindversie-home', url: 'http://localhost:8000' },
      { name: 'eindversie-overzicht', url: 'http://localhost:8000/presets' },
      { name: 'eindversie-admin', url: 'http://localhost:8000/admin/presets', login: true, mobiel: false },
    ],
  },
};

/* Inloggegevens voor het adminpaneel komen uit de omgeving, niet uit de code. */
const ADMIN = {
  url: 'http://localhost:8000/admin/login',
  email: process.env.ADMIN_EMAIL ?? '',
  wachtwoord: process.env.ADMIN_PASSWORD ?? '',
};

/* ------------------------------------------------------------------------ */

const vandaag = () => new Date().toISOString().slice(0, 10);

function parseArgs(argv) {
  const vlaggen = new Set();
  const opties = {};
  const los = [];
  for (let i = 0; i < argv.length; i++) {
    const a = argv[i];
    if (a === '--url' || a === '--name') opties[a.slice(2)] = argv[++i];
    else if (a.startsWith('--')) vlaggen.add(a);
    else los.push(a);
  }
  return { vlaggen, opties, tag: los[0], beschrijving: los[1] };
}

async function bereikbaar(url) {
  try {
    const u = new URL(url);
    const res = await fetch(u.protocol + '//' + u.host, { signal: AbortSignal.timeout(4000) });

    /* Body altijd afsluiten. Laat je 'm openstaan, dan klapt undici in Node 24
       er even later op met een assertion zodra de socket dichtgaat. */
    await res.body?.cancel().catch(() => {});

    return true;
  } catch {
    return false;
  }
}

const escapeHtml = (s) => s.replace(/[&<>]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' })[c]);

/* JSON-endpoint ophalen, opslaan als .json én als leesbare afbeelding renderen. */
async function jsonShot(page, shot, datum) {
  const res = await fetch(shot.url);
  const tekst = await res.text();
  let mooi = tekst;
  try {
    mooi = JSON.stringify(JSON.parse(tekst), null, 2);
  } catch {
    /* geen geldige JSON - dan tonen we de ruwe tekst */
  }

  const jsonPad = path.join(SNAPDIR, datum + '-' + shot.name + '.json');
  await writeFile(jsonPad, mooi, 'utf8');

  const html = [
    '<!doctype html><meta charset="utf-8">',
    '<style>',
    '  body { margin:0; background:#07060c; color:#efedf7;',
    '         font:13px/1.55 "Cascadia Code",Consolas,monospace; padding:28px 32px; }',
    '  .kop { font-family:system-ui,sans-serif; margin-bottom:18px;',
    '         padding-bottom:14px; border-bottom:1px solid rgba(139,116,255,.35); }',
    '  .m { color:#b4a4ff; font-weight:700; font-size:15px; }',
    '  .s { color:#7de08a; margin-left:10px; font-size:13px; }',
    '  .u { color:#8a85a3; font-size:12px; margin-top:6px; }',
    '  pre { margin:0; white-space:pre-wrap; word-break:break-word; }',
    '</style>',
    '<div class="kop">',
    '  <span class="m">GET</span><span class="s">' + res.status + ' ' + res.statusText + '</span>',
    '  <div class="u">' + escapeHtml(shot.url) + '</div>',
    '</div>',
    '<pre>' + escapeHtml(mooi) + '</pre>',
  ].join('\n');

  await page.setContent(html, { waitUntil: 'load' });
  const png = path.join(SNAPDIR, datum + '-' + shot.name + '.png');
  await page.screenshot({ path: png, fullPage: true });
  return [path.basename(png), path.basename(jsonPad)];
}

async function paginaShot(context, shot, datum, viewport, achtervoegsel) {
  const page = await context.newPage();
  await page.setViewportSize(viewport);
  await page.emulateMedia({ reducedMotion: 'reduce' }); // stabiele, niet-bewegende opname

  /* Niet op 'networkidle' wachten: TikTok-embeds blijven verkeer maken, dus
     dat moment komt nooit. 'load' plus even laten bezinken is genoeg. */
  await page.goto(shot.url, { waitUntil: 'load', timeout: 45000 });
  await page.evaluate(() => document.fonts.ready).catch(() => {});

  /* Chromium haalt bij een fullPage-opname de verkeerde inhoud onder een
     backdrop-filter vandaan, waardoor er een spookbeeld van de footer
     bovenaan verschijnt. In een echte browser gebeurt dat niet, dus zetten
     we het filter alleen tijdens het fotograferen uit. */
  await page.addStyleTag({ content: '*, *::before, *::after { backdrop-filter: none !important; }' });

  await page.waitForTimeout(shot.traag ? 6000 : 900);
  const bestand = path.join(SNAPDIR, datum + '-' + shot.name + achtervoegsel + '.png');
  await page.screenshot({ path: bestand, fullPage: true });
  await page.close();
  return path.basename(bestand);
}

async function inloggen(context) {
  if (!ADMIN.email || !ADMIN.wachtwoord) {
    console.log('  ! ADMIN_EMAIL / ADMIN_PASSWORD niet gezet - adminpagina overgeslagen');
    return false;
  }
  const page = await context.newPage();
  await page.goto(ADMIN.url, { waitUntil: 'networkidle' });
  await page.fill('input[type="email"]', ADMIN.email);
  await page.fill('input[type="password"]', ADMIN.wachtwoord);
  await page.click('button[type="submit"]');
  await page.waitForLoadState('networkidle');
  await page.close();
  return true;
}

/* Nieuwe regel onderaan de logboektabel van VOORTGANG.md zetten. */
async function logboekBijwerken({ datum, fase, beschrijving, bestanden, tag }) {
  const inhoud = await readFile(LOGBOEK, 'utf8');
  const regels = inhoud.split(/\r?\n/);

  /* Anker op de koprij van de logboektabel en loop alleen de regels af die er
     direct op volgen. Zonder dat anker pikt hij ook het voorbeeld op dat
     verderop in de handleiding in een code-blok staat. */
  const kop = regels.findIndex((r) => /^\|\s*#\s*\|\s*Datum\s*\|/.test(r));
  if (kop === -1) throw new Error('Logboektabel niet gevonden in docs/VOORTGANG.md');

  let laatste = kop + 1; // de |---|---| scheidingsregel
  while (laatste + 1 < regels.length && /^\|\s*\d+\s*\|/.test(regels[laatste + 1])) laatste++;
  if (laatste === kop + 1) throw new Error('Logboektabel heeft nog geen regel om op verder te tellen');

  const vorigNr = parseInt(regels[laatste].split('|')[1].trim(), 10);
  const nr = String(vorigNr + 1).padStart(2, '0');

  const links = bestanden.length
    ? bestanden
        .map((b) => '[' + (b.includes('mobiel') ? 'mobiel' : 'bekijk') + '](snapshots/' + b + ')')
        .join(' · ')
    : '—';

  regels.splice(
    laatste + 1,
    0,
    '| ' + nr + ' | ' + datum + ' | ' + fase + ' | ' + beschrijving + ' | ' + links + ' | `' + tag + '` |'
  );

  await writeFile(LOGBOEK, regels.join('\n'), 'utf8');
  return nr;
}

function git(args) {
  return execFileSync('git', args, { cwd: ROOT, encoding: 'utf8' }).trim();
}

/* ------------------------------------------------------------------------ */

async function main() {
  const { vlaggen, opties, tag, beschrijving } = parseArgs(process.argv.slice(2));

  if (!tag) {
    console.error('Gebruik: node tools/snapshot.mjs <tag> "beschrijving" [--push] [--no-git]');
    console.error('Bekende milestones: ' + Object.keys(MILESTONES).join(', '));
    process.exit(1);
  }

  const alsJson = vlaggen.has('--json');
  const config = opties.url
    ? {
        fase: 'Los',
        shots: [{ name: opties.name ?? 'snapshot', url: opties.url, json: alsJson, mobiel: !alsJson }],
      }
    : MILESTONES[tag];

  if (!config) {
    console.error('Onbekende milestone "' + tag + '".');
    console.error('Kies uit: ' + Object.keys(MILESTONES).join(', '));
    console.error('Of gebruik: --url http://localhost:8000/iets --name iets');
    process.exit(1);
  }

  const datum = vandaag();
  const tekst = beschrijving ?? tag;
  await mkdir(SNAPDIR, { recursive: true });

  const eerste = config.shots[0].url;
  if (!(await bereikbaar(eerste))) {
    console.error('\nServer niet bereikbaar op ' + new URL(eerste).host + '.');
    console.error('Start je containers eerst:  docker compose up -d');
    console.error('Voor phpMyAdmin: start Apache en MySQL in het XAMPP-configuratiescherm.\n');
    process.exit(1);
  }

  console.log('\nSnapshot ' + tag + ' - ' + datum + '\n');
  const browser = await chromium.launch();
  const context = await browser.newContext({ deviceScaleFactor: 2 });
  const gemaakt = [];
  let ingelogd = false;

  for (const shot of config.shots) {
    try {
      if (shot.login && !ingelogd) {
        ingelogd = await inloggen(context);
        if (!ingelogd) continue;
      }

      if (shot.json) {
        const page = await context.newPage();
        await page.setViewportSize(DESKTOP);
        const bestanden = await jsonShot(page, shot, datum);
        await page.close();
        gemaakt.push(bestanden[0]);
        bestanden.forEach((b) => console.log('  + ' + b));
        continue;
      }

      const d = await paginaShot(context, shot, datum, DESKTOP, '');
      gemaakt.push(d);
      console.log('  + ' + d);

      if (shot.mobiel !== false) {
        const m = await paginaShot(context, shot, datum, MOBIEL, '-mobiel');
        gemaakt.push(m);
        console.log('  + ' + m);
      }
    } catch (e) {
      console.error('  ! ' + shot.name + ' mislukt: ' + e.message.split('\n')[0]);
    }
  }

  await browser.close();

  if (!gemaakt.length) {
    console.error('\nGeen screenshots gelukt - logboek niet aangepast.\n');
    process.exit(1);
  }

  const nr = await logboekBijwerken({ datum, fase: config.fase, beschrijving: tekst, bestanden: gemaakt, tag });
  console.log('\n  Logboekregel ' + nr + ' toegevoegd aan docs/VOORTGANG.md');

  if (vlaggen.has('--no-git')) {
    console.log('\n  (--no-git) Niets gecommit.\n');
    return;
  }

  git(['add', 'docs/']);
  git(['-c', 'commit.gpgsign=false', 'commit', '-m', 'Snapshot ' + tag + ': ' + tekst]);
  try {
    git(['tag', '-a', tag, '-m', tekst]);
    console.log('  Commit en tag ' + tag + ' aangemaakt');
  } catch {
    console.log('  Commit aangemaakt (tag ' + tag + ' bestond al)');
  }

  if (vlaggen.has('--push')) {
    git(['push']);
    git(['push', '--tags']);
    console.log('  Gepusht naar GitHub');
  } else {
    console.log('\n  Nog niet gepusht. Doe dat met:  git push && git push --tags');
  }
  console.log('');
}

main().catch((e) => {
  console.error('\nFout:', e.message, '\n');
  process.exit(1);
});
