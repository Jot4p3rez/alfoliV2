<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: index.html');
    exit;
}
if ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'editor') {
    header('Location: acceso_denegado.php');
    exit;
}

require 'php/csrf.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/../conexion.php';

// Obtenemos la lista de hermanos existentes desde SP
$stmt = $pdo->prepare("CALL participantes('LISTAR', NULL, NULL,null)");
$stmt->execute();
$hermanos = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt->closeCursor(); // obligatorio con SPs

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>Agregar Hermano</title>
    <link rel="stylesheet" href="assets/style.css" />
    <link rel="shortcut icon" href="assets/logo.png" type="image/png" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="assets/scripts.js"></script>
</head>

<body>
    <div class="container">
        <img src="assets/logo.png" alt="Logo Alfolí" class="logo" />
        <h2>Agregar Participante</h2>

        <form action="php/guardar_hermano.php" method="POST" onsubmit="return validarFormularioHermano();">
            <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>" />
            <label for="nombres">Nombres:</label>
            <input type="text" name="nombres" required />

            <label for="apellidos">Apellidos:</label>
            <input type="text" name="apellidos" required />

            <label for="telefono">Teléfono (opcional):</label>
            <input type="tel" name="telefono" pattern="[0-9+ -]*" maxlength="20">


            <div style="display: flex; gap: 10px; margin-top: 10px;">
                <button type="submit" name="guardar"><i class="fa-solid fa-save"></i> Guardar</button>
                <button type="submit" name="guardar_y_agregar"><i class="fa-solid fa-plus"></i> Guardar y Agregar
                    Otro</button>
                <button type="button" onclick="limpiarFormulario()"><i class="fa-solid fa-xmark"></i> Cancelar</button>
            </div>
        </form>
        <!-- Lista de hermanos -->
        <h3 style="margin-top: 40px;">Lista de Participantes Registrados</h3>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Nombre Completo</th>
                        <th>Teléfono</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($hermanos)): ?>
                        <?php foreach ($hermanos as $h): ?>
                            <tr>
                                <td><?= htmlspecialchars($h['nombre_completo']) ?></td>
                                <td><?= htmlspecialchars($h['telefono'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2">No hay hermanos registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
    <div class="menu-actions" style="margin-top: 20px;">
        <a href="detalle.php" class="btn"><i class="fa-solid fa-arrow-left"></i> Volver a Detalles</a>
    </div>
    <?php if (isset($_SESSION['mensaje'])): ?>
        <script>
            Swal.fire({
                icon: '<?php echo $_SESSION["mensaje"]["tipo"]; ?>',
                title: '<?php echo $_SESSION["mensaje"]["tipo"] === "success" ? "Éxito" : "Error"; ?>',
                text: '<?php echo $_SESSION["mensaje"]["texto"]; ?>',
                confirmButtonText: 'Aceptar'
            });
        </script>
        <?php unset($_SESSION['mensaje']); ?>
    <?php endif; ?>

    <script>
        function limpiarFormulario() {
            document.querySelector('form').reset();
        }
    </script>
</body>

</html>