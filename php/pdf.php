<?php
// libreria
require('../libreria/fpdf/fpdf.php');

require_once('../php/conexion.php');

mysqli_set_charset($conexion, "utf8");

// miro si hay id
if (!isset($_GET['id'])) {
    die("Error: ID de reserva no proporcionado"); // termino si no hay
}

// lo meto siendo numero entero
$id = intval($_GET['id']);

// consigo los datos de la reserva
$sql = "SELECT 
    reservas.*, 
    clientes.nombre AS nombre_cliente,
    pilotos.nombre AS nombre_piloto
FROM reservas
JOIN clientes ON reservas.idCliente = clientes.id
JOIN pilotos ON reservas.idPiloto = pilotos.id
WHERE reservas.idReserva = $id";


$resultado = $conexion->query($sql);

// compruebo consulta
if (!$resultado || $resultado->num_rows === 0) {
    die("Error: Reserva no encontrada"); // acabo srcitp.
}

// alamaceno array
$reserva = $resultado->fetch_assoc();


class TicketPDF extends FPDF {

    // para errores
    private function convertirUTF8($texto) {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $texto);
    }

    // para los carecteres
    function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='') {
        parent::Cell($w, $h, $this->convertirUTF8($txt), $border, $ln, $align, $fill, $link);
    }

    // manejo el texto del pdf
    function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false) {
        parent::MultiCell($w, $h, $this->convertirUTF8($txt), $border, $align, $fill);
    }

    // encabezado
    function Header() {
        $this->SetFont('Arial', 'B', 20); // la fuente
        $this->Cell(0, 10, 'AEROLINE', 0, 1, 'C'); // nombre centrado

        $this->SetFont('Arial', 'B', 15); // fuente subtitulos
        $this->Cell(0, 10, 'TICKET DE RESERVA', 0, 1, 'C'); // titulo

        $this->Ln(10); // Agrega un espacio en blanco.
    }
}

$pdf = new TicketPDF();
$pdf->AddPage(); // pongo pagina
$pdf->SetFont('Arial', 'B', 12); // fuente


// parte de arriba
$pdf->SetFillColor(200, 220, 255); // color del fondo
$pdf->Cell(0, 8, 'DATOS DE LA RESERVA', 0, 1, 'L', true); // titulo
$pdf->SetFont('Arial', '', 11); //formato letra
$pdf->Ln(5); // espacio


$addLine = function($label, $value) use ($pdf) {
    $pdf->SetFont('Arial', 'B', 11); // en negrita
    $pdf->Cell(60, 6, $label . ':', 0); // stilo de la etiqueta

    $pdf->SetFont('Arial', '', 11); // formato del texto
    $pdf->Cell(0, 6, $value, 0, 1); // pongo vaalor
};

// datos d la reserva
$addLine('Número de Reserva', $reserva['idReserva']);
$addLine('Pasajero', $reserva['nombre_cliente']);
$addLine('Origen', $reserva['ciudad_inicial']);
$addLine('Destino', $reserva['ciudad_final']);
$addLine('Fecha del Vuelo', date('d/m/Y', strtotime($reserva['fecha_vuelo'])));
$addLine('Clase', $reserva['clase_vuelo']);
$addLine('Piloto', $reserva['nombre_piloto']);

// terminos y condiciones
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'TÉRMINOS Y CONDICIONES', 0, 1, 'L', true);
$pdf->SetFont('Arial', '', 10);
$pdf->Ln(5);

// meto el texto
$pdf->MultiCell(0, 5,
    "1. Presentarse 2 horas antes del vuelo para el check-in.\n" .
    "2. Documento de identidad obligatorio para abordar.\n" .
    "3. Equipaje permitido según clase de vuelo."
);

// parte final
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, '¡Gracias por volar con AeroLine!', 0, 1, 'C');

// genero pdf
$pdf->Output('D', 'reserva_' . $id . '.pdf');
?>
