<?php
usleep(400000);
require('conexion.php');

$nombre = $_POST['nombre'];
$email = $_POST['email'];
$asunto = $_POST['asunto'];
$mensaje = $_POST['mensaje'];

if (empty($nombre) || empty($email) || empty($asunto) || empty($mensaje)) {
    echo "<script>alert('Todos los campos son obligatorios.'); window.history.back();</script>";
    exit();
}

$insertar = "INSERT INTO contacto (nombre, email, asunto, mensaje) VALUES ('$nombre', '$email', '$asunto', '$mensaje')";

if (mysqli_query($conexion, $insertar)) {
    echo "<script>window.location.href='index.php';</script>";
} else {
    echo "<script>alert('Error en el envío del mensaje.'); window.history.back();</script>";
}

mysqli_close($conexion);
?>
