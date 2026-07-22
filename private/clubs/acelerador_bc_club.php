<?php

/*
    Modulo de notificaciones de broadcast , obtiene como parametros
    el id_carrier
*/
set_time_limit(0);
chdir(dirname(__FILE__));
header ("Content-type: text/plain");
//error_reporting(E_ALL ^ E_NOTICE);
date_default_timezone_set('America/Tegucigalpa');
 
require_once ('kannel.php');
require_once ('edge.php');

require_once ('./conf/envios_mt.php');
require_once ('./conf/accelerador.php');
require_once ('./conf/cliente.php');

$userdb = 'admin';
$passdb = '8sC3rq2iQqEztsj1';
$namedb = 'CLUBS';
$hostdb = 'rds-compartido.pconnection.net';

//obtenermos la fecha y hora actual
$today = getdate();
$hora = $today['hours'];
$fecha = date("Y-m-d");
$wd = $today['wday'];
$str_day_field = select_week_day($wd);

 //Esto es unicamente para la linea de comando
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


$id_carrier = urldecode($_REQUEST['id_carrier']);


$to_mt;
$smsc_mt;

$conn = mysql_connect($hostdb,$userdb,$passdb);
if($conn)
{
      if(mysql_selectdb($namedb,$conn))
      {
        $sql = "SELECT clubs_SERVICE.id_club,clubs_SERVICE.name_club,clubs_SERVICE.id_carrier,clubs_SERVICE.sc_mo,".
          "clubs_SERVICE.type_send,clubs_SERVICE.enable_points,clubs_ACELERADOR_AUTO.id_broad_conf,clubs_SERVICE.`status`" . 
          " FROM clubs_CARRIER INNER JOIN clubs_SERVICE ON clubs_CARRIER.id_carrier = clubs_SERVICE.id_carrier INNER JOIN clubs_ACELERADOR_AUTO ON clubs_SERVICE.id_club = clubs_ACELERADOR_AUTO.id_clubs".
          " WHERE clubs_CARRIER.id_carrier = $id_carrier and clubs_ACELERADOR_AUTO.hour_session = '$hora' and clubs_SERVICE.`status` = 'enable' and clubs_ACELERADOR_AUTO.last_exe < '$fecha' and clubs_ACELERADOR_AUTO.$str_day_field='1'";
          
          $response = mysql_query($sql,$conn);
          
          $envios = new envios_mt($conn);
          //Recorremos los distintos servicios activos
          while($row_servicio = mysql_fetch_array($response))
          {
              $id_conf = $row_servicio['id_broad_conf'];
              //Actualizamos el registro de los cobros
              $sql = "update clubs_ACELERADOR_AUTO set last_exe = DATE(CONVERT_TZ(curdate(), 'UTC', 'America/Tegucigalpa')) where id_broad_conf = $id_conf";
              mysql_query($sql,$conn);
              
              $id_club = $row_servicio['id_club'];
              
              $accelerador = new acelerador($conn);
              $acce_data = $accelerador->get_acce_data($id_club,$fecha,$hora);
              
              
              if ($acce_data)
              {
                  $accelerador->execute_acce_data($acce_data['id_acce_conf']);
                  $puntos_acc = $acce_data['puntos'] > 0  ? $acce_data['puntos'] : 0;
                  $base_cliente = $acce_data['base_cliente'];
                  if ($base_cliente == 'activos')
                  {
                    $sql = "SELECT clubs_MSISDN.id_msisdn, clubs_MSISDN.msisdn,DATEDIFF(DATE(CONVERT_TZ(curdate(), 'UTC', 'America/Tegucigalpa')),".
                    "case clubs_MSISDN.fecha_baja when 0 then clubs_MSISDN.fecha_alta else clubs_MSISDN.fecha_baja end)".
                    " as dias_alta FROM clubs_MSISDN WHERE clubs_MSISDN.id_clubs = $id_club  AND clubs_MSISDN.state = 'enable' ";
                      
                  }else if ($base_cliente == 'inactivos')
                  {
                    $sql = "SELECT clubs_MSISDN.id_msisdn, clubs_MSISDN.msisdn,DATEDIFF(DATE(CONVERT_TZ(curdate(), 'UTC', 'America/Tegucigalpa')),".
                    "case clubs_MSISDN.fecha_baja when 0 then clubs_MSISDN.fecha_alta else clubs_MSISDN.fecha_baja end)".
                    " as dias_alta FROM clubs_MSISDN WHERE clubs_MSISDN.id_clubs = $id_club  AND clubs_MSISDN.state = 'disable' ";
                      
                  }
                  else
                  {
                      //Toda la base
                      $sql = "SELECT clubs_MSISDN.id_msisdn, clubs_MSISDN.msisdn,DATEDIFF(DATE(CONVERT_TZ(curdate(), 'UTC', 'America/Tegucigalpa')),".
                      "case clubs_MSISDN.fecha_baja when 0 then clubs_MSISDN.fecha_alta else clubs_MSISDN.fecha_baja end)".
                      " as dias_alta FROM clubs_MSISDN WHERE clubs_MSISDN.id_clubs = $id_club";
                  }
                  
                  //seleccionamos los clientes activos del club

                  $clientes = mysql_query($sql,$conn);
                  //Seleccionamos el tipo de envio que se realizara
                  switch($row_servicio['type_send'])
                  {
                    case 'Kannel':
                        $response_free = new kannel($conn,$row_servicio);
                        break;
                    case 'Edge':
                        $response_free = new edge($conn,$row_servicio);
                        $binfo = 'G';
                        if ($acce_data['generic'] > 0)
                            $binfo .= $acce_data['generic'];
                        break;
                    case 'Beconn':
                        $response_free = new kannel($conn,$row_servicio);
                        break;
                    case 'CDC': //No implementado aun
                        break;
                    case 'SACA': //No implementado aun
                        break;
                    
                  }
                  //Si contenido a enviar a los suscritos
                  while(($row_cliente = mysql_fetch_array($clientes)) && ($acce_data['mensaje']))
                  {
                      $id_msisdn = $row_cliente['id_msisdn'];
                      $msisdn = $row_cliente['msisdn'];
                      $cliente_msisdn = new cliente($conn,$msisdn,$id_club);
                      
                      if (($puntos_acc > 0) && ($row_servicio['enable_points'] == 'enable'))
                        $cliente_msisdn->update_question_points($id_msisdn,$puntos_acc,0);

                      if (($row_servicio['type_send'] == 'Beconn') ||($row_servicio['type_send'] == 'Kannel') )
                      {
                          $id_delivery = $response_free->enviar($msisdn,$acce_data['sc_mo'],$acce_data['mensaje'],false);
                      }
                      else if ($row_servicio['type_send'] == 'Edge')
                      {
                          $id_delivery = $response_free->enviar($msisdn,$acce_data['sc_mo'],$acce_data['mensaje'],false,1,$binfo);
                      }
                      
                  }
                  
              }
          }
          
      }
      mysql_close($conn);
}
function select_week_day($wd)
{
    $str_day;
    switch (intval($wd))
    {
        case 0:
            $str_day = "domingo";
            break;
        case 1:
            $str_day = "lunes";
            break;
        case 2:
            $str_day = "martes";
            break;
        case 3:
            $str_day = "miercoles";
            break;
        case 4:
            $str_day = "jueves";
            break;
        case 5:
            $str_day = "viernes";
            break;
        case 6:
            $str_day = "sabado";
            break;
    }
    return $str_day;
    
}
  
?>
