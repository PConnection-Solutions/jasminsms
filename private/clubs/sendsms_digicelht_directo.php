<?php
	$hostname_sms = "rds-compartido.pconnection.net";
	$database_sms = "smsdigicelht";
	$username_sms = "admin";
	$password_sms = "8sC3rq2iQqEztsj1";
	$sms = mysql_pconnect($hostname_sms, $username_sms, $password_sms) or die(mysql_error());
	date_default_timezone_set("America/Tegucigalpa");

	function sendsms($host, $script, $request, $port){
	        $request_length = strlen($request);
	        $method = "GET"; // must be POST if sending multiple messages
		if ($method == "GET")
		{
		  $script .= "?$request";
		}
		//Now comes the header which we are going to post.
		$header = "$method $script HTTP/1.1\r\n";
		$header .= "Host: $host\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: $request_length\r\n";
		$header .= "Connection: close\r\n\r\n";
		$header .= "$request\r\n";

		//Now we open up the connection
		$socket = @fsockopen($host, $port, $errno, $errstr);
		if ($socket) //if its open, then...
		{
		  fputs($socket, $header); // send the details over
		  $output = "";
		  while(!feof($socket))
		  {
		    $output .= fgets($socket); //get the results
		  }
		  fclose($socket);
		}
			//echo $output;
			
		return;	
                
	}
	
	function enviar($origen, $destino, $mensaje,$id,$comunidad){
   
		$username = "peopleconnect";
		$password = "p3opl3HTTP";
		$sender = $origen;  
		$recipient = $destino;
		$message = $mensaje;
		$transactionId=$id;
		$messageType="SMS";
		$subscriptionCode="LIFE_CLUB";
		
		$url = "http://services.newcomwi.com/mt/http/run";  
		$parametros_post = 'username='.urlencode($username).'&password='.urlencode($password).'&sender='.urlencode($sender).'&recipient='.urlencode($recipient).'&message='.urlencode($message).'&transactionId='.urlencode($transactionId).'&messageType='.urlencode($messageType).'&subscriptionCode='.urlencode($subscriptionCode);
		//error_log($parametros_post ."\n", 3, "/opt/lampp/htdocs/private/clubs/digicel_recobro.log");				
		$sesion = curl_init($url);
		
		curl_setopt ($sesion, CURLOPT_POST, true); 
		curl_setopt ($sesion, CURLOPT_POSTFIELDS, $parametros_post); 
		curl_setopt($sesion, CURLOPT_HEADER, false); 
		curl_setopt($sesion, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($sesion); 
		//error_log($response ."\n", 3, "/opt/lampp/htdocs/private/clubs/digicel_recobro.log");				
		if($response === FALSE){
			//die(curl_error($sesion));
			$ws_error = curl_error($sesion);
			sms_ws_error($url, $parametros_post,$ws_error);			
		}
		$responseData = json_decode($response, TRUE);
		
	        $id_answer = $responseData['id'];
		$status_billing  = $responseData['status_billing'];
		$status_sms  = $responseData['status_sms'];
		curl_close($sesion); 
		sms_log($id, $origen, $destino, $mensaje,$comunidad,$id_answer,$status_billing,$status_sms,"0");
		if ($status_billing == "INSUFFICIENT_FUNDS"){
			//enviar_reintento($origen, $destino, $mensaje,$comunidad);
		}
		return;
        
	}
	function enviarc($origen, $destino, $mensaje,$id,$comunidad){
   
                $username = "peopleconnect";
		$password = "p3opl3HTTP";
		$sender = $origen;  
		$recipient = $destino;
		$message = $mensaje;
		$transactionId=$id;
		$messageType="SMS";
		$subscriptionCode="LIFE_CLUB";
		$charge="20";
		
		
                $url = "http://services.newcomwi.com/mt/http/run";  
		$parametros_post = 'username='.urlencode($username).'&password='.urlencode($password).'&sender='.urlencode($sender).'&recipient='.urlencode($recipient).'&message='.urlencode($message).'&transactionId='.urlencode($transactionId).'&messageType='.urlencode($messageType).'&subscriptionCode='.urlencode($subscriptionCode).'&charge='.urlencode($charge);
		//die($parametros_post);
		$sesion = curl_init($url);
		
		curl_setopt ($sesion, CURLOPT_POST, true); 
		curl_setopt ($sesion, CURLOPT_POSTFIELDS, $parametros_post); 
		curl_setopt($sesion, CURLOPT_HEADER, false); 
		curl_setopt($sesion, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($sesion);
		//die ($response);
		if($response === FALSE){
			//die(curl_error($sesion));
			$ws_error = curl_error($sesion);
			sms_ws_error($url, $parametros_post,$ws_error);	
		}
		$responseData = json_decode($response, TRUE);
		//die ($responseData);
		//error_log($responseData ."\n", 3, "/opt/lampp/htdocs/private/clubs/log_26032022.log");	
		$id_answer = $responseData['id'];
		if (strlen($id_answer) == 0){
			$id_answer = "0";
		}
		$status_billing['status_billing']  = $responseData['status_billing'];
		$status_billing['id']  = $responseData['id'];
		$status_sms  = $responseData['status_sms'];
		$info = curl_getinfo($sesion);
		$status_billing['total_time'] = $info['total_time'];
		$status_billing['http_code'] = $info['http_code'];
		curl_close($sesion); 
		sms_log($id, $origen, $destino, $mensaje,$comunidad,$id_answer,$status_billing['status_billing'],$status_sms,"1");
		if ($status_billing['status_billing'] == "INSUFFICIENT_FUNDS"){
			//enviar_reintento($origen, $destino, $mensaje,$comunidad);
			if (date("H") >= '15' and date("H") < '18') {
				//nuevo cambio 30062022 $mensaje = "Enskripsyon 'LIFE CLUB' la paka renouvle paske w pa gen ase lajan sou kont ou. Tanpri rechaje kont ou pou itilize l. Voye STOP bay 325 pou kanpe enskripsyon an"; //cambio 22022021
				$mensaje = "Enskripsyon 'LIFE CLUB' la paka renouvle paske w pa gen ase lajan sou kont ou. Tanpri rechaje kont ou pou itilize l. Voye STOP bay 325 pou kanpe enskripsyon an";
				enviar_mensaje($destino, $origen, $mensaje,$comunidad); //cambio 22022021
			}

		}
		return $status_billing;
        
	}
	
	function enviar_mensaje($origen, $destino, $mensaje,$comunidad){
		$fecha= date("Y-m-d H:i:s");
		/*$query  = "update smsdigicelht.reference set id = LAST_INSERT_ID(id+1)";
		$result = mysql_query($query);
		$ref = mysql_insert_id();*/
		$ref = uniqid();
		enviar($destino,$origen,$mensaje,$ref,$comunidad);
		return;
	}

	function enviar_cobro($origen, $destino, $mensaje,$comunidad){
		$fecha= date("Y-m-d H:i:s");
		/*$query  = "update smsdigicelht.reference set id = LAST_INSERT_ID(id+1)";
		$result = mysql_query($query);
		$ref = mysql_insert_id();*/
		$ref = uniqid();
		//enviarc($destino,$origen,$mensaje,$ref,$comunidad);
		return enviarc($destino,$origen,$mensaje,$ref,$comunidad);
		
	}
	function sms_log($id, $origen, $destino, $mensaje,$comunidad,$id_answer,$status_billing,$status_sms,$tipo_sms){
		$mensaje = addslashes($mensaje);
		$query = "insert into smsdigicelht.sms_log (id, origen, destino, mensaje, fecha,comunidad,id_answer,status_billing,status_sms,tipo_sms) values ('$id', '$origen', '$destino', '$mensaje', CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa'),'$comunidad',$id_answer,'$status_billing','$status_sms','$tipo_sms')";

		//error_log($query ."\n", 3, "/opt/lampp/htdocs/private/clubs/log_26032022.log");				
		$result = mysql_query($query);
		//return;
	
	}
	function sms_ws_error($url, $parametros,$ws_error){
		$query = "insert into smsdigicelht.sms_ws_error (url,parametros,ws_error,fecha) values ('$url', '$parametros', '$ws_error', CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa'))";
		$result = mysql_query($query);
		//return;
	
	}
	function actualizacion_cobro($msisdn,$cobro){
		$query5  = "update comunidadesdigicelht.suscritos_comunidades set ultimo_cobro='$cobro',fecha_ultimo_cobro=date(CONVERT_TZ(NOW(), 'UTC', 'America/Tegucigalpa')),numero_cobros = numero_cobros + 1, p1 = IF(p1 <=2 , p1 + 1, p1), cobro_acumulado=cobro_acumulado + '$cobro' where numero = '$msisdn'";
		$result5 = mysql_query($query5);
	
	}
        function suscribir_digicelht($msisdn){

		$username = "peopleconnect";
		$password = "p3opl3HTTP";
		$shortcode = "325";
		$msisdn = $msisdn;
		$code="LIFE_CLUB";
		$source = "SMS";
		
		$url =  "http://services.newcomwi.com/mt/subscribe"; 
		$parametros_post = 'username='.urlencode($username).'&password='.urlencode($password).'&shortCode='.urlencode($shortcode).'&msisdn='.urlencode($msisdn).'&code='.urlencode($code).'&source='.urlencode($source);

		$sesion = curl_init($url);
		
		curl_setopt ($sesion, CURLOPT_POST, true); 
		curl_setopt ($sesion, CURLOPT_POSTFIELDS, $parametros_post); 
		curl_setopt($sesion, CURLOPT_HEADER, false); 
		curl_setopt($sesion, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($sesion);
		
		if($response === FALSE){
			die(curl_error($ch));
		}
		$responseData = json_decode($response, TRUE);
		$status_sms  = $responseData['status'];
		if ($status_sms == "true"){
			return true;	
		}else{
			return false;
		}
		/*print_r($responseData);
		die();*/
	     
		//return;
        
	}
	function desuscribir_digicelht($msisdn){

		$username = "peopleconnect";
		$password = "p3opl3HTTP";
		$shortcode = "325";
		$msisdn = $msisdn;
		$code="LIFE_CLUB";
		$source = "SMS";
		
		$url =  "http://services.newcomwi.com/mt/unsubscribe"; 
		$parametros_post = 'username='.urlencode($username).'&password='.urlencode($password).'&shortCode='.urlencode($shortcode).'&msisdn='.urlencode($msisdn).'&code='.urlencode($code).'&source='.urlencode($source);

		$sesion = curl_init($url);
		
		curl_setopt ($sesion, CURLOPT_POST, true); 
		curl_setopt ($sesion, CURLOPT_POSTFIELDS, $parametros_post); 
		curl_setopt($sesion, CURLOPT_HEADER, false); 
		curl_setopt($sesion, CURLOPT_RETURNTRANSFER, true); 
		$response = curl_exec($sesion);
		
		if($response === FALSE){
			die(curl_error($ch));
		}
		$responseData = json_decode($response, TRUE);
		$status_sms  = $responseData['status'];
		if ($status_sms == "true"){
			return true;	
		}else{
			return false;
		}
		//return;
        
	}
	
?>
