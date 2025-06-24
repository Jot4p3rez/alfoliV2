// ===============================
// 📦 assets/scripts.js - COMPLETO Y ACTUALIZADO
// ===============================

// ================================
// 1. FUNCIONALIDAD ARTÍCULOS
// ================================
document.addEventListener('DOMContentLoaded', function () {
    const mesArticuloSelect = document.querySelector('select[name="mes_articulo"]');
    const cantidadInput = document.querySelector('input[name="cantidad"]');
    const codigoBarraInput = document.getElementById('codigo_barra');
    const btnEscanear = document.getElementById('btnEscanear');
    const contenedorCamara = document.getElementById('contenedorCamara');
    const camaraElement = document.getElementById('camara');
    const btnDetenerEscaneo = document.getElementById('btnDetenerEscaneo');
    const resultadoEscaneoDiv = document.getElementById('resultadoEscaneo');
    let escanerActivo = false;

    function autocompletarMes() {
        if (!mesArticuloSelect) return;
        const fechaActual = new Date();
        const mesActualNumero = fechaActual.getMonth();
        const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        const mesActualTexto = meses[mesActualNumero];
        mesArticuloSelect.value = mesActualTexto;
    }

    function iniciarQuagga() {
        if (!camaraElement) return;
        Quagga.init({
            inputStream: {
                name: 'Live',
                type: 'LiveStream',
                target: camaraElement
            },
            decoder: {
                readers: ['ean_13_reader', 'code_128_reader', 'upc_a_reader', 'upc_e_reader', 'ean_8_reader']
            }
        }, function (err) {
            if (err) {
                console.error('Error Quagga:', err);
                resultadoEscaneoDiv.innerText = 'Error al iniciar escáner.';
                return;
            }
            Quagga.start();
        });

        Quagga.onDetected(function (result) {
            if (result?.codeResult?.code && escanerActivo) {
                codigoBarraInput.value = result.codeResult.code;
                detenerQuagga();
            }
        });
    }

    function detenerQuagga() {
        if (Quagga) Quagga.stop();
        if (camaraElement?.srcObject) {
            camaraElement.srcObject.getTracks().forEach(track => track.stop());
            camaraElement.srcObject = null;
        }
        contenedorCamara.style.display = 'none';
        escanerActivo = false;
    }

    if (btnEscanear) {
        btnEscanear.addEventListener('click', function () {
            contenedorCamara.style.display = 'block';
            escanerActivo = true;

            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
                .then(stream => {
                    camaraElement.srcObject = stream;
                    camaraElement.play();
                    iniciarQuagga();
                })
                .catch(err => {
                    console.error('Error cámara:', err);
                    resultadoEscaneoDiv.innerText = 'No se pudo acceder a la cámara.';
                });
        });
    }

    if (btnDetenerEscaneo) {
        btnDetenerEscaneo.addEventListener('click', detenerQuagga);
    }

    autocompletarMes();
});

// ================================
// 2. VALIDACIONES FORMULARIOS
// ================================
function validarFormularioArticulo() {
    const codigo = document.getElementById('codigo_barra')?.value;
    const descripcion = document.querySelector('input[name="descripcion"]')?.value;
    const mes = document.querySelector('select[name="mes_articulo"]')?.value;
    const cantidad = document.querySelector('input[name="cantidad"]')?.value;

    if (!codigo || !/^[0-9]+$/.test(codigo) || codigo.length > 13) {
        Swal.fire('Error', 'Código de barra inválido.', 'error');
        return false;
    }
    if (!descripcion || descripcion.length > 150) {
        Swal.fire('Error', 'Descripción inválida.', 'error');
        return false;
    }
    if (!mes) {
        Swal.fire('Error', 'Debe seleccionar un mes.', 'error');
        return false;
    }
    if (!/^[1-9]$/.test(cantidad)) {
        Swal.fire('Error', 'Cantidad debe ser un número del 1 al 9.', 'error');
        return false;
    }
    return true;
}

function validarFormularioHermano() {
    const nombres = document.querySelector('input[name="nombres"]')?.value.trim();
    const apellidos = document.querySelector('input[name="apellidos"]')?.value.trim();

    if (!nombres || !apellidos) {
        Swal.fire('Error', 'Nombre y apellido son requeridos.', 'error');
        return false;
    }
    return true;
}

// ================================
// 3. ELIMINAR PRODUCTOS CADUCADOS (usando id_detalle)
// ================================
document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener('click', function (event) {
        if (event.target.closest('.eliminar-producto')) {
            const button = event.target.closest('.eliminar-producto');
            const id_detalle = button.getAttribute('data-id');

            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    const formData = new URLSearchParams();
                    formData.append('id', id_detalle);

                    const response = await fetch('php/productos_vencimiento/eliminar_producto.php', {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        Swal.fire('Eliminado', data.message, 'success').then(() => window.location.reload());
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                }
            });
        }
    });
});

// ================================
// 4. EDITAR PRODUCTOS CADUCADOS (id_articulo + id_detalle)
// ================================

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
                Swal.fire('Éxito', result.message, 'success').then(() => window.location.reload());
            } else {
                Swal.fire('Error', result.message, 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'No se pudo editar el producto.', 'error');
        }
    }
}

