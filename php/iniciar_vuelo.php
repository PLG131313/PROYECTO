<?php
session_start();

// Verificar si el piloto ha iniciado sesión
if (!isset($_SESSION['idusuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Verificar si es una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

require_once('conexion.php');

$vuelo_id = isset($_POST['vuelo_id']) ? (int)$_POST['vuelo_id'] : 0;
$piloto_id = $_SESSION['idusuario'];

if ($vuelo_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de vuelo inválido']);
    exit;
}

try {
    // Verificar que el vuelo pertenece al piloto y está programado
    $queryVerificar = "
        SELECT * FROM vuelos 
        WHERE id = ? AND piloto_id = ? AND estado = 'programado'
        AND DATE(fecha_hora) = CURDATE()
    ";

    $stmtVerificar = mysqli_prepare($conexion, $queryVerificar);
    mysqli_stmt_bind_param($stmtVerificar, "ii", $vuelo_id, $piloto_id);
    mysqli_stmt_execute($stmtVerificar);
    $resultadoVerificar = mysqli_stmt_get_result($stmtVerificar);

    if (mysqli_num_rows($resultadoVerificar) == 0) {
        throw new Exception("No se puede iniciar este vuelo. Verifique que sea el día correcto y que el vuelo esté programado.");
    }

    mysqli_stmt_close($stmtVerificar);

    // Actualizar estado del vuelo
    $queryActualizar = "UPDATE vuelos SET estado = 'en_vuelo' WHERE id = ?";
    $stmtActualizar = mysqli_prepare($conexion, $queryActualizar);
    mysqli_stmt_bind_param($stmtActualizar, "i", $vuelo_id);

    if (!mysqli_stmt_execute($stmtActualizar)) {
        throw new Exception("Error al actualizar el estado del vuelo");
    }

    mysqli_stmt_close($stmtActualizar);

    echo json_encode([
        'success' => true,
        'message' => 'Vuelo iniciado exitosamente'
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

mysqli_close($conexion);
?>
