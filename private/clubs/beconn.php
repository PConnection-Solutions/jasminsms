<?php
class beconn
{
    //200.62.74.82:9001 NI
    //10.199.201.30:9001 HN
    //ssh -L 9001:10.199.201.30:9001 pconnection@50.62.143.121
    
    const SD = 2000; //Envios de 30K msisdn
    const DD = 1500;  //Envios personalizados de contenidos de 1.5K de msisdn y 1.5 de mensajes
    
    //const SD = 4; //Envios de 30K msisdn
    //const DD = 2;  //Envios personalizados de contenidos de 1.5K
    
    private $conn;
    public $beconn_data,$id_club,$last_unique_id;
    private $timeout,$return_id_array;
    private $url_beconn,$param,$count_msisdn,$msg,$from,$destination;
    private $number_array;
    public function __construct ($conn,$clubs_data,$timeout=1200)
    {
        $this->conn = $conn;
        $this->id_club = $clubs_data['id_club'];
        $this->beconn_data = $this->get_beconn_conf_data($this->id_club);
        $this->timeout = $timeout;
        $this->count_msisdn = 0;
        $this->return_id_array = array();
        $this->number_array = array();
        if ($this->beconn_data)
        {
            $this->url_beconn = $this->beconn_data['host'];
            
            $add_ampersand = false;
            foreach ($this->beconn_data as $key => $value)
            {
                if (($key != 'host') && ($key != 'UrlNotification') && ($key != 'IdRate2'))
                {
                    //User,Password,IdRate,IdRate2,UrlNotification,host
                    if($add_ampersand)
                    {
                        $this->param .= '&';
                    }
                    $this->param .= $key . '=' .urlencode($value);
                    $add_ampersand = true;
                }
            }
            $this->param .="&Type=SMS";
            
        }
    } 
    private function get_beconn_conf_data($id_clubs)
    {
        $sql = "select User,Password,IdRate,IdRate2,UrlNotification,host from clubs_BECONN where id_clubs=$id_clubs";
        $response = mysql_query($sql,$this->conn);
        if (mysql_num_rows($response))
        {
            return mysql_fetch_assoc($response);
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
    public function enviar($to,$from,$message,$enable_dlr=true,$encode_msg=true,$last_unique_id=null)
    {
        /*Envia un solo mensaje*/
        $params='';
        $last_id = !$last_unique_id ? $this->unique_id(): $last_unique_id;
        $id_club = $this->id_club;
        //myid='.$last_id
        $url_dlr = $this->beconn_data['UrlNotification'] ."?source=$from&id_club=$id_club&recobro=1&myid=$last_id";
        //$url_dlr = $this->beconn_data['UrlNotification'];
        
        $bconn_info = array('Origin'    =>  $from,
                            'ContentId' =>  $last_id,
                            'Destination' => $to,
                            //'Msg' => $message,
                            'UrlNotification' =>$url_dlr);
        //Creamos la cadena con los valores a enviar en el post
        foreach ($bconn_info as $key => $value)
        {
            $params .='&'.$key .'='.urlencode($value);
        }
        //Verificamos si codificamos el msg
        if ($encode_msg)
        {
            $params .= '&Msg='.urlencode($message);
        }
        else
        {
            $params .= '&Msg='.$message;
            
        }
        $id_beconn_send = $this->send_message($this->param . $params);
        if ($id_beconn_send != false)
        {
            //Creamos un arreglo donde la key es el id de beconn y el valor el id personalizado
            $return_id_array = array();
            $return_id_array[$id_beconn_send] = $last_id;
            return $return_id_array;
        }
        else
        {
            return false;
        }

        
        
    }
    public function enviar_lista($to,$from,$message,$enable_dlr=true,$type_bc=true,$my_id='')
    {
        /*Envia multiples mensajes*/
        //Agregamos los numeros
        $this->from = $from;
        $this->number_array[]=$to;
        //!$this->destination ? $this->destination = $to:$this->destination .=",$to";
        $this->last_unique_id = $my_id;
        //!$this->last_unique_id ? $this->last_unique_id = $this->unique_id() : $this->last_unique_id; 
        //Generamos un contador de numeros
        $this->count_msisdn++;
        $this->msg = $message;
        /*
        if (!$type_bc)
        {
            !$this->msg ? $this->msg = urlencode($message):$this->msg .="&Msg=".urlencode($message);
        }
        else
        {
            $this->msg = $message;
        }*/
        if (($this->count_msisdn == self::SD) && ($type_bc))
        //if ((($this->count_msisdn == self::DD) && (!$type_bc)) || (($this->count_msisdn == self::SD) && ($type_bc)))
        {
            $this->destination = implode( ',', $this->number_array );
            $return_id_array = $this->enviar($this->destination,$this->from,$this->msg,$enable_dlr,false,$this->last_unique_id);
            if ($return_id_array != false)
            {
                $this->return_id_array = array_merge($this->return_id_array,$return_id_array);
                $last_id = $this->last_unique_id;
                //reseteamos las variables
                
            }
            $this->reset_variables();
            return $last_id;

        }
        return $this->last_unique_id;
    }
    private function send_message($params)
    {
        $ch = curl_init($this->url_beconn ."/Tx");
        curl_setopt($ch,CURLOPT_HEADER,true);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt ($ch, CURLOPT_POST, true);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, $this->timeout);
        //curl_setopt($ch,CURLOPT_TIMEOUT, 3);
        $response = curl_exec ($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close ($ch);
        if ($response)
        {
            $header = substr($response, 0, $header_size);
            $headers_response = $this->get_header($header);
            if ($headers_response["X-BE-STATUS"]=="OK")
              {
                  //La peticion esta en procesamiento por el servidor de Claro
                  if ($headers_response["X-BE-DESCRIPTION"]=="OK")
                  {
                      //Pasaron todos los controles y la peticion esta en procesamiento
                      return $headers_response["X-BE-DELIVER_ID"];
                  }
                  else
                  {
                      //No pasaron todos los controles, guardar el mensaje de descripcion
                      return $headers_response["X-BE-DESCRIPTION"];
                  }
              }
        }
        else
        {
            //return false;
            return $this->unique_id();
        }
    }
    private function get_header($response)
    {
        /*Esta funcion obtiene el encabezado personalizado que devuelve la plataforma de Beconn
        y genera un arreglo en base a esta respuesta*/
        $headers = array();
        foreach (explode("\r\n", $response) as $i => $line)
        if ($i === 0)
            $headers['http_code'] = $line;
        else
        {
            list ($key, $value) = explode(': ', $line);
            $headers[$key] = $value;
        }
        return $headers;
    }
    public function forzar_envio_lista()
    {
        $this->destination = implode( ',', $this->number_array );
        if($this->destination)
        {
            $return_id_array = $this->enviar($this->destination,$this->from,$this->msg,true,false,$this->last_unique_id);
            //$this->return_id_array = array_merge($this->return_id_array,$return_id_array);
            $this->reset_variables();
            //$this->return_id_array = array();
            return $return_id_array;
        }
        
    }
    private function reset_variables()
    {
        //reseteamos las variables
        $this->count_msisdn = 0;
        $this->destination ='';
        $this->msg = '';
        //$this->last_unique_id = '';
        $this->number_array = array();
        //fin del reset de las variables
    
    }
    public function format_id($array)
    {
        $return_array = array();
        if (($array !=false)&&(is_array($array)))
        {
            //Solamente un registro
            foreach($array as $key=>$value)
            {
                $return_array['id_beconn'] = $key;
                $return_array['id_custom'] = $value;
            }
            return $return_array;
            
        }
        else if (($array !=false)&&(!is_array($array)))
        {
            $return_array['id_beconn'] = '';
            $return_array['id_custom'] = $array;
            return $return_array;
        }else
        {
            return false;
        }
    }

}
  
?>
