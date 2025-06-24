<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/../conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/../correo_config.php';
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
    $html = '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;font-family:Arial,sans-serif;"><thead><tr style="background:#f2f2f2;">';
    foreach ($campos as $c) {
        $html .= "<th>" . ucfirst(str_replace("_", " ", $c)) . "</th>";
    }
    $html .= '</tr></thead><tbody>';
    foreach ($datos as $fila) {
        $html .= '<tr>';
        foreach ($campos as $c) {
            $html .= '<td>' . htmlspecialchars($fila[$c]) . '</td>';
        }
        $html .= '</tr>';
    }
    return $html . '</tbody></table>';
}

$alertas_definidas = [
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

try {
    $ahora = new DateTime();

    $stmt = $pdo->prepare("SELECT * FROM alertas_programadas WHERE programada = TRUE");
    $stmt->execute();
    $alertas_configuradas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    foreach ($alertas_configuradas as $config) {
        $enviar = false;
        $ahora_hora = $ahora->format('H:i');

        switch ($config['frecuencia']) {
            case 'diaria':
                $enviar = $ahora_hora === substr($config['hora'], 0, 5);
                break;
            case 'semanal':
                $enviar = $ahora->format('l') === $config['dia_semana'] && $ahora_hora === substr($config['hora'], 0, 5);
                break;
            case 'mensual':
                $enviar = $ahora->format('j') == $config['dia_mes'] && $ahora_hora === substr($config['hora'], 0, 5);
                break;
        }

        if ($enviar && esEmailValido($config['correo'])) {
            $tipos_alerta = $config['programar_todas'] ? array_keys($alertas_definidas) : [$config['tipo_alerta']];

            foreach ($tipos_alerta as $tipo) {
                if (!isset($alertas_definidas[$tipo]))
                    continue;

                $alerta = $alertas_definidas[$tipo];
                $stmt = $pdo->prepare($alerta['sp']);
                $stmt->execute();
                $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $stmt->closeCursor();

                if (isset($alerta['filtro'])) {
                    $datos = array_filter($datos, $alerta['filtro']);
                }

                if (count($datos) > 0) {
                    $tabla = construirTablaHTML($datos, $alerta['campos']);

                    $mail = new PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host = SMTP_HOST;
                    $mail->Username = SMTP_USER;
                    $mail->Password = SMTP_PASS;
                    $mail->SMTPSecure = SMTP_SECURE;
                    $mail->Port = SMTP_PORT;
                    $mail->CharSet = 'UTF-8';
                    $mail->setFrom(SMTP_USER, SMTP_FROM_NAME);
                    $mail->addAddress($config['correo']);
                    $mail->isHTML(true);
                    $mail->Subject = $alerta['titulo'];
                    $mail->Body = "
                        <p>Estimado usuario,</p>
                        <p>Se ha generado la siguiente alerta correspondiente a <strong>{$alerta['titulo']}</strong>.</p>
                        $tabla
                        <p><br>Saludos,<br><b>Jesús te bendiga</b></p>
                    ";

                    $mail->send();
                }
            }
        }
    }

    echo "✔️ Alertas programadas ejecutadas correctamente.";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
