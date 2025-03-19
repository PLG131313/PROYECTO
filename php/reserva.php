<?php
session_start();
//Librerias y conexiones
require_once 'conexion.php';
require '../libreria/correo/PHPMailer.php';
require '../libreria/correo/SMTP.php';
require '../libreria/correo/Exception.php';
require_once 'conexion_mail.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['idusuario'])) {  // mira la sesion
echo json_encode(['exito' => false, 'mensaje' => 'No has iniciado sesión']);  //mensaje de no logueado
exit();
}

$id_cliente = $_SESSION['idusuario'];  // por sesion
$ciudad_origen = $_POST['origin'] ?? '';  // por post
$ciudad_destino = $_POST['destination'] ?? '';  // por post
$fecha_vuelo = $_POST['departureDate'] ?? '';  // por post
$clase_vuelo = $_POST['flightClass'] ?? '';  // por post

if (!$ciudad_origen || !$ciudad_destino || !$fecha_vuelo || !$clase_vuelo) {  // mira que esten todos
echo json_encode(['exito' => false, 'mensaje' => 'Todos los campos son obligatorios']);  // mensjae de todos obligatorios
exit();  // paro
}

$piloto = $conexion->query("SELECT id FROM pilotos ORDER BY RAND() LIMIT 1")->fetch_assoc();  // piloto ramdom
if (!$piloto) {
echo json_encode(['exito' => false, 'mensaje' => 'No hay pilotos disponibles']);  // si no hay pilotos error
exit();  // lo paro
}

// hago reserva
$query = "INSERT INTO reservas (fecha_reserva, fecha_vuelo, idCliente, idPiloto, clase_vuelo, ciudad_inicial, ciudad_final)
VALUES (NOW(), '$fecha_vuelo', $id_cliente, {$piloto['id']}, '$clase_vuelo', '$ciudad_origen', '$ciudad_destino')";
if ($conexion->query($query)) {
$id_reserva = $conexion->insert_id;  // id de la reserva
$url_pdf = "../php/pdf.php?id=" . $id_reserva;  // id de la reserva para el pdf

// datos para la reserva
$cliente = $conexion->query("SELECT nombre, email FROM clientes WHERE id = $id_cliente")->fetch_assoc();
if ($cliente) {
$nombre_cliente = $cliente['nombre'];  // nombre cliente
$email_cliente = $cliente['email'];  // correo cliente

// correo
$exito = enviarCorreoReserva($id_reserva, $email_cliente, $nombre_cliente, $ciudad_origen, $ciudad_destino, $fecha_vuelo, $clase_vuelo);


echo json_encode([
'exito' => true,
'mensaje' => $exito ? 'Reserva realizada y correo enviado' : 'Reserva realizada, pero el correo no se pudo enviar',
'url_pdf' => $url_pdf
]);
} else {  // Si no se encuentra
echo json_encode(['exito' => false, 'mensaje' => 'Error al obtener datos del cliente']);
}
} else {  // Si la reserva falla
echo json_encode(['exito' => false, 'mensaje' => 'Error al crear la reserva']);
}

$conexion->close();

function enviarCorreoReserva($id_reserva, $email_cliente, $nombre_cliente, $ciudad_origen, $ciudad_destino, $fecha_vuelo, $clase_vuelo) {
global $Username, $Password, $From, $FromName;  // datos para correo

$mail = new PHPMailer();
$mail->isSMTP();
$mail->SMTPDebug = 0;
$mail->SMTPAuth = true;
$mail->Host = "smtp.gmail.com";
$mail->SMTPSecure = 'ssl';
$mail->Port = 465;
$mail->Username = $Username;
$mail->Password = $Password;
$mail->From = $From;
$mail->FromName = $FromName;

global $AddAddress;
$mail->AddAddress($AddAddress);  // direccion de correo
$mail->IsHTML(true);  // es html
$mail->Subject = "Confirmacion de Reserva de Vuelo #" . $id_reserva;  // id de la reserva

// parte html
$mail->Body = "
<html>
<head>
    <style>
        .container {
         padding: 20px;
         border: 1px solid #003366;
         background-color: white;
         max-width: 600px;
         margin: 0 auto;
         font-family:
         Arial, sans-serif;
         }
        .header {
         text-align: center;
         padding: 10px;
         background-color: #003366; 
         color: white; 
         }
        .reservation-details {
         border: 1px solid #cccccc;
         padding: 15px;
         margin: 20px 0; 
         background-color: #f9f9f9;
         }
        .reservation-details h2 {
         color: #003366;
         margin-top: 0; 
         }
        .detail-row {
         margin: 10px 0;
         }
        .detail-label {font-weight: bold;
         color: #003366;
         }
        .footer { text-align: center;
         font-size: 12px;
         color: #666666;
         margin-top: 20px;
          }
    </style>
</head>
<body>
<div class='container'>
    <div class='header'>
        <h1>Confirmación de Reserva</h1>
    </div>

    <p>Estimado/a <strong>$nombre_cliente</strong>,</p>
    <p>Gracias por elegir nuestra aerolínea. Su reserva ha sido confirmada con éxito.</p>

    <div class='reservation-details'>
        <h2>Detalles de la Reserva #$id_reserva</h2>
        <div class='detail-row'><span class='detail-label'>Origen:</span> $ciudad_origen</div>
        <div class='detail-row'><span class='detail-label'>Destino:</span> $ciudad_destino</div>
        <div class='detail-row'><span class='detail-label'>Fecha del Vuelo:</span> $fecha_vuelo</div>
        <div class='detail-row'><span class='detail-label'>Clase:</span> $clase_vuelo</div>
    </div>

    <p>Si tiene alguna pregunta o necesita realizar cambios en su reserva, tenemos una parte en nuestra pagina para contactarnos</p>

    <div class='footer'>
        <p>Este es un correo automático, por favor no responda a este mensaje.</p>
        <p>&copy; " . date('Y') . " Su Aerolínea.</p>
    </div>
</div>
</body>
</html>";

return $mail->send();  // Envio correo.
}
?>
