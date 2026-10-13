# Mittwald Code Challenge - Geräteausleihe

Eine kleine API (API Platform) für Geräteausleihe mit den Entities Device, EquipmentCabinet und Employee, wobei Devices als owning side über nullable Many-To-One-Beziehungen entweder einem Schrank zugewiesen oder an Mitarbeitende ausgeliehen werden.
Geräte lassen sich nach Typ, Hersteller, Seriennummer und Zustand suchen, z. B. `/api/devices?type=laptop&defective=false`.
Mehrere custom Validators und Constraints sichern die Datenintegrität und verhindern Bedienfehler, etwa das Überschreiten der maximalen Geräteanzahl eines Schranks.

## Projekt starten
Nach `composer install` und `docker compose up -d` das Schema mit `symfony console doctrine:migrations:migrate` anlegen, die Beispieldaten mit `symfony console doctrine:fixtures:load` laden und den Server mit `symfony serve -d` starten, die API ist dann unter `/api` ausprobierbar.
Für die Tests einmalig `symfony console doctrine:database:create --env=test` sowie die Migrationen mit `--env=test` ausführen und danach `symfony php bin/phpunit` starten.

## Entscheidungen die ich getroffen habe
- Ich habe mich für Symfony mit API Platform entschieden weil diese Kombination extrem schnelle Entwicklung mit guter Performance und Datenintegritätsgarantien erlaubt.
- Ein Gerät ist entweder im Schrank oder ausgeliehen, nie beides: so gibt eine Ausleihe den Platz im Schrank frei, und bei der Rückgabe greift dieselbe Kapazitätsprüfung wie beim Einsortieren. Defekte Geräte können nicht ausgeliehen werden, einen Defekt meldet man bei der Rückgabe.
- Ich habe mich für den Bau einer API entschieden, weil diese die Datengrundlage für die Entwicklung eines Interfaces bietet.

## Was ich als Nächstes machen würde
- Ein User Interface bauen