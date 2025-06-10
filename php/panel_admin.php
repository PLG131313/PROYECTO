<?php
session_start();

// Verificar si el usuario ha iniciado sesión como administrador
if (!isset($_SESSION['idusuario']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

require_once('conexion.php');

// Configuración de paginación
$registros_por_pagina = 7;

// Obtener páginas actuales para cada sección
$pagina_vuelos = isset($_GET['pagina_vuelos']) ? (int)$_GET['pagina_vuelos'] : 1;
$pagina_pilotos = isset($_GET['pagina_pilotos']) ? (int)$_GET['pagina_pilotos'] : 1;
$pagina_reservas = isset($_GET['pagina_reservas']) ? (int)$_GET['pagina_reservas'] : 1;

// Validar que las páginas sean válidas
$pagina_vuelos = max(1, $pagina_vuelos);
$pagina_pilotos = max(1, $pagina_pilotos);
$pagina_reservas = max(1, $pagina_reservas);

// Calcular offset para cada sección
$offset_vuelos = ($pagina_vuelos - 1) * $registros_por_pagina;
$offset_pilotos = ($pagina_pilotos - 1) * $registros_por_pagina;
$offset_reservas = ($pagina_reservas - 1) * $registros_por_pagina;

// Obtener filtros de reservas
$filtro_fecha = isset($_GET['filtro_fecha']) ? $_GET['filtro_fecha'] : '';
$filtro_reserva = isset($_GET['filtro_reserva']) ? $_GET['filtro_reserva'] : '';
$filtro_estado = isset($_GET['filtro_estado']) ? $_GET['filtro_estado'] : '';

// Obtener estadísticas para el dashboard
$adminId = $_SESSION['idusuario'];
$nombreAdmin = $_SESSION['nombre_admin'];

// Obtener número de vuelos activos (vuelos futuros)
$consultaVuelos = "SELECT COUNT(*) as total FROM vuelos WHERE fecha_hora >= NOW() AND estado != 'cancelado'";
$resultadoVuelos = mysqli_query($conexion, $consultaVuelos);
$vuelosActivos = 0;
if ($resultadoVuelos) {
    $vuelosActivos = mysqli_fetch_assoc($resultadoVuelos)['total'];
}

// Obtener número de reservas nuevas (últimas 24 horas) - USANDO TABLA COMPRAS
$consultaReservas = "SELECT COUNT(*) as total FROM compras WHERE fecha_compra >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
$resultadoReservas = mysqli_query($conexion, $consultaReservas);
$reservasNuevas = 0;
if ($resultadoReservas) {
    $reservasNuevas = mysqli_fetch_assoc($resultadoReservas)['total'];
}

// Obtener número de pilotos disponibles
$fechaActual = date('Y-m-d');
$consultaPilotosDisponibles = "
    SELECT COUNT(*) as total 
    FROM pilotos p 
    WHERE p.estado != 'baja' 
    AND p.id NOT IN (
        SELECT DISTINCT v.idPiloto 
        FROM vuelos v 
        WHERE DATE(v.fecha_hora) = '$fechaActual' 
        AND v.estado != 'cancelado'
        
        UNION
        
        SELECT DISTINCT v2.idPiloto
        FROM vuelos v2
        WHERE DATE(v2.fecha_hora) BETWEEN DATE_SUB('$fechaActual', INTERVAL 2 DAY) AND DATE_SUB('$fechaActual', INTERVAL 1 DAY)
        AND v2.estado = 'completado'
    )
";
$resultadoPilotosDisponibles = mysqli_query($conexion, $consultaPilotosDisponibles);
$pilotosDisponibles = 0;
if ($resultadoPilotosDisponibles) {
    $pilotosDisponibles = mysqli_fetch_assoc($resultadoPilotosDisponibles)['total'];
}

// Obtener próximos vuelos (solo 5 para el dashboard)
$consultaProximosVuelos = "SELECT v.id, v.codigo, v.fecha_hora, v.origen, v.destino, p.nombre as nombre_piloto
                          FROM vuelos v 
                          LEFT JOIN pilotos p ON v.idPiloto = p.id
                          WHERE v.fecha_hora >= NOW() 
                          AND v.estado != 'cancelado'
                          ORDER BY v.fecha_hora ASC 
                          LIMIT 5";
$resultadoProximosVuelos = mysqli_query($conexion, $consultaProximosVuelos);
$proximosVuelos = [];
if ($resultadoProximosVuelos) {
    $proximosVuelos = mysqli_fetch_all($resultadoProximosVuelos, MYSQLI_ASSOC);
}

// Obtener últimas reservas (solo 5 para el dashboard) - USANDO TABLA COMPRAS
$consultaUltimasReservas = "SELECT c.id as idReserva, c.fecha_compra, c.cliente_id as idCliente, 
                                  c.estado, c.precio_total,
                                  cl.nombre as nombre_cliente,
                                  v.codigo as codigo_vuelo,
                                  v.fecha_hora as fecha_vuelo
                           FROM compras c
                           LEFT JOIN clientes cl ON c.cliente_id = cl.id
                           LEFT JOIN vuelos v ON c.vuelo_id = v.id
                           ORDER BY c.fecha_compra DESC 
                           LIMIT 5";
$resultadoUltimasReservas = mysqli_query($conexion, $consultaUltimasReservas);
$ultimasReservas = [];
if ($resultadoUltimasReservas) {
    $ultimasReservas = mysqli_fetch_all($resultadoUltimasReservas, MYSQLI_ASSOC);
}

// ===== PAGINACIÓN PARA VUELOS =====
// Contar total de vuelos
$consultaTotalVuelos = "SELECT COUNT(*) as total FROM vuelos";
$resultadoTotalVuelos = mysqli_query($conexion, $consultaTotalVuelos);
$totalVuelos = mysqli_fetch_assoc($resultadoTotalVuelos)['total'];
$totalPaginasVuelos = ceil($totalVuelos / $registros_por_pagina);

// Validar página de vuelos
if ($pagina_vuelos > $totalPaginasVuelos && $totalPaginasVuelos > 0) {
    $pagina_vuelos = $totalPaginasVuelos;
    $offset_vuelos = ($pagina_vuelos - 1) * $registros_por_pagina;
}

// Obtener vuelos con paginación
$consultaTodosVuelos = "SELECT v.*, p.nombre as nombre_piloto 
                        FROM vuelos v 
                        LEFT JOIN pilotos p ON v.idPiloto = p.id 
                        ORDER BY v.fecha_hora DESC
                        LIMIT $registros_por_pagina OFFSET $offset_vuelos";
$resultadoTodosVuelos = mysqli_query($conexion, $consultaTodosVuelos);
$todosVuelos = [];
if ($resultadoTodosVuelos) {
    $todosVuelos = mysqli_fetch_all($resultadoTodosVuelos, MYSQLI_ASSOC);
}

// ===== PAGINACIÓN PARA PILOTOS =====
// Contar total de pilotos
$consultaTotalPilotos = "SELECT COUNT(*) as total FROM pilotos";
$resultadoTotalPilotos = mysqli_query($conexion, $consultaTotalPilotos);
$totalPilotos = mysqli_fetch_assoc($resultadoTotalPilotos)['total'];
$totalPaginasPilotos = ceil($totalPilotos / $registros_por_pagina);

// Validar página de pilotos
if ($pagina_pilotos > $totalPaginasPilotos && $totalPaginasPilotos > 0) {
    $pagina_pilotos = $totalPaginasPilotos;
    $offset_pilotos = ($pagina_pilotos - 1) * $registros_por_pagina;
}

// Obtener pilotos con paginación
$consultaTodosPilotos = "SELECT id, nombre, email, telefono, licencia, estado 
                         FROM pilotos 
                         ORDER BY id
                         LIMIT $registros_por_pagina OFFSET $offset_pilotos";
$resultadoTodosPilotos = mysqli_query($conexion, $consultaTodosPilotos);
$todosPilotos = [];
if ($resultadoTodosPilotos) {
    $todosPilotos = mysqli_fetch_all($resultadoTodosPilotos, MYSQLI_ASSOC);
}

// ===== PAGINACIÓN PARA RESERVAS CON FILTROS =====
// Construir consulta con filtros
$whereClause = "WHERE 1=1";
$parametros = [];
$tipos = '';

if (!empty($filtro_fecha)) {
    $whereClause .= " AND DATE(v.fecha_hora) = ?";
    $parametros[] = $filtro_fecha;
    $tipos .= 's';
}

if (!empty($filtro_reserva)) {
    $whereClause .= " AND c.id = ?";
    $parametros[] = (int)$filtro_reserva;
    $tipos .= 'i';
}

if (!empty($filtro_estado)) {
    $whereClause .= " AND c.estado = ?";
    $parametros[] = $filtro_estado;
    $tipos .= 's';
}

// Contar total de reservas con filtros
$consultaTotalReservas = "SELECT COUNT(*) as total 
                         FROM compras c
                         LEFT JOIN vuelos v ON c.vuelo_id = v.id
                         $whereClause";

if (!empty($parametros)) {
    $stmtCount = mysqli_prepare($conexion, $consultaTotalReservas);
    mysqli_stmt_bind_param($stmtCount, $tipos, ...$parametros);
    mysqli_stmt_execute($stmtCount);
    $resultadoTotalReservas = mysqli_stmt_get_result($stmtCount);
    $totalReservas = mysqli_fetch_assoc($resultadoTotalReservas)['total'];
    mysqli_stmt_close($stmtCount);
} else {
    $resultadoTotalReservas = mysqli_query($conexion, $consultaTotalReservas);
    $totalReservas = mysqli_fetch_assoc($resultadoTotalReservas)['total'];
}

$totalPaginasReservas = ceil($totalReservas / $registros_por_pagina);

// Validar página de reservas
if ($pagina_reservas > $totalPaginasReservas && $totalPaginasReservas > 0) {
    $pagina_reservas = $totalPaginasReservas;
    $offset_reservas = ($pagina_reservas - 1) * $registros_por_pagina;
}

// Obtener reservas con paginación y filtros
$consultaTodasReservas = "SELECT c.id as idReserva, c.cliente_id as idCliente, 
                                c.vuelo_id, c.num_pasajeros, c.precio_total,
                                c.fecha_compra, c.estado,
                                cl.nombre as nombre_cliente, cl.email as email_cliente,
                                v.codigo as codigo_vuelo, v.fecha_hora as fecha_vuelo,
                                p.nombre as nombre_piloto
                         FROM compras c
                         LEFT JOIN clientes cl ON c.cliente_id = cl.id
                         LEFT JOIN vuelos v ON c.vuelo_id = v.id
                         LEFT JOIN pilotos p ON v.idPiloto = p.id
                         $whereClause
                         ORDER BY c.fecha_compra DESC
                         LIMIT $registros_por_pagina OFFSET $offset_reservas";

if (!empty($parametros)) {
    $stmtReservas = mysqli_prepare($conexion, $consultaTodasReservas);
    mysqli_stmt_bind_param($stmtReservas, $tipos, ...$parametros);
    mysqli_stmt_execute($stmtReservas);
    $resultadoTodasReservas = mysqli_stmt_get_result($stmtReservas);
    $todasReservas = mysqli_fetch_all($resultadoTodasReservas, MYSQLI_ASSOC);
    mysqli_stmt_close($stmtReservas);
} else {
    $resultadoTodasReservas = mysqli_query($conexion, $consultaTodasReservas);
    $todasReservas = [];
    if ($resultadoTodasReservas) {
        $todasReservas = mysqli_fetch_all($resultadoTodasReservas, MYSQLI_ASSOC);
    }
}

// Preparar HTML para tabla de vuelos
$htmlTablaVuelos = '';
foreach ($todosVuelos as $vuelo) {
    $claseBadge = 'bg-primary';
    if ($vuelo['estado'] == 'en_vuelo') {
        $claseBadge = 'bg-info';
    } elseif ($vuelo['estado'] == 'completado') {
        $claseBadge = 'bg-success';
    } elseif ($vuelo['estado'] == 'retrasado') {
        $claseBadge = 'bg-warning';
    } elseif ($vuelo['estado'] == 'cancelado') {
        $claseBadge = 'bg-danger';
    }

    $fechaHora = new DateTime($vuelo['fecha_hora']);
    $fecha = $fechaHora->format('d/m/Y');
    $hora = $fechaHora->format('H:i');

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

    $origenNombre = isset($ciudades[$vuelo['origen']]) ? $ciudades[$vuelo['origen']] : $vuelo['origen'];
    $destinoNombre = isset($ciudades[$vuelo['destino']]) ? $ciudades[$vuelo['destino']] : $vuelo['destino'];
    $estadoMostrar = ucfirst($vuelo['estado']);

    $htmlTablaVuelos .= '
    <tr>
        <td>' . htmlspecialchars($vuelo['codigo']) . '</td>
        <td>' . htmlspecialchars($origenNombre) . '</td>
        <td>' . htmlspecialchars($destinoNombre) . '</td>
        <td>' . $fecha . '</td>
        <td>' . $hora . '</td>
        <td>' . htmlspecialchars($vuelo['nombre_piloto']) . '</td>
        <td><span class="badge ' . $claseBadge . '">' . htmlspecialchars($estadoMostrar) . '</span></td>
    </tr>';
}

if (empty($htmlTablaVuelos)) {
    $htmlTablaVuelos = '<tr><td colspan="7" class="text-center">No hay vuelos registrados</td></tr>';
}

// Preparar HTML para tabla de pilotos
$htmlTablaPilotos = '';
foreach ($todosPilotos as $piloto) {
    $idPiloto = $piloto['id'];

    $consultaVueloHoy = "SELECT id FROM vuelos WHERE idPiloto = $idPiloto AND DATE(fecha_hora) = '$fechaActual' AND estado != 'cancelado'";
    $resultadoVueloHoy = mysqli_query($conexion, $consultaVueloHoy);
    $tieneVueloHoy = mysqli_num_rows($resultadoVueloHoy) > 0;

    $consultaDescanso = "SELECT id, fecha_hora FROM vuelos 
                         WHERE idPiloto = $idPiloto 
                         AND DATE(fecha_hora) BETWEEN DATE_SUB('$fechaActual', INTERVAL 2 DAY) AND DATE_SUB('$fechaActual', INTERVAL 1 DAY) 
                         AND estado = 'completado'
                         ORDER BY fecha_hora DESC
                         LIMIT 1";
    $resultadoDescanso = mysqli_query($conexion, $consultaDescanso);
    $estaEnDescanso = mysqli_num_rows($resultadoDescanso) > 0;
    $vueloDescanso = $estaEnDescanso ? mysqli_fetch_assoc($resultadoDescanso) : null;

    $estadoReal = $piloto['estado'];
    $claseBadge = 'bg-success';
    $infoAdicional = '';

    if ($tieneVueloHoy) {
        $estadoReal = 'en_vuelo';
        $claseBadge = 'bg-danger';
    } elseif ($estaEnDescanso) {
        $estadoReal = 'descanso';
        $claseBadge = 'bg-warning';

        $fechaVuelo = new DateTime($vueloDescanso['fecha_hora']);
        $fechaFinDescanso = clone $fechaVuelo;
        $fechaFinDescanso->modify('+2 days');
        $fechaHoy = new DateTime();
        $diasRestantes = $fechaHoy->diff($fechaFinDescanso)->days;

        if ($diasRestantes > 0) {
            $infoAdicional = ' (termina en ' . $diasRestantes . ' día(s))';
        } else {
            $infoAdicional = ' (termina hoy)';
        }
    } elseif ($piloto['estado'] == 'baja') {
        $claseBadge = 'bg-secondary';
    }

    $estadoMostrar = ucfirst(str_replace('_', ' ', $estadoReal)) . $infoAdicional;

    $htmlTablaPilotos .= '
    <tr>
        <td>' . htmlspecialchars($piloto['id']) . '</td>
        <td>' . htmlspecialchars($piloto['nombre']) . '</td>
        <td>' . htmlspecialchars($piloto['email']) . '</td>
        <td>' . htmlspecialchars($piloto['licencia']) . '</td>
        <td>' . htmlspecialchars($piloto['telefono'] ?? 'No disponible') . '</td>
        <td><span class="badge ' . $claseBadge . '">' . htmlspecialchars($estadoMostrar) . '</span></td>
    </tr>';
}

if (empty($htmlTablaPilotos)) {
    $htmlTablaPilotos = '<tr><td colspan="6" class="text-center">No hay pilotos registrados</td></tr>';
}

// Preparar HTML para tabla de reservas - USANDO TABLA COMPRAS
$htmlTablaReservas = '';
foreach ($todasReservas as $reserva) {
    $estado = isset($reserva['estado']) ? $reserva['estado'] : 'confirmada';
    $claseBadge = 'bg-success';

    if ($estado == 'pendiente') {
        $claseBadge = 'bg-warning';
    } elseif ($estado == 'cancelada') {
        $claseBadge = 'bg-danger';
    }

    $fechaCompra = new DateTime($reserva['fecha_compra']);
    $fechaVuelo = new DateTime($reserva['fecha_vuelo']);

    $htmlTablaReservas .= '
    <tr>
        <td>' . htmlspecialchars($reserva['idReserva']) . '</td>
        <td>' . htmlspecialchars($reserva['nombre_cliente'] ?? 'Cliente #'.$reserva['idCliente']) . '</td>
        <td>' . htmlspecialchars($reserva['email_cliente'] ?? 'No disponible') . '</td>
        <td>' . htmlspecialchars($reserva['codigo_vuelo'] ?? 'N/A') . '</td>
        <td>' . $fechaVuelo->format('d/m/Y') . '</td>
        <td>' . htmlspecialchars($reserva['num_pasajeros']) . '</td>
        <td>' . htmlspecialchars($reserva['precio_total']) . '€</td>
        <td><span class="badge ' . $claseBadge . '">' . ucfirst($estado) . '</span></td>
        <td>
            <a href="ver_reserva.php?id=' . $reserva['idReserva'] . '" class="btn btn-sm btn-info btn-action" title="Ver detalle"><i class="fas fa-eye"></i></a>
            ' . ($estado != 'confirmada' ? '<a href="cambiar_estado.php?id=' . $reserva['idReserva'] . '&estado=confirmada" class="btn btn-sm btn-success btn-action" title="Confirmar" onclick="return confirm(\'¿Confirmar reserva?\')"><i class="fas fa-check"></i></a>' : '') . '
            ' . ($estado != 'cancelada' ? '<a href="cambiar_estado.php?id=' . $reserva['idReserva'] . '&estado=cancelada" class="btn btn-sm btn-danger btn-action" title="Cancelar" onclick="return confirm(\'¿Cancelar reserva?\')"><i class="fas fa-times"></i></a>' : '') . '
        </td>
    </tr>';
}

if (empty($htmlTablaReservas)) {
    $htmlTablaReservas = '<tr><td colspan="9" class="text-center">No hay reservas registradas</td></tr>';
}

// Función para generar HTML de paginación
function generarPaginacion($paginaActual, $totalPaginas, $tipoSeccion, $filtros = []) {
    if ($totalPaginas <= 1) {
        return '';
    }

    // Construir parámetros adicionales para mantener filtros
    $parametrosExtra = '';
    foreach ($filtros as $key => $value) {
        if (!empty($value)) {
            $parametrosExtra .= "&$key=" . urlencode($value);
        }
    }

    $html = '<nav aria-label="Paginación ' . $tipoSeccion . '">';
    $html .= '<ul class="pagination pagination-sm justify-content-center">';

    // Botón anterior
    if ($paginaActual > 1) {
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="?pagina_' . $tipoSeccion . '=' . ($paginaActual - 1) . $parametrosExtra . '">';
        $html .= '<i class="fas fa-chevron-left"></i> Anterior</a>';
        $html .= '</li>';
    } else {
        $html .= '<li class="page-item disabled">';
        $html .= '<span class="page-link"><i class="fas fa-chevron-left"></i> Anterior</span>';
        $html .= '</li>';
    }

    // Números de página
    $inicio = max(1, $paginaActual - 2);
    $fin = min($totalPaginas, $paginaActual + 2);

    // Primera página si no está en el rango
    if ($inicio > 1) {
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="?pagina_' . $tipoSeccion . '=1' . $parametrosExtra . '">1</a>';
        $html .= '</li>';
        if ($inicio > 2) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }

    // Páginas en el rango
    for ($i = $inicio; $i <= $fin; $i++) {
        if ($i == $paginaActual) {
            $html .= '<li class="page-item active">';
            $html .= '<span class="page-link">' . $i . '</span>';
            $html .= '</li>';
        } else {
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="?pagina_' . $tipoSeccion . '=' . $i . $parametrosExtra . '">' . $i . '</a>';
            $html .= '</li>';
        }
    }

    // Última página si no está en el rango
    if ($fin < $totalPaginas) {
        if ($fin < $totalPaginas - 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="?pagina_' . $tipoSeccion . '=' . $totalPaginas . $parametrosExtra . '">' . $totalPaginas . '</a>';
        $html .= '</li>';
    }

    // Botón siguiente
    if ($paginaActual < $totalPaginas) {
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="?pagina_' . $tipoSeccion . '=' . ($paginaActual + 1) . $parametrosExtra . '">';
        $html .= 'Siguiente <i class="fas fa-chevron-right"></i></a>';
        $html .= '</li>';
    } else {
        $html .= '<li class="page-item disabled">';
        $html .= '<span class="page-link">Siguiente <i class="fas fa-chevron-right"></i></span>';
        $html .= '</li>';
    }

    $html .= '</ul>';
    $html .= '</nav>';

    return $html;
}

// Generar HTML de paginación para cada sección
$paginacionVuelos = generarPaginacion($pagina_vuelos, $totalPaginasVuelos, 'vuelos');
$paginacionPilotos = generarPaginacion($pagina_pilotos, $totalPaginasPilotos, 'pilotos');
$paginacionReservas = generarPaginacion($pagina_reservas, $totalPaginasReservas, 'reservas', [
    'filtro_fecha' => $filtro_fecha,
    'filtro_reserva' => $filtro_reserva,
    'filtro_estado' => $filtro_estado
]);

// Cerrar la conexión
mysqli_close($conexion);

// Incluir la vista
include('../Vistas/HTMLadmin.php');
?>
