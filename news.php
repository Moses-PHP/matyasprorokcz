<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=300');

$cache_file = sys_get_temp_dir() . '/idnes_news.json';
$cache_ttl  = 300; // 5 minut

// Vrať cache pokud je aktuální
if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_ttl) {
    echo file_get_contents($cache_file);
    exit;
}

$rss_url = 'https://servis.idnes.cz/rss.aspx?c=zpravy';
$xml = @simplexml_load_file($rss_url);

if (!$xml) {
    http_response_code(503);
    echo json_encode(['error' => 'Nelze načíst zprávy z iDnes.cz']);
    exit;
}

$items = [];
foreach ($xml->channel->item as $item) {
    $items[] = [
        'title' => (string) $item->title,
        'link'  => (string) $item->link,
    ];
    if (count($items) >= 20) break;
}

$result = json_encode(['items' => $items], JSON_UNESCAPED_UNICODE);
file_put_contents($cache_file, $result);
echo $result;
