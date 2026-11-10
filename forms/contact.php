<?php
/**
 *  * Requiere PHPMailer en ../phpMailer/
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../phpMailer/PHPMailer.php';
require '../phpMailer/Exception.php';
require '../phpMailer/SMTP.php';

$configFile = __DIR__ . '/../config/mail_config.php';
if (!file_exists($configFile)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error de configuración del servidor de correo.']);
    exit;
}
require $configFile;



header('Content-Type: application/json; charset=utf-8');

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'mensaje' => 'Método no permitido.']);
    exit;
}

// Honeypot
if (!empty($_POST['website'])) {
    echo json_encode(['ok' => true, 'mensaje' => 'Mensaje enviado correctamente.']);
    exit;
}

// ─── LEER Y VALIDAR ──────────────────────────────────────────────────────────
$nombre  = trim($_POST['nombre']  ?? '');
$email   = trim($_POST['email']   ?? '');
$empresa = trim($_POST['empresa'] ?? '');
$asunto  = trim($_POST['asunto']  ?? '');
$mensaje = trim($_POST['mensaje'] ?? '');

$errores = [];
if (empty($nombre) || strlen($nombre) < 2)       $errores[] = 'El nombre es obligatorio.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL))   $errores[] = 'El email no es válido.';
if (empty($asunto))                               $errores[] = 'El asunto es obligatorio.';
if (empty($mensaje) || strlen($mensaje) < 10)     $errores[] = 'El mensaje es muy corto.';

if (!empty($errores)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'errores' => $errores]);
    exit;
}

// ─── SANITIZAR ───────────────────────────────────────────────────────────────
$nombre  = htmlspecialchars($nombre,  ENT_QUOTES, 'UTF-8');
$email   = filter_var($email,         FILTER_SANITIZE_EMAIL);
$empresa = htmlspecialchars($empresa, ENT_QUOTES, 'UTF-8');
$asunto  = htmlspecialchars($asunto,  ENT_QUOTES, 'UTF-8');
$mensaje = nl2br(htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'));

// ─── CUERPO DEL EMAIL ────────────────────────────────────────────────────────
$empresaFila = $empresa ? "<tr><td><b>Empresa:</b></td><td>{$empresa}</td></tr>" : '';
$cuerpo = "
<html><body style='font-family:sans-serif;color:#333'>
  <h2 style='color:#0e4993'>Nueva consulta desde el sitio web</h2>
  <table cellpadding='8'>
    <tr><td><b>Nombre:</b></td><td>{$nombre}</td></tr>
    <tr><td><b>Email:</b></td><td><a href='mailto:{$email}'>{$email}</a></td></tr>
    {$empresaFila}
    <tr><td><b>Asunto:</b></td><td>{$asunto}</td></tr>
  </table>
  <hr>
  <p><b>Mensaje:</b></p>
  <p>{$mensaje}</p>
  <hr>
  <small>Enviado el " . date('d/m/Y \a \l\a\s H:i') . " hs.</small>
</body></html>
";

// ─── ENVIAR ──────────────────────────────────────────────────────────────────
$mail = new PHPMailer(true);
try {
    $mail->SMTPDebug  = SMTP::DEBUG_OFF;
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom(SMTP_USER, MAIL_NOMBRE);
    $mail->addReplyTo($email, $nombre);
    $mail->addAddress(MAIL_DESTINO);

    $mail->isHTML(true);
    $mail->Subject = "[EGP Contacto] {$asunto}";
    $mail->Body    = $cuerpo;

    $mail->send();
    echo json_encode(['ok' => true, 'mensaje' => '¡Mensaje enviado! Nos pondremos en contacto a la brevedad.']);

} catch (Exception $e) {
    error_log("Error al enviar email en contact.php: " . $mail->ErrorInfo);
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'No se pudo enviar el mensaje en este momento. Por favor, intentá nuevamente más tarde o contactanos por WhatsApp.']);
}