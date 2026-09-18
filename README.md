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
| Backend | Laravel 12 (PHP 8.2) | Sluit aan bij de module OOP & Laravel; migrations en Eloquent schelen veel handwerk |
| API | Eigen endpoints in `routes/api.php` | Zelf geschreven, zoals in mijn kiosk-project (Happy Herbivore) |
| Database | MySQL (XAMPP) | Bekend vanuit eerdere schoolprojecten |
| Admin | Filament | Eigen beheeromgeving, vergelijkbaar met het CMS van mijn U Festival App |

---

## Databasestructuur

Twee gekoppelde tabellen (één-op-veel: een categorie heeft veel presets).

```
categories                      presets
----------                      -------
id            PK        1 ---┐  id             PK
name                         └- category_id    FK -> categories.id
slug                    ∞       name
created_at                      slug
updated_at                      description
                                price          DECIMAL(8,2)
                                image_path
                                download_path
                                is_featured    BOOLEAN
                                created_at
                                updated_at
```

**Categorieën:** Color Corrections (CC's) · Text Presets · Shakes · Zooms

---

## Installatie (lokaal draaien)

```bash
git clone https://github.com/LarsM04/lxrs-webshop.git
cd lxrs-webshop
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Zorg dat MySQL draait in XAMPP en dat er een database `lxrs_webshop` bestaat.

---

## Voortgang / Snapshots

Bewijs van vordering. Elke regel = een moment waarop er iets nieuws werkt, met screenshot
in [`docs/snapshots/`](docs/snapshots/) en een git tag om naar terug te springen.

| # | Datum | Fase | Wat werkt er nu | Snapshot | Tag |
|---|---|---|---|---|---|
| 00 | 2026-09-18 | Setup | Repo aangemaakt, ontwerp geanalyseerd, plan vastgelegd | — | `v0.0-setup` |

<!-- Nieuwe regel toevoegen bij elke milestone. Format snapshot: ![](docs/snapshots/2026-10-05-presets-overzicht.png) -->

### Hoe ik een snapshot maak

1. Screenshot van wat er nieuw werkt → opslaan als `docs/snapshots/JJJJ-MM-DD-korte-omschrijving.png`
2. Regel toevoegen aan de tabel hierboven
3. Committen en taggen:
   ```bash
   git add .
   git commit -m "Snapshot: presets-overzicht laadt live uit database"
   git tag v0.3-overzicht
   git push && git push --tags
   ```

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
- [ ] Snapshotlogboek in deze README als bewijs van vordering

---

## Mappenstructuur

```
lxrs-webshop/
├── design/          Origineel ontwerp (Claude artifact-export, referentie)
├── docs/
│   ├── PLAN.md      Gefaseerde planning
│   └── snapshots/   Screenshots per milestone
└── ...              Laravel-project (volgt in fase 0)
```
