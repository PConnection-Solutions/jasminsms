<?php
//http://localhost/private/clubs/ayuda_club.php?phone=%p&text=%a&prefix=%k&service=%s&sc=%P&ruta=TIGO_HN&type=%B
error_reporting(E_ERROR | E_WARNING);       
header("Content-type: text/plain");

require_once('./conf/conf.inc');
require_once('kannel.php');
require_once('edge.php');
require_once('cdc.php');

$phone = str_replace('+','',rawurldecode($_REQUEST['phone']));
$texto = rawurldecode($_REQUEST['text']);
$prefix =  strtoupper(rawurldecode($_REQUEST['prefix'])); //primer palabra
$service  = strtoupper(rawurldecode($_REQUEST['service'])); //segunda palabra en caso que exista
$ruta =strtoupper(rawurldecode($_REQUEST['ruta']));
$shortcode = str_replace('+','',rawurldecode($_REQUEST['sc']));
$service_type = rawurldecode($_REQUEST['type']); //binfo del kannel

/*
Mensajes de ayuda, el usuario genera un MO bajo los siguiente escenario
AYUDA
o 
AYUDA PROMESAS
*/

$conn = mysql_connect($hostdb,$userdb,$passdb);
if ($conn)
{
    if (mysql_selectdb($namedb,$conn))
    {
        $log_user_data = new user_log($conn);
        $id_user_log = $log_user_data->guardar_log_mo($phone,$texto.':AYUDA',$shortcode,$ruta);
        $utilidades = new utilidades(); 
        
        if ($service)
        {
            $clubs = new service_conf($conn,$ruta,$shortcode,$service);
            $clubs_data = $clubs->service_data;
            if (($clubs_data) && ($clubs_data['status']=='enable')) 
            {
                $id_clubs = $clubs_data['id_club'];
                $msg =  $clubs_data['ayuda_text'];
                $msg2 =  $clubs_data['ayuda_text_2'];

            }
            
        }
        else
        {
            $sql = "SELECT clubs_MSISDN.id_msisdn,clubs_SERVICE.name_club,clubs_SERVICE.id_club FROM clubs_MSISDN".
            " INNER JOIN clubs_SERVICE ON clubs_SERVICE.id_club = clubs_MSISDN.id_clubs".
            " WHERE clubs_MSISDN.msisdn = '$phone' and clubs_MSISDN.state = 'enable' and".
            " (clubs_SERVICE.sc_mo = '$shortcode' or clubs_SERVICE.sc_mo_adicional = '$shortcode' or".
            " clubs_SERVICE.sc_cobro = '$shortcode' or clubs_SERVICE.sc_cobro1 = '$shortcode') and clubs_SERVICE.status = 'enable'";
            //status
            $response = mysql_query($sql,$conn);
            $num_row = mysql_num_rows($response);
            if ($num_row > 0)
            {
                if ($num_row == 1)
                {
                    $response = mysql_fetch_array($response);
                    $clubs_data = $utilidades->get_service_id($conn,$response['id_club']);
                    $msg =  $clubs_data['ayuda_text'];
                    $msg2 =  $clubs_data['ayuda_text_2'];
                }
                else
                {
                    $clubs_name;
                    while($row = mysql_fetch_array($response))
                    {
                        $id_club = $row['id_club'];
                        $clubs_name .= !$clubs_name ? $row['name_club']:','.$row['name_club'];
                    }
                    $clubs_data = $utilidades->get_service_id($conn,$id_club);

                    $msg = $clubs_data['ayuda_multiples'] . $clubs_name;
                    
                }
            }
            else
            {

                $clubs = new service_conf($conn,$ruta,$shortcode,'');
                $clubs_data = $clubs->service_data;
                $msg =  $clubs_data['ayuda_text'];
                $msg2 =  $clubs_data['ayuda_text_2'];
            }
            
        }
        //uno de los casos
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
                $clubs_data['transId'] = $service_type ? $service_type:'';
                $response_free = new cdc($conn,$clubs_data);
                break;
            case 'SACA': //No implementado aun
                break;
            
        }
        if ($msg)
        {
            $id_delivery = $response_free->enviar($phone,$clubs_data['sc_mo'],$msg,false);
            $log_user_data->actualizar_log($id_user_log,$id_delivery);
        }
        if ($msg2)
        {
            $id_delivery = $response_free->enviar($phone,$clubs_data['sc_mo'],$msg2,false);
            $log_user_data->actualizar_log($id_user_log,$id_delivery);
        }
        
        
        
    }
    mysql_close($conn);
}  
?>
