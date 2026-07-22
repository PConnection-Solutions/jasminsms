<?php
  require_once __DIR__ . '/vendor/autoload.php';
  use PhpAmqpLib\Connection\AMQPConnection;
  chdir(dirname(__FILE__));
  require_once('./conf/conf.inc');
  set_time_limit(0);
  
  
  $conn = mysql_connect($hostdb,$userdb,$passdb) or die();
  mysql_selectdb($namedb,$conn) or die();
  mysql_query('SET SESSION wait_timeout = 50400',$conn);

  $conn2 = mysql_pconnect('127.0.0.1','root','') or die();
  mysql_selectdb('CLUBS',$conn2) or die();
  mysql_query('SET SESSION wait_timeout = 50400',$conn2);
  
  $connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
  $channel = $connection->channel();

  //$channel->queue_declare('cdc_notification', false, true, false, false);
  //$channel->queue_declare('CLARO_HN', false, true, false, false);
  $channel->queue_declare('CLARO_NI', false, true, false, false);
  //$channel->queue_declare('CLARO_SV', false, true, false, false);
  echo ' [*] Esperando los mensajes del deliverNotification de Claro NI. Oprima CTRL+C para salir', "\n";
  
  $callback = function($msg) use ($conn) 
  {
      /*

      */
      if (!mysql_ping($conn)) 
      {
        mysql_close($conn);
        $conn = mysql_connect('rds-compartido.pconnection.net','admin','8sC3rq2iQqEztsj1') or die();
        mysql_selectdb('CLUBS',$conn) or die();
      }

      if (!mysql_ping($conn2)) 
      {
        mysql_close($conn2);
        $conn2 = mysql_connect('rds-compartido.pconnection.net','admin','8sC3rq2iQqEztsj1') or die();
        mysql_selectdb('CLUBS',$conn2) or die();
      }         

      $utilidades = new utilidades();
      $deliveryNotificationRequest = json_decode($msg->body);
      
      $transId = $deliveryNotificationRequest[0]->transId; // string
      $messageId = $deliveryNotificationRequest[0]->messageId; // string
      
      $isDlr = strpos($messageId,'-')=== false ? false : true;//El id de los dlr tiene una estructura ID-ID_CLUB ej. xytzt3-5 donde 5 es el id del club
      
      $indice_noti = substr($messageId,0,1);
      
      //$messageId_mod = substr($messageId,1);//Dlr propio que se envio
      $messageId_mod = $messageId;
      
      $indice = strrpos ($messageId_mod,'-') +1 ;//Obtenemos el indice donde esta -
      $id_club = substr($messageId_mod,$indice);//Obtenemos el ID del Club 
      
      $isDlr = strpos($messageId,'-')=== false ? false : true;//El id de los dlr tiene una estructura ID-ID_CLUB ej. xytzt3-5 donde 5 es el id del club
      
      $from = $deliveryNotificationRequest[0]->from; // string
      $to = $deliveryNotificationRequest[0]->to; // string
      $status = $deliveryNotificationRequest[0]->status; // int
      $tariffId = $deliveryNotificationRequest[0]->tariffId; // int
      $price = $deliveryNotificationRequest[0]->price; // double
      //$errorCode = is_null($deliveryNotificationRequest[0]->errorCode)? -1 :$deliveryNotificationRequest[0]->errorCode; // int
      $errorCode = isset($deliveryNotificationRequest[0]->errorCode)? $deliveryNotificationRequest[0]->errorCode :-1; // int
      $errorMessage = $deliveryNotificationRequest[0]->errorMessage; // string
      $red = $deliveryNotificationRequest[0]->red;
      $url = '';
      //Obtenemos el club_name x el tariffId, esto es para las suscripciones
      $club_data = $utilidades->get_club_name_by_tariffId($conn,$tariffId,$red);
      $club_name = $club_data['name_club'];
      $club_id = $club_data['id_club'];//Por si acaso viene por este lado tambien (por el tariffId)
      $text = "Alta $club_name:$tariffId";
      
      //http://localhost/private/clubs/dlr_club.php?destination=$to&source=$from&meta=%a&myid=$messageId_mod&id_club=$id_club&recobro=1;
      //status 0 y errorcode -1 tariffix <> 0 esto significa dlr cobrado
      //status -1 y errorcode otro y tariffid <> 0 
      switch($errorCode)
      {
          case 601://suscripcion OK
          case 602://Renovacion OK
          case 4002://Suscriptor ya suscrito al paquete OK
            $text = urlencode("ALTA:$club_name TariffId:$tariffId Msg:$errorMessage");
            $service = "";
            $url = "http://localhost/private/clubs/default_club.php?phone=$to&text=$text&prefix=$club_name&service=$service&sc=$from&ruta=$red&type=$transId";
            if($from == '8611' && $red == 'CLARO_NI')
        {
            $url2 = "http://localhost/comunout/desafio/incripcion_juegani.php";
	    //$texto2 = str_replace('CDC_submitSMS','',$text);
            $texto2 = "PREMIO";
            $valores = "origen=".$to."&mensaje=".$texto2."&destino=".$from."&ruta=".$red."&transid=".$transId;
            $handler = curl_init();
            curl_setopt($handler, CURLOPT_URL, $url2);
            curl_setopt($handler, CURLOPT_POST,true);
            curl_setopt($handler, CURLOPT_POSTFIELDS, $valores);
            $response = curl_exec ($handler);
            curl_close($handler);
           // $log_user_data->mos($phone,$texto,$shortcode,$ruta,$service_type);
        }

        if($from == '8600' && $red == 'CLARO_NI' && ($club_name == 'CRISTIANOS' || $club_name == 'SOLTEROS')){

            $url2 = "http://localhost/solterosvip/wscritianos.php?origen=".$to."&mensaje=".$texto2."&destino=".$from;
            error_log("http://localhost/solterosvip/wscritianos.php?origen=".$to."&mensaje=".$texto2."&destino=".$from."\n",3,"/opt/lampp/htdocs/crissolni.log");
              $ch=curl_init();
              curl_setopt($ch,CURLOPT_URL, $url2);
              curl_exec($ch);
              curl_close($ch);

          }
            break;
          case 603://Cancelacion OK
          case 604://Expiracion OK
          case 206://Suscriptor no encontrado
          case 215://Invalid msisdn
          //case 222://Suscriptor no esta activo
          //case 224://Usuario en Black List
            $text = urlencode("BAJA:$club_name TariffId:$tariffId Msg:$errorMessage");
            $service = "";
            $url = "http://localhost/private/clubs/baja_club.php?phone=$to&text=$text&prefix=BAJA&service=$club_name&sc=$from&ruta=$red&type=$transId";
            if($from == '8611' && $red == 'CLARO_NI')
            {
              $url2 = "http://localhost/comunout/desafio/incripcion_juegani.php";
              //$texto2 = str_replace('CDC_submitSMS','',$text);
              $texto2 = "BAJA";
              $valores = "origen=".$to."&mensaje=".$texto2."&destino=".$from."&ruta=".$red."&transid=".$transId;
              $handler = curl_init();
              curl_setopt($handler, CURLOPT_URL, $url2);
              curl_setopt($handler, CURLOPT_POST,true);
              curl_setopt($handler, CURLOPT_POSTFIELDS, $valores);
              $response = curl_exec ($handler);
              curl_close($handler);
             // $log_user_data->mos($phone,$texto,$shortcode,$ruta,$service_type);
            }
            break;
          case -1:
          case 0:
          case 201:
          case 202:
          case 203:
          case 204:
          case 205://Balance insuficiente
          case 206:
          case 207:
          case 208:
          case 209:
          case 210://Solicitud de cobro duplicada
          case 211:
          case 212:
          case 213:
          case 214:
          case 215:
          case 216:
          case 217://Chargin fail
          case 218:
          case 219:
          case 220:
          case 221:
          case 222:
          case 299:
            if (($tariffId != 0))
            {
                if (($status == 0) && ($errorCode == -1))
                {
                    $errorCode = 0;//Cobrado positivamente
                }
                $text = urlencode("$errorCode");
                $url = "http://localhost/private/clubs/dlr_club.php?destination=$to&source=$from&meta=$text&myid=$messageId_mod&id_club=$id_club&recobro=1";
            }
            break;
          default:
            $text = urlencode("$errorCode");
            $url = "http://localhost/private/clubs/dlr_club.php?destination=$to&source=$from&meta=$text&myid=$messageId_mod&id_club=$id_club&recobro=0";
            break;
          
      }
      echo " [x] Received ", $msg->body, "\n";
      //echo 'datos:' ,$url;
      $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
      
      $notification_log = new smsNotificationLog($conn2);
      $notification_log->appendData($deliveryNotificationRequest[0]);
      
      $options = array( 'http'=>array('method'=>"GET", 
                                        'header'=>"Accept-language: en\r\n", 
                                        'timeout' => 2 
                                        ) 
                        );
      $context = stream_context_create($options); 
      if ($url)
      {
          $result = @file($url,null,$context);
      }
      
      
  };
  
  $channel->basic_qos(null, 1, null);
  $channel->basic_consume('CLARO_NI', '', false, false, false, false, $callback);
  
  while(count($channel->callbacks)) 
  {
    $channel->wait();
  }
  $channel->close();
  $connection->close();

  
?>
