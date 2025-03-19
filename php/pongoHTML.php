<?php

/*parte mostrar reservas*/
function generarHtmlReservas($reservas) {
    $html = '';  // meto html

    if (empty($reservas)) {  // si el array esta vacio es que no hay reservas
        $html = '<p class="text-center"><i class="fas fa-info-circle me-2"></i>No tienes vuelos programados.</p>';  //mensaje de que no hay reservas
    } else {  // recorre cuando hay reservas
        foreach ($reservas as $reserva) {  // para cada reserva
            $html .= '
            <div class="border-bottom mb-3 pb-2">
                <h5><i class="fas fa-plane-departure me-2"></i> ' . $reserva['ciudad_inicial'] . ' → ' . $reserva['ciudad_final'] . '</h5>
                <p><strong>Pasajero:</strong> ' . ($reserva['nombre_cliente'] ?? 'No asignado') . '</p>
                <p><strong>ID Reserva:</strong> ' . $reserva['idReserva'] . '</p>
                <p><strong>Fecha de Vuelo:</strong> ' . date('d/m/Y H:i', strtotime($reserva['fecha_vuelo'])) . '</p>
                <p><strong>Reservado el:</strong> ' . date('d/m/Y', strtotime($reserva['fecha_reserva'])) . '</p>
            </div>';
            // - ciudad origen y destino
            // - ele nombre
            // - ide de la reserva
            // - fecha del vuelo
            // - fecha de la reserva
        }
    }

    return $html;  // pongo html
}

/*parte de paginacion*/
function generarHtmlPaginacion($paginaActual, $totalPaginas) {
    $html = '<div class="d-flex justify-content-between mt-3">';  //html

    if ($paginaActual > 1) {  // si no es la primera
        $html .= '<a href="?pagina=' . ($paginaActual - 1) . '" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Anterior</a>';
        // anterior pagina
    } else {  // Si es la primera
        $html .= '<button class="btn btn-secondary" disabled><i class="fas fa-arrow-left"></i> Anterior</button>';
        // anterior deshabilitado
    }

    if ($paginaActual < $totalPaginas) {  // si no es la ultima
        $html .= '<a href="?pagina=' . ($paginaActual + 1) . '" class="btn btn-primary">Siguiente <i class="fas fa-arrow-right"></i></a>';
        // siguiente que pasa pagina
    } else {  // si estoy en la ultima
        $html .= '<button class="btn btn-secondary" disabled>Siguiente <i class="fas fa-arrow-right"></i></button>';
        // deshabilito siguiente
    }

    $html .= '</div>';  // Cierro

    return $html;  // pongo html.
}
?>
