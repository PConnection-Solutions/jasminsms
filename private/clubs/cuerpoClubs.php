<?php
require_once('./conf/saldo.php');
if ($conn)
    {
        if (mysql_selectdb($namedb,$conn))
        {
            $dlr_info = new dlr($conn);
            if (isset($meta)) //Kannel y Edge
            {
                $meta_mod = substr($meta,0,9);


                //Guardar solo los NACK y los ACK 
                if ((substr($meta_mod,0,4) == 'ACK/') || (substr($meta_mod,0,5) == 'NACK/'))//Kannel y Edge
                {
                    $sql ="insert into clubs_DLR (id_transaction,id_deliver,msisdn,source,estado,fecha_evento,fecha,id_club,otros)".
                    " value('$myid','$myid','$destination','$source','$meta_mod',CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa'),DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')),$id_club,'$meta') on duplicate key".
                    " update estado = '$meta_mod',otros = '$meta',source = '$source'";
                    mysql_query($sql,$conn);

                    error_log(date("Y-m-d H:i:s") . "Last_ID:$myid-Destination:$destination-Source:$source-Id_Club:$id_club-Meta:$meta_mod"."\n", 3, "/opt/lampp/htdocs/private/clubs/dlr_totales.log");

                    /* Respuestas de Kannel
                    cobrados: ACK/
                    no cobrados: NACK/1060/
                    */

                    /*if(substr($meta_mod,0,4) == 'ACK/'){
                        //darvidasAPPrende($destination, "1");
                        //http://72.55.181.60/websocketCobros/client/client2.php

                        $curl = curl_init();
                        // Set some options - we are passing in a useragent too here
                        curl_setopt_array($curl, array(
                            CURLOPT_RETURNTRANSFER => 1,
                            CURLOPT_URL => 'http://127.0.0.1/websocketCobros/client/client2.php',
                            CURLOPT_USERAGENT => 'Codular Sample cURL Request'
                        ));
                        // Send the request & save response to $resp
                        $resp = curl_exec($curl);
                        // Close request to clear up some resources
                        curl_close($curl);

                    }*/
                    $hora_actual = date("H:i");
                    if ($hora_actual >= '08:00' && $hora_actual <= '18:00') { 
                        $NACK = [
                                    "NACK/1060",
                                    "NACK/1003",
                                    "NACK/1064",
                                    "NACK/9999",
                                ];
                     }else{
                        $NACK = [
                                    "NACK/1003",
                                    "NACK/1064",
                                    "NACK/9999",
                                ];
                     }       

                    if ($dlr_alta == 1 and !in_array(substr($meta_mod,0,9), $NACK)) {
                        
                        error_log(date("Y-m-d H:i:s") . " - DLR_ALTA: Destination $destination - Source: $source - DLR: $meta_mod"."\n", 3, "/opt/lampp/htdocs/private/clubs/dlr_alta.log");
                        require_once('./dlr_1003.php');

                        $con = mysqli_connect('rds-compartido.pconnection.net', 'admin', '8sC3rq2iQqEztsj1', 'CLUBS');

                        $Conversion = new Conversion;
                        $suscripcion_dlr = $Conversion->suscripcion_dlr($con, $destination, $id_club);

                        if ($suscripcion_dlr['primer_cobro'] == 'SI') {
                            
                            $data_club = $Conversion->club($con, $suscripcion_dlr['data']['id_clubs']);

                            if ($data_club['estado'] == 1) {

                                $enviar_conversion = $Conversion->enviar_conversion($destination, $data_club['data']['name_club'], $data_club['data']['sc_mo']);
                                echo "Enviar Conversion de Destination $destination - Source: $source: " . $enviar_conversion;
                                print_r($data_club);

                            }

                        }

                        mysqli_close($con);

                    } else if ($dlr_alta == 1 and in_array(substr($meta_mod,0,9), $NACK)) {
                        error_log(date("Y-m-d H:i:s") . " - DLR_ALTA: Destination $destination - Source: $source - DLR: $meta_mod"."\n", 3, "/opt/lampp/htdocs/private/clubs/dlr_1003_1061.log");
                    }

                    ### REGALAR RECARGA POR QUE SE COBRÓ ###

                    /*
                    if (substr($meta_mod,0,4) == 'ACK/') {
                        
                        if (rand(1,4) == 1) {
                            
                            //error_log("MESSAGE: Deberia mandar recarga a $destination."."\n", 3, "dlr_escalonado.log");
                            
                            $PremiosSorteados = new PremioSorteado;
                            $premio = $PremiosSorteados->premio('PemioCobro');

                            if ($premio <> '-1') {
                                
                                $PremiosSorteados->descuento_premio($premio['id']);
                                $PremiosSorteados->insertarmodem($destination, $premio['valor'], 888);

                            }
                            

                        }

                    }
                    */

                    ### FIN CÓDIGO DE REGALAR RECARGA ###

                    $points = 4;

                    if($id_club == 93 and substr($meta_mod,0,4) == 'ACK/'){ 

                        $query = "UPDATE CLUBS.clubs_MSISDN SET fecha_cobro=DATE_ADD(DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')), INTERVAL 3 DAY) WHERE msisdn='$destination' AND id_clubs = $id_club";
                        error_log($query . "\n", 3, "solterosemanal.log");
                        $result = mysql_query($query,$conn);

                    }

                    ### JUEGA 7784, QBN ###
                    if($id_club == 79 and substr($meta_mod,0,4) == 'ACK/'){

                        $query = "UPDATE CLUBS.clubs_MSISDN SET fecha_cobro=DATE_ADD(DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')), INTERVAL 3 DAY), points = $points WHERE msisdn='$destination' AND id_clubs = $id_club";
                        error_log($query . "\n", 3, "qbn.log");
                        $result = mysql_query($query,$conn);

                    }

                    ### FUTBOL 7789 ###
                    if($id_club == 95 and substr($meta_mod,0,4) == 'ACK/'){

                        $query = "UPDATE CLUBS.clubs_MSISDN SET fecha_cobro=DATE_ADD(DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')), INTERVAL 3 DAY) WHERE msisdn='$destination' AND id_clubs = $id_club";
                        //error_log($query . "\n", 3, "fut_Semanal.log");
                        $result = mysql_query($query,$conn);

                    }

                    ### MUSICA 7785 ###
                    if($id_club == 21 and substr($meta_mod,0,4) == 'ACK/'){

                        $query = "UPDATE CLUBS.clubs_MSISDN SET fecha_cobro=DATE_ADD(DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')), INTERVAL 4 DAY) WHERE msisdn='$destination' AND id_clubs = $id_club";
                        //error_log($query . "\n", 3, "fut_Semanal.log");
                        $result = mysql_query($query,$conn);

                    }

                    ### TRABAJO 7787 ###
                    if($id_club == 94 and substr($meta_mod,0,4) == 'ACK/'){

                        $query = "UPDATE CLUBS.clubs_MSISDN SET fecha_cobro=DATE_ADD(DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')), INTERVAL 4 DAY) WHERE msisdn='$destination' AND id_clubs = $id_club";
                        //error_log($query . "\n", 3, "fut_Semanal.log");
                        $result = mysql_query($query,$conn);

                    }

                    ### solteros 7787 ###
                    if($id_club == 93 and substr($meta_mod,0,4) == 'ACK/'){

                        $query = "UPDATE CLUBS.clubs_MSISDN SET fecha_cobro=DATE_ADD(DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')), INTERVAL 4 DAY) WHERE msisdn='$destination' AND id_clubs = $id_club";
                        //error_log($query . "\n", 3, "fut_Semanal.log");
                        $result = mysql_query($query,$conn);

                    }

                    ### DESAFIO 8611 ###
                    if($id_club == 64 and substr($meta_mod,0,4) == 'ACK/'){

                        $query = "UPDATE CLUBS.clubs_MSISDN SET fecha_cobro=DATE_ADD(DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')), INTERVAL 4 DAY) WHERE msisdn='$destination' AND id_clubs = $id_club";
                        //error_log($query . "\n", 3, "fut_Semanal.log");
                        $result = mysql_query($query,$conn);

                    } 

                    ### TRABAJO 1185 ###
                    if($id_club == 32 and substr($meta_mod,0,4) == 'ACK/'){

                        $query = "UPDATE CLUBS.clubs_MSISDN SET fecha_cobro=DATE_ADD(DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')), INTERVAL 4 DAY) WHERE msisdn='$destination' AND id_clubs = $id_club";
                        //error_log($query . "\n", 3, "fut_Semanal.log");
                        $result = mysql_query($query,$conn);

                    } 

                    ### EMPLEO 1185 ###
                    if($id_club == 17 and substr($meta_mod,0,4) == 'ACK/'){

                        $query = "UPDATE CLUBS.clubs_MSISDN SET fecha_cobro=DATE_ADD(DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')), INTERVAL 4 DAY) WHERE msisdn='$destination' AND id_clubs = $id_club";
                        //error_log($query . "\n", 3, "fut_Semanal.log");
                        $result = mysql_query($query,$conn);

		    }

		    if($id_club == 31 and substr($meta_mod,0,4) == 'ACK/'){

                        $query = "UPDATE CLUBS.clubs_MSISDN SET fecha_cobro=DATE_ADD(DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')), INTERVAL 4 DAY) WHERE msisdn='$destination' AND id_clubs = $id_club";
                        //error_log($query . "\n", 3, "fut_Semanal.log");
                        $result = mysql_query($query,$conn);

                    }

                    ### KULTURA 7785 ###
                    if($id_club == 124 and substr($meta_mod,0,4) == 'ACK/'){

                        $query = "UPDATE CLUBS.clubs_MSISDN SET fecha_cobro=DATE_ADD(DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')), INTERVAL 3 DAY) WHERE msisdn='$destination' AND id_clubs = $id_club";
                        //error_log($query . "\n", 3, "fut_Semanal.log");
                        $result = mysql_query($query,$conn);

                        kultura($destination);

                    }

                    ### FOODIES 7785 ###
                    if($id_club == 125 and substr($meta_mod,0,4) == 'ACK/'){

                        $query = "UPDATE CLUBS.clubs_MSISDN SET fecha_cobro=DATE_ADD(DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')), INTERVAL 3 DAY) WHERE msisdn='$destination' AND id_clubs = $id_club";
                        //error_log($query . "\n", 3, "fut_Semanal.log");
                        $result = mysql_query($query,$conn);

                    }

                    ### VIAJE 7785 ###
                    if($id_club == 126 and substr($meta_mod,0,4) == 'ACK/'){

                        $query = "UPDATE CLUBS.clubs_MSISDN SET fecha_cobro=DATE_ADD(DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')), INTERVAL 3 DAY) WHERE msisdn='$destination' AND id_clubs = $id_club";
                        //error_log($query . "\n", 3, "fut_Semanal.log");
                        $result = mysql_query($query,$conn);

                    }

                    ### MUJER 7700 ###
                    if($id_club == 128 and substr($meta_mod,0,4) == 'ACK/'){

                        $query = "UPDATE CLUBS.clubs_MSISDN SET fecha_cobro=DATE_ADD(DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')), INTERVAL 3 DAY) WHERE msisdn='$destination' AND id_clubs = $id_club";
                        //error_log($query . "\n", 3, "fut_Semanal.log");
                        $result = mysql_query($query,$conn);

                    }

                    ### ATIRI 7786 ###
                    if($id_club == 127 and substr($meta_mod,0,4) == 'ACK/'){

                        $query = "UPDATE CLUBS.clubs_MSISDN SET fecha_cobro=DATE_ADD(DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')), INTERVAL 1 DAY) WHERE msisdn='$destination' AND id_clubs = $id_club";
                        //error_log($query . "\n", 3, "fut_Semanal.log");
                        $result = mysql_query($query,$conn);

                    }

                    ### PROMO ###
                    if($id_club == 129 and substr($meta_mod,0,4) == 'ACK/'){

                        $query = "UPDATE CLUBS.clubs_MSISDN SET fecha_cobro=DATE_ADD(DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')), INTERVAL 8 DAY) WHERE msisdn='$destination' AND id_clubs = $id_club";
                        //error_log($query . "\n", 3, "fut_Semanal.log");
                        $result = mysql_query($query,$conn);

                    }

                    ### FARANDULA ###
                    if($id_club == 130 and substr($meta_mod,0,4) == 'ACK/'){

                        $query = "UPDATE CLUBS.clubs_MSISDN SET fecha_cobro=DATE_ADD(DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')), INTERVAL 2 DAY) WHERE msisdn='$destination' AND id_clubs = $id_club";
                        //error_log($query . "\n", 3, "fut_Semanal.log");
                        $result = mysql_query($query,$conn);

                    }

                    ###VIDA ###
                    if($id_club == 131 and substr($meta_mod,0,4) == 'ACK/'){

                        $query = "UPDATE CLUBS.clubs_MSISDN SET fecha_cobro=DATE_ADD(DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')), INTERVAL 7 DAY) WHERE msisdn='$destination' AND id_clubs = $id_club";
                        //error_log($query . "\n", 3, "fut_Semanal.log");
                        $result = mysql_query($query,$conn);

                    }

                    ###EXITO 1180###
                    if($id_club == 60 and substr($meta_mod,0,4) == 'ACK/'){

                        $query = "UPDATE CLUBS.clubs_MSISDN SET fecha_cobro=DATE_ADD(DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')), INTERVAL 4  DAY) WHERE msisdn='$destination' AND id_clubs = $id_club";
                        //error_log($query . "\n", 3, "fut_Semanal.log");
                        $result = mysql_query($query,$conn);

                    }

                    ###ORAR 1180###
                    if($id_club == 62 and substr($meta_mod,0,4) == 'ACK/'){

                        $query = "UPDATE CLUBS.clubs_MSISDN SET fecha_cobro=DATE_ADD(DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')), INTERVAL 4  DAY) WHERE msisdn='$destination' AND id_clubs = $id_club";
                        //error_log($query . "\n", 3, "fut_Semanal.log");
                        $result = mysql_query($query,$conn);

                    }

                    ### PROMESA 7789 ###
                     if($id_club == 25 and substr($meta_mod,0,4) == 'ACK/'){

                        $query = "UPDATE CLUBS.clubs_MSISDN SET fecha_cobro=DATE_ADD(DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')), INTERVAL 4  DAY) WHERE msisdn='$destination' AND id_clubs = $id_club";
                        //error_log($query . "\n", 3, "fut_Semanal.log");
                        $result = mysql_query($query,$conn);

                    }

                    ### ORAR 7789 ###
                     if($id_club == 1 and substr($meta_mod,0,4) == 'ACK/'){

                        $query = "UPDATE CLUBS.clubs_MSISDN SET fecha_cobro=DATE_ADD(DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')), INTERVAL 4  DAY) WHERE msisdn='$destination' AND id_clubs = $id_club";
                        //error_log($query . "\n", 3, "fut_Semanal.log");
                        $result = mysql_query($query,$conn);

                    }
                    ### LUCKY 7789 ###
                    if($id_club == 55 and substr($meta_mod,0,4) == 'ACK/'){

                        $query = "UPDATE CLUBS.clubs_MSISDN SET fecha_cobro=DATE_ADD(DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')), INTERVAL 4  DAY) WHERE msisdn='$destination' AND id_clubs = $id_club";
                        //error_log($query . "\n", 3, "lucky_Semanal.log");
                        $result = mysql_query($query,$conn);

                    }

                    if($id_club == 88 and substr($meta_mod,0,4) == 'ACK/'){

                        $query = "UPDATE CLUBS.clubs_MSISDN SET fecha_cobro=DATE_ADD(DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')), INTERVAL 3 DAY) WHERE msisdn='$destination' AND id_clubs = $id_club";
                        //error_log($query . "\n", 3, "musicasemanal.log");
                        $result = mysql_query($query,$conn);

                    }
                    
                    
                    if($id_club == 121 and substr($meta_mod,0,4) == 'ACK/'){

                        $query = "UPDATE `CAJA`.`suscrito` SET `intentos` = '0' WHERE `msisdn` = '$destination'";
                        //error_log($query . "\n", 3, "solterosemanal.log");
                        //$result = mysql_query($query,$conn);

                    }

                    if($id_club == 95 and substr($meta_mod,0,4) == 'ACK/'){

                        puntosFutbol($destination);

                    }

                    ###Sección de captura de 1003 para dar de baja ###

                    if(strstr($meta_mod, 'NACK/1003')){

                        $query1003 = "UPDATE CLUBS.clubs_MSISDN SET fecha_ult_evento = CONVERT_TZ(NOW(), 'UTC', 'America/Tegucigalpa'), next_points = next_points + 1 WHERE msisdn = '$destination' AND id_clubs = $id_club AND `fecha_ult_evento` <> DATE(CONVERT_TZ(NOW(), 'UTC', 'America/Tegucigalpa'))";
                        $result1003 = mysql_query($query1003, $conn);
                    
                    }

                    ###Sección de captura de 1061 para dar de baja ###

                    if(strstr($meta_mod, 'NACK/1061')){

                        $query1061 = "UPDATE CLUBS.clubs_MSISDN SET fecha_ult_evento = CONVERT_TZ(NOW(), 'UTC', 'America/Tegucigalpa'), id_question = id_question + 1 WHERE msisdn = '$destination' AND id_clubs = $id_club AND `fecha_ult_evento` <> DATE(CONVERT_TZ(NOW(), 'UTC', 'America/Tegucigalpa'))";
                        $result1061 = mysql_query($query1061, $conn);
                    
                    }

                    if(strstr($meta_mod, 'NACK/1060')){

                        //error_log("Destination $destination Meta $meta MyID $myid Source $source Id_Club $id_club Recobro $recobro"."\n", 3, "dlr_escalonado.log");

                        $verificarTigo = verificarTigo($id_club);

                        if($verificarTigo == 1){

                            if ($penvio == 1) {
                                $priority = 3;
                            } else {
                                $priority = 2;
                            }

                            //error_log("Destination $destination Meta $meta MyID $myid Source $source Id_Club $id_club Recobro $recobro"."\n", 3, "dlr_escalonado.log");

                            if(strstr($source, "@1")){

                                //if ($id_club <> '53') { // Cortos de 7787 no deben de mandar @2 ni @3

                                if (!strstr($source, "7787@")) { // Cortos de 7787 no deben de mandar @2 ni @3


                                    if($id_club == '55'){

                                        $codigo = genera_codigo_sin_premio();
                                        $content = "Tu codigo es $codigo. Recibe numeros repetidos y gana. Motivado? Entra aqui http://goo.gl/iQDPJz Salir? SALIR LUCKY al 7788 -PConnection";

                                    } else if ($id_club == '103'){

                                        $codigo = genera_codigo_sin_premio();
                                        $content = "Tu codigo es $codigo. Recibe numeros repetidos y gana paquetes de internet. Salir? SALIR MEGA al 8611 - PConnection";

                                    } else if($id_club == '121') {

                                        $verificarNumero = verificarNumero(''); // Importante
                                        $numero = str_split($verificarNumero['codigo']); // Importante
                                        
                                        $i = rand ( 0 , 3 ); // Importante
                                        $pista = pista($numero[$i]); // Importante

                                        $content = "Esta es tu pista de hoy: ". $pista['pista'] . ". Tienes 5 intentos para adivinar - People Connection";


                                    } else {

                                        $contenido = new contenido($conn);
                                        $content_data = $contenido->obtener_contenido_bc($id_club, false);
                                        $content = $content_data['content'];
                                    
                                    }

                                    $binfo = crearBinfo($id_club);
                                    $nuevoSource = str_replace("@1", "@2", $source);

                                    if($content <> ''){
                                        $id_msisdn = id_msisdn($destination, $id_club);
                                        $id_delivery = enviar_mensaje($destination, $nuevoSource, str_replace('<URL_RULETA_URL_RULETA_>', 'http://pcon.vip/'.$id_msisdn, $content), "C".$binfo, $id_club, 1);
                                        insertar_envios_mt($id_club,$id_msisdn,$destination,$puntos=0,$id_delivery,'0000');

                                        error_log("enviar_mensaje($destination, $nuevoSource, $content, C"."$binfo);"." Cola: $priority"."\n", 3, "/opt/lampp/htdocs/private/clubs/dlr_escalonado.log");
                                    }

                                }
                                
                            }
                            ### De acá ###
                            
                            else if(strstr($source, "@2")){
                                
                                if($id_club == '55'){

                                    $codigo = genera_codigo_sin_premio();
                                    $content = "Tu codigo es $codigo. Recibe numeros repetidos y gana. Motivado? Entra aqui http://goo.gl/iQDPJz Salir? SALIR LUCKY al 7788 -PConnection";

                                } else if ($id_club == '103'){

                                    $codigo = genera_codigo_sin_premio();
                                    $content = "Tu codigo es $codigo. Recibe numeros repetidos y gana paquetes de internet. Salir? SALIR MEGA al 8611 - PConnection";

                                } else if($id_club == '121') {

                                    $verificarNumero = verificarNumero(''); // Importante
                                    $numero = str_split($verificarNumero['codigo']); // Importante
                                    
                                    $i = rand ( 0 , 3 ); // Importante
                                    $pista = pista($numero[$i]); // Importante

                                    $content = "Esta es tu pista de hoy: ". $pista['pista'] . ". Tienes 5 intentos para adivinar - People Connection";


                                } else {

                                    $contenido = new contenido($conn);
                                    $content_data = $contenido->obtener_contenido_bc($id_club, false);
                                    $content = $content_data['content'];
                                
                                }
                                
                                $binfo = crearBinfo($id_club);
                                $nuevoSource = str_replace("@2", "@3", $source);

                                if($content <> ''){

                                    $id_msisdn = id_msisdn($destination, $id_club);
                                    $id_delivery = enviar_mensaje($destination, $nuevoSource, str_replace('<URL_RULETA_URL_RULETA_>', 'http://pcon.vip/'.$id_msisdn, $content), "C".$binfo, $id_club, 2);
                                    insertar_envios_mt($id_club,$id_msisdn,$destination,$puntos=0,$id_delivery,'0000');
                                    
                                    error_log("enviar_mensaje($destination, $nuevoSource, $content, C"."$binfo);"." Cola: $priority"."\n", 3, "/opt/lampp/htdocs/private/clubs/dlr_escalonado.log");
                                }

                            }
                        
                            ### hasta acá ### 

                        }

                    }

                    if ($recobro == '1')
                    {
                        $dlr_info->analizar_registros($meta,$destination,$myid,$id_club,true);
                        
                    }
                    else
                    {
                        $dlr_info->analizar_registros($meta,$destination,$myid,$id_club,false);
                        
                    }
                    
                } else if (($meta_mod == '0') || (($meta_mod >= '201') && ($meta_mod <= '299')))//CDC
                {
                    //$myid_mod = $myid. substr(uniqid('', true), -3);
                    $myid_mod = substr(uniqid('', true),-19);
                    $sql ="insert into clubs_DLR (id_transaction,id_deliver,msisdn,source,estado,fecha_evento,fecha,id_club,otros)".
                    " value('$myid_mod','$myid','$destination','$source','$meta_mod',CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa'),DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')),$id_club,'$meta') on duplicate key".
                    " update estado = '$meta_mod',otros = '$meta',source = '$source'";
                    mysql_query($sql,$conn);
                    if ($recobro == '1')
                    {
                        $dlr_info->analizar_registros($meta,$destination,$myid,$id_club,true);
                        
                    }
                    else
                    {
                        $dlr_info->analizar_registros($meta,$destination,$myid,$id_club,false);
                    }
                    
                }
            } 
            else if($resultdata)//Beconnected
            {
                // | son las divisiones de registro y , son las divisiones del campo
                $result_data = explode("|",$resultdata);
                foreach($result_data as $key => $value)
                {
                    $result_data_comma = explode(",",$value);
                    $id_tran = $result_data_comma[0]; //idtransaction
                    $msisdn = $result_data_comma[1]; //msisdn
                    $status = $result_data_comma[2]; //status
                    $cobro = 0;
                    switch  ($status)
                    {
                        case 100:
                        //Mensaje cobrado
                            //$cobro = $result_data_comma[3]; //valor cobrado
                            break;
                        case 200:
                        //Mensaje sin aplicación de cobro  
                            break;
                        case 300:
                        //Error en el cobro 
                            break;
                        case 400:
                        //Saldo insuficiente
                            break;
                        case 500:
                        //Número inactivo 
                            break;
                        case 600:
                        //Número en lista negra
                            break;
                        case 700:
                        //Número inválido
                            break;
                        case 800:
                        //Número inexistente o no registrado con el operador 
                            break;
                        default:
                    }
                    $sql ="insert into clubs_DLR (id_transaction,id_deliver,msisdn,source,estado,fecha_evento,fecha,valor_cobrado,id_club,otros)".
                        //" value('$id_tran','$myid','$msisdn','$source','$status',DATE(CONVERT_TZ(curdate(), 'UTC', 'America/Tegucigalpa')),$cobro,$id_club,'$value')";
                        " value('$id_tran','$myid','$msisdn','$source','$status',CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa'),DATE(CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa')),$cobro,$id_club,'$status')";
                    mysql_query($sql,$conn);
                    if ($recobro == '1')
                    {
                        $dlr_info->analizar_registros($status,$msisdn,$myid,$id_club,true);
                        
                    }
                    else
                    {
                        $dlr_info->analizar_registros($status,$msisdn,$myid,$id_club,false);
                    }
                    
                    
                }
            }
            
        }
        
    } else {

        echo "No conecto\n";

    }
?>
