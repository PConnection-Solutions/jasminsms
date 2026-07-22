<?php
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class GenericResponseType 
{
  public $transId; // string
  public $statusCode; // int
  public $statusMessage; // string
}

class SubscriptionResponseType 
{
  public $providerId; // string
  public $originid; // string
  public $msisdn; // string
  public $subscriptionId; // string
}

class ShortMessageType 
{
  public $messageType; // int
  public $messageHeader; // string
  public $messageBody; // string
}

class AuthenticationRequestType 
{
  public $userId; // string
  public $password; // string
  public $moduleNumber; // int
}

class AuthenticationResponseType 
{
  public $statusCode; // int
  public $statusMessage; // string
  public $moduleNumber; // int
  public $rightsTypeId; // int
  public $rightsType; // string
}

class ServiceRequestType 
{
  public $authenticationRequest; // AuthenticationRequestType
  public $pageRequest; // PageRequestType
}

class ServiceResponseType 
{
  public $responseCode; // int
  public $responseMessage; // string
  public $authenticationResponse; // AuthenticationResponseType
  public $pageResponse; // PageResponseType
}

class PageRequestType 
{
  public $pageNumber; // int
}

class PageResponseType 
{
  public $numberOfRecords; // int
  public $totalNumberOfRecords; // int
  public $currentPage; // int
  public $totalPage; // int
}

class DeliverResponseType 
{
  public $statusCode; // int
  public $statusMessage; // string
}

class SubmitResponseType 
{
  public $transId; // string
  public $statusCode; // int
  public $statusMessage; // string
}

class DeliverContentResponseType 
{
  public $chargeFlag; // string
  public $statusCode; // int
  public $statusMessage; // string
  public $responseMessage; // string
}

class bonusChrgDate 
{
  public $bonusChrgFromDate; // date
  public $bonusChrgToDate; // date
}

class bonusChrgDates 
{
  public $bonusChrgDate; // bonusChrgDate
}

class bonusChrgDetail 
{
  public $bonusChrgType; // int
  public $bonusChrgRetries; // int
  public $bonusChrgDates; // bonusChrgDates
}

class tariffDetail 
{
  public $providerId; // string
  public $applicationId; // int
  public $serviceId; // int
  public $originId; // int
}

class tariff 
{
  public $tariffId; // int
  public $tariffDetail; // tariffDetail
  public $isSubscription; // string
  public $bonusChrgDetails; // bonusChrgDetail
}

class tariffList 
{
  public $tariff; // tariff
}

class updateAllTariffRequestType 
{
  public $tariffList; // tariffList
}

class deliveryNotificationRequest 
{
  public $transId; // string
  public $messageId; // string
  public $from; // string
  public $to; // string
  public $status; // int
  public $tariffId; // int
  public $price; // double
  public $errorCode; // int
  public $errorMessage; // string
}

class sendCPNotificationRequest 
{
  public $subscriptionId; // string
  public $msisdn; // string
  public $shortcode; // string
  public $packageId; // string
  public $tariffId; // string
  public $price; // double
  public $operationDescription; // operationDescription
  public $notifyTo; // notifyTo
  public $errorCode; // int
  public $errorMessage; // string
}

class operationDescription 
{
}

class notifyTo 
{
}

class deliveryNotificationResponse 
{
  public $genericResponse; // GenericResponseType
}

class sendCPNotificationResponse 
{
  public $genericResponse; // GenericResponseType
}

class deliverSMSRequest
{
    public $transId; //string
    public $From; //string
    public $To; //string
    public $Suffix; //string
    public $requestDtime; //string
    public $messageBody; //string
    
}

class cdc_soap_server
{
    public $auth;
    private $red;
    function cdc_soap_server($red)
    {
        $this->red = $red;
    } 
    function deliverSMS(deliverSMSRequest $deliverSMSRequest)
    {
        $deliverSMSRequest->red = $this->red;
        
        $connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();
        $channel->queue_declare('cdc_deliversms', false, true, false, false);
        $data = json_encode(array($deliverSMSRequest));

        $msg = new AMQPMessage($data, array('delivery_mode' => 2) );
        
        
        $channel->basic_publish($msg, '', 'cdc_deliversms');
        
        $channel->close();
        $connection->close();
        $ns = 'http://type.ws.cdc.tecnotree.com/CDCSMSType';
        
        //Respuesta del metodo
        $response = new DeliverResponseType();
        $response->statusCode = new SoapVar(0, XSD_INT, null, null, null, $ns);
        $response->statusMessage = new SOAPVar('Success', XSD_STRING, null, null, null, $ns);
        
        return array('deliverResponse'=>$response);
        
    }
    function Security($headerData)
    {
        return true;
        
    }
    
}
class cdc_soap_notification
{

    
    public $red;
    function cdc_soap_notification($red)
    {
        $this->red = $red;
    }
    function deliveryNotification($deliveryNotificationRequest)
    {
        /*
            “I”   - Invalid MT 
            “M”  - On demand MT Message 
            “S”   - Subscription MT message 
            “W”  - Web MT message 
        */
        /*
        $indice_noti = substr($deliveryNotificationRequest->messageId,0,1);
        
        $transId = $deliveryNotificationRequest->transId; // string
        
        $messageId = substr($deliveryNotificationRequest->messageId,1); // string
        
        $from = $deliveryNotificationRequest->from; // string
        $to = $deliveryNotificationRequest->to; // string
        $status = $deliveryNotificationRequest->status; // int
        $tariffId = $deliveryNotificationRequest->tariffId; // int
        $price = $deliveryNotificationRequest->price; // double
        $errorCode = $deliveryNotificationRequest->errorCode; // int
        $errorMessage = $deliveryNotificationRequest->errorMessage; // string
        
        $indice = strrpos ($messageId,'-') +1 ;
        $id_club = substr($messageId,$indice); 
        */
        
        //"?destination=$to&source=$from&myid=$messageId&id_club=$id_club&recobro=1&data=";        
        ///////////////////
        //DebugBreak();
        $deliveryNotificationRequest->red = $this->red;
        $transId = $deliveryNotificationRequest->transId;
        
        $connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();
        //$channel->queue_declare('cdc_notification', false, true, false, false);
        $channel->queue_declare($this->red, false, true, false, false);
        $data = json_encode(array($deliveryNotificationRequest));

        $msg = new AMQPMessage($data, array('delivery_mode' => 2) );
        
        
        //$channel->basic_publish($msg, '', 'cdc_notification');
        $channel->basic_publish($msg, '', $this->red);
        $channel->close();
        $connection->close();

        
        $response = new GenericResponseType();
        $response->transId = $transId;
        $response->statusCode = 0;
        $response->statusMessage = 'Request accepted successfully';
        return array('genericResponse'=>$response);
    }
    function Security($headerData)
    {
        return true;
        
    }
}
?>
