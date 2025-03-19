<?php
session_start();

require_once('conexion.php');
require_once('pongoHTML.php');



// cojo id de la sesion
$pilotId = $_SESSION['idusuario'];

// consulta piloto
$consultaPiloto = "SELECT nombre FROM pilotos WHERE id = '$pilotId'";
$resultadoPiloto = mysqli_query($conexion, $consultaPiloto);
$piloto = mysqli_fetch_assoc($resultadoPiloto);
$nombrePiloto = $piloto['nombre'];  // nombre del piloto

// indico la cantidad de registros por pagina
$registrosPorPagina = 2;

// cojo numero pagina
$paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;

// pongo numero en la url
if ($paginaActual < 1) {
    $paginaActual = 1;  // Si la página es menor que 1, lo ajusta a 1
}


$offset = ($paginaActual - 1) * $registrosPorPagina;  // cuantos registro quitar por pagina

// consulta con datos a mostrar
$consulta = "SELECT pilotos.nombre AS nombre_piloto, 
       reservas.*, 
       clientes.nombre AS nombre_cliente
        FROM pilotos
        LEFT JOIN reservas ON pilotos.id = reservas.idPiloto
        LEFT JOIN clientes ON reservas.idCliente = clientes.id
        WHERE pilotos.id = '$pilotId'
        ORDER BY reservas.fecha_vuelo ASC
        LIMIT $registrosPorPagina OFFSET $offset";


$resultado = mysqli_query($conexion, $consulta);  // Realiza la consulta
$reservas = mysqli_fetch_all($resultado, MYSQLI_ASSOC);  // recupero en un array aociado

// todas las reservas que tiene
$consultaTotal = "SELECT COUNT(*) AS total FROM reservas WHERE idPiloto = '$pilotId'";
$resultadoTotal = mysqli_query($conexion, $consultaTotal);
$filaTotal = mysqli_fetch_assoc($resultadoTotal);
$totalReservas = $filaTotal['total'];  // cantidad de reservas

// total de paginas
$totalPaginas = ceil($totalReservas / $registrosPorPagina);

//aqui meto el HTML que tengo en otra clase
$htmlReservas = generarHtmlReservas($reservas);

//aqui meto el HTML que tengo en otra clase

$htmlPaginacion = generarHtmlPaginacion($paginaActual, $totalPaginas);

mysqli_close($conexion);

include('../Vistas/HTMLpiloto.php');
?>
