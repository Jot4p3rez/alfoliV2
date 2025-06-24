<?php
session_start();
require 'csrf.php';

if (!verificarTokenCSRF($_POST['csrf_token'])) {
    header('Location: ../agregar_alfoli.php?error=csrf');
    exit;
}

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.html');
    exit;
}

require 'conexion.php';

$hermano = $_POST['hermano'];
$articulo = $_POST['articulo'];
$cantidad = $_POST['cantidad'];
$fecha_caducidad = $_POST['fecha_caducidad'];

$sql = "INSERT INTO detalle_alfoli (id_hermano, id_articulo, cantidad, fecha_registro, fecha_caducidad)
        VALUES (:hermano, :articulo, :cantidad, NOW(), :fecha_caducidad)";
$stmt = $pdo->prepare($sql);

try {
    $stmt->execute([
        'hermano' => $hermano,
        'articulo' => $articulo,
        'cantidad' => $cantidad,
        'fecha_caducidad' => $fecha_caducidad
    ]);

    if (isset($_POST['guardar_y_agregar'])) {
        header('Location: ../agregar_alfoli.php?msg=otro');
    } else {
        header('Location: ../detalle.php?msg=exito');
    }
    exit;

} catch (Exception $e) {
    header('Location: ../agregar_alfoli.php?error=bd');
    exit;
}
