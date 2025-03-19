<?php
usleep(400000);

require('conexion.php');

// pot post recibe email y contraseña
$email = $_POST['email'];
$contrasena = $_POST['contrasena'];

// select para ver cliente
$consulta = "SELECT * FROM clientes WHERE email='$email' AND contrasena='$contrasena'";
$resultado = mysqli_query($conexion, $consulta);

// numero de filas
$nregistros = mysqli_num_rows($resultado);

// si no existe errror
if ($nregistros == 0) {
    echo "<font face='Calibri' color='red' size='3'>Error de validación. Credenciales incorrectas.</font>";
} else {
    // inicia si existe
    session_start();

    // informacion del registro
    $registro = mysqli_fetch_array($resultado);

    $_SESSION['idusuario'] = $registro['id']; // Guardo id en session
    $_SESSION['cliente_email'] = $registro['email']; // Guardo email

    // Envio 0 de que funciona
    echo "0";
}
mysqli_close($conexion);
?>
