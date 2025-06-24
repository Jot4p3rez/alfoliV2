<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit;
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/../conexion.php';
require 'logs.php';
require '../csrf.php';


$id = $_POST['id'] ?? null;
$nueva_clave = $_POST['nueva_clave'] ?? null;

// Validar
if (empty($id) || empty($nueva_clave)) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos.']);
    exit;
}

// Opcional: Verifica CSRF si lo tienes implementado
// if (!verificarTokenCSRF($_POST['csrf_token'])) {
//     echo json_encode(['success' => false, 'message' => 'Token inválido.']);
//     exit;
// }

$hash = password_hash($nueva_clave, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE usuarios SET clave_hash = ?, cambiar_password = 1 WHERE id = ?");
$result = $stmt->execute([$hash, $id]);


if ($result) {
    registrar_log($pdo, $_SESSION['usuario'], 'Reset de contraseña', $id);
    echo json_encode(['success' => true, 'message' => 'Contraseña actualizada correctamente.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar la contraseña.']);
}
?>