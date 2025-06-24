<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/../conexion.php';
require 'log_auditoria.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_caducado = $_POST['id_caducado'] ?? null;
    $nuevo_codigo = $_POST['nuevo_codigo'] ?? null;

    if (!$id_caducado || !$nuevo_codigo) {
        echo json_encode(['status' => 'error', 'message' => 'Faltan datos.']);
        exit;
    }

    try {
        // Buscar producto nuevo por código
        $stmt = $pdo->prepare("SELECT id, nombre FROM productos WHERE codigo = :codigo AND estado = 'activo' LIMIT 1");
        $stmt->execute([':codigo' => $nuevo_codigo]);
        $nuevo_producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$nuevo_producto) {
            echo json_encode(['status' => 'error', 'message' => 'El nuevo producto no existe o no está activo.']);
            exit;
        }

        // Reemplazar producto caducado
        $stmt = $pdo->prepare("UPDATE productos SET codigo = :nuevo_codigo, nombre = :nuevo_nombre, estado = 'reemplazado' WHERE id = :id_caducado");
        $stmt->execute([
            ':nuevo_codigo' => $nuevo_codigo,
            ':nuevo_nombre' => $nuevo_producto['nombre'],
            ':id_caducado' => $id_caducado
        ]);

        // Registrar auditoría
        $usuario = $_SESSION['usuario'] ?? 'sistema';
        $detalle = "Reemplazó el producto ID $id_caducado con el código $nuevo_codigo (ID nuevo: {$nuevo_producto['id']})";
        registrarAccion($usuario, 'Reemplazo de producto caducado', $detalle);

        echo json_encode(['status' => 'ok', 'message' => 'Producto reemplazado con éxito.']);

    } catch (Exception $e) {
        error_log("Error al reemplazar producto: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Ocurrió un error al reemplazar el producto.']);
    }
}
?>