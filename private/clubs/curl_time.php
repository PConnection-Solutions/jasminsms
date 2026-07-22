#!/usr/bin/php -q
<?php


$url = "https://www.mayanschool.edu.hn";  
		$parametros_post = '';
		//die($parametros_post);
		$sesion = curl_init($url);
		
		curl_setopt ($sesion, CURLOPT_POST, true); 
		curl_setopt ($sesion, CURLOPT_POSTFIELDS, $parametros_post); 
		curl_setopt($sesion, CURLOPT_HEADER, false); 
		curl_setopt($sesion, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($sesion);

		$info = curl_getinfo($sesion);

		curl_close($sesion); 


echo $info['total_time'] . "s\n";