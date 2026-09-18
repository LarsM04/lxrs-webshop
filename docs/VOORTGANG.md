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

<!-- Nieuwe regel bovenaan of onderaan toevoegen bij elke milestone.
     Snapshot-kolom: [bekijk](snapshots/2026-10-05-presets-overzicht.png) -->

---

## Hoe ik een snapshot vastleg

1. **Screenshot maken** van wat er nieuw werkt
   Opslaan als `docs/snapshots/JJJJ-MM-DD-korte-omschrijving.png`

2. **Regel toevoegen** aan het logboek hierboven

3. **Committen en taggen:**
   ```bash
   git add .
   git commit -m "Snapshot: presets-overzicht laadt live uit database"
   git tag -a v0.3-overzicht -m "Presets-overzicht werkt met database-data"
   git push && git push --tags
   ```

---

## Geplande milestones

De tags die er aan het eind van het traject moeten staan:

| Tag | Fase | Wanneer | Waar de snapshot van is |
|---|---|---|---|
| `v0.0-setup` | 0 | sep 2026 | Plan en repo — ✅ behaald |
| `v0.1-laravel` | 0 | sep 2026 | Laravel-welkomstpagina op localhost |
| `v0.2-database` | 1 | okt 2026 | Beide tabellen met data in phpMyAdmin |
| `v0.3-api` | 2 | okt 2026 | `GET /api/presets`-response in Postman |
| `v0.4-frontend` | 3 | nov 2026 | Presets-overzicht met database-data |
| `v1.0-crud` | 4 | dec 2026 | Adminpaneel naast de bijgewerkte website |
| `v1.1-shop` | 5 | jan 2027 | Winkelwagen tot en met download |
| `v1.0-oplevering` | 6 | jan 2027 | Eindversie |

---

## Waarom dit telt als bewijs

Drie sporen die samen laten zien dat er over een langere periode aan gewerkt is:

- **Dit logboek** — wat er wanneer werkend was, met beeld erbij
- **Git tags** — terugspringen naar hoe het project er in oktober uitzag
- **Commitgeschiedenis** — datums en berichten zijn op GitHub automatisch zichtbaar
