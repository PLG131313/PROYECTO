<?php
session_start();
require_once 'conexion.php';

header('Content-Type: application/json');

// Verificar que el usuario esté logueado como cliente
if (!isset($_SESSION['idusuario']) || $_SESSION['tipo_usuario'] !== 'cliente') {
    echo json_encode([
        'success' => false,
        'message' => 'No has iniciado sesión como cliente',
        'reservas' => []
    ]);
    exit();
}

$id_cliente = $_SESSION['idusuario'];

try {
    // Obtener todas las reservas del cliente
    $sql = "SELECT r.*, v.codigo as codigo_vuelo, p.nombre as nombre_piloto 
            FROM reservas r 
            LEFT JOIN vuelos v ON (v.origen = r.ciudad_inicial AND v.destino = r.ciudad_final AND DATE(v.fecha_hora) = DATE(r.fecha_vuelo))
            LEFT JOIN pilotos p ON r.idPiloto = p.id
            WHERE r.idCliente = ?
            ORDER BY r.fecha_reserva DESC";

    $stmt = mysqli_prepare($conexion, $sql);
    if (!$stmt) {
        throw new Exception("Error preparando consulta: " . mysqli_error($conexion));
    }

    mysqli_stmt_bind_param($stmt, 'i', $id_cliente);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    $reservas = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $reservas[] = [
            'idReserva' => $row['idReserva'],
            'fecha_reserva' => $row['fecha_reserva'],
            'fecha_vuelo' => $row['fecha_vuelo'],
            'ciudad_inicial' => $row['ciudad_inicial'],
            'ciudad_final' => $row['ciudad_final'],
            'clase_vuelo' => $row['clase_vuelo'],
            'estado' => $row['estado'],
            'total' => $row['total'] ?? 0,
            'observaciones' => $row['observaciones'] ?? '',
            'codigo_vuelo' => $row['codigo_vuelo'] ?? 'N/A',
            'nombre_piloto' => $row['nombre_piloto'] ?? 'No asignado'
        ];
    }

    mysqli_stmt_close($stmt);

    echo json_encode([
        'success' => true,
        'message' => count($reservas) > 0 ? 'Reservas encontradas' : 'No tienes reservas',
        'reservas' => $reservas,
        'total' => count($reservas)
    ]);

} catch (Exception $e) {
    error_log("Error obteniendo reservas: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener las reservas: ' . $e->getMessage(),
        'reservas' => []
    ]);
}

mysqli_close($conexion);
?>
