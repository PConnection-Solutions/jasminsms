<?php
 class utilidades
 {
     public function get_service_id($conn,$id_club)
     {
         $sql = "select * from clubs_SERVICE where id_club = $id_club";
         $response = mysql_query($sql,$conn);
         if (mysql_num_rows($response))
         {
             return mysql_fetch_array($response);
         }
         else
         {
             return false;
             
         }
         
     }
     public function disable_user_edge($conn,$msisdn,$sc,$service)
     {
         $sql = "update CLUBS.clubs_MSISDN A".
         " inner join clubs_SERVICE B on A.id_clubs = B.id_club".
         " set A.state = 'disable', A.fecha_baja = DATE(CONVERT_TZ(curdate(), 'UTC', 'America/Tegucigalpa'))".
         " where B.name_club like '$service%' and (B.sc_mo = '$sc' or B.sc_mo_adicional = '$sc') and A.msisdn = '$msisdn' and B.route = 'TIGO_HN'";
         return mysql_query($sql,$conn);
     }
     public function get_club_name_by_tariffId($conn,$tariffId,$red)
     {

         $sql = "SELECT clubs_SERVICE.name_club,clubs_SERVICE.id_club".
                " FROM clubs_SERVICE INNER JOIN clubs_CDC_CONF ON clubs_SERVICE.id_club = clubs_CDC_CONF.id_club".
                " where route = '$red' and tariffId = $tariffId";
         
         echo $sql;
         $response = mysql_query($sql,$conn);
         if (mysql_num_rows($response))
         {
             $response = mysql_fetch_array($response); 
             return $response;
         }
         else
         {
             return false;
             
         }
         
     }
     public function get_club_msisdn($conn,$msisdn,$sc,$red)
     {
         $sql = "SELECT clubs_SERVICE.* FROM clubs_MSISDN".
         " INNER JOIN clubs_SERVICE ON clubs_MSISDN.id_clubs = clubs_SERVICE.id_club".
         " WHERE clubs_MSISDN.msisdn = '$msisdn' and clubs_SERVICE.route = '$red' and clubs_MSISDN.state = 'enable'".
         " and (clubs_SERVICE.sc_mo = '$sc' or clubs_SERVICE.sc_mo_adicional = '$sc' or clubs_SERVICE.sc_cobro1 = '$sc' ) limit 1";
         $response = mysql_query($sql,$conn);
         if (mysql_num_rows($response))
         {
             $response = mysql_fetch_array($response); 
             return $response;
         }
         else
         {
             return false;
             
         }
     }
 } 
?>
