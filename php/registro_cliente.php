<?php
usleep(400000);
require('conexion.php');

$nombre = $_POST['nombre'];
$email = $_POST['email'];
$telefono = $_POST['telefono'];
$contrasena = $_POST['contrasena'];

if (empty($nombre) || empty($email) || empty($telefono) || empty($contrasena)) {
    echo "<font face='Calibri' color='red' size='3'>Todos los campos son obligatorios.</font>";
    exit();
}

$verificar = "SELECT * FROM clientes WHERE email='$email'";
$resultado = mysqli_query($conexion, $verificar);

if (mysqli_num_rows($resultado) > 0) {
    echo "ERROR: Ese correo ya esta registrado";
} else {
    $insertar = "INSERT INTO clientes (nombre, email, telefono, contrasena) VALUES ('$nombre', '$email', '$telefono', '$contrasena')";

    if (mysqli_query($conexion, $insertar)) {
        echo 0;
    } else {
        echo "<font face='Calibri' color='red' size='3'>Error en el registro.</font>";
    }
}

mysqli_close($conexion);
?>

