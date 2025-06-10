<?php
session_start();

// Verificar si el usuario ha iniciado sesión como administrador
if (!isset($_SESSION['idusuario']) || $_SESSION['tipo_usuario'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

require_once('conexion.php');

// Obtener parámetros de filtro
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : '';
$vuelo = isset($_GET['vuelo']) ? $_GET['vuelo'] : '';
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';

// Construir la consulta base usando la tabla 'compras'
$consulta = "
    SELECT 
        c.id as idReserva,
        c.cliente_id as idCliente,
        c.vuelo_id,
        c.num_pasajeros,
        c.precio_total as total,
        c.fecha_compra,
        c.estado,
        cl.nombre as nombre_cliente,
        cl.email as email_cliente,
        cl.telefono as telefono_cliente,
        v.codigo as codigo_vuelo,
        v.origen,
        v.destino,
        v.fecha_hora as fecha_vuelo,
        p.nombre as nombre_piloto
    FROM compras c
    LEFT JOIN clientes cl ON c.cliente_id = cl.id
    LEFT JOIN vuelos v ON c.vuelo_id = v.id
    LEFT JOIN pilotos p ON v.idPiloto = p.id
    WHERE 1=1
";

$parametros = [];
$tipos = '';

// Aplicar filtros
if (!empty($fecha)) {
    $consulta .= " AND DATE(v.fecha_hora) = ?";
    $parametros[] = $fecha;
    $tipos .= 's';
}

if (!empty($vuelo)) {
    $consulta .= " AND c.id = ?";
    $parametros[] = (int)$vuelo;
    $tipos .= 'i';
}

if (!empty($estado)) {
    $consulta .= " AND c.estado = ?";
    $parametros[] = $estado;
    $tipos .= 's';
}

// Ordenar por fecha de compra más reciente
$consulta .= " ORDER BY c.fecha_compra DESC";

// Preparar y ejecutar la consulta
$stmt = mysqli_prepare($conexion, $consulta);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta: ' . mysqli_error($conexion)]);
    exit;
}

// Vincular parámetros si existen
if (!empty($parametros)) {
    mysqli_stmt_bind_param($stmt, $tipos, ...$parametros);
}

mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

$reservas = [];
while ($reserva = mysqli_fetch_assoc($resultado)) {
    $reservas[] = $reserva;
}

echo json_encode([
    'success' => true,
    'reservas' => $reservas
]);

mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>
