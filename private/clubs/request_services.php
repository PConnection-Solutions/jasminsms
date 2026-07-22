<?php
error_reporting(0);
@ini_set('display_errors', 0);
include_once('./conf/conf.inc');
$content_type = 'application/json';

/*
http://72.55.181.60/private/clubs/request_services.php
*/
//Obtener los datos del post
$data = json_decode(file_get_contents('php://input'));


if ($data)
{
    
    //$conn = mysql_connect($hostdb,$userdb,$passdb);
    $conn = new mysqli($hostdb, $userdb, $passdb,$namedb);
    $result=array();
    if ($conn->connect_errno > 0)
    {
        $response_value = 1;//Error
    }
    else
    {
        if ($data->type == 'request')
        {
        /*
            {"type":"request","id_carrier":"3"}
        */
            $id_carrier = $data->id_carrier; //Que carrier
            $sql = "select id,tipo,msisdn,recharge from clubs_REQUEST".
                   " where id_carrier=? and ejecutado='disable' and success='disable'";
            $statement = $conn->prepare($sql);
            $statement->bind_param('i',$id_carrier);
            $statement->execute();
            $statement->store_result();
            //$registros = $statement->num_rows();
            $output = array();
            
            $resultrow = array();
            stmt_bind_assoc($statement, $resultrow);
            
            
            while($statement->fetch())
            {
                $id = $resultrow['id'];
                $output[$resultrow['id']]=array($resultrow['tipo'],$resultrow['msisdn'],$resultrow['recharge']);
                $sql = "update clubs_REQUEST set ejecutado = 'disable' where id = $id";
                $conn->query($sql);
            }
            $statement->close();
            $result['data']=$output;
            
        }
        else if ($data->type == 'response')  //Esto es response
        {
            /*
            {"type":"response",data":{"1":["enable","hola mundo"],"2":["disable","mas datos"]}}
            */
            if ($data->data)
            {
                foreach ($data->data as $key=>$value)
                {
                    //Aqui tenemos los key
                    $sql = "update clubs_REQUEST set success=?, response = ?, ejecutado='enable' where id= ?".
                    " and ejecutado='disable' and success='disable'";
                    $stm = $conn->prepare($sql);
                    $stm->bind_param('ssi',$value[0],$value[1],$key);
                    $stm->execute();
                    
                }
                if ($stm)
                {
                    $stm->close();
                }
            }
            
        }
        else //Esto es para logs
        {
        /*
            {"type":"log","id_carrier":"3","contenido":"Hoy ha ganado algo"}
        */
            $id_carrier = $data->id_carrier; //Que carrier
            $contenido = $data->contenido;
            $sql = "insert into clubs_LOGS (id_carrier, contenido) values (?,?)";
            $statement = $conn->prepare($sql);
            $statement->bind_param('ss',$id_carrier,$contenido);
            $statement->execute();
            
        }

        $response_value = 0;//OK
        $conn->close();

    }
}
else
{
    $response_value = 1;//Error request
}
$result['status'] = $response_value;
//print json_encode($result);
sendResponse(200,json_encode($result),'application/json');

function stmt_bind_assoc (&$stmt, &$out) 
{
    $data = mysqli_stmt_result_metadata($stmt);
    $fields = array();
    $out = array();

    $fields[0] = $stmt;
    $count = 1;

    while($field = mysqli_fetch_field($data)) 
    {
        $fields[$count] = &$out[$field->name];
        $count++;
    }
    call_user_func_array('mysqli_stmt_bind_result', $fields);
}
  
?>
