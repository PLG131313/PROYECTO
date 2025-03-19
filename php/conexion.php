<?php
$servidor = getenv('DB_HOST');
$usuario  = getenv('DB_USER');
$contrasena = getenv('DB_PASSWORD');
$shema = getenv('DB_NAME');

// Ejemplo de conexión MySQLi
$conexion = new mysqli($servidor, $usuario, $contrasena, $shema);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>
