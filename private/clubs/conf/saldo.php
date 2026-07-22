<?php

class PremioSorteado {

	public function dia(){

		date_default_timezone_set('America/Tegucigalpa');
		$hora = date("H:i:s");
		if($hora >= '00:00:00' and $hora <= '11:59:59'){
			$porciento = '0.6';
		}elseif($hora >= '12:00:00' and $hora <= '18:59:59'){
			$porciento = '0.8';
		}elseif($hora >= '19:00:00' and $hora <= '23:59:59'){
			$porciento = '1';
		}
		return $porciento;

	}

	public function descuento_premio($id){

		$query  = "update comunidades.`lucky_premios2` set cantidad = LAST_INSERT_ID(cantidad-1) where id = $id";
		$result = mysql_query($query);

	}

	public function premio($premio){

		$porc = $this->dia();
		$query = "select * from comunidades.`lucky_premios2` where descripcion = '$premio' and fecha = substr(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa'),1,10) and cantidad > (aprobado-($porc*aprobado)) order by rand() limit 1";
		$result = mysql_query($query);
		
		if(mysql_num_rows($result) > 0){

			if ($fila = mysql_fetch_assoc($result)) {
				return $fila;	
			}

		}else{
			return "-1";
		}

	}

	public function insertarmodem($origen, $mensaje, $id_club){

		$destino = substr($origen, -8);

		$con = mysqli_connect('rds-compartido.pconnection.net', 'admin', '8sC3rq2iQqEztsj1', 'ruletaRecargas');

		$query = "INSERT INTO recargas (`msisdn`, `valor`, fecha, flag, club) VALUES ('$destino', $mensaje, CONVERT_TZ(NOW(), 'UTC', 'America/Tegucigalpa'), 0, $id_club)";
		//echo $query;
		$result = mysqli_query($con, $query);

		mysqli_close($con);

		return;
	}

}

?>
