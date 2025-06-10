<?php
// Configurar encabezados antes de cualquier salida
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Iniciar sesión
session_start();

// Verificar si el piloto ha iniciado sesión
if (!isset($_SESSION['idusuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once('conexion.php');

$vuelo_id = isset($_GET['vuelo_id']) ? (int)$_GET['vuelo_id'] : 0;
$piloto_id = $_SESSION['idusuario'];

if ($vuelo_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de vuelo inválido']);
    exit;
}

try {
    // Obtener detalles completos del vuelo (usando idPiloto)
    $queryVuelo = "
        SELECT v.*, 
               COUNT(c.id) as reservas_totales,
               SUM(c.num_pasajeros) as pasajeros_confirmados,
               SUM(c.precio_total) as ingresos_totales
        FROM vuelos v
        LEFT JOIN compras c ON v.id = c.vuelo_id AND c.estado = 'confirmada'
        WHERE v.id = ? AND v.idPiloto = ?
        GROUP BY v.id
    ";

    $stmtVuelo = mysqli_prepare($conexion, $queryVuelo);

    if (!$stmtVuelo) {
        throw new Exception("Error preparando consulta: " . mysqli_error($conexion));
    }

    mysqli_stmt_bind_param($stmtVuelo, "ii", $vuelo_id, $piloto_id);
    mysqli_stmt_execute($stmtVuelo);
    $resultadoVuelo = mysqli_stmt_get_result($stmtVuelo);
    $vuelo = mysqli_fetch_assoc($resultadoVuelo);

    if (!$vuelo) {
        mysqli_stmt_close($stmtVuelo);
        throw new Exception("Vuelo no encontrado o no autorizado");
    }

    mysqli_stmt_close($stmtVuelo);

    // Calcular ocupación
    $ocupacion = $vuelo['capacidad'] > 0 ? ($vuelo['pasajeros_confirmados'] / $vuelo['capacidad']) * 100 : 0;

    // Generar HTML
    $html = '
    <div class="row">
        <div class="col-md-6">
            <h6 class="text-primary">Información del Vuelo</h6>
            <table class="table table-sm">
                <tr>
                    <td><strong>Código:</strong></td>
                    <td>' . htmlspecialchars($vuelo['codigo']) . '</td>
                </tr>
                <tr>
                    <td><strong>Origen:</strong></td>
                    <td>' . htmlspecialchars($vuelo['origen']) . '</td>
                </tr>
                <tr>
                    <td><strong>Destino:</strong></td>
                    <td>' . htmlspecialchars($vuelo['destino']) . '</td>
                </tr>
                <tr>
                    <td><strong>Fecha y Hora:</strong></td>
                    <td>' . date('d/m/Y H:i', strtotime($vuelo['fecha_hora'])) . '</td>
                </tr>
                <tr>
                    <td><strong>Estado:</strong></td>
                    <td><span class="badge bg-info">' . ucfirst($vuelo['estado']) . '</span></td>
                </tr>
                <tr>
                    <td><strong>Precio:</strong></td>
                    <td>' . number_format($vuelo['precio'], 2) . '€</td>
                </tr>
            </table>
        </div>
        
        <div class="col-md-6">
            <h6 class="text-success">Estadísticas</h6>
            <table class="table table-sm">
                <tr>
                    <td><strong>Capacidad Total:</strong></td>
                    <td>' . $vuelo['capacidad'] . ' asientos</td>
                </tr>
                <tr>
                    <td><strong>Pasajeros Confirmados:</strong></td>
                    <td>' . ($vuelo['pasajeros_confirmados'] ?? 0) . '</td>
                </tr>
                <tr>
                    <td><strong>Asientos Disponibles:</strong></td>
                    <td>' . ($vuelo['capacidad'] - ($vuelo['pasajeros_confirmados'] ?? 0)) . '</td>
                </tr>
                <tr>
                    <td><strong>Ocupación:</strong></td>
                    <td>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar" role="progressbar" style="width: ' . round($ocupacion) . '%">
                                ' . round($ocupacion) . '%
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><strong>Reservas Totales:</strong></td>
                    <td>' . ($vuelo['reservas_totales'] ?? 0) . '</td>
                </tr>
                <tr>
                    <td><strong>Ingresos Generados:</strong></td>
                    <td>' . number_format($vuelo['ingresos_totales'] ?? 0, 2) . '€</td>
                </tr>
            </table>
        </div>
    </div>';

    // Información adicional
    $tiempoRestante = strtotime($vuelo['fecha_hora']) - time();
    if ($tiempoRestante > 0) {
        $dias = floor($tiempoRestante / 86400);
        $horas = floor(($tiempoRestante % 86400) / 3600);
        $minutos = floor(($tiempoRestante % 3600) / 60);

        $html .= '
        <div class="alert alert-info mt-3">
            <h6><i class="fas fa-clock"></i> Tiempo restante para el vuelo:</h6>
            <p class="mb-0"><strong>' . $dias . '</strong> días, <strong>' . $horas . '</strong> horas y <strong>' . $minutos . '</strong> minutos</p>
        </div>';
    } else {
        $html .= '
        <div class="alert alert-warning mt-3">
            <h6><i class="fas fa-exclamation-triangle"></i> Este vuelo ya ha pasado</h6>
        </div>';
    }

    echo json_encode(['success' => true, 'html' => $html]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

mysqli_close($conexion);
?>
