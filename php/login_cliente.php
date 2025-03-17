<?php
usleep(400000);
require('conexion.php');

$email = $_POST['email'];
$contrasena = $_POST['contrasena'];

$consulta = "SELECT * FROM clientes WHERE email='$email' AND contrasena='$contrasena'";
$resultado = mysqli_query($conexion, $consulta);

$nregistros = mysqli_num_rows($resultado);

if ($nregistros == 0) {
    echo "<font face='Calibri' color='red' size='3'>Error de validación. Credenciales incorrectas.</font>";
} else {
    session_start();
    $registro = mysqli_fetch_array($resultado);
    $_SESSION['idusuario'] = $registro['id'];
    $_SESSION['cliente_email'] = $registro['email'];

    echo "0";
}

mysqli_close($conexion);
?>
