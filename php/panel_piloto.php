<?php
session_start();

// Verificar si el administrador ha iniciado sesión
if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
    header('Location: ../index.php');
    exit;
}

require_once('conexion.php');

// Obtener estadísticas del sistema
try {
    // Total de vuelos
    $queryVuelos = "SELECT COUNT(*) as total FROM vuelos";
    $resultVuelos = mysqli_query($conexion, $queryVuelos);
    $totalVuelos = mysqli_fetch_assoc($resultVuelos)['total'];

    // Total de pilotos
    $queryPilotos = "SELECT COUNT(*) as total FROM pilotos";
    $resultPilotos = mysqli_query($conexion, $queryPilotos);
    $totalPilotos = mysqli_fetch_assoc($resultPilotos)['total'];

    // Total de clientes
    $queryClientes = "SELECT COUNT(*) as total FROM clientes";
    $resultClientes = mysqli_query($conexion, $queryClientes);
    $totalClientes = mysqli_fetch_assoc($resultClientes)['total'];

    // Total de compras (reservas)
    $queryCompras = "SELECT COUNT(*) as total FROM compras WHERE estado = 'confirmada'";
    $resultCompras = mysqli_query($conexion, $queryCompras);
    $totalCompras = mysqli_fetch_assoc($resultCompras)['total'];

    // Ingresos totales
    $queryIngresos = "SELECT SUM(precio_total) as total FROM compras WHERE estado = 'confirmada'";
    $resultIngresos = mysqli_query($conexion, $queryIngresos);
    $totalIngresos = mysqli_fetch_assoc($resultIngresos)['total'] ?? 0;

    // Vuelos por estado
    $queryEstados = "
        SELECT estado, COUNT(*) as cantidad 
        FROM vuelos 
        GROUP BY estado
    ";
    $resultEstados = mysqli_query($conexion, $queryEstados);
    $estadosVuelos = [];
    while ($row = mysqli_fetch_assoc($resultEstados)) {
        $estadosVuelos[$row['estado']] = $row['cantidad'];
    }

    // Compras recientes
    $queryComprasRecientes = "
        SELECT c.*, v.codigo, v.origen, v.destino, cl.nombre as cliente_nombre
        FROM compras c
        JOIN vuelos v ON c.vuelo_id = v.id
        JOIN clientes cl ON c.cliente_id = cl.id
        ORDER BY c.fecha_compra DESC
        LIMIT 10
    ";
    $resultComprasRecientes = mysqli_query($conexion, $queryComprasRecientes);
    $comprasRecientes = [];
    while ($row = mysqli_fetch_assoc($resultComprasRecientes)) {
        $comprasRecientes[] = $row;
    }

    // Vuelos próximos
    $queryVuelosProximos = "
        SELECT v.*, p.nombre as piloto_nombre,
               COUNT(c.id) as total_pasajeros
        FROM vuelos v
        LEFT JOIN pilotos p ON v.piloto_id = p.id
        LEFT JOIN compras c ON v.id = c.vuelo_id AND c.estado = 'confirmada'
        WHERE v.fecha_hora >= NOW()
        GROUP BY v.id
        ORDER BY v.fecha_hora ASC
        LIMIT 10
    ";
    $resultVuelosProximos = mysqli_query($conexion, $queryVuelosProximos);
    $vuelosProximos = [];
    while ($row = mysqli_fetch_assoc($resultVuelosProximos)) {
        $vuelosProximos[] = $row;
    }

} catch (Exception $e) {
    $error_message = "Error al obtener estadísticas: " . $e->getMessage();
}

