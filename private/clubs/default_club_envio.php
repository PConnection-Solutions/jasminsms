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
require_once('edge.php');
require_once('beconn.php');
require_once('cdc.php');
//Tigo HN 50498935271
//Claro HN 50432487992

$phone = str_replace('+','',rawurldecode($_REQUEST['phone']));
$texto = rawurldecode($_REQUEST['text']);
$prefix =  strtoupper(rawurldecode($_REQUEST['prefix']));
$service  = strtoupper(rawurldecode($_REQUEST['service']));
$ruta =strtoupper(rawurldecode($_REQUEST['ruta']));
$shortcode = str_replace('+','',rawurldecode($_REQUEST['sc']));
$service_type = rawurldecode($_REQUEST['type']); //binfo del kannel

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
	/*if($shortcode == '2589')
	{
	    $url = "http://localhost/cron/sv/CDC/capturar_mo_cdc.php";
	    $valores = "msisdn=".$phone."&mensaje=".$texto."&sc=".$shortcode."&ruta=".$ruta."&transid=".$service_type;
	    $handler = curl_init();
	    curl_setopt($handler, CURLOPT_URL, $url);
	    curl_setopt($handler, CURLOPT_POST,true);
	    curl_setopt($handler, CURLOPT_POSTFIELDS, $valores);
	    $response = curl_exec ($handler);
	    curl_close($handler);
            $log_user_data->mos($phone,$texto,$shortcode,$ruta,$service_type);    
	}*/
	if($shortcode == '2589')
        {
            $texto2 = str_replace('-CDC_submitSMS','',$texto);
            if(strtoupper($texto2) == 'VIDAS' or strtoupper($texto2) == 'VIDA'){
                $url = "http://localhost/comunout/desafio/CDC/sms_ondemandsv.php";
                            
                $valores = "origen=".$phone."&mensaje=".$texto2."&destino=".$shortcode."&ruta=".$ruta."&transid=".$service_type;
                $handler = curl_init();
                curl_setopt($handler, CURLOPT_URL, $url);
                curl_setopt($handler, CURLOPT_POST,true);
                curl_setopt($handler, CURLOPT_POSTFIELDS, $valores);
                $response = curl_exec ($handler);
                curl_close($handler);
                $log_user_data->mos($phone,$texto2,$shortcode,$ruta,$service_type);
            }else{
                $url = "http://localhost/comunout/desafio/incripcion_juegasv_trivia.php";
    			$texto2 = str_replace('-CDC_submitSMS','',$texto);
                $valores = "origen=".$phone."&mensaje=".$texto2."&destino=".$shortcode."&ruta=".$ruta."&transid=".$service_type;
                $handler = curl_init();
                curl_setopt($handler, CURLOPT_URL, $url);
                curl_setopt($handler, CURLOPT_POST,true);
                curl_setopt($handler, CURLOPT_POSTFIELDS, $valores);
                $response = curl_exec ($handler);
                curl_close($handler);
                $log_user_data->mos($phone,$texto2,$shortcode,$ruta,$service_type);
            }
        }
	if($shortcode == '7788')
        {
            $url = "http://localhost/comunout/desafio/incripcion_juegasv.php";
			$texto2 = str_replace('-CDC_submitSMS','',$texto);
            $valores = "origen=".$phone."&mensaje=".$texto2."&destino=".$shortcode."&ruta=".$ruta."&transid=".$service_type;
            $handler = curl_init();
            curl_setopt($handler, CURLOPT_URL, $url);
            curl_setopt($handler, CURLOPT_POST,true);
            curl_setopt($handler, CURLOPT_POSTFIELDS, $valores);
            $response = curl_exec ($handler);
            curl_close($handler);
            $log_user_data->mos($phone,$texto,$shortcode,$ruta,$service_type);
        }
	if($shortcode == '8611' && $ruta == 'CLARO_NI')
        {
            $url = "http://localhost/comunout/desafio/incripcion_juegani.php";
			$texto2 = str_replace('-CDC_submitSMS','',$texto);
            $valores = "origen=".$phone."&mensaje=".$texto2."&destino=".$shortcode."&ruta=".$ruta."&transid=".$service_type;
            $handler = curl_init();
            curl_setopt($handler, CURLOPT_URL, $url);
            curl_setopt($handler, CURLOPT_POST,true);
            curl_setopt($handler, CURLOPT_POSTFIELDS, $valores);
            $response = curl_exec ($handler);
            curl_close($handler);
            $log_user_data->mos($phone,$texto,$shortcode,$ruta,$service_type);
        }
		if($shortcode == '8555' && $ruta == 'CLARO_NI')
        {
            $texto2 = str_replace('-CDC_submitSMS','',$texto);
            if(strtoupper($texto2) == 'VIDAS' or strtoupper($texto2) == 'VIDA'){
                $url = "http://localhost/comunout/desafio/CDC/sms_ondemandni.php";
                            
                $valores = "origen=".$phone."&mensaje=".$texto2."&destino=".$shortcode."&ruta=".$ruta."&transid=".$service_type;
                $handler = curl_init();
                curl_setopt($handler, CURLOPT_URL, $url);
                curl_setopt($handler, CURLOPT_POST,true);
                curl_setopt($handler, CURLOPT_POSTFIELDS, $valores);
                $response = curl_exec ($handler);
                curl_close($handler);
                $log_user_data->mos($phone,$texto2,$shortcode,$ruta,$service_type);

            }elseif(strtoupper($texto2) == 'MAS VIDAS' or strtoupper($texto2) == 'MAS VIDA'){

                $url = "http://localhost/solterosvip/wscritianosVidas.php";
                            
                $valores = "origen=".$phone."&mensaje=".$texto2."&destino=".$shortcode."&ruta=".$ruta."&transid=".$service_type;
                $handler = curl_init();
                curl_setopt($handler, CURLOPT_URL, $url);
                curl_setopt($handler, CURLOPT_POST,true);
                curl_setopt($handler, CURLOPT_POSTFIELDS, $valores);
                $response = curl_exec ($handler);
                curl_close($handler);
                $log_user_data->mos($phone,$texto2,$shortcode,$ruta,$service_type);

            }else{
                $url = "http://localhost/comunout/desafio/incripcion_juegani_trivia.php";
    			$texto2 = str_replace('-CDC_submitSMS','',$texto);
                $valores = "origen=".$phone."&mensaje=".$texto2."&destino=".$shortcode."&ruta=".$ruta."&transid=".$service_type;
                $handler = curl_init();
                curl_setopt($handler, CURLOPT_URL, $url);
                curl_setopt($handler, CURLOPT_POST,true);
                curl_setopt($handler, CURLOPT_POSTFIELDS, $valores);
                $response = curl_exec ($handler);
                curl_close($handler);
                $log_user_data->mos($phone,$texto2,$shortcode,$ruta,$service_type);
            }
        }
	
		if($shortcode == '8555' && $ruta == 'CLARO_HN'  && $prefix <> 'INFO')
        {
            $texto2 = str_replace('-CDC_submitSMS','',$texto);
            if(strtoupper($texto2) == 'VIDAS' or strtoupper($texto2) == 'VIDA'){
                $url = "http://localhost/comunout/desafio/CDC/sms_ondemand.php";
                            
                $valores = "origen=".$phone."&mensaje=".$texto2."&destino=".$shortcode."&ruta=".$ruta."&transid=".$service_type;
                $handler = curl_init();
                curl_setopt($handler, CURLOPT_URL, $url);
                curl_setopt($handler, CURLOPT_POST,true);
                curl_setopt($handler, CURLOPT_POSTFIELDS, $valores);
                $response = curl_exec ($handler);
                curl_close($handler);
                $log_user_data->mos($phone,$texto2,$shortcode,$ruta,$service_type);
            }elseif(strtoupper($texto2) == 'MAS VIDAS' or strtoupper($texto2) == 'MAS VIDA'){

                $url = "http://localhost/cristianovip/wscritianosVidas.php";
                            
                $valores = "origen=".$phone."&mensaje=".$texto2."&destino=".$shortcode."&ruta=".$ruta."&transid=".$service_type;
                $handler = curl_init();
                curl_setopt($handler, CURLOPT_URL, $url);
                curl_setopt($handler, CURLOPT_POST,true);
                curl_setopt($handler, CURLOPT_POSTFIELDS, $valores);
                $response = curl_exec ($handler);
                curl_close($handler);
                $log_user_data->mos($phone,$texto2,$shortcode,$ruta,$service_type);


            }else{
                /*$url = "http://localhost/comunout/desafio/incripcion_juegahn_trivia.php";
                            
                $valores = "origen=".$phone."&mensaje=".$texto2."&destino=".$shortcode."&ruta=".$ruta."&transid=".$service_type;
                $handler = curl_init();
                curl_setopt($handler, CURLOPT_URL, $url);
                curl_setopt($handler, CURLOPT_POST,true);
                curl_setopt($handler, CURLOPT_POSTFIELDS, $valores);
                $response = curl_exec ($handler);
                curl_close($handler);
                $log_user_data->mos($phone,$texto2,$shortcode,$ruta,$service_type);*/
                $texto2 = str_replace('-CDC_submitSMS','',$texto);
                $str = 'http://localhost/sms/sms_ondemand.php?origen=' . $phone . '&destino=' .$shortcode. '&mensaje=' .$texto2.'&transid='.$service_type;
                    $ch=curl_init();
                    curl_setopt($ch,CURLOPT_URL, $str);
                    curl_exec($ch);
                    curl_close($ch);   
                }
        }

        ### PROMO FICOHSA ###
        if($shortcode == '7789' && $ruta == 'CLARO_HN') {

            $texto2 = str_replace('-CDC_submitSMS','',$texto);
            $url = "http://localhost/ficohsa3/SMS/codigo.php";

            if (substr_count($texto2, ' ') == 1) {

                $valores = "msisdn=".$phone."&mensaje=".urlencode($texto2)."&sc=".$shortcode."&red=".$ruta."&transid=".$service_type;
                $handler = curl_init();
                curl_setopt($handler, CURLOPT_URL, $url);
                curl_setopt($handler, CURLOPT_POST,true);
                curl_setopt($handler, CURLOPT_POSTFIELDS, $valores);
                $response = curl_exec ($handler);
                curl_close($handler);
                $log_user_data->mos($phone,$texto,$shortcode,$ruta,$service_type);
                error_log("$url . $valores\n", 3, "/opt/lampp/htdocs/ficohsa3/SMS/log.log");
                
            }

        }

		if($shortcode == '7789' && $ruta == 'CLARO_HN' && $prefix == 'A')
        {
            $url = "http://localhost/comunout/desafio/incripcion_juegahn.php";
			$texto2 = str_replace('-CDC_submitSMS','',$texto);
            $valores = "origen=".$phone."&mensaje=".$texto2."&destino=".$shortcode."&ruta=".$ruta."&transid=".$service_type;
            $handler = curl_init();
            curl_setopt($handler, CURLOPT_URL, $url);
            curl_setopt($handler, CURLOPT_POST,true);
            curl_setopt($handler, CURLOPT_POSTFIELDS, $valores);
            $response = curl_exec ($handler);
            curl_close($handler);
            $log_user_data->mos($phone,$texto,$shortcode,$ruta,$service_type);
        }
		if($shortcode == '7789' && $ruta == 'CLARO_HN' && $prefix == 'B')
        {
            $url = "http://localhost/comunout/desafio/incripcion_juegahn.php";
                        $texto2 = str_replace('-CDC_submitSMS','',$texto);
            $valores = "origen=".$phone."&mensaje=".$texto2."&destino=".$shortcode."&ruta=".$ruta."&transid=".$service_type;
            $handler = curl_init();
            curl_setopt($handler, CURLOPT_URL, $url);
            curl_setopt($handler, CURLOPT_POST,true);
            curl_setopt($handler, CURLOPT_POSTFIELDS, $valores);
            $response = curl_exec ($handler);
            curl_close($handler);
            $log_user_data->mos($phone,$texto,$shortcode,$ruta,$service_type);
        }
		if($shortcode == '7789' && $ruta == 'CLARO_HN' && $prefix == 'SI')
        {
            $url = "http://localhost/comunout/desafio/incripcion_juegahn.php";
                        $texto2 = str_replace('-CDC_submitSMS','',$texto);
            $valores = "origen=".$phone."&mensaje=".$texto2."&destino=".$shortcode."&ruta=".$ruta."&transid=".$service_type;
            $handler = curl_init();
            curl_setopt($handler, CURLOPT_URL, $url);
            curl_setopt($handler, CURLOPT_POST,true);
            curl_setopt($handler, CURLOPT_POSTFIELDS, $valores);
            $response = curl_exec ($handler);
            curl_close($handler);
            $log_user_data->mos($phone,$texto,$shortcode,$ruta,$service_type);
        }
		if($shortcode == '7789' && $ruta == 'CLARO_HN' && $prefix == 'NO')
        {
            $url = "http://localhost/comunout/desafio/incripcion_juegahn.php";
                        $texto2 = str_replace('-CDC_submitSMS','',$texto);
            $valores = "origen=".$phone."&mensaje=".$texto2."&destino=".$shortcode."&ruta=".$ruta."&transid=".$service_type;
            $handler = curl_init();
            curl_setopt($handler, CURLOPT_URL, $url);
            curl_setopt($handler, CURLOPT_POST,true);
            curl_setopt($handler, CURLOPT_POSTFIELDS, $valores);
            $response = curl_exec ($handler);
            curl_close($handler);
            $log_user_data->mos($phone,$texto,$shortcode,$ruta,$service_type);
        }

	if($shortcode == '7789' && $ruta == 'CLARO_HN' && $prefix == 'INFO')
        {
            $url = "http://localhost/comunout/desafio/incripcion_juegahn.php";
                        $texto2 = str_replace('-CDC_submitSMS','',$texto);
            $valores = "origen=".$phone."&mensaje=".$texto2."&destino=".$shortcode."&ruta=".$ruta."&transid=".$service_type;
            $handler = curl_init();
            curl_setopt($handler, CURLOPT_URL, $url);
            curl_setopt($handler, CURLOPT_POST,true);
            curl_setopt($handler, CURLOPT_POSTFIELDS, $valores);
            $response = curl_exec ($handler);
            curl_close($handler);
            $log_user_data->mos($phone,$texto,$shortcode,$ruta,$service_type);
        }

	if($shortcode == '7789' && $ruta == 'CLARO_HN' && $prefix == 'AYUDA')
        {
            $url = "http://localhost/comunout/desafio/incripcion_juegahn.php";
                        $texto2 = str_replace('-CDC_submitSMS','',$texto);
            $valores = "origen=".$phone."&mensaje=".$texto2."&destino=".$shortcode."&ruta=".$ruta."&transid=".$service_type;
            $handler = curl_init();
            curl_setopt($handler, CURLOPT_URL, $url);
            curl_setopt($handler, CURLOPT_POST,true);
            curl_setopt($handler, CURLOPT_POSTFIELDS, $valores);
            $response = curl_exec ($handler);
            curl_close($handler);
            $log_user_data->mos($phone,$texto,$shortcode,$ruta,$service_type);
        }

	if($shortcode == '7789' && $ruta == 'CLARO_HN' && $prefix == 'PUNTOS')
        {
            $url = "http://localhost/comunout/desafio/incripcion_juegahn.php";
                        $texto2 = str_replace('-CDC_submitSMS','',$texto);
            $valores = "origen=".$phone."&mensaje=".$texto2."&destino=".$shortcode."&ruta=".$ruta."&transid=".$service_type;
            $handler = curl_init();
            curl_setopt($handler, CURLOPT_URL, $url);
            curl_setopt($handler, CURLOPT_POST,true);
            curl_setopt($handler, CURLOPT_POSTFIELDS, $valores);
            $response = curl_exec ($handler);
            curl_close($handler);
            $log_user_data->mos($phone,$texto,$shortcode,$ruta,$service_type);
        }

        if($shortcode == '8555' && $ruta == 'CLARO_HN'  && $prefix == 'PIN')
        {
            $url = "http://localhost/cajafuerte/cajafuertepin.php";
                        $texto2 = str_replace('-CDC_submitSMS','',$texto);
            $valores = "origen=".$phone."&mensaje=".$texto2."&destino=".$shortcode."&ruta=".$ruta."&transid=".$service_type;
            $handler = curl_init();
            curl_setopt($handler, CURLOPT_URL, $url);
            curl_setopt($handler, CURLOPT_POST,true);
            curl_setopt($handler, CURLOPT_POSTFIELDS, $valores);
            $response = curl_exec ($handler);
            curl_close($handler);
            $log_user_data->mos($phone,$texto,$shortcode,$ruta,$service_type);
        }

	if($shortcode == '7781' && $ruta == 'CLARO_HN')
        {
            $url = "http://localhost/cajafuerte/cajafuertepin.php";
                        $texto2 = str_replace('-CDC_submitSMS','',$texto);
            $valores = "origen=".$phone."&mensaje=".$texto2."&destino=".$shortcode."&ruta=".$ruta."&transid=".$service_type;
            $handler = curl_init();
            curl_setopt($handler, CURLOPT_URL, $url);
            curl_setopt($handler, CURLOPT_POST,true);
            curl_setopt($handler, CURLOPT_POSTFIELDS, $valores);
            $response = curl_exec ($handler);
            curl_close($handler);
            $log_user_data->mos($phone,$texto,$shortcode,$ruta,$service_type);
        }	
	
	if($shortcode == '7788' && $ruta == 'CLARO_NI')
        {
        $texto2 = str_replace('-CDC_submitSMS','',$texto);    
		$str = 'http://localhost/websocket/client/client2.php?msisdn=' . $phone . '&mensaje=' . urlencode($texto2) . '&operadora=CLARO_NI';
        //echo "$str \n";
        	$ch=curl_init();
        	curl_setopt($ch,CURLOPT_URL, $str);
        	curl_exec($ch);
        	curl_close($ch);
        	
            $log_user_data->mos($phone,$texto,$shortcode,$ruta,$service_type);
            exit;
        }	

        /*
            Determinamos el servicio que se desea a utilizar
        */
        //$clubs = new service_conf($conn,$ruta,$shortcode,'');
        $util = new utilidades();
        
        $clubs_data = $util->get_club_msisdn($conn,$phone,$shortcode,$ruta); //Devolvera 1, 0 o el primero
        if (($clubs_data) && ($clubs_data['status']=='enable')) 
        {
            $id_clubs = $clubs_data['id_club'];
            /*
                Buscamos, registramos y cargamos informacion del usuario
            */

            //$contenido = new contenido($conn);
            //$contenido_data = $contenido->obtener_contenido_random($id_clubs);

            /*
                Pendientes de probar todos los tipos de envio
            */
            switch($clubs_data['type_send'])
            {
                case 'Kannel':
                    $response_free = new kannel($conn,$clubs_data);
                    //$response_paid = new kannel($conn,$clubs_data);
                    break;
                case 'Edge':
                    $response_free = new edge($conn,$clubs_data);
                    //$response_paid = new edge($conn,$clubs_data); 
                    break;
                case 'Beconn':
                    $response_free = new kannel($conn,$clubs_data);
                    //$response_paid = new beconn($conn,$clubs_data);
                    break;
                case 'CDC': //No implementado aun
                    //Tenemos que enviar el transID, asi que lo agregamos al arreglo
                    $clubs_data['transId'] = $service_type ? $service_type:'';
                    $response_free = new cdc($conn,$clubs_data);
                    //$response_paid = new cdc($conn,$clubs_data);
                    break;
                case 'SACA': //No implementado aun
                    break;
                
            }
            

            if (($clubs_data['info_text_2']))
            {
                $id_delivery = $response_free->enviar($phone,$clubs_data['sc_mo'],$clubs_data['info_text_2'],false);
            }

            $log_user_data->actualizar_log($id_user_log,$id_delivery);
            
        }
    }
    mysql_close($conn);
}
?>
