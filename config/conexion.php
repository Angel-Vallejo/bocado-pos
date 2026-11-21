<?php
// config/conexion.php

$serverName = "localhost";
$connectionOptions = array(
    "Database" => "BD_Hamburgueseria_Bocado",
    "Uid" => "",      //Autenticación de Windows
    "PWD" => ""       
);

// Establecer la conexión con SQL Server usando la extensión sqlsrv
$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    // Si falla la conexión, detenemos la ejecución y mostramos el error.
    die(print_r(sqlsrv_errors(), true));
}
?>