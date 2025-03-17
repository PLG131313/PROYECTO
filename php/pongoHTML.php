<?php
/**
 * Archivo con funciones para generar HTML
 */

/**
 * Genera el HTML para mostrar la lista de reservas
 *
 * @param array $reservas Lista de reservas del piloto
 * @return string HTML generado
 */
function generarHtmlReservas($reservas) {
    $html = '';

    if (empty($reservas)) {
        $html = '<p class="text-center"><i class="fas fa-info-circle me-2"></i>No tienes vuelos programados.</p>';
    } else {
        foreach ($reservas as $reserva) {
            $html .= '
            <div class="border-bottom mb-3 pb-2">
                <h5><i class="fas fa-plane-departure me-2"></i> ' . $reserva['ciudad_inicial'] . ' → ' . $reserva['ciudad_final'] . '</h5>
                <p><strong>Pasajero:</strong> ' . ($reserva['nombre_cliente'] ?? 'No asignado') . '</p>
                <p><strong>ID Reserva:</strong> ' . $reserva['idReserva'] . '</p>
                <p><strong>Fecha de Vuelo:</strong> ' . date('d/m/Y H:i', strtotime($reserva['fecha_vuelo'])) . '</p>
                <p><strong>Reservado el:</strong> ' . date('d/m/Y', strtotime($reserva['fecha_reserva'])) . '</p>
            </div>';
        }
    }

    return $html;
}

/**
 * Genera el HTML para los botones de paginación
 *
 * @param int $paginaActual Número de página actual
 * @param int $totalPaginas Total de páginas disponibles
 * @return string HTML generado
 */
function generarHtmlPaginacion($paginaActual, $totalPaginas) {
    $html = '<div class="d-flex justify-content-between mt-3">';

    if ($paginaActual > 1) {
        $html .= '<a href="?pagina=' . ($paginaActual - 1) . '" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Anterior</a>';
    } else {
        $html .= '<button class="btn btn-secondary" disabled><i class="fas fa-arrow-left"></i> Anterior</button>';
    }

    if ($paginaActual < $totalPaginas) {
        $html .= '<a href="?pagina=' . ($paginaActual + 1) . '" class="btn btn-primary">Siguiente <i class="fas fa-arrow-right"></i></a>';
    } else {
        $html .= '<button class="btn btn-secondary" disabled>Siguiente <i class="fas fa-arrow-right"></i></button>';
    }

    $html .= '</div>';

    return $html;
}
?>