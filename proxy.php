<?php
/*
 * OKULAR – Mini-Proxy für Flugdaten
 * Die ADS-B-Dienste erlauben keine direkten Browser-Abfragen (CORS),
 * deshalb holt dieses Skript die Daten serverseitig und reicht sie weiter.
 * Es leitet NUR an die unten erlaubten Adressen weiter und puffert 10 Sekunden.
 */
$allowed = [
  'api.adsb.lol'       => '#^/v2/(mil|point/-?[0-9.]+/-?[0-9.]+/[0-9]+)$#',
  'api.airplanes.live' => '#^/v2/(mil|point/-?[0-9.]+/-?[0-9.]+/[0-9]+)$#',
];
$ttl = 10; // Sekunden

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('X-Content-Type-Options: nosniff');

$u = isset($_GET['u']) ? (string)$_GET['u'] : '';
$p = parse_url($u);
$host = $p['host'] ?? '';
$path = $p['path'] ?? '';
if (($p['scheme'] ?? '') !== 'https' || !isset($allowed[$host]) || !preg_match($allowed[$host], $path) || isset($p['query'])) {
  http_response_code(400);
  echo '{"error":"Adresse nicht erlaubt"}';
  exit;
}

$dir = sys_get_temp_dir() . '/okular-cache';
if (!is_dir($dir)) @mkdir($dir, 0700, true);
$file = $dir . '/' . sha1($u) . '.json';
if (is_file($file) && time() - filemtime($file) < $ttl) {
  header('X-Cache: HIT');
  readfile($file);
  exit;
}

$ch = curl_init($u);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_FOLLOWLOCATION => false,
  CURLOPT_CONNECTTIMEOUT => 6,
  CURLOPT_TIMEOUT        => 15,
  CURLOPT_ENCODING       => '',
  CURLOPT_USERAGENT      => 'OKULAR-Lagekarte/1.0 (selbst gehostet)',
]);
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($body === false || $code !== 200 || ($body[0] ?? '') !== '{') {
  if (is_file($file)) { header('X-Cache: STALE'); readfile($file); exit; }
  http_response_code(502);
  echo '{"error":"Quelle antwortet nicht (' . (int)$code . ')"}';
  exit;
}
@file_put_contents($file, $body, LOCK_EX);
header('X-Cache: MISS');
echo $body;
