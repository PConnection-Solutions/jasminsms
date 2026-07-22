<?php
class user_log
{
    private $conn;
    function __construct($conn)
    {
        $this->conn = $conn;
    }
    public function guardar_log_mo($msisdn,$texto,$shortcode,$ruta)
    {
        //$texto = is_string($texto) ? $texto : '';
        
        $sql = "insert into clubs_USER_MO (msisdn,sc,comentario,fecha,payload) value('$msisdn','$shortcode','$ruta',CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa'),'$texto')";
        $result = mysql_query($sql,$this->conn);
        if ($result)
        {
            return mysql_insert_id($this->conn);
        }
        else
        {
            return false;
        }
    }
    public function actualizar_log($id_log,$id_deliver)
    {
        $sql = "update clubs_USER_MO set id_deliver='$id_deliver' where id_mo=$id_log";
        $result = mysql_query($sql,$this->conn);
    }
    public function mos($msisdn,$texto,$shortcode,$ruta,$transid)
    {
	$sql = "insert into clubs_MOS (msisdn,sc,comentario,fecha,payload,id_deliver) value('$msisdn','$shortcode','$ruta',CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa'),'$texto','$transid')";
        $result = mysql_query($sql,$this->conn);
        if ($result)
        {
            return mysql_insert_id($this->conn);
        }
        else
        {
            return false;
        }
    }
    
}
  
?>
