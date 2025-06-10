<?php
session_start();

// Verificar si el usuario ha iniciado sesión como administrador
if (!isset($_SESSION['idusuario']) || $_SESSION['tipo_usuario'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Verificar si es una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

require_once('conexion.php');

$nombre = trim($_POST['nombre']);
$email = trim($_POST['email']);
$licencia = trim($_POST['licencia']);
$contrasena = trim($_POST['contrasena']);
$telefono = trim($_POST['telefono']);
$estado = isset($_POST['estado']) ? $_POST['estado'] : 'disponible';

// Validaciones básicas
if (empty($nombre) || empty($email) || empty($licencia) || empty($contrasena) || empty($telefono)) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
    exit;
}

try {
    // Verificar email único
    $queryEmail = "SELECT id FROM pilotos WHERE email = '$email'";
    $resultEmail = mysqli_query($conexion, $queryEmail);
    if (mysqli_num_rows($resultEmail) > 0) {
        throw new Exception("Ya existe un piloto con ese email");
    }

    // Verificar licencia única
    $queryLicencia = "SELECT id FROM pilotos WHERE licencia = '$licencia'";
    $resultLicencia = mysqli_query($conexion, $queryLicencia);
    if (mysqli_num_rows($resultLicencia) > 0) {
        throw new Exception("Ya existe un piloto con esa licencia");
    }

    // Insertar piloto
    $queryInsertar = "INSERT INTO pilotos (nombre, email, telefono, licencia, contrasena, estado) 
                      VALUES ('$nombre', '$email', '$telefono', '$licencia', '$contrasena', '$estado')";

    if (mysqli_query($conexion, $queryInsertar)) {
        echo json_encode(['success' => true, 'message' => 'Piloto creado exitosamente']);
    } else {
        throw new Exception("Error al crear piloto: " . mysqli_error($conexion));
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

mysqli_close($conexion);
?>
