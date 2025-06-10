<?php
// Incluir la librería FPDF
require('../libreria/fpdf/fpdf.php');
require_once('../php/conexion.php');

mysqli_set_charset($conexion, "utf8");

// Verificar si hay ID de reserva
if (!isset($_GET['id'])) {
    die("Error: ID de reserva no proporcionado");
}

// Convertir a entero para seguridad
$id = intval($_GET['id']);

// Obtener los datos de la reserva con información completa
$sql = "SELECT 
    r.*, 
    c.nombre AS nombre_cliente,
    c.email AS email_cliente,
    c.telefono AS telefono_cliente,
    p.nombre AS nombre_piloto,
    p.licencia AS licencia_piloto,
    v.codigo AS codigo_vuelo,
    v.fecha_hora AS fecha_hora_vuelo,
    v.capacidad AS capacidad_vuelo
FROM reservas r
LEFT JOIN clientes c ON r.idCliente = c.id
LEFT JOIN pilotos p ON r.idPiloto = p.id
LEFT JOIN vuelos v ON (v.origen = r.ciudad_inicial AND v.destino = r.ciudad_final AND DATE(v.fecha_hora) = DATE(r.fecha_vuelo))
WHERE r.idReserva = ?
LIMIT 1";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

// Verificar si se encontró la reserva
if (!$resultado || mysqli_num_rows($resultado) === 0) {
    die("Error: Reserva no encontrada");
}

// Obtener los datos
$reserva = mysqli_fetch_assoc($resultado);
mysqli_stmt_close($stmt);
mysqli_close($conexion);

// Clase personalizada para el PDF mejorada
class TicketPDF extends FPDF {

    // Convertir texto UTF-8 para evitar errores de caracteres
    private function convertirUTF8($texto) {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $texto);
    }

    // Sobrescribir método Cell para manejar UTF-8
    function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='') {
        parent::Cell($w, $h, $this->convertirUTF8($txt), $border, $ln, $align, $fill, $link);
    }

    // Sobrescribir método MultiCell para manejar UTF-8
    function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false) {
        parent::MultiCell($w, $h, $this->convertirUTF8($txt), $border, $align, $fill);
    }

    // Encabezado del documento mejorado
    function Header() {
        // Fondo del encabezado
        $this->SetFillColor(13, 110, 253); // Azul AeroLine
        $this->Rect(0, 0, 210, 45, 'F');

        // Logo y título
        $this->SetTextColor(255, 255, 255); // Blanco
        $this->SetFont('Arial', 'B', 28);
        $this->SetY(10);
        $this->Cell(0, 12, 'AEROLINE', 0, 1, 'C');

        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 8, 'BILLETE ELECTRONICO', 0, 1, 'C');

        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 6, 'Tu aerolínea de confianza', 0, 1, 'C');

        // Reset color
        $this->SetTextColor(0, 0, 0);
        $this->Ln(15);
    }

    // Pie de página mejorado
    function Footer() {
        $this->SetY(-35);

        // Línea decorativa
        $this->SetDrawColor(13, 110, 253);
        $this->SetLineWidth(0.5);
        $this->Line(20, $this->GetY(), 190, $this->GetY());

        $this->Ln(5);
        $this->SetFont('Arial', 'I', 9);
        $this->SetTextColor(100, 100, 100);

        $this->Cell(0, 4, 'Este documento es su billete electronico. Conservelo hasta el final de su viaje.', 0, 1, 'C');
        $this->Cell(0, 4, 'Para cualquier consulta, contacte con AeroLine: info@aeroline.com | +34 900 123 456', 0, 1, 'C');
        $this->Cell(0, 4, 'Generado el ' . date('d/m/Y H:i:s') . ' | Pagina ' . $this->PageNo(), 0, 1, 'C');
    }

    // Función para crear secciones con estilo
    function CrearSeccion($titulo, $color_r = 240, $color_g = 248, $color_b = 255) {
        $this->SetFillColor($color_r, $color_g, $color_b);
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(13, 110, 253);
        $this->Cell(0, 8, $titulo, 0, 1, 'L', true);
        $this->SetTextColor(0, 0, 0);
        $this->Ln(3);
    }

    // Función para agregar líneas de información con estilo
    function AgregarInfo($label, $value, $bold = false) {
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(60, 6, $label . ':', 0, 0);

        $this->SetFont('Arial', $bold ? 'B' : '', 10);
        $this->Cell(0, 6, $value, 0, 1);
    }

    // Crear código de barras simple (simulado)
    function CodigoBarras($codigo, $x, $y, $w, $h) {
        $this->SetXY($x, $y);
        $this->SetFont('Arial', '', 8);

        // Simular código de barras con líneas
        for ($i = 0; $i < $w; $i += 2) {
            $this->SetDrawColor(0, 0, 0);
            $this->SetLineWidth(0.5);
            $this->Line($x + $i, $y, $x + $i, $y + $h);
        }

        // Texto del código
        $this->SetXY($x, $y + $h + 2);
        $this->Cell($w, 4, $codigo, 0, 0, 'C');
    }
}

// Crear el PDF
$pdf = new TicketPDF();
$pdf->AddPage();

