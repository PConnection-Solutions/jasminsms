<?php
class envios_mt
{
    private $conn;
    public function __construct($conn)
    {
        $this->conn = $conn;
    }
    public function insertar_envios_mt($id_club,$id_msisdn,$msisdn,$puntos=0,$id_deliver,$id_detalle)
    {
        $sql = "insert into clubs_ENVIOS_DIARIOS (id_clubs,id_msisdn,fecha,puntos,id_deliver,msisdn,id_detalle_service)".
            " values($id_club,$id_msisdn,DATE(CONVERT_TZ(curdate(), 'UTC', 'America/Tegucigalpa')),$puntos,'$id_deliver','$msisdn',$id_detalle)";
        return mysql_query($sql,$this->conn);
    }
    public function insertar_masivo($id_club,$puntos=0,$id_deliver,$id_detalle)
    {
        $sql = "insert into clubs_ENVIOS_DIARIOS (id_clubs,id_msisdn,fecha,msisdn,puntos,id_deliver,id_detalle_service)".
            " SELECT $id_club,clubs_MSISDN.id_msisdn,DATE(CONVERT_TZ(curdate(), 'UTC', 'America/Tegucigalpa')), clubs_MSISDN.msisdn,$puntos,'$id_deliver',$id_detalle".
            " FROM clubs_MSISDN WHERE clubs_MSISDN.id_clubs = $id_club  AND clubs_MSISDN.state = 'enable' ";
        return mysql_query($sql,$this->conn);
    }
    public function insertar_masivo_recobro($id_club,$puntos=0,$id_deliver,$id_detalle,$cobros_diarios)
    {
        $sql ="insert into clubs_ENVIOS_DIARIOS (id_clubs,id_msisdn,fecha,msisdn,puntos,id_deliver,id_detalle_service)".
                " select $id_club,A.id_msisdn,DATE(CONVERT_TZ(curdate(), 'UTC', 'America/Tegucigalpa')),B.msisdn,$puntos,'$id_deliver',$id_detalle from". 
                " ((SELECT clubs_ENVIOS_DIARIOS.id_msisdn AS id_msisdn,".
                "Sum(clubs_ENVIOS_DIARIOS.eventos_cobrados) AS eventos_cobrados".
                " from `clubs_ENVIOS_DIARIOS`".
                " where ((`clubs_ENVIOS_DIARIOS`.`id_clubs` = $id_club) and (`clubs_ENVIOS_DIARIOS`.`fecha` = DATE(CONVERT_TZ(curdate(), 'UTC', 'America/Tegucigalpa')))". 
                " and (`clubs_ENVIOS_DIARIOS`.`id_msisdn` is not null))".
                " group by `clubs_ENVIOS_DIARIOS`.`id_msisdn`) as A". 
                " INNER JOIN". 
                " (select clubs_MSISDN.id_msisdn,clubs_MSISDN.msisdn,DATEDIFF(DATE(CONVERT_TZ(curdate(), 'UTC', 'America/Tegucigalpa')),case clubs_MSISDN.fecha_baja when 0 then clubs_MSISDN.fecha_alta else clubs_MSISDN.fecha_baja end) as dias_alta from clubs_MSISDN". 
                " where clubs_MSISDN.state = 'enable' and clubs_MSISDN.id_clubs = $id_club) as B". 
                " on A.id_msisdn = B.id_msisdn )".
                " where A.eventos_cobrados < $cobros_diarios";
        return mysql_query($sql,$this->conn);
    }
    
}
  
?>
