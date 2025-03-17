<?php
session_start();
require_once('conexion.php');
require_once('pongoHTML.php');

if (!isset($_SESSION['idusuario'])) {
    header('Location: ../Vistas/login_piloto.php');
    exit;
}

// Obtener datos del piloto
$pilotId = $_SESSION['idusuario'];

// Obtener nombre del piloto
$consultaPiloto = "SELECT nombre FROM pilotos WHERE id = '$pilotId'";
$resultadoPiloto = mysqli_query($conexion, $consultaPiloto);
$piloto = mysqli_fetch_assoc($resultadoPiloto);
$nombrePiloto = $piloto['nombre'];

// Configuración de la paginación
$registrosPorPagina = 2;
$paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($paginaActual < 1) {
    $paginaActual = 1;
}
$offset = ($paginaActual - 1) * $registrosPorPagina;

// Obtener reservas
$consulta = "SELECT p.nombre, r.*, c.nombre AS nombre_cliente 
             FROM pilotos p
             LEFT JOIN reservas r ON p.id = r.idPiloto
             LEFT JOIN clientes c ON r.idCliente = c.id
             WHERE p.id = '$pilotId' 
             ORDER BY r.fecha_vuelo ASC
             LIMIT $registrosPorPagina OFFSET $offset";

$resultado = mysqli_query($conexion, $consulta);
$reservas = mysqli_fetch_all($resultado, MYSQLI_ASSOC);

// Obtener total de reservas para paginación
$consultaTotal = "SELECT COUNT(*) AS total FROM reservas WHERE idPiloto = '$pilotId'";
$resultadoTotal = mysqli_query($conexion, $consultaTotal);
$filaTotal = mysqli_fetch_assoc($resultadoTotal);
$totalReservas = $filaTotal['total'];
$totalPaginas = ceil($totalReservas / $registrosPorPagina);

// Generar HTML para las reservas y paginación
$htmlReservas = generarHtmlReservas($reservas);
$htmlPaginacion = generarHtmlPaginacion($paginaActual, $totalPaginas);

mysqli_close($conexion);

// Incluir la vista
include('../Vistas/HTMLpiloto.php');
?>