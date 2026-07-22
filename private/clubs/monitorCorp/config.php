<?php
    /*
     * Configure the kannel instances here
     */
    $configs = array(
        array( "base_url" => "http://localhost:13010",
               "status_passwd" => "",
               "admin_passwd" => "bar",
               "name" => "Tigo_C33"
             ),
        array( "base_url" => "http://localhost:13020",
               "status_passwd" => "",
               "admin_passwd" => "bar",
               "name" => "Tigo_C80"
             ),
        array( "base_url" => "http://localhost:13030",
               "status_passwd" => "",
               "admin_passwd" => "bar",
               "name" => "Tigo_C81"
             ),
        array( "base_url" => "http://localhost:13040",
               "status_passwd" => "",
               "admin_passwd" => "bar",
               "name" => "Tigo_C82"
             ),
        array( "base_url" => "http://localhost:13050",
               "status_passwd" => "",
               "admin_passwd" => "bar",
               "name" => "Tigo_C83"
             ),
    );

    /* some constants */
    define('MAX_QUEUE', 100); /* Maximum size of queues before displaying it in red */
    define('DEFAULT_REFRESH', 30); /* Default refresh time for the web interface */
?>
