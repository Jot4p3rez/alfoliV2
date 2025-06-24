<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida.']);
    exit;
}

if ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'editor') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/../conexion.php';

// Depuración: Verificar los datos recibidos
error_log('Datos recibidos en editar_producto.php (backend): ' . print_r($_POST, true));

$id_detalle = $_POST['id_detalle'] ?? null;
$id_articulo = $_POST['id_articulo'] ?? null;
$codigo_barra = $_POST['codigo_barra'] ?? null;
$descripcion = $_POST['descripcion'] ?? null;
$cantidad = $_POST['cantidad'] ?? null;
$fecha_caducidad = $_POST['fecha_caducidad'] ?? null;

// Validación
if (!$id_detalle || !$id_articulo || !$codigo_barra || !$descripcion || !$cantidad || !$fecha_caducidad) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos para la actualización.']);
    exit;
}

if (!is_numeric($cantidad) || $cantidad < 1) {
    echo json_encode(['success' => false, 'message' => 'Cantidad inválida.']);
    exit;
}

// Validar formato de fecha (puedes usar una función más robusta si es necesario)
if (strtotime($fecha_caducidad) === false) {
    echo json_encode(['success' => false, 'message' => 'Fecha de caducidad inválida.']);
    exit;
}

// Validar que la fecha no sea menor a hoy + 59 días (backend)
$fecha_minima = date('Y-m-d', strtotime('+59 days'));
if ($fecha_caducidad < $fecha_minima) {
    echo json_encode(['success' => false, 'message' => 'La fecha debe ser 2 meses posterior a la fecha actual.']);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt_articulo = $pdo->prepare("
            UPDATE laflorid_alfoli_db.articulos
            SET codigo_barra = :codigo_barra,
                descripcion = :descripcion
            WHERE id = :id_articulo
        ");
    $stmt_articulo->execute([
        ':codigo_barra' => $codigo_barra,
        ':descripcion' => $descripcion,
        ':id_articulo' => $id_articulo
    ]);

    $stmt_detalle = $pdo->prepare("
            UPDATE laflorid_alfoli_db.detalle_alfoli
            SET fecha_caducidad = :fecha_caducidad,
                cantidad = :cantidad
            WHERE id = :id_detalle
        ");
    $stmt_detalle->execute([
        ':fecha_caducidad' => $fecha_caducidad,
        ':cantidad' => $cantidad,
        ':id_detalle' => $id_detalle
    ]);

    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Producto actualizado correctamente.']);

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Error al actualizar producto: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el producto: ' . $e->getMessage()]);
}
?>