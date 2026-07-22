<?php
include_once('./conf/conf.inc');
$content_type = 'application/json';

/*
"address": "+595981000000", 
"eventDateTime": "2012-09-10T15:40:25-04:00", 
"serviceCode": "TONE", 
"groupCode": "1020", 
"source": "SMS", 
"channel": "SMS", 
"sourceData": "tone", 
"contextData": "tone", 
"userRequest": true 
*/
//Obtener los datos del post
$data = json_decode(file_get_contents('php://input'), true);
//var_dump($data);
if (!$data)
{
    foreach ($_POST as $key => $value)
    {
        $data = json_decode($value);
    }
}



if ($data)
{
    
    $conn = mysql_connect($hostdb,$userdb,$passdb);
    if ($conn)
    {
        if (mysql_selectdb($namedb,$conn))
        {
                    
            $msisdn = str_replace('+','',rawurldecode($data['address']));
            $sc = $data['groupCode'];
            $service = $data['serviceCode'];
            
            $log_user_data = new user_log($conn);
            $id_user_log = $log_user_data->guardar_log_mo($msisdn,$service.' ' .$data['channel'],$sc,'TIGO_HN');
            $util = new utilidades();
	    //echo "$conn , $msisdn , $sc , $service";
            $util->disable_user_edge($conn,$msisdn,$sc,$service);
            $response_value = 0;
        }
        else
        {
            $response_value = 1;
        }
    }
    else
    {
        $response_value = 1;
    }
}
else
{
    $response_value = 1;
}
$estado['status'] = $response_value;
sendResponse(200,json_encode($estado),'application/json');
  
?>