// Información de la reserva
$pdf->CrearSeccion('INFORMACION DE LA RESERVA', 240, 248, 255);

$pdf->AgregarInfo('Numero de Reserva', '#' . $reserva['idReserva'], true);
$pdf->AgregarInfo('Estado', ucfirst($reserva['estado'] ?? 'Confirmada'));
$pdf->AgregarInfo('Fecha de Reserva', date('d/m/Y H:i', strtotime($reserva['fecha_reserva'])));

// Calcular precio si no está en la BD
$precio_mostrar = $reserva['total'] ?? 0;
if ($precio_mostrar == 0) {
    switch ($reserva['clase_vuelo']) {
        case 'Business':
            $precio_mostrar = 250;
            break;
        case 'Primera':
            $precio_mostrar = 400;
            break;
        default:
            $precio_mostrar = 100;
    }
}

$pdf->AgregarInfo('Precio Total', number_format($precio_mostrar, 2) . ' EUR', true);

$pdf->Ln(5);

// Información del pasajero
$pdf->CrearSeccion('INFORMACION DEL PASAJERO', 240, 255, 240);

$pdf->AgregarInfo('Nombre', $reserva['nombre_cliente'] ?? 'No disponible');
$pdf->AgregarInfo('Email', $reserva['email_cliente'] ?? 'No disponible');
if (!empty($reserva['telefono_cliente'])) {
    $pdf->AgregarInfo('Telefono', $reserva['telefono_cliente']);
}

$pdf->Ln(5);

// Información del vuelo
$pdf->CrearSeccion('DETALLES DEL VUELO', 255, 248, 240);

$pdf->AgregarInfo('Codigo de Vuelo', $reserva['codigo_vuelo'] ?? 'AE-' . $reserva['idReserva'], true);
$pdf->AgregarInfo('Origen', $reserva['ciudad_inicial']);
$pdf->AgregarInfo('Destino', $reserva['ciudad_final']);

// Formatear fecha y hora del vuelo
$fecha_vuelo = $reserva['fecha_hora_vuelo'] ?? $reserva['fecha_vuelo'];
$fecha_formateada = date('d/m/Y', strtotime($fecha_vuelo));
$hora_formateada = date('H:i', strtotime($fecha_vuelo));

$pdf->AgregarInfo('Fecha del Vuelo', $fecha_formateada, true);
$pdf->AgregarInfo('Hora de Salida', $hora_formateada, true);
$pdf->AgregarInfo('Clase', $reserva['clase_vuelo']);

$pdf->Ln(5);

// Información del piloto y aeronave
if ($reserva['nombre_piloto']) {
    $pdf->CrearSeccion('TRIPULACION Y AERONAVE', 248, 255, 248);

    $pdf->AgregarInfo('Piloto', $reserva['nombre_piloto']);
    if ($reserva['licencia_piloto']) {
        $pdf->AgregarInfo('Licencia', $reserva['licencia_piloto']);
    }
    if ($reserva['capacidad_vuelo']) {
        $pdf->AgregarInfo('Capacidad', $reserva['capacidad_vuelo'] . ' pasajeros');
    }

    $pdf->Ln(5);
}

// Observaciones si las hay
if (!empty($reserva['observaciones'])) {
    $pdf->CrearSeccion('OBSERVACIONES', 255, 255, 240);
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(0, 5, $reserva['observaciones']);
    $pdf->Ln(5);
}

// Código de barras simulado
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 6, 'CODIGO DE RESERVA:', 0, 1, 'C');
$pdf->CodigoBarras('AE' . str_pad($reserva['idReserva'], 8, '0', STR_PAD_LEFT), 60, $pdf->GetY(), 90, 15);

$pdf->Ln(25);

// Términos y condiciones
$pdf->CrearSeccion('TERMINOS Y CONDICIONES', 248, 248, 255);
$pdf->SetFont('Arial', '', 9);

$terminos = [
    "• Presentarse en el aeropuerto 2 horas antes del vuelo para el check-in.",
    "• Documento de identidad vigente obligatorio para abordar.",
    "• Equipaje permitido segun la clase de vuelo contratada.",
    "• Los cambios y cancelaciones estan sujetos a las politicas de la aerolinea.",
    "• AeroLine no se hace responsable por retrasos debido a condiciones meteorologicas.",
    "• Este billete es personal e intransferible.",
    "• Conserve este documento hasta el final de su viaje.",
    "• Para asistencia: info@aeroline.com | +34 900 123 456"
];

foreach ($terminos as $termino) {
    $pdf->Cell(0, 4, $termino, 0, 1);
}

// Mensaje final
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetTextColor(13, 110, 253);
$pdf->Cell(0, 8, 'Gracias por volar con AeroLine!', 0, 1, 'C');

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', 'I', 11);
$pdf->Cell(0, 6, 'Que tenga un excelente viaje', 0, 1, 'C');

// Generar y descargar el PDF
$nombre_archivo = 'billete_aeroline_' . $reserva['idReserva'] . '_' . date('Ymd') . '.pdf';
$pdf->Output('D', $nombre_archivo);
?>
