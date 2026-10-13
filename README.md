# Mittwald Code Challenge - Geräteausleihe

Eine kleine API (API Platform) für Geräteausleihe mit den Entities Device und EquipmentCabinet, wobei Devices als owning side über eine nullable Many-To-One-Beziehung einem Schrank zugewiesen werden.
Mehrere custom Validators und Constraints sichern die Datenintegrität und verhindern Bedienfehler, etwa das Überschreiten der maximalen Geräteanzahl eines Schranks.

## Projekt starten
Nach `composer install` und `docker compose up -d` das Schema mit `symfony console doctrine:migrations:migrate` anlegen, die Beispieldaten mit `symfony console doctrine:fixtures:load` laden und den Server mit `symfony serve -d` starten, die API ist dann unter `/api` ausprobierbar.
Für die Tests einmalig `symfony console doctrine:database:create --env=test` sowie die Migrationen mit `--env=test` ausführen und danach `symfony php bin/phpunit` starten.

## Entscheidungen die ich getroffen habe
- Ich habe mich für Symfony mit API Platform entschieden weil diese Kombination extrem schnelle Entwicklung mit guter Performance und Datenintegritätsgarantien erlaubt.
- Ich habe mich für den Bau einer API entschieden, weil für mich die Datenintegrität in der Benutzung am wichtigsten war; das Interface muss bedienbar sein, aber wenn die Daten nicht korrekt sind hat man größere Probleme als wenn etwas nicht schön aussieht.

## Was ich als Nächstes machen würde
- Ein User Interface bauen
- Eine Entity bauen für die Person an die ausgeliehen wurde, damit entsprechend getrackt werden kann welches Gerät an wen ausgeliehen ist
- Sicherstellen dass defekte Geräte nicht ausgeliehen werden können