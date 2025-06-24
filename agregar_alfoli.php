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

require_once $_SERVER['DOCUMENT_ROOT'] . '/../conexion.php';
require 'php/csrf.php';

$fmt = new IntlDateFormatter('es_ES', IntlDateFormatter::FULL, IntlDateFormatter::NONE, null, null, 'MMMM');
$mesActual = ucfirst($fmt->format(new DateTime()));
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Agregar Alfolí</title>
  <link rel="stylesheet" href="assets/style.css">
  <link rel="shortcut icon" href="assets/logo.png" type="image/png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="assets/scripts.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>

<body>
  <div class="container">
    <header class="header">
      <img src="assets/logo.png" alt="Logo Alfolí" class="logo">
      <h2>Agregar Alfolí</h2>
      <p>Hola, <strong><?php echo $_SESSION['nombre_completo']; ?></strong></p>
    </header>

    <form action="php/guardar_alfoli.php" method="POST" onsubmit="return validarFormularioAlfoli();">
      <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
      <label for="hermano">Nombre del Hermano:</label>
      <select name="hermano" required>
        <option value="">Seleccione</option>
        <?php
        $hermanos = $pdo->query("SELECT id, CONCAT(nombres, ' ', apellidos) as nombre_completo FROM hermanos")->fetchAll();
        foreach ($hermanos as $hermano) {
          echo "<option value='{$hermano['id']}'>{$hermano['nombre_completo']}</option>";
        }
        ?>
      </select>

      <label for="articulo">Artículo:</label>
      <select name="articulo" required>
        <option value="">Seleccione</option>
        <?php
        $articulos = $pdo->prepare("SELECT id, descripcion FROM articulos WHERE mes_articulo = :mes");
        $articulos->execute(['mes' => $mesActual]);
        $articulosList = $articulos->fetchAll();

        if (!empty($articulosList)) {
          foreach ($articulosList as $articulo) {
            echo "<option value='{$articulo['id']}'>{$articulo['descripcion']}</option>";
          }
        } else {
          echo "<option value=''>No hay artículos para este mes</option>";
        }
        ?>
      </select>

      <label for="cantidad">Cantidad (1-9):</label>
      <input type="number" name="cantidad" min="1" max="9" placeholder="Ingrese la cantidad" required>

      <label for="fecha_caducidad">Fecha de Caducidad:</label>
      <input type="text" id="fecha_caducidad" name="fecha_caducidad" placeholder="Seleccione la fecha de caducidad"
        required>

      <div style="display: flex; gap: 10px; margin-top: 10px;">
        <button type="submit" name="guardar"><i class="fa-solid fa-save"></i> Guardar</button>
        <button type="submit" name="guardar_y_agregar"><i class="fa-solid fa-plus"></i> Guardar y Agregar Otro</button>
        <button type="button" onclick="limpiarFormulario()"><i class="fa-solid fa-xmark"></i> Cancelar</button>
      </div>

    </form>
    <div class="menu-actions" style="margin-top: 20px;">
      <a href="detalle.php" class="btn"><i class="fa-solid fa-arrow-left"></i> Volver a Detalles</a>
    </div>
  </div>

  <?php if (isset($_GET['msg']) && $_GET['msg'] === 'otro'): ?>
    <script>
      Swal.fire({
        icon: 'success',
        title: '¡Registro guardado!',
        text: 'Puedes agregar otro artículo.',
        confirmButtonText: 'Aceptar'
      });
    </script>
  <?php elseif (isset($_GET['error']) && $_GET['error'] === 'csrf'): ?>
    <script>
      Swal.fire({
        icon: 'error',
        title: 'Error de seguridad',
        text: 'Token CSRF inválido. Intenta de nuevo.',
        confirmButtonText: 'Aceptar'
      });
    </script>
  <?php elseif (isset($_GET['error']) && $_GET['error'] === 'bd'): ?>
    <script>
      Swal.fire({
        icon: 'error',
        title: 'Error al guardar',
        text: 'Hubo un problema con la base de datos.',
        confirmButtonText: 'Aceptar'
      });
    </script>
  <?php endif; ?>
  <script>
    flatpickr("#fecha_caducidad", {
      dateFormat: "Y-m-d",
      minDate: new Date().fp_incr(60), // mínimo 60 días desde hoy
      locale: "es"
    });
  </script>

</body>

</html>