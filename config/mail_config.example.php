<?php
/**
 * Plantilla de configuración de correo para PHPMailer.
 * 
 * INSTRUCCIONES:
 * 1. Copiar este archivo como 'mail_config.php' en este mismo directorio ('config/').
 * 2. Completar con las credenciales reales de tu servidor SMTP.
 * 3. NO subir 'mail_config.php' al repositorio (está protegido en .gitignore).
 */

define('SMTP_HOST', 'c0160232.ferozo.com'); // Servidor SMTP
define('SMTP_PORT', 465);                  // Puerto (465 SSL o 587 TLS)
define('SMTP_SECURE', PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS);

define('SMTP_USER', 'tu_correo@tudominio.com');
define('SMTP_PASS', 'tu_contraseña_aqui');

define('MAIL_DESTINO', 'tu_correo@tudominio.com');
define('MAIL_NOMBRE', 'EGP Comercio Exterior');
