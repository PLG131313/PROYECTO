<?php
session_start();

// Verificar si el piloto ha iniciado sesión
if (!isset($_SESSION['idusuario'])) {
    header('Location: ../index.php');
    exit;
}

require_once('../php/conexion.php');
require_once('../php/configuracion_paginacion.php');

$piloto_id = $_SESSION['idusuario'];

// Configuración de paginación avanzada
$vuelos_por_pagina = PaginacionConfig::obtenerElementosPorPagina('vuelos');
$pagina_actual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina_actual - 1) * $vuelos_por_pagina;

// Filtros adicionales
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$filtro_fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : '';
$filtro_fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : '';

// Construir WHERE clause para filtros
$where_conditions = ["v.idPiloto = ?"];
$params = [$piloto_id];
$param_types = "i";

if (!empty($filtro_estado)) {
    $where_conditions[] = "v.estado = ?";
    $params[] = $filtro_estado;
    $param_types .= "s";
}

if (!empty($filtro_fecha_desde)) {
    $where_conditions[] = "DATE(v.fecha_hora) >= ?";
    $params[] = $filtro_fecha_desde;
    $param_types .= "s";
} else {
    // Por defecto, solo vuelos futuros
    $where_conditions[] = "v.fecha_hora >= NOW()";
}

if (!empty($filtro_fecha_hasta)) {
    $where_conditions[] = "DATE(v.fecha_hora) <= ?";
    $params[] = $filtro_fecha_hasta;
    $param_types .= "s";
}

$where_clause = "WHERE " . implode(" AND ", $where_conditions);

// Obtener información del piloto
$queryPiloto = "SELECT * FROM pilotos WHERE id = ?";
$stmtPiloto = mysqli_prepare($conexion, $queryPiloto);
mysqli_stmt_bind_param($stmtPiloto, "i", $piloto_id);
mysqli_stmt_execute($stmtPiloto);
$resultadoPiloto = mysqli_stmt_get_result($stmtPiloto);
$piloto = mysqli_fetch_assoc($resultadoPiloto);
mysqli_stmt_close($stmtPiloto);

if (!$piloto) {
    die('Error: Piloto no encontrado');
}

// Contar total de vuelos con filtros
$queryTotal = "SELECT COUNT(*) as total FROM vuelos v $where_clause";
$stmtTotal = mysqli_prepare($conexion, $queryTotal);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmtTotal, $param_types, ...$params);
}
mysqli_stmt_execute($stmtTotal);
$resultadoTotal = mysqli_stmt_get_result($stmtTotal);
$total_vuelos = mysqli_fetch_assoc($resultadoTotal)['total'];
mysqli_stmt_close($stmtTotal);

// Calcular total de páginas
$total_paginas = ceil($total_vuelos / $vuelos_por_pagina);

// Obtener vuelos paginados con filtros
$queryVuelos = "
    SELECT v.*, 
           COUNT(c.id) as total_reservas,
           COALESCE(SUM(c.num_pasajeros), 0) as pasajeros_confirmados
    FROM vuelos v
    LEFT JOIN compras c ON v.id = c.vuelo_id AND c.estado = 'confirmada'
    $where_clause
    GROUP BY v.id
    ORDER BY v.fecha_hora ASC
    LIMIT ? OFFSET ?
";

$params_paginacion = array_merge($params, [$vuelos_por_pagina, $offset]);
$param_types_paginacion = $param_types . "ii";

$stmtVuelos = mysqli_prepare($conexion, $queryVuelos);
mysqli_stmt_bind_param($stmtVuelos, $param_types_paginacion, ...$params_paginacion);
mysqli_stmt_execute($stmtVuelos);
$resultadoVuelos = mysqli_stmt_get_result($stmtVuelos);
$vuelos = mysqli_fetch_all($resultadoVuelos, MYSQLI_ASSOC);
mysqli_stmt_close($stmtVuelos);

// Obtener estadísticas del piloto
$queryEstadisticas = "
    SELECT 
        COUNT(*) as total_vuelos,
        COUNT(CASE WHEN fecha_hora >= NOW() THEN 1 END) as vuelos_pendientes,
        COUNT(CASE WHEN fecha_hora < NOW() THEN 1 END) as vuelos_completados,
        COUNT(CASE WHEN estado = 'programado' THEN 1 END) as vuelos_programados,
        COUNT(CASE WHEN estado = 'en_vuelo' THEN 1 END) as vuelos_en_curso
    FROM vuelos 
    WHERE idPiloto = ?
