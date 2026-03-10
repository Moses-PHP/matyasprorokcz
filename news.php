<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=300');

$cache_file = sys_get_temp_dir() . '/idnes_news_filtered.json';
$cache_ttl  = 300; // 5 minut

// Vrať cache pokud je aktuální
if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_ttl) {
    echo file_get_contents($cache_file);
    exit;
}

// Klíčová slova: politika a válečný/obranný sektor
$keywords = [
    // Politika
    'vláda', 'ministr', 'parlament', 'poslanec', 'senát', 'volby',
    'strana', 'koalice', 'opozice', 'prezident', 'premiér', 'sněmovna',
    'politika', 'politik', 'zákon', 'hlasování', 'fiala', 'pavel',
    'referendum', 'kandidát', 'volební',
    // Válka a obrana
    'válka', 'armáda', 'obrana', 'vojenský', 'nato', 'zbraně', 'útok',
    'konflikt', 'rusko', 'ukrajina', 'gaza', 'izrael', 'čína', 'vojáci',
    'drony', 'rakety', 'bezpečnost', 'bombardování', 'ofenzíva', 'obranný',
    'pentagon', 'generál', 'mise', 'putin', 'zelenskyj', 'trump',
    'sankce', 'diplomacie', 'zbrojní', 'munice', 'tanky', 'letectvo',
];

// RSS zdroje: hlavní zprávy + zahraniční
$rss_urls = [
    'https://servis.idnes.cz/rss.aspx?c=zpravy',
    'https://servis.idnes.cz/rss.aspx?c=zpravy-zahranicni',
];

$items = [];
$seen  = [];

foreach ($rss_urls as $url) {
    $xml = @simplexml_load_file($url);
    if (!$xml) continue;

    foreach ($xml->channel->item as $item) {
        $title = (string) $item->title;
        $link  = (string) $item->link;
        $desc  = (string) $item->description;

        if (isset($seen[$link])) continue;

        $text = mb_strtolower($title . ' ' . $desc, 'UTF-8');

        foreach ($keywords as $kw) {
            if (mb_strpos($text, $kw, 0, 'UTF-8') !== false) {
                $items[] = ['title' => $title, 'link' => $link];
                $seen[$link] = true;
                break;
            }
        }

        if (count($items) >= 25) break 2;
    }
}

$result = json_encode(['items' => $items], JSON_UNESCAPED_UNICODE);
file_put_contents($cache_file, $result);
echo $result;
