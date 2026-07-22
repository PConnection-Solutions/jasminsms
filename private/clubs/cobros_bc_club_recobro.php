<?php

/*
    Modulo de cobros , obtiene como parametros
    el id_carrier y tipo que indica si son cobros 
*/
set_time_limit(0);
chdir(dirname(__FILE__));
header ("Content-type: text/plain");
//error_reporting(E_ALL ^ E_NOTICE);
date_default_timezone_set('America/Tegucigalpa');
 
require_once ('kannel.php');
require_once ('edge.php');
require_once ('beconn.php');
require_once('cdc.php');
require_once ('./conf/contenido.php');
require_once ('./conf/envios_mt.php');
require_once('./conf/cdc_data.php');

$userdb = 'admin';
$passdb = '8sC3rq2iQqEztsj1';
$namedb = 'CLUBS';
$hostdb = 'rds-compartido.pconnection.net';
//$hostdb = '184.107.61.32';

//obtenermos la fecha y hora actual
$today = getdate();
$hora = $today['hours'];
$fecha = date("Y-m-d");
$wd = $today['wday'];
$str_day_field = select_week_day($wd);

 //Esto es unicamente para la linea de comando
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
        $sql = "SELECT clubs_SERVICE.id_club,clubs_SERVICE.name_club,clubs_SERVICE.id_carrier,clubs_SERVICE.route,".
          "clubs_SERVICE.sc_mo,clubs_SERVICE.sc_mo_adicional,clubs_SERVICE.sc_cobro,clubs_SERVICE.sc_cobro1,clubs_SERVICE.fecha,clubs_SERVICE.welcome_text,".
          "clubs_SERVICE.goodbye_text,clubs_SERVICE.goodbye_not_found,clubs_SERVICE.goodbye_multiples,clubs_SERVICE.info_text,clubs_SERVICE.info_text_2,".
          "clubs_SERVICE.ayuda_text,clubs_SERVICE.ayuda_text_2,clubs_SERVICE.ayuda_multiples,clubs_SERVICE.type_send,clubs_SERVICE.ptos_inicio,clubs_SERVICE.unidad,clubs_SERVICE.auto,".
          "clubs_SERVICE.enable_points,clubs_SERVICE.free_days,clubs_SERVICE.days_to_disable,clubs_SERVICE.cobros_diarios,clubs_COBRO_AUTO.id_broad_conf,clubs_SERVICE.`status`,clubs_SERVICE.sufijo_text" . 
          " FROM clubs_CARRIER INNER JOIN clubs_SERVICE ON clubs_CARRIER.id_carrier = clubs_SERVICE.id_carrier INNER JOIN clubs_COBRO_AUTO ON clubs_SERVICE.id_club = clubs_COBRO_AUTO.id_clubs".
          " WHERE clubs_CARRIER.id_carrier = $id_carrier and clubs_COBRO_AUTO.hour_session = '8' and clubs_SERVICE.`status` = 'enable' and clubs_COBRO_AUTO.last_exe <= '$fecha' and clubs_COBRO_AUTO.$str_day_field='1' and clubs_COBRO_AUTO.status = 'enable' ORDER BY clubs_COBRO_AUTO.id_clubs ASC";


  echo $sql . "\n";
          $response = mysql_query($sql,$conn);
          $contenido = new contenido($conn);
          $envios = new envios_mt($conn);
          //Recorremos los distintos servicios activos
          while($row_servicio = mysql_fetch_array($response))
          {
              $id_conf = $row_servicio['id_broad_conf'];
              //Actualizamos el registro de los cobros
              $sql = "update clubs_COBRO_AUTO set last_exe = DATE(CONVERT_TZ(curdate(), 'UTC', 'America/Tegucigalpa')) where id_broad_conf = $id_conf";
    echo $sql . "\n";
              mysql_query($sql,$conn);
              
              $id_club = $row_servicio['id_club'];
              $contenido_data = $contenido->obtener_contenido_bc($id_club,$row_servicio['auto']);
              $puntos_bc = $row_servicio['enable_points'] == 'enable' ? $row_servicio['ptos_inicio'] : 0;
              $sufijo = $row_servicio['sufijo_text'] ? ':'. $row_servicio['sufijo_text'] : ''; 
              
              if (strlen($contenido_data['content'].$sufijo) > 160)
              {
                  $contenido_text = $contenido_data['content'];
              }
              else
              {
                  $contenido_text = $contenido_data['content'].$sufijo;
              }
              
              //seleccionamos los clientes activos del club
              $sql = "SELECT clubs_MSISDN.id_msisdn, clubs_MSISDN.msisdn,DATEDIFF(DATE(CONVERT_TZ(curdate(), 'UTC', 'America/Tegucigalpa')),".
              "case clubs_MSISDN.fecha_baja when 0 then clubs_MSISDN.fecha_alta else clubs_MSISDN.fecha_baja end)".
              " as dias_alta FROM clubs_MSISDN WHERE clubs_MSISDN.id_clubs = $id_club  AND clubs_MSISDN.state = 'enable' AND clubs_MSISDN.msisdn IN (SELECT clubs_ENVIOS_DIARIOS.msisdn FROM clubs_ENVIOS_DIARIOS WHERE clubs_ENVIOS_DIARIOS.id_clubs = $id_club  AND eventos_cobrados = 0) ORDER BY clubs_MSISDN.points DESC";
    echo $sql . "\n";
	/*if($id_club == 102){
		exit;
	}*/
              $clientes = mysql_query($sql,$conn);
              //Seleccionamos el tipo de envio que se realizara
              switch($row_servicio['type_send'])
              {
                case 'Kannel':
                    $response_free = new kannel($conn,$row_servicio);
                    $response_paid = new kannel($conn,$row_servicio);
                    break;
                case 'Edge':
                    $response_free = new edge($conn,$row_servicio);
                    $response_paid = new edge($conn,$row_servicio); 
                    break;
                case 'Beconn':
                    $response_free = new kannel($conn,$row_servicio);
                    $response_paid = new beconn($conn,$row_servicio);
                    break;
                case 'CDC': //No implementado aun
                    $response_free = new cdc($conn,$row_servicio);
                    $response_paid = new cdc($conn,$row_servicio);
                    $id_delivery = $response_paid->id_envio();
                    break;
                case 'SACA': //No implementado aun
                    break;
                
              }
              //Si existe contenido a enviar, enviarselo a los suscritos
              while(($row_cliente = mysql_fetch_array($clientes)) && ($contenido_data))
              {
                  $id_msisdn = $row_cliente['id_msisdn'];
                  $msisdn = $row_cliente['msisdn'];
                  $dias_alta = $row_cliente['dias_alta'];
                  if ($dias_alta < $row_servicio['free_days'] )
                  {
                      //Gratis
                      $id_delivery = $response_free->enviar($msisdn,$row_servicio['sc_mo'],$contenido_text,false,1,'G');
                      //no estoy seguro que se pueda implementar esto en el CDC
                      
                  }
                  else
                  {
                      //Pagado, revisar si es beconn
                      if ($row_servicio['type_send'] == 'Beconn')
                      {
                          //Agregamos los numeros a enviar
                          $id_array = $response_paid->enviar_lista($msisdn,$row_servicio['sc_cobro'],$contenido_text,true);
                          //Revisamos el arreglo
                          $data_id = $response_paid->format_id($id_array);
                          if ($data_id != false)
                          {
                            $data_id['id_beconn'];
                            $id_delivery = $data_id['id_custom'];
                          }
                      }
                      else if ($row_servicio['type_send'] == 'Edge')
                      {
                          $id_delivery = $response_paid->enviar($msisdn,$row_servicio['sc_cobro'],$contenido_text,true);
                      }
                      else if ($row_servicio['type_send'] == 'Kannel')
                      {
                          $id_delivery = $response_paid->enviar($msisdn,$row_servicio['sc_cobro'],$contenido_text,true);
                      }
                      else
                      {
                          //Este es el CDC, no hacemos nada hasta el final del ciclo
                          $id_delivery;
                      }
                  }
                  //Insertamos los registros enviados
                  $envios->insertar_envios_mt($id_club,$id_msisdn,$msisdn,$puntos_bc,$id_delivery,$contenido_data['id_detalle_service']);
          
                  //Guardar el contenido enviado.....
              }
              //Revisar si el bconnected tiene numeros encolados, si estan encolados
              //forzar el envio y enviarlo
              if ($row_servicio['type_send'] == 'Beconn')
              {
                $id_array = $response_paid->forzar_envio_lista();
                /*
                $data_id = $response_paid->format_id($id_array);
                if ($data_id != false)
                {
                    $data_id['id_beconn']; //Despues de forzar envio siempre id_beconn estara con datos (si conexion efectiva) 
                    $id_delivery = $data_id['id_custom']; //id_custom estara con datos
                }
                */
              } else if ($row_servicio['type_send'] == 'CDC')
              {
                  $response_paid->enviar_bc($row_servicio['sc_cobro'],$contenido_text,$id_delivery);
                  
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
