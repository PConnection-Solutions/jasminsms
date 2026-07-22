<?php
class acelerador
{
    private $conn;
    public function __construct($conn)
    {
        $this->conn = $conn;
        
    }
    public function get_acce_data($id_club,$fecha,$hora)
    {
        $sql ="SELECT * FROM clubs_ACELERADOR_PROGRAM".
        " where clubs_ACELERADOR_PROGRAM.fecha = '$fecha' and clubs_ACELERADOR_PROGRAM.hora = '$hora' and clubs_ACELERADOR_PROGRAM.`status` = 'pendiente'".
        " and clubs_ACELERADOR_PROGRAM.id_club = $id_club limit 1";
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
    public function cancel_acce_data($id_acce_conf)
    {
        $sql ="update clubs_ACELERADOR_PROGRAM set status = 'cancelado' where id_acce_conf = $id_acce_conf";
        return mysql_query($sql,$this->conn);
        
    }
    public function execute_acce_data($id_acce_conf)
    {
        $sql ="update clubs_ACELERADOR_PROGRAM set status = 'ejecutado', fecha_ejecucion = CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa') where id_acce_conf = $id_acce_conf";
        return mysql_query($sql,$this->conn);
        
    }
    
}  
?>
