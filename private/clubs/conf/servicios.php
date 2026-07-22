<?php
class service_conf
{
    private $conn;
    
    private $ruta;
    private $shortcode;
    private $prefix;
    public $service_data;
    
    function __construct($conn,$ruta,$shortcode,$prefix)
    {
        $this->conn = $conn;
        $this->ruta = $ruta;
        $this->shortcode = $shortcode;
        $this->prefix = $prefix;
        
        $this->service_data = $this->get_service();
    }
    private function get_service()
    {
        $ruta = $this->ruta;
        $shortcode = $this->shortcode;
        $prefix = $this->prefix;
        
        $sql = "select * from clubs_SERVICE where route = '$ruta' and" .
        " (sc_mo = '$shortcode' or sc_mo_adicional = '$shortcode' or sc_cobro = '$shortcode' or".
        " sc_cobro1 = '$shortcode')";
        
        $response = mysql_query($sql,$this->conn);
        
        $rows_count = mysql_num_rows($response);
        
        if ($rows_count)
        {
            if ($rows_count == 1)
            {
                return mysql_fetch_array($response);
            }
            else
            {
                $word = array();
                while($row = mysql_fetch_array($response))
                {
                    $word[$row['name_club']] = $row;
                }
                $id; //Es el id del registro
                $shortest = -1;
                $near_word;
                
                foreach($word as $key => $values)
                {
                    $lev = levenshtein($prefix,$key);
                    if ($lev == 0 )
                    {
                        $shortest = 0;
                        $near_word = $key;
                        $id = $values;
                        break; //Quiebre del foreach
                    }
                    
                    if ($lev <= $shortest || $shortest < 0)
                    {
                        $near_word = $key;
                        $id = $values;
                        $shortest = $lev;
                    }
                    
                }
                return $id;
            }
            
        }
        else
        {
            return false;
        }
    }

}  
?>
