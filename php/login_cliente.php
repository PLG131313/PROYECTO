<?php
usleep(400000);

require('conexion.php');

// Recibir email y contraseña por POST
$email = $_POST['email'];
$contrasena = $_POST['contrasena'];

// Consulta para verificar cliente
$consulta = "SELECT * FROM clientes WHERE email='$email' AND contrasena='$contrasena'";
$resultado = mysqli_query($conexion, $consulta);

// Número de filas
$nregistros = mysqli_num_rows($resultado);

// Si no existe, error
if ($nregistros == 0) {
    echo "<font face='Calibri' color='red' size='3'>Error de validación. Credenciales incorrectas.</font>";
} else {
    // Iniciar sesión si existe
    session_start();

    // Información del registro
    $registro = mysqli_fetch_array($resultado);

    $_SESSION['idusuario'] = $registro['id']; // Guardar ID en sesión
    $_SESSION['cliente_email'] = $registro['email']; // Guardar email en sesión
    $_SESSION['nombre_cliente'] = $registro['nombre']; // Guardar nombre en sesión
    $_SESSION['tipo_usuario'] = 'cliente'; // Identificar tipo de usuario

    // Enviar 0 para indicar que funciona
    echo "0";
}
mysqli_close($conexion);
?>