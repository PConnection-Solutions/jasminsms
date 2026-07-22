<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://www.google.com");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLINFO_HEADER_OUT, true);
curl_exec($ch);
var_dump(curl_getinfo($ch,CURLINFO_HEADER_OUT));
?>
