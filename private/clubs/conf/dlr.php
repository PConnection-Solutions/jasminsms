<?php
class dlr
{
    private $conn;
    public function __construct($conn)
    {
        $this->conn = $conn;
        
    }
    public function guardar_cobro_parcial($msisdn,$myid,$id_club)
    {
        $sql = "insert into clubs_DLR_COBRO_PARCIAL (id_club,msisdn,myid) values ($id_club,'$msisdn','$myid')";
        return mysql_query($sql,$this->conn);
    }
    public function analizar_registros($respuesta,$msisdn,$myid,$id_club,$enable_cobro_parcial=false)
    {
        /**/
        $puntos = $this->get_points($id_club);
        if (substr($respuesta,0,4) == 'ACK/') //Cobro de Kannel 
        {
            $this->actualizar_cobro($msisdn,$myid,$puntos);
            $this->actualizar_fecha_ultimo_cobro($msisdn);
            //modificar la fecha del ultimo evento cobrado
            
        }else if($respuesta == '0') //Cobro del CDC
        {
            $this->actualizar_cobro($msisdn,$myid,$puntos);
            $this->actualizar_fecha_ultimo_cobro($msisdn);
        }
        else if ($respuesta == 100) //Cobro de beconnected
        {
            $this->actualizar_cobro($msisdn,$myid,$puntos);
            $this->actualizar_fecha_ultimo_cobro($msisdn);
            //modificar la fecha del ultimo evento cobrado
            
        }else if ($enable_cobro_parcial)
        {
            /*
                Aqui se encuentra los numeros que no han sido cobrado por una u otra 
                razon. En el caso del kannel todos 
            */
            $error_str = substr($respuesta,0,9);
            switch($error_str)
            {
                case 'NACK/1060':
                case 'NACK/1061':
                case 'NACK/1066':
                case 'NACK/1201':
                case 'NACK/1202':
                case 'NACK/1203':
                case 'NACK/1204':
                case 'NACK/1207':
                case 'NACK/1208':
                case 'NACK/1299':
                case 'NACK/1080':
                //case 'NACK/11/I': //Esto es para pruebas
                //case 'NACK/1018':
                case '400'://Beconnected
                case '300'://Beconnected
                case '205'://CDC
                case '210'://CDC
                case '217'://CDC
                    //$this->guardar_cobro_parcial($msisdn,$myid,$id_club);
                    break;
                default://En caso que se requiera cobrar todo quitar el comentario siguiente
                    //$this->guardar_cobro_parcial($msisdn,$myid,$id_club);
                 
                
            }
        }
    }
    private function actualizar_cobro ($msisdn,$myid,$puntos=0)
    {
        $sql = "update clubs_ENVIOS_DIARIOS set eventos_cobrados = eventos_cobrados + 1,puntos = $puntos".
                " where msisdn='$msisdn' and id_deliver = '$myid'";
        return mysql_query($sql,$this->conn);
        
    }
    private function actualizar_fecha_ultimo_cobro($msisdn)
    {
        /*
            Esta funcion supone que no se repiten numeros entre las mismas redes, es decir que incluyen el numero de pais 
            ejemplo :505888...en caso que el numero no incluya el codigo de pais, el resultado es impredecible ya que puede actualizar
            datos de distintas redes en distintos paises...
        */
        $sql = "update clubs_MSISDN set fecha_ult_evento=DATE(CONVERT_TZ(curdate(), 'UTC', 'America/Tegucigalpa')) where msisdn= '$msisdn'";
        //return mysql_query($sql,$this->conn);
    }
    private function get_points($id_club)
    {
        $sql = "select ptos_inicio,enable_points from clubs_SERVICE where id_club=$id_club";
        $response = mysql_query($sql,$this->conn);
        $puntos = 0;
        if (mysql_num_rows($response))
        {
            $response = mysql_fetch_array($response);
            if ($response['enable_points'] == 'enable')
            {
                $puntos = $response['ptos_inicio'];
                
            }
            else
            {
                $puntos = 0;
            }
            
        }
        return $puntos;
        
    }
    
}
  
?>
