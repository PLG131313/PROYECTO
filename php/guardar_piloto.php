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

// Obtener datos del formulario
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : null;
$licencia = isset($_POST['licencia']) ? trim($_POST['licencia']) : '';
$contrasena = isset($_POST['contrasena']) ? trim($_POST['contrasena']) : '';
$estado = isset($_POST['estado']) ? trim($_POST['estado']) : 'disponible';

// Validar datos
if (empty($nombre) || empty($email) || empty($licencia) || empty($contrasena)) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos obligatorios deben ser completados']);
    exit;
}

// Validar formato de email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'El formato del email no es válido']);
    exit;
}

// Verificar si el email ya existe
$checkEmail = "SELECT id FROM pilotos WHERE email = ?";
$stmtEmail = mysqli_prepare($conexion, $checkEmail);
mysqli_stmt_bind_param($stmtEmail, "s", $email);
mysqli_stmt_execute($stmtEmail);
mysqli_stmt_store_result($stmtEmail);

if (mysqli_stmt_num_rows($stmtEmail) > 0) {
    echo json_encode(['success' => false, 'message' => 'El email ya está registrado']);
    mysqli_stmt_close($stmtEmail);
    mysqli_close($conexion);
    exit;
}
mysqli_stmt_close($stmtEmail);

// Verificar si la licencia ya existe
$checkLicencia = "SELECT id FROM pilotos WHERE licencia = ?";
$stmtLicencia = mysqli_prepare($conexion, $checkLicencia);
mysqli_stmt_bind_param($stmtLicencia, "s", $licencia);
mysqli_stmt_execute($stmtLicencia);
mysqli_stmt_store_result($stmtLicencia);

if (mysqli_stmt_num_rows($stmtLicencia) > 0) {
    echo json_encode(['success' => false, 'message' => 'La licencia ya está registrada']);
    mysqli_stmt_close($stmtLicencia);
    mysqli_close($conexion);
    exit;
}
mysqli_stmt_close($stmtLicencia);

// Validar estados permitidos
$estadosPermitidos = ['disponible', 'en_vuelo', 'descanso', 'baja'];
if (!in_array($estado, $estadosPermitidos)) {
    $estado = 'disponible';
}

// Insertar el nuevo piloto
$query = "INSERT INTO pilotos (nombre, email, telefono, licencia, contrasena, estado) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conexion, $query);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta: ' . mysqli_error($conexion)]);
    exit;
}

mysqli_stmt_bind_param($stmt, "ssssss", $nombre, $email, $telefono, $licencia, $contrasena, $estado);

if (mysqli_stmt_execute($stmt)) {
    $pilotoId = mysqli_insert_id($conexion);
    echo json_encode([
        'success' => true,
        'message' => 'Piloto creado exitosamente',
        'piloto' => [
            'id' => $pilotoId,
            'nombre' => $nombre,
            'email' => $email,
            'telefono' => $telefono,
            'licencia' => $licencia,
            'estado' => $estado
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al guardar el piloto: ' . mysqli_error($conexion)]);
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>
