<?php
// Pausa la ejecución del script durante 400,000 microsegundos (0.4 segundos)
usleep(400000);

// Requiere el archivo de conexión a la base de datos (conexion.php)
require('conexion.php');

// por post recibe id y contraseña
$pilotId = $_POST['pilotId']; // Obtiene el ID del piloto
$contrasena = $_POST['contrasena']; // Obtiene la contraseña

// consulto si existe este piloto
$consulta = "SELECT * FROM pilotos WHERE id='$pilotId' AND contrasena='$contrasena'";
$resultado = mysqli_query($conexion, $consulta);

// cojo numero de filas
$nregistros = mysqli_num_rows($resultado);

// si no existe da error
if ($nregistros == 0) {
    echo "<font face='Calibri' color='red' size='3'>Error de validación. Credenciales incorrectas.</font>";
} else {
    // si existe inicio la sesion
    session_start();

    // cojo informacion de ese piloto
    $registro = mysqli_fetch_array($resultado);

    $_SESSION['idusuario'] = $registro['id']; // sesion con su id
    $_SESSION['tipo_usuario'] = 'piloto'; // Identificar tipo de usuario
    $_SESSION['nombre_piloto'] = $registro['nombre']; // Guardar nombre del piloto

    // mando 0 que es exito
    echo "0";
}

// Cierra la conexión a la base de datos
mysqli_close($conexion);
?>
