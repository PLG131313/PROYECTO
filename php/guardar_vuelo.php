<?php
session_start();

// Verificar si el usuario ha iniciado sesión como administrador
if (!isset($_SESSION['idusuario']) || $_SESSION['tipo_usuario'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Verificar si es una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

require_once('conexion.php');

// Obtener datos del formulario
$codigo = isset($_POST['codigo']) ? trim($_POST['codigo']) : '';
$idPiloto = isset($_POST['piloto']) ? (int)$_POST['piloto'] : 0;
$origen = isset($_POST['origen']) ? trim($_POST['origen']) : '';
$destino = isset($_POST['destino']) ? trim($_POST['destino']) : '';
$fecha = isset($_POST['fecha']) ? trim($_POST['fecha']) : '';
$hora = isset($_POST['hora']) ? trim($_POST['hora']) : '';
$capacidad = isset($_POST['capacidad']) ? (int)$_POST['capacidad'] : 0;
$precio = isset($_POST['precio']) ? (float)$_POST['precio'] : 0;
$observaciones = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : '';
$estado = 'programado'; // Estado por defecto para nuevos vuelos

// Log para depuración
error_log("Datos recibidos - Fecha: '$fecha', Hora: '$hora'");

// Validar datos
if (empty($codigo) || $idPiloto <= 0 || empty($origen) || empty($destino) || empty($fecha) || empty($hora) || $capacidad <= 0 || $precio <= 0) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos obligatorios deben ser completados']);
    exit;
}

// Validar que origen y destino no sean iguales
if ($origen === $destino) {
    echo json_encode(['success' => false, 'message' => 'El origen y destino no pueden ser iguales']);
    exit;
}

// Validar formato de fecha (YYYY-MM-DD)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    echo json_encode(['success' => false, 'message' => 'Formato de fecha inválido. Debe ser YYYY-MM-DD']);
    exit;
}

// Validar formato de hora (HH:MM)
if (!preg_match('/^\d{2}:\d{2}$/', $hora)) {
    echo json_encode(['success' => false, 'message' => 'Formato de hora inválido. Debe ser HH:MM']);
    exit;
}

// Extraer componentes de la fecha
list($anio, $mes, $dia) = explode('-', $fecha);
list($horas, $minutos) = explode(':', $hora);

// Validar que los componentes sean números válidos
if (!is_numeric($anio) || !is_numeric($mes) || !is_numeric($dia) ||
    !is_numeric($horas) || !is_numeric($minutos)) {
    echo json_encode(['success' => false, 'message' => 'Los componentes de fecha y hora deben ser numéricos']);
    exit;
}

// Validar rangos de fecha y hora
if ($mes < 1 || $mes > 12 || $dia < 1 || $dia > 31 ||
    $horas < 0 || $horas > 23 || $minutos < 0 || $minutos > 59) {
    echo json_encode(['success' => false, 'message' => 'Valores de fecha u hora fuera de rango']);
    exit;
}

// Construir la fecha y hora en formato MySQL
$fechaHora = sprintf('%04d-%02d-%02d %02d:%02d:00', $anio, $mes, $dia, $horas, $minutos);
error_log("Fecha y hora formateada: $fechaHora");

// Verificar si el código de vuelo ya existe
$checkCodigo = "SELECT id FROM vuelos WHERE codigo = ?";
$stmtCodigo = mysqli_prepare($conexion, $checkCodigo);
mysqli_stmt_bind_param($stmtCodigo, "s", $codigo);
mysqli_stmt_execute($stmtCodigo);
mysqli_stmt_store_result($stmtCodigo);

if (mysqli_stmt_num_rows($stmtCodigo) > 0) {
    echo json_encode(['success' => false, 'message' => 'El código de vuelo ya está registrado']);
    mysqli_stmt_close($stmtCodigo);
    mysqli_close($conexion);
    exit;
}
mysqli_stmt_close($stmtCodigo);

// Verificar si el piloto existe y no está de baja
$checkPiloto = "SELECT id, estado FROM pilotos WHERE id = ?";
$stmtPiloto = mysqli_prepare($conexion, $checkPiloto);
mysqli_stmt_bind_param($stmtPiloto, "i", $idPiloto);
mysqli_stmt_execute($stmtPiloto);
$resultadoPiloto = mysqli_stmt_get_result($stmtPiloto);
$piloto = mysqli_fetch_assoc($resultadoPiloto);

if (!$piloto) {
    echo json_encode(['success' => false, 'message' => 'El piloto seleccionado no existe']);
    mysqli_stmt_close($stmtPiloto);
    mysqli_close($conexion);
    exit;
}

