<?php
  require_once __DIR__ . '/vendor/autoload.php';
  use PhpAmqpLib\Connection\AMQPConnection;
  chdir(dirname(__FILE__));
  require_once('./conf/conf.inc');
  require_once('./conf/dlr.php');
  set_time_limit(0);
  
  $conn = mysql_connect($hostdb,$userdb,$passdb) or die();
  mysql_selectdb($namedb,$conn) or die();
  //ping($conn);
  //mysql_query('SET SESSION wait_timeout = 50400',$conn);
  $connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
  $channel = $connection->channel();

  $channel->queue_declare('beconn_dlr', false, true, false, false);
  
  echo ' [*] Esperando los mensajes del dlr. Oprima CTRL+C para salir', "\n";
  
  $callback = function($msg) use ($conn) 
  {
      if (!mysql_ping($conn)) 
      {
        mysql_close($conn);
        //$conn = mysql_connect('184.107.61.32','admin','8sC3rq2iQqEztsj1') or die();
        $conn = mysql_connect('rds-compartido.pconnection.net','admin','8sC3rq2iQqEztsj1') or die();
        mysql_selectdb('CLUBS',$conn) or die();
      } 
    
      $dlr_info = new dlr($conn);
      $datos = json_decode($msg->body);
      
      $resulttype = $datos[0]->resulttype;
      $resultdata = $datos[0]->resultdata;
      $iddeliver = $datos[0]->iddeliver;
      $id_club = $datos[0]->id_club;
      $source = $datos[0]->source;
      $recobro = $datos[0]->recobro;
      $myid = $datos[0]->myid;
      
      echo " [x] Received ", $msg->body, "\n";
      $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
      if($resultdata)//Beconnected
      {
            // | son las divisiones de registro y , son las divisiones del campo
            $result_data = explode("|",$resultdata);
            foreach($result_data as $key => $value)
            {
                $result_data_comma = explode(",",$value);
                $id_tran = $result_data_comma[0]; //idtransaction
                $msisdn = $result_data_comma[1]; //msisdn
                $status = $result_data_comma[2]; //status
                //$cobro = 0;
                $cobro = $result_data_comma[3] ? $result_data_comma[3] : 0;
                $sql ="insert into clubs_DLR (id_transaction,id_deliver,msisdn,source,estado,fecha_evento,fecha,valor_cobrado,id_club,otros)".
                    //" value('$id_tran','$myid','$msisdn','$source','$status',DATE(CONVERT_TZ(curdate(), 'UTC', 'America/Tegucigalpa')),$cobro,$id_club,'$value')";
                    " value('$id_tran','$myid','$msisdn','$source','$status',CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa'),DATE(CONVERT_TZ(curdate(), 'UTC', 'America/Tegucigalpa')),$cobro,$id_club,'$status')";
                mysql_query($sql,$conn);
                if ($status == '100')
                {
                    $sql = "update clubs_ENVIOS_DIARIOS set eventos_cobrados = eventos_cobrados + 1".
                    " where msisdn='$msisdn' and id_deliver = '$myid'";
                    mysql_query($sql,$conn);
                    $sql = "update clubs_MSISDN set fecha_ult_evento=DATE(CONVERT_TZ(curdate(), 'UTC', 'America/Tegucigalpa')) where msisdn= '$msisdn'";
                    mysql_query($sql,$conn);
                }
                else if ($status == '400')
                {
                    $sql = "insert into clubs_DLR_COBRO_PARCIAL (id_club,msisdn,myid) values ($id_club,'$msisdn','$myid')";
                    //mysql_query($sql,$conn);
                }

            }
      }
  };
  
  $channel->basic_qos(null, 1, null);
  $channel->basic_consume('beconn_dlr', '', false, false, false, false, $callback);
  
  while(count($channel->callbacks)) 
  {
    $channel->wait();
  }
  $channel->close();
  $connection->close();

  ?>
