# LXRS2004 Webshop

Webshop voor het verkopen van mijn eigen video-editing presets, extensies en clip packs.
Gebouwd voor het **keuzedeel Verdieping Software**.

**Naam:** Lars Mudde · **GitHub:** [LarsM04](https://github.com/LarsM04) · **Deadline:** eind januari 2027

---

## Waar sta ik nu?

**Fase 4: de beheeromgeving.** Presets toevoegen, bekijken, bewerken en verwijderen werkt.
**Volgende taak:** [#3 Stap 5: afbeelding uploaden](https://github.com/LarsM04/lxrs-webshop/issues/3)

| | |
|---|---|
| ✅ Af | Setup · database · eigen API · front-end · inloggen · CRUD voor presets in de beheeromgeving |
| 🔄 Nu | Afbeeldingen uploaden |
| ⬜ Daarna | Categorieën beheren · afronden van fase 4 · extra's · afronden |

## Waar vind ik wat?

| Wat zoek je? | Waar |
|---|---|
| Wat moet ik nog doen? | [Issues](https://github.com/LarsM04/lxrs-webshop/issues): één taak per stap, met een afvinklijstje |
| Hoe ver is elke fase? | [Milestones](https://github.com/LarsM04/lxrs-webshop/milestones): één per fase, met een voortgangsbalk |
| Het hele plan en waarom ik keuzes maakte | [docs/PLAN.md](docs/PLAN.md) |
| Bewijs: wat werkte wanneer, met screenshots | [docs/VOORTGANG.md](docs/VOORTGANG.md) |
| Elke versie met uitleg en een screenshot | [Releases](https://github.com/LarsM04/lxrs-webshop/releases) |
| De API testen in Postman | [docs/postman/](docs/postman/) |
| Het originele ontwerp | [design/](design/) |

---

## Leerdoel

> Uiterlijk eind januari 2027 kan ik een webshop bouwen voor het verkopen van mijn eigen
> editing-presets, met een front-end op basis van mijn bestaande ontwerp, een eigen
> backend-API en een database, waarbij bezoekers presets kunnen bekijken en ik als
> beheerder presets kan toevoegen, bewerken en verwijderen (CRUD).

Aangetoond door: een productoverzicht dat live uit de database geladen wordt, plus een
beheeromgeving waarmee ik het aanbod aanpas **zonder de code te wijzigen**.

### Bewijs voor de beoordeling

- [x] Presets bekijken op de website, live uit de database
- [x] Preset toevoegen, bewerken en verwijderen in de beheeromgeving
- [ ] Laten zien dat een wijziging direct zichtbaar is op de website
- [ ] Toelichting op de databasestructuur en de koppeling tussen de tabellen
- [x] Snapshotlogboek in [docs/VOORTGANG.md](docs/VOORTGANG.md)

---

## De webshop starten

### Elke keer dat je gaat werken

1. **Dubbelklik op `start-webshop.bat`** in de projectmap. Dat zet Docker Desktop aan,
   start de webshop en opent hem in je browser. Dit duurt ongeveer een halve minuut.
2. Klaar met werken? Typ in de terminal van VS Code `docker compose down`, of sluit
   gewoon Docker Desktop af.

Liever zelf in de terminal? Open eerst Docker Desktop en wacht tot het walvisje stil
staat. Typ dan in de terminal van VS Code:

```powershell
docker compose up -d
```

| Wat | Adres |
|---|---|
| De website | http://localhost:8000 |
| De beheeromgeving | http://localhost:8000/admin |
| phpMyAdmin (database bekijken) | http://localhost:8080 |

**Inloggen op de beheeromgeving:** je e-mailadres en wachtwoord staan in het bestand
`.env`, bij `ADMIN_EMAIL` en `ADMIN_PASSWORD`. Wil je een ander wachtwoord, verander het
daar en voer dit uit:

```powershell
docker compose exec laravel.test php artisan db:seed --class=AdminSeeder
```

**Tests draaien:**

```powershell
docker compose exec laravel.test php artisan test
```

### Eerste keer installeren

Dit hoef je maar één keer te doen, op een nieuwe computer of na een nieuwe clone.

Je hebt alleen **Docker Desktop** nodig. PHP en MySQL draaien in Docker, dus je hoeft
verder niets te installeren. Voer dit uit in PowerShell:

```powershell
git clone https://github.com/LarsM04/lxrs-webshop.git
cd lxrs-webshop
copy .env.example .env
```

Open nu `.env` en vul in:

- `ADMIN_NAME`, `ADMIN_EMAIL` en `ADMIN_PASSWORD`: hiermee log je in op `/admin`
- `LXRS_API_KEY`: een zelfbedachte geheime sleutel voor de API

Daarna:

```powershell
# Laravel en de andere PHP-pakketten downloaden (de map vendor/)
docker run --rm -v "${PWD}:/var/www/html" -w /var/www/html laravelsail/php84-composer:latest composer install --ignore-platform-reqs

docker compose up -d                                                  # webshop aanzetten
docker compose exec laravel.test php artisan key:generate             # geheime app-sleutel maken
docker compose exec laravel.test php artisan migrate --seed           # tabellen maken en vullen
```

<details>
<summary>Problemen?</summary>

**`Permission denied` op `storage/logs/laravel.log`.** Op Windows hoort die map bij een
andere gebruiker dan de webserver. Oplossing:

```powershell
docker compose exec -u root laravel.test chmod -R 777 storage bootstrap/cache
```

**`port is already allocated`.** Er draait al iets op die poort. De poorten staan in
`.env` (`APP_PORT`, `FORWARD_DB_PORT`, `FORWARD_PHPMYADMIN_PORT`); pas ze daar aan.

**`failed to connect to the docker API`.** Docker Desktop staat nog niet aan. Start het
en probeer het opnieuw zodra het walvisje stil staat.

</details>

---

## Hoe het werkt

### Tech stack

| Onderdeel | Keuze | Waarom |
|---|---|---|
| Front-end | Blade + HTML/CSS/JS, geen framework | Mijn bestaande ontwerp uitwerken, zoals in mijn portfolio-website |
| Backend | Laravel 13 (PHP 8.5) | Sluit aan bij de module OOP & Laravel |
| Database | MySQL 8.4 | Bekend uit eerdere schoolprojecten |
| API | Eigen endpoints in `routes/api.php` | Zelf geschreven, zoals in mijn kiosk-project |
| Beheeromgeving | Zelf gebouwd | Inloggen, formulieren en uploads zelf maken in plaats van een pakket als Filament |
| Omgeving | Docker (Laravel Sail) | Iedereen draait dezelfde versies, één commando om op te starten |

### Database

Twee gekoppelde tabellen: **een categorie heeft veel presets**. De kolom
`presets.category_id` verwijst naar `categories.id`.

```
categories                      presets
----------                      -------
id            PK        1 ---┐  id                PK
name                         └- category_id       FK -> categories.id
slug                    ∞       name
afkorting                       slug
omschrijving                    tagline           korte regel op de kaart
created_at                      description
updated_at                      price             DECIMAL(8,2)
                                image_path
                                download_path
                                is_featured       uitgelicht op de homepage
                                soort             los | pack | bundel
                                aantal            hoeveel presets erin zitten
                                includes          JSON: 'wat je krijgt'
                                ae_version        welke After Effects-versie
                                bestandsgrootte
                                created_at
                                updated_at
```

**Categorieën:** Color Corrections · Text Presets · Zooms · Shakes · Effects · Twixtor · Audio · Bundels

| Soort | Wat het is | Voorbeeld |
|---|---|---|
| `los` | één preset | Midnight CC, €4 |
| `pack` | alle presets uit één categorie | Shakes Pack (8 presets), €10 |
| `bundel` | het complete pack | LXRS Complete Pack (55 presets), €39 |

De catalogus komt uit de mappen van het echte edit pack:
`node tools/catalogus-genereren.mjs "<pad naar het pack>"` schrijft `database/data/presets.php`.
De `.ffx`-bestanden zelf staan **niet** in deze repo, want die zijn het product.

### API

Dezelfde data als de website, maar als JSON. Alles staat onder `/api`.

| Methode | Endpoint | Wat | Sleutel nodig |
|---|---|---|---|
| `GET` | `/api/presets` | alle presets; filter met `?categorie=`, `?soort=`, `?zoek=` | nee |
| `GET` | `/api/presets/{id}` | één preset | nee |
| `POST` | `/api/presets` | preset toevoegen → `201` | ja |
| `PUT` / `PATCH` | `/api/presets/{id}` | bewerken (PUT: alles, PATCH: alleen wat verandert) | ja |
| `DELETE` | `/api/presets/{id}` | verwijderen → `204` | ja |
| `GET` | `/api/categories` | alle categorieën, met het aantal presets | nee |

**Sleutel:** zet de header `X-API-Key` op de waarde van `LXRS_API_KEY` uit `.env`. Zonder
of met een foute sleutel krijg je `401`. Foute invoer geeft `422`, met per veld wat er mis is.

**Postman:** importeer [`docs/postman/lxrs-api.postman_collection.json`](docs/postman/lxrs-api.postman_collection.json)
en vul bij *Variables* je `api_key` in.

### Recente edits op de homepage

De TikTok-video's op de homepage staan in [`config/edits.php`](config/edits.php). Plak
een link (via **Delen → Link kopiëren**, geen `vm.tiktok.com`-link) en haal daarna de
thumbnails op:

```powershell
docker compose exec laravel.test php artisan edits:thumbnails
```

De homepage laadt pas iets van TikTok als een bezoeker op play drukt. Dat scheelt ruim
150 verzoeken per bezoek, en geen cookiebanners over je edits heen.

---

## Mappenstructuur

```
lxrs-webshop/
├── app/                 PHP-code: controllers, modellen en de rest van de logica
├── config/              Instellingen, zoals edits.php voor de TikTok-video's
├── database/            Migrations (tabellen), seeders (vullen) en de catalogus
├── public/              CSS, JavaScript, afbeeldingen en lettertypes voor de website
├── resources/views/     De pagina's (Blade-templates); admin/ is de beheeromgeving
├── routes/              Welke URL naar welke code gaat: web.php en api.php
├── tests/               Automatische tests
├── docs/                PLAN.md, VOORTGANG.md, screenshots en de Postman-collectie
├── design/              Het originele ontwerp, als voorbeeld
├── tools/               Scripts voor snapshots en het genereren van de catalogus
└── start-webshop.bat    Dubbelklik om de webshop te starten
```

---

## Zo werk ik

1. **Kies een taak** uit de [Issues](https://github.com/LarsM04/lxrs-webshop/issues).
   Pak de laagste open stap van de huidige fase.
2. **Bouw en commit vaak**, met een bericht dat zegt wat er nu werkt. Zet het nummer van
   de taak erin, bijvoorbeeld `Stap 3: formulier voor nieuwe preset (#1)`.
3. **Klaar?** Schrijf `Closes #1` in de laatste commit. Zodra die op GitHub staat, gaat de
   taak vanzelf dicht.
4. **Hoort er een tag bij?** Maak dan een snapshot, zie [VOORTGANG.md](docs/VOORTGANG.md#zo-maak-je-een-snapshot).
5. **Werk op `main`.** Maakt Claude Code een eigen branch (`claude/...`)? Voeg die samen
   met `main` zodra de stap af is, anders raakt het werk zoek.

### Begrippen

| Woord | Betekenis |
|---|---|
| **Commit** | Een opgeslagen moment in je code, met een bericht wat je hebt veranderd |
| **Branch** | Een aparte lijn van commits. `main` is de hoofdlijn, waar alles uiteindelijk hoort |
| **Tag** | Een vast label op één commit, bijvoorbeeld `v0.7-login`, zodat je een versie terugvindt |
| **Release** | Een tag met uitleg en screenshot op GitHub |
| **Issue** | Een taak op GitHub, met een afvinklijstje |
| **Milestone** | Een groep issues die samen één fase vormen, met een voortgangsbalk |
| **Migration** | Een bestand dat een databasetabel aanmaakt of aanpast |
| **Seeder** | Een bestand dat de database met beginwaarden vult |
| **Model** | Een PHP-class die bij een tabel hoort, bijvoorbeeld `Preset` bij `presets` |
| **API** | Een ingang waarmee programma's data ophalen of aanpassen, in JSON |
| **CRUD** | Create, Read, Update, Delete: toevoegen, bekijken, bewerken, verwijderen |
| **`.env`** | Bestand met geheime instellingen (wachtwoorden, sleutels). Staat **niet** op GitHub |
| **Docker / container** | Een afgesloten mini-computer met precies de juiste PHP- en MySQL-versie |
