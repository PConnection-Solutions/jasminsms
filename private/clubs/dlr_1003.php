<?php

class Conversion {

	### VERFICAR QUE ESTE SUSCRITO Y QUE EL DLR ES EL PRIMERO ###
	public function suscripcion_dlr($con, $msisdn, $id_club) {

		### VERIFICO QUE ESTE SUSCRITO ###
		$querySus = "SELECT * FROM clubs_MSISDN WHERE id_clubs = $id_club AND msisdn = '$msisdn' AND state = 'enable'";
		error_log(date("Y-m-d H:i:s") . " - Verificar suscrito: $querySus\n", 3, "/opt/lampp/htdocs/private/clubs/conversiones.log");
		$resultSus = mysqli_query($con, $querySus);

		$return['estado'] = 0;

		while ($fila = mysqli_fetch_assoc($resultSus)) {
			
			$return['estado'] = 1;

			$return['data'] = $fila;

		}

		### SI ESTÁ SUSCRITO EL ESTADO SERÁ 1 ###
		if ($return['estado'] == 1) {
			
			$queryDLR = "SELECT * FROM clubs_DLR WHERE id_club = $id_club AND msisdn = '$msisdn'";

			$resultDLR = mysqli_query($con, $queryDLR);

			$cantidad = mysqli_num_rows($resultDLR);

			error_log(date("Y-m-d H:i:s") . " - Primer Cobro: $queryDLR Cantidad - $cantidad\n", 3, "/opt/lampp/htdocs/private/clubs/conversiones.log");
			

			### CERO ES PORQUE ES EL PRIMER COBRO ###
			if ($cantidad <= 2 ) {
				
				$return['primer_cobro'] = 'SI';

			} else {

				$return['primer_cobro'] = 'NO';

			}

		}

		error_log(date("Y-m-d H:i:s") . " - suscripcion_dlr: " . json_encode($return) . "\n", 3, "/opt/lampp/htdocs/private/clubs/conversiones.log");
		return $return;

	}

	### OBTENER INFORMACIÓN DEL CLUB ###
	public function club($con, $id_club){

		$query = "SELECT id_club, name_club, id_carrier, route, sc_mo, sc_cobro FROM clubs_SERVICE WHERE id_club = $id_club";
		error_log(date("Y-m-d H:i:s") . " - Verificar CLUB: $query\n", 3, "/opt/lampp/htdocs/private/clubs/conversiones.log");
		$result = mysqli_query($con, $query);

		$return['estado'] = 0;

		while ($fila = mysqli_fetch_assoc($result)) {
			
			$return['estado'] = 1;

			$return['data'] = $fila;

		}

		return $return;		

	}

	public function enviar_conversion($msisdn, $club, $corto){

		$url = "http://localhost:8081/adnetworks/ws_adnetworks_cpa/enviar_pixel_conversion_adnetworks_cpa.php?validar=abc33ef2021&msisdn=$msisdn&club=$club&corto=$corto";
		//echo "URL: " . $url . "\n";

		// create curl resource 
        $ch = curl_init(); 

        // set url 
        curl_setopt($ch, CURLOPT_URL, $url); 

        //return the transfer as a string 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

        // $output contains the output string 
        $output = curl_exec($ch); 

        // close curl resource to free up system resources 
        curl_close($ch);

        error_log(date("Y-m-d H:i:s") . " - Enviar conversion $msisdn: $output a URL: $url\n", 3, "/opt/lampp/htdocs/private/clubs/conversiones.log");

        return $output;

	}

}

/*
$msisdn = '50498385754';
$club = 25;

$con =  mysqli_connect('rds-compartido.pconnection.net', 'admin', '8sC3rq2iQqEztsj1', 'CLUBS');
$Conversion = new Conversion;
$suscripcion_dlr = $Conversion->suscripcion_dlr($con, $msisdn, $club);

if ($suscripcion_dlr['primer_cobro'] == 'SI') {
	
	$data_club = $Conversion->club($con, $suscripcion_dlr['data']['id_clubs']);

	if ($data_club['estado'] == 1) {

		$enviar_conversion = $Conversion->enviar_conversion($msisdn, $club, $data_club['data']['sc_mo']);
		echo "Enviar Conversion: " . $enviar_conversion;
		print_r($data_club);

	}

}

print_r($suscripcion_dlr);
*/
