# Voortgang

Bewijs van vordering voor het keuzedeel Verdieping Software.

Elke regel in het logboek is een moment waarop er iets nieuws werkte. Daarbij horen
screenshots in [`snapshots/`](snapshots/) en een **git tag**: een vast label op die
versie van de code, zodat je later precies kunt terugzien hoe het project er toen uitzag.

Terug naar de [README](../README.md) · planning in [PLAN.md](PLAN.md) ·
alle versies met uitleg onder [Releases](https://github.com/LarsM04/lxrs-webshop/releases)

---

## Logboek

| # | Datum | Fase | Wat werkt er nu | Snapshot | Tag |
|---|---|---|---|---|---|
| 00 | 2026-09-18 | 0 · Setup | Repo aangemaakt, ontwerp geanalyseerd, plan van aanpak vastgelegd | — | `v0.0-setup` |
| 01 | 2026-09-18 | 0 · Setup | Laravel 13 draait in Docker met MySQL en phpMyAdmin | [laravel welkom](snapshots/2026-09-18-laravel-welkom.png) | `v0.1-laravel` |
| 02 | 2026-09-18 | 3 · Front-end | Front-end af: homepage, presets-overzicht met filter en zoek, en productdetailpagina | [home](snapshots/2026-09-18-home.png) · [home (mobiel)](snapshots/2026-09-18-home-mobiel.png) · [presets overzicht](snapshots/2026-09-18-presets-overzicht.png) · [presets overzicht (mobiel)](snapshots/2026-09-18-presets-overzicht-mobiel.png) · [preset detail](snapshots/2026-09-18-preset-detail.png) · [preset detail (mobiel)](snapshots/2026-09-18-preset-detail-mobiel.png) · [presets gefilterd](snapshots/2026-09-18-presets-gefilterd.png) · [presets gefilterd (mobiel)](snapshots/2026-09-18-presets-gefilterd-mobiel.png) | `v0.4-frontend` |
| 03 | 2026-09-18 | 3 · Front-end | Recente edits op de homepage, met klik-om-te-spelen in plaats van automatische embeds | [home met edits](snapshots/2026-09-18-home-met-edits.png) · [home met edits (mobiel)](snapshots/2026-09-18-home-met-edits-mobiel.png) | `v0.5-edits` |
| 04 | 2026-09-18 | 3 · Front-end | Echte catalogus uit het edit pack: 55 losse presets, 7 packs en het complete pack | [shop alles](snapshots/2026-09-18-shop-alles.png) · [shop alles (mobiel)](snapshots/2026-09-18-shop-alles-mobiel.png) · [shop packs](snapshots/2026-09-18-shop-packs.png) · [shop packs (mobiel)](snapshots/2026-09-18-shop-packs-mobiel.png) · [product complete pack](snapshots/2026-09-18-product-complete-pack.png) · [product complete pack (mobiel)](snapshots/2026-09-18-product-complete-pack-mobiel.png) · [product los](snapshots/2026-09-18-product-los.png) · [product los (mobiel)](snapshots/2026-09-18-product-los-mobiel.png) | `v0.6-catalogus` |
| 05 | 2026-09-25 | 1 · Database | Presets en categorieën komen live uit de database: 8 categorieën en 63 producten. De website ziet er precies hetzelfde uit als bij #04, alleen komt de data nu uit de database | [db presets](snapshots/2026-09-25-db-presets.png) · [db categories](snapshots/2026-09-25-db-categories.png) | `v0.2-database` |
| 06 | 2026-09-25 | 2 · API | Eigen API: presets en categorieën als JSON, CRUD getest met Postman | [api presets](snapshots/2026-09-25-api-presets.png) · [api categories](snapshots/2026-09-25-api-categories.png) · [postman 201 toevoegen](snapshots/2026-09-25-postman-201-toevoegen.png) · [postman 401 zonder sleutel](snapshots/2026-09-25-postman-401-zonder-sleutel.png) | `v0.3-api` |
| 07 | 2026-10-09 | 4 · Beheeromgeving | Beheerder kan inloggen, /admin is afgeschermd | [admin login](snapshots/2026-10-09-admin-login.png) · [admin login (mobiel)](snapshots/2026-10-09-admin-login-mobiel.png) · [admin start](snapshots/2026-10-09-admin-start.png) | `v0.7-login` |

> **Waarom staan de tags niet op volgorde?** De nummers volgen de fases uit het plan,
> niet de tijd. De front-end (fase 3, `v0.4`–`v0.6`) heb ik naar voren gehaald, dus
> die was eerder af dan de database (fase 1, `v0.2`). Het logboek staat wél op datum.

---

## Alle tags

| Tag | Wat | Status |
|---|---|---|
| `v0.0-setup` | Plan en repo | ✅ 18 sep |
| `v0.1-laravel` | Laravel draait in Docker | ✅ 18 sep |
| `v0.2-database` | Data uit de database | ✅ 25 sep |
| `v0.3-api` | Eigen API, getest met Postman | ✅ 25 sep |
| `v0.4-frontend` | Drie pagina's, filter en zoek | ✅ 18 sep |
| `v0.5-edits` | Recente edits op de homepage | ✅ 18 sep |
| `v0.6-catalogus` | Echte catalogus | ✅ 18 sep |
| `v0.7-login` | Inloggen voor de beheerder | ✅ 9 okt |
| `v0.8-admin-presets` | Presets toevoegen, bewerken, verwijderen | ⬜ [#2](https://github.com/LarsM04/lxrs-webshop/issues/2) |
| `v0.9-afbeeldingen` | Afbeeldingen uploaden | ⬜ [#3](https://github.com/LarsM04/lxrs-webshop/issues/3) |
| `v1.0-crud` | Beheeromgeving af, leerdoel behaald | ⬜ [#5](https://github.com/LarsM04/lxrs-webshop/issues/5) |
| `v1.1-shop` | Winkelwagen tot en met download (extra) | ⬜ [#8](https://github.com/LarsM04/lxrs-webshop/issues/8) |
| `v1.2-oplevering` | Eindversie | ⬜ [#9](https://github.com/LarsM04/lxrs-webshop/issues/9) |

---

## Zo maak je een snapshot

Dat doet het script [`tools/snapshot.mjs`](../tools/snapshot.mjs) voor je. Het maakt de
screenshots (computer én telefoon), zet een regel in het logboek hierboven en maakt een
commit met een tag. De commando's hieronder zijn voor PowerShell, de terminal in VS Code.

**Eenmalig**, na het clonen van de repo:

```powershell
cd tools
npm install
npx playwright install chromium
cd ..
```

**Elke keer als er een tag af is:**

```powershell
docker compose up -d                                       # 1. webshop aanzetten
$env:ADMIN_EMAIL="..."; $env:ADMIN_PASSWORD="..."          # 2. alleen nodig voor /admin-pagina's
node tools/snapshot.mjs v0.8-admin-presets "Wat er nu werkt"  # 3. snapshot maken
git push; git push --tags                                  # 4. naar GitHub
```

Kijk vóór stap 4 even naar de screenshots in `docs/snapshots/`. Welke pagina's bij welke
tag horen, staat bovenin `tools/snapshot.mjs` onder `MILESTONES`.

<details>
<summary>Extra opties van het script</summary>

| Vlag | Wat het doet |
|---|---|
| `--push` | Meteen naar GitHub pushen (stap 4 overslaan) |
| `--no-git` | Alleen screenshots maken, niets committen |
| `--url <u> --name <n>` | Een losse pagina vastleggen |
| `--json` | De URL is een API-endpoint: antwoord opslaan en netjes tonen |

</details>

<details>
<summary>Zelf een screenshot toevoegen (zonder script)</summary>

Voor dingen buiten de browser, zoals VS Code, Postman of een foutmelding.

1. `Win + Shift + S` en sleep het gebied
2. Opslaan in `docs/snapshots/` als `JJJJ-MM-DD-korte-omschrijving.png`
3. Een regel toevoegen aan het logboek: kopieer de vorige regel en zet het nummer één hoger
4. Committen en taggen:

```powershell
git add docs/
git commit -m "Snapshot v0.8-admin-presets: wat er nu werkt"
git tag -a v0.8-admin-presets -m "Wat er nu werkt"
git push; git push --tags
```

</details>
