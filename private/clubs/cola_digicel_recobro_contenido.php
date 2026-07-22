<?php
header("Access-Control-Allow-Origin: *");
error_reporting(E_ALL);
require_once __DIR__ . '/vendor/autoload.php';

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

 if (isset($_REQUEST['aprobacion'])) {
 	$aprobacion = urldecode($_REQUEST['aprobacion']);
 } else {
 	$aprobacion = '';
 }

 if ($aprobacion <> 'DigicelEnviosSeguridadParaEnviarRecobro') {

 	die("OK");

 }

	use PhpAmqpLib\Connection\AMQPConnection;
	use PhpAmqpLib\Message\AMQPMessage;

	date_default_timezone_set("America/Tegucigalpa");

	$hostname_sms = "rds-compartido.pconnection.net";
	$database_sms = "smsdigicelht";
	$username_sms = "admin";
	$password_sms = "8sC3rq2iQqEztsj1";
	$sms = mysqli_connect($hostname_sms, $username_sms, $password_sms, $database_sms);

	$connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
	$channel = $connection->channel();

	$red = 'DigicelHaiti';

	$channel->queue_declare($red, false, true, false, false);

	$ncobro = "";

	$query ="SELECT destino FROM smsdigicelht.sms_log where fecha >= CONCAT(DATE(CONVERT_TZ(NOW(), 'UTC', 'America/Tegucigalpa')), ' 00:00:00') AND status_billing = 'SUCCESS' and origen in (325) and tipo_sms = '1' and comunidad = 'lavi'";

	$result = mysqli_query($sms, $query);
	//$mensaje = contenido('aliento');
	while($fila = mysqli_fetch_assoc($result)){
		$ncobro = $ncobro . $fila['destino']. ",";
	}

	$ncobro = substr($ncobro, 0, -1);

	//$query = "select * from comunidadesdigicelht.suscritos_comunidades where activo = 1 and destino = '7789' and comunidad_suscrita = 'aliento' group by numero";
	//se quito 02022021 agrego cobro semanal $query ="SELECT * FROM comunidadesdigicelht.suscritos_comunidades where activo = 1 and destino = '325' and comunidad_suscrita = 'lavi' and DATEDIFF(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa'), fecha_suscripcion) >= 1 and numero not in  (".$ncobro.") order by fecha_ultimo_cobro desc";
	/*$query ="SELECT * FROM comunidadesdigicelht.suscritos_comunidades where activo = 1 and destino = '325' and comunidad_suscrita = 'lavi' and DATEDIFF(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa'), fecha_suscripcion) >= 1 and numero not in  (".$ncobro.") and (tipo_envio_cobro='0' or (tipo_envio_cobro = '1' and date(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')) >= proximo_envio_cobro)) order by fecha_ultimo_cobro desc";cambio a peticion de ernesto 25012022 por skype indico carlos para enviarle cobro a toda la base diariamente*/

	$query ="SELECT * FROM comunidadesdigicelht.suscritos_comunidades where activo = 1 and destino = '325' and comunidad_suscrita = 'lavi' and DATEDIFF(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa'), fecha_suscripcion) >= 1 and numero not in  (".$ncobro.") and (tipo_envio_cobro='0' or tipo_envio_cobro = '1') order by fecha_ultimo_cobro desc";

	echo $query;

	$result = mysqli_query($sms, $query);

	while($fila = mysqli_fetch_assoc($result)){

		$origen = $fila['numero'];

		echo date("Y-m-d H:i:s") . " $origen"."\n";

		/*$data = [
		        $origen
		        ];

		$dataJSON = json_encode($data);*/

		//$channel->queue_declare('cdc_notification', false, true, false, false);

		$msg = new AMQPMessage($origen, array('delivery_mode' => 2) );

		//$channel->basic_publish($msg, '', 'cdc_notification');
		$channel->basic_publish($msg, '', $red);

	}

	$channel->close();
	$connection->close();
	mysqli_close($sms);

	//require_once('./conf/conf.inc');
