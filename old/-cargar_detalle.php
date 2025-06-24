<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.html');
    exit;
}

require 'conexion.php';

$pdo->exec("SET lc_time_names = 'es_ES'");

// aplicar SP en la DB para evitar exponer la clave de la DB
$sql = "SELECT 
            detalle_alfoli.cantidad,
            detalle_alfoli.fecha_registro,
            Upper(MONTHNAME(detalle_alfoli.fecha_registro)) as mes,
            detalle_alfoli.fecha_caducidad,
            substring(articulos.descripcion,1,30) AS descripcion,
            CONCAT(hermanos.nombres, ' ', hermanos.apellidos) AS nombre_hermano
        FROM detalle_alfoli
        INNER JOIN articulos ON detalle_alfoli.id_articulo = articulos.id
        INNER JOIN hermanos ON detalle_alfoli.id_hermano = hermanos.id
        ORDER BY detalle_alfoli.fecha_registro DESC";

$stmt = $pdo->query($sql);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($data);
