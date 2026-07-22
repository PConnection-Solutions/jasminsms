<?php
require_once('./conf/dlr.php');
error_reporting(E_ERROR | E_WARNING);

date_default_timezone_set("America/Tegucigalpa");
$userdb = 'admin';
$passdb = '8sC3rq2iQqEztsj1';
$namedb = 'CLUBS';
$hostdb = 'rds-compartido.pconnection.net';
//$hostdb = '184.107.61.32';

function darvidasAPPrende($from, $tasa){
    $hostname_smsR = "10.7.42.10";
    $database_smsR = "sms";
    $username_smsR = "root";
    $password_smsR = "";
    $smsRemoto = mysql_pconnect($hostname_smsR, $username_smsR, $password_smsR) or die(mysql_error());
    $query = "UPDATE `CLUBPREGUNTAS`.`mega_PUNTOS` SET `eqssd` = `eqssd` + ROUND((3*$tasa),0) WHERE  `msisdn`='$from'";
    //echo $query;
    $result = mysql_query($query, $smsRemoto);
}

function log_CDR('$destination', '$source', '$meta', '$mensaje'){
    $hostname_smsR = "10.7.42.10";
    $database_smsR = "sms";
    $username_smsR = "root";
    $password_smsR = "";
    $smsRemoto = mysql_pconnect($hostname_smsR, $username_smsR, $password_smsR) or die(mysql_error());
    $query = "INSERT INTO `sms`.`dlrcr2` (`status`, `answer`, `to`, `from`, `ts`,`llave`, mensaje) VALUES ('' , $meta', '$source', '$destination', CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa'), CONVERT_TZ(now(), 'UTC', 'America/Tegucigalpa'), '$mensaje')";
    //echo $query;
    $result = mysql_query($query, $smsRemoto);
}

/*
Kannel:destination,source,meta,myid
Beconn:ResultType,ResultData,IdDeliver
Modulo de registro de los DLR, de kannel y Beconnected, este modulo guarda los dlr que se consideran importantes,
actualiza la tabla de envio en caso que sea positivo el cobro y establece los recobros en caso que se requiera

*/
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{

    
    $resulttype = urldecode($_REQUEST['ResultType']);
    $resultdata = urldecode($_REQUEST['ResultData']);
    $iddeliver = urldecode($_REQUEST['IdDeliver']);
    
    $id_club = urldecode($_REQUEST['id_club']);
    $source = urldecode($_REQUEST['source']);
    $recobro=urldecode($_REQUEST['recobro']);
    $myid = urldecode($_REQUEST['myid']);
}
else if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
    //$destination = urldecode($_REQUEST['destination']);
    $destination = str_replace('+','',rawurldecode($_REQUEST['destination']));
    $meta = urldecode($_REQUEST['meta']);
    $myid = urldecode($_REQUEST['myid']);
    $source = urldecode($_REQUEST['source']);

    $id_club = urldecode($_REQUEST['id_club']);
    $recobro=urldecode($_REQUEST['recobro']);
    //$data = urldecode($_REQUEST['data']);//Este indicador de cdc
    
} 
$mensaje = "";
if(isset($_REQUEST['mensaje'])){
    $mensaje = $_REQUEST['mensaje'];
}


