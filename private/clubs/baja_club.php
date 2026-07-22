<?php
//http://localhost/private/clubs/baja_club.php?phone=%p&text=%a&prefix=%k&service=%s&sc=%P&ruta=TIGO_HN&type=%B
error_reporting(E_ERROR | E_WARNING);       
header("Content-type: text/plain");

require_once('./conf/conf.inc');
require_once('kannel.php');
require_once('edge.php');
require_once('cdc.php');

function listaNegraTemporal($msisdn) {

        $con = mysqli_connect('rds-compartido.pconnection.net', 'admin', '8sC3rq2iQqEztsj1', 'lista_negra_wap');

        $query = "INSERT INTO `lista_negra_lista_negra` (`msisdn`, `fecha_ingreso`, `razon_ingreso`, `operadora`) VALUES ('$msisdn', CONVERT_TZ(NOW(), 'UTC', 'America/Tegucigalpa'), 'Lista Negra Temporal', 'Tigo')";
        $result = mysqli_query($con, $query);

        mysqli_close($con);

        return;

    }

$phone = str_replace('+','',rawurldecode($_REQUEST['phone']));
$texto = rawurldecode($_REQUEST['text']);
$prefix =  strtoupper(rawurldecode($_REQUEST['prefix'])); //primer palabra
$service  = strtoupper(rawurldecode($_REQUEST['service'])); //segunda palabra en caso que exista
$ruta =strtoupper(rawurldecode($_REQUEST['ruta']));
$shortcode = str_replace('+','',rawurldecode($_REQUEST['sc']));
$service_type = rawurldecode($_REQUEST['type']); //binfo del kannel

/*
Mensajes de baja, el usuario genera un MO bajo los siguiente escenario
BAJA
o 
BAJA PROMESAS
*/

$conn = mysql_connect($hostdb,$userdb,$passdb);
if ($conn)
{
    if (mysql_selectdb($namedb,$conn))
    {
        $log_user_data = new user_log($conn);
        $id_user_log = $log_user_data->guardar_log_mo($phone,$texto,$shortcode,$ruta);
        $utilidades = new utilidades(); 
        
        if ($service)
        {
            $clubs = new service_conf($conn,$ruta,$shortcode,$service);
            $clubs_data = $clubs->service_data;
            if (($clubs_data) && ($clubs_data['status']=='enable')) 
            {
                $id_clubs = $clubs_data['id_club'];
                $cliente_msisdn = new cliente($conn,$phone,$id_clubs);
                if (!$cliente_msisdn->cliente_registrado)
                {
                    //usuario no esta registrado al club xxxx
                    $msg = $clubs_data['goodbye_not_found'];
                    
                }
                else
                {
                    //Si esta suscrito al club
                    $cliente_data = $cliente_msisdn->cliente_data;
                    if($cliente_data['state'] == 'enable')
                    {
                        $cliente_msisdn->disable_user();
                        $msg = $clubs_data['goodbye_text'];
                        listaNegraTemporal($phone);
                    }else
                    {
                        //Cliente ya estaba de baja
                        $msg = $clubs_data['goodbye_not_found'];
                    }
                }
            }
            
        }
        else
        {
            $sql = "SELECT clubs_MSISDN.id_msisdn,clubs_SERVICE.name_club,clubs_SERVICE.id_club FROM clubs_MSISDN".
            " INNER JOIN clubs_SERVICE ON clubs_SERVICE.id_club = clubs_MSISDN.id_clubs".
            " WHERE clubs_MSISDN.msisdn = '$phone' and clubs_MSISDN.state = 'enable' and".
            " (clubs_SERVICE.sc_mo = '$shortcode' or clubs_SERVICE.sc_mo_adicional = '$shortcode' or".
            " clubs_SERVICE.sc_cobro = '$shortcode' or clubs_SERVICE.sc_cobro1 = '$shortcode') and clubs_SERVICE.status = 'enable'";
            $response = mysql_query($sql,$conn);
            $num_row = mysql_num_rows($response);
            if ($num_row > 0)
            {
                if ($num_row == 1)
                {
                    $response = mysql_fetch_array($response);
                    $cliente_msisdn = new cliente($conn,$phone,$response['id_club']);
                    $clubs_data = $utilidades->get_service_id($conn,$response['id_club']);
                    if (!$cliente_msisdn->cliente_registrado)
                    {
                        //usuario no esta registrado al club xxxx
                        $msg = $clubs_data['goodbye_not_found'];
                        
                    }
                    else
                    {
                        //Si esta suscrito al club
                        $cliente_data = $cliente_msisdn->cliente_data;
                        if($cliente_data['state'] == 'enable')
                        {
                            $cliente_msisdn->disable_user();
                            $msg = $clubs_data['goodbye_text'];
                            listaNegraTemporal($phone);
                        }else
                        {
                            //Cliente ya estaba de baja
                            $msg = $clubs_data['goodbye_not_found'];
                        }
                    }
                    
                }
                else
                {
                    $clubs_name;
                    $prefix;
                    while($row = mysql_fetch_array($response))
                    {
                        $id_club = $row['id_club'];
                        $prefix .= !$prefix ? " id_clubs =$id_club" : " or id_clubs=$id_club";
                        $clubs_name .= !$clubs_name ? $row['name_club']:','.$row['name_club'];
                    }
                    $clubs_data = $utilidades->get_service_id($conn,$id_club);

                    $msg = $clubs_data['goodbye_multiples'] . $clubs_name;
                    if  ((strtoupper(substr($service_type,0,1)) == 'U') && strlen($service_type)==1)
                    {
                        //Esto solo aplica al edge, implica dar de baja de todos los clubes
                        $sql = "update clubs_MSISDN set state = 'disable'  where $prefix";
                        mysql_query($sql,$conn);
                        $msg = "Ok:$clubs_name";
                    }
                        
                        
                    
                }
            }
            else
            {

                $clubs = new service_conf($conn,$ruta,$shortcode,'');
                $clubs_data = $clubs->service_data;
                
                $msg = $clubs_data['goodbye_not_found'];
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
        
        if (strlen($msg) > 1) {
            $id_delivery = $response_free->enviar($phone,$clubs_data['sc_mo'],$msg,false);
            $log_user_data->actualizar_log($id_user_log,$id_delivery);
        }

    }
    mysql_close($conn);
}  
?>