";

$stmtStats = mysqli_prepare($conexion, $queryEstadisticas);
mysqli_stmt_bind_param($stmtStats, "i", $piloto_id);
mysqli_stmt_execute($stmtStats);
$resultadoStats = mysqli_stmt_get_result($stmtStats);
$estadisticas = mysqli_fetch_assoc($resultadoStats);
mysqli_stmt_close($stmtStats);

// Obtener próximo vuelo
$queryProximo = "
    SELECT * FROM vuelos 
    WHERE idPiloto = ? 
    AND fecha_hora >= NOW() 
    ORDER BY fecha_hora ASC 
    LIMIT 1
";

$stmtProximo = mysqli_prepare($conexion, $queryProximo);
mysqli_stmt_bind_param($stmtProximo, "i", $piloto_id);
mysqli_stmt_execute($stmtProximo);
$resultadoProximo = mysqli_stmt_get_result($stmtProximo);
$proximoVuelo = mysqli_fetch_assoc($resultadoProximo);
mysqli_stmt_close($stmtProximo);

mysqli_close($conexion);

// Función para construir URL con parámetros
function construirUrl($nuevos_params = []) {
    $params_actuales = $_GET;
    $params_finales = array_merge($params_actuales, $nuevos_params);

    // Limpiar parámetros vacíos
    $params_finales = array_filter($params_finales, function($value) {
        return $value !== '' && $value !== null;
    });

    return '?' . http_build_query($params_finales);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Piloto Avanzado - AeroLine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        }

        .pilot-header {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin: 30px 0;
        }

        .stats-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 25px;
            text-align: center;
            transition: transform 0.3s;
            margin-bottom: 20px;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .filters-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 20px;
        }

        .flight-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
            overflow: hidden;
            transition: transform 0.3s;
            border: 1px solid #e9ecef;
        }

        .flight-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .flight-card .card-header {
            padding: 20px 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .flight-card .card-body {
            padding: 25px;
        }

        .route-display {
            margin: 20px 0;
            padding: 15px 0;
        }

        .route-display h4 {
            margin-bottom: 15px;
        }

        .flight-info-grid {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }

        .flight-actions {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }

        .stats-row {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin: 15px 0;
        }

        .stat-item {
            text-align: center;
            padding: 10px;
        }

        .stat-item .h5 {
            margin-bottom: 5px;
            font-weight: bold;
        }

        .stat-item small {
            font-size: 0.85em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .pagination-controls {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .filter-badge {
            background: #e3f2fd;
            color: #1976d2;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85em;
            margin: 2px;
            display: inline-block;
        }

        .clear-filters {
            color: #dc3545;
            text-decoration: none;
            font-size: 0.9em;
        }

        .clear-filters:hover {
            color: #c82333;
            text-decoration: underline;
        }
    </style>
</head>
<body>

<!-- Barra de navegación -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">
            <i class="fas fa-plane"></i> AeroLine - Panel de Piloto
        </a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="../index.php">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
        </div>
    </div>
</nav>

<div class="container">
    <!-- Encabezado del piloto -->
    <div class="pilot-header">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="mb-1">
                    <i class="fas fa-user-tie"></i>
                    Capitán <?php echo htmlspecialchars($piloto['nombre']); ?>
                </h2>
                <p class="text-muted mb-0">
                    <i class="fas fa-id-badge"></i> ID: <?php echo $piloto['id']; ?>
                </p>
            </div>
            <div class="col-auto">
                <div class="text-end">
                    <h4 class="text-primary mb-0"><?php echo $estadisticas['vuelos_pendientes']; ?></h4>
                    <small class="text-muted">Vuelos pendientes</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row">
        <div class="col-md-3">
            <div class="stats-card">
                <div class="text-primary mb-2">
                    <i class="fas fa-plane-departure" style="font-size: 2em;"></i>
                </div>
                <div class="h3 text-primary mb-1"><?php echo $estadisticas['total_vuelos']; ?></div>
                <div class="text-muted">Total Vuelos</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="text-warning mb-2">
                    <i class="fas fa-clock" style="font-size: 2em;"></i>
                </div>
                <div class="h3 text-warning mb-1"><?php echo $estadisticas['vuelos_programados']; ?></div>
                <div class="text-muted">Programados</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="text-info mb-2">
                    <i class="fas fa-plane" style="font-size: 2em;"></i>
                </div>
                <div class="h3 text-info mb-1"><?php echo $estadisticas['vuelos_en_curso']; ?></div>
                <div class="text-muted">En Vuelo</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="text-success mb-2">
                    <i class="fas fa-check-circle" style="font-size: 2em;"></i>
                </div>
                <div class="h3 text-success mb-1"><?php echo $estadisticas['vuelos_completados']; ?></div>
                <div class="text-muted">Completados</div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="filters-card">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Estado del Vuelo</label>
                <select name="estado" class="form-select">
                    <option value="">Todos los estados</option>
                    <option value="programado" <?php echo ($filtro_estado == 'programado') ? 'selected' : ''; ?>>Programado</option>
                    <option value="en_vuelo" <?php echo ($filtro_estado == 'en_vuelo') ? 'selected' : ''; ?>>En Vuelo</option>
                    <option value="completado" <?php echo ($filtro_estado == 'completado') ? 'selected' : ''; ?>>Completado</option>
                    <option value="cancelado" <?php echo ($filtro_estado == 'cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Fecha Desde</label>
                <input type="date" name="fecha_desde" class="form-control" value="<?php echo htmlspecialchars($filtro_fecha_desde); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Fecha Hasta</label>
                <input type="date" name="fecha_hasta" class="form-control" value="<?php echo htmlspecialchars($filtro_fecha_hasta); ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <div class="w-100">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <a href="?" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                </div>
            </div>
        </form>

        <!-- Filtros activos -->
        <?php if (!empty($filtro_estado) || !empty($filtro_fecha_desde) || !empty($filtro_fecha_hasta)): ?>
            <div class="mt-3 pt-3 border-top">
                <small class="text-muted">Filtros activos:</small>
                <?php if (!empty($filtro_estado)): ?>
                    <span class="filter-badge">Estado: <?php echo ucfirst($filtro_estado); ?></span>
                <?php endif; ?>
                <?php if (!empty($filtro_fecha_desde)): ?>
                    <span class="filter-badge">Desde: <?php echo date('d/m/Y', strtotime($filtro_fecha_desde)); ?></span>
                <?php endif; ?>
                <?php if (!empty($filtro_fecha_hasta)): ?>
                    <span class="filter-badge">Hasta: <?php echo date('d/m/Y', strtotime($filtro_fecha_hasta)); ?></span>
                <?php endif; ?>
                <a href="?" class="clear-filters ms-2">
                    <i class="fas fa-times"></i> Limpiar todos los filtros
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Controles de paginación superiores -->
    <div class="pagination-controls">
        <div class="row align-items-center">
            <div class="col-md-4">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-alt"></i> Mis Vuelos
                </h5>
                <small class="text-muted">
                    <?php echo $total_vuelos; ?> vuelos encontrados
                </small>
            </div>
            <div class="col-md-4 text-center">
                <?php echo PaginacionConfig::generarSelectorElementosPorPagina('vuelos', $vuelos_por_pagina); ?>
            </div>
            <div class="col-md-4 text-end">
                <?php if ($total_paginas > 1): ?>
                    <small class="text-muted">
                        Página <?php echo $pagina_actual; ?> de <?php echo $total_paginas; ?>
                    </small>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Lista de vuelos -->
    <?php if (empty($vuelos)): ?>
        <div class="text-center py-5">
            <div class="bg-white rounded-3 p-5 shadow">
                <i class="fas fa-plane text-muted" style="font-size: 4em; margin-bottom: 20px;"></i>
                <h4 class="text-muted">No se encontraron vuelos</h4>
                <p class="text-muted">
                    <?php if (!empty($filtro_estado) || !empty($filtro_fecha_desde) || !empty($filtro_fecha_hasta)): ?>
                        Intente ajustar los filtros de búsqueda.
                    <?php else: ?>
                        No tiene vuelos asignados próximamente.
                    <?php endif; ?>
                </p>
                <?php if (!empty($filtro_estado) || !empty($filtro_fecha_desde) || !empty($filtro_fecha_hasta)): ?>
                    <a href="?" class="btn btn-primary">Ver todos los vuelos</a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($vuelos as $index => $vuelo): ?>
            <div class="flight-card">
                <div class="card-header bg-primary text-white">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-1">
                                <i class="fas fa-plane me-2"></i>
                                Vuelo <?php echo htmlspecialchars($vuelo['codigo']); ?>
                            </h5>
                            <small class="opacity-75">
                                #<?php echo ($offset + $index + 1); ?> de <?php echo $total_vuelos; ?>
                            </small>
                        </div>
                        <div class="col-auto">
                    <span class="badge bg-light text-dark px-3 py-2">
                        <?php echo ucfirst($vuelo['estado']); ?>
                    </span>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Ruta del vuelo -->
                    <div class="route-display">
                        <h4 class="text-primary mb-3">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            <?php echo htmlspecialchars($vuelo['origen']); ?>
                            <i class="fas fa-arrow-right mx-3 text-muted"></i>
                            <?php echo htmlspecialchars($vuelo['destino']); ?>
                        </h4>

                        <div class="row">
                            <div class="col-md-6">
                                <p class="text-muted mb-2">
                                    <i class="fas fa-calendar me-2"></i>
                                    <?php echo date('d/m/Y', strtotime($vuelo['fecha_hora'])); ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted mb-2">
                                    <i class="fas fa-clock me-2"></i>
                                    <?php echo date('H:i', strtotime($vuelo['fecha_hora'])); ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Estadísticas del vuelo -->
                    <div class="stats-row">
                        <div class="row">
                            <div class="col-4">
                                <div class="stat-item">
                                    <div class="h5 text-success mb-1"><?php echo $vuelo['pasajeros_confirmados']; ?></div>
                                    <small class="text-muted">Pasajeros</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <div class="h5 text-info mb-1"><?php echo $vuelo['capacidad']; ?></div>
                                    <small class="text-muted">Capacidad</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <div class="h5 text-warning mb-1"><?php echo number_format($vuelo['precio'], 0); ?>€</div>
                                    <small class="text-muted">Precio</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Barra de ocupación -->
                    <div class="mb-3">
                        <?php
                        $ocupacion = $vuelo['capacidad'] > 0 ? ($vuelo['pasajeros_confirmados'] / $vuelo['capacidad']) * 100 : 0;
                        $color_ocupacion = $ocupacion < 50 ? 'success' : ($ocupacion < 80 ? 'warning' : 'danger');
                        ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted">Ocupación del vuelo</small>
                            <small class="text-muted"><?php echo round($ocupacion); ?>%</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-<?php echo $color_ocupacion; ?>"
                                 role="progressbar"
                                 style="width: <?php echo $ocupacion; ?>%">
                            </div>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="flight-actions text-end">
                        <button class="btn btn-outline-primary btn-sm me-2" onclick="verPasajeros(<?php echo $vuelo['id']; ?>)">
                            <i class="fas fa-users me-1"></i> Pasajeros
                        </button>
                        <button class="btn btn-outline-info btn-sm me-2" onclick="verDetallesVuelo(<?php echo $vuelo['id']; ?>)">
                            <i class="fas fa-info-circle me-1"></i> Detalles
                        </button>
                        <?php if (date('Y-m-d') == date('Y-m-d', strtotime($vuelo['fecha_hora'])) && $vuelo['estado'] == 'programado'): ?>
                            <button class="btn btn-success btn-sm" onclick="marcarEnVuelo(<?php echo $vuelo['id']; ?>)">
                                <i class="fas fa-play me-1"></i> Iniciar
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Paginación inferior -->
    <?php if ($total_paginas > 1): ?>
        <div class="pagination-controls">
            <nav aria-label="Navegación de vuelos">
                <ul class="pagination justify-content-center mb-0">
                    <!-- Botón Anterior -->
                    <?php if ($pagina_actual > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo construirUrl(['pagina' => $pagina_actual - 1]); ?>">
                                <i class="fas fa-chevron-left"></i> Anterior
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Números de página -->
                    <?php
                    $inicio = max(1, $pagina_actual - 2);
                    $fin = min($total_paginas, $pagina_actual + 2);

                    for ($i = $inicio; $i <= $fin; $i++): ?>
                        <li class="page-item <?php echo ($i == $pagina_actual) ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo construirUrl(['pagina' => $i]); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <!-- Botón Siguiente -->
                    <?php if ($pagina_actual < $total_paginas): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo construirUrl(['pagina' => $pagina_actual + 1]); ?>">
                                Siguiente <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<!-- Modales -->
<div class="modal fade" id="pasajerosModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lista de Pasajeros</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="pasajerosContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando información de pasajeros...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="detallesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles del Vuelo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detallesContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando detalles del vuelo...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function cambiarElementosPorPagina(cantidad, tipo) {
        const url = new URL(window.location);
        url.searchParams.set(tipo + '_por_pagina', cantidad);
        url.searchParams.set('pagina', 1);
        window.location.href = url.toString();
    }

    function verPasajeros(vueloId) {
        // Mostrar modal inmediatamente con spinner
        const modal = new bootstrap.Modal(document.getElementById('pasajerosModal'));
        modal.show();

        // Resetear contenido a spinner
        document.getElementById('pasajerosContent').innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2">Cargando información de pasajeros...</p>
        </div>
    `;

        // CAMBIO AQUÍ: Usar el nombre correcto del archivo
        fetch('../php/obtener_pasajeros.php?vuelo_id=' + vueloId)
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('La respuesta no es JSON válido');
                }

                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);

                if (data.success) {
                    document.getElementById('pasajerosContent').innerHTML = data.html;
                } else {
                    document.getElementById('pasajerosContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Error: ${data.message}
                    </div>
                `;
                }
            })
            .catch(error => {
                console.error('Error completo:', error);
                document.getElementById('pasajerosContent').innerHTML = `
                <div class="alert alert-danger">
                    <h6><i class="fas fa-exclamation-triangle"></i> Error de conexión</h6>
                    <p><strong>Detalles:</strong> ${error.message}</p>
                    <small class="text-muted">
                        Verifique que el archivo obtener_pasajeros.php exista en la carpeta php/
                    </small>
                </div>
            `;
            });
    }

    function verDetallesVuelo(vueloId) {
        // Mostrar modal inmediatamente con spinner
        const modal = new bootstrap.Modal(document.getElementById('detallesModal'));
        modal.show();

        // Resetear contenido a spinner
        document.getElementById('detallesContent').innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2">Cargando detalles del vuelo...</p>
        </div>
    `;

        // CAMBIO AQUÍ: Usar el nombre correcto del archivo
        fetch('../php/obtener_detalles_vuelo.php?vuelo_id=' + vueloId)
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('La respuesta no es JSON válido');
                }

                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);

                if (data.success) {
                    document.getElementById('detallesContent').innerHTML = data.html;
                } else {
                    document.getElementById('detallesContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Error: ${data.message}
                    </div>
                `;
                }
            })
            .catch(error => {
                console.error('Error completo:', error);
                document.getElementById('detallesContent').innerHTML = `
                <div class="alert alert-danger">
                    <h6><i class="fas fa-exclamation-triangle"></i> Error de conexión</h6>
                    <p><strong>Detalles:</strong> ${error.message}</p>
                    <small class="text-muted">
                        Verifique que el archivo obtener_detalles_vuelo.php exista en la carpeta php/
                    </small>
                </div>
            `;
            });
    }

    function marcarEnVuelo(vueloId) {
        if (confirm('¿Confirma que el vuelo está iniciando?')) {
            fetch('../php/iniciar_vuelo_corregido.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'vuelo_id=' + vueloId
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Vuelo marcado como iniciado');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al actualizar el estado del vuelo');
                });
        }
    }

    // Navegación con teclado
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey) {
            switch(e.key) {
                case 'ArrowLeft':
                    e.preventDefault();
                <?php if ($pagina_actual > 1): ?>
                    window.location.href = '<?php echo construirUrl(['pagina' => $pagina_actual - 1]); ?>';
                <?php endif; ?>
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                <?php if ($pagina_actual < $total_paginas): ?>
                    window.location.href = '<?php echo construirUrl(['pagina' => $pagina_actual + 1]); ?>';
                <?php endif; ?>
                    break;
            }
        }
    });
</script>

</body>
</html>
