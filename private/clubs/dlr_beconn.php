<?php
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    
    $resulttype = urldecode($_REQUEST['ResultType']);
    $resultdata = urldecode($_REQUEST['ResultData']);
    $iddeliver = urldecode($_REQUEST['IdDeliver']);

    $id_club = urldecode($_REQUEST['id_club']);
    $source = urldecode($_REQUEST['source']);
    $recobro=urldecode($_REQUEST['recobro']);
    $myid = urldecode($_REQUEST['myid']);
    
    $dlr_data = new stdClass();
    $dlr_data->resulttype = $resulttype;
    $dlr_data->resultdata = $resultdata;
    $dlr_data->iddeliver = $iddeliver;
    $dlr_data->id_club = $id_club;
    $dlr_data->source = $source;
    $dlr_data->recobro = $recobro;
    $dlr_data->myid = $myid;

    $connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');

    $channel = $connection->channel();
    $channel->queue_declare('beconn_dlr', false, true, false, false);

    $data = json_encode(array($dlr_data));

    $msg = new AMQPMessage($data, array('delivery_mode' => 2) );


    $channel->basic_publish($msg, '', 'beconn_dlr');

    $channel->close();
    $connection->close();

}
?>
