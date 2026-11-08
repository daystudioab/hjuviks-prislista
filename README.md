# Hjuviks Marina – Prislista

WordPress-tillägg för hjuviksmarina.se. Byggt av Studio Day.

## Ny version

1. Höj `Version:` i `hjuviks-prislista.php` (och versionskonstanten under).
2. Committa och pusha till `main`. Commit-meddelandet blir releasens anteckningar.
3. GitHub Actions ser att versionen är ny, bygger `hjuviks-prislista.zip` och publicerar releasen `v` + versionsnumret.
   Ändringar utan ny version ger ingen release.
4. Sajten visar uppdateringen under **Tillägg** (kollar var 12:e timme, eller direkt via länken *Sök efter uppdateringar* på tilläggets rad).

## Kortkoder

- `[hjuviks_prislista]` – hela listan som tabell. `grupp="Båtplatser"` visar en grupp, `rubrik="nej"` tar bort rubrikraden, `noter="nej"` tar bort noterna.
- `[hjuviks_prislista stil="flikar"]` – en flik per grupp och ett reglage för pris med eller utan moms. Följer sidans typsnitt. Utan JavaScript visas alla grupper efter varandra.
- `[hjuviks_aktuellt]` – rutan Just nu och Nästa, som byter innehåll efter dagens datum (säsong 1 april–31 oktober, vinterplatsavtal senast 1 oktober, vinterplats 1 november–31 mars).

Kortkoderna kan skrivas som vanlig text i en Elementor-widget.

## Publikt repo

Repot är publikt, så sajten hämtar releaser utan nyckel. Om repot görs privat behöver `wp-config.php` en läsnyckel
(fine-grained token med **Contents: Read-only**): `define( 'STUDIODAY_GITHUB_TOKEN', 'github_pat_…' );`

## Innehåll

- `lib/plugin-update-checker/` – [Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker) v5.6 (MIT), sköter uppdateringarna.
