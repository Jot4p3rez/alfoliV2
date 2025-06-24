<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.html');
    exit;
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/../conexion.php';
require 'csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verificarTokenCSRF($_POST['csrf_token'])) {
        $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Error de seguridad: Token CSRF inválido.'];
        header("Location: ../alertas.php");
        exit;
    }

    $usuario_id = $_SESSION['usuario_id']; // Asegúrate de que 'usuario_id' esté correctamente seteado en tu sesión

    $alertas_config = [
        'stock' => ['activar' => $_POST['activar_stock'] ?? false, 'correo' => trim($_POST['correo_stock'] ?? '')],
        'vencidos' => ['activar' => $_POST['activar_vencidos'] ?? false, 'correo' => trim($_POST['correo_vencidos'] ?? '')],
        'por_vencer' => ['activar' => $_POST['activar_por_vencer'] ?? false, 'correo' => trim($_POST['correo_por_vencer'] ?? '')],
        'incumplimientos' => ['activar' => $_POST['activar_incumplimientos'] ?? false, 'correo' => trim($_POST['correo_incumplimientos'] ?? '')],
    ];

    $programar_alertas = $_POST['programar_alertas'] ?? false;
    $frecuencia_alerta = $_POST['frecuencia_alerta'] ?? null;
    $dia_semana_alerta = $_POST['dia_semana_alerta'] ?? null;
    $dia_mes_alerta = $_POST['dia_mes_alerta'] ?? null;
    $hora_alerta = $_POST['hora_alerta'] ?? null;
    $programar_todas = $_POST['programar_todas'] ?? false;

    try {
        // Guardar o actualizar la configuración de cada alerta
        foreach ($alertas_config as $tipo => $config) {
            $stmt = $pdo->prepare("REPLACE INTO alertas_programadas (usuario_id, tipo_alerta, correo, programada, frecuencia, dia_semana, dia_mes, hora, programar_todas) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $usuario_id,
                $tipo,
                $config['correo'],
                $programar_alertas,
                $frecuencia_alerta,
                $dia_semana_alerta,
                $dia_mes_alerta,
                $hora_alerta,
                $programar_todas
            ]);
        }

        $_SESSION['mensaje'] = ['tipo' => 'success', 'texto' => 'Configuración de alertas guardada correctamente.'];

    } catch (PDOException $e) {
        $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Error al guardar la configuración: ' . $e->getMessage()];
    }

} else {
    $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Acceso no permitido.'];
}

header("Location: ../alertas.php");
exit;
?>