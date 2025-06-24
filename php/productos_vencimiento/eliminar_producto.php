<?php
session_start();
header('Content-Type: application/json');

// 1. Verificar sesión activa
if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no iniciada.']);
    exit;
}

// 2. Verificar rol permitido
if ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'editor') {
    echo json_encode(['success' => false, 'message' => 'No tienes permisos para realizar esta acción.']);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/../conexion.php';

// 3. Obtener ID de producto
$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID de producto no válido.']);
    exit;
}

// 4. Verificar existencia
try {
    $verificar = $pdo->prepare("SELECT id FROM detalle_alfoli WHERE id = ?");
    $verificar->execute([$id]);

    if ($verificar->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'El producto no existe.']);
        exit;
    }
} catch (PDOException $e) {
    # error_log("Error al verificar existencia: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al verificar la existencia.']);
    exit;
}

// 5. Eliminar registro
try {
    // Verificar conexión PDO
    if (!($pdo instanceof PDO)) {
        # error_log("Error: \$pdo no es un objeto PDO válido.");
        echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']);
        exit;
    } else {
        # error_log("Conexión PDO válida.");
    }

    $pdo->beginTransaction();

    $eliminar = $pdo->prepare("
        DELETE FROM laflorid_alfoli_db.detalle_alfoli
        WHERE id = :id_detalle
    ");
    $eliminar->bindParam(':id_detalle', $id, PDO::PARAM_INT);

    // Registrar la consulta SQL completa
    $sql_debug = $eliminar->queryString;
    $params_debug = [':id_detalle' => $id];
    # error_log("Consulta DELETE: " . $sql_debug . " | Parámetros: " . print_r($params_debug, true));

    $eliminar->execute();

    // Registrar errores de PDO
    $pdo_error = $pdo->errorInfo();
    if ($pdo_error[0] != '00000') {
        # error_log("Error de PDO: " . print_r($pdo_error, true));
    }

    // Registrar rowCount()
    $rows_affected = $eliminar->rowCount();
    #error_log("Filas afectadas por DELETE: " . $rows_affected);

    // 6. Auditar (opcional)
    if (file_exists('../logs.php')) {
        require '../logs.php';
        registrar_log($_SESSION['usuario'], 'Eliminó un producto caducado', "ID: $id");
    } else {
        #error_log("Archivo de auditoría no encontrado.");
    }

    $pdo->commit();
    #error_log("Transacción COMMIT exitosa.");

    echo json_encode(['success' => true, 'message' => 'Producto eliminado correctamente.']);

} catch (Exception $e) {
    $pdo->rollBack();
    # error_log("Transacción ROLLBACK. Error al eliminar producto: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al eliminar el producto: ' . $e->getMessage()]);
}
?>