// ================================
// 5. Fecha y hora Login + limpieza de campos
// ================================
function actualizarFechaHora() {
    const ahora = new Date();
    const opcionesFecha = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const fecha = ahora.toLocaleDateString('es-CL', opcionesFecha);
    const hora = ahora.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' });

    document.getElementById('fecha').textContent = fecha.charAt(0).toUpperCase() + fecha.slice(1);
    document.getElementById('hora').textContent = hora;
}
function limpiarFormulario() {
    const form = document.querySelector('form');
    form.reset();
}

// ===============================
// 6. Funcionalidad de la página de usuarios
// ================================


// ================================
//  Formulario de Productos Vencimiento
// ================================
// 


// ================================
// Formulario de detalle
// ================================

async function cargarDatos() {
    document.getElementById('loader').style.display = 'block';

    try {
        const response = await fetch('php/cargar_detalle.php');
        const data = await response.json();
        dataGlobal = data;

        const fechaLimite = new Date();
        fechaLimite.setDate(fechaLimite.getDate() + 59);

        let registrosVencimiento = 0;
        const meses = new Set();
        const hermanos = new Set();

        data.forEach(item => {
            const fechaCaducidad = new Date(item.fecha_caducidad);
            const esProximoAVencer = fechaCaducidad <= fechaLimite;

            if (esProximoAVencer) registrosVencimiento++;

            meses.add(item.mes);
            hermanos.add(item.nombre_hermano);
        });

        // llenarFiltro('filtroMes', meses); // Omitido
        // llenarFiltro('filtroHermano', hermanos); // Omitido

        if (registrosVencimiento > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: `Existen ${registrosVencimiento} registro(s) con fecha de caducidad menor a 60 días.`,
                confirmButtonText: 'Entendido'
            });
        }

        filtrarTabla();
    } catch (error) {
        Swal.fire('Error', 'No se pudieron cargar los datos.', 'error');
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
    const filtroVencimiento = document.getElementById('filtroVencimiento').value;
    // const filtroMes = document.getElementById('filtroMes').value; // Eliminado
    // const filtroHermano = document.getElementById('filtroHermano').value; // Eliminado
    const textoBusqueda = document.getElementById('busquedaGeneral').value.toLowerCase();

    const fechaLimite = new Date();
    fechaLimite.setDate(fechaLimite.getDate() + 59);

    const tbody = document.querySelector('#tablaAlfoli tbody');
    tbody.innerHTML = '';

    datosFiltradosGlobal = dataGlobal.filter(item => {
        const fechaCaducidad = new Date(item.fecha_caducidad);
        const esProximoAVencer = fechaCaducidad <= fechaLimite;

        const cumpleVencimiento = filtroVencimiento === 'todos' || (filtroVencimiento === 'proximos' && esProximoAVencer);
        // const cumpleMes = filtroMes === 'todos' || item.mes === filtroMes; // Eliminado
        // const cumpleHermano = filtroHermano === 'todos' || item.nombre_hermano === filtroHermano; // Eliminado

        const textoItem = `${item.codigo_barra} ${item.descripcion} ${item.nombre_hermano}`.toLowerCase();
        const cumpleBusqueda = textoItem.includes(textoBusqueda);

        return cumpleVencimiento && cumpleBusqueda;
    });

    actualizarContador(datosFiltradosGlobal.length);

    if (datosFiltradosGlobal.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7">No hay registros que coincidan con los filtros seleccionados.</td></tr>';
        return;
    }

    datosFiltradosGlobal.forEach(item => {
        const fechaCaducidad = new Date(item.fecha_caducidad);
        const esProximoAVencer = fechaCaducidad <= fechaLimite;
        const claseFila = esProximoAVencer ? 'alerta-caducidad' : '';

        tbody.innerHTML += `
        <tr class="${claseFila}">
          <td>${item.cantidad}</td>
          <td>${item.fecha_registro}</td>
          <td>${item.mes}</td>
          <td>${item.fecha_caducidad}</td>
          <td>${item.descripcion}</td>
          <td>${item.nombre_hermano}</td>
        </tr>
      `;
    });
}

function actualizarContador(cantidad) {
    const contador = document.getElementById('resultsCount');
    contador.textContent = `Mostrando ${cantidad} registro(s)`;
}

function exportToExcel() {
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.json_to_sheet(datosFiltradosGlobal);
    XLSX.utils.book_append_sheet(wb, ws, "Detalle Alfolí");
    XLSX.writeFile(wb, 'detalle_alfoli_filtrado.xlsx');
}

function exportToPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    doc.text("Detalle Alfolí (Filtrado)", 14, 16);
    doc.autoTable({
        head: [['Cantidad', 'Fecha Registro', 'Mes', 'F. Caducidad', 'Código Artículo', 'Descripción', 'Nombre del Hermano']],
        body: datosFiltradosGlobal.map(item => [
            item.cantidad,
            item.fecha_registro,
            item.mes,
            item.fecha_caducidad,
            item.codigo_barra,
            item.descripcion,
            item.nombre_hermano
        ])
    });
    doc.save("detalle_alfoli_filtrado.pdf");
}


// ================================
// formulario de Panel dashboard
// ================================



// ================================
// FIN ARCHIVO ACTUALIZADO al 17/04/2025
// ================================
