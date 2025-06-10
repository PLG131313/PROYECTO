<?php
// verificar_disponibilidad_piloto.php
function verificarDisponibilidadPiloto($idPiloto, $fecha, $conexion) {
    // Verificar si el piloto tiene un vuelo en la fecha especificada
    $consultaVuelo = "SELECT id FROM vuelos 
                      WHERE idPiloto = ? 
                      AND DATE(fecha_hora) = ? 
                      AND estado != 'cancelado'";
    $stmt = mysqli_prepare($conexion, $consultaVuelo);
    mysqli_stmt_bind_param($stmt, "is", $idPiloto, $fecha);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $tieneVuelo = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);

    if ($tieneVuelo) {
        return ['disponible' => false, 'motivo' => 'Ya tiene un vuelo programado para esta fecha'];
    }

    // Verificar si el piloto tuvo un vuelo en los 2 días anteriores (período de descanso)
    $consultaDescanso = "SELECT id FROM vuelos 
                         WHERE idPiloto = ? 
                         AND DATE(fecha_hora) BETWEEN DATE_SUB(?, INTERVAL 2 DAY) AND DATE_SUB(?, INTERVAL 1 DAY) 
                         AND estado = 'completado'";
    $stmt = mysqli_prepare($conexion, $consultaDescanso);
    mysqli_stmt_bind_param($stmt, "iss", $idPiloto, $fecha, $fecha);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $estaEnDescanso = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);

    if ($estaEnDescanso) {
        return ['disponible' => false, 'motivo' => 'Está en período de descanso'];
    }

    // Verificar si el piloto está de baja
    $consultaBaja = "SELECT id FROM pilotos WHERE id = ? AND estado = 'baja'";
    $stmt = mysqli_prepare($conexion, $consultaBaja);
    mysqli_stmt_bind_param($stmt, "i", $idPiloto);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $estaDeBaja = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);

    if ($estaDeBaja) {
        return ['disponible' => false, 'motivo' => 'El piloto está de baja'];
    }

    // Si no hay restricciones, el piloto está disponible
    return ['disponible' => true, 'motivo' => ''];
}
?>