<?php
session_start();

// Verificar si el usuario ha iniciado sesión como cliente
if (!isset($_SESSION['idusuario']) || $_SESSION['tipo_usuario'] !== 'cliente') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Verificar si es una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

require_once('conexion.php');

$compra_id = isset($_POST['compra_id']) ? (int)$_POST['compra_id'] : 0;
$cliente_id = $_SESSION['idusuario'];

if ($compra_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de compra inválido']);
    exit;
}

try {
    // Verificar que la compra pertenece al cliente y está confirmada
    $queryVerificar = "
        SELECT c.*, v.fecha_hora 
        FROM compras c 
        JOIN vuelos v ON c.vuelo_id = v.id 
        WHERE c.id = ? AND c.cliente_id = ? AND c.estado = 'confirmada'
    ";

    $stmtVerificar = mysqli_prepare($conexion, $queryVerificar);
    mysqli_stmt_bind_param($stmtVerificar, "ii", $compra_id, $cliente_id);
    mysqli_stmt_execute($stmtVerificar);
    $resultado = mysqli_stmt_get_result($stmtVerificar);
    $compra = mysqli_fetch_assoc($resultado);

    if (!$compra) {
        throw new Exception("Compra no encontrada o no autorizada");
    }

    // Verificar que el vuelo no haya pasado
    if (strtotime($compra['fecha_hora']) <= time()) {
        throw new Exception("No se puede cancelar un vuelo que ya ha pasado");
    }

    mysqli_stmt_close($stmtVerificar);

    // Actualizar estado de la compra
    $queryActualizar = "UPDATE compras SET estado = 'cancelada' WHERE id = ?";
    $stmtActualizar = mysqli_prepare($conexion, $queryActualizar);
    mysqli_stmt_bind_param($stmtActualizar, "i", $compra_id);

    if (!mysqli_stmt_execute($stmtActualizar)) {
        throw new Exception("Error al cancelar la compra");
    }

    mysqli_stmt_close($stmtActualizar);

    echo json_encode([
        'success' => true,
        'message' => 'Vuelo cancelado exitosamente'
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

mysqli_close($conexion);
?>
