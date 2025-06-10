<?php
session_start();

// Verificar si el usuario ha iniciado sesión como administrador
if (!isset($_SESSION['idusuario']) || $_SESSION['tipo_usuario'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

require_once('conexion.php');

// Verificar que se haya proporcionado un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de reserva no válido']);
    exit;
}

$idReserva = (int)$_GET['id'];

// Obtener los datos completos de la reserva
$consulta = "
    SELECT 
        c.id as idReserva,
        c.cliente_id as idCliente,
        c.vuelo_id as idVuelo,
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
        v.capacidad,
        v.precio as precio_base,
        p.nombre as nombre_piloto,
        p.licencia as licencia_piloto
    FROM compras c
    LEFT JOIN clientes cl ON c.cliente_id = cl.id
    LEFT JOIN vuelos v ON c.vuelo_id = v.id
    LEFT JOIN pilotos p ON v.idPiloto = p.id
    WHERE c.id = ?
";

$stmt = mysqli_prepare($conexion, $consulta);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta']);
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $idReserva);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

if ($reserva = mysqli_fetch_assoc($resultado)) {
    // Definir ciudades para mostrar nombres completos
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

    // Formatear datos adicionales
    $reserva['ciudad_inicial_completa'] = isset($ciudades[$reserva['origen']]) ? $ciudades[$reserva['origen']] : $reserva['origen'];
    $reserva['ciudad_final_completa'] = isset($ciudades[$reserva['destino']]) ? $ciudades[$reserva['destino']] : $reserva['destino'];

    // Formatear fechas
    $reserva['fecha_reserva_formateada'] = date('d/m/Y H:i', strtotime($reserva['fecha_compra']));
    $reserva['fecha_vuelo_formateada'] = date('d/m/Y H:i', strtotime($reserva['fecha_vuelo']));

    // Clase de vuelo (por defecto económica)
    $reserva['clase_vuelo'] = 'Económica';

    echo json_encode([
        'success' => true,
        'reserva' => $reserva
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Reserva no encontrada']);
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>
