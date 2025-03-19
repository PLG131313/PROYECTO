<?php
usleep(400000);

require('conexion.php');

// por post recibe
$nombre = $_POST['nombre'];
$email = $_POST['email'];
$telefono = $_POST['telefono'];
$contrasena = $_POST['contrasena'];

// miro si esta vacio
if (empty($nombre) || empty($email) || empty($telefono) || empty($contrasena)) {
    // si hay vacio error
    echo "<font face='Calibri' color='red' size='3'>Todos los campos son obligatorios.</font>";
    exit(); //paro si falta
}

//miro si existe ese correo
$verificar = "SELECT * FROM clientes WHERE email='$email'";
$resultado = mysqli_query($conexion, $verificar);

// verifico si exist el correp
if (mysqli_num_rows($resultado) > 0) {
    // si existe error
    echo "ERROR: Ese correo ya esta registrado";
} else {
    // inserto en base de datos
    $insertar = "INSERT INTO clientes (nombre, email, telefono, contrasena) VALUES ('$nombre', '$email', '$telefono', '$contrasena')";

    if (mysqli_query($conexion, $insertar)) {
        //existo mando 0
        echo 0;
    } else {
        // Ssi hay error lo mando
        echo "<font face='Calibri' color='red' size='3'>Error en el registro.</font>";
    }
}

mysqli_close($conexion);
?>
