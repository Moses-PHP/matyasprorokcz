<?php
/**
 * Jednorázový setup skript – vytvoří tabulku events a vloží první koncert.
 * Po spuštění tento soubor smažte nebo zablokujte přístup.
 */
require __DIR__ . '/config.php';

$db = getDB();

// Vytvoření tabulky
$db->exec("
    CREATE TABLE IF NOT EXISTS events (
        id              INT AUTO_INCREMENT PRIMARY KEY,
        title           VARCHAR(255) NOT NULL,
        event_date      DATE NOT NULL,
        time_start      TIME DEFAULT NULL,
        time_end        TIME DEFAULT NULL,
        venue           VARCHAR(255) DEFAULT NULL,
        city            VARCHAR(100) DEFAULT NULL,
        description     TEXT DEFAULT NULL,
        admission       VARCHAR(100) DEFAULT NULL,
        badge           VARCHAR(50)  DEFAULT NULL,
        image_path      VARCHAR(255) DEFAULT NULL,
        additional_info TEXT DEFAULT NULL,
        is_upcoming     TINYINT(1) DEFAULT 1,
        created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

echo "Tabulka 'events' vytvořena.\n";

// Vložení existujícího koncertu (6. 3. 2026), pokud ještě neexistuje
$check = $db->query("SELECT COUNT(*) FROM events WHERE event_date = '2026-03-06' AND title LIKE '%Alice Vackové%'");
if ((int) $check->fetchColumn() === 0) {
    $stmt = $db->prepare("
        INSERT INTO events (title, event_date, time_start, time_end, venue, city, admission, badge, image_path, additional_info, is_upcoming)
        VALUES (:title, :event_date, :time_start, :time_end, :venue, :city, :admission, :badge, :image_path, :additional_info, :is_upcoming)
    ");
    $stmt->execute([
        'title'           => 'Koncert Alice Vackové a Adély Gottvaldové',
        'event_date'      => '2026-03-06',
        'time_start'      => '20:00:00',
        'time_end'        => '22:00:00',
        'venue'           => 'Bar Everything, Palác Hybských, Pardubice',
        'city'            => 'Pardubice',
        'admission'       => 'Vstupné dobrovolné · Open door od 19:00',
        'badge'           => 'Volný vstup',
        'image_path'      => '../images/koncert260306.jpg',
        'additional_info' => 'Hudební doprovod: Martin Kudrna',
        'is_upcoming'     => 1,
    ]);
    echo "Koncert 6. 3. 2026 vložen.\n";
} else {
    echo "Koncert 6. 3. 2026 již existuje.\n";
}

echo "Setup dokončen.\n";
