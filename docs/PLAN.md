# Plan van aanpak — LXRS2004 Webshop

Van ontwerp naar werkende full-stack webshop. Startdatum 18 september 2026,
deadline eind januari 2027 — ongeveer 19 weken.

Elke fase eindigt met een **snapshot**: iets dat aantoonbaar werkt, een screenshot in
`docs/snapshots/`, een regel in het logboek van [VOORTGANG.md](VOORTGANG.md) en een git tag.

---

## Uitgangspunt: wat er al ligt

Het bestaande ontwerp (`design/ontwerp.dc.html`) is een one-pager met vier secties:
Shop, Werk, Accounts en Contact. Donker paars/zwart thema, Saira Condensed + Space Grotesk,
veel animatie en een custom cursor.

Drie dingen om te weten voordat we beginnen:

1. **Het ontwerp draait niet standalone.** Het is een Claude artifact-export die een
   `x-dc` runtime gebruikt en `window.React` verwacht, maar React wordt nergens geladen.
   Openen in een browser levert dus geen werkende pagina op. De HTML/CSS is prima
   bruikbaar als *referentie*, maar moet overgezet worden naar echte Blade-templates.
2. **De producten staan hardcoded** in een JavaScript-class (`renderVals()`), met
   drie vaste items: AE Extensie €24, Edit Presets €14, Clip Packs €9. Precies wat het
   leerdoel wil vervangen door database-data.
3. **Er zijn geen productafbeeldingen.** Overal staan placeholders als `[ ui-screenshot ]`
   en `[ preset-preview ]`. Het keuzedeel eist een voorbeeldafbeelding per preset, dus
   die moeten nog gemaakt worden.

---

## Fase 0 — Setup (week van 22 september)

**Doel:** werkende Laravel-omgeving en een vastgelegd databaseontwerp.

- [x] Laravel 13 aangemaakt met Composer in een Docker-container (PHP 8.4)
- [x] Laravel Sail toegevoegd met MySQL 8.4
- [x] phpMyAdmin als extra service in `compose.yaml`
- [x] `.env` ingesteld: database `lxrs_webshop`, app op poort 8000
- [x] Containers draaien: app `:8000`, phpMyAdmin `:8080`, MySQL `:3307`
- [x] Migraties gedraaid
- [x] `.gitignore` samengevoegd met die van Laravel
- [x] Snapshot-tool opgezet in `tools/` — zie [VOORTGANG.md](VOORTGANG.md)

> **Waarom Docker en niet XAMPP:** de XAMPP-installatie had PHP 8.2.12 uit 2023, wat
> Laravel 13 niet accepteert. In een container draaien we PHP 8.5 en MySQL 8.4, en kan
> iedereen het project met één commando opstarten.

**Snapshot:** screenshot van de Laravel-welkomstpagina · tag `v0.1-laravel`

---

## Fase 1 — Database en modellen (begin oktober)

**Doel:** de twee gekoppelde tabellen uit het leerdoel, gevuld met testdata.

- [x] Migration `categories`: `name`, `slug`, plus `afkorting` en `omschrijving` voor de tegels
- [x] Migration `presets`: `category_id` (foreign key), `name`, `slug`, `description`,
      `price`, `image_path`, `download_path`, `is_featured`, plus wat de echte catalogus
      nodig heeft: `soort`, `aantal`, `tagline`, `includes` (JSON), `bestandsgrootte`, `ae_version`
- [x] Model `Category` met `hasMany(Preset::class)`
- [x] Model `Preset` met `belongsTo(Category::class)`
- [x] Seeder met de categorieën — inmiddels acht, want de catalogus volgt het echte pack
- [x] Seeder met presets — niet acht voorbeelden, maar alle 63 producten uit het echte pack
      (`database/data/presets.php`)
- [x] `php artisan migrate:fresh --seed` draait zonder fouten
- [x] `PresetCatalog` van binnen vervangen door Eloquent — de views blijven gelijk
- [x] Factories en feature tests voor overzicht, filters, zoeken, detailpagina en de foreign key

> **Keuze:** een categorie waar nog presets in zitten kan niet verwijderd worden
> (`restrictOnDelete`). Anders zou één klik in de beheeromgeving (fase 4) een hele
> categorie aan producten meenemen.

> Dit is het stuk waar je bij de demo de databasestructuur op uitlegt. De foreign key
> `presets.category_id → categories.id` ís de koppeling waar het keuzedeel om vraagt.

**Snapshot:** screenshot van beide tabellen met data in phpMyAdmin · tag `v0.2-database`

---

## Fase 2 — Eigen API (half oktober)

**Doel:** eigen endpoints, net als in het kiosk-project, maar nu met Laravel eronder.

- [x] `GET    /api/presets`          — alle presets, met categorie erbij (en dezelfde filters als de site)
- [x] `GET    /api/presets/{id}`     — één preset
- [x] `POST   /api/presets`          — toevoegen
- [x] `PUT    /api/presets/{id}`     — bewerken (PATCH kan ook, dan alleen wat verandert)
- [x] `DELETE /api/presets/{id}`     — verwijderen
- [x] `GET    /api/categories`       — alle categorieën, met aantal presets
- [x] Validatie via een Form Request (naam verplicht, prijs numeriek, categorie bestaat)
- [x] API Resource zodat de JSON-uitvoer netjes en voorspelbaar is
- [x] Schrijven alleen met API-sleutel (header `X-API-Key`), lezen is open
- [x] Feature tests voor alle endpoints, validatie en de sleutel
- [x] Alle endpoints getest met Postman, met de collectie uit `docs/postman/` (25 september)

