<?php

header("Access-Control-Allow-Origin: *");
//error_reporting(0);
require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

date_default_timezone_set("America/Tegucigalpa");

function convertirHexadecimalADecimal($dlr) {
  // Buscar el código de error hexadecimal en la cadena DLR
  preg_match('/NACK\/0x(?P<hex_code>[0-9a-f]{8})/', $dlr, $matches);

  // Convertir el código hexadecimal a decimal
  $decimal_code = hexdec($matches['hex_code']);

  // Reemplazar el código hexadecimal por el decimal en la cadena DLR
  $new_dlr = str_replace('0x'.$matches['hex_code'], $decimal_code, $dlr);

  return $new_dlr;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{

    
    $resulttype = urldecode($_REQUEST['ResultType']);
    $resultdata = urldecode($_REQUEST['ResultData']);
    $iddeliver = urldecode($_REQUEST['IdDeliver']);
    
    $id_club = urldecode($_REQUEST['id_club']);
    $source = urldecode($_REQUEST['source']);
    $recobro=urldecode($_REQUEST['recobro']);
    $myid = urldecode($_REQUEST['myid']);
}
else if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
    //$destination = urldecode($_REQUEST['destination']);
    $destination = str_replace('+','',rawurldecode($_REQUEST['destination']));
    $meta = convertirHexadecimalADecimal(urldecode($_REQUEST['meta']));
    $myid = urldecode($_REQUEST['myid']);
    $source = urldecode($_REQUEST['source']);

    $id_club = urldecode($_REQUEST['id_club']);
    $recobro=urldecode($_REQUEST['recobro']);

    if (isset($_REQUEST['dlr_alta'])) {
        $dlr_alta = $_REQUEST['dlr_alta'];
    } else {
        $dlr_alta = 0;
    }

    if (isset($_REQUEST['penvio'])) {
        $penvio = $_REQUEST['penvio'];
    } else {
        $penvio = 0;
    }
    //$data = urldecode($_REQUEST['data']);//Este indicador de cdc
    
} 

$meta_mod = substr($meta,0,9);

if (substr($meta_mod,0,4) == 'ACK/') {

    $red = 'TigoACK';

} else if (substr($meta_mod,0,5) == 'NACK/') {

    if ($dlr_alta == 0) {
        $red = 'TigoNACK';
    } else {
        $hora_actual = date("H:i");
        if ($hora_actual >= '08:00' && $hora_actual <= '18:00') {
            $NACK = [
                        "ACK/",
                    ];
        }else{
            $NACK = [
                        "ACK/",
                        "NACK/1060",
                    ];
        }            

        if (in_array(substr($meta_mod,0,9), $NACK)) {
            //$red = 'TigoNACKAltaNoConv';
	    $red = 'TigoNACKAlta';
        } else {
            //$red = 'TigoNACKAlta';
	    $red = 'TigoNACKAltaNoConv';
        }

    }

    if ($red == 'TigoNACK' and substr($source, -2) == '@2') {
        $red = 'TigoNACK@2';
    }

    if ($red == 'TigoNACK' and substr($source, -2) == '@3') {
        $red = 'TigoNACK@3';
    }

} else {

    die;

}

$connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$varPost = [
                "destination"   => $destination,
                "meta"          => $meta,
                "myid"          => $myid,
                "source"        => $source,
                "id_club"       => $id_club,
                "recobro"       => $recobro,
                "dlr_alta"      => $dlr_alta,
                "penvio"        => $penvio,
            ];

$channel->queue_declare($red, false, true, false, false);

$msg = new AMQPMessage(json_encode($varPost), array('delivery_mode' => 2) );

//$channel->basic_publish($msg, '', 'cdc_notification');
$channel->basic_publish($msg, '', $red);


$channel->close();
$connection->close();
//mysqli_close($sms);

//require_once('./conf/conf.inc');
