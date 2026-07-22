<?php

ini_set('memory_limit', '-1');

error_reporting(0);

if ($argv)
 {
     foreach ($argv as $arg) 
    {
        $e=explode("=",$arg);
        if(count($e)==2)
            $_REQUEST[$e[0]] = $e[1];
        else    
            $_REQUEST[$e[0]] = 0;
    }
     
 }

 if (isset($_REQUEST['validarEnvio'])) {
 	$validarEnvio = urldecode($_REQUEST['validarEnvio']);
 } else {
 	$validarEnvio = '';
 }

 if ($validarEnvio <> 'EnvioAprobado') {

 	die("OK");

 }

$con =  mysqli_connect('rds-compartido.pconnection.net', 'admin', '8sC3rq2iQqEztsj1', 'CLUBS');

$club = 88;

$cmd = "pkill -f cobros_bc_club_recobro_totito_V2.php";

shell_exec($cmd);

sleep(1);

$query = "SELECT clubs_MSISDN.id_msisdn, clubs_MSISDN.msisdn,DATEDIFF(DATE(CONVERT_TZ(NOW(), 'UTC', 'America/Tegucigalpa')),case clubs_MSISDN.fecha_baja when 0 then clubs_MSISDN.fecha_alta else clubs_MSISDN.fecha_baja end) as dias_alta FROM clubs_MSISDN WHERE clubs_MSISDN.id_clubs = $club  AND clubs_MSISDN.state = 'enable' AND fecha_cobro <= DATE(CONVERT_TZ(NOW(), 'UTC', 'America/Tegucigalpa')) AND clubs_MSISDN.msisdn NOT IN (SELECT clubs_DLR.msisdn FROM clubs_DLR WHERE clubs_DLR.id_club = $club) ORDER BY clubs_MSISDN.id_msisdn DESC";

$result = mysqli_query($con, $query);

$datos = array();

$contador = 0;

while ($fila = mysqli_fetch_assoc($result)) {

	$datos[] = $fila['id_msisdn'];

	if (count($datos) === 1) {

		++$contador;
		echo "Proceso $contador\n";
		
		$limiteFinal = $datos[count($datos) - 1];

	}

	if (count($datos) === 50000) {
		
		$limiteInicio = $datos[count($datos) - 1];
		echo "LIMITE INICIO: $limiteInicio\n";
		echo "ENVIADOS: ".count($datos) . "\n";
		$cmd = "php -q /opt/lampp/htdocs/private/clubs/cobros_bc_club_recobro_totito_V2.php id_carrier=1 prioridad=2 inicio=$limiteInicio final=$limiteFinal >> /opt/lampp/htdocs/private/clubs/cobros_bc_club_recobro_totito_V2.log &";
		echo $cmd . "\n";
		shell_exec($cmd);
		sleep(2);
		unset($datos);

	}

}

$limiteInicio = $datos[count($datos) - 1];

$cmd = "php -q /opt/lampp/htdocs/private/clubs/cobros_bc_club_recobro_totito_V2.php id_carrier=1 prioridad=2 inicio=$limiteInicio final=$limiteFinal >> /opt/lampp/htdocs/private/clubs/cobros_bc_club_recobro_totito_V2.log &";
echo $cmd . "\n";
shell_exec($cmd);

echo "LIMITE INICIO: $limiteInicio\n";

echo count($datos);

mysqli_close($con);