> **Keuze:** toevoegen, bewerken en verwijderen vragen om een sleutel uit `.env`.
> Zonder die sleutel kan iedereen die de URL kent je producten wissen. Een
> sleutel is voor nu genoeg; in fase 4 komt er een echte login voor de beheeromgeving.

**Snapshot:** screenshot van een `GET /api/presets`-response in Postman · tag `v0.3-api`

---

## Fase 3 — Front-end (gedaan op 18 september)

> **Let op: deze fase is naar voren gehaald.** Op mijn eigen verzoek heb ik eerst de
> front-end goed neergezet, met een vaste lijst presets in `app/Support/PresetCatalog.php`.
> In fase 1 wordt alleen de binnenkant van die klasse vervangen door Eloquent; de
> Blade-views hoeven dan niet mee te veranderen.

### 3a. Ontwerp overzetten
- [x] Layout uit het ontwerp naar `resources/views/layouts/app.blade.php`
- [x] Inline styles uit de export naar een echt `public/css/style.css`
- [x] JavaScript (custom cursor, scroll-reveal, mobiel menu) naar `public/js/main.js`
- [x] Logo geoptimaliseerd: 405 KB JPEG → 14 KB WebP met JPEG-fallback
- [x] Werkt zonder de `x-dc` runtime en zonder React

### 3b. Pagina's bouwen
- [x] **Homepage** — hero, uitgelicht aanbod, categorie-tegels, vertrouwensbalk
- [x] **Presets-overzicht** — alle presets, met categoriefilter en zoekveld
- [x] **Productdetailpagina** — beeld, prijs, wat je krijgt, specs, gerelateerde presets
- [x] Data uit de database halen in plaats van uit een vaste lijst (gedaan in fase 1)

### 3c. Verbeteringen meegenomen
- [x] `prefers-reduced-motion` — ontbrak volledig bij 12 animaties
- [x] Custom cursor alleen bij een echte muis, niet op touchscreens
- [x] Zichtbare focus-states voor toetsenbordgebruikers, plus een skiplink
- [x] Eén `h1` per pagina en geen sprongen in de koppenstructuur
- [x] Mobiel menu, en op de detailpagina staat de prijs direct onder het beeld
- [x] Lege staat als een filter niets oplevert
- [x] Kruimelpad, paginatitels en meta-omschrijvingen

**Snapshot:** tag `v0.4-frontend` · 8 screenshots, desktop en telefoon

---

## Fase 4 — Beheeromgeving / CRUD (eind november – begin december)

**Doel:** het aanbod aanpassen zonder één regel code te wijzigen. Dit is het hart
van het leerdoel.

- [ ] Filament installeren en een admin-account aanmaken
- [ ] Resource voor `Preset` — **C**reate, **R**ead, **U**pdate, **D**elete
- [ ] Resource voor `Category`
- [ ] Afbeelding uploaden vanuit het adminpaneel
- [ ] Inloggen verplicht voor `/admin`
- [ ] Testen: preset toevoegen in admin → verschijnt direct op de website

**Snapshot:** twee screenshots naast elkaar — adminpaneel en de website die meteen
de nieuwe preset toont · tag `v1.0-crud`

> Na deze fase is het volledige leerdoel behaald. Alles hierna is bonus.

---

## Fase 5 — Stretch goals (december – half januari)

Optioneel, voor een hogere beoordeling. Pak ze in deze volgorde — de eerste is het
meest zichtbaar bij een demo.

- [ ] **Zoeken en filteren op categorie** — kleinste moeite, direct resultaat
- [ ] **Winkelwagen** — sessiegebaseerd, zoals de bestelflow uit het kiosk-project
- [ ] **Afrekenflow** — order en order_items opslaan, zonder echte betaalprovider
- [ ] **Downloadlink na gesimuleerde aankoop** — bestand alleen bereikbaar via een
      route die controleert of er een bestelling is

**Snapshot:** schermopname van de hele flow, van winkelwagen tot download · tag `v1.1-shop`

---

## Fase 6 — Afronden en demo (half januari – eind januari)

- [ ] Alles nalopen op fouten en losse eindjes
- [ ] README en VOORTGANG.md compleet, met het volledige snapshotlogboek
- [ ] Databasediagram tekenen voor de toelichting
- [ ] Demo oefenen: presets bekijken → preset toevoegen → live zichtbaar
- [ ] Back-up van de database-export in de repo

**Snapshot:** eindversie · tag `v1.0-oplevering`

---

## Risico's

| Risico | Kans | Wat ik doe |
|---|---|---|
| Fase 3 loopt uit (ontwerp omzetten is meer werk dan het lijkt) | Groot | Eerst één pagina helemaal af, pas daarna de rest |
| Geen productafbeeldingen beschikbaar | Groot | Vroeg beginnen met previews maken, placeholders tot die tijd |
| Filament wijkt te veel af van "zelf gebouwd" | Klein | API-endpoints schrijf ik zelf, dus het eigen werk blijft aantoonbaar |
| Stretch goals kosten alle tijd | Middel | Pas beginnen als fase 4 helemaal af is |
