<?php
  require_once __DIR__ . '/vendor/autoload.php';
  use PhpAmqpLib\Connection\AMQPConnection;
  chdir(dirname(__FILE__));

require_once('./conf/dlr.php');
require_once('./conf/contenido.php');
require_once('./conf/saldo.php');
include '.wsdlr.php';
error_reporting(E_ERROR | E_WARNING);

date_default_timezone_set("America/Tegucigalpa");
$userdb = 'admin';
$passdb = '8sC3rq2iQqEztsj1';
$namedb = 'CLUBS';
$hostdb = 'rds-compartido.pconnection.net';
//$hostdb = '184.107.61.32';

function darvidasAPPrende($from, $tasa){
    $hostname_smsR = "rds-compartido.pconnection.net";
    $database_smsR = "sms";
    $username_smsR = "admin";
    $password_smsR = "8sC3rq2iQqEztsj1";
    $smsRemoto = mysql_pconnect($hostname_smsR, $username_smsR, $password_smsR) or die(mysql_error());
    $query = "UPDATE `CLUBPREGUNTAS`.`mega_PUNTOS` SET `eqssd` = `eqssd` + ROUND((3*$tasa),0) WHERE  `msisdn`='$from'";
    //echo $query;
    $result = mysql_query($query, $smsRemoto);
}

function puntosFutbol($msisdn) {

    $hostname_smsR = "rds-compartido.pconnection.net";
    $database_smsR = "sms";
    $username_smsR = "admin";
    $password_smsR = "8sC3rq2iQqEztsj1";
    $smsRemoto = mysql_pconnect($hostname_smsR, $username_smsR, $password_smsR) or die(mysql_error());

    $query = "UPDATE `Quiniela2018`.`promo_MSISDN` SET `puntos` = `puntos` + 10 WHERE  `msisdn`='$msisdn'";
    $result = mysql_query($query);

}

function verificarTigo($id_club){

    $query = "SELECT * FROM clubs_SERVICE WHERE route = 'TIGO_HN' AND id_club = $id_club";
    $result = mysql_query($query);
    //error_log("$query"."\n", 3, "dlr_escalonado.log");
    $cantidad = mysql_num_rows($result);
    if($cantidad >= 1){

        return 1;

    }else{

        return 0;

    }

}

function id_msisdn($msisdn, $id_club){

    $query = "select id_msisdn from clubs_MSISDN WHERE msisdn = '$msisdn' AND id_clubs = $id_club";
    $result = mysql_query($query);
    while ($row = mysql_fetch_assoc($result)){

        $id_msisdn = $row['id_msisdn'];

    }

    return $id_msisdn;

}

function contenido($id_club){

    $query = "SELECT * FROM clubs_SERVICE_DET WHERE id_clubs = $id_club ORDER BY RAND() LIMIT 1";
    $result = mysql_query($query);

    error_log("Conteido Query: $query"."\n", 3, "dlr_escalonado.log");

    while ($fila = mysql_fetch_assoc($result)){

        $contenido = $fila['content'];

    }

    return $contenido;

}

function unique_id()
    {
        return uniqid(true);
    }

function sendsms($host, $script, $request, $port){
        $request_length = strlen($request);
        $method = "GET"; // must be POST if sending multiple messages
        if ($method == "GET")
        {
          $script .= "?$request";
        }
        //Now comes the header which we are going to post.
        $header = "$method $script HTTP/1.1\r\n";
        $header .= "Host: $host\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: $request_length\r\n";
        $header .= "Connection: close\r\n\r\n";
        $header .= "$request\r\n";

        //Now we open up the connection
        $socket = @fsockopen($host, $port, $errno, $errstr);
        if ($socket) //if its open, then...
        {
          fputs($socket, $header); // send the details over
          while(!feof($socket))
          {
            $output[] = fgets($socket); //get the results
          }
        fclose($socket);
        }
}

function enviar_mensaje($destino, $origen, $mensaje, $binfo, $id_club, $priority=2){
    $username = 'tigo';
    $password = 'esosa';    
    $host = "localhost";
    $script = "/cgi-bin/sendsms";
    $port = '13013';
    //$id_club = '55';
    $last_id = unique_id();
    //$request = 'username=broadcast&password=tigo&from=' . $to . '&to=' . $from . urlencode($message) . '&mclass=0';
//  $url = "http://localhost/sms/dlr-honduras.php?smsc-id=%i&status=%d&answer=%A&to=%P&from=%p&ts=%t";
    $url = 'http://'.$wsdlr.'/private/clubs/dlr_club.php?destination=%p&source=%P&meta=%a&myid='.$last_id . '&id_club='.$id_club . '&recobro=0';

    $smsc = array('tigo-hn-bc-1', 'tigo-hn-bc-2', 'tigo-hn-bc-3', 'tigo-hn-bc-4');

    $tigo_hn_bc = $smsc[rand(0,3)];

    if(substr($binfo,0,1) == "C"){      
        $request = 'username=' . $username . '&password=' . $password . '&from=' . $origen . '&to=' . $destino . '&text=' . urlencode($mensaje) . "&dlr-mask=31&smsc=".$tigo_hn_bc."&priority=".$priority."&dlr-url=" . urlencode($url) . "&binfo=" . $binfo;
    }else{
        $request = 'username=' . $username . '&password=' . $password . '&from=' . $origen . '&to=' . $destino . '&text=' . urlencode($mensaje) . "&smsc=tigo-hn-bc-4&binfo=" . $binfo;
    }

    error_log("$request"."\n", 3, "dlr_escalonado.log");
    //echo $request . "\n";
    sendsms($host, $script, $request, $port);
    return $last_id;
}

