<?php
//http://localhost/private/clubs/puntos_club.php?phone=%p&text=%a&prefix=%k&service=%s&sc=%P&ruta=TIGO_HN&type=%B
error_reporting(E_ERROR | E_WARNING);       
header("Content-type: text/plain");

require_once('./conf/conf.inc');
require_once('kannel.php');
require_once('edge.php');
/*
Script devuelve la cantidad de puntos acumulados por cada cliente
*/

$phone = str_replace('+','',rawurldecode($_REQUEST['phone']));
$texto = rawurldecode($_REQUEST['text']);
$prefix =  strtoupper(rawurldecode($_REQUEST['prefix'])); //primer palabra
$service  = strtoupper(rawurldecode($_REQUEST['service'])); //segunda palabra en caso que exista
$ruta =strtoupper(rawurldecode($_REQUEST['ruta']));
$shortcode = str_replace('+','',rawurldecode($_REQUEST['sc']));
$service_type = rawurldecode($_REQUEST['type']); //binfo del kannel

/*
Este es el modulo de las las consultas de los puntos, millas etc.
*/

$conn = mysql_connect($hostdb,$userdb,$passdb);
if ($conn)
{
    if (mysql_selectdb($namedb,$conn))
    {
        $log_user_data = new user_log($conn);
        $id_user_log = $log_user_data->guardar_log_mo($phone,$texto,$shortcode,$ruta);
        
        $service = !$service ? '' : $service;
        
        //$clubs = new service_conf($conn,$ruta,$shortcode,'');
        $clubs = new service_conf($conn,$ruta,$shortcode,$service);
        $clubs_data = $clubs->service_data;
        $unidades = $clubs_data['unidad'];
        
        if (($clubs_data) && ($clubs_data['status']=='enable') && ($clubs_data['enable_points']=='enable')) 
        {
            $id_clubs = $clubs_data['id_club'];
            
            $cliente_msisdn = new cliente($conn,$phone,$id_clubs);
            if (!$cliente_msisdn->cliente_registrado)
            {
                $msg = $clubs_data['goodbye_not_found'];
                
            }
            else
            {
                $cliente_data = $cliente_msisdn->cliente_data;
                $msg = $cliente_data['points']. ' '. $unidades;  
            }
            switch($clubs_data['type_send'])
            {
                case 'Kannel':
                    $response_free = new kannel($conn,$clubs_data);
                    break;
                case 'Edge':
                    $response_free = new edge($conn,$clubs_data);
                    break;
                case 'Beconn':
                    $response_free = new kannel($conn,$clubs_data);
                    break;
                case 'CDC': //No implementado aun
                    break;
                case 'SACA': //No implementado aun
                    break;
                
            }
            if ($msg)
            {
                $id_delivery = $response_free->enviar($phone,$clubs_data['sc_mo'],$msg,false,1,'R');
                $log_user_data->actualizar_log($id_user_log,$id_delivery);
            }


        }

    }
    mysql_close($conn);
}  
?>
