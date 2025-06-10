<?php
session_start();

// Verificar si el usuario ha iniciado sesión como cliente
if (!isset($_SESSION['idusuario']) || $_SESSION['tipo_usuario'] !== 'cliente') {
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
$vuelo_id = isset($_POST['vuelo_id']) ? (int)$_POST['vuelo_id'] : 0;
$num_pasajeros = isset($_POST['num_pasajeros']) ? (int)$_POST['num_pasajeros'] : 0;
$total = isset($_POST['total']) ? (float)$_POST['total'] : 0;
$cliente_id = $_SESSION['idusuario'];

// Validar datos
if ($vuelo_id <= 0 || $num_pasajeros <= 0 || $total <= 0) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

// Iniciar transacción
mysqli_begin_transaction($conexion);

try {
    // Verificar que el vuelo existe y está disponible
    $queryVuelo = "
        SELECT v.*, 
               (v.capacidad - COALESCE(SUM(c.num_pasajeros), 0)) as asientos_disponibles
        FROM vuelos v
        LEFT JOIN compras c ON v.id = c.vuelo_id AND c.estado = 'confirmada'
        WHERE v.id = ? AND v.estado = 'programado'
        GROUP BY v.id
    ";

    $stmtVuelo = mysqli_prepare($conexion, $queryVuelo);
    mysqli_stmt_bind_param($stmtVuelo, "i", $vuelo_id);
    mysqli_stmt_execute($stmtVuelo);
    $resultadoVuelo = mysqli_stmt_get_result($stmtVuelo);
    $vuelo = mysqli_fetch_assoc($resultadoVuelo);

    if (!$vuelo) {
        throw new Exception("El vuelo no existe o no está disponible");
    }

    if ($vuelo['asientos_disponibles'] < $num_pasajeros) {
        throw new Exception("No hay suficientes asientos disponibles");
    }

    // Verificar que el precio es correcto
    $precio_esperado = $vuelo['precio'] * $num_pasajeros;
    if (abs($total - $precio_esperado) > 0.01) {
        throw new Exception("Error en el cálculo del precio");
    }

    mysqli_stmt_close($stmtVuelo);

    // Insertar la compra
    $queryCompra = "
        INSERT INTO compras (cliente_id, vuelo_id, num_pasajeros, precio_total, fecha_compra, estado) 
        VALUES (?, ?, ?, ?, NOW(), 'confirmada')
    ";

    $stmtCompra = mysqli_prepare($conexion, $queryCompra);
    mysqli_stmt_bind_param($stmtCompra, "iiid", $cliente_id, $vuelo_id, $num_pasajeros, $total);

    if (!mysqli_stmt_execute($stmtCompra)) {
        throw new Exception("Error al registrar la compra");
    }

    $compra_id = mysqli_insert_id($conexion);
    mysqli_stmt_close($stmtCompra);

    // Obtener información del cliente
    $queryCliente = "SELECT * FROM clientes WHERE id = ?";
    $stmtCliente = mysqli_prepare($conexion, $queryCliente);
    mysqli_stmt_bind_param($stmtCliente, "i", $cliente_id);
    mysqli_stmt_execute($stmtCliente);
    $resultadoCliente = mysqli_stmt_get_result($stmtCliente);
    $cliente = mysqli_fetch_assoc($resultadoCliente);
    mysqli_stmt_close($stmtCliente);

    // Confirmar transacción
    mysqli_commit($conexion);

    // Log de la compra exitosa
    error_log("Compra exitosa - ID: $compra_id, Cliente: {$cliente['email']}, Vuelo: {$vuelo['codigo']}");

    // Generar PDF y enviar email
    try {
        require_once('generar_pdf.php');
        require_once('enviar_email.php');

        $pdf_path = generarPDFCompra($compra_id, $cliente, $vuelo, $num_pasajeros, $total);

        // Intentar enviar el email
        $email_enviado = enviarEmailConfirmacion($cliente['email'], $cliente['nombre'], $vuelo, $pdf_path);

        if ($email_enviado) {
            error_log("Email de confirmación enviado exitosamente a: {$cliente['email']}");
        } else {
            error_log("Error al enviar email de confirmación a: {$cliente['email']}");
        }

    } catch (Exception $e) {
        // Si hay error con PDF o email, no fallar la compra
        error_log("Error en PDF/Email: " . $e->getMessage());
    }

    echo json_encode([
        'success' => true,
        'message' => 'Compra realizada con éxito',
        'compra_id' => $compra_id,
        'email_enviado' => isset($email_enviado) ? $email_enviado : false
    ]);

} catch (Exception $e) {
    // Revertir transacción en caso de error
    mysqli_rollback($conexion);
    error_log("Error en compra: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

mysqli_close($conexion);
?>
