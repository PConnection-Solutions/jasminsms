<?php
  require_once __DIR__ . '/vendor/autoload.php';
  use PhpAmqpLib\Connection\AMQPConnection;
  chdir(dirname(__FILE__));
  require_once('./conf/conf.inc');
  set_time_limit(0);
  
  $conn = mysql_connect($hostdb,$userdb,$passdb) or die();
  mysql_selectdb($namedb,$conn) or die();
  
  $connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
  $channel = $connection->channel();

  $channel->queue_declare('cdc_deliversms', false, true, false, false);
  
  echo ' [*] Esperando los mensajes del deliverSMS. Oprima CTRL+C para salir', "\n";
  
  $callback = function($msg) use ($conn) 
  {
      $deliverSMSRequest = json_decode($msg->body);
      
      $transId = $deliverSMSRequest[0]->transId;
      $From = urlencode($deliverSMSRequest[0]->from);
      $To = urlencode($deliverSMSRequest[0]->to);
      $Suffix = $deliverSMSRequest[0]->suffix;
      $requestDtime = $deliverSMSRequest[0]->requestDtime;
      $messageBody = $deliverSMSRequest[0]->messageBody;
      $red = $deliverSMSRequest[0]->red;
      
      $words = explode(' ',trim($messageBody));
      $count_words = count($words);
      if ($count_words >= 2)
      {
          $service = $words[1];
          $prefix = $words[0];
      }else if ($count_words < 2)
      {
          $prefix = $words[0];
          $service = '';
      }
      
      $messageBody = urlencode($messageBody.'-CDC_submitSMS');
      
      echo " [x] Received ", $msg->body, "\n";
      $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
      
      $sufijo = "?phone=$From&text=$messageBody&prefix=$prefix&service=$service&sc=$To&ruta=$red&type=$transId";
      //http://localhost/private/clubs/default_club.php?phone=%p&text=%a&prefix=%k&service=%s&sc=%P&ruta=TIGO_HN&type=%B
      /*Opciones de Entrega
        1) Ayuda Ej: Ayuda Cristianos
        2) Trivia Ej: Cualquier palabra
        3) Puntos Ej: Puntos Cristianos
      */
      $url = '';
      switch(strtoupper($prefix))
      {
          case 'AYUDA':
          case 'HELP':
            $url = 'http://localhost/private/clubs/ayuda_club.php';
            break;
          case 'PUNTOS':
          case 'PUNTO':
          case 'POINTS':
          case 'POINT':
            $url = 'http://localhost/private/clubs/puntos_club.php';
            break;
          default://Aqui deberia de caer en los que solo son envios
            
            /*if ($To == '7789' && $red == 'CLARO_HN' && substr_count($messageBody, ' ') == 1) {

              $texto2 = str_replace('-CDC_submitSMS','',$messageBody);
              $url = "http://localhost/ficohsa3/SMS/codigo.php";
              $sufijo = "msisdn=".$From."&mensaje=".$texto2."&sc=".$To."&red=".$red."&transid=".$transId;

            } else {*/

              $url = 'http://localhost/private/clubs/default_club_envio.php';
            
           // }
            
            //$url = 'http://localhost/private/clubs/trivia_club.php';
          
      }
      
      $options = array( 'http'=>array('method'=>"GET", 
                                        'header'=>"Accept-language: en\r\n", 
                                        'timeout' => 2 
                                        ) 
                        );
      $context = stream_context_create($options); 
      echo $url . $sufijo;
      $result = @file($url . $sufijo,null,$context);
      
      
  };
  
  $channel->basic_qos(null, 1, null);
  $channel->basic_consume('cdc_deliversms', '', false, false, false, false, $callback);
  
  while(count($channel->callbacks)) 
  {
    $channel->wait();
  }
  $channel->close();
  $connection->close();

  
?>
