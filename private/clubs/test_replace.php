<?php

$message = "Bienvenido a la comunidad AMOR. recibe contenido de amor, esperanza y la oportunidad de participar en juegos con premios. Costo por SMS recibido: $0.30 (impuestos incluidos). Para dejar de recibir mensajes envía SALIR AMOR al 1185. Conoce mas y participa en: fz.gameconnections.net?m=<<msisdn>>";
$to = '50496687930';

$text = str_replace('<<msisdn>>', substr($to, -8), $message);

echo $text;
