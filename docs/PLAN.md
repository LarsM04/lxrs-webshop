# Plan van aanpak

Van ontwerp naar werkende webshop. Gestart op 18 september 2026, deadline eind januari 2027.

Terug naar de [README](../README.md) · bewijs in [VOORTGANG.md](VOORTGANG.md)

---

## Overzicht

| Fase | Wat | Wanneer | Status |
|---|---|---|---|
| 0 | [Setup](#fase-0--setup) | sep 2026 | ✅ |
| 1 | [Database en modellen](#fase-1--database-en-modellen) | sep 2026 | ✅ |
| 2 | [Eigen API](#fase-2--eigen-api) | sep 2026 | ✅ |
| 3 | [Front-end](#fase-3--front-end) | sep 2026 | ✅ |
| 4 | [Beheeromgeving (CRUD)](#fase-4--beheeromgeving-crud) | okt – dec 2026 | 🔄 stap 3 van 7 |
| 5 | [Extra's](#fase-5--extras) | dec 2026 – jan 2027 | ⬜ |
| 6 | [Afronden en demo](#fase-6--afronden-en-demo) | jan 2027 | ⬜ |

✅ af · 🔄 mee bezig · ⬜ nog niet begonnen

**Wat er nog moet gebeuren, staat als taak in de
[Issues](https://github.com/LarsM04/lxrs-webshop/issues)**, gegroepeerd per fase onder
[Milestones](https://github.com/LarsM04/lxrs-webshop/milestones). Daar vink je de
stappen af. Dit bestand legt vooral uit *wat* er per fase gebeurt en *waarom*.

Na elke belangrijke stap komt er een snapshot: screenshots, een regel in het logboek
van [VOORTGANG.md](VOORTGANG.md) en een git tag.

---

## Fase 0 — Setup

✅ **Af op 18 september** · tag `v0.0-setup` en `v0.1-laravel`

Laravel 13 draait in Docker (Laravel Sail), met MySQL 8.4 en phpMyAdmin erbij.

| Wat | Waar |
|---|---|
| Website | http://localhost:8000 |
| phpMyAdmin | http://localhost:8080 |
| MySQL | `localhost:3307` |

> **Waarom Docker en niet XAMPP?** Mijn XAMPP had PHP 8.2 uit 2023, en dat accepteert
> Laravel 13 niet. In Docker draaien PHP en MySQL in een eigen container met de juiste
> versie, en iedereen kan het project met één commando opstarten.

Het ontwerp in `design/ontwerp.dc.html` werkt niet los in een browser (het is een export
die React verwacht). Ik gebruik het als **voorbeeld** en heb het zelf omgezet naar Laravel.

---

## Fase 1 — Database en modellen

✅ **Af op 25 september** · tag `v0.2-database`

Twee tabellen die aan elkaar gekoppeld zijn: **een categorie heeft veel presets**.

- Tabel `categories` en tabel `presets`, gemaakt met **migrations** (bestanden die de
  tabellen voor je aanmaken)
- De koppeling: `presets.category_id` verwijst naar `categories.id` (een *foreign key*)
- Model `Category` met `hasMany(Preset::class)` en model `Preset` met `belongsTo(Category::class)`
- **Seeders** vullen de database met de echte catalogus: 8 categorieën en 63 producten
  (uit `database/data/presets.php`)
- Tests voor het overzicht, de filters, het zoeken, de detailpagina en de koppeling

> **Keuze:** een categorie waar nog presets in zitten kan niet verwijderd worden
> (`restrictOnDelete`). Anders zou één klik in de beheeromgeving een hele categorie
> aan producten meenemen.

> **Voor de demo:** hier leg je de databasestructuur uit. De foreign key
> `presets.category_id → categories.id` is de koppeling waar het keuzedeel om vraagt.

---

## Fase 2 — Eigen API

✅ **Af op 25 september** · tag `v0.3-api`

Dezelfde data als de website, maar als JSON, zodat andere programma's (zoals Postman)
ermee kunnen werken. Alle endpoints staan in de [README](../README.md#api).

- Lezen (`GET`) kan iedereen
- Toevoegen, bewerken en verwijderen (`POST`, `PUT`, `DELETE`) alleen met een geheime
  sleutel in de header `X-API-Key`
- Controle van de invoer via een **Form Request** (`PresetRequest`): naam verplicht,
  prijs een getal, categorie moet bestaan
- Een **API Resource** zorgt dat de JSON er altijd hetzelfde uitziet
- Getest met feature tests en met Postman (collectie in `docs/postman/`)

> **Keuze:** zonder sleutel kan iedereen die de URL kent je producten wissen. Voor de
> API is een sleutel genoeg; de beheeromgeving krijgt een echte login.

---

## Fase 3 — Front-end

✅ **Af op 18 september** · tags `v0.4-frontend`, `v0.5-edits` en `v0.6-catalogus`

> Deze fase heb ik **naar voren gehaald**: eerst de website goed neerzetten, daarna de
> database eronder. Daarom zijn de tags `v0.4`–`v0.6` ouder dan `v0.2`.

- Het ontwerp omgezet naar Blade-templates, met eigen CSS en JavaScript
- **Homepage:** hero, uitgelicht aanbod, categorietegels en recente edits
- **Presets-overzicht:** alle presets, met filter op categorie en soort en een zoekveld
- **Productdetailpagina:** beeld, prijs, wat je krijgt, specificaties en gerelateerde presets
- Werkt goed op de telefoon, met het toetsenbord en voor mensen die minder beweging willen
  (`prefers-reduced-motion`)

---

## Fase 4 — Beheeromgeving (CRUD)

🔄 **Bezig** · streefdatum 4 december · [Milestone met alle taken](https://github.com/LarsM04/lxrs-webshop/milestone/1)

Het aanbod aanpassen **zonder één regel code te wijzigen**. Dit is het hart van het
leerdoel. CRUD staat voor **C**reate (toevoegen), **R**ead (bekijken), **U**pdate
(bewerken) en **D**elete (verwijderen).

| Stap | Wat | Streefdatum | Tag | Status |
|---|---|---|---|---|
| 1 | Inloggen voor de beheerder | 16 okt | `v0.7-login` | ✅ |
| 2 | Overzicht van alle presets (**R**) | 23 okt | | ✅ |
| 3 | Preset toevoegen en bewerken (**C U**) | 6 nov | | ⬜ [#1](https://github.com/LarsM04/lxrs-webshop/issues/1) |
| 4 | Preset verwijderen (**D**) | 13 nov | `v0.8-admin-presets` | ⬜ [#2](https://github.com/LarsM04/lxrs-webshop/issues/2) |
| 5 | Afbeelding uploaden | 20 nov | `v0.9-afbeeldingen` | ⬜ [#3](https://github.com/LarsM04/lxrs-webshop/issues/3) |
| 6 | Categorieën beheren (**CRUD**) | 27 nov | | ⬜ [#4](https://github.com/LarsM04/lxrs-webshop/issues/4) |
| 7 | Afronden en demo-test | 4 dec | `v1.0-crud` | ⬜ [#5](https://github.com/LarsM04/lxrs-webshop/issues/5) |

> **Keuze: zelf bouwen in plaats van Filament.** Filament is een pakket dat in een middag
> een compleet adminpaneel geeft, maar dan doet het pakket het werk. Zelf bouwen kost meer
> tijd, maar zo laat ik zien dat ik inloggen, formulieren, validatie en uploads zelf kan
> maken. Daar gaat het om bij *Verdieping* Software.

> **Keuze: één beheerder, geen registratie.** Alleen ik beheer de shop. Het account
> komt uit de `AdminSeeder`, met de gegevens uit `.env`.

> **De API blijft zoals hij is.** De API werkt met een sleutel, de beheeromgeving met een
> login. Twee ingangen, maar dezelfde controle van de invoer (`PresetRequest`).

### Stap 1 — Inloggen ✅

- Loginpagina op `/admin/login`, met een eigen `Admin\LoginController` (zonder starterkit)
- Alles onder `/admin` is afgeschermd: zonder login word je naar de loginpagina gestuurd
- Na 5 foute pogingen een minuut wachten, tegen het raden van wachtwoorden
- Na het inloggen een nieuw sessie-id, zodat niemand een oude sessie kan overnemen
- Startpagina `/admin` met tellers: presets, categorieën en uitgelicht

### Stap 2 — Overzicht van alle presets ✅

- `/admin/presets`: tabel met afbeelding, naam, categorie, soort, prijs en uitgelicht
- Zoeken en filteren met dezelfde code als de website (`PresetCatalog::filterQuery()`)
- 25 per pagina; je filters blijven staan als je naar de volgende pagina gaat
- De categorieën worden in één keer opgehaald (`with('category')`) in plaats van één
  databasevraag per rij. Dat heet het *N+1-probleem*.

### Stap 3 t/m 7

Staan als afvinklijstje in de [Issues #1 t/m #5](https://github.com/LarsM04/lxrs-webshop/milestone/1).

> Na deze fase is het volledige leerdoel behaald. Alles hierna is extra.

---

## Fase 5 — Extra's

⬜ **Optioneel** · [Milestone](https://github.com/LarsM04/lxrs-webshop/milestone/2) · pas beginnen als fase 4 af is

Voor een hogere beoordeling. In deze volgorde, want elke stap bouwt op de vorige:

1. **Winkelwagen**, bewaard in de sessie · [#6](https://github.com/LarsM04/lxrs-webshop/issues/6)
2. **Afrekenen**: bestelling opslaan, zonder echte betaling · [#7](https://github.com/LarsM04/lxrs-webshop/issues/7)
3. **Downloadlink** die alleen werkt als er een bestelling is · [#8](https://github.com/LarsM04/lxrs-webshop/issues/8)

Zoeken en filteren op categorie stond hier eerst ook, maar dat zit al in de front-end (fase 3).

**Snapshot:** de hele flow van winkelwagen tot download · tag `v1.1-shop`

---

## Fase 6 — Afronden en demo

⬜ **Eind januari 2027** · [Milestone](https://github.com/LarsM04/lxrs-webshop/milestone/3) · taak [#9](https://github.com/LarsM04/lxrs-webshop/issues/9)

Alles nalopen, de documentatie compleet maken, een databasediagram tekenen en de demo
oefenen: presets bekijken → preset toevoegen → meteen zichtbaar op de website.

**Snapshot:** eindversie · tag `v1.2-oplevering`

---

## Risico's

| Risico | Kans | Wat ik doe |
|---|---|---|
| Geen productafbeeldingen | Groot | Bij stap 5 beginnen met de 7 packs en het complete pack; tot die tijd placeholders |
| Fase 4 loopt uit | Middel | Elke stap is los af te ronden; de extra's pas als fase 4 af is |
| Extra's kosten alle tijd | Middel | Zelfde regel: eerst fase 4 helemaal af |
