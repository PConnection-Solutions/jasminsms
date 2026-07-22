<?php
    class genericResponse extends stdClass
    {
          public $transId;
          public $statusCode;
          public $statusMessage;
          public function genericResponse($ns,$transId='',$statusCode=0,$statusMessage='')
          {
            $this->transId = new SoapVar($transId,XSD_STRING,null,null,null,$ns);
            $this->statusCode = new SoapVar($statusCode,XSD_INT,null,null,null,$ns);
            $statusMessage = $statusCode == 0 ?'Success':$statusMessage;
            $this->statusMessage = new SoapVar($statusMessage,XSD_STRING,null,null,null,$ns);
            //parent::stdClass();
              
          }
          
      }
    class shortMessage extends stdClass
    {
          public $messageType;
          public $messageHeader;
          public $messageBody;
          
          public function shortMessage($messageBody='',$messageType=0,$messageHeader='')
          {
            $ns='http://type.ws.cdc.tecnotree.com/CDCCommonType';
            //$this->messageType = new SoapVar($messageType,XSD_INT,null,null,null,$ns);//0-texto normal, 245 Binario, 8 UCS2 y 3-ASCII extended(latin 11)
            $this->messageType = new SoapVar($messageType,XSD_STRING,null,null,null,$ns);
            $this->messageHeader = new SoapVar($messageHeader, XSD_STRING, null, null, null, $ns);//string(40)
            $this->messageBody = new SoapVar($messageBody, XSD_STRING, null, null, null, $ns); //String(1040)
            //parent::stdClass();
          }
          
      }
    class deliverResponse extends stdClass
    {
          private $ns;
          public $statusCode;
          public $statusMessage;
          
          public function deliverResponse($ns,$statusCode=0,$statusMessage='')
          {
            $this->ns = $ns;
            $this->statusCode = new SoapVar($statusCode,XSD_INT,null,null,null,$ns);
            $statusMessage = ($statusCode == 0) || ($statusCode == 1000)  ?'Success':$statusMessage;
            $this->statusMessage = new SoapVar($statusMessage,XSD_STRING,null,null,null,$ns);
            //parent::stdClass();
              
          }
      }
    class submitResponse extends stdClass
    {
          public $transId;
          public $statusCode;
          public $statusMessage;
          
          public function submitResponse($ns,$transId='',$statusCode=0,$statusMessage='')
          {
            $this->ns = $ns;
            $this->transId = new SoapVar($transId,XSD_STRING,null,null,null,$ns);
            $this->statusCode = new SoapVar($statusCode,XSD_INT,null,null,null,$ns);
            $statusMessage = $statusCode == 0 ?'Success':$statusMessage;
            $this->statusMessage = new SoapVar($statusMessage,XSD_STRING,null,null,null,$ns);
            //parent::stdClass();
          }
      }
    class credencials extends stdClass
    {
        public $username;
        public $Password;
        public function credencials ($ns,$username,$password)
        {
            $this->username = new SoapVar($username,XSD_STRING,null,null,null,$ns);
            $this->Password = new SoapVar($password,XSD_STRING,null,null,null,$ns);
            parent::stdClass();
        }
    }
    class submitBulkSMS extends stdClass
    {
        public $messageId;
        public $providerId;
        public $shortCode;
        public $packageId;
        public $message;
        public function submitBulkSMS($messageId,$providerId,$shortCode,$packageId,$message)
        {
            $ns = 'http://type.ws.cdc.tecnotree.com/SBRType';
            $this->messageId = new SoapVar($messageId,XSD_STRING,null,null,null,$ns);
            $this->providerId = new SoapVar($providerId,XSD_INT,null,null,null,$ns);
            $this->shortCode = new SoapVar($shortCode,XSD_STRING,null,null,null,$ns);
            $this->packageId = new SoapVar($packageId,XSD_INT,null,null,null,$ns);
            
            $msg = new shortMessage($message);
            $this->message = new SoapVar($msg,SOAP_ENC_OBJECT,null,null,null,$ns);
            //$resultado = var_dump($this->message);
            //error_log("Resultado: " . $resultado . "\n", 3, "/var/log/CDC/enviar_bc.log");
            //parent::stdClass();
            
        }
    }    
    class authCDCData extends stdClass
    {
        public $Username;
        public $Password;
        public $headers;
        public function authCDCData($username,$password)
        {
            $ns = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
            $token = new stdClass();
            $token->Username = new SOAPVar($username, XSD_STRING, null, null, null, $ns);
            $token->Password = new SOAPVar($password, XSD_STRING, null, null, null, $ns);
            
            $this->Username = $token->Username;
            $this->Password = $token->Password;
            
            $ws = new stdClass();
            $ws->UsernameToken =  new SoapVar($token, SOAP_ENC_OBJECT, null, null, null, $ns);
            
            $this->headers = new SOAPHeader($ns, 'Security', $ws, true);
        }
        
    }
    class smsRequest extends stdClass
    {
            public $transId;
            public $messageId;
            public $from;
            public $to;
            public $suffix;
            public $requestDtime;
            public $message;
            
        public function smsRequest($transId,$messageId,$from,$to,$suffix,$message)
        {
            $ns = 'http://type.ws.cdc.tecnotree.com/CDCSMSType';
            $fecha = $this->return_fecha();
            
            $this->transId = new SoapVar($transId,XSD_STRING,null,null,null,$ns);
            $this->messageId = new SoapVar($messageId,XSD_STRING,null,null,null,$ns);
            $this->from = new SoapVar($from,XSD_STRING,null,null,null,$ns);
            $this->to = new SoapVar($to,XSD_STRING,null,null,null,$ns);
            $this->suffix = new SoapVar($suffix,XSD_STRING,null,null,null,$ns);
            $this->requestDtime = new SoapVar($fecha,XSD_STRING,null,null,null,$ns);
            
            $ns1='http://type.ws.cdc.tecnotree.com/CDCCommonType';
            
            $msg = new shortMessage($message);
            $this->message = new SoapVar($msg,SOAP_ENC_OBJECT,null,null,null,$ns);
            //*/
            //parent::stdClass();
            
        }
        private function return_fecha()
        {
            $format_date = date("YmdGis");
            return $format_date;
        }
    }
    class smsNotificationLog
    {
        private $conn;
        public function smsNotificationLog($conn)
        {
 
            $this->conn = $conn;
            
        }
        public function appendData($data)
        {
            $transId = isset($data->transId) ? $data->transId : 'NO DATA'; // string
            $messageId = isset($data->messageId) ? $data->messageId : 'NO DATA' ; // string
            $from = isset($data->from) ? $data->from: 'NO DATA' ; // string
            $to = isset($data->to) ? $data->to : 'NO DATA' ; // string
            $status = isset($data->status) ? $data->status : -1 ; // int
            $tariffId = isset($data->tariffId) ? $data->tariffId : -1 ; // int
            $price = isset($data->price) ? $data->price : 0 ; // double
            $errorCode = isset($data->errorCode) ? $data->errorCode : -1; // int
            $errorMessage = isset($data->errorMessage) ? $data->errorMessage: ''  ; // string
            $red = $data->red;

            ### Validación de VALLAS LOTO ###

            $queryVallas = "SELECT * FROM lotelsa.`vallas_VALLAS` WHERE msisdn = '$to'";
            $resultVallas = mysql_query($queryVallas, $this->conn); 

            $cantidad = mysql_num_rows($resultVallas);

            if($cantidad > 0) {

                if($errorCode == '-1') {

                    $errorCode2 = 0;
                    $errorMessage2 = 'Entregado';

                } else {

                    $errorCode2 = $errorCode;
                    $errorMessage2 = $errorMessage;

                }

                $queryData = "SELECT * FROM lotelsa.vallas_DLR WHERE idmensaje = '$messageId'";
                $resultData = mysql_query($queryData, $this->conn);

                while ($fila = mysql_fetch_assoc($resultData)) {

                    $cod_agente = $fila['cod_agente'];
                    $descripcion = $fila['descripcion'];
                    $texto = $fila['texto'];
                    $usuario = $fila['usuario'];

                    $query = "INSERT INTO lotelsa.vallas_DLR (idmensaje, msisdn, origen, status, mensaje, fecha, red, cod_agente, descripcion, texto, usuario) VALUES ('$messageId', '$to', '$from', '$errorCode2', '$errorMessage2', CONVERT_TZ(NOW(), 'UTC', 'America/Tegucigalpa'), '$red', '$cod_agente', '$descripcion', '$texto', '$usuario')";
                    $result = mysql_query($query, $this->conn);

                }

            }

            $sql = "insert into clubs_CDC_NOTIFICATION (transId,messageId,from_,to_,status_,tariffId,price,errorCode,errorMessage,red)".
                    " values ('$transId','$messageId','$from','$to',$status,$tariffId,$price,$errorCode,'$errorMessage','$red')";
                    
            return mysql_query($sql,$this->conn);
        }
    }
?>
