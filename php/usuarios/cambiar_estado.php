<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/../conexion.php';

$id = $_GET['id'];
$estadoActual = $_GET['estado'];

// Cambiar el estado
$nuevoEstado = ($estadoActual === '1') ? 0 : 1;

$stmt = $pdo->prepare("UPDATE usuarios SET activo = ? WHERE id = ?");
if ($stmt->execute([$nuevoEstado, $id])) {
    $mensaje = ($nuevoEstado === 1) ? 'Usuario activado exitosamente.' : 'Usuario desactivado exitosamente.';
    echo json_encode(['success' => true, 'message' => $mensaje]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al cambiar el estado del usuario.']);
}
require 'logs.php';
registrar_log($pdo, $_SESSION['usuario'], ($nuevoEstado ? 'Activación' : 'Desactivación') . ' de usuario', $id);
