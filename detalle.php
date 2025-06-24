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
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Detalle Ingreso Alfolí</title>
  <link rel="stylesheet" href="assets/style.css">
  <link rel="shortcut icon" href="assets/logo.png" type="image/png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="assets/scripts.js"></script>
</head>

<body>
  <div class="container">
    <header class="header">
      <img src="assets/logo.png" alt="Logo Alfolí" class="logo">
      <h2>Detalle de Ingreso Alfolí Mensual</h2>
      <p>Hola, <strong><?php echo $_SESSION['nombre_completo']; ?></strong></p>
    </header>

    <div class="actions">
      <div class="dropdown">
        <button class="dropbtn">Opciones</button>
        <div class="dropdown-content">
          <button onclick="exportToExcel()">📤 Exportar a Excel (Filtro)</button>
          <button onclick="exportToPDF()">📄 Exportar a PDF (Filtro)</button>
          <button onclick="window.print()">🖨️ Imprimir</button>
        </div>
      </div>
    </div>

    <div class="filters">
      <select id="filtroVencimiento" onchange="filtrarTabla()">
        <option value="todos">🧾 Mostrar Todos</option>
        <option value="proximos">⚠️ Pronto a Vencer (menos de 60 días)</option>
      </select>
      <input type="text" id="busquedaGeneral" placeholder="🔍 Buscar..." oninput="filtrarTabla()">
    </div>
    <div class="menu-actions">
      <a href="agregar_alfoli.php" class="btn"><i class="fa-solid fa-circle-plus"></i> Agregar Alfolí</a>
      <a href="agregar_articulo.php" class="btn"><i class="fa-solid fa-cart-plus"></i> Agregar Artículo</a>
      <a href="agregar_hermano.php" class="btn"><i class="fa-solid fa-users"></i> Agregar Hermano</a>
    </div>

    <div class="results-count" id="resultsCount">Mostrando registros...</div>

    <div class="loader" id="loader">⏳ Cargando registros, por favor espera...</div>
    <div class="table-responsive">
      <table id="tablaAlfoli">
        <thead>
          <tr>
            <th>Cantidad</th>
            <th>Fecha Registro</th>
            <th>Mes</th>
            <th>F. Caducidad</th>
            <th>Descripción</th>
            <th>Nombre del Hermano</th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
    </div>
  </div>
  <div class="menu-actions">
    <a href="home.php" class="btn"><i class="fa-solid fa-house"></i> Volver al Menú Principal</a>
  </div>
  <footer class="footer">
    © 2025 Sistema Alfolí — Desarrollado por Aura Solutions Group
  </footer>
  <script>
    let dataGlobal = [];
    let datosFiltradosGlobal = [];

    cargarDatos();
  </script>
</body>

</html>