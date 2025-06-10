<?php
session_start();

// Verificar si el usuario ha iniciado sesión como administrador
if (!isset($_SESSION['idusuario']) || $_SESSION['tipo_usuario'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Verificar si se proporcionó un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de vuelo no proporcionado']);
    exit;
}

require_once('conexion.php');

// Obtener el ID del vuelo
$vueloId = (int)$_GET['id'];

// Consultar los datos del vuelo
$query = "SELECT v.*, p.nombre as nombre_piloto 
          FROM vuelos v 
          LEFT JOIN pilotos p ON v.idPiloto = p.id 
          WHERE v.id = ?";
$stmt = mysqli_prepare($conexion, $query);
mysqli_stmt_bind_param($stmt, "i", $vueloId);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($resultado) === 0) {
    echo json_encode(['success' => false, 'message' => 'Vuelo no encontrado']);
    mysqli_stmt_close($stmt);
    mysqli_close($conexion);
    exit;
}

$vuelo = mysqli_fetch_assoc($resultado);
mysqli_stmt_close($stmt);

// Formatear la fecha y hora para el formulario
$fechaHora = new DateTime($vuelo['fecha_hora']);
$vuelo['fecha'] = $fechaHora->format('Y-m-d');
$vuelo['hora'] = $fechaHora->format('H:i');

// Obtener lista de pilotos disponibles para esta fecha (incluyendo el piloto actual)
$fecha = $vuelo['fecha'];
$idPilotoActual = $vuelo['idPiloto'];

$queryPilotos = "
    SELECT p.id, p.nombre, p.licencia 
    FROM pilotos p 
    WHERE p.estado != 'baja' 
    AND (p.id = ? OR p.id NOT IN (
        -- Pilotos que ya tienen vuelo ese día (excepto el piloto actual)
        SELECT DISTINCT v.idPiloto 
        FROM vuelos v 
        WHERE DATE(v.fecha_hora) = ? 
        AND v.estado != 'cancelado'
        AND v.id != ?
        
        UNION
        
        -- Pilotos que tuvieron vuelo en los 2 días anteriores (período de descanso)
        SELECT DISTINCT v2.idPiloto
        FROM vuelos v2
        WHERE DATE(v2.fecha_hora) BETWEEN DATE_SUB(?, INTERVAL 2 DAY) AND DATE_SUB(?, INTERVAL 1 DAY)
        AND v2.estado = 'completado'
        AND v2.idPiloto != ?
    ))
    ORDER BY p.nombre ASC
";

$stmtPilotos = mysqli_prepare($conexion, $queryPilotos);
mysqli_stmt_bind_param($stmtPilotos, "isisii", $idPilotoActual, $fecha, $vueloId, $fecha, $fecha, $idPilotoActual);
mysqli_stmt_execute($stmtPilotos);
$resultadoPilotos = mysqli_stmt_get_result($stmtPilotos);

$pilotos = [];
while ($piloto = mysqli_fetch_assoc($resultadoPilotos)) {
    $pilotos[] = $piloto;
}
mysqli_stmt_close($stmtPilotos);

// Obtener lista de aeropuertos/ciudades
$ciudades = [
    'Madrid' => 'Madrid',
    'Barcelona' => 'Barcelona',
    'Valencia' => 'Valencia',
    'Sevilla' => 'Sevilla',
    'Málaga' => 'Málaga',
    'Bilbao' => 'Bilbao',
    'Las Palmas' => 'Las Palmas',
    'Palma de Mayorca' => 'Palma de Mayorca'
];

// Preparar la respuesta
$respuesta = [
    'success' => true,
    'vuelo' => $vuelo,
    'pilotos' => $pilotos,
    'ciudades' => $ciudades
];

mysqli_close($conexion);

echo json_encode($respuesta);
?>