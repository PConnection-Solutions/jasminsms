<?php
require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

chdir(dirname(__FILE__)); 

//require_once('./conf/conf.inc');
require_once('./conf/cdc_server_data.php');

$classmap = array();

$wsdlPath = "http://172.17.72.245:9093/cdc-service/services/CPSMSService.wsdl";  
             
$options = array('uri'=>$wsdlPath);

foreach($classmap as $key => $value) 
{
      if(!isset($options['classmap'][$key])) 
      {
        $options['classmap'][$key] = $value;
      }
}
try
{
    $red = 'CLARO_SV';
    $server = new SoapServer($wsdlPath,$options);
    $server->setClass("cdc_soap_server",$red);
    $server->handle();
    
}catch(Exception $e)
{
    
}
?>
