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

## Fase 4 — Beheeromgeving / CRUD (oktober – begin december)

**Doel:** het aanbod aanpassen zonder één regel code te wijzigen. Dit is het hart
van het leerdoel.

> **Keuze: zelf bouwen in plaats van Filament.** Filament levert in een middag een
> compleet adminpaneel, maar dan doet een pakket het werk. Zelf bouwen kost meer tijd,
> maar zo laat ik zien dat ik inloggen, formulieren, validatie en uploads zelf kan
> maken — en daar gaat het om bij *Verdieping* Software. Er komt ook geen extra pakket bij.

> **Keuze: één beheerder, geen registratie.** Alleen ik beheer de shop. Het account
> komt uit de seeder, met de gegevens uit `.env`. Wie geen account heeft, komt er niet in.

> **De API blijft zoals hij is.** Die werkt met de sleutel, de beheeromgeving met een
> login. Twee ingangen, maar dezelfde validatie (`PresetRequest`).

### Overzicht

Elke stap is los af te ronden en te testen. Na stap 1, 4, 5 en 7 komt er een snapshot
met een git tag, zodat de voortgang ook tussendoor zichtbaar is.

| Stap | Wat | CRUD | Streefdatum | Tag | Status |
|---|---|---|---|---|---|
| 1 | [Inloggen voor de beheerder](#stap-1--inloggen-voor-de-beheerder) | | 16 okt | `v0.7-login` | ✅ |
| 2 | [Overzicht van alle presets](#stap-2--overzicht-van-alle-presets) | **R** | 23 okt | | ✅ |
| 3 | [Preset toevoegen en bewerken](#stap-3--preset-toevoegen-en-bewerken) | **C U** | 6 nov | | ⬜ |
| 4 | [Preset verwijderen](#stap-4--preset-verwijderen) | **D** | 13 nov | `v0.8-admin-presets` | ⬜ |
| 5 | [Afbeelding uploaden](#stap-5--afbeelding-uploaden) | | 20 nov | `v0.9-afbeeldingen` | ⬜ |
| 6 | [Categorieën beheren](#stap-6--categorieën-beheren) | **CRUD** | 27 nov | | ⬜ |
| 7 | [Afronden en demo-test](#stap-7--afronden-en-demo-test) | | 4 dec | `v1.0-crud` | ⬜ |

Status: ⬜ nog niet begonnen · 🔄 mee bezig · ✅ af

**Snapshot maken (PowerShell).** Zet eerst je inloggegevens, dan kan het script ook de
beheeromgeving fotograferen. Ze blijven alleen bewaard zolang het terminalvenster open is.
Vink de stap eerst af in dit bestand: het script commit alles in `docs/` mee.

```powershell
$env:ADMIN_EMAIL="…"; $env:ADMIN_PASSWORD="…"
node tools/snapshot.mjs <tag> "<wat er nu werkt>"
git push; git push --tags
```

### Stap 1 — Inloggen voor de beheerder

Alles wat hierna komt zit achter deze login, dus die moet er als eerste staan.

- [x] `ADMIN_NAME`, `ADMIN_EMAIL` en `ADMIN_PASSWORD` in `.env` — dezelfde namen die
      het snapshot-script al gebruikt
- [x] Een eigen `AdminSeeder` maakt met die gegevens de beheerder aan, in plaats van de
      `Test User`. Ook los te draaien: `php artisan db:seed --class=AdminSeeder`
- [x] Loginpagina op `/admin/login`: e-mail, wachtwoord en een knop *Inloggen*
- [x] Inloggen en uitloggen in een eigen `Admin\LoginController`, zonder starterkit
- [x] Na het inloggen een nieuw sessie-id, zodat niemand een oude sessie kan overnemen
- [x] Na 5 foute pogingen een minuut wachten, tegen het raden van wachtwoorden
- [x] Alles onder `/admin` achter de `auth`-middleware; zonder login → naar `/admin/login`
- [x] Eigen layout `layouts/admin.blade.php`, met de kleuren en het lettertype van de site
      en een menu: Start · Naar de website · Uitloggen. *Presets* en *Categorieën*
      komen erbij in stap 2 en 6
- [x] Startpagina `/admin` met een paar tellers: presets, categorieën, uitgelicht

**Testen:** gast wordt doorgestuurd naar de loginpagina · fout wachtwoord geeft een
melding · goed wachtwoord laat je binnen · uitloggen werkt · na 5 foute pogingen geblokkeerd

**Klaar als:** ik met de gegevens uit `.env` kan inloggen en `/admin` zonder login niet te zien is.

**Snapshot:** `node tools/snapshot.mjs v0.7-login "Beheerder kan inloggen, /admin is afgeschermd"`

### Stap 2 — Overzicht van alle presets

De **R** van CRUD: alles op een rij, zodat ik snel vind wat ik wil aanpassen.

- [x] `/admin/presets`: tabel met afbeelding, naam, categorie, soort, prijs en uitgelicht
- [x] Zoeken op naam en filteren op categorie en soort, net als op de website. Dezelfde
      code: `PresetCatalog::filterQuery()` wordt door allebei gebruikt
- [x] 25 per pagina, want 63 rijen is te veel voor één scherm. Bij het bladeren blijven
      je filters staan
- [x] De categorie in één keer meeladen (`with('category')`), anders doet elke rij een
      eigen query (het N+1-probleem)
- [x] Per rij een link *Bekijk op de site*. *Bewerken* en *Verwijderen* komen erbij in
      stap 3 en 4
- [x] Lege staat als zoeken niets oplevert
- [x] *Presets* in het menu van de beheeromgeving

**Testen:** gast wordt doorgestuurd · tabel toont categorie, soort, prijs en uitgelicht ·
de drie filters samen · 25 per pagina · filters blijven staan bij bladeren · lege staat ·
de categorieën in één query · HTML in een naam wordt niet uitgevoerd

**Klaar als:** alle 63 producten in de tabel staan en ik er met zoeken snel eentje vind.

### Stap 3 — Preset toevoegen en bewerken

De **C** en de **U**. Toevoegen en bewerken delen één formulier.

- [ ] `/admin/presets/create` en `/admin/presets/{id}/edit`, met één gedeeld formulier
      in `admin/presets/_formulier.blade.php`
- [ ] Velden: categorie (keuzelijst), naam, slug (leeg = automatisch), soort, aantal,
      prijs, tagline, omschrijving, *wat je krijgt* (één per regel), bestandsgrootte,
      AE-versie en uitgelicht (vinkje)
- [ ] Dezelfde validatie als de API: een `Admin\PresetFormRequest` bouwt voort op
      `PresetRequest` en zet alleen het tekstvak *wat je krijgt* om naar een lijst en
      het vinkje naar `true`/`false`
- [ ] Bij een fout: de melding staat bij het veld zelf en wat je al had ingevuld blijft staan
- [ ] Foutmeldingen in het Nederlands: `APP_LOCALE=nl` en een eigen `lang/nl/validation.php`
- [ ] Na opslaan terug naar het overzicht, met de melding *"Preset opgeslagen"*
- [ ] Knop *Nieuwe preset* boven de tabel en per rij een link *Bewerken*

**Testen:** toevoegen slaat op · bewerken past aan · lege naam of prijs `abc` geeft een
fout · slug wordt automatisch gemaakt · *wat je krijgt* wordt een lijst

**Klaar als:** een preset die ik in de beheeromgeving toevoeg meteen op `/presets` staat.

### Stap 4 — Preset verwijderen

De **D**. Daarmee is de CRUD voor presets compleet.

- [ ] *Verwijderen* is een klein formulier met `DELETE`, geen gewone link — een link kan
      door een browser of bot worden geopend zonder dat iemand erop klikt
- [ ] Eerst bevestigen: *"Weet je zeker dat je Midnight CC wilt verwijderen?"*
- [ ] Na verwijderen de melding *"Preset verwijderd"*
- [ ] Per rij in het overzicht een knop *Verwijderen*

**Testen:** verwijderen haalt de preset uit de database · een gast kan niets verwijderen

**Klaar als:** ik een preset kan toevoegen, bewerken en verwijderen zonder Postman.

**Snapshot:** `node tools/snapshot.mjs v0.8-admin-presets "Presets toevoegen, bewerken en verwijderen in de beheeromgeving"`

### Stap 5 — Afbeelding uploaden

Nog geen enkel product heeft een afbeelding: `image_path` is overal leeg. De views
zoeken al in `storage/`, dus zodra uploaden werkt verschijnen ze vanzelf op de site.

- [ ] `php artisan storage:link`, en die stap ook in de installatie van de README
- [ ] Bestandsveld in het formulier (`enctype="multipart/form-data"`)
- [ ] Alleen afbeeldingen: jpg, png of webp, maximaal 2 MB
- [ ] Opslaan in `storage/app/public/presets/`, het pad in `image_path`
- [ ] Bij een nieuwe afbeelding, of als de preset weg gaat, wordt het oude bestand opgeruimd
- [ ] In het bewerkformulier de huidige afbeelding als voorbeeld, met een vinkje
      *afbeelding weghalen*
- [ ] Buiten de code: previews maken. Begin met de 7 packs en het complete pack, die
      staan het meest in beeld

**Testen:** met `Storage::fake()` en een nep-afbeelding · uploaden slaat het bestand op ·
een pdf wordt geweigerd · de oude afbeelding wordt opgeruimd

**Klaar als:** een geüploade afbeelding op de kaart en op de detailpagina staat.

**Snapshot:** `node tools/snapshot.mjs v0.9-afbeeldingen "Afbeeldingen uploaden vanuit de beheeromgeving"`

### Stap 6 — Categorieën beheren

Volledige CRUD voor de tweede tabel.

- [ ] `/admin/categories`: lijst met per categorie het aantal presets
- [ ] Toevoegen en bewerken: naam, slug, afkorting (max. 20 tekens) en omschrijving,
      met een eigen `CategoryRequest`
- [ ] Een categorie met presets erin verwijderen geeft een duidelijke melding
      (*"Er zitten nog 8 presets in Shakes"*) in plaats van een databasefout.
      `restrictOnDelete` uit fase 1 blijft als vangnet
- [ ] *Categorieën* in het menu van de beheeromgeving

**Testen:** toevoegen, bewerken en verwijderen · een lege categorie kan weg · een
categorie met presets niet

**Klaar als:** een nieuwe categorie meteen als filter op `/presets` staat.

### Stap 7 — Afronden en demo-test

- [ ] Eén test die elke `/admin`-route als gast probeert, zodat er nooit eentje open staat
- [ ] Alle tests groen (`php artisan test`) en Pint gedraaid
- [ ] Demo-test: preset toevoegen in de beheeromgeving → staat direct op de website →
      bewerken → verwijderen
- [ ] Handig voor de demo: een link *Bewerken* op de productpagina als ik ben ingelogd
- [ ] README bijwerken: hoe je inlogt, de `ADMIN_`-gegevens in `.env` en `storage:link`
- [ ] De bewijs-vinkjes in de README afvinken

**Snapshot:** twee screenshots naast elkaar — adminpaneel en de website die meteen
de nieuwe preset toont · tag `v1.0-crud`

```powershell
node tools/snapshot.mjs v1.0-crud "Beheeromgeving werkt: aanbod aanpassen zonder code"
```

> Na deze fase is het volledige leerdoel behaald. Alles hierna is bonus.

---

## Fase 5 — Stretch goals (december – half januari)

Optioneel, voor een hogere beoordeling. Pak ze in deze volgorde — de eerste is het
meest zichtbaar bij een demo.

- [x] **Zoeken en filteren op categorie** — al gebouwd in fase 3, samen met het filter op soort
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
| Zelf bouwen kost meer tijd dan Filament | Middel | Zeven kleine stappen die elk los af zijn; begin december is er nog ruimte voor uitloop |
| Stretch goals kosten alle tijd | Middel | Pas beginnen als fase 4 helemaal af is |
