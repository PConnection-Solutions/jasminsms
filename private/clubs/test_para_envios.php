<?php

ini_set('memory_limit', '-1');

$con =  mysqli_connect('rds-compartido.pconnection.net', 'admin', '8sC3rq2iQqEztsj1', 'CLUBS');

$query = "SELECT clubs_MSISDN.id_msisdn, clubs_MSISDN.msisdn,DATEDIFF(DATE(CONVERT_TZ(curdate(), 'UTC', 'America/Tegucigalpa')),case clubs_MSISDN.fecha_baja when 0 then clubs_MSISDN.fecha_alta else clubs_MSISDN.fecha_baja end) as dias_alta FROM clubs_MSISDN WHERE clubs_MSISDN.id_clubs = 79  AND clubs_MSISDN.state = 'enable' AND fecha_cobro <= DATE(CONVERT_TZ(NOW(), 'UTC', 'America/Tegucigalpa')) AND clubs_MSISDN.msisdn NOT IN (SELECT clubs_ENVIOS_DIARIOS.msisdn FROM clubs_ENVIOS_DIARIOS WHERE clubs_ENVIOS_DIARIOS.id_clubs = 79) ORDER BY clubs_MSISDN.id_msisdn DESC";

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

	if (count($datos) === 100000) {
		
		$limiteInicio = $datos[count($datos) - 1];
		echo "LIMITE INICIO: $limiteInicio\n";
		echo "ENVIADOS: ".count($datos) . "\n";
		$cmd = "php -q /opt/lampp/htdocs/private/clubs/cobros_bc_club_recobro_qbn_V2.php id_carrier=1 prioridad=2 inicio=$limiteInicio final=$limiteFinal >> /opt/lampp/htdocs/private/clubs/cobros_bc_club_recobro_qbn.log";
		echo $cmd . "\n";
		shell_exec($cmd);
		unset($datos);

	}

}

$limiteInicio = $datos[count($datos) - 1];

$cmd = "php -q /opt/lampp/htdocs/private/clubs/cobros_bc_club_recobro_qbn_V2.php id_carrier=1 prioridad=2 inicio=$limiteInicio final=$limiteFinal >> /opt/lampp/htdocs/private/clubs/cobros_bc_club_recobro_qbn.log";
echo $cmd . "\n";
shell_exec($cmd);

echo "LIMITE INICIO: $limiteInicio\n";

echo count($datos);

mysqli_close($con);