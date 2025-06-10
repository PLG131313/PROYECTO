<?php
// Usar tu librería PHPMailer local
require_once('../libreria/correo/PHPMailer.php');
require_once('../libreria/correo/SMTP.php');
require_once('../libreria/correo/Exception.php');

// Cargar configuración
require_once('configuracion_email.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function enviarEmailConfirmacion($email_cliente, $nombre_cliente, $vuelo, $pdf_path) {
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP usando las constantes
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        // Configuración del email
        $mail->setFrom(SMTP_USERNAME, SMTP_FROM_NAME);
        $mail->addAddress($email_cliente, $nombre_cliente);
        $mail->addReplyTo(SMTP_USERNAME, SMTP_REPLY_NAME);

        // Adjuntar PDF si existe
        if (file_exists($pdf_path)) {
            $mail->addAttachment($pdf_path, 'billete_aeroline.pdf');
        }

        // Contenido del email
        $mail->isHTML(true);
        $mail->Subject = '✈️ Confirmación de vuelo - AeroLine';

        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    line-height: 1.6; 
                    color: #333; 
                    margin: 0; 
                    padding: 0; 
                }
                .container { 
                    max-width: 600px; 
                    margin: 0 auto; 
                    background: #ffffff;
                }
                .header { 
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                    color: white; 
                    padding: 30px 20px; 
                    text-align: center; 
                }
                .header h1 {
                    margin: 0;
                    font-size: 28px;
                }
                .content { 
                    padding: 30px 20px; 
                    background: #f9f9f9; 
                }
                .flight-info { 
                    background: white; 
                    padding: 20px; 
                    margin: 20px 0; 
                    border-radius: 8px;
                    border-left: 4px solid #667eea;
                }
                .route {
                    text-align: center;
                    margin: 15px 0;
                    font-size: 18px;
                    font-weight: bold;
                    color: #667eea;
                }
                .footer { 
                    text-align: center; 
                    padding: 20px; 
                    color: #666; 
                    font-size: 12px; 
                    background: #333;
                    color: white;
                }
                .important-list {
                    background: #fff3cd;
                    border: 1px solid #ffeaa7;
                    border-radius: 5px;
                    padding: 15px;
                    margin: 15px 0;
                }
                .important-list ul {
                    margin: 0;
                    padding-left: 20px;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>✈ AeroLine</h1>
                    <h2 style="margin: 10px 0 0 0;">¡Confirmación de vuelo!</h2>
                </div>
                
                <div class="content">
                    <p><strong>Estimado/a ' . htmlspecialchars($nombre_cliente) . ',</strong></p>
                    
                    <p>¡Gracias por elegir AeroLine! Su reserva ha sido <strong>confirmada exitosamente</strong>.</p>
                    
                    <div class="flight-info">
                        <h3 style="margin-top: 0; color: #333;">✈ Detalles de su vuelo:</h3>
                        
                        <div class="route">
                            ' . htmlspecialchars($vuelo['origen']) . ' → ' . htmlspecialchars($vuelo['destino']) . '
                        </div>
                        
                        <p><strong>Vuelo:</strong> ' . htmlspecialchars($vuelo['codigo']) . '</p>
                        <p><strong>Fecha:</strong> ' . date('d/m/Y', strtotime($vuelo['fecha_hora'])) . '</p>
                        <p><strong>Hora:</strong> ' . date('H:i', strtotime($vuelo['fecha_hora'])) . '</p>
                    </div>
                    
                    <div class="important-list">
                        <h4 style="margin-top: 0; color: #856404;">📋 Importante:</h4>
                        <ul>
                            <li>Su billete electrónico está adjunto a este email</li>
                            <li>Llegue al aeropuerto al menos 2 horas antes del vuelo</li>
                            <li>Presente su billete y documento de identidad en el check-in</li>
                            <li>El check-in online estará disponible 24 horas antes</li>
                        </ul>
                    </div>
                    
                    <p>Si tiene alguna pregunta, no dude en contactarnos a través de:</p>
                    <p>📧 <strong>Email:</strong> ' . SMTP_USERNAME . '<br>
                    📞 <strong>Teléfono:</strong> +34 900 123 456</p>
                    
                    <p style="text-align: center; margin-top: 30px;">
                        <strong>¡Que tenga un excelente viaje! ✈</strong>
                    </p>
                </div>
                
                <div class="footer">
                    <p><strong>AeroLine</strong> - Su compañía aérea de confianza</p>
                    <p>www.aeroline.com | ' . SMTP_USERNAME . ' | +34 900 123 456</p>
                    <p style="font-size: 10px; margin-top: 10px;">
                        Este email fue enviado automáticamente. Por favor, no responda a este mensaje.
                    </p>
                </div>
            </div>
        </body>
        </html>';

        // Versión texto plano
        $mail->AltBody = "Estimado/a $nombre_cliente,\n\n" .
            "Su reserva de vuelo ha sido confirmada.\n\n" .
            "Vuelo: {$vuelo['codigo']}\n" .
            "Ruta: {$vuelo['origen']} -> {$vuelo['destino']}\n" .
            "Fecha: " . date('d/m/Y H:i', strtotime($vuelo['fecha_hora'])) . "\n\n" .
            "Consulte el archivo adjunto para mas detalles.\n\n" .
            "Gracias por elegir AeroLine.";

        $mail->send();

        // Log de éxito
        error_log("Email enviado exitosamente a: $email_cliente");
        return true;

    } catch (Exception $e) {
        // Log detallado del error
        error_log("Error enviando email a $email_cliente: {$mail->ErrorInfo}");
        error_log("Exception: " . $e->getMessage());
        return false;
    }
}

// Función para probar el envío de emails
function probarEnvioEmail($email_destino = null) {
    if (!$email_destino) {
        $email_destino = SMTP_USERNAME; // Enviar a ti mismo como prueba
    }

    $vuelo_prueba = [
        'codigo' => 'AE001',
        'origen' => 'Madrid',
        'destino' => 'Barcelona',
        'fecha_hora' => date('Y-m-d H:i:s', strtotime('+1 day'))
    ];

    $resultado = enviarEmailConfirmacion(
        $email_destino,
        'Usuario de Prueba',
        $vuelo_prueba,
        '' // Sin PDF para la prueba
    );

    return $resultado;
}
?>
