<?php
session_start();

// Verificar si el usuario ha iniciado sesión como cliente
if (!isset($_SESSION['idusuario']) || $_SESSION['tipo_usuario'] !== 'cliente') {
    header('Location: ../index.php');
    exit;
}

require_once('../php/conexion.php');

$cliente_id = $_SESSION['idusuario'];

// Obtener compras del cliente
$queryCompras = "
    SELECT c.*, v.codigo, v.origen, v.destino, v.fecha_hora, v.precio
    FROM compras c
    JOIN vuelos v ON c.vuelo_id = v.id
    WHERE c.cliente_id = ?
    ORDER BY c.fecha_compra DESC
";

$stmtCompras = mysqli_prepare($conexion, $queryCompras);
mysqli_stmt_bind_param($stmtCompras, "i", $cliente_id);
mysqli_stmt_execute($stmtCompras);
$resultadoCompras = mysqli_stmt_get_result($stmtCompras);
$compras = mysqli_fetch_all($resultadoCompras, MYSQLI_ASSOC);
mysqli_stmt_close($stmtCompras);

mysqli_close($conexion);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AeroLine - Mis Vuelos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .ticket-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            overflow: hidden;
            transition: transform 0.3s;
        }

        .ticket-card:hover {
            transform: translateY(-5px);
        }

        .ticket-header {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 15px 20px;
        }

        .ticket-body {
            padding: 20px;
        }

        .route-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 15px 0;
        }

        .city {
            font-size: 1.5em;
            font-weight: bold;
            color: #333;
        }

        .flight-arrow {
            color: #667eea;
            font-size: 1.5em;
        }

        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
        }

        .status-confirmada {
            background: #d4edda;
            color: #155724;
        }

        .status-cancelada {
            background: #f8d7da;
            color: #721c24;
        }

        .welcome-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin: 30px 0;
            text-align: center;
        }
    </style>
</head>
<body>

<!-- Barra de navegación -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">✈️ AeroLine</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="buscar-vuelos.php">
                        <i class="fas fa-search"></i> Buscar Vuelos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['nombre_cliente']); ?>
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
    <!-- Tarjeta de bienvenida -->
    <div class="welcome-card">
        <h2>
            <i class="fas fa-plane"></i>
            Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre_cliente']); ?>
        </h2>
        <p class="text-muted">Aquí puede ver y gestionar todos sus vuelos</p>
        <a href="buscar-vuelos.php" class="btn btn-primary btn-lg">
            <i class="fas fa-plus"></i> Buscar Nuevos Vuelos
        </a>
    </div>

    <!-- Lista de vuelos -->
    <div class="row">
        <div class="col-12">
            <h3 class="text-white mb-4">
                <i class="fas fa-ticket-alt"></i> Mis Vuelos
            </h3>

            <?php if (empty($compras)): ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i>
                    No tiene vuelos reservados.
                    <a href="buscar-vuelos.php" class="alert-link">¡Reserve su primer vuelo aquí!</a>
                </div>
            <?php else: ?>
                <?php foreach ($compras as $compra): ?>
                    <div class="ticket-card">
                        <div class="ticket-header">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5 class="mb-0">
                                        <i class="fas fa-plane"></i>
                                        Vuelo <?php echo htmlspecialchars($compra['codigo']); ?>
                                    </h5>
                                    <small>Confirmación: #<?php echo str_pad($compra['id'], 6, '0', STR_PAD_LEFT); ?></small>
                                </div>
                                <div class="col-md-4 text-end">
                                    <span class="status-badge status-<?php echo $compra['estado']; ?>">
                                        <?php echo ucfirst($compra['estado']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="ticket-body">
                            <div class="route-info">
                                <div class="city"><?php echo htmlspecialchars($compra['origen']); ?></div>
                                <div class="flight-arrow">
                                    <i class="fas fa-plane"></i>
                                </div>
                                <div class="city"><?php echo htmlspecialchars($compra['destino']); ?></div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Fecha:</strong><br>
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('d/m/Y', strtotime($compra['fecha_hora'])); ?>
                                </div>
                                <div class="col-md-3">
                                    <strong>Hora:</strong><br>
                                    <i class="fas fa-clock"></i>
                                    <?php echo date('H:i', strtotime($compra['fecha_hora'])); ?>
                                </div>
                                <div class="col-md-3">
                                    <strong>Pasajeros:</strong><br>
                                    <i class="fas fa-users"></i>
                                    <?php echo $compra['num_pasajeros']; ?>
                                </div>
                                <div class="col-md-3">
                                    <strong>Total:</strong><br>
                                    <i class="fas fa-euro-sign"></i>
                                    <?php echo number_format($compra['precio_total'], 2); ?>€
                                </div>
                            </div>

                            <div class="mt-3 text-end">
                                <?php if ($compra['estado'] === 'confirmada' && strtotime($compra['fecha_hora']) > time()): ?>
                                    <button class="btn btn-outline-danger btn-sm"
                                            onclick="cancelarVuelo(<?php echo $compra['id']; ?>)">
                                        <i class="fas fa-times"></i> Cancelar
                                    </button>
                                <?php endif; ?>

                                <button class="btn btn-outline-primary btn-sm"
                                        onclick="descargarPDF(<?php echo $compra['id']; ?>)">
                                    <i class="fas fa-download"></i> Descargar PDF
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal de confirmación de cancelación -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Cancelación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea cancelar este vuelo?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Atención:</strong> Esta acción no se puede deshacer.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, mantener</button>
                <button type="button" class="btn btn-danger" onclick="confirmarCancelacion()">
                    Sí, cancelar vuelo
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    let compraACancelar = null;

    function cancelarVuelo(compraId) {
        compraACancelar = compraId;
        const modal = new bootstrap.Modal(document.getElementById('cancelModal'));
        modal.show();
    }

    function confirmarCancelacion() {
        if (!compraACancelar) return;

        const formData = new FormData();
        formData.append('compra_id', compraACancelar);

        fetch('../php/cancelar_vuelo.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Vuelo cancelado exitosamente');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al cancelar el vuelo');
            })
            .finally(() => {
                bootstrap.Modal.getInstance(document.getElementById('cancelModal')).hide();
                compraACancelar = null;
            });
    }

    function descargarPDF(compraId) {
        window.open('../php/descargar_pdf.php?compra_id=' + compraId, '_blank');
    }
</script>

</body>
</html>
