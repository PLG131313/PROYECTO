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
    // Verificar que el vuelo pertenece al piloto (usando idPiloto)
    $queryVerificar = "SELECT * FROM vuelos WHERE id = ? AND idPiloto = ?";
    $stmtVerificar = mysqli_prepare($conexion, $queryVerificar);

    if (!$stmtVerificar) {
        throw new Exception("Error preparando consulta: " . mysqli_error($conexion));
    }

    mysqli_stmt_bind_param($stmtVerificar, "ii", $vuelo_id, $piloto_id);
    mysqli_stmt_execute($stmtVerificar);
    $resultadoVerificar = mysqli_stmt_get_result($stmtVerificar);

    if (mysqli_num_rows($resultadoVerificar) == 0) {
        mysqli_stmt_close($stmtVerificar);
        throw new Exception("No autorizado para ver este vuelo");
    }

    $vuelo = mysqli_fetch_assoc($resultadoVerificar);
    mysqli_stmt_close($stmtVerificar);

    // Obtener pasajeros del vuelo
    $queryPasajeros = "
        SELECT c.*, cl.nombre, cl.email, cl.telefono
        FROM compras c
        JOIN clientes cl ON c.cliente_id = cl.id
        WHERE c.vuelo_id = ? AND c.estado = 'confirmada'
        ORDER BY cl.nombre ASC
    ";

    $stmtPasajeros = mysqli_prepare($conexion, $queryPasajeros);

    if (!$stmtPasajeros) {
        throw new Exception("Error preparando consulta de pasajeros: " . mysqli_error($conexion));
    }

    mysqli_stmt_bind_param($stmtPasajeros, "i", $vuelo_id);
    mysqli_stmt_execute($stmtPasajeros);
    $resultadoPasajeros = mysqli_stmt_get_result($stmtPasajeros);
    $pasajeros = mysqli_fetch_all($resultadoPasajeros, MYSQLI_ASSOC);
    mysqli_stmt_close($stmtPasajeros);

    // Generar HTML
    $html = '
    <div class="mb-3">
        <h6>Vuelo: ' . htmlspecialchars($vuelo['codigo']) . '</h6>
        <p class="text-muted">' . htmlspecialchars($vuelo['origen']) . ' → ' . htmlspecialchars($vuelo['destino']) . '</p>
    </div>';

    if (empty($pasajeros)) {
        $html .= '<div class="alert alert-info">No hay pasajeros registrados para este vuelo.</div>';
    } else {
        $html .= '
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Pasajeros</th>
                        <th>Fecha Compra</th>
                    </tr>
                </thead>
                <tbody>';

        $totalPasajeros = 0;
        foreach ($pasajeros as $pasajero) {
            $totalPasajeros += $pasajero['num_pasajeros'];
            $html .= '
                <tr>
                    <td>' . htmlspecialchars($pasajero['nombre']) . '</td>
                    <td>' . htmlspecialchars($pasajero['email']) . '</td>
                    <td>' . htmlspecialchars($pasajero['telefono'] ?? 'No especificado') . '</td>
                    <td><span class="badge bg-primary">' . $pasajero['num_pasajeros'] . '</span></td>
                    <td>' . date('d/m/Y H:i', strtotime($pasajero['fecha_compra'])) . '</td>
                </tr>';
        }

        $html .= '
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            <strong>Total de pasajeros: ' . $totalPasajeros . ' / ' . $vuelo['capacidad'] . '</strong>
        </div>';
    }

    echo json_encode(['success' => true, 'html' => $html]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

mysqli_close($conexion);
?>
