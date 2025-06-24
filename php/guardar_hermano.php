<?php
session_start();
require 'csrf.php';

if (!verificarTokenCSRF($_POST['csrf_token'])) {
    $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Token de seguridad inválido.'];
    header('Location: ../agregar_hermano.php');
    exit;
}

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.html');
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/../conexion.php';

// Prefijo dinámico
$prefijo = "Hno. ";
$nombres_original = $_POST['nombres'];
$nombres = $prefijo . $nombres_original;
$apellidos = $_POST['apellidos'];
$telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : null;


try {
    $stmt = $pdo->prepare("CALL participantes('AGREGAR', :nombres, :apellidos, :telefono)");
    $stmt->execute([
        'nombres' => $nombres,
        'apellidos' => $apellidos,
        'telefono' => $telefono
    ]);

    $redireccion = isset($_POST['guardar_y_agregar']) ? '../agregar_hermano.php' : '../detalle.php';
    $_SESSION['mensaje'] = ['tipo' => 'success', 'texto' => 'Registrado exitosamente.'];
    header("Location: $redireccion");
    exit;

} catch (Exception $e) {
    $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Error al guardar: ' . $e->getMessage()];
    header('Location: ../agregar_hermano.php');
    exit;
}
