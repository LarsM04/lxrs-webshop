# Voortgang en snapshots

Bewijs van vordering voor het keuzedeel Verdieping Software.

Elke regel hieronder is een moment waarop er iets nieuws aantoonbaar werkt. Bij elke
milestone hoort een screenshot in [`snapshots/`](snapshots/) en een git tag, zodat de
staat van het project op dat moment terug te halen is.

Terug naar de [README](../README.md) · planning in [PLAN.md](PLAN.md)

---

## Logboek

| # | Datum | Fase | Wat werkt er nu | Snapshot | Tag |
|---|---|---|---|---|---|
| 00 | 2026-09-18 | Setup | Repo aangemaakt, ontwerp geanalyseerd, plan van aanpak vastgelegd | — | `v0.0-setup` |
| 01 | 2026-09-18 | 0 · Setup | Laravel 13 draait in Docker met MySQL en phpMyAdmin | [bekijk](snapshots/2026-09-18-laravel-welkom.png) | `v0.1-laravel` |
| 02 | 2026-09-18 | 3 · Front-end | Front-end af: homepage, presets-overzicht met filter en zoek, en productdetailpagina | [bekijk](snapshots/2026-09-18-home.png) · [mobiel](snapshots/2026-09-18-home-mobiel.png) · [bekijk](snapshots/2026-09-18-presets-overzicht.png) · [mobiel](snapshots/2026-09-18-presets-overzicht-mobiel.png) · [bekijk](snapshots/2026-09-18-preset-detail.png) · [mobiel](snapshots/2026-09-18-preset-detail-mobiel.png) · [bekijk](snapshots/2026-09-18-presets-gefilterd.png) · [mobiel](snapshots/2026-09-18-presets-gefilterd-mobiel.png) | `v0.4-frontend` |
| 03 | 2026-09-18 | 3 · Front-end | Recente edits op de homepage, met klik-om-te-spelen in plaats van automatische embeds | [bekijk](snapshots/2026-09-18-home-met-edits.png) · [mobiel](snapshots/2026-09-18-home-met-edits-mobiel.png) | `v0.5-edits` |

<!-- Nieuwe regel bovenaan of onderaan toevoegen bij elke milestone.
     Snapshot-kolom: [bekijk](snapshots/2026-10-05-presets-overzicht.png) -->

---

## Snapshot maken — automatisch

Eenmalig, na het clonen van de repo:

```bash
cd tools
npm install
```

Daarna, telkens als een milestone af is. **Zorg dat je containers draaien**:

```bash
docker compose up -d
```

En draai dan het script met de tag van de milestone:

```bash
node tools/snapshot.mjs v0.4-frontend "Presets-overzicht laadt live uit de database"
```

Dat doet in één keer:

1. Screenshots van alle pagina's die bij die milestone horen — desktop **en** telefoon
2. Een regel toevoegen aan het logboek hierboven
3. Een commit maken met een git tag

Nog niet gepusht. Controleer de screenshots en push daarna zelf:

```bash
git push && git push --tags
```

Of laat het script het meteen doen met `--push`.

### Vlaggen

| Vlag | Wat het doet |
|---|---|
| `--push` | Pusht commit en tag direct naar GitHub |
| `--no-git` | Alleen screenshots maken, niets committen |
| `--url <u> --name <n>` | Losse pagina vastleggen, buiten de vaste milestones om |
| `--json` | Behandel de URL als API-endpoint: response opslaan én netjes renderen |

Voorbeeld van een losse opname:

```bash
node tools/snapshot.mjs --url http://localhost:8000/presets?categorie=shakes --name filter v0.5-filter "Filter op categorie werkt"
```

### Adminpaneel

Het adminpaneel zit achter een login. Zet je gegevens in de omgeving voordat je het
script draait, dan logt Playwright zelf in:

```bash
ADMIN_EMAIL="lars@lxrs2004.com" ADMIN_PASSWORD="..." node tools/snapshot.mjs v1.0-crud "Admin CRUD werkt"
```

Staan die niet ingesteld, dan slaat het script de adminpagina over en gaat de rest gewoon door.

### Als de URL's niet kloppen

De pagina's per milestone staan bovenin [`tools/snapshot.mjs`](../tools/snapshot.mjs)
in `MILESTONES`. Heten je routes anders, pas ze daar aan.

---

## Snapshot maken — handmatig

Nodig wanneer het script er niet bij kan: alles buiten de browser (VS Code, After Effects,
een foutmelding in je terminal) of iets waar je zelf doorheen moet klikken.

**1. Screenshot maken**

- Windows: `Win + Shift + S` → sleep het gebied → plakken en opslaan
- Hele venster: `Alt + PrtScn`

**2. Opslaan in `docs/snapshots/`** met deze naam:

```
JJJJ-MM-DD-korte-omschrijving.png
```

Dus bijvoorbeeld `2026-11-16-presets-overzicht.png`. De datum vooraan zorgt dat ze
vanzelf op volgorde staan.

**3. Regel toevoegen** aan het logboek bovenin dit bestand. Kopieer de vorige regel en
pas hem aan — nummer één omhoog:

```
| 04 | 2026-11-16 | 3 · Front-end | Wat er nu werkt | [bekijk](snapshots/2026-11-16-presets-overzicht.png) | `v0.4-frontend` |
```

**4. Committen en taggen:**

```bash
git add docs/
git commit -m "Snapshot v0.4-frontend: presets-overzicht laadt live uit database"
git tag -a v0.4-frontend -m "Presets-overzicht werkt met database-data"
git push && git push --tags
```

> Tag-naam al gebruikt? Kies dan een nieuwe (`v0.4.1-frontend`). Een bestaande tag
> verplaatsen kan, maar dan raak je het moment kwijt dat je juist wilde vastleggen.

---

## Geplande milestones

De tags die er aan het eind van het traject moeten staan:

| Tag | Fase | Wanneer | Waar de snapshot van is |
|---|---|---|---|
| `v0.0-setup` | 0 | sep 2026 | Plan en repo — ✅ behaald |
| `v0.1-laravel` | 0 | sep 2026 | Laravel draait in Docker — ✅ behaald |
| `v0.2-database` | 1 | okt 2026 | Beide tabellen met data in phpMyAdmin |
| `v0.3-api` | 2 | okt 2026 | `GET /api/presets`-response in Postman |
| `v0.4-frontend` | 3 | sep 2026 | Drie pagina's, filter en zoek — ✅ behaald |
| `v1.0-crud` | 4 | dec 2026 | Adminpaneel naast de bijgewerkte website |
| `v1.1-shop` | 5 | jan 2027 | Winkelwagen tot en met download |
| `v1.0-oplevering` | 6 | jan 2027 | Eindversie |

---

## Waarom dit telt als bewijs

Drie sporen die samen laten zien dat er over een langere periode aan gewerkt is:

- **Dit logboek** — wat er wanneer werkend was, met beeld erbij
- **Git tags** — terugspringen naar hoe het project er in oktober uitzag
- **Commitgeschiedenis** — datums en berichten zijn op GitHub automatisch zichtbaar
