<?php
session_start();
require 'csrf.php';

header('Content-Type: application/json');

// Verificar token CSRF
if (!verificarTokenCSRF($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido.']);
    exit;
}

// Verificar sesión activa
if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no iniciada.']);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/../conexion.php';


$codigo_barra = $_POST['codigo_barra'];
$descripcion = $_POST['descripcion'];
$cantidad = $_POST['cantidad'];
$mes_articulo = $_POST['mes_articulo'];

// Verificar qué botón se presionó
$guardarYAgregar = isset($_POST['guardar_y_agregar']);

$sql = "INSERT INTO articulos (codigo_barra, descripcion, cantidad, mes_articulo)
        VALUES (:codigo_barra, :descripcion, :cantidad, :mes_articulo)";
$stmt = $pdo->prepare($sql);

try {
    $stmt->execute([
        'codigo_barra' => $codigo_barra,
        'descripcion' => $descripcion,
        'cantidad' => $cantidad,
        'mes_articulo' => $mes_articulo
    ]);

    echo json_encode([
        'success' => true,
        'message' => $guardarYAgregar
            ? 'Artículo guardado. Puedes agregar otro.'
            : 'Artículo guardado exitosamente.'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar: ' . $e->getMessage()
    ]);
}
?>