<?php
session_start();
require 'conexion.php';

require '../libs/PHPMailer/PHPMailer.php';
require '../libs/PHPMailer/SMTP.php';
require '../libs/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function esEmailValido($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function construirTablaHTML($datos, $campos)
{
    $tabla = '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;font-family:Arial,sans-serif;">';
    $tabla .= '<thead><tr>';
    foreach ($campos as $c) {
        $tabla .= "<th>{$c}</th>";
    }
    $tabla .= '</tr></thead><tbody>';

    foreach ($datos as $fila) {
        $tabla .= '<tr>';
        foreach ($campos as $c) {
            $tabla .= "<td>{$fila[$c]}</td>";
        }
        $tabla .= '</tr>';
    }

    $tabla .= '</tbody></table>';
    return $tabla;
}

// Configuración general
$alertas = [
    'stock' => [
        'titulo' => '📦 Stock General por Producto',
        'sp' => "CALL ObtStockXMes(NULL, NULL)",
        'campos' => ['artículo', 'mes_registro', 'estado_caducidad', 'cantidad_total']
    ],
    'vencidos' => [
        'titulo' => '🔴 Productos Vencidos',
        'sp' => "CALL ObtArtProxAVencer(0)",
        'campos' => ['descripcion', 'fecha_caducidad']
    ],
    'por_vencer' => [
        'titulo' => '🟠 Productos Próximos a Vencer',
        'sp' => "CALL ObtArtProxAVencer(60)",
        'campos' => ['descripcion', 'fecha_caducidad']
    ],
    'incumplimientos' => [
        'titulo' => '🚨 Participantes con Incumplimientos',
        'sp' => "CALL ObtCumpAportes()",
        'filtro' => fn($r) => $r['estado'] === 'No Cumple',
        'campos' => ['hermano', 'mes', 'articulo']
    ]
];

// Inicializar PHPMailer
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'mail.laflorida-icifd.com';
$mail->SMTPAuth = true;
$mail->Username = 'notificaciones@laflorida-icifd.com';
$mail->Password = 'Adm1n!st5';
$mail->SMTPSecure = 'ssl';
$mail->Port = 465;
$mail->CharSet = 'UTF-8';
$mail->setFrom('notificaciones@laflorida-icifd.com', 'Sistema Alfolí La Florida');

$alertasEnviadas = 0;
$errores = [];

foreach ($alertas as $tipo => $info) {
    if (!isset($_POST["correo_$tipo"]))
        continue;

    $correo = trim($_POST["correo_$tipo"]);
    if (!esEmailValido($correo)) {
        $errores[] = "Correo inválido para alerta de tipo $tipo.";
        continue;
    }

    try {
        // Ejecutar SP
        $stmt = $pdo->prepare($info['sp']);
        $stmt->execute();
        $datos = $stmt->fetchAll();
        $stmt->closeCursor();

        // Aplicar filtro si hay
        if (isset($info['filtro'])) {
            $datos = array_filter($datos, $info['filtro']);
        }

        if (empty($datos))
            continue;

        $tabla = construirTablaHTML($datos, $info['campos']);

        // Armar correo
        $mail->clearAddresses();
        $mail->addAddress($correo);
        $mail->isHTML(true);
        $mail->Subject = "📌 Alerta Automática: {$info['titulo']}";
        $mail->Body = "
        Estimado(a),<br><br>
        Se ha generado la siguiente alerta del sistema:<br><br>
        <b>{$info['titulo']}</b><br><br>
        $tabla
        <br><br>Saludos,<br>Jesús te bendiga.";

        $mail->send();
        $alertasEnviadas++;

    } catch (Exception $e) {
        $errores[] = "Error al enviar alerta '$tipo': " . $mail->ErrorInfo;
    }
}

// Resultado final
if ($alertasEnviadas > 0) {
    $_SESSION['mensaje'] = [
        'tipo' => 'success',
        'texto' => "✅ Se enviaron $alertasEnviadas alerta(s) correctamente."
    ];
} elseif (!empty($errores)) {
    $_SESSION['mensaje'] = [
        'tipo' => 'error',
        'texto' => implode('<br>', $errores)
    ];
} else {
    $_SESSION['mensaje'] = [
        'tipo' => 'info',
        'texto' => 'No se enviaron alertas (posiblemente sin datos).'
    ];
}

header("Location: ../alertas.php");
exit;