mysqli_close($conexion);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - AeroLine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .admin-header {
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
            border-left: 5px solid;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .stats-card.vuelos { border-left-color: #007bff; }
        .stats-card.pilotos { border-left-color: #28a745; }
        .stats-card.clientes { border-left-color: #ffc107; }
        .stats-card.compras { border-left-color: #dc3545; }
        .stats-card.ingresos { border-left-color: #6f42c1; }

        .stats-icon {
            font-size: 3em;
            margin-bottom: 15px;
        }

        .stats-number {
            font-size: 2.5em;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .content-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 20px;
        }

        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }

        .admin-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(45deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2em;
            margin-right: 20px;
        }

        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
        }

        .status-programado { background: #d4edda; color: #155724; }
        .status-en_vuelo { background: #fff3cd; color: #856404; }
        .status-completado { background: #cce5ff; color: #004085; }
        .status-cancelado { background: #f8d7da; color: #721c24; }
        .status-confirmada { background: #d4edda; color: #155724; }
    </style>
</head>
<body>

<!-- Barra de navegación -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">
            <i class="fas fa-cogs"></i> AeroLine - Panel de Administración
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../Vistas/gestion_vuelos.php">
                        <i class="fas fa-plane"></i> Gestión de Vuelos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../Vistas/gestion_pilotos.php">
                        <i class="fas fa-users"></i> Gestión de Pilotos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../index.php">
                        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <!-- Encabezado del administrador -->
    <div class="admin-header">
        <div class="row align-items-center">
            <div class="col-auto">
                <div class="admin-avatar">
                    <i class="fas fa-user-shield"></i>
                </div>
            </div>
            <div class="col">
                <h2 class="mb-1">Panel de Administración</h2>
                <p class="text-muted mb-0">
                    <i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i'); ?> |
                    <i class="fas fa-user"></i> Administrador del Sistema
                </p>
            </div>
            <div class="col-auto">
                <div class="text-end">
                    <h4 class="text-primary mb-0"><?php echo $totalVuelos; ?></h4>
                    <small class="text-muted">Vuelos totales</small>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <!-- Estadísticas principales -->
    <div class="row">
        <div class="col-md-2-4">
            <div class="stats-card vuelos">
                <div class="stats-icon text-primary">
                    <i class="fas fa-plane"></i>
                </div>
                <div class="stats-number text-primary"><?php echo $totalVuelos; ?></div>
                <div class="text-muted">Total Vuelos</div>
            </div>
        </div>
        <div class="col-md-2-4">
            <div class="stats-card pilotos">
                <div class="stats-icon text-success">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="stats-number text-success"><?php echo $totalPilotos; ?></div>
                <div class="text-muted">Pilotos</div>
            </div>
        </div>
        <div class="col-md-2-4">
            <div class="stats-card clientes">
                <div class="stats-icon text-warning">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stats-number text-warning"><?php echo $totalClientes; ?></div>
                <div class="text-muted">Clientes</div>
            </div>
        </div>
        <div class="col-md-2-4">
            <div class="stats-card compras">
                <div class="stats-icon text-danger">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stats-number text-danger"><?php echo $totalCompras; ?></div>
                <div class="text-muted">Reservas</div>
            </div>
        </div>
        <div class="col-md-2-4">
            <div class="stats-card ingresos">
                <div class="stats-icon text-purple">
                    <i class="fas fa-euro-sign"></i>
                </div>
                <div class="stats-number text-purple"><?php echo number_format($totalIngresos, 2); ?>€</div>
                <div class="text-muted">Ingresos</div>
            </div>
        </div>
    </div>

    <!-- Gráficos y estadísticas -->
    <div class="row">
        <div class="col-md-6">
            <div class="content-card">
                <h5 class="mb-4">
                    <i class="fas fa-chart-pie"></i> Estado de Vuelos
                </h5>
                <canvas id="estadosChart" width="400" height="200"></canvas>
            </div>
        </div>
        <div class="col-md-6">
            <div class="content-card">
                <h5 class="mb-4">
                    <i class="fas fa-plane-departure"></i> Próximos Vuelos
                </h5>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                        <tr>
                            <th>Código</th>
                            <th>Ruta</th>
                            <th>Fecha</th>
                            <th>Pasajeros</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($vuelosProximos as $vuelo): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($vuelo['codigo']); ?></strong></td>
                                <td><?php echo htmlspecialchars($vuelo['origen'] . ' → ' . $vuelo['destino']); ?></td>
                                <td><?php echo date('d/m H:i', strtotime($vuelo['fecha_hora'])); ?></td>
                                <td>
                                        <span class="badge bg-info">
                                            <?php echo $vuelo['total_pasajeros']; ?>/<?php echo $vuelo['capacidad']; ?>
                                        </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Compras recientes -->
    <div class="content-card">
        <h5 class="mb-4">
            <i class="fas fa-shopping-cart"></i> Reservas Recientes
        </h5>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Vuelo</th>
                    <th>Ruta</th>
                    <th>Pasajeros</th>
                    <th>Total</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($comprasRecientes as $compra): ?>
                    <tr>
                        <td>#<?php echo str_pad($compra['id'], 4, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo htmlspecialchars($compra['cliente_nombre']); ?></td>
                        <td><strong><?php echo htmlspecialchars($compra['codigo']); ?></strong></td>
                        <td><?php echo htmlspecialchars($compra['origen'] . ' → ' . $compra['destino']); ?></td>
                        <td><?php echo $compra['num_pasajeros']; ?></td>
                        <td><?php echo number_format($compra['precio_total'], 2); ?>€</td>
                        <td><?php echo date('d/m/Y H:i', strtotime($compra['fecha_compra'])); ?></td>
                        <td>
                                <span class="status-badge status-<?php echo $compra['estado']; ?>">
                                    <?php echo ucfirst($compra['estado']); ?>
                                </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Acciones rápidas -->
    <div class="content-card">
        <h5 class="mb-4">
            <i class="fas fa-bolt"></i> Acciones Rápidas
        </h5>
        <div class="row">
            <div class="col-md-3">
                <a href="../Vistas/gestion_vuelos.php" class="btn btn-primary w-100 mb-2">
                    <i class="fas fa-plus"></i> Nuevo Vuelo
                </a>
            </div>
            <div class="col-md-3">
                <a href="../Vistas/gestion_pilotos.php" class="btn btn-success w-100 mb-2">
                    <i class="fas fa-user-plus"></i> Nuevo Piloto
                </a>
            </div>
            <div class="col-md-3">
                <button class="btn btn-info w-100 mb-2" onclick="generarReporte()">
                    <i class="fas fa-file-pdf"></i> Generar Reporte
                </button>
            </div>
            <div class="col-md-3">
                <button class="btn btn-warning w-100 mb-2" onclick="exportarDatos()">
                    <i class="fas fa-download"></i> Exportar Datos
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
    // Gráfico de estados de vuelos
    const ctx = document.getElementById('estadosChart').getContext('2d');
    const estadosChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: [
                <?php
                $labels = [];
                foreach ($estadosVuelos as $estado => $cantidad) {
                    $labels[] = "'" . ucfirst($estado) . "'";
                }
                echo implode(', ', $labels);
                ?>
            ],
            datasets: [{
                data: [
                    <?php
                    $datos = [];
                    foreach ($estadosVuelos as $estado => $cantidad) {
                        $datos[] = $cantidad;
                    }
                    echo implode(', ', $datos);
                    ?>
                ],
                backgroundColor: [
                    '#28a745',
                    '#ffc107',
                    '#007bff',
                    '#dc3545',
                    '#6c757d'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    function generarReporte() {
        alert('Funcionalidad de reportes próximamente');
    }

    function exportarDatos() {
        alert('Funcionalidad de exportación próximamente');
    }

    // Actualizar estadísticas cada 5 minutos
    setInterval(function() {
        location.reload();
    }, 300000);
</script>

<style>
    .col-md-2-4 {
        flex: 0 0 auto;
        width: 20%;
    }

    @media (max-width: 768px) {
        .col-md-2-4 {
            width: 50%;
            margin-bottom: 15px;
        }
    }

    @media (max-width: 576px) {
        .col-md-2-4 {
            width: 100%;
        }
    }

    .text-purple {
        color: #6f42c1 !important;
    }
</style>

</body>
</html>
