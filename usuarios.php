<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: index.html');
    exit;
}

if ($_SESSION['rol'] !== 'admin') {
    header('Location: home.php');

    exit;
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Administración de Usuarios</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="shortcut icon" href="assets/logo.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/scripts.js"></script>
    <style>
        /* Estilos para el contenedor de filtros */
        .filters {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filters select,
        .filters input {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1em;
        }

        .filters input {
            flex-grow: 1;
            min-width: 200px;
        }

        .filters select {
            min-width: 180px;
        }

        .filters input::placeholder {
            color: #aaa;
            font-style: italic;
        }

        /* Estilos para dispositivos móviles */
        @media (max-width: 768px) {
            .filters {
                flex-direction: column;
                align-items: stretch;
            }

            .filters select,
            .filters input {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <header class="header">
            <img src="assets/logo.png" alt="Logo Alfolí" class="logo">
            <h2>Administración de Usuarios</h2>
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
                <option value="todos">Estado: Todos</option>
                <option value="activo">🟢 Activo</option>
                <option value="inactivo">🔴 Inactivo</option>
            </select>

            <select id="filtroRol" onchange="filtrarTabla()">
                <option value="todos">Rol: Todos</option>
                <!-- Añadir opciones de roles aquí -->
            </select>

            <input type="text" id="busquedaGeneral" placeholder="🔍 Buscar usuario..." oninput="filtrarTabla()">
        </div>

        <div class="menu-actions" style="margin-top: 20px;">
            <a href="crear_usuario.php" class="btn"><i class="fa-solid fa-user-plus"></i>Agregar Nuevo Usuario</a>
        </div>

        <div class="results-count" id="resultsCount">Mostrando usuarios...</div>

        <div class="loader" id="loader">⏳ Cargando usuarios, por favor espera...</div>

        <div class="table-responsive">
            <table id="tablaUsuarios">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Nombre Completo</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Se llenará dinámicamente -->
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
        let dataUsuarios = [];
        let datosFiltradosGlobal = [];
        async function cargarUsuarios() {
            document.getElementById('loader').style.display = 'block';

            try {
                const response = await fetch('php/usuarios/cargar_usuarios.php');
                const data = await response.json();
                dataUsuarios = data;

                const roles = new Set();

                data.forEach(item => roles.add(item.rol));

                llenarFiltro('filtroRol', roles);
                filtrarTabla();
            } catch (error) {
                Swal.fire('Error', 'No se pudieron cargar los usuarios.', 'error');
            } finally {
                document.getElementById('loader').style.display = 'none';
            }
        }

        function llenarFiltro(id, valores) {
            const filtro = document.getElementById(id);
            filtro.innerHTML = `<option value="todos">Todos</option>`;
            valores.forEach(valor => {
                filtro.innerHTML += `<option value="${valor}">${valor}</option>`;
            });
        }

        function filtrarTabla() {
            const filtroEstado = document.getElementById('filtroEstado').value;
            const filtroRol = document.getElementById('filtroRol').value;
            const textoBusqueda = document.getElementById('busquedaGeneral').value.toLowerCase();

            const tbody = document.querySelector('#tablaUsuarios tbody');
            tbody.innerHTML = '';

            datosFiltradosGlobal = dataUsuarios.filter(item => {
                const cumpleEstado = filtroEstado === 'todos' ||
                    (filtroEstado === 'activo' && item.activo == '1') ||
                    (filtroEstado === 'inactivo' && item.activo == '0');

                const cumpleRol = filtroRol === 'todos' || item.rol === filtroRol;

                const textoItem = `${item.nombre_usuario} ${item.nombre_completo} ${item.email}`.toLowerCase();
                const cumpleBusqueda = textoItem.includes(textoBusqueda);

                return cumpleEstado && cumpleRol && cumpleBusqueda;
            });

            actualizarContador(datosFiltradosGlobal.length);

            if (datosFiltradosGlobal.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6">No hay usuarios que coincidan con los filtros.</td></tr>';
                return;
            }

            datosFiltradosGlobal.forEach(item => {
                const estado = item.activo == '1' ? '🟢 Activo' : '🔴 Inactivo';

                tbody.innerHTML += `
    <tr>
        <td>${item.nombre_usuario}</td>
        <td>${item.nombre_completo}</td>
        <td>${item.email}</td>
        <td>${item.rol}</td>
        <td>${estado}</td>

<td>
    <div class="button-container">
        <button class="btn-small">
            <a href="editar_usuario.php?id=${item.id}">
                <i class="fa-solid fa-user-pen"></i> Editar
            </a>
        </button>
        <button class="btn-small" onclick="confirmarReset(${item.id})">
            <i class="fa-solid fa-key"></i> Resetear
        </button>
        <button class="btn-small" onclick="cambiarEstado(${item.id}, '${item.activo}')">
            <i class="fa-solid ${item.activo == '1' ? 'fa-toggle-off' : 'fa-toggle-on'}"></i>
            ${item.activo == '1' ? ' Desactivar' : ' Activar'}
        </button>
    </div>
</td>

    </tr>
`;
            });
        }

        function actualizarContador(cantidad) {
            const contador = document.getElementById('resultsCount');
            contador.textContent = `Mostrando ${cantidad} usuario(s)`;
        }

        function exportToExcel() {
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(datosFiltradosGlobal);
            XLSX.utils.book_append_sheet(wb, ws, "Usuarios");
            XLSX.writeFile(wb, 'usuarios_filtrados.xlsx');
        }

        function exportToPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            doc.text("Usuarios (Filtrado)", 14, 16);
            doc.autoTable({
                head: [['Usuario', 'Nombre Completo', 'Email', 'Rol', 'Estado']],
                body: datosFiltradosGlobal.map(item => [
                    item.nombre_usuario,
                    item.nombre_completo,
                    item.email,
                    item.rol,
                    item.activo === '1' ? 'Activo' : 'Inactivo'
                ])
            });
            doc.save("usuarios_filtrados.pdf");
        }

        async function cambiarEstado(id, estadoActual) {
            const accion = estadoActual === '1' ? 'desactivar' : 'activar';
            const confirmacion = await Swal.fire({
                title: `¿Seguro que quieres ${accion} este usuario?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, confirmar',
                cancelButtonText: 'Cancelar'
            });

            if (confirmacion.isConfirmed) {
                const response = await fetch(`php/usuarios/cambiar_estado.php?id=${id}&estado=${estadoActual}`);
                const result = await response.json();

                if (result.success) {
                    Swal.fire('Hecho', result.message, 'success');
                    cargarUsuarios();
                } else {
                    Swal.fire('Error', result.message, 'error');
                }
            }
        }

        function confirmarReset(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Se enviará a la página para restablecer la contraseña.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `resetear_password.php?id=${id}`;
                }
            });
        }


        cargarUsuarios();
    </script>
</body>

</html>