$conn = mysql_connect($hostdb,$userdb,$passdb);
if ($conn)
{
    if (mysql_selectdb($namedb,$conn))
    {
        $dlr_info = new dlr($conn);
        if (isset($meta)) //Kannel y Edge
        {
            $meta_mod = substr($meta,0,9);
            //Guardar solo los NACK y los ACK 
            if ((substr($meta_mod,0,4) == 'ACK/') || (substr($meta_mod,0,5) == 'NACK/'))//Kannel y Edge
            {
                $sql ="insert into clubs_DLR (id_transaction,id_deliver,msisdn,source,estado,fecha,id_club,otros)".
                " value('$myid','$myid','$destination','$source','$meta_mod',DATE(CONVERT_TZ(curdate(), 'UTC', 'America/Tegucigalpa')),$id_club,'$meta') on duplicate key".
                " update estado = '$meta_mod',otros = '$meta',source = '$source'";
                mysql_query($sql,$conn);
                /* Respuestas de Kannel
                cobrados: ACK/
                no cobrados: NACK/1060/
                */
                //if(substr($meta_mod,0,4) == 'ACK/'){
                    log_CDR('$destination','$source', '$meta', '$mensaje');
                //}

                if(substr($meta_mod,0,4) == 'ACK/'){
                    darvidasAPPrende($destination, "1");
                }

                if ($recobro == '1')
                {
                    $dlr_info->analizar_registros($meta,$destination,$myid,$id_club,true);
                    
                }
                else
                {
                    $dlr_info->analizar_registros($meta,$destination,$myid,$id_club,false);
                    
                }
                
            } else if (($meta_mod == '0') || (($meta_mod >= '201') && ($meta_mod <= '299')))//CDC
            {
                //$myid_mod = $myid. substr(uniqid('', true), -3);
                $myid_mod = substr(uniqid('', true),-19);
                $sql ="insert into clubs_DLR (id_transaction,id_deliver,msisdn,source,estado,fecha,id_club,otros)".
                " value('$myid_mod','$myid','$destination','$source','$meta_mod',DATE(CONVERT_TZ(curdate(), 'UTC', 'America/Tegucigalpa')),$id_club,'$meta') on duplicate key".
                " update estado = '$meta_mod',otros = '$meta',source = '$source'";
                mysql_query($sql,$conn);
                if ($recobro == '1')
                {
                    $dlr_info->analizar_registros($meta,$destination,$myid,$id_club,true);
                    
                }
                else
                {
                    $dlr_info->analizar_registros($meta,$destination,$myid,$id_club,false);
                }
                
            }
        } 
        else if($resultdata)//Beconnected
        {
            // | son las divisiones de registro y , son las divisiones del campo
            $result_data = explode("|",$resultdata);
            foreach($result_data as $key => $value)
            {
                $result_data_comma = explode(",",$value);
                $id_tran = $result_data_comma[0]; //idtransaction
                $msisdn = $result_data_comma[1]; //msisdn
                $status = $result_data_comma[2]; //status
                $cobro = 0;
                switch  ($status)
                {
                    case 100:
                    //Mensaje cobrado
                        //$cobro = $result_data_comma[3]; //valor cobrado
                        break;
                    case 200:
                    //Mensaje sin aplicación de cobro  
                        break;
                    case 300:
                    //Error en el cobro 
                        break;
                    case 400:
                    //Saldo insuficiente
                        break;
                    case 500:
                    //Número inactivo 
                        break;
                    case 600:
                    //Número en lista negra
                        break;
                    case 700:
                    //Número inválido
                        break;
                    case 800:
                    //Número inexistente o no registrado con el operador 
                        break;
                    default:
                }
                $sql ="insert into clubs_DLR (id_transaction,id_deliver,msisdn,source,estado,fecha,valor_cobrado,id_club,otros)".
                    //" value('$id_tran','$myid','$msisdn','$source','$status',DATE(CONVERT_TZ(curdate(), 'UTC', 'America/Tegucigalpa')),$cobro,$id_club,'$value')";
                    " value('$id_tran','$myid','$msisdn','$source','$status',DATE(CONVERT_TZ(curdate(), 'UTC', 'America/Tegucigalpa')),$cobro,$id_club,'$status')";
                mysql_query($sql,$conn);
                if ($recobro == '1')
                {
                    $dlr_info->analizar_registros($status,$msisdn,$myid,$id_club,true);
                    
                }
                else
                {
                    $dlr_info->analizar_registros($status,$msisdn,$myid,$id_club,false);
                }
                
                
            }
        }
        
    }
    mysql_close($conn);
}
  
?>
