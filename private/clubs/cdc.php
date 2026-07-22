<?php
    date_default_timezone_set('America/Tegucigalpa');
    /*
        $ns_sbr = 'http://type.ws.cdc.tecnotree.com/CDCWebRequestType';
        $ns_sbr = 'http://type.ws.cdc.tecnotree.com/CDCCommonType';
        $ns_sbr = 'http://type.ws.cdc.tecnotree.com/CDCNotificationType';
        $ns_sbr = 'http://type.ws.cdc.tecnotree.com/CDCSMSType';
        $ns_sbr = 'http://type.ws.cdc.tecnotree.com/CDCWebRequestType';
        $ns_sbr = 'http://type.ws.cdc.tecnotree.com/SBRType';
    */
    class cdc
    {
        private $conn;
        public $data_cdc;
        public $data_cdc_club;
        private $client_bulk;
        private $client_send;
        private $clubs_data;
        public function cdc($conn,$clubs_data,$timeout=60)
        {
            $this->conn = $conn;
            $this->clubs_data = $clubs_data;
            
            $this->data_cdc = $this->get_data_cdc($clubs_data['id_carrier']);
            $this->data_cdc_club = $this->get_data_cdc_club($clubs_data['id_club']);

            if ($this->data_cdc)
            {
              $ns_bulk = $this->data_cdc['bulk_requ'];
              $ns_send = $this->data_cdc['cp_sms'];
              $user = $this->data_cdc['user'];
              $pass = $this->data_cdc['password'];
              try
              {
                $client_bulk = new SoapClient($ns_bulk . '?wsdl',
                                        array('location' => $ns_bulk,
                                        'trace' => 1,
                                        'cache_wsdl' => WSDL_CACHE_NONE,
                                        'connection_timeout' => $timeout)
                                        );
                $data_headers = new authCDCData($user,$pass);
                $client_bulk->__setSOAPHeaders($data_headers->headers);
                $this->client_bulk = $client_bulk;
                  
              } catch(Exception $e)
              {
                  $error = $e->getMessage();
                  echo $error;
                  //$error = $fault->code;
                  
              }
              try
              {
                $client_send = new SoapClient($ns_send . '?wsdl',
                                array('location' => $ns_send,
                                'trace' => 1,
                                'cache_wsdl' => WSDL_CACHE_NONE,
                                'connection_timeout' => $timeout,
                                )
                                );
                $data_headers = new authCDCData($user,$pass);
                $client_send->__setSOAPHeaders($data_headers->headers);
                $this->client_send = $client_send;
                  
              } catch(Exception $e)
              {
                  $error = $e->getMessage();
                  echo $error;
                  //$error = $fault->code;
                  
              }
          }

        }
        private function get_data_cdc($id_carrier)
        {
          $sql = "select * from clubs_CDC where id_carrier = $id_carrier";
          $response = mysql_query($sql,$this->conn);
          if (mysql_num_rows($response))
          {
              return mysql_fetch_array($response);
          }
          else
          {
              return false;
          }
        }
        private function unique_id()
        {
            return uniqid(true);
        }
        public function enviar($phone,$sc,$message,$enable_dlr=false,$mclass=1,$prefix_binfo='')
        {

            $id_club = $this->clubs_data['id_club'];
            //$suffix = $this->clubs_data['name_club'];
            $suffix = '';
            $transid = $this->clubs_data['transId'];
            
            //$ns_sbr = 'http://type.ws.cdc.tecnotree.com/CDCSMSType';
            $last_id = $this->unique_id() . "-$id_club";
            
            if ($enable_dlr == false)
            {
//		$sc = $sc;
                if ($sc == '2589'){
                }else{    
                $sc = $sc ."1";//Estas son las cuentas de BC con 1 al final del corto
                $transid = '';
                }
            }
            
            $submitSMSRequest = new smsRequest($transid,$last_id,$sc,$phone,$suffix,$message);
            
            try
            {
                $result = $this->client_send->submitSMS($submitSMSRequest);
                //return $result;
                
                return $last_id;
            }
            catch(Exception $e)
            {
                $error = $e->getMessage();
                //echo "REQUEST:\n" . htmlentities($this->client_send->__getLastRequest()) . "\n\n\n";
                //echo "RESPONSE:\n" . htmlentities($this->client_send->__getLastResponse()) . "\n";
                return false;
            }
            
        }
        public function enviar_bc($sc,$message,$last_id='')
        {
            //$ns_sbr = 'http://type.ws.cdc.tecnotree.com/SBRType';
            $last_id = $last_id ? $last_id : $this->unique_id()."-$id_club";
            //$last_id = $this->unique_id()."-$id_club";
            $providerId = $this->data_cdc['providerId'];
            $packageId = $this->data_cdc_club['packageId'];
            
            $submitBulkSMSRequest = new submitBulkSMS($last_id,$providerId,$sc,$packageId,utf8_encode($message));
            try
            {
                $result = $this->client_bulk->submitBulkSMS($submitBulkSMSRequest);
                //error_log("Resultado: $result\n", 3, "/var/log/CDC/enviar_bc.log");
                return $result;
                //return $last_id;
            }
            catch(Exception $e)
            {
                $error = $e->getMessage();
            }
            
        }
        public function id_envio()
        {
            $id_club = $this->clubs_data['id_club'];
            $last_id = $this->unique_id() . "-$id_club";
            return $last_id;
            
        }
        private function get_data_cdc_club($id_club)
        {
            $sql = "select * from clubs_CDC_CONF where id_club = $id_club";
            $response = mysql_query($sql,$this->conn);
            if (mysql_num_rows($response))
            {
                return mysql_fetch_array($response);
            }
            else
            {
                return false;
            }
        }
    }
?>
