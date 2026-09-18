# LXRS2004 Webshop

Webshop voor het verkopen van mijn eigen video-editing presets, extensies en clip packs.
Gebouwd voor het **keuzedeel Verdieping Software**.

**Naam:** Lars Mudde · **GitHub:** [LarsM04](https://github.com/LarsM04)
**Deadline:** eind januari 2027

---

## Leerdoel (SMART)

> Uiterlijk eind januari 2027 kan ik een webshop bouwen voor het verkopen van mijn eigen
> editing-presets, met een front-end op basis van mijn bestaande ontwerp, een eigen
> backend-API en een database, waarbij bezoekers presets kunnen bekijken en ik als
> beheerder presets kan toevoegen, bewerken en verwijderen (CRUD).

Aangetoond door: een productoverzicht dat live uit de database geladen wordt, plus een
beheeromgeving waarmee ik het aanbod aanpas **zonder de code te wijzigen**.

---

## Tech stack

| Laag | Keuze | Waarom |
|---|---|---|
| Front-end | Blade + HTML/CSS/JS (geen framework) | Mijn bestaande ontwerp uitwerken, zoals in mijn portfolio-website |
| Typografie | Manrope (variabel, lokaal) | Één bestand van 53 KB voor alle gewichten; geen verzoek naar Google Fonts |
| Backend | Laravel 13 (PHP 8.5) | Sluit aan bij de module OOP & Laravel; migrations en Eloquent schelen veel handwerk |
| API | Eigen endpoints in `routes/api.php` | Zelf geschreven, zoals in mijn kiosk-project (Happy Herbivore) |
| Database | MySQL 8.4 | Bekend vanuit eerdere schoolprojecten |
| Omgeving | Docker (Laravel Sail) | Iedereen draait exact dezelfde versies; één commando om op te starten |
| Admin | Filament | Eigen beheeromgeving, vergelijkbaar met het CMS van mijn U Festival App |

---

## Databasestructuur

Twee gekoppelde tabellen (één-op-veel: een categorie heeft veel presets).

```
categories                      presets
----------                      -------
id            PK        1 ---┐  id                PK
name                         └- category_id       FK -> categories.id
slug                    ∞       name
omschrijving                    slug
created_at                      tagline           korte regel op de kaart
updated_at                      description
                                price             DECIMAL(8,2)
                                image_path
                                download_path
                                is_featured       BOOLEAN
                                includes          JSON  'wat je krijgt'
                                ae_version        welke AE-versie
                                bestandsgrootte
                                created_at
                                updated_at
```

De laatste vijf velden zijn erbij gekomen tijdens het bouwen van de front-end: een
webshop verkoopt niet zonder te vertellen wat je krijgt en waar het mee werkt.

`includes` is een lijstje, dus dat wordt een JSON-kolom. Kan ook een derde tabel
worden (`preset_items`), maar het keuzedeel vraagt om twee gekoppelde tabellen en
JSON houdt het simpel.

**Categorieën:** Color Corrections (CC's) · Text Presets · Shakes · Zooms

---

## Installatie (lokaal draaien)

Je hebt alleen **Docker Desktop** nodig. PHP, MySQL en phpMyAdmin draaien in containers,
dus er hoeft niets op je eigen computer geïnstalleerd te worden.

```bash
git clone https://github.com/LarsM04/lxrs-webshop.git
cd lxrs-webshop
cp .env.example .env
docker compose up -d
docker compose exec laravel.test php artisan key:generate
docker compose exec laravel.test php artisan migrate --seed
```

Daarna draait alles:

| Wat | Waar |
|---|---|
| De website | http://localhost:8000 |
| phpMyAdmin | http://localhost:8080 |
| MySQL (extern) | `localhost:3307`, gebruiker `sail`, wachtwoord `password` |

Stoppen doe je met `docker compose down`.

### Veelvoorkomende problemen

**`Permission denied` op `storage/logs/laravel.log`** — op Windows staan bind-mounted
bestanden op `root`, terwijl de webserver als `sail` draait:

```bash
docker compose exec -u root laravel.test chmod -R 777 storage bootstrap/cache
```

**`port is already allocated`** — er draait al iets op die poort. De poorten staan in
`.env` (`APP_PORT`, `FORWARD_DB_PORT`, `FORWARD_PHPMYADMIN_PORT`), pas ze daar aan.

---

## Recente edits op de homepage

De 'Recente edits'-sectie toont TikTok-video's. De links staan in
[`config/edits.php`](config/edits.php) — plakken en opslaan is genoeg,
er hoeft niets aan de code te veranderen.

```php
'reels' => [
    'https://www.tiktok.com/@lxrs2004/video/7301234567890123456',
],
```

Zo'n link haal je via **Delen → Link kopiëren**. Een korte link
(`https://vm.tiktok.com/...`) werkt niet, want daar staat het videonummer niet in:
open die eerst in je browser en kopieer dan de link uit de adresbalk.

Haal daarna de thumbnails op:

```bash
docker compose exec laravel.test php artisan edits:thumbnails
```

Dat zet de voorbeeldafbeelding en de titel lokaal neer in `public/images/edits/`.
De homepage laadt dus niks van TikTok tot een bezoeker op play drukt — dat scheelt
ruim 150 verzoeken per paginabezoek, en je bezoekers krijgen geen vier
cookiebanners van TikTok over je edits heen.

Staat de lijst leeg, dan toont de sectie je drie TikTok-accounts.

---

## Voortgang

Het snapshotlogboek — wat er wanneer werkend was, met screenshots en git tags —
staat in [`docs/VOORTGANG.md`](docs/VOORTGANG.md).

**Laatste milestone:** `v0.0-setup` — plan van aanpak vastgelegd (18 september 2026)

---

## Planning

De volledige fasering staat in [`docs/PLAN.md`](docs/PLAN.md).

| Fase | Periode | Resultaat |
|---|---|---|
| 0. Setup | sep 2026 | Repo, Laravel, databaseontwerp |
| 1. Database | okt 2026 | Migrations, modellen, seeders |
| 2. API | okt 2026 | Eigen CRUD-endpoints |
| 3. Front-end | okt–nov 2026 | Ontwerp omgezet, live data |
| 4. Admin CRUD | nov–dec 2026 | Beheeromgeving werkend |
| 5. Stretch goals | dec–jan 2027 | Winkelwagen, downloads, filters |
| 6. Afronden | jan 2027 | Testen, demo, oplevering |

---

## Bewijs voor de beoordeling

- [ ] Live demo: presets bekijken via de front-end
- [ ] Live demo: preset toevoegen, bewerken en verwijderen in de beheeromgeving
- [ ] Laten zien dat een wijziging direct zichtbaar is op de website
- [ ] Toelichting op de databasestructuur en de koppeling tussen de tabellen
- [ ] Snapshotlogboek in `docs/VOORTGANG.md` als bewijs van vordering

---

## Mappenstructuur

```
lxrs-webshop/
├── design/          Origineel ontwerp (Claude artifact-export, referentie)
├── tools/           Snapshot-script (Playwright)
├── docs/
│   ├── PLAN.md      Gefaseerde planning
│   ├── VOORTGANG.md Snapshotlogboek en milestones
│   └── snapshots/   Screenshots per milestone
└── ...              Laravel-project (volgt in fase 0)
```
