<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header('Location: index.html');
  exit;
}

if ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'visualizador' && $_SESSION['rol'] !== 'editor') {
  header('Location: acceso_denegado.php');
  exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/../conexion.php';
require 'php/csrf.php';

$dias = 55;
// aplicar control de cumplimiento por mes, si requieren detalle de los meses anterioes realizar un módulo de indicadores historico (cumplimientos, vencimiento y extras)
$stmt_cumplimiento = $pdo->prepare("CALL ObtCumpAportes()");
$stmt_cumplimiento->execute();
$indicadores = $stmt_cumplimiento->fetchAll();
$stmt_cumplimiento->closeCursor();

// se debe aplicar unicamente a los produtos relacionados a los artículos del mes
$stmt_comparacion_total = $pdo->prepare("CALL ObtCompTotalesArt()");
$stmt_comparacion_total->execute();
$comparacion_total = $stmt_comparacion_total->fetchAll();
$stmt_comparacion_total->closeCursor();

// este no debe tener restricciones o filtros periodicos, dado que los articulos puedes caducar en fechas futuras a los ingresos.
$stmt_productos_caducidad = $pdo->prepare("CALL ObtArtProxAVencer(:dias)");
$stmt_productos_caducidad->bindParam(':dias', $dias, PDO::PARAM_INT);
$stmt_productos_caducidad->execute();
$productos_caducidad = $stmt_productos_caducidad->fetchAll();
$stmt_productos_caducidad->closeCursor();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Dashboard Alfolí</title>
  <link rel="stylesheet" href="assets/style.css">
  <link rel="shortcut icon" href="assets/logo.png" type="image/png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.6/jspdf.plugin.autotable.min.js"></script>
  <script src="assets/scripts.js"></script>
</head>

<body>
  <div class="container">
    <img src="assets/logo.png" alt="Logo Alfolí" class="logo">
    <h2>Dashboard Alfolí</h2>

    <div class="actions">
      <div class="dropdown">
        <button class="dropbtn">Opciones</button>
        <div class="dropdown-content">
          <button onclick="exportToExcel()">📤 Exportar a Excel</button>
          <button onclick="exportToPDF()">📄 Exp. a PDF</button>
          <button onclick="window.print()">🖨️ Imprimir</button>
        </div>
      </div>
    </div>
    <div class="indicador-cumplimiento">
      <h3>Indicadores de Cumplimiento</h3>
      <div class="table-responsive">
        <table>
          <tr>
            <th>Hermano</th>
            <th>Mes</th>
            <th>Artículo</th>
            <th>Cantidad</th>
            <th>Aporte</th>
            <th>Estado</th>
          </tr>
          <?php foreach ($indicadores as $indicador): ?>
            <tr class="<?= $indicador['estado'] === 'No Cumple' ? 'no-cumple' : '' ?>">
              <td><?= $indicador['hermano'] ?></td>
              <td><?= $indicador['mes'] ?></td>
              <td><?= $indicador['articulo'] ?></td>
              <td><?= $indicador['cant'] ?></td>
              <td><?= $indicador['aporte'] ?></td>
              <td><?= $indicador['estado'] ?></td>
            </tr>
          <?php endforeach; ?>
        </table>
      </div>
    </div>
    <div class="indicador-caducidad">
      <h3>Productos Próximos a Caducar</h3>
      <div class="table-responsive">
        <table>
          <tr>
            <th>Fecha Registro</th>
            <th>Mes</th>
            <th>Fecha Caducidad</th>
            <th>Código Barras</th>
            <th>Descripción</th>
            <th>Cantidad</th>
          </tr>
          <?php foreach ($productos_caducidad as $producto): ?>
            <tr class="caducidad-proxima">
              <td><?= $producto['fecha_registro'] ?></td>
              <td><?= $producto['mes'] ?></td>
              <td><?= $producto['fecha_caducidad'] ?></td>
              <td><?= $producto['codigo_barra'] ?></td>
              <td><?= $producto['descripcion'] ?></td>
              <td><?= $producto['cantidad'] ?></td>
            </tr>
          <?php endforeach; ?>
        </table>
      </div>
    </div>

    <div class="indicador-total-articulo">
      <h3>Comparativa Total por Artículo</h3>
      <div class="table-responsive">
        <table>
          <tr>
            <th>Mes</th>
            <th>Artículo</th>
            <th>Cant. Requerida/Art.</th>
            <th>Total Esperado</th>
            <th>Total Aportado</th>
            <th>Diferencia</th>
          </tr>
          <?php foreach ($comparacion_total as $item): ?>
            <?php
            $diferencia = $item['total_esperado_articulo'] - $item['total_aportado_articulo'];
            $clase_destaque = ($diferencia > 0) ? 'no-cumple-total-articulo' : '';
            ?>
            <tr class="<?= $clase_destaque ?>">
              <td><?= $item['mes'] ?></td>
              <td><?= $item['articulo'] ?></td>
              <td><?= $item['cant_requerida_articulo'] ?></td>
              <td><?= $item['total_esperado_articulo'] ?></td>
              <td><?= $item['total_aportado_articulo'] ?></td>
              <td><?= $diferencia ?></td>
            </tr>
          <?php endforeach; ?>
        </table>
      </div>
    </div>
  </div>
  <div class="menu-actions" style="margin-top: 20px;">
    <a href="home.php" class="btn"><i class="fa-solid fa-house"></i>Volver al Menú Principal</a>
  </div>
  <footer class="footer">
    © 2025 Sistema Alfolí - Desarrollado por Aura Solutions Group SpA
  </footer>

  <script>
    const datosFiltradosGlobal = <?php echo json_encode($indicadores); ?>;
    const datosComparacionTotalArticulos = <?php echo json_encode($comparacion_total); ?>;
    const datosProductosCaducidad = <?php echo json_encode($productos_caducidad); ?>;

    function exportToExcel() {
      const fecha = new Date().toLocaleDateString().replace(/\//g, '-');
      const wb = XLSX.utils.book_new();

      const ws_cumplimiento = XLSX.utils.json_to_sheet(datosFiltradosGlobal);
      XLSX.utils.book_append_sheet(wb, ws_cumplimiento, "Cumplimiento");

      const ws_comparacion = XLSX.utils.json_to_sheet(datosComparacionTotalArticulos);
      XLSX.utils.book_append_sheet(wb, ws_comparacion, "Total por Artículo");

      const ws_caducidad = XLSX.utils.json_to_sheet(datosProductosCaducidad);
      XLSX.utils.book_append_sheet(wb, ws_caducidad, "Productos Caducidad");

      XLSX.writeFile(wb, `alfoli_dashboard_${fecha}.xlsx`);
    }

    function exportToPDF() {
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF();
      const fecha = new Date().toLocaleDateString();

      doc.text(`Indicadores de Cumplimiento (${fecha})`, 14, 16);
      doc.autoTable({
        head: [['Hermano', 'Mes', 'Artículo', 'Cantidad', 'Aporte', 'Estado']],
        body: datosFiltradosGlobal.map(item => [item.hermano, item.mes, item.articulo, item.cant, item.aporte, item.estado])
      });

      doc.addPage();
      doc.text(`Comparativa Total por Artículo (${fecha})`, 14, 16);
      doc.autoTable({
        head: [['Mes', 'Artículo', 'Cant. Requerida', 'Total Esperado', 'Total Aportado', 'Diferencia']],
        body: datosComparacionTotalArticulos.map(item => [item.mes, item.articulo, item.cant_requerida_articulo, item.total_esperado_articulo, item.total_aportado_articulo, (item.total_esperado_articulo - item.total_aportado_articulo)])
      });

      doc.addPage();
      doc.text(`Productos Próximos a Caducar (${fecha})`, 14, 16);
      doc.autoTable({
        head: [['Hermano', 'Fecha Registro', 'Mes', 'Fecha Caducidad', 'Código Barras', 'Descripción', 'Cantidad']],
        body: datosProductosCaducidad.map(item => [item.nombre_hermano, item.fecha_registro, item.mes, item.fecha_caducidad, item.codigo_barra, item.descripcion, item.cantidad])
      });

      doc.save(`alfoli_dashboard_${fecha}.pdf`);
    }
  </script>
</body>

</html>