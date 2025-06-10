<?php
// Usar tu librería FPDF local
require_once('../libreria/fpdf/fpdf.php');

function generarPDFCompra($compra_id, $cliente, $vuelo, $num_pasajeros, $total) {
    // Crear nuevo PDF con FPDF
    $pdf = new FPDF();
    $pdf->AddPage();

    // Configurar fuente
    $pdf->SetFont('Arial', 'B', 20);

    // Encabezado con símbolo de avión ASCII
    $pdf->SetTextColor(102, 126, 234); // Color azul
    $pdf->Cell(0, 15, 'AeroLine', 0, 1, 'C');

    $pdf->SetFont('Arial', '', 12);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(0, 8, 'Su compania aerea de confianza', 0, 1, 'C');

    // Línea separadora
    $pdf->Ln(5);
    $pdf->SetDrawColor(102, 126, 234);
    $pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
    $pdf->Ln(10);

    // Título del billete
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(100, 10, 'BILLETE ELECTRONICO', 0, 0, 'L');
    $pdf->Cell(90, 10, 'Confirmacion: #' . str_pad($compra_id, 6, '0', STR_PAD_LEFT), 0, 1, 'R');

    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(100, 8, '', 0, 0, 'L');
    $pdf->Cell(90, 8, 'Fecha: ' . date('d/m/Y H:i'), 0, 1, 'R');

    $pdf->Ln(10);

    // Información del pasajero
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetTextColor(51, 51, 51);
    $pdf->Cell(0, 10, 'INFORMACION DEL PASAJERO', 0, 1, 'L');

    // Crear tabla de información del pasajero
    $pdf->SetFont('Arial', '', 11);
    $pdf->SetFillColor(248, 249, 250);

    $pdf->Cell(50, 8, 'Nombre:', 1, 0, 'L', true);
    $pdf->Cell(140, 8, $cliente['nombre'], 1, 1, 'L');

    $pdf->Cell(50, 8, 'Email:', 1, 0, 'L');
    $pdf->Cell(140, 8, $cliente['email'], 1, 1, 'L');

    $pdf->Cell(50, 8, 'Telefono:', 1, 0, 'L', true);
    $pdf->Cell(140, 8, isset($cliente['telefono']) ? $cliente['telefono'] : 'No proporcionado', 1, 1, 'L');

    $pdf->Ln(10);

    // Detalles del vuelo
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'DETALLES DEL VUELO', 0, 1, 'L');

    // Encabezado del vuelo
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(102, 126, 234);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 12, 'VUELO ' . $vuelo['codigo'], 1, 1, 'C', true);

    // Información de ruta
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetTextColor(102, 126, 234);
    $pdf->SetFillColor(248, 249, 250);

    $pdf->Cell(95, 15, $vuelo['origen'], 1, 0, 'C', true);
    $pdf->Cell(95, 15, $vuelo['destino'], 1, 1, 'C', true);

    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(95, 8, 'ORIGEN', 1, 0, 'C');
    $pdf->Cell(95, 8, 'DESTINO', 1, 1, 'C');

    // Fecha y hora
    $pdf->Cell(95, 10, 'Fecha: ' . date('d/m/Y', strtotime($vuelo['fecha_hora'])), 1, 0, 'C');
    $pdf->Cell(95, 10, 'Hora: ' . date('H:i', strtotime($vuelo['fecha_hora'])), 1, 1, 'C');

    $pdf->Ln(10);

    // Resumen de la compra
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'RESUMEN DE LA COMPRA', 0, 1, 'L');

    $pdf->SetFont('Arial', '', 11);
    $pdf->SetFillColor(248, 249, 250);

    $pdf->Cell(130, 8, 'Numero de pasajeros:', 1, 0, 'L', true);
    $pdf->Cell(60, 8, $num_pasajeros, 1, 1, 'R');

    $pdf->Cell(130, 8, 'Precio por pasajero:', 1, 0, 'L');
    $pdf->Cell(60, 8, number_format($vuelo['precio'], 2) . ' EUR', 1, 1, 'R');

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(102, 126, 234);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(130, 10, 'TOTAL PAGADO:', 1, 0, 'L', true);
    $pdf->Cell(60, 10, number_format($total, 2) . ' EUR', 1, 1, 'R', true);

    $pdf->Ln(10);

    // Instrucciones importantes
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 8, 'INSTRUCCIONES IMPORTANTES', 0, 1, 'L');

    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor(100, 100, 100);

    $instrucciones = [
        '* Llegue al aeropuerto al menos 2 horas antes del vuelo',
        '* Presente este billete electronico y un documento de identidad valido',
        '* El check-in online esta disponible 24 horas antes del vuelo',
        '* Para cambios o cancelaciones, contacte con nuestro servicio al cliente'
    ];

    foreach ($instrucciones as $instruccion) {
        $pdf->Cell(0, 6, $instruccion, 0, 1, 'L');
    }

    $pdf->Ln(10);

    // Footer
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetTextColor(150, 150, 150);
    $pdf->Cell(0, 5, 'Este es un billete electronico valido. No es necesario imprimirlo.', 0, 1, 'C');
    $pdf->Cell(0, 5, 'AeroLine - Volando hacia el futuro | www.aeroline.com | Tel: +34 900 123 456', 0, 1, 'C');

    // Crear directorio si no existe
    $upload_dir = '../uploads/tickets/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Guardar PDF
    $filename = 'ticket_' . $compra_id . '_' . date('YmdHis') . '.pdf';
    $filepath = $upload_dir . $filename;
    $pdf->Output('F', $filepath);

    return $filepath;
}
?>
