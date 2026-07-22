<?php
class kannel
{
    private $conn;
    public $kannel_data;
    public $url_kannel;
    private $timeout;
    private $id_clubs;
    
    public function __construct($conn,$clubs_data,$timeout=2)
    {
        $this->conn = $conn;
        $this->id_clubs = $clubs_data['id_club'];
        $id_carrier = $clubs_data['id_carrier'];
        $this->timeout = $timeout;
        $this->kannel_data = $this->get_kannel_conf_data($id_carrier);
        if($this->kannel_data)
        {
            $this->url_kannel = 'http://'.$this->kannel_data['host'].'/cgi-bin/sendsms?';
            
            $add_ampersand = false;
            foreach ($this->kannel_data as $key => $value)
            {
                if (($key != 'host') && ($key != 'dlr_url') && ($key != 'smsc2'))
                {
                    if($add_ampersand)
                    {
                        $this->url_kannel .= '&';
                    }
                    $this->url_kannel .= $key . '=' .urlencode($value);
                    $add_ampersand = true;
                }
            }
        }
        
    }
    private function get_kannel_conf_data($id_carrier)
    {
        $sql = "select username,password,smsc,dlr_url,host,smsc2 from clubs_KANNEL where id_carrier=$id_carrier";
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
    public function enviar($to,$from,$message,$enable_dlr=false,$mclass=1,$prefix_binfo=null)
    {
        $prefix_url = '';
        $dlr_mask = $enable_dlr ? 31 : 0;
        $last_id = $this->unique_id();
        $id_club = $this->id_clubs;
        $url_dlr = $this->kannel_data['dlr_url'] . '?destination=%p&source=%P&meta=%a&myid='.$last_id . '&id_club='.$id_club . '&recobro=1';
        $kannel_info = array('to'       => $to,
                            'from'      => $from,
                            'dlr-mask'  => $dlr_mask,
                            'charset' => 'UTF-8',
                            'text'      => $message,
                            'dlr-url'   => $url_dlr/*,
                            'mclass'    => $mclass*/);
        foreach ($kannel_info as $key => $value)
        {
            $prefix_url .='&'.$key .'='.urlencode($value);
        }
        $options = array( 'http'=>array('method'=>"GET", 
                                        'header'=>"Accept-language: en\r\n", 
                                        'timeout' => $this->timeout 
                                        ) 
                        );
        $context = stream_context_create($options); 
        $result = @file($this->url_kannel . $prefix_url,null,$context);
        return $last_id;
        
    }
    private function unique_id()
    {
        return uniqid(true);
    }
    
}
  
?>
