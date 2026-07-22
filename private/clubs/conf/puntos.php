<?php
class puntos
{
    private $conn;
    public function __construct($conn)
    {
        $this->conn = $conn;
    }
    public function agregar_puntos($id_msisdn,$cantidad=0,$unidad,$id_trans='')
    {
        $descripcion = "CREDITO:$unidad";
        if ($cantidad < 0)
        {
            $cantidad *= -1;
        }
        $sql = "insert into clubs_PUNTOS (id_msisdn,id_transaction,cantidad,descripcion)".
            " values($id_msisdn,'$id_trans',$cantidad,'$descripcion')";
        $response = mysql_query($sql,$this->conn);
        $last_id = mysql_insert_id($this->conn);
        if ($response)
        {
            $this->actualizar_puntos_usuario($id_msisdn,$cantidad);
        }
        return $last_id;
        
    }
    public function disminuir_puntos($id_msisdn,$cantidad=0,$unidad,$id_trans='')
    {
        $descripcion = "DEBITO:$unidad";
        if ($cantidad > 0)
        {
            $cantidad *= -1;
        }
        $sql = "insert into clubs_PUNTOS (id_msisdn,id_transaction,cantidad,descripcion)".
            " values($id_msisdn,'$id_trans',$cantidad,'$descripcion')";
        $response = mysql_query($sql,$this->conn);
        $last_id = mysql_insert_id($this->conn);
        if ($response)
        {
            $this->actualizar_puntos_usuario($id_msisdn,$cantidad);
        }
        return $last_id;
    }
    private function actualizar_puntos_usuario($id_msisdn,$cantidad)
    {
        $sql = "update clubs_MSISDN set points = $cantidad + points where".
                " id_msisdn = $id_msisdn";
        return mysql_query($sql,$this->conn);
        
    }
    public function actualizar_transaccion_id($id_puntos,$id_trans)
    {
        $sql = "update clubs_PUNTOS set id_transaction = '$id_trans' where id_points=$id_puntos";
        return mysql_query($sql,$this->conn);
    }
 
}
  
?>
