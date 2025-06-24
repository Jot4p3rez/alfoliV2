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

require_once $_SERVER['DOCUMENT_ROOT'] . '/../conexion.php';

$hermano = $_POST['hermano'];
$articulo = $_POST['articulo'];
$cantidad = $_POST['cantidad'];
$fecha_caducidad = $_POST['fecha_caducidad'];
$id_usrregistra = $_SESSION['usuario']; // Asumiendo que el ID del usuario está en $_SESSION['usuario']['id']

$sql = "CALL InsAlfoli(:hermano, :articulo, :cantidad, :fecha_caducidad, :id_usrregistra)";
$stmt = $pdo->prepare($sql);

try {
    $stmt->execute([
        'hermano' => $hermano,
        'articulo' => $articulo,
        'cantidad' => $cantidad,
        'fecha_caducidad' => $fecha_caducidad,
        'id_usrregistra' => $id_usrregistra
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
?>