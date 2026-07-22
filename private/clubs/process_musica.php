<?php
$salida = shell_exec('ps xa | grep "musica"');
echo $salida;
