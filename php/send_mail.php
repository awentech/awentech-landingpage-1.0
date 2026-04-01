<?php
/**
 * Awen Tech — Formulario de contacto
 * Requiere: PHPMailer (ver instrucciones en README_SETUP.txt)
 * Configurar: RECAPTCHA_SECRET_KEY, SMTP_USER, SMTP_PASS
 */

header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

// ── Configuración ─────────────────────────────────────────
define('RECAPTCHA_SECRET', '6Ldil6AsAAAAAN_gGy0tjCGco2F_zoWFWrXjl6Ji');   // <-- reemplazá
define('SMTP_HOST',   'smtp.gmail.com');
define('SMTP_USER',   'awentechargentina@gmail.com');
define('SMTP_PASS',   'TU_APP_PASSWORD_AQUI');       // <-- App Password de Google
define('SMTP_PORT',   587);
define('MAIL_TO',     'awentechargentina@gmail.com');
define('MAIL_FROM',   'awentechargentina@gmail.com');
define('MAIL_NAME',   'Awen Tech · Formulario Web');
// ─────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

// ── Rate limiting básico (por IP, via sesión) ─────────────
session_start();
$now = time();
if (isset($_SESSION['last_submit']) && ($now - $_SESSION['last_submit']) < 60) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Por favor esperá un momento antes de enviar otro mensaje.']);
    exit;
}

// ── Verificar reCAPTCHA ───────────────────────────────────
$recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
if (empty($recaptcha_response)) {
    echo json_encode(['success' => false, 'message' => 'Por favor completá el reCAPTCHA.']);
    exit;
}

$verify_url = 'https://www.google.com/recaptcha/api/siteverify';
$verify_data = http_build_query([
    'secret'   => RECAPTCHA_SECRET,
    'response' => $recaptcha_response,
    'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
]);
$ctx = stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => 'Content-Type: application/x-www-form-urlencoded',
        'content' => $verify_data,
        'timeout' => 10,
    ]
]);
$verify_result = @file_get_contents($verify_url, false, $ctx);
$verify_json   = $verify_result ? json_decode($verify_result, true) : null;

if (!$verify_json || !($verify_json['success'] ?? false)) {
    echo json_encode(['success' => false, 'message' => 'reCAPTCHA inválido. Intentá de nuevo.']);
    exit;
}

// ── Sanitizar inputs ──────────────────────────────────────
function sanitize(string $val): string {
    return htmlspecialchars(trim(strip_tags($val)), ENT_QUOTES, 'UTF-8');
}

$nombre   = sanitize($_POST['nombre']   ?? '');
$empresa  = sanitize($_POST['empresa']  ?? '');
$email    = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$telefono = sanitize($_POST['telefono'] ?? '');
$necesidad = sanitize($_POST['necesidad'] ?? '');
$mensaje  = sanitize($_POST['mensaje']  ?? '');

