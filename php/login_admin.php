<?php
// Pausa la ejecución del script durante 400,000 microsegundos (0.4 segundos)
usleep(400000);

// Requiere el archivo de conexión a la base de datos
require('conexion.php');

// Obtener datos del formulario
$adminId = $_POST['adminId'];
$contrasena = $_POST['adminPassword'];

// Prevenir inyección SQL usando prepared statements
$consulta = "SELECT * FROM administradores WHERE id = ? AND contrasena = ? AND estado = 'activo'";
$stmt = mysqli_prepare($conexion, $consulta);
mysqli_stmt_bind_param($stmt, "ss", $adminId, $contrasena);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

// Verificar si existe el administrador
$nregistros = mysqli_num_rows($resultado);

if ($nregistros == 0) {
    echo "<font face='Calibri' color='red' size='3'>Error de validación. Credenciales incorrectas.</font>";
} else {
    // Iniciar sesión
    session_start();

    // Obtener información del administrador
    $registro = mysqli_fetch_array($resultado);

    // Guardar datos en la sesión
    $_SESSION['idusuario'] = $registro['id'];
    $_SESSION['tipo_usuario'] = 'admin';
    $_SESSION['nombre_admin'] = $registro['nombre'] . ' ' . $registro['apellidos'];

    // Actualizar último acceso
    $update = "UPDATE administradores SET ultimo_acceso = NOW() WHERE id = ?";
    $stmt_update = mysqli_prepare($conexion, $update);
    mysqli_stmt_bind_param($stmt_update, "s", $adminId);
    mysqli_stmt_execute($stmt_update);
    mysqli_stmt_close($stmt_update);

    // Enviar código de éxito
    echo "0";
}

// Cerrar statement y conexión
mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>