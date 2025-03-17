<?php
$servidor = getenv('DB_HOST');
$usuario  = getenv('DB_USER');
$contrassda = getenv('DB_PASSWORD');
$sheng = getenv('DB_NAME');

// Ejemplo de conexión MySQLi
$conexion = new mysqli($servidor, $usuario, $contrassda, $sheng);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>
