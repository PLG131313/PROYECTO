<?php
usleep(400000);
require('conexion.php');

$nombre = $_POST['nombre'];
$email = $_POST['email'];
$asunto = $_POST['asunto'];
$mensaje = $_POST['mensaje'];

if (empty($nombre) || empty($email) || empty($asunto) || empty($mensaje)) {
    echo "<font face='Calibri' color='red' size='3'>Todos los campos son obligatorios.</font>";
    exit();
}

$insertar = "INSERT INTO contacto (nombre, email, asunto, mensaje) VALUES ('$nombre', '$email', '$asunto', '$mensaje')";

if (mysqli_query($conexion, $insertar)) {
    echo 0;
} else {
    echo "<font face='Calibri' color='red' size='3'>Error en el envío del mensaje.</font>";
}

mysqli_close($conexion);
?>
