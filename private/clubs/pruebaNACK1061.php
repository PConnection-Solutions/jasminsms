<?php

$dlr_alta = 1;

$NACK = [
            "NACK/1003",
            "NACK/1061"
        ];

$meta_mod = 'NACK/1003';

//substr($meta_mod,0,9)

if ($dlr_alta == 1 and !in_array(substr($meta_mod,0,9), $NACK)) {

    echo "Entro\n";

} else {

    echo "NO Entro\n";

}