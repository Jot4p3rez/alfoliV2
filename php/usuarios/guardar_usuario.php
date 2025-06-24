<?php
session_start();
header('Content-Type: application/json');

// Seguridad: solo admin puede acceder
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/../conexion.php';

$nombre_usuario = trim($_POST['nombre_usuario']);
$clave = $_POST['clave'];
$nombre_completo = trim($_POST['nombre_completo']);
$email = trim($_POST['email']);
$rol = $_POST['rol'];

// Validaciones básicas
if (empty($nombre_usuario) || empty($clave) || empty($nombre_completo) || empty($email) || empty($rol)) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
    exit;
}

// Verificar si el usuario ya existe
$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE nombre_usuario = ?");
$stmt->execute([$nombre_usuario]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'El nombre de usuario ya existe.']);
    exit;
}

// Encriptar la contraseña
$clave_hash = password_hash($clave, PASSWORD_BCRYPT);

// Insertar usuario
$stmt = $pdo->prepare("INSERT INTO usuarios (nombre_usuario, clave_hash, nombre_completo, email, rol, creado_por, activo) VALUES (?, ?, ?, ?, ?, ?, 1)");
$creado_por = $_SESSION['usuario'];

if ($stmt->execute([$nombre_usuario, $clave_hash, $nombre_completo, $email, $rol, $creado_por])) {
    echo json_encode(['success' => true, 'message' => 'Usuario creado exitosamente.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al crear el usuario.']);
}
require 'logs.php';
registrar_log($pdo, $_SESSION['usuario'], 'Creación de usuario', $nombre_usuario, 'Rol: ' . $rol);
