<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/../conexion.php';

$id = $_POST['id'];
$nombre_usuario = trim($_POST['nombre_usuario']);
$nombre_completo = trim($_POST['nombre_completo']);
$email = trim($_POST['email']);
$rol = $_POST['rol'];

// Validación básica
if (empty($nombre_usuario) || empty($nombre_completo) || empty($email) || empty($rol)) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
    exit;
}

// Validamos que el usuario no exista duplicado (excepto él mismo)
$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE nombre_usuario = ? AND id != ?");
$stmt->execute([$nombre_usuario, $id]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'El nombre de usuario ya está en uso.']);
    exit;
}

// Actualizar datos
$stmt = $pdo->prepare("UPDATE usuarios SET nombre_usuario = ?, nombre_completo = ?, email = ?, rol = ? WHERE id = ?");
if ($stmt->execute([$nombre_usuario, $nombre_completo, $email, $rol, $id])) {
    echo json_encode(['success' => true, 'message' => 'Usuario actualizado correctamente.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el usuario.']);
}
require 'logs.php';
registrar_log($pdo, $_SESSION['usuario'], 'Edición de usuario', $nombre_usuario, 'Rol actualizado a: ' . $rol);
