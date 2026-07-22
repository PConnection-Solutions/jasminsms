<?php
/*
Implementacion del servicio de clubs para Tigo HN utilizando EDGE
http://localhost/private/clubs/default_club.php?phone=%p&text=%a&prefix=%k&service=%s&sc=%P&ruta=TIGO_HN&type=%B
tel claro:50432487992
*/      
//set_time_limit(0);
error_reporting(E_ERROR | E_WARNING);       
header("Content-type: text/plain");
require_once('./conf/conf.inc');
require_once('kannel.php');
require_once ('edgePrioridadAlta.php');
require_once('beconn.php');
require_once('cdc.php');
//Tigo HN 50498935271
//Claro HN 50432487992,50433005255
//Claro NI 50584234578

$phone = str_replace('+','',rawurldecode($_REQUEST['phone']));
$texto = rawurldecode($_REQUEST['text']);
$prefix =  strtoupper(rawurldecode($_REQUEST['prefix']));
$service  = strtoupper(rawurldecode($_REQUEST['service']));
$ruta =strtoupper(rawurldecode($_REQUEST['ruta']));
$shortcode = str_replace('+','',rawurldecode($_REQUEST['sc']));
$service_type = rawurldecode($_REQUEST['type']); //binfo del kannel

#print_r($_REQUEST);

