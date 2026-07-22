<?php

function generarUUIDv4() {
    if (function_exists('random_bytes')) {
        $data = random_bytes(16);
    } elseif (function_exists('openssl_random_pseudo_bytes')) {
        $data = openssl_random_pseudo_bytes(16);
    } else {
        // Fallback menos seguro (solo si no hay otra opción)
        $data = '';
        for ($i = 0; $i < 16; $i++) {
            $data .= chr(mt_rand(0, 255));
        }
    }
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // Versión 4
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // Variante RFC 4122
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function log_envios ($id_club, $fecha_inicio, $uuid, $envios, $fecha_final='') {

	$query = "INSERT INTO CLUBS.`clubs_LOGS_ENVIOS` (`id`, `id_club`, `fecha_inicio`) VALUES ('".$uuid."', $id_club, '$fecha_inicio') ON DUPLICATE KEY UPDATE fecha_final = '$fecha_final', envios = $envios, flag = 1";
	mysql_query($query);
	
}

/*
$userdb = 'admin';
$passdb = '8sC3rq2iQqEztsj1';
$namedb = 'CLUBS';
$hostdb = 'rds-compartido.pconnection.net';

$con = mysqli_connect($hostdb, $userdb, $passdb, $namedb);

$uuid = generarUUIDv4();

log_envios($con, 1, '2025-02-15 12:00:00', $uuid, 321, '2025-02-15 14:00:00');
sleep(10);
log_envios($con, 1, '2025-02-15 12:00:00', $uuid, 321, '2025-02-15 14:00:00');
*/
