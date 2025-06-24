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

require 'conexion.php';

// Obtener datos y agregar prefijo
$prefijo = "Hno. "; // Puedes cambiar esto o hacerlo dinámico
$nombres_original = $_POST['nombres'];
$nombres = $prefijo . $nombres_original;
$apellidos = $_POST['apellidos'];

$sql = "INSERT INTO hermanos (nombres, apellidos) VALUES (:nombres, :apellidos)";
$stmt = $pdo->prepare($sql);

try {
    $stmt->execute([
        'nombres' => $nombres,
        'apellidos' => $apellidos
    ]);

    // ¿Qué botón se presionó?
    $redireccion = isset($_POST['guardar_y_agregar']) ? '../agregar_hermano.php' : '../detalle.php';

    $_SESSION['mensaje'] = [
        'tipo' => 'success',
        'texto' => 'Registrado exitosamente.'
    ];

    header("Location: $redireccion");
    exit;

} catch (Exception $e) {
    $_SESSION['mensaje'] = [
        'tipo' => 'error',
        'texto' => 'Error al guardar: ' . $e->getMessage()
    ];
    header('Location: ../agregar_hermano.php');
    exit;
}