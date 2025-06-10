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

// Obtener datos del formulario
$origen = isset($_POST['origen']) ? trim($_POST['origen']) : '';
$destino = isset($_POST['destino']) ? trim($_POST['destino']) : '';
$fecha = isset($_POST['fecha']) ? trim($_POST['fecha']) : '';

// Validar datos
if (empty($origen) || empty($destino) || empty($fecha)) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
    exit;
}

if ($origen === $destino) {
    echo json_encode(['success' => false, 'message' => 'El origen y destino no pueden ser iguales']);
    exit;
}

// Validar formato de fecha
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    echo json_encode(['success' => false, 'message' => 'Formato de fecha inválido']);
    exit;
}

// Verificar que la fecha no sea en el pasado
if (strtotime($fecha) < strtotime(date('Y-m-d'))) {
    echo json_encode(['success' => false, 'message' => 'No se pueden buscar vuelos en fechas pasadas']);
    exit;
}

try {
    // Buscar vuelos disponibles
    $query = "
        SELECT v.*, 
               (v.capacidad - COALESCE(SUM(c.num_pasajeros), 0)) as asientos_disponibles
        FROM vuelos v
        LEFT JOIN compras c ON v.id = c.vuelo_id AND c.estado = 'confirmada'
        WHERE v.origen = ? 
        AND v.destino = ? 
        AND DATE(v.fecha_hora) = ?
        AND v.estado = 'programado'
        GROUP BY v.id
        HAVING asientos_disponibles > 0
        ORDER BY v.fecha_hora ASC
    ";

    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, "sss", $origen, $destino, $fecha);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    $vuelos = [];
    while ($vuelo = mysqli_fetch_assoc($resultado)) {
        $vuelos[] = $vuelo;
    }

    mysqli_stmt_close($stmt);

    echo json_encode([
        'success' => true,
        'vuelos' => $vuelos,
        'total' => count($vuelos)
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al buscar vuelos: ' . $e->getMessage()]);
}

mysqli_close($conexion);
?>
