<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../../index.html');
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/../conexion.php';

$diasProntoVencer = 59;
$hoy = date('Y-m-d');
$fechaLimite = date('Y-m-d', strtotime("+{$diasProntoVencer} days"));

// ✅ USAMOS ALIAS CLAROS: id_articulo y id_detalle
$sql = "SELECT 
            a.id AS id_articulo,
            b.id AS id_detalle,
            b.fecha_registro,
            b.fecha_caducidad,
            a.codigo_barra,
            a.descripcion,
            b.cantidad,
            CASE 
                WHEN b.fecha_caducidad < :hoy_vencido THEN 'vencido'
                WHEN b.fecha_caducidad BETWEEN :hoy_pronto AND :fechaLimite_pronto THEN 'pronto_vencer'
                ELSE 'ok'
            END AS estado
        FROM laflorid_alfoli_db.articulos a
        JOIN laflorid_alfoli_db.detalle_alfoli b ON a.id = b.id_articulo
        WHERE b.fecha_caducidad <= :fechaLimite_where
        ORDER BY b.fecha_caducidad ASC";

$stmt = $pdo->prepare($sql);

if (!($pdo instanceof PDO)) {
    error_log("Error: \$pdo no es un objeto PDO válido.");
    die("Error: No se pudo establecer la conexión a la base de datos correctamente.");
}

$stmt->execute([
    'hoy_vencido' => $hoy,
    'hoy_pronto' => $hoy,
    'fechaLimite_pronto' => $fechaLimite,
    'fechaLimite_where' => $fechaLimite
]);

$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($productos);
?>