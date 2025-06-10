<?php
// Archivo para obtener todos los datos del dashboard
// Este archivo debe ser incluido en panel_admin.php antes de incluir HTMLadmin.php

// Asegurar que tenemos una conexión válida
if (!isset($conexion) || !($conexion instanceof mysqli) || $conexion->connect_error) {
    require_once('conexion.php');
}

// Inicializar variables con valores por defecto
$vuelosActivos = 0;
$reservasNuevas = 0;
$pilotosDisponibles = 0;
$proximosVuelos = [];
$ultimasReservas = [];

try {
    // 1. Contar vuelos activos (programados y en vuelo)
    $consultaVuelosActivos = "SELECT COUNT(*) as total FROM vuelos WHERE estado IN ('programado', 'en_vuelo') AND fecha_hora >= NOW()";
    $resultadoVuelosActivos = mysqli_query($conexion, $consultaVuelosActivos);
    if ($resultadoVuelosActivos) {
        $vuelosActivos = mysqli_fetch_assoc($resultadoVuelosActivos)['total'];
    }

    // 2. Contar reservas de hoy (usando la tabla correcta 'compras')
    $consultaReservasHoy = "SELECT COUNT(*) as total FROM compras WHERE DATE(fecha_compra) = CURDATE()";
    $resultadoReservasHoy = mysqli_query($conexion, $consultaReservasHoy);
    if ($resultadoReservasHoy) {
        $reservasNuevas = mysqli_fetch_assoc($resultadoReservasHoy)['total'];
    }

    // 3. Contar pilotos disponibles
    $consultaPilotosDisponibles = "SELECT COUNT(*) as total FROM pilotos WHERE estado = 'disponible'";
    $resultadoPilotosDisponibles = mysqli_query($conexion, $consultaPilotosDisponibles);
    if ($resultadoPilotosDisponibles) {
        $pilotosDisponibles = mysqli_fetch_assoc($resultadoPilotosDisponibles)['total'];
    }

    // 4. Obtener próximos 5 vuelos
    $consultaProximosVuelos = "
        SELECT 
            v.codigo,
            v.origen,
            v.destino,
            v.fecha_hora,
            p.nombre as piloto_nombre
        FROM vuelos v
        LEFT JOIN pilotos p ON v.idPiloto = p.id
        WHERE v.fecha_hora >= NOW() AND v.estado != 'cancelado'
        ORDER BY v.fecha_hora ASC
        LIMIT 5
    ";
    $resultadoProximosVuelos = mysqli_query($conexion, $consultaProximosVuelos);
    if ($resultadoProximosVuelos) {
        while ($vuelo = mysqli_fetch_assoc($resultadoProximosVuelos)) {
            $proximosVuelos[] = $vuelo;
        }
    }

    // 5. Obtener últimas 5 reservas (usando la tabla correcta 'compras')
    $consultaUltimasReservas = "
        SELECT 
            c.id as idReserva,
            c.cliente_id as idCliente,
            c.vuelo_id,
            c.num_pasajeros,
            c.precio_total,
            c.fecha_compra,
            c.estado,
            cl.nombre as nombre_cliente,
            v.codigo as codigo_vuelo
        FROM compras c
        LEFT JOIN clientes cl ON c.cliente_id = cl.id
        LEFT JOIN vuelos v ON c.vuelo_id = v.id
        ORDER BY c.fecha_compra DESC
        LIMIT 5
    ";
    $resultadoUltimasReservas = mysqli_query($conexion, $consultaUltimasReservas);
    if ($resultadoUltimasReservas) {
        while ($reserva = mysqli_fetch_assoc($resultadoUltimasReservas)) {
            $ultimasReservas[] = $reserva;
        }
    }

} catch (Exception $e) {
    // En caso de error, mantener los valores por defecto
    error_log("Error en dashboard_data.php: " . $e->getMessage());
}
?>
