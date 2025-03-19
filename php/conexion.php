<?php
$servidor = "iasanz.synology.me:3306/plopezg129_WebTFG";
$usuario="alumno";
$contrassda="AlumnoSanz$1";
$sheng="plopezg129_WebTFG";

// Ejemplo de conexión MySQLi
$conexion = new mysqli($servidor, $usuario, $contrassda, $sheng);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>
