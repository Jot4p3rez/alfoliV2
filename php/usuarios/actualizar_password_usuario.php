<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/../conexion.php';

$nueva_clave = $_POST['nueva_clave'];

if (empty($nueva_clave)) {
    echo json_encode(['success' => false, 'message' => 'La contraseña es obligatoria.']);
    exit;
}

// Encriptar nueva contraseña
$clave_hash = password_hash($nueva_clave, PASSWORD_BCRYPT);

// Actualizar contraseña y marcar que ya no es necesario cambiarla
$stmt = $pdo->prepare("UPDATE usuarios SET clave_hash = ?, cambiar_password = 0 WHERE nombre_usuario = ?");
if ($stmt->execute([$clave_hash, $_SESSION['usuario']])) {
    echo json_encode(['success' => true, 'message' => 'Contraseña actualizada correctamente.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar la contraseña.']);
}
require 'logs.php';
registrar_log($pdo, $_SESSION['usuario'], 'Cambio forzado de contraseña', $_SESSION['usuario']);
