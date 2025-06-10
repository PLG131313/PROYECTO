<?php
session_start();

// Verificar si el usuario ha iniciado sesión como administrador
if (!isset($_SESSION['idusuario']) || $_SESSION['tipo_usuario'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Verificar si se proporcionó un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de piloto no proporcionado']);
    exit;
}

require_once('conexion.php');

// Obtener el ID del piloto
$pilotoId = (int)$_GET['id'];

// Consultar los datos del piloto
$query = "SELECT id, nombre, email, telefono, licencia, estado FROM pilotos WHERE id = ?";
$stmt = mysqli_prepare($conexion, $query);
mysqli_stmt_bind_param($stmt, "i", $pilotoId);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

if ($piloto = mysqli_fetch_assoc($resultado)) {
    echo json_encode(['success' => true, 'piloto' => $piloto]);
} else {
    echo json_encode(['success' => false, 'message' => 'Piloto no encontrado']);
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>