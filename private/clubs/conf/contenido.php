<?php
class contenido
{
    private $conn;
    public function __construct($conn)
    {
        $this->conn = $conn;
    }
    public function obtener_contenido_bc($id_clubs,$auto='false')
    {
        if ($auto === 'true')
        {
            $sql ="select * from clubs_SERVICE_DET where id_clubs=$id_clubs order by rand() limit 0,1";
            $response = mysql_query($sql,$this->conn);
            if(mysql_num_rows($response))
            {
                $response = mysql_fetch_array($response);
                return $response; 
            }
            else
            {
                return false;
            }
            
        }
        else
        {
            /*
            Esta condicion implica que el mensaje es programado
            */
            $fecha = date("Y-m-d");
            $today = getdate();
            $hora = $today['hours'];
            /*$sql = "SELECT clubs_SERVICE_PROGRAM.id_broad,clubs_SERVICE_PROGRAM.fecha_broad,clubs_SERVICE_PROGRAM.id_detalle_service,clubs_SERVICE_DET.content".
                " FROM clubs_SERVICE_PROGRAM Inner Join clubs_SERVICE_DET ON clubs_SERVICE_PROGRAM.id_detalle_service = clubs_SERVICE_DET.id_detalle_service".
                " where clubs_SERVICE_PROGRAM.fecha_broad = '$fecha' and clubs_SERVICE_PROGRAM.status = 'pendiente' and clubs_SERVICE_DET.id_clubs = $id_clubs".
                " order by rand () limit 0,1";*/

            $sql = "SELECT clubs_SERVICE_PROGRAM.id_broad,clubs_SERVICE_PROGRAM.fecha_broad,clubs_SERVICE_PROGRAM.id_detalle_service,clubs_SERVICE_DET.content".
                " FROM clubs_SERVICE_PROGRAM Inner Join clubs_SERVICE_DET ON clubs_SERVICE_PROGRAM.id_detalle_service = clubs_SERVICE_DET.id_detalle_service".
                " where clubs_SERVICE_PROGRAM.fecha_broad = '$fecha' and clubs_SERVICE_DET.id_clubs = $id_clubs".
                " order by rand () limit 0,1";

            //echo "Contenido: " . $sql . "\n";
            
            $response = mysql_query($sql,$this->conn);
            if(mysql_num_rows($response))
            {
                /*
                Se encontro un mensaje programado, seleccionarlo
                */
                $response = mysql_fetch_array($response);
                $id_broad = $response['id_broad'];
                $sql = "update clubs_SERVICE_PROGRAM set status = 'ejecutado', fecha_ejecucion=CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa') where id_broad = $id_broad";
                //mysql_query($sql,$this->conn);
                return $response;
            }
            else
            {
                return $this->obtener_contenido_random($id_clubs);

            }
        }
        
    }
    public function obtener_contenido_random($id_clubs)
    {
        /*
            No hay mensaje programado, posiblemente a alguien se le olvido realizarlo, 
            en determinada hora, asi que buscamos los programados por el dia
        */
        $fecha = date("Y-m-d");
        
        $sql = "SELECT clubs_SERVICE_PROGRAM.id_broad,clubs_SERVICE_PROGRAM.fecha_broad,clubs_SERVICE_PROGRAM.id_detalle_service,clubs_SERVICE_DET.content".
            " FROM clubs_SERVICE_PROGRAM Inner Join clubs_SERVICE_DET ON clubs_SERVICE_PROGRAM.id_detalle_service = clubs_SERVICE_DET.id_detalle_service".
            " where clubs_SERVICE_PROGRAM.fecha_broad = '$fecha' and clubs_SERVICE_PROGRAM.status = 'pendiente' and clubs_SERVICE_DET.id_clubs = $id_clubs order by rand () limit 0,1";
        
        $response = mysql_query($sql,$this->conn);
        if(mysql_num_rows($response))
        {
            /*
            Se encontro un mensaje programado
            */
            $response = mysql_fetch_array($response);
            return $response;
        }
        else
        {
            /*
            No hay mensaje programado, posiblemente a alguien se le olvido realizarlo, 
            en determinada hora, asi que buscamos los programados por el dia
            */
            $sql ="select * from clubs_SERVICE_DET where id_clubs=$id_clubs order by rand() limit 0,1";
            $response = mysql_query($sql,$this->conn);
            if(mysql_num_rows($response))
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
}  
?>
