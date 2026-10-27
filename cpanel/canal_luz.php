<?php
/**
 * canal_luz.php — proxy mínimo para canalLUZ.
 *
 * Existe por un solo motivo: el CDN de canalLUZ exige la cabecera
 * "Referer: https://vmf.edge-apps.net/" y ni Roku ni TiviMate pueden
 * mandarla. Este script la agrega, y reescribe la lista HLS para que
 * cada variante y cada segmento pasen también por acá.
 *
 * Uso:  https://tudominio/canal_luz.php
 *
 * Seguridad: NO acepta URLs del cliente. El único upstream es $BASE,
 * y el parámetro "f" solo puede ser una ruta relativa dentro de $BASE.
 */

$BASE    = 'http://g2.vxral-slo.transport.edge-access.net/a11/ngrp:canal_luz01-100009_all/';
$INICIO  = 'Playlist.m3u8?sense=true';
$REFERER = 'https://vmf.edge-apps.net/';
$TOKEN   = '';   // si lo completás, la URL debe llevar ?t=valor

if ($TOKEN !== '') {
    $t = isset($_GET['t']) ? (string) $_GET['t'] : '';
    if (!hash_equals($TOKEN, $t)) {
        http_response_code(403);
        exit('forbidden');
    }
}

$f = isset($_GET['f']) ? (string) $_GET['f'] : $INICIO;

// Solo rutas relativas simples: nada de "..", esquemas ni barras invertidas.
if ($f === '' || preg_match('#(\.\.|://|\\\\|\x00)#', $f)) {
    http_response_code(400);
    exit('bad request');
}

$datos = bajar($BASE . ltrim($f, '/'), $REFERER);
if ($datos === false || $datos === '') {
    http_response_code(502);
    exit('upstream error');
}

if (!esPlaylist($datos)) {
    header('Content-Type: video/mp2t');
    header('Content-Length: ' . strlen($datos));
    header('Cache-Control: no-store');
    echo $datos;
    exit;
}

header('Content-Type: application/vnd.apple.mpegurl');
header('Cache-Control: no-store');

$dir   = dirname($f);
if ($dir === '.' || $dir === '/') {
    $dir = '';
}
$self  = basename(__FILE__);
$extra = ($TOKEN !== '') ? '&t=' . rawurlencode($TOKEN) : '';

$salida = array();
foreach (preg_split("/\r\n|\n|\r/", $datos) as $linea) {
    if ($linea === '') {
        continue;
    }
    if ($linea[0] === '#') {
        $salida[] = $linea;
        continue;
    }
    $resuelto = resolver($linea, $dir);
    if ($resuelto === null) {
        continue;
    }
    $salida[] = $self . '?f=' . rawurlencode($resuelto) . $extra;
}

echo implode("\n", $salida) . "\n";

function esPlaylist($texto)
{
    return strncmp(ltrim($texto), '#EXTM3U', 7) === 0;
}

function resolver($uri, $dir)
{
    if (preg_match('#^https?://#i', $uri)) {
        $p = parse_url($uri);
        if (!isset($p['host']) || stripos($p['host'], 'edge-access.net') === false) {
            return null;   // host ajeno: se descarta
        }
        $ruta  = ltrim($p['path'], '/');
        $marca = 'ngrp:canal_luz01-100009_all/';
        $pos   = strpos($ruta, $marca);
        if ($pos !== false) {
            $ruta = substr($ruta, $pos + strlen($marca));
        }
        if (isset($p['query']) && $p['query'] !== '') {
            $ruta .= '?' . $p['query'];
        }
        return normalizar($ruta);
    }
    $ruta = ($dir !== '' ? $dir . '/' : '') . $uri;
    return normalizar($ruta);
}

function normalizar($ruta)
{
    $partes = array();
    foreach (explode('/', $ruta) as $p) {
        if ($p === '' || $p === '.') {
            continue;
        }
        if ($p === '..') {
            array_pop($partes);
            continue;
        }
        $partes[] = $p;
    }
    return implode('/', $partes);
}

function bajar($url, $referer)
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_REFERER        => $referer,
            CURLOPT_USERAGENT      => 'Mozilla/5.0',
            CURLOPT_HTTPHEADER     => array('Origin: https://vmf.edge-apps.net/'),
        ));
        $r = curl_exec($ch);
        curl_close($ch);
        return $r;
    }

    $ctx = stream_context_create(array('http' => array(
        'header'  => "Referer: $referer\r\nOrigin: https://vmf.edge-apps.net/\r\nUser-Agent: Mozilla/5.0\r\n",
        'timeout' => 10,
    )));
    return @file_get_contents($url, false, $ctx);
}
