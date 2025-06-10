<?php
session_start();

// Verificar si el usuario ha iniciado sesión como cliente
if (!isset($_SESSION['idusuario']) || $_SESSION['tipo_usuario'] !== 'cliente') {
    header('Location: ../index.php');
    exit;
}

require_once('conexion.php');

$compra_id = isset($_GET['compra_id']) ? (int)$_GET['compra_id'] : 0;
$cliente_id = $_SESSION['idusuario'];

if ($compra_id <= 0) {
    die('ID de compra inválido');
}

// Verificar que la compra pertenece al cliente
$query = "
    SELECT c.*, v.*, cl.nombre, cl.email, cl.telefono
    FROM compras c
    JOIN vuelos v ON c.vuelo_id = v.id
    JOIN clientes cl ON c.cliente_id = cl.id
    WHERE c.id = ? AND c.cliente_id = ?
";

$stmt = mysqli_prepare($conexion, $query);
mysqli_stmt_bind_param($stmt, "ii", $compra_id, $cliente_id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$datos = mysqli_fetch_assoc($resultado);

if (!$datos) {
    die('Compra no encontrada');
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);

// Generar PDF
require_once('generar_pdf.php');

$cliente = [
    'nombre' => $datos['nombre'],
    'email' => $datos['email'],
    'telefono' => $datos['telefono']
];

$vuelo = [
    'codigo' => $datos['codigo'],
    'origen' => $datos['origen'],
    'destino' => $datos['destino'],
    'fecha_hora' => $datos['fecha_hora'],
    'precio' => $datos['precio']
];

$pdf_path = generarPDFCompra($compra_id, $cliente, $vuelo, $datos['num_pasajeros'], $datos['precio_total']);

// Descargar PDF
if (file_exists($pdf_path)) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="billete_' . $compra_id . '.pdf"');
    header('Content-Length: ' . filesize($pdf_path));
    readfile($pdf_path);
} else {
    die('Error al generar el PDF');
}
?>
