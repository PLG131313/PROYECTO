<?php
session_start();

// Verificar si el usuario ha iniciado sesión como administrador
if (!isset($_SESSION['idusuario']) || $_SESSION['tipo_usuario'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Verificar si se proporcionó una fecha
if (!isset($_GET['fecha']) || empty($_GET['fecha'])) {
    echo json_encode(['success' => false, 'message' => 'Fecha no proporcionada']);
    exit;
}

require_once('conexion.php');

// Obtener la fecha seleccionada
$fecha = $_GET['fecha'];

// Validar formato de fecha (YYYY-MM-DD)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    echo json_encode(['success' => false, 'message' => 'Formato de fecha inválido']);
    exit;
}

// Consultar pilotos disponibles para la fecha seleccionada
// Un piloto NO está disponible si:
// 1. Ya tiene un vuelo programado para esa fecha
// 2. Tuvo un vuelo en los 2 días anteriores (período de descanso)
// 3. Está de baja
$query = "
    SELECT p.id, p.nombre, p.licencia 
    FROM pilotos p 
    WHERE p.estado != 'baja' 
    AND p.id NOT IN (
        -- Pilotos que ya tienen vuelo ese día
        SELECT DISTINCT v.idPiloto 
        FROM vuelos v 
        WHERE DATE(v.fecha_hora) = ? 
        AND v.estado != 'cancelado'
        
        UNION
        
        -- Pilotos que tuvieron vuelo en los 2 días anteriores (período de descanso)
        SELECT DISTINCT v2.idPiloto
        FROM vuelos v2
        WHERE DATE(v2.fecha_hora) BETWEEN DATE_SUB(?, INTERVAL 2 DAY) AND DATE_SUB(?, INTERVAL 1 DAY)
        AND v2.estado = 'completado'
    )
    ORDER BY p.nombre ASC
";

$stmt = mysqli_prepare($conexion, $query);
mysqli_stmt_bind_param($stmt, "sss", $fecha, $fecha, $fecha);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

$pilotos = [];
while ($piloto = mysqli_fetch_assoc($resultado)) {
    $pilotos[] = $piloto;
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);

echo json_encode(['success' => true, 'pilotos' => $pilotos]);
?>