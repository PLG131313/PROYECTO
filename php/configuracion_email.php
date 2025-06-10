<?php
// Archivo de configuración para el email
// Edita estos valores con tus credenciales reales

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'cuentapruebaplg1313@gmail.com'); // Cambia por tu email
define('SMTP_PASSWORD', 'jzur qtov wfrj bjvx');    // Cambia por tu password de aplicación
define('SMTP_FROM_EMAIL', 'noreply@aeroline.com');
define('SMTP_FROM_NAME', 'AeroLine');
define('SMTP_REPLY_EMAIL', 'soporte@aeroline.com');
define('SMTP_REPLY_NAME', 'Soporte AeroLine');

// Para usar en enviar_email.php:
// $mail->Host = SMTP_HOST;
// $mail->Port = SMTP_PORT;
// $mail->Username = SMTP_USERNAME;
// $mail->Password = SMTP_PASSWORD;
?>
