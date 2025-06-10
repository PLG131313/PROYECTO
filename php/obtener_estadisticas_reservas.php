<?php
session_start();

// Verificar si el usuario ha iniciado sesión como administrador
if (!isset($_SESSION['idusuario']) || $_SESSION['tipo_usuario'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

require_once('conexion.php');

// Obtener estadísticas para el dashboard
$estadisticas = [];

// Total de reservas
$consultaTotal = "SELECT COUNT(*) as total FROM compras";
$resultadoTotal = mysqli_query($conexion, $consultaTotal);
$estadisticas['total_reservas'] = mysqli_fetch_assoc($resultadoTotal)['total'];

// Reservas confirmadas
$consultaConfirmadas = "SELECT COUNT(*) as total FROM compras WHERE estado = 'confirmada'";
$resultadoConfirmadas = mysqli_query($conexion, $consultaConfirmadas);
$estadisticas['reservas_confirmadas'] = mysqli_fetch_assoc($resultadoConfirmadas)['total'];

// Reservas canceladas
$consultaCanceladas = "SELECT COUNT(*) as total FROM compras WHERE estado = 'cancelada'";
$resultadoCanceladas = mysqli_query($conexion, $consultaCanceladas);
$estadisticas['reservas_canceladas'] = mysqli_fetch_assoc($resultadoCanceladas)['total'];

// Reservas de hoy
$consultaHoy = "SELECT COUNT(*) as total FROM compras WHERE DATE(fecha_compra) = CURDATE()";
$resultadoHoy = mysqli_query($conexion, $consultaHoy);
$estadisticas['reservas_hoy'] = mysqli_fetch_assoc($resultadoHoy)['total'];

// Ingresos totales
$consultaIngresos = "SELECT SUM(precio_total) as total FROM compras WHERE estado = 'confirmada'";
$resultadoIngresos = mysqli_query($conexion, $consultaIngresos);
$estadisticas['ingresos_totales'] = mysqli_fetch_assoc($resultadoIngresos)['total'] ?? 0;

// Últimas 5 reservas
$consultaUltimas = "
    SELECT 
        c.id as idReserva,
        c.cliente_id as idCliente,
        c.estado,
        c.fecha_compra,
        cl.nombre as nombre_cliente
    FROM compras c
    LEFT JOIN clientes cl ON c.cliente_id = cl.id
    ORDER BY c.fecha_compra DESC
    LIMIT 5
";
$resultadoUltimas = mysqli_query($conexion, $consultaUltimas);
$ultimasReservas = [];
while ($reserva = mysqli_fetch_assoc($resultadoUltimas)) {
    $ultimasReservas[] = $reserva;
}
$estadisticas['ultimas_reservas'] = $ultimasReservas;

echo json_encode([
    'success' => true,
    'estadisticas' => $estadisticas
]);

mysqli_close($conexion);
?>
