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

?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Agregar Hermano</title>
  <link rel="stylesheet" href="assets/style.css">
  <link rel="shortcut icon" href="assets/logo.png" type="image/png">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <script src="assets/scripts.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>
  <div class="container">
    <img src="assets/logo.png" alt="Logo Alfolí" class="logo">
    <h2>Agregar Participante</h2>
    <form action="php/guardar_hermano.php" method="POST" onsubmit="return validarFormularioHermano();">
      <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
      <label for="nombres">Nombres:</label>
      <input type="text" name="nombres" required>

      <label for="apellidos">Apellidos:</label>
      <input type="text" name="apellidos" required>

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
      const form = document.querySelector('form');
      form.reset();
    }
  </script>
</body>

</html>