<?php
    /*
     * Configure the kannel instances here
     */
    $configs = array(
        array( "base_url" => "http://localhost:13000",
               "status_passwd" => "",
               "admin_passwd" => "bar",
               "name" => "Tigo HN"
             )
    );

    /* some constants */
    define('MAX_QUEUE', 100); /* Maximum size of queues before displaying it in red */
    define('DEFAULT_REFRESH', 10); /* Default refresh time for the web interface */
?>