// ── Validar campos obligatorios ───────────────────────────
if (empty($nombre) || empty($email) || empty($necesidad) || empty($mensaje)) {
    echo json_encode(['success' => false, 'message' => 'Por favor completá todos los campos obligatorios.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'El email no es válido.']);
    exit;
}

if (strlen($mensaje) < 20) {
    echo json_encode(['success' => false, 'message' => 'El mensaje es demasiado corto.']);
    exit;
}

// ── Mapear valor de necesidad a etiqueta legible ──────────
$necesidades = [
    'desarrollo_medida'  => 'Desarrollo de software a medida',
    'sistema_existente'  => 'Adaptar uno de sus sistemas existentes',
    'automatizacion'     => 'Automatización de procesos',
    'agente_ia'          => 'Agente de Inteligencia Artificial',
    'consultoria'        => 'Consultoría técnica',
    'no_se'              => 'No lo tiene claro aún',
];
$necesidad_label = $necesidades[$necesidad] ?? $necesidad;

// ── Construir el email HTML ───────────────────────────────
$fecha  = date('d/m/Y H:i');
$subject = "Nuevo contacto web — {$nombre}" . ($empresa ? " ({$empresa})" : '');

$body_html = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<style>
  body { font-family: 'Segoe UI', Arial, sans-serif; background: #f8f9fc; margin: 0; padding: 0; }
  .wrap { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 12px;
          box-shadow: 0 4px 24px rgba(12,70,139,0.1); overflow: hidden; }
  .header { background: linear-gradient(135deg, #0c468b, #083470); padding: 32px 32px 24px; }
  .header h1 { color: #fff; margin: 0; font-size: 20px; font-weight: 700; }
  .header p  { color: rgba(255,255,255,0.7); margin: 6px 0 0; font-size: 13px; }
  .body  { padding: 28px 32px; }
  .field { margin-bottom: 18px; }
  .label { font-size: 11px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase;
           color: #4a6080; margin-bottom: 4px; display: block; }
  .value { font-size: 15px; color: #0d1a2e; line-height: 1.6; }
  .mensaje-box { background: #f0f4fa; border-left: 3px solid #0c468b; border-radius: 6px;
                 padding: 14px 16px; font-size: 14px; color: #0d1a2e; line-height: 1.7; }
  .footer { background: #f0f4fa; padding: 16px 32px; font-size: 12px; color: #8fa3be;
            border-top: 1px solid #e8eef6; }
  .divider { border: none; border-top: 1px solid #e8eef6; margin: 16px 0; }
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <h1>Nuevo mensaje de contacto</h1>
    <p>Recibido el {$fecha} desde el formulario web de Awen Tech</p>
  </div>
  <div class="body">
    <div class="field">
      <span class="label">Nombre</span>
      <span class="value">{$nombre}</span>
    </div>
    <hr class="divider"/>
    <div class="field">
      <span class="label">Empresa / Negocio</span>
      <span class="value">{$empresa}</span>
    </div>
    <hr class="divider"/>
    <div class="field">
      <span class="label">Email</span>
      <span class="value"><a href="mailto:{$email}" style="color:#0c468b">{$email}</a></span>
    </div>
    <hr class="divider"/>
    <div class="field">
      <span class="label">Teléfono / WhatsApp</span>
      <span class="value">{$telefono}</span>
    </div>
    <hr class="divider"/>
    <div class="field">
      <span class="label">¿Qué necesita?</span>
      <span class="value">{$necesidad_label}</span>
    </div>
    <hr class="divider"/>
    <div class="field">
      <span class="label">Mensaje</span>
      <div class="mensaje-box">{$mensaje}</div>
    </div>
  </div>
  <div class="footer">
    Este mensaje fue enviado desde el formulario de contacto de www.awentech.com.ar<br/>
    IP: {$_SERVER['REMOTE_ADDR']}
  </div>
</div>
</body>
</html>
HTML;

// ── Intentar enviar con PHPMailer ─────────────────────────
$phpmailer_path = __DIR__ . '/vendor/phpmailer/src/';
$use_phpmailer  = is_dir($phpmailer_path);

if ($use_phpmailer) {
    require $phpmailer_path . 'Exception.php';
    require $phpmailer_path . 'PHPMailer.php';
    require $phpmailer_path . 'SMTP.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(MAIL_FROM, MAIL_NAME);
        $mail->addAddress(MAIL_TO, 'Awen Tech');
        $mail->addReplyTo($email, $nombre);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body_html;
        $mail->AltBody = "Nuevo contacto: {$nombre} | {$email} | {$necesidad_label}\n\n{$mensaje}";

        $mail->send();
        $_SESSION['last_submit'] = $now;
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        error_log('PHPMailer error: ' . $mail->ErrorInfo);
        echo json_encode(['success' => false, 'message' => 'Error al enviar el mensaje. Por favor escribinos directamente a awentechargentina@gmail.com']);
    }

} else {
    // Fallback: mail() nativo de PHP (funciona en Hostinger sin config extra)
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . MAIL_NAME . " <" . MAIL_FROM . ">\r\n";
    $headers .= "Reply-To: {$nombre} <{$email}>\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

    $sent = mail(MAIL_TO, $subject, $body_html, $headers);
    if ($sent) {
        $_SESSION['last_submit'] = $now;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al enviar. Por favor escribinos directamente a awentechargentina@gmail.com']);
    }
}
