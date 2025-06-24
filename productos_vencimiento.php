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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Gestión Productos Caducados</title>
    <link rel="shortcut icon" href="assets/logo.png" type="image/png">
    <link rel="stylesheet" href="assets/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/scripts.js"></script>
</head>

<body>
    <div class="container">
        <header class="header">
            <img src="assets/logo.png" alt="Logo Alfolí" class="logo" />
            <h2>Productos Pronto a Vencer / Vencidos</h2>
            <p>Hola, <strong><?php echo $_SESSION['nombre_completo']; ?></strong></p>
        </header>

        <div class="menu-actions">
            <div class="dropdown">
                <btn class="dropbtn">Opciones</btn>
                <div class="dropdown-content">
                    <button onclick="exportToExcel()">📤 Exportar a Excel</button>
                    <button onclick="exportToPDF()">📄 Exp. a PDF</button>
                    <button onclick="window.print()">🖨️ Imprimir</button>
                </div>
            </div>
        </div>

        <div class="filters">
            <select id="filtroEstado" onchange="filtrarTabla()">
                <option value="todos">Todos</option>
                <option value="pronto_vencer">Pronto a Vencer</option>
                <option value="vencido">Vencido</option>
            </select>
            <input type="text" id="busqueda" placeholder="🔍 Buscar producto..." oninput="filtrarTabla()" />
        </div>

        <div class="results-count" id="resultsCount">Cargando productos...</div>
        <div class="table-responsive">
            <table id="tablaProductos">
                <thead>
                    <tr>
                        <th>Fecha Registro</th>
                        <th>Fecha Caducidad</th>
                        <th>Descripción</th>
                        <th>Cantidad</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    <div class="menu-actions">
        <a href="home.php" class="btn"><i class="fa-solid fa-house"></i> Volver al Menú Principal</a>
    </div>
    <footer class="footer">
        © 2025 Sistema Alfolí — Desarrollado por Aura Solutions Group
    </footer>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>

    <script>
        let dataProductos = [];
        let datosFiltradosGlobal = [];
        async function cargarProductos() {
            try {
                const response = await fetch('php/productos_vencimiento/cargar_productos.php');
                const data = await response.json();
                dataProductos = data;
                filtrarTabla();
            } catch (error) {
                Swal.fire('Error', 'No se pudieron cargar los productos.', 'error');
            }
        }

        function filtrarTabla() {
            const filtroEstado = document.getElementById('filtroEstado').value;
            const textoBusqueda = document.getElementById('busqueda').value.toLowerCase();
            const tbody = document.querySelector('#tablaProductos tbody');
            tbody.innerHTML = '';

            datosFiltradosGlobal = dataProductos.filter(item => {
                const cumpleEstado = filtroEstado === 'todos' || filtroEstado === item.estado;
                const textoItem = `${item.descripcion} ${item.codigo_barra}`.toLowerCase();
                return cumpleEstado && textoItem.includes(textoBusqueda);
            });

            actualizarContador(datosFiltradosGlobal.length);

            if (datosFiltradosGlobal.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7">No hay productos que coincidan.</td></tr>';
                return;
            }

            datosFiltradosGlobal.forEach(item => {
                let clase = item.estado === 'pronto_vencer' ? 'alerta-caducidad' : item.estado === 'vencido' ? 'destacado-rojo' : '';
                tbody.innerHTML += `
                        <tr class="${clase}">
                            <td>${item.fecha_registro}</td>
                            <td>${item.fecha_caducidad}</td>
                            <td>${item.descripcion}</td>
                            <td>${item.cantidad}</td>
                            <td>${item.estado === 'vencido' ? 'Vencido' : 'Pronto a Vencer'}</td>
                            <td>
                                <button class="btn-small" onclick="editarProducto('${item.id_articulo}', '${item.id_detalle}', '${item.codigo_barra}', '${item.descripcion}', '${item.cantidad}', '${item.fecha_caducidad}')">✏️ Editar</button>
                                <button class="btn-small" onclick="eliminarProducto('${item.id_detalle}')">🗑️ Eliminar</button>
                            </td>
                        </tr>
                    `;
            });
        }

        function actualizarContador(cantidad) {
            const contador = document.getElementById('resultsCount');
            contador.textContent = `Mostrando ${cantidad} producto(s)`;
        }

        function exportToExcel() {
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(datosFiltradosGlobal);
            XLSX.utils.book_append_sheet(wb, ws, "Productos Vencimiento");
            XLSX.writeFile(wb, 'productos_vencimiento.xlsx');
        }

        function exportToPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            doc.text("Productos Vencimiento", 14, 16);
            doc.autoTable({ html: '#tablaProductos' });
            doc.save("productos_vencimiento.pdf");
        }

        async function editarProducto(id_articulo, id_detalle, codigo_barra, descripcion, cantidad, fecha_caducidad) {
            // Calcular la fecha mínima permitida (hoy + 59 días)
            const fechaMinima = new Date();
            fechaMinima.setDate(fechaMinima.getDate() + 59);
            const fechaMinimaFormato = fechaMinima.toISOString().split('T')[0]; // Formato YYYY-MM-DD

            const { value: formValues } = await Swal.fire({
                title: 'Editar Producto',
                html:
                    `<input id="swal-codigo" class="swal2-input" value="${codigo_barra}" placeholder="Código de barra">` +
                    `<input id="swal-desc" class="swal2-input" value="${descripcion}" placeholder="Descripción">` +
                    `<input id="swal-cantidad" class="swal2-input" type="number" min="1" value="${cantidad}" placeholder="Cantidad">` +
                    `<input id="swal-fecha" class="swal2-input" type="date" value="${fecha_caducidad}" min="${fechaMinimaFormato}" placeholder="Fecha de caducidad">`,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Guardar cambios',
                preConfirm: () => {
                    return [
                        document.getElementById('swal-codigo').value,
                        document.getElementById('swal-desc').value,
                        document.getElementById('swal-cantidad').value,
                        document.getElementById('swal-fecha').value
                    ]
                }
            });

            if (formValues) {
                const [codigo, desc, cant, fecha] = formValues;

                const formData = new FormData();
                formData.append('id_articulo', id_articulo);
                formData.append('id_detalle', id_detalle);
                formData.append('codigo_barra', codigo);
                formData.append('descripcion', desc);
                formData.append('cantidad', cant);
                formData.append('fecha_caducidad', fecha);

                try {
                    const response = await fetch('php/productos_vencimiento/editar_producto.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();

                    if (result.success) {
                        Swal.fire('Éxito', result.message, 'success');
                        cargarProductos();
                    } else {
                        Swal.fire('Error', result.message, 'error');
                    }
                } catch (error) {
                    Swal.fire('Error', 'No se pudo editar el producto.', 'error');
                }
            }
        }

        function eliminarProducto(id_detalle) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    const response = await fetch(`php/productos_vencimiento/eliminar_producto.php`, {
                        method: 'POST',
                        body: new URLSearchParams({ id: id_detalle })
                    });
                    const result = await response.json();
                    if (result.success) {
                        Swal.fire('Eliminado', result.message, 'success');
                        cargarProductos();
                    } else {
                        Swal.fire('Error', result.message, 'error');
                    }
                }
            });
        }
        cargarProductos();
    </script>
</body>

</html>