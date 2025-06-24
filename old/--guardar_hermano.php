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

// Obtener datos
$nombres_original = $_POST['nombres'];
$apellidos_original = $_POST['apellidos'];

// Normalizar nombres y apellidos
$nombres_normalizado = preg_replace('/\s+/', ' ', trim(strtolower($nombres_original)));
$apellidos_normalizado = preg_replace('/\s+/', ' ', trim(strtolower($apellidos_original)));
$nombres_normalizado = str_replace(array('á', 'é', 'í', 'ó', 'ú'), array('a', 'e', 'i', 'o', 'u'), $nombres_normalizado);
$apellidos_normalizado = str_replace(array('á', 'é', 'í', 'ó', 'ú'), array('a', 'e', 'i', 'o', 'u'), $apellidos_normalizado);

// Determinar el prefijo según la terminación del nombre (¡¡¡ADVERTENCIA: Método poco fiable!!!)
$letra_final = substr($nombres_normalizado, -1);
$prefijo = ($letra_final === 'a') ? "Hna. " : "Hno. ";
$nombres_con_prefijo = $prefijo . $nombres_original; // Usar el nombre original para la inserción

// Validar si el hermano ya existe (ignorando tildes y mayúsculas/minúsculas)
$sql_verificar = "SELECT id FROM hermanos WHERE LOWER(TRIM(nombres)) = :nombres AND LOWER(TRIM(apellidos)) = :apellidos";
$stmt_verificar = $pdo->prepare($sql_verificar);
$stmt_verificar->execute([
    'nombres' => $nombres_normalizado,
    'apellidos' => $apellidos_normalizado
]);

if ($stmt_verificar->fetch()) {
    $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Ya existe un participante con ese nombre y apellido.'];
    header('Location: ../agregar_hermano.php');
    exit;
}

// Si no existe, insertar el nuevo hermano
$sql_insertar = "INSERT INTO hermanos (nombres, apellidos) VALUES (:nombres, :apellidos)";
$stmt_insertar = $pdo->prepare($sql_insertar);

try {
    $stmt_insertar->execute([
        'nombres' => $nombres_con_prefijo,
        'apellidos' => $apellidos_original // Usar el apellido original para la inserción
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
?>