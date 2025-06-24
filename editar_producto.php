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

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: productos_vencimiento.php');
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/../conexion.php';
$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM articulos WHERE id = ?");
$stmt->execute([$id]);
$producto = $stmt->fetch();

if (!$producto) {
    header('Location: productos_vencimiento.php');
    exit;
}

// Calcular la fecha mínima permitida (hoy + 59 días)
$fecha_minima = date('Y-m-d', strtotime('+59 days'));
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Editar Producto</title>
    <link rel="stylesheet" href="assets/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/scripts.js"></script>

</head>

<body>
    <div class="container">
        <header class="header">
            <img src="assets/logo.png" alt="Logo Alfolí" class="logo" />
            <h2>Editar Producto</h2>
            <p>Hola, <strong><?php echo $_SESSION['nombre_completo']; ?></strong></p>
        </header>

        <form id="formEditarProducto">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($producto['id']); ?>" />
            <label for="codigo_barra">Código de Barras:</label>
            <input type="text" name="codigo_barra" id="codigo_barra"
                value="<?php echo htmlspecialchars($producto['codigo_barra']); ?>" required />

            <label for="descripcion">Descripción:</label>
            <input type="text" name="descripcion" id="descripcion"
                value="<?php echo htmlspecialchars($producto['descripcion']); ?>" required />

            <label for="cantidad">Cantidad:</label>
            <input type="number" name="cantidad" id="cantidad"
                value="<?php echo htmlspecialchars($producto['cantidad']); ?>" required min="1" />

            <label for="fecha_caducidad">Fecha de Caducidad:</label>
            <input type="date" name="fecha_caducidad" id="fecha_caducidad"
                value="<?php echo isset($producto['fecha_caducidad']) ? htmlspecialchars($producto['fecha_caducidad']) : ''; ?>"
                required min="<?php echo $fecha_minima; ?>" />

            <div style="display: flex; gap: 10px; margin-top: 10px;">
                <button type="submit" name="guardar"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
                <button type="button" onclick="limpiarFormulario()"><i class="fa-solid fa-xmark"></i> Cancelar</button>
            </div>
        </form>

        <div class="menu-actions" style="margin-top: 20px;">
            <a href="productos_vencimiento.php" class="btn"><i class="fa-solid fa-arrow-left"></i> Volver</a>
        </div>
        <script>
            function limpiarFormulario() {
                const form = document.querySelector('form');
                form.reset();
            }
        </script>
    </div>
    <footer class="footer">
        © 2025 Sistema Alfolí — Desarrollado por Aura Solutions Group
    </footer>
    <script>
        document.getElementById('formEditarProducto').addEventListener('submit', async function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            const response = await fetch('php/productos_vencimiento/editar_producto.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                Swal.fire('Actualizado', result.message, 'success')
                    .then(() => window.location.href = 'productos_vencimiento.php');
            } else {
                Swal.fire('Error', result.message, 'error');
            }
        });
    </script>
</body>

</html>