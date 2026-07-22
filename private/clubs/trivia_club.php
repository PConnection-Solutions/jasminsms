<?php
//http://localhost/private/clubs/trivia_club.php?phone=%p&text=%a&prefix=%k&service=%s&sc=%P&ruta=TIGO_HN&type=%B
error_reporting(E_ERROR | E_WARNING);       
header("Content-type: text/plain");

define('MAX_POINTS',10000);

require_once('./conf/conf.inc');
require_once('kannel.php');
require_once('edge.php');
require_once('cdc.php');
/*
Script que maneja las trivas, escencialmente utiliza el campo sc_mo_adicional de la tabla clubs_SERVICE
para determinal a que club pertenece, el numero corto asignado a este campo NO DEBE DE SER COMPARTIDO EN OTROS CLUBES,
DEBE SER UNICO POR CADA CLUB
*/

$phone = str_replace('+','',rawurldecode($_REQUEST['phone']));
$texto = rawurldecode($_REQUEST['text']);
$prefix =  strtoupper(rawurldecode($_REQUEST['prefix'])); //primer palabra
$service  = strtoupper(rawurldecode($_REQUEST['service'])); //segunda palabra en caso que exista
$ruta =strtoupper(rawurldecode($_REQUEST['ruta']));
$shortcode = str_replace('+','',rawurldecode($_REQUEST['sc']));
$service_type = rawurldecode($_REQUEST['type']); //binfo del kannel

/*
Este es el modulo de las trivias, esto hace que los clientes generen puntos, millas etc.

*/

$conn = mysql_connect($hostdb,$userdb,$passdb);
if ($conn)
{
    if (mysql_selectdb($namedb,$conn))
    {
        $log_user_data = new user_log($conn);
        $id_user_log = $log_user_data->guardar_log_mo($phone,$texto,$shortcode,$ruta);
        //$utilidades = new utilidades(); 
        
        $clubs = new service_conf($conn,$ruta,$shortcode,'');
        $clubs_data = $clubs->service_data;
        $unidades = $clubs_data['unidad'];
        
        if (($clubs_data) && ($clubs_data['status']=='enable') && ($clubs_data['enable_points']=='enable')) 
        {
            $id_clubs = $clubs_data['id_club'];
            
            $cliente_msisdn = new cliente($conn,$phone,$id_clubs);
            if (!$cliente_msisdn->cliente_registrado)
            {
                $cliente_msisdn->insertar_nuevo_usuario();
                
            }
            $cliente_data = $cliente_msisdn->cliente_data;
            
            $id_msisdn = $cliente_data['id_msisdn'];
            
            $trivia_data = new trivia($conn,$id_clubs);
            
            $puntos_club = new puntos($conn);
            
            if ($cliente_data['id_question'] > 0)
            {
                //Preguntas pendientes de contestar
                $trivia_resultado = $trivia_data->check_answer($prefix,$cliente_data['id_question']);
                $prefijo_msg = $trivia_data->random_prefijo($trivia_resultado);
                $next_question = $trivia_data->return_random_question();
                //Obtenemos los puntos para la siguiente pregunta y verificamos si gano la totalidad
                //de puntos o solo una parte
                
                $puntos_ganados = $trivia_resultado ? ceil($cliente_data['next_points']) : ceil(log10($cliente_data['next_points']));
                //Actualizamos los valores de los puntos pendientes 
                $puntos_club->agregar_puntos($id_msisdn,$puntos_ganados,strtoupper($unidades).':TRIVIA');
                
                //$puntos_proximos = $clubs_data['ptos_inicio']*$cliente_data['next_points'];
                $puntos_proximos = $clubs_data['ptos_inicio']*$puntos_ganados;
                if ($puntos_proximos > MAX_POINTS)
                {
                    $puntos_proximos = MAX_POINTS;
                }
                
                //Proxima pregunta y proximos puntos a asignar
                $cliente_msisdn->update_question_points($id_msisdn,$puntos_proximos,$next_question['id_trivia']);
                
                $msg = "$prefijo_msg x $puntos_proximos $unidades+." . $next_question['question'];
                
            }
            else
            {
                //No hay preguntas pendientes de contestar
                //Obtenemos la pregunta que realizaremos
                $next_question = $trivia_data->return_random_question();
                
                //Actualizamos los valores de los puntos pendientes 
                $puntos_club->agregar_puntos($id_msisdn,$cliente_data['next_points'],strtoupper($unidades).':TRIVIA');
                
                $puntos_proximos = $cliente_data['next_points'] != 0 ? $clubs_data['ptos_inicio']*$cliente_data['next_points'] : $clubs_data['ptos_inicio'];
                
                if ($puntos_proximos > MAX_POINTS)
                {
                    $puntos_proximos = MAX_POINTS;
                }
                //Proxima pregunta y proximos puntos a asignar
                $cliente_msisdn->update_question_points($id_msisdn,$puntos_proximos,$next_question['id_trivia']);
                $msg = "x $puntos_proximos $unidades+." . $next_question['question'];
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
                    $clubs_data['transId'] = $service_type ? $service_type:'';
                    $response_free = new cdc($conn,$clubs_data);
                    break;
                case 'SACA': //No implementado aun
                    break;
                
            }
            if ($msg)
            {
                $id_delivery = $response_free->enviar($phone,$clubs_data['sc_mo'],$msg,true,1,'R');
                $log_user_data->actualizar_log($id_user_log,$id_delivery);
            }

        }

    }
    mysql_close($conn);
}  
?>
