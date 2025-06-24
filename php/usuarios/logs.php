<?php
function registrar_log($pdo, $usuario_actor, $accion, $usuario_afectado = '', $detalles = '')
{
    $ip = $_SERVER['REMOTE_ADDR'];

    $stmt = $pdo->prepare("INSERT INTO logs (ip_origen, usuario_actor, accion, usuario_afectado, detalles) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$ip, $usuario_actor, $accion, $usuario_afectado, $detalles]);
}