$conn = mysql_connect($hostdb,$userdb,$passdb);
if ($conn)
{
    if (mysql_selectdb($namedb,$conn))
    {
        /*
            Creamos el registro de todos los mensajes MO recibidos
        */
        $log_user_data = new user_log($conn);
        $id_user_log = $log_user_data->guardar_log_mo($phone,$texto,$shortcode,$ruta); 
        
        /*
            Determinamos el servicio que se desea a utilizar
        */
        $clubs = new service_conf($conn,$ruta,$shortcode,$prefix);
        $clubs_data = $clubs->service_data;
        if (($clubs_data) && ($clubs_data['status']=='enable')) 
        {
            $id_clubs = $clubs_data['id_club'];
            /*
                Buscamos, registramos y cargamos informacion del usuario
            */
            $cliente_msisdn = new cliente($conn,$phone,$id_clubs);
            if (!$cliente_msisdn->cliente_registrado)
            {
                $cliente_msisdn->insertar_nuevo_usuario();

                ### WS PARA ADNETWORK ###
                    $str = "http://127.0.0.1/adnetworks/ws_adnetworks_cpa/adnetwork_cpa.php?msisdn=".$phone."&validar=abc33ef2019&club=".$service_type."&corto=".$shortcode;
                    //echo $str;
                    $ch=curl_init();
                    curl_setopt($ch,CURLOPT_URL, $str);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_exec($ch);
                    curl_close($ch);

                    if ($service_type == 'SKULT') {

                        $str = "http://127.0.0.1/tierra_catracha/ws/asignar_estampilla.php?msisdn_destino=" . $phone;
                        //echo $str;
                        $ch=curl_init();
                        curl_setopt($ch,CURLOPT_URL, $str);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_exec($ch);
                        curl_close($ch);

                    }

                    if ($service_type == 'SPROM' && $shortcode == '7786') {

                        $str = "http://127.0.0.1/private/clubs/clubs_primera_suscripcion.php?msisdn=$phone&short_code=$shortcode&service_type=$service_type";
                        //echo $str;
                        $ch=curl_init();
                        curl_setopt($ch,CURLOPT_URL, $str);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_exec($ch);
                        curl_close($ch);

                    }

                    if ($prefix == 'AMOR' && $shortcode == '1185') {

   			#echo "http://localhost/people_games/escapa_de_la_friendzone/ws/guardar_adnetwork_conversion.php?pass=s3cr3t2025&msisdn=$phone&club=AMOR&corto=$shortcode";
			$curl = curl_init();
 
                        curl_setopt_array($curl, array(
                          CURLOPT_URL => 'http://localhost/people_games/escapa_de_la_friendzone/ws/guardar_adnetwork_conversion.php?pass=s3cr3t2025&msisdn='.$phone.'&club=AMOR&corto='.$shortcode,
                          CURLOPT_RETURNTRANSFER => true,
                          CURLOPT_ENCODING => '',
                          CURLOPT_MAXREDIRS => 10,
                          CURLOPT_TIMEOUT => 0,
                          CURLOPT_FOLLOWLOCATION => true,
                          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                          CURLOPT_CUSTOMREQUEST => 'GET',
                        ));
                         
                        $response = curl_exec($curl);
                         
			curl_close($curl);
			#echo $response;

		    }

		    if ($prefix == 'JUEGA' && $shortcode == '7786') {

                        #echo "http://localhost/people_games/escapa_de_la_friendzone/ws/guardar_adnetwork_conversion.php?pass=s3cr3t2025&msisdn=$phone&club=AMOR&corto=$shortcode";
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                          CURLOPT_URL => 'http://localhost/people_games/escapa_de_la_friendzone/ws/guardar_adnetwork_conversion.php?pass=s3cr3t2025&msisdn='.$phone.'&club=AMOR&corto='.$shortcode,
                          CURLOPT_RETURNTRANSFER => true,
                          CURLOPT_ENCODING => '',
                          CURLOPT_MAXREDIRS => 10,
                          CURLOPT_TIMEOUT => 0,
                          CURLOPT_FOLLOWLOCATION => true,
                          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                          CURLOPT_CUSTOMREQUEST => 'GET',
                        ));

                        $response = curl_exec($curl);

                        curl_close($curl);
                        #echo $response;

		    }

		    if ($prefix == 'VIDA' && $shortcode == '7786') {

                        #echo "http://localhost/people_games/escapa_de_la_friendzone/ws/guardar_adnetwork_conversion.php?pass=s3cr3t2025&msisdn=$phone&club=AMOR&corto=$shortcode";
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                          CURLOPT_URL => 'http://localhost/people_games/escapa_de_la_friendzone/ws/guardar_adnetwork_conversion.php?pass=s3cr3t2025&msisdn='.$phone.'&club=AMOR&corto='.$shortcode,
                          CURLOPT_RETURNTRANSFER => true,
                          CURLOPT_ENCODING => '',
                          CURLOPT_MAXREDIRS => 10,
                          CURLOPT_TIMEOUT => 0,
                          CURLOPT_FOLLOWLOCATION => true,
                          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                          CURLOPT_CUSTOMREQUEST => 'GET',
                        ));

                        $response = curl_exec($curl);

                        curl_close($curl);
                        #echo $response;

		    }
		    
		    if ($prefix == 'CONSEJO' && $shortcode == '1185') {

                        #echo "http://localhost/people_games/escapa_de_la_friendzone/ws/guardar_adnetwork_conversion.php?pass=s3cr3t2025&msisdn=$phone&club=AMOR&corto=$shortcode";
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                          CURLOPT_URL => 'http://localhost/servicios_honduras/ws/guardar_adnetwork_conversion.php?pass=s3cr3t2025&msisdn='.$phone.'&club=CONSEJO&corto='.$shortcode,
                          CURLOPT_RETURNTRANSFER => true,
                          CURLOPT_ENCODING => '',
                          CURLOPT_MAXREDIRS => 10,
                          CURLOPT_TIMEOUT => 0,
                          CURLOPT_FOLLOWLOCATION => true,
                          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                          CURLOPT_CUSTOMREQUEST => 'GET',
                        ));

                        $response = curl_exec($curl);

                        curl_close($curl);
                        #echo $response;

		    }

		    if ($prefix == 'JUEGA' && $shortcode == '7784') {

                        #echo "http://localhost/people_games/escapa_de_la_friendzone/ws/guardar_adnetwork_conversion.php?pass=s3cr3t2025&msisdn=$phone&club=AMOR&corto=$shortcode";
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                          CURLOPT_URL => 'http://localhost/servicios_honduras/ws/guardar_adnetwork_conversion.php?pass=s3cr3t2025&msisdn='.$phone.'&club=JUEGA&corto='.$shortcode,
                          CURLOPT_RETURNTRANSFER => true,
                          CURLOPT_ENCODING => '',
                          CURLOPT_MAXREDIRS => 10,
                          CURLOPT_TIMEOUT => 0,
                          CURLOPT_FOLLOWLOCATION => true,
                          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                          CURLOPT_CUSTOMREQUEST => 'GET',
                        ));

                        $response = curl_exec($curl);

                        curl_close($curl);
                        #echo $response;

                    }

            if ($prefix == 'FUTBOL' && $shortcode == '7786') {

                        #echo "http://localhost/people_games/escapa_de_la_friendzone/ws/guardar_adnetwork_conversion.php?pass=s3cr3t2025&msisdn=$phone&club=AMOR&corto=$shortcode";
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                          CURLOPT_URL => 'http://localhost/people_games/futbol4/ws/guardar_adnetwork_conversion.php?pass=s3cr3t2025&msisdn='.$phone.'&club=FUTBOL&corto='.$shortcode,
                          CURLOPT_RETURNTRANSFER => true,
                          CURLOPT_ENCODING => '',
                          CURLOPT_MAXREDIRS => 10,
                          CURLOPT_TIMEOUT => 0,
                          CURLOPT_FOLLOWLOCATION => true,
                          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                          CURLOPT_CUSTOMREQUEST => 'GET',
                        ));

                        $response = curl_exec($curl);

                        curl_close($curl);
                        #echo $response;

                    }

            if ($prefix == 'LUCKY' && $shortcode == '7789') {

                        #echo "http://localhost/people_games/escapa_de_la_friendzone/ws/guardar_adnetwork_conversion.php?pass=s3cr3t2025&msisdn=$phone&club=AMOR&corto=$shortcode";
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                          CURLOPT_URL => 'http://localhost/people_games/decisiones/ws/guardar_adnetwork_conversion.php?pass=s3cr3t2025&msisdn='.$phone.'&club=LUCKY&corto='.$shortcode,
                          CURLOPT_RETURNTRANSFER => true,
                          CURLOPT_ENCODING => '',
                          CURLOPT_MAXREDIRS => 10,
                          CURLOPT_TIMEOUT => 0,
                          CURLOPT_FOLLOWLOCATION => true,
                          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                          CURLOPT_CUSTOMREQUEST => 'GET',
                        ));

                        $response = curl_exec($curl);

                        curl_close($curl);
                        #echo $response;

                    }
                
            }
            $cliente_data = $cliente_msisdn->cliente_data;
            
            $id_msisdn = $cliente_data['id_msisdn'];
            //Activar el usuario
            if ($cliente_data['state']=='disable')
            {
                $cliente_msisdn->enable_user();

                ### WS PARA ADNETWORK ###
                    $str = "http://127.0.0.1/adnetworks/ws_adnetworks_cpa/adnetwork_cpa.php?msisdn=".$phone."&validar=abc33ef2019&club=".$service_type."&corto=".$shortcode;
                    //echo $str;
                    $ch=curl_init();
                    curl_setopt($ch,CURLOPT_URL, $str);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_exec($ch);
                    curl_close($ch);

                if ($prefix == 'AMOR' && $shortcode == '1185') {
                    #echo "http://localhost/people_games/escapa_de_la_friendzone/ws/guardar_adnetwork_conversion.php?pass=s3cr3t2025&msisdn=$phone&club=AMOR&corto=$shortcode";
                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                      CURLOPT_URL => 'http://localhost/people_games/escapa_de_la_friendzone/ws/guardar_adnetwork_conversion.php?pass=s3cr3t2025&msisdn='.$phone.'&club=AMOR&corto='.$shortcode,
                      CURLOPT_RETURNTRANSFER => true,
                      CURLOPT_ENCODING => '',
                      CURLOPT_MAXREDIRS => 10,
                      CURLOPT_TIMEOUT => 0,
                      CURLOPT_FOLLOWLOCATION => true,
                      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                      CURLOPT_CUSTOMREQUEST => 'GET',
                    ));
                     
                    $response = curl_exec($curl);
                     
		    curl_close($curl);
		    #echo $response;

		}

		if ($prefix == 'JUEGA' && $shortcode == '7786') {

                        #echo "http://localhost/people_games/escapa_de_la_friendzone/ws/guardar_adnetwork_conversion.php?pass=s3cr3t2025&msisdn=$phone&club=AMOR&corto=$shortcode";
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                          CURLOPT_URL => 'http://localhost/people_games/escapa_de_la_friendzone/ws/guardar_adnetwork_conversion.php?pass=s3cr3t2025&msisdn='.$phone.'&club=JUEGA&corto='.$shortcode,
                          CURLOPT_RETURNTRANSFER => true,
                          CURLOPT_ENCODING => '',
                          CURLOPT_MAXREDIRS => 10,
                          CURLOPT_TIMEOUT => 0,
                          CURLOPT_FOLLOWLOCATION => true,
                          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                          CURLOPT_CUSTOMREQUEST => 'GET',
                        ));

                        $response = curl_exec($curl);

                        curl_close($curl);
                        #echo $response;

                    }

                    if ($prefix == 'VIDA' && $shortcode == '7786') {

                        #echo "http://localhost/people_games/escapa_de_la_friendzone/ws/guardar_adnetwork_conversion.php?pass=s3cr3t2025&msisdn=$phone&club=AMOR&corto=$shortcode";
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                          CURLOPT_URL => 'http://localhost/people_games/escapa_de_la_friendzone/ws/guardar_adnetwork_conversion.php?pass=s3cr3t2025&msisdn='.$phone.'&club=VIDA&corto='.$shortcode,
                          CURLOPT_RETURNTRANSFER => true,
                          CURLOPT_ENCODING => '',
                          CURLOPT_MAXREDIRS => 10,
                          CURLOPT_TIMEOUT => 0,
                          CURLOPT_FOLLOWLOCATION => true,
                          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                          CURLOPT_CUSTOMREQUEST => 'GET',
                        ));

                        $response = curl_exec($curl);

                        curl_close($curl);
                        #echo $response;

		    }

		    if ($prefix == 'CONSEJO' && $shortcode == '1185') {

                        #echo "http://localhost/people_games/escapa_de_la_friendzone/ws/guardar_adnetwork_conversion.php?pass=s3cr3t2025&msisdn=$phone&club=AMOR&corto=$shortcode";
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                          CURLOPT_URL => 'http://localhost/servicios_honduras/ws/guardar_adnetwork_conversion.php?pass=s3cr3t2025&msisdn='.$phone.'&club=CONSEJO&corto='.$shortcode,
                          CURLOPT_RETURNTRANSFER => true,
                          CURLOPT_ENCODING => '',
                          CURLOPT_MAXREDIRS => 10,
                          CURLOPT_TIMEOUT => 0,
                          CURLOPT_FOLLOWLOCATION => true,
                          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                          CURLOPT_CUSTOMREQUEST => 'GET',
                        ));

                        $response = curl_exec($curl);

                        curl_close($curl);
                        #echo $response;

		    }

		    if ($prefix == 'JUEGA' && $shortcode == '7784') {

                        #echo "http://localhost/people_games/escapa_de_la_friendzone/ws/guardar_adnetwork_conversion.php?pass=s3cr3t2025&msisdn=$phone&club=AMOR&corto=$shortcode";
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                          CURLOPT_URL => 'http://localhost/servicios_honduras/ws/guardar_adnetwork_conversion.php?pass=s3cr3t2025&msisdn='.$phone.'&club=JUEGA&corto='.$shortcode,
                          CURLOPT_RETURNTRANSFER => true,
                          CURLOPT_ENCODING => '',
                          CURLOPT_MAXREDIRS => 10,
                          CURLOPT_TIMEOUT => 0,
                          CURLOPT_FOLLOWLOCATION => true,
                          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                          CURLOPT_CUSTOMREQUEST => 'GET',
                        ));

                        $response = curl_exec($curl);

                        curl_close($curl);
                        #echo $response;

                    }

            if ($prefix == 'FUTBOL' && $shortcode == '7786') {

                        #echo "http://localhost/people_games/futbol4/ws/guardar_adnetwork_conversion.php?pass=s3cr3t2025&msisdn=50496687930&club=FUTBOL&corto=7786";
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                          CURLOPT_URL => 'http://localhost/people_games/futbol4/ws/guardar_adnetwork_conversion.php?pass=s3cr3t2025&msisdn='.$phone.'&club=FUTBOL&corto='.$shortcode,
                          CURLOPT_RETURNTRANSFER => true,
                          CURLOPT_ENCODING => '',
                          CURLOPT_MAXREDIRS => 10,
                          CURLOPT_TIMEOUT => 0,
                          CURLOPT_FOLLOWLOCATION => true,
                          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                          CURLOPT_CUSTOMREQUEST => 'GET',
                        ));

                        $response = curl_exec($curl);

                        curl_close($curl);
                        #echo $response;

                    }

            if ($prefix == 'LUCKY' && $shortcode == '7789') {

                        #echo "http://localhost/people_games/escapa_de_la_friendzone/ws/guardar_adnetwork_conversion.php?pass=s3cr3t2025&msisdn=$phone&club=AMOR&corto=$shortcode";
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                          CURLOPT_URL => 'http://localhost/people_games/decisiones/ws/guardar_adnetwork_conversion.php?pass=s3cr3t2025&msisdn='.$phone.'&club=LUCKY&corto='.$shortcode,
                          CURLOPT_RETURNTRANSFER => true,
                          CURLOPT_ENCODING => '',
                          CURLOPT_MAXREDIRS => 10,
                          CURLOPT_TIMEOUT => 0,
                          CURLOPT_FOLLOWLOCATION => true,
                          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                          CURLOPT_CUSTOMREQUEST => 'GET',
                        ));

                        $response = curl_exec($curl);

                        curl_close($curl);
                        #echo $response;

                    }

            }
            
            /*
                Buscamos el contenido del dia a entregar
            */
            $contenido = new contenido($conn);
            $contenido_data = $contenido->obtener_contenido_bc($id_clubs);
            //$contenido_data = $contenido->obtener_contenido_bc($id_clubs,$clubs_data['auto']);
            
            /*
                Asignamos los puntos del servicio cuando apliquen, unicamente cuando el usuario es nuevo
            */
            $puntos_club = new puntos($conn);
            if (($cliente_msisdn->new_user) && ($clubs_data['enable_points'] == 'enable'))
            {
                $last_id_puntos = $puntos_club->agregar_puntos($id_msisdn,$clubs_data['ptos_inicio'],strtoupper($clubs_data['unidad']).' INICIO');
                //$puntos_club->actualizar_transaccion_id($last_id_puntos,"zvxbxtxt");
                
            }
            if (($cliente_data['next_points'] > 0) && ($cliente_data['id_question'] == 0))
            {
                $last_id_puntos = $puntos_club->agregar_puntos($id_msisdn,$cliente_data['next_points'],strtoupper($clubs_data['unidad']).' ACCE');
                
            }
            /*
                Pendientes de probar todos los tipos de envio
            */
            switch($clubs_data['type_send'])
            {
                case 'Kannel':
                    $response_free = new kannel($conn,$clubs_data);
                    
                    $smsc_free = $response_free->kannel_data['smsc2'];
                    $response_free->url_kannel = str_replace($response_free->kannel_data['smsc'],$smsc_free,$response_free->url_kannel);
                    
                    $response_paid = new kannel($conn,$clubs_data);
                    
                    $smsc_paid = $response_paid->kannel_data['smsc2'];
                    $response_paid->url_kannel = str_replace($response_paid->kannel_data['smsc'],$smsc_paid,$response_paid->url_kannel);
                    break;
                case 'Edge':
                    $response_free = new edge($conn,$clubs_data);
                    
                    $smsc_free = $response_free->kannel_data['smsc2'];
                    $response_free->url_kannel = str_replace($response_free->kannel_data['smsc'],$smsc_free,$response_free->url_kannel);
                    $response_paid = new edge($conn,$clubs_data);
                    
                    $smsc_paid = $response_paid->kannel_data['smsc2'];
                    $response_paid->url_kannel = str_replace($response_paid->kannel_data['smsc'],$smsc_paid,$response_paid->url_kannel); 
                    break;
                case 'Beconn':
                    $response_free = new kannel($conn,$clubs_data);
                    $response_paid = new beconn($conn,$clubs_data);
                    break;
                case 'CDC': //No implementado aun
                    //Tenemos que enviar el transID, asi que lo agregamos al arreglo
                    $clubs_data['transId'] = $service_type ? $service_type:'';
                    $response_free = new cdc($conn,$clubs_data);
                    $response_paid = new cdc($conn,$clubs_data);
                    break;
                case 'SACA': //No implementado aun
                    break;
                
            }
            
            if (($cliente_msisdn->new_user) && ($clubs_data['welcome_text']))
            {
                $response_free->enviar($phone,$clubs_data['sc_mo'],$clubs_data['welcome_text'],false);
                
            }
            if ($clubs_data['info_text'] && (!$service))
            {
                $response_free->enviar($phone,$clubs_data['sc_mo'],$clubs_data['info_text'],false);
                error_log("enviar($phone," . $clubs_data['sc_mo'] . ",".$clubs_data['info_text'].",false);", 3, "/var/log/sms/default_club.log");
                
            }
            if ((!$cliente_msisdn->new_user) && ($clubs_data['info_text_2']) && ($service))
            {
                $response_free->enviar($phone,$clubs_data['sc_mo'],$clubs_data['info_text_2'],false);
                
            }

            
            //Revisar cual es el shortcode MT donde se enviara el mensaje cobrado
            $fecha_hoy = date_create(date("Y-m-d"));
            $fecha_alta = date_create($cliente_data['fecha_alta']);
            $interval = date_diff($fecha_alta, $fecha_hoy);
            if (($interval->d <= $clubs_data['free_days'] ) && ($clubs_data['free_days'] > 0))
            {
                //Usuario en el periodo de gracia, donde los MT son gratis
                $id_delivery = $response_free->enviar($phone,$clubs_data['sc_cobro1'],str_replace('<URL_RULETA_URL_RULETA_>', 'http://pcon.vip/'.$id_msisdn, $contenido_data['content']),true);
            }
            else if(!$service)
            {
                //Se acabo el periodo de gracia enviar el cobro
                //en caso de beconn regresa un arreglo
                $id_delivery = $response_paid->enviar($phone,$clubs_data['sc_cobro'],str_replace('<URL_RULETA_URL_RULETA_>', 'http://pcon.vip/'.$id_msisdn, $contenido_data['content']),true);
                if (is_array($id_delivery))
                {
                    //Esta es una respuesta de beconn viene en un arreglo todo raro
                    $id_array = $response_paid->format_id($id_delivery);
                    //obtenemos el id de beconn que nos regreso la plataforma
                    $id_delivery = $id_array['id_beconn'];
                }
                $envios = new envios_mt($conn);
                $envios->insertar_envios_mt($id_club,$id_msisdn,$phone,$clubs_data['ptos_inicio'],$id_delivery,$contenido_data['id_detalle_service']);
            }
            $log_user_data->actualizar_log($id_user_log,$id_delivery);
            
        }
    }
    mysql_close($conn);
}
?>
