<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/../conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/../correo_config.php';
require '../libs/PHPMailer/PHPMailer.php';
require '../libs/PHPMailer/SMTP.php';
require '../libs/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Obtener los indicadores pendientes
$stmt = $pdo->prepare("CALL ObtCumpAportes()");
$stmt->execute();
$indicadores = $stmt->fetchAll();
$stmt->closeCursor();

$pendientes = array_filter($indicadores, fn($i) => $i['estado'] === 'No Cumple');

if (empty($pendientes)) {
    $_SESSION['mensaje'] = [
        'tipo' => 'info',
        'texto' => 'No hay hermanos con notificaciones pendientes.'
    ];
    header("Location: ../notificaciones.php");
    exit;
}

// Obtener los usuarios encargados
$stmt = $pdo->prepare("CALL obtener_usuarios()");
$stmt->execute();
$usuarios = $stmt->fetchAll();
$stmt->closeCursor();

// Filtrar roles encargados (Administrador o Moderador, evitar Consultas)
$encargados = array_filter($usuarios, function ($u) {
    $rol = strtolower($u['rol']);
    return in_array($rol, ['administrador', 'moderador']) && filter_var($u['email'], FILTER_VALIDATE_EMAIL);
});

if (empty($encargados)) {
    $_SESSION['mensaje'] = [
        'tipo' => 'error',
        'texto' => 'No hay usuarios encargados con email válido.'
    ];
    header("Location: ../notificaciones.php");
    exit;
}

// Construir tabla HTML con hermanos pendientes
$tabla = '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;font-family:Arial,sans-serif;">
<thead><tr style="background:#f2f2f2;"><th>Hermano</th><th>Mes</th><th>Artículo</th></tr></thead><tbody>';

foreach ($pendientes as $p) {
    $tabla .= "<tr>
        <td>{$p['hermano']}</td>
        <td>{$p['mes']}</td>
        <td>{$p['articulo']}</td>
    </tr>";
}
$tabla .= '</tbody></table>';

// Configurar PHPMailer
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASS;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->Port = SMTP_PORT;
    $mail->CharSet = 'UTF-8';
    $mail->setFrom(SMTP_USER, SMTP_FROM_NAME);

    foreach ($encargados as $user) {
        $mail->addAddress($user['email'], $user['nombre_completo']);

        $body = "Estimado(a) <b>{$user['nombre_completo']}</b>,<br><br>
            Esperamos que te encuentres bien. Este es un mensaje informativo sobre el estado de los aportes mensuales de Alfolí. Actualmente, los siguientes hermanos tienen pendiente su registro:<br><br>
            $tabla
            <br><br>
            Tu colaboración es fundamental para el cumplimiento de nuestra labor. Si tienes alguna consulta, no dudes en contactarnos.<br><br>
            <br>
            Saludos,<br>
            <b>Jesús le bendiga.<br>
            <b>Iglesia Cristiana Int. Familia de Dios, La Florida.</b>
        ";

        $mail->isHTML(true);
        $mail->Subject = "📩 Notificación - Hermanos Pendientes de Alfolí";
        $mail->Body = $body;

        $mail->send();
        $mail->clearAddresses();
    }

    $_SESSION['mensaje'] = [
        'tipo' => 'success',
        'texto' => 'Correos enviados correctamente a los encargados.'
    ];
} catch (Exception $e) {
    $_SESSION['mensaje'] = [
        'tipo' => 'error',
        'texto' => 'No se pudo enviar el correo. Detalle: ' . $mail->ErrorInfo
    ];
}

header("Location: ../notificaciones.php");
exit;


