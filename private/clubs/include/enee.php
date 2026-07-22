<?php

function limpiarTextoParaSMS($texto) {
    // Arreglo de caracteres a buscar
    $buscar = [
        // Vocales con tilde (minúsculas y mayúsculas)
        'á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú',
        // Vocales con diéresis (minúsculas y mayúsculas)
        'ä', 'ë', 'ï', 'ö', 'ü', 'Ä', 'Ë', 'Ï', 'Ö', 'Ü',
        // Letra ñ (minúscula y mayúscula)
        'ñ', 'Ñ',
        // Signos de interrogación y exclamación invertidos
        '¿', '¡',
        // Otros caracteres comunes que pueden dar problemas
        'à', 'è', 'ì', 'ò', 'ù', 'À', 'È', 'Ì', 'Ò', 'Ù', // Vocales con acento grave
        'â', 'ê', 'î', 'ô', 'û', 'Â', 'Ê', 'Î', 'Ô', 'Û', // Vocales con acento circunflejo
        'ç', 'Ç' // Cedilla
    ];

    // Arreglo de caracteres por los que se van a reemplazar
    $reemplazar = [
        // Vocales sin tilde
        'a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U',
        // Vocales sin diéresis
        'a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U',
        // Letra n
        'n', 'N',
        // Se eliminan los signos
        '', '',
        // Reemplazo para otros caracteres
        'a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U',
        'a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U',
        'c', 'C'
    ];

    // Se realiza el reemplazo y se devuelve el texto limpio
    return str_replace($buscar, $reemplazar, $texto);
}

function enee($msisdn) {
    // Database configuration
    //mysql -h "rds-compartido.pconnection.net" -u admin --password="8sC3rq2iQqEztsj1" -e "CALL CLUBS.p_rename_clubs_dlr_tmp()"
    $host = "rds-compartido.pconnection.net"; // Or your database host (e.g., "127.0.0.1" or a remote IP)
    $username = "admin"; // Your database username
    $password = "8sC3rq2iQqEztsj1"; // Your database password
    $database = "servicios_honduras"; // The name of your database

    // Attempt to establish a database connection
    $conn = mysqli_connect($host, $username, $password, $database);

    // Check connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $conn->set_charset("utf8");

    $hoy = date('Y-m-d');

    $sql = "SELECT circuito, departamento, municipio, barrio_colonia FROM zona_servicio WHERE msisdn = '$msisdn' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result) {
    // Si hay resultados, puedes procesarlos
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $circuito = $row['circuito'];
                $departamento = $row['departamento'];
                $municipio = $row['municipio'];
                $barrio_colonia = $row['barrio_colonia'];
            }
        } else {
		// No Suscrito
		return 1;
        }
    } else {
        // No Suscrito
        return 1;
    }

    $sql2 = "SELECT DATE(ri.fecha_interrupcion) AS fecha_interrupcion, ri.horario, ri.tipo_trabajo
            FROM resumen_interrupciones AS ri
            LEFT JOIN base_barrios_colonias AS bbc
              ON ri.circuito = bbc.circuito
            WHERE bbc.departamento   = '$departamento'
              AND bbc.municipio      = '$municipio'
              AND bbc.barrio_colonia = '$barrio_colonia'
              AND DATE(ri.fecha_interrupcion) >= '$hoy'
            ORDER BY ri.fecha_interrupcion, ri.horario";
    $result2 = mysqli_query($conn, $sql2);
    if ($result2) {
    // Si hay resultados, puedes procesarlos
        if (mysqli_num_rows($result2) > 0) {
            while ($row2 = mysqli_fetch_assoc($result2)) {
                return limpiarTextoParaSMS("Motivo:" . $row2['tipo_trabajo'] . "\nFecha:" . $row2['fecha_interrupcion'] . "\nHorario:" . $row2['horario'] . "\nDepto:" . $departamento . "\nMunicipio:" . $municipio . "\nBarrio/Colonia:" . $barrio_colonia);
            }
	} else {
	    // No corte
            return 0;
        }
    } else {
        // No corte
        return 0;
    }

    // You can now perform database operations using the $conn object.
    // For example, to close the connection when you're done:
    mysqli_close($conn);


}

/*
print_r(enee('50498107596'));

if (enee('50498107596') == 1) {
	echo "no suscrito";
} else {
	echo "otra cosa";
}
*/
