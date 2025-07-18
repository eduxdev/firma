<?php
// Configuración del servidor SMTP de Gmail
$smtp_host = 'smtp.gmail.com';
$smtp_port = 587;
$smtp_user = 'dropsinfusionvital@gmail.com';
$smtp_pass = 'jjdjcgyrjkpkcbll';
$smtp_from = 'dropsinfusionvital@gmail.com';
$smtp_name = 'Drops Infusion Vital';

// Función para enviar correo de notificación
function enviarNotificacionNuevoFormulario($datos_formulario) {
    require_once 'PHPMailer/PHPMailer.php';
    require_once 'PHPMailer/SMTP.php';
    require_once 'PHPMailer/Exception.php';

    global $smtp_host, $smtp_port, $smtp_user, $smtp_pass, $smtp_from, $smtp_name;

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Configuración del servidor
        $mail->isSMTP();
        $mail->Host = $smtp_host;
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_user;
        $mail->Password = $smtp_pass;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $smtp_port;
        $mail->CharSet = 'UTF-8';

        // Remitente
        $mail->setFrom($smtp_from, $smtp_name);
        
        // Destinatario
        $mail->addAddress($smtp_user);

        // Contenido
        $mail->isHTML(true);
        $mail->Subject = 'Nuevo Formulario de Consentimiento Recibido';
        
        // Crear el cuerpo del correo con los datos del formulario
        $cuerpo = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { padding: 20px; }
                .header { background-color: #f8f9fa; padding: 20px; margin-bottom: 20px; }
                .info { margin-bottom: 15px; }
                .footer { margin-top: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Nuevo Formulario de Consentimiento</h2>
                    <p>Se ha recibido un nuevo formulario de consentimiento que requiere su revisión.</p>
                </div>
                
                <div class='info'>
                    <h3>Datos del Paciente:</h3>
                    <p><strong>Nombre:</strong> {$datos_formulario['nombre']} {$datos_formulario['apellido']}</p>
                    <p><strong>Fecha de Nacimiento:</strong> {$datos_formulario['fecha_nacimiento']}</p>
                    <p><strong>Correo:</strong> {$datos_formulario['correo']}</p>
                    <p><strong>Teléfono:</strong> {$datos_formulario['telefono_celular']}</p>
                </div>

                <div class='info'>
                    <p>Por favor, ingrese al sistema para revisar el formulario completo y tomar las acciones necesarias.</p>
                    <p><a href='http://localhost/firma/frontend/views/login.php'>Click aquí para ver los formularios pendientes</a></p>
                </div>

                <div class='footer'>
                    <p>Este es un correo automático, por favor no responda a este mensaje.</p>
                    <p>Drops Infusion Vital - Sistema de Gestión de Consentimientos</p>
                </div>
            </div>
        </body>
        </html>";

        $mail->Body = $cuerpo;
        $mail->AltBody = "Nuevo formulario de consentimiento recibido de {$datos_formulario['nombre']} {$datos_formulario['apellido']}. Por favor, revise el sistema para más detalles.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar correo: {$mail->ErrorInfo}");
        return false;
    }
}
?> 