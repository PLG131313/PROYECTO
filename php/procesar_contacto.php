<?php
usleep(400000);
require('conexion.php');

// post recibo los datos
$nombre = $_POST['nombre'];
$email = $_POST['email'];
$asunto = $_POST['asunto'];
$mensaje = $_POST['mensaje'];

// miro si hay vacio
if (empty($nombre) || empty($email) || empty($asunto) || empty($mensaje)) {
    // muestro alert de que faltan cosas
    echo "<script>alert('Todos los campos son obligatorios.'); window.history.back();</script>";
    exit(); // paro si faltan cosas
}

// meto datos
$insertar = "INSERT INTO contacto (nombre, email, asunto, mensaje) VALUES ('$nombre', '$email', '$asunto', '$mensaje')";

if (mysqli_query($conexion, $insertar)) {
    //si esta bien al index lo mando al usuario
    echo "<script>window.location.href='../index.php';</script>";
} else {
    // si hay algun error lo pongo
    echo "<script>alert('Error en el envío del mensaje.'); window.history.back();</script>";
}

mysqli_close($conexion);
?>
