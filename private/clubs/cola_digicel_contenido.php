<?php
header("Access-Control-Allow-Origin: *");
error_reporting(0);
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

 if ($aprobacion <> 'DigicelEnviosSeguridadParaEnviarCobro') {

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


	//$query = "select * from comunidadesdigicelht.suscritos_comunidades where activo = 1 and destino = '7789' and comunidad_suscrita = 'aliento' group by numero";
	//se quito 02022021 agrego cobro semanal $query ="SELECT * FROM comunidadesdigicelht.suscritos_comunidades WHERE activo = 1 AND destino = '325' AND comunidad_suscrita = 'lavi' AND DATEDIFF(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa'), fecha_suscripcion) >= 1 ORDER BY fecha_ultimo_cobro DESC";
	//*$query ="SELECT * FROM comunidadesdigicelht.suscritos_comunidades WHERE activo = 1 AND destino = '325' AND comunidad_suscrita = 'lavi' AND DATEDIFF(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa'), fecha_suscripcion) >= 1 and (tipo_envio_cobro='0' or (tipo_envio_cobro = '1' and date(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')) >= proximo_envio_cobro)) ORDER BY fecha_ultimo_cobro DESC"; cambio a peticion de ernesto 25012022 por skype indico carlos para enviarle cobro a toda la base diariamente*/

	$query ="SELECT * FROM comunidadesdigicelht.suscritos_comunidades WHERE activo = 1 AND destino = '325' AND comunidad_suscrita = 'lavi' AND DATEDIFF(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa'), fecha_suscripcion) >= 1 and (tipo_envio_cobro='0' or tipo_envio_cobro = '1') ORDER BY fecha_ultimo_cobro DESC";

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