function crearBinfo($id_club){

    $query = "SELECT SUBSTR(name_club, 1, 4) binfo FROM clubs_SERVICE WHERE id_club = $id_club";
    $result = mysql_query($query);
    
    while ($fila = mysql_fetch_assoc($result)){

        $binfo = $fila['binfo'];

    }

    return $binfo;

}

function insertar_envios_mt($id_club,$id_msisdn,$msisdn,$puntos=0,$id_deliver,$id_detalle)
    {
        $sql = "insert into CLUBS.clubs_ENVIOS_DIARIOS (id_clubs,id_msisdn,fecha,puntos,id_deliver,msisdn,id_detalle_service)".
            " values($id_club,$id_msisdn,DATE(CONVERT_TZ(curdate(), 'UTC', 'America/Tegucigalpa')),$puntos,'$id_deliver','$msisdn',$id_detalle)";
        error_log("$sql"."\n", 3, "dlr_escalonado.log");
    //echo date("Y-m-d H:i:s")  . " ".$sql."\n";
        //return mysql_query($sql);
    }

function genera_codigo_sin_premio(){
    while(true){
        $codigo = "";
        for($i = 0; $i < 3; ++$i) {
            $codigo .= rand(0,9);
        }
        $codigo1 = substr_count($codigo, $codigo[0]);
        $codigo2 = substr_count($codigo, $codigo[1]);
        if(($codigo1 == 2) or ($codigo2 == 2)){
            continue;
        }elseif($codigo1 == 3){
            continue;
        }else{
            break;
        }
    }
    return $codigo;
}

function verificarNumero($mensaje){

        $datos = array();
        $mensaje = trim($mensaje);
        $query = "SELECT * FROM `CAJA`.`ganadores` WHERE flag = 0 LIMIT 1";
        //echo $query;
        $result = mysql_query($query);
        while ($row = mysql_fetch_assoc($result)){

            $datos = $row;

            if($row['codigo'] == $mensaje){

                $datos['resultado'] = 1;

            }else{

                $datos['resultado'] = 0;

            }

        }

        return $datos;

    }

    function pista($numero){

        $query = "SELECT * FROM CAJA.pista WHERE respuesta = $numero ORDER BY RAND() LIMIT 1";
        $result = mysql_query($query);

        while ($row = mysql_fetch_assoc($result)){

            $datos = $row;

        }

        //print_r($datos);

        return $datos;

    }

    function kultura($msisdn) {

        $conkultura = mysqli_connect('rds-compartido.pconnection.net', 'admin', '8sC3rq2iQqEztsj1', 'promo_tierra_catracha');

        $intentos = 3;

        $query1 = "INSERT INTO usuarios (msisdn, intentos_disponibles, fecha_ultimo_cobro_mt, cobros_acumulados_mt) VALUES ('$msisdn', 3, DATE(CONVERT_TZ(NOW(), 'UTC', 'America/Tegucigalpa')), 1) ON DUPLICATE KEY UPDATE fecha_ultimo_cobro_mt = DATE(CONVERT_TZ(NOW(), 'UTC', 'America/Tegucigalpa')), cobros_acumulados_mt = cobros_acumulados_mt + 1, intentos_disponibles = intentos_disponibles + $intentos";
        $result = mysqli_query($conkultura, $query1);

        $query2 = "INSERT INTO logs_MT (msisdn, fecha_cobro, cant_intentos) VALUES ('$msisdn', CONVERT_TZ(NOW(), 'UTC', 'America/Tegucigalpa'), $intentos)";
        $result2 = mysqli_query($conkultura, $query2);

        mysqli_close($conkultura);

    }

/*
Kannel:destination,source,meta,myid
Beconn:ResultType,ResultData,IdDeliver
Modulo de registro de los DLR, de kannel y Beconnected, este modulo guarda los dlr que se consideran importantes,
actualiza la tabla de envio en caso que sea positivo el cobro y establece los recobros en caso que se requiera

*/

$conn = mysql_connect($hostdb,$userdb,$passdb);

$connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');

  $channel = $connection->channel();

  $channel->queue_declare('TigoNACK', false, true, false, false);
  echo ' [*] Esperando los mensajes del deliverNotification de Claro HN. Oprima CTRL+C para salir', "\n";

$callback = function($msg) use ($conn, $namedb)
{

    //echo " [x] Received ", $msg->body, "\n";
    $DATA = json_decode($msg->body, true);

    $destination = str_replace('+','',rawurldecode($DATA['destination']));
    $meta = urldecode($DATA['meta']);
    $myid = urldecode($DATA['myid']);
    $source = urldecode($DATA['source']);

    $id_club = urldecode($DATA['id_club']);
    $recobro=urldecode($DATA['recobro']);

    if (isset($DATA['dlr_alta'])) {
        $dlr_alta = $DATA['dlr_alta'];
    } else {
        $dlr_alta = 0;
    }

    if (isset($DATA['penvio'])) {
        $penvio = $DATA['penvio'];
    } else {
        $penvio = 0;
    }

    include ("cuerpoClubs.php");

    echo " [x] Received ", $msg->body, "\n";
      //echo 'datos:' ,$url;
      $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);

};

$channel->basic_qos(null, 1, null);
  $channel->basic_consume('TigoNACK', '', false, false, false, false, $callback);
  
  while(count($channel->callbacks)) 
  {
    $channel->wait();
  }
  $channel->close();
  $connection->close();

mysql_close($conn);
  
?>
