<?php

$registros = [
                            "id_club"=>"17",
                            "id_msisdn"=>"id_msisdn17",
                            "msisdn"=>"msisdn17",
                            "puntos_bc"=>"puntos_bc17",
                            "id_delivery"=>"id_delivery17",
                            "id_detalle_service"=>"id_detalle_service17",
                          ];

$param[] = $registros;

$registros = [
                            "id_club"=>"79",
                            "id_msisdn"=>"id_msisdn79",
                            "msisdn"=>"msisdn79",
                            "puntos_bc"=>"puntos_bc79",
                            "id_delivery"=>"id_delivery79",
                            "id_detalle_service"=>"id_detalle_service79",
                          ];

$param[] = $registros;

$registros = [
                            "id_club"=>"102",
                            "id_msisdn"=>"id_msisdn102",
                            "msisdn"=>"msisdn102",
                            "puntos_bc"=>"puntos_bc102",
                            "id_delivery"=>"id_delivery102",
                            "id_detalle_service"=>"id_detalle_service102",
                          ];

$param[] = $registros;

//print_r($param);

$sql = "insert into clubs_ENVIOS_DIARIOS (id_clubs,id_msisdn,fecha,puntos,id_deliver,msisdn,id_detalle_service) values";

for ($i=0; $i < count($param); $i++) { 
	$id_club = $param[$i]['id_club'];
	$id_msisdn = $param[$i]['id_msisdn'];
	$msisdn = $param[$i]['msisdn'];
	$puntos = $param[$i]['puntos_bc'];
	$id_deliver = $param[$i]['id_delivery'];
	$id_detalle = $param[$i]['id_detalle_service'];
    
	if ($i < (count($param) - 1)) {
		$sql .= " ($id_club,$id_msisdn,DATE(CONVERT_TZ(curdate(), 'UTC', 'America/Tegucigalpa')),$puntos,'$id_deliver','$msisdn',$id_detalle),";
	} else {
		$sql .= " ($id_club,$id_msisdn,DATE(CONVERT_TZ(curdate(), 'UTC', 'America/Tegucigalpa')),$puntos,'$id_deliver','$msisdn',$id_detalle);";
	}
}

echo $sql;