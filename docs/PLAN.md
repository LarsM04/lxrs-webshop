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

- [ ] Composer installeren (PHP 8.2 staat al in XAMPP)
- [ ] `composer create-project laravel/laravel .` in deze map
- [ ] MySQL-database `lxrs_webshop` aanmaken via phpMyAdmin
- [ ] `.env` instellen op de lokale database
- [ ] `php artisan serve` draait op `localhost:8000`
- [ ] `.gitignore` controleren (`vendor/`, `.env` en `node_modules/` eruit)

**Snapshot:** screenshot van de Laravel-welkomstpagina · tag `v0.1-laravel`

---

## Fase 1 — Database en modellen (begin oktober)

**Doel:** de twee gekoppelde tabellen uit het leerdoel, gevuld met testdata.

- [ ] Migration `categories`: `name`, `slug`
- [ ] Migration `presets`: `category_id` (foreign key), `name`, `slug`, `description`,
      `price`, `image_path`, `download_path`, `is_featured`
- [ ] Model `Category` met `hasMany(Preset::class)`
- [ ] Model `Preset` met `belongsTo(Category::class)`
- [ ] Seeder met de vier categorieën: Color Corrections, Text Presets, Shakes, Zooms
- [ ] Seeder met een stuk of acht voorbeeldpresets
- [ ] `php artisan migrate:fresh --seed` draait zonder fouten

> Dit is het stuk waar je bij de demo de databasestructuur op uitlegt. De foreign key
> `presets.category_id → categories.id` ís de koppeling waar het keuzedeel om vraagt.

**Snapshot:** screenshot van beide tabellen met data in phpMyAdmin · tag `v0.2-database`

---

## Fase 2 — Eigen API (half oktober)

**Doel:** eigen endpoints, net als in het kiosk-project, maar nu met Laravel eronder.

- [ ] `GET    /api/presets`          — alle presets, met categorie erbij
- [ ] `GET    /api/presets/{id}`     — één preset
- [ ] `POST   /api/presets`          — toevoegen
- [ ] `PUT    /api/presets/{id}`     — bewerken
- [ ] `DELETE /api/presets/{id}`     — verwijderen
- [ ] `GET    /api/categories`       — alle categorieën
- [ ] Validatie via een Form Request (naam verplicht, prijs numeriek, categorie bestaat)
- [ ] API Resource zodat de JSON-uitvoer netjes en voorspelbaar is
- [ ] Alle vijf endpoints getest met Postman of Thunder Client

**Snapshot:** screenshot van een `GET /api/presets`-response in Postman · tag `v0.3-api`

---

## Fase 3 — Front-end omzetten (eind oktober – half november)

**Doel:** het ontwerp wordt een echte website die live uit de database leest.
Dit is de grootste fase — plan er ruim tijd voor.

### 3a. Ontwerp overzetten
- [ ] Layout uit het ontwerp naar `resources/views/layouts/app.blade.php`
- [ ] Inline styles uit de export naar een echt `public/css/style.css`
- [ ] JavaScript (custom cursor, scroll-reveal, marquee) naar `public/js/main.js`
- [ ] Logo optimaliseren — die is nu 415 KB als JPEG, kan naar WebP onder de 50 KB
- [ ] Controleren dat alles werkt zonder de `x-dc` runtime en zonder React

### 3b. Pagina's bouwen
- [ ] **Homepage** — hero en uitgelicht aanbod (presets met `is_featured = true`)
- [ ] **Presets-overzicht** — alle presets uit de database, in de grid van het ontwerp
- [ ] **Productdetailpagina** — naam, beschrijving, prijs, afbeelding, categorie

### 3c. Verbeteringen meenemen
- [ ] `prefers-reduced-motion` toevoegen (er zijn nu 12 animaties en geen ontsnapping)
- [ ] Custom cursor uitzetten op touchscreens
- [ ] Echte `alt`-teksten op alle afbeeldingen
- [ ] Testen op telefoonbreedte — de layout is fluid, maar de header is nog niet getest

**Snapshot:** screenshot van het overzicht met database-data · tag `v0.4-frontend`

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
