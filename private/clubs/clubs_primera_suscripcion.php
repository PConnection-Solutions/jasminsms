<?php

set_time_limit(0);

if ( isset($_REQUEST['service_type']) ) {
	$service_type = substr($_REQUEST['service_type'], -4);
} else {
	exit("NO_PARAM");
}

if ( isset($_REQUEST['msisdn']) ) {
	$msisdn = $_REQUEST['msisdn'];
} else {
	exit("NO_PARAM");
}

if ( isset($_REQUEST['short_code']) ) {
	$short_code = $_REQUEST['short_code'];
} else {
	exit("NO_PARAM");
}


$con = mysqli_connect('rds-compartido.pconnection.net','admin' ,'8sC3rq2iQqEztsj1' ,'primeras_altas_clubs');

$query = "INSERT INTO `clubs_MSISDN` (`msisdn`, `service_type`, `short_code`, `date_time`) VALUES ('$msisdn', '$service_type', '$short_code', CONVERT_TZ(NOW(), 'UTC', 'America/Tegucigalpa'))";
$result = mysqli_query($con, $query);

echo mysqli_affected_rows($con);

mysqli_close($con);