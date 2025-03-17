<?php
require('../libreria/fpdf/fpdf.php');
require_once('../php/conexion.php');

mysqli_set_charset($conexion, "utf8");

// Verificar ID de reserva
if (!isset($_GET['id'])) {
    die("Error: ID de reserva no proporcionado");
}

// Obtener datos de la reserva
$id = intval($_GET['id']);
$sql = "SELECT 
            r.*, 
            c.nombre AS nombre_cliente,
            p.nombre AS nombre_piloto
        FROM reservas r
        JOIN clientes c ON r.idCliente = c.id
        JOIN pilotos p ON r.idPiloto = p.id
        WHERE r.idReserva = $id";

$resultado = $conexion->query($sql);
if (!$resultado || $resultado->num_rows === 0) {
    die("Error: Reserva no encontrada");
}

$reserva = $resultado->fetch_assoc();

// Clase PDF personalizada
class TicketPDF extends FPDF {
    private function convertirUTF8($texto) {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $texto);
    }

    function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='') {
        parent::Cell($w, $h, $this->convertirUTF8($txt), $border, $ln, $align, $fill, $link);
    }

    function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false) {
        parent::MultiCell($w, $h, $this->convertirUTF8($txt), $border, $align, $fill);
    }

    function Header() {
        $this->SetFont('Arial', 'B', 20);
        $this->Cell(0, 10, 'AEROLINE', 0, 1, 'C');
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, 'TICKET DE RESERVA', 0, 1, 'C');
        $this->Ln(10);
    }
}

// Crear PDF
$pdf = new TicketPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);

// Información de la Reserva
$pdf->SetFillColor(200, 220, 255);
$pdf->Cell(0, 8, 'DATOS DE LA RESERVA', 0, 1, 'L', true);
$pdf->SetFont('Arial', '', 11);
$pdf->Ln(5);

// Función helper para añadir líneas de información
$addLine = function($label, $value) use ($pdf) {
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(60, 6, $label . ':', 0);
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 6, $value, 0, 1);
};

// Datos principales
$addLine('Número de Reserva', $reserva['idReserva']);
$addLine('Pasajero', $reserva['nombre_cliente']);
$addLine('Origen', $reserva['ciudad_inicial']);
$addLine('Destino', $reserva['ciudad_final']);
$addLine('Fecha del Vuelo', date('d/m/Y', strtotime($reserva['fecha_vuelo'])));
$addLine('Clase', $reserva['clase_vuelo']);
$addLine('Piloto', $reserva['nombre_piloto']);

// Términos y condiciones
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'TÉRMINOS Y CONDICIONES', 0, 1, 'L', true);
$pdf->SetFont('Arial', '', 10);
$pdf->Ln(5);
$pdf->MultiCell(0, 5,
    "1. Presentarse 2 horas antes del vuelo para el check-in.\n" .
    "2. Documento de identidad obligatorio para abordar.\n" .
    "3. Equipaje permitido según clase de vuelo."
);

// Mensaje final
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, '¡Gracias por volar con AeroLine!', 0, 1, 'C');

// Generar PDF
$pdf->Output('D', 'reserva_' . $id . '.pdf');
?>

