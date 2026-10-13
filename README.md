# Mittwald Code Challenge - Geräteausleihe

Eine kleine API (API Platform) für Geräteausleihe mit den Entities Device und EquipmentCabinet, wobei Devices als owning side über eine nullable Many-To-One-Beziehung einem Schrank zugewiesen werden.
Mehrere custom Validators und Constraints sichern die Datenintegrität und verhindern Bedienfehler, etwa das Überschreiten der maximalen Geräteanzahl eines Schranks.

## Projekt starten
Nach `composer install` und `docker compose up -d` das Schema mit `symfony console doctrine:migrations:migrate` anlegen und den Server mit `symfony serve -d` starten, die API liegt dann unter `/api`.
Für die Tests einmalig `symfony console doctrine:database:create --env=test` sowie die Migrationen mit `--env=test` ausführen und danach `symfony php bin/phpunit` starten.
