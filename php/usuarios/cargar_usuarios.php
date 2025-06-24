<?php
session_start();
header('Content-Type: application/json');
#if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
#   echo json_encode([]);
#    exit;
#}
#require '../conexion.php';
#$stmt = $pdo->query("SELECT id, nombre_usuario, nombre_completo, email, case rol  when 'admin' then 'Administrador' when 'editor' then 'Moderador' when 'visualizador' then 'Consultas'else 'usuario' end as rol, activo FROM usuarios WHERE nombre_usuario <> 'admin' ORDER BY creado_en DESC");
#$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
#echo json_encode($data);


if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode([]);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/../conexion.php';

// Llamar al procedimiento almacenado
$stmt = $pdo->prepare("CALL obtener_usuarios()");
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data);
?>