if ($piloto['estado'] === 'baja') {
    echo json_encode(['success' => false, 'message' => 'El piloto seleccionado está de baja']);
    mysqli_stmt_close($stmtPiloto);
    mysqli_close($conexion);
    exit;
}
mysqli_stmt_close($stmtPiloto);

// Verificar si el piloto ya tiene un vuelo programado para esa fecha
$checkVueloPiloto = "SELECT id FROM vuelos WHERE idPiloto = ? AND DATE(fecha_hora) = ? AND estado != 'cancelado'";
$stmtVueloPiloto = mysqli_prepare($conexion, $checkVueloPiloto);
mysqli_stmt_bind_param($stmtVueloPiloto, "is", $idPiloto, $fecha);
mysqli_stmt_execute($stmtVueloPiloto);
mysqli_stmt_store_result($stmtVueloPiloto);

if (mysqli_stmt_num_rows($stmtVueloPiloto) > 0) {
    echo json_encode(['success' => false, 'message' => 'El piloto ya tiene un vuelo programado para esta fecha']);
    mysqli_stmt_close($stmtVueloPiloto);
    mysqli_close($conexion);
    exit;
}
mysqli_stmt_close($stmtVueloPiloto);

// Verificar si el piloto está en período de descanso
$checkDescanso = "SELECT id FROM vuelos 
                  WHERE idPiloto = ? 
                  AND DATE(fecha_hora) BETWEEN DATE_SUB(?, INTERVAL 2 DAY) AND DATE_SUB(?, INTERVAL 1 DAY) 
                  AND estado = 'completado'";
$stmtDescanso = mysqli_prepare($conexion, $checkDescanso);
mysqli_stmt_bind_param($stmtDescanso, "iss", $idPiloto, $fecha, $fecha);
mysqli_stmt_execute($stmtDescanso);
mysqli_stmt_store_result($stmtDescanso);

if (mysqli_stmt_num_rows($stmtDescanso) > 0) {
    echo json_encode(['success' => false, 'message' => 'El piloto está en período de descanso para esta fecha']);
    mysqli_stmt_close($stmtDescanso);
    mysqli_close($conexion);
    exit;
}
mysqli_stmt_close($stmtDescanso);

// Iniciar transacción
mysqli_begin_transaction($conexion);

try {
    // Insertar nuevo vuelo
    $query = "INSERT INTO vuelos (codigo, idPiloto, origen, destino, fecha_hora, capacidad, precio, observaciones, estado) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, "sisssdiss", $codigo, $idPiloto, $origen, $destino, $fechaHora, $capacidad, $precio, $observaciones, $estado);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error al guardar el vuelo: " . mysqli_error($conexion));
    }

    $vueloId = mysqli_insert_id($conexion);
    mysqli_stmt_close($stmt);

    // Confirmar transacción
    mysqli_commit($conexion);

    // Obtener información del piloto para la respuesta
    $queryPiloto = "SELECT nombre FROM pilotos WHERE id = ?";
    $stmtPilotoInfo = mysqli_prepare($conexion, $queryPiloto);
    mysqli_stmt_bind_param($stmtPilotoInfo, "i", $idPiloto);
    mysqli_stmt_execute($stmtPilotoInfo);
    $resultadoPilotoInfo = mysqli_stmt_get_result($stmtPilotoInfo);
    $pilotoInfo = mysqli_fetch_assoc($resultadoPilotoInfo);
    $nombrePiloto = $pilotoInfo['nombre'];
    mysqli_stmt_close($stmtPilotoInfo);

    // Preparar respuesta
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

    $origenNombre = isset($ciudades[$origen]) ? $ciudades[$origen] : $origen;
    $destinoNombre = isset($ciudades[$destino]) ? $ciudades[$destino] : $destino;

    echo json_encode([
        'success' => true,
        'message' => 'Vuelo creado correctamente',
        'vuelo' => [
            'id' => $vueloId,
            'codigo' => $codigo,
            'piloto' => $nombrePiloto,
            'origen' => $origenNombre,
            'destino' => $destinoNombre,
            'fecha' => date('d/m/Y', strtotime($fecha)),
            'hora' => $hora,
            'capacidad' => $capacidad,
            'precio' => $precio,
            'estado' => $estado
        ]
    ]);

} catch (Exception $e) {
    // Revertir transacción en caso de error
    mysqli_rollback($conexion);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

mysqli_close($conexion);
?>