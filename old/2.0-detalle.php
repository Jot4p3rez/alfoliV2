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
    <style>
        .tarjetas-resumen {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1em;
            margin-bottom: 1em;
        }

        .tarjeta {
            background: #f5f7ff;
            border-left: 6px solid #3f51b5;
            padding: 1em;
            border-radius: 8px;
            min-width: 180px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .tarjeta h4 {
            margin: 0;
            color: #3f51b5;
        }

        .tarjeta p {
            margin: 0.2em 0;
            font-weight: bold;
        }
    </style>
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
                    <button onclick="exportToExcel()">📤 Exportar a Excel</button>
                    <button onclick="exportToPDF()">📄 Exportar a PDF</button>
                    <button onclick="window.print()">🖨️ Imprimir</button>
                </div>
            </div>
        </div>

        <div class="filters">
            <select id="filtroMes" onchange="filtrarTabla()">
                <option value="todos">📅 Todos los Meses</option>
            </select>
            <select id="filtroHermano" onchange="filtrarTabla()">
                <option value="todos">👤 Todos los Hermanos</option>
            </select>
            <select id="filtroVencimiento" onchange="filtrarTabla()">
                <option value="todos">🧾 Mostrar Todos</option>
                <option value="proximos">⚠️ Caducidad < 60 días</option>
            </select>
            <input type="text" id="busquedaGeneral" placeholder="🔍 Buscar descripción o código"
                oninput="filtrarTabla()">
        </div>

        <div class="tarjetas-resumen" id="resumenTarjetas"></div>

        <div class="results-count" id="resultsCount">📦 Cargando registros...</div>
        <div class="loader" id="loader">⏳ Por favor espera...</div>

        <div class="table-responsive">
            <table id="tablaAlfoli">
                <thead>
                    <tr>
                        <th>Cantidad</th>
                        <th>F. Registro</th>
                        <th>Mes</th>
                        <th>F. Caducidad</th>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Hermano</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <div class="menu-actions">
            <a href="agregar_alfoli.php" class="btn"><i class="fa-solid fa-circle-plus"></i> Agregar Alfolí</a>
            <a href="agregar_articulo.php" class="btn"><i class="fa-solid fa-cart-plus"></i> Agregar Artículo</a>
            <a href="agregar_hermano.php" class="btn"><i class="fa-solid fa-users"></i> Agregar Hermano</a>
            <a href="home.php" class="btn"><i class="fa-solid fa-house"></i> Volver al Menú</a>
        </div>
    </div>

    <footer class="footer">
        © 2025 Sistema Alfolí — Desarrollado por Aura Solutions Group
    </footer>

    <script>
        let dataGlobal = [];
        let datosFiltradosGlobal = [];

        async function cargarDatos() {
            document.getElementById('loader').style.display = 'block';
            try {
                const res = await fetch('php/cargar_detalle.php');
                const data = await res.json();
                dataGlobal = data;

                const meses = [...new Set(data.map(d => d.mes))];
                const hermanos = [...new Set(data.map(d => d.nombre_hermano))];
                llenarFiltro('filtroMes', meses);
                llenarFiltro('filtroHermano', hermanos);

                resumenTarjetas(data);
                filtrarTabla();
            } catch {
                Swal.fire('Error', 'No se pudieron cargar los datos.', 'error');
            } finally {
                document.getElementById('loader').style.display = 'none';
            }
        }

        function llenarFiltro(id, valores) {
            const select = document.getElementById(id);
            valores.sort().forEach(val => {
                const option = document.createElement('option');
                option.value = val;
                option.textContent = val;
                select.appendChild(option);
            });
        }

        function resumenTarjetas(data) {
            const container = document.getElementById('resumenTarjetas');
            const total = data.length;
            const vencimientos = data.filter(d => new Date(d.fecha_caducidad) <= sumarDias(59)).length;
            const hermanos = new Set(data.map(d => d.nombre_hermano)).size;

            container.innerHTML = `
        <div class="tarjeta"><h4>Total Registros</h4><p>${total}</p></div>
        <div class="tarjeta"><h4>Próximos a Vencer</h4><p>${vencimientos}</p></div>
        <div class="tarjeta"><h4>Hermanos</h4><p>${hermanos}</p></div>
      `;
        }

        function sumarDias(dias) {
            const fecha = new Date();
            fecha.setDate(fecha.getDate() + dias);
            return fecha;
        }

        function filtrarTabla() {
            const filtroMes = document.getElementById('filtroMes').value;
            const filtroHermano = document.getElementById('filtroHermano').value;
            const filtroVencimiento = document.getElementById('filtroVencimiento').value;
            const busqueda = document.getElementById('busquedaGeneral').value.toLowerCase();
            const fechaLimite = sumarDias(59);

            const tbody = document.querySelector('#tablaAlfoli tbody');
            tbody.innerHTML = '';

            datosFiltradosGlobal = dataGlobal.filter(d => {
                const cad = new Date(d.fecha_caducidad);
                const matchMes = filtroMes === 'todos' || d.mes === filtroMes;
                const matchHermano = filtroHermano === 'todos' || d.nombre_hermano === filtroHermano;
                const matchVenc = filtroVencimiento === 'todos' || (filtroVencimiento === 'proximos' && cad <= fechaLimite);
                const matchTexto = (d.codigo_barra + d.descripcion + d.nombre_hermano).toLowerCase().includes(busqueda);
                return matchMes && matchHermano && matchVenc && matchTexto;
            });

            actualizarContador(datosFiltradosGlobal.length);

            if (!datosFiltradosGlobal.length) {
                tbody.innerHTML = `<tr><td colspan="7">❌ No hay coincidencias.</td></tr>`;
                return;
            }

            datosFiltradosGlobal.forEach(d => {
                const cad = new Date(d.fecha_caducidad);
                const clase = cad <= fechaLimite ? 'alerta-caducidad' : '';
                tbody.innerHTML += `
          <tr class="${clase}">
            <td>${d.cantidad}</td>
            <td>${d.fecha_registro}</td>
            <td>${d.mes}</td>
            <td>${d.fecha_caducidad}</td>
            <td>${d.codigo_barra}</td>
            <td>${d.descripcion}</td>
            <td>${d.nombre_hermano}</td>
          </tr>`;
            });
        }

        function actualizarContador(cantidad) {
            document.getElementById('resultsCount').textContent = `📦 Mostrando ${cantidad} registro(s)`;
        }

        function exportToExcel() {
            Swal.fire({ title: 'Generando Excel...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            setTimeout(() => {
                const wb = XLSX.utils.book_new();
                const ws = XLSX.utils.json_to_sheet(datosFiltradosGlobal);
                XLSX.utils.book_append_sheet(wb, ws, "Detalle Alfolí");
                XLSX.writeFile(wb, 'detalle_alfoli.xlsx');
                Swal.close(); Swal.fire('✅ Exportado', '', 'success');
            }, 500);
        }

        function exportToPDF() {
            Swal.fire({ title: 'Generando PDF...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            setTimeout(() => {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();
                doc.text("Detalle Alfolí", 14, 16);
                doc.autoTable({
                    head: [['Cant.', 'Registro', 'Mes', 'Caducidad', 'Código', 'Descripción', 'Hermano']],
                    body: datosFiltradosGlobal.map(d => [
                        d.cantidad, d.fecha_registro, d.mes, d.fecha_caducidad, d.codigo_barra, d.descripcion, d.nombre_hermano
                    ])
                });
                doc.save("detalle_alfoli.pdf");
                Swal.close(); Swal.fire('✅ PDF generado', '', 'success');
            }, 500);
        }

        // Mostrar alerta de vencimientos directamente en el formulario
        fetch('php/cargar_detalle.php')
            .then(res => res.json())
            .then(data => {
                const fechaLimite = new Date();
                fechaLimite.setDate(fechaLimite.getDate() + 59);

                const proximos = data.filter(d => new Date(d.fecha_caducidad) <= fechaLimite);
                if (proximos.length > 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: '⚠️ Atención',
                        text: `Existen ${proximos.length} registro(s) con fecha de caducidad menor a 60 días.`,
                        confirmButtonText: 'Entendido'
                    });
                }
            });

        cargarDatos();

    </script>

</body>

</html>