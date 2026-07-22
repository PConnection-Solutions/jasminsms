<?php
header("Access-Control-Allow-Origin: *");
error_reporting(E_ALL);
require_once __DIR__ . '/vendor/autoload.php';

//require_once('./conf/conf.inc');
require_once('./conf/cdc_server_data.php');

chdir(dirname(__FILE__)); 
/*
$classmap = array('GenericResponseType' => 'GenericResponseType',
                'SubscriptionResponseType' => 'SubscriptionResponseType',
                'ShortMessageType' => 'ShortMessageType',
                'AuthenticationRequestType' => 'AuthenticationRequestType',
                'AuthenticationResponseType' => 'AuthenticationResponseType',
                'ServiceRequestType' => 'ServiceRequestType',
                'ServiceResponseType' => 'ServiceResponseType',
                'PageRequestType' => 'PageRequestType',
                'PageResponseType' => 'PageResponseType',
                'DeliverResponseType' => 'DeliverResponseType',
                'SubmitResponseType' => 'SubmitResponseType',
                'DeliverContentResponseType' => 'DeliverContentResponseType',
                'bonusChrgDate' => 'bonusChrgDate',
                'bonusChrgDates' => 'bonusChrgDates',
                'bonusChrgDetail' => 'bonusChrgDetail',
                'tariffDetail' => 'tariffDetail',
                'tariff' => 'tariff',
                'tariffList' => 'tariffList',
                'updateAllTariffRequestType' => 'updateAllTariffRequestType',
                'deliveryNotificationRequest' => 'deliveryNotificationRequest',
                'sendCPNotificationRequest' => 'sendCPNotificationRequest',
                'operationDescription' => 'operationDescription',
                'notifyTo' => 'notifyTo',
                'deliveryNotificationResponse' => 'deliveryNotificationResponse',
                'sendCPNotificationResponse' => 'sendCPNotificationResponse',
                'deliverSMSRequest' => 'deliverSMSRequest',
                );
                */
$classmap = array();
/*
$classmap = array('deliveryNotificationRequest' => 'deliveryNotificationRequest',
                'deliveryNotificationResponse' => 'deliveryNotificationResponse',
                'deliverSMSRequest' => 'deliverSMSRequest',
                );
*/
$wsdlPath = "http://172.17.72.244:9090/cdc-service/services/CPNotificationService.wsdl";  
             
$options = array('uri'=>$wsdlPath);
//$options = array();

foreach($classmap as $key => $value) 
{
      if(!isset($options['classmap'][$key])) 
      {
        $options['classmap'][$key] = $value;
      }
}
try
{
    $red = 'CLARO_HN';
    //$url = "http://localhost/private/clubs/dlr_club.php";
    $server = new SoapServer($wsdlPath,$options);
    $server->setClass("cdc_soap_notification",$red);
    $server->handle();
    
}catch(Exception $e)
{
    //_log($e->getMessage() . ' in '.$e->getFile().'#'.$e->getLine());
    //$server->fault($e->getCode(), $e->getMessage());
}
?>
