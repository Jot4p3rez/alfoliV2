<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.html');
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/../conexion.php';

$pdo->exec("SET lc_time_names = 'es_ES'");

// Invocar el Stored Procedure
$sql = "CALL ObtRegisAlfoli()";

$stmt = $pdo->query($sql);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($data);