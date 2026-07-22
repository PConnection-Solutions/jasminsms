<?php
  require_once __DIR__ . '/vendor/autoload.php';
  use PhpAmqpLib\Connection\AMQPConnection;
  chdir(dirname(__FILE__));
  //require_once('./conf/conf.inc');
  set_time_limit(0);
  require('sendsms_digicelht_directo.php');
  $comunidad = "lavi";
  $cola = $argv[1];

  $hostname_sms = "rds-compartido.pconnection.net";
  $database_sms = "smsdigicelht";
  $username_sms = "admin";
  $password_sms = "8sC3rq2iQqEztsj1";
  $sms = mysql_pconnect($hostname_sms, $username_sms, $password_sms) or die(mysql_error());
  date_default_timezone_set("America/Tegucigalpa");

  function guardar_sms($origen, $destino, $palabra_clave_promocion, $mensaje){
    $query = "INSERT INTO comunidadesdigicelht.mensajes_comunidades(numero, c_corto, palabra_clave, mensaje, fecha) 
      VALUES('$origen', '$destino', '$palabra_clave_promocion', '$mensaje', CONVERT_TZ(NOW(), 'UTC', 'America/Tegucigalpa'))";
    $result = mysql_query($query);
  }

  function contenido($comunidad){
  $flag = '0';
  
  $query = "select * from comunidadesdigicelht.contenidosms where comunidad = '$comunidad' and fecha = date(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')) limit 1";  
  $texto = "";
  $result = mysql_query($query);
  while($fila = mysql_fetch_assoc($result)){
    $id = $fila['id'];
    $texto = $fila['texto'];
    $flag = '1';
  }
  if ($flag == '0'){
    $query = "select * from comunidadesdigicelht.contenidosms where comunidad = '$comunidad' and id not in (select id from comunidadesdigicelht.crtlsms where comunidad = '$comunidad') limit 1"; 
    $result = mysql_query($query);
    $texto = "";
    while($fila = mysql_fetch_assoc($result)){
      $id = $fila['id'];
      $texto = $fila['texto'];
      $query_upd = "insert into comunidadesdigicelht.crtlsms set id = $id, comunidad = '$comunidad'";
      $result_upd = mysql_query($query_upd);
      $queryup  = "update comunidadesdigicelht.contenidosms set fecha = date(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')) where id = '$id'";
      $resultup = mysql_query($queryup);
    }
    if ($texto == ""){
      $query_upd = "delete from comunidadesdigicelht.crtlsms where comunidad = '$comunidad'";
      $result_upd = mysql_query($query_upd);
      $result = mysql_query($query);
      while($fila = mysql_fetch_assoc($result)){
        $id = $fila['id'];
        $texto = $fila['texto'];
        $query_upd = "insert into comunidadesdigicelht.crtlsms set id = $id, comunidad = '$comunidad'";
        $result_upd = mysql_query($query_upd);
        $queryup  = "update comunidadesdigicelht.contenidosms set fecha = date(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')) where id = '$id'";
        $resultup = mysql_query($queryup);
      }
    
    }
  }
  if ($texto == ""){
    exit;
  }
  return $texto;
}

  function par($num) {
    if ($num %2 == 0) {
      return true;
    }else {
      return false;
    }
  }

  $connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
  $channel = $connection->channel();

  $channel->queue_declare('DigicelHaiti', false, true, false, false);
  
  echo ' [*] Esperando los mensajes del deliverSMS. Oprima CTRL+C para salir', "\n";

  //no se enviara contenido a solicitu digicel 05032021 $contenido = contenido('lavi');
  $contenido = "";
  //cambio 04122018 $mensaje_renovacion = "Enskripsyon Life Club la fek renouvle pou 8HTG. Klike sou https://goo.gl/e78Rzw pou w pwofite pi bon konteni nou yo. Pou w kite sevis la, voye STOP bay 325";
  //cambio 22022021 $mensaje_renovacion = "Enskripsyon Life Club la fek renouvle pou 8HTG. Klike sou https://goo.gl/e78Rzw pou w pwofite pi bon konteni nou yo. Pou w kite sevis la, voye STOP bay 325";
  //cambio 30062022 $mensaje_renovacion = "Enskripsyon pou 1 jou nan sevis 'LIFE CLUB' la renouvle pou 8HTG! Kontinye pwofite sevis la sou https://goo.gl/e78Rzw. Voye STOP bay 325 pou kanpe enskripsyon";
  $mensaje_renovacion = "Enskripsyon pou 1 jou nan sevis 'LIFE CLUB' la renouvle pou 20HTG! Kontinye pwofite sevis la sou https://goo.gl/e78Rzw. Voye STOP bay 325 pou kanpe enskripsyon";
  $callback = function($msg) use ($sms, $contenido, $mensaje_renovacion, $comunidad, $cola)
  {

      echo " [x] Received ", $msg->body, "\n"; 
      $origen = $msg->body;
      $destino = "325";
      $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
      $cobro = enviar_cobro($origen, $destino, $mensaje_renovacion, $comunidad);
      //echo "enviar_cobro($origen, $destino, $mensaje_renovacion, $comunidad)";
      if($cobro['status_billing'] == 'SUCCESS'){
        //no se enviara contenido a solicitu digicel 05032021 enviar_mensaje($origen, $destino, $contenido, $comunidad);
        //guardar_sms($origen, $destino, "lavi", $mensaje);
        //no se enviara contenido a solicitu digicel 05032021 guardar_sms($origen, $destino, 'lavi', $contenido);
        actualizacion_cobro($origen,20);
      }
      

      $log = date("Y-m-d H:i:s")." Worker: $cola - Origen: $origen - Destino: $destino - Tiempo Respuesta - ".round($cobro['total_time'], 2)."s - Cobro: " . $cobro['status_billing']." id:". $cobro['id']. " https_response:".$cobro['http_code'];

      error_log($log ."\n", 3, "/opt/lampp/htdocs/private/clubs/digicel_recobro.log");
  
  };
  
  $channel->basic_qos(null, 1, null);
  $channel->basic_consume('DigicelHaiti', '', false, false, false, false, $callback);
  
  while(count($channel->callbacks)) 
  {
    $channel->wait();
  }
  $channel->close();
  $connection->close();

  
?>
