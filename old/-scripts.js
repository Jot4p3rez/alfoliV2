// ===============================
// 📦 assets/scripts.js - COMPLETO
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

function validarFormularioUsuario() {
    const usuario = document.querySelector('input[name="nombre_usuario"]')?.value.trim();
    const nombreCompleto = document.querySelector('input[name="nombre_completo"]')?.value.trim();
    const email = document.querySelector('input[name="email"]')?.value.trim();
    const password = document.querySelector('input[name="password"]')?.value;
    const confirmPassword = document.querySelector('input[name="confirm_password"]')?.value;

    if (!usuario || !nombreCompleto || !email || !password || !confirmPassword) {
        Swal.fire('Error', 'Todos los campos son obligatorios.', 'error');
        return false;
    }

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        Swal.fire('Error', 'Correo electrónico inválido.', 'error');
        return false;
    }

    if (password !== confirmPassword || password.length < 8) {
        Swal.fire('Error', 'Las contraseñas no coinciden o son muy cortas.', 'error');
        return false;
    }

    if (!/^[a-zA-Z0-9]+$/.test(usuario)) {
        Swal.fire('Error', 'Usuario solo con letras y números.', 'error');
        return false;
    }

    return true;
}

function validarFormularioAlfoli() {
    const hermano = document.querySelector('select[name="hermano"]')?.value;
    const articulo = document.querySelector('select[name="articulo"]')?.value;
    const cantidad = document.querySelector('input[name="cantidad"]')?.value;
    const fechaCaducidad = document.querySelector('input[name="fecha_caducidad"]')?.value;

    if (!hermano || !articulo || !fechaCaducidad || cantidad < 1 || cantidad > 9) {
        Swal.fire('Error', 'Todos los campos son obligatorios.', 'error');
        return false;
    }

    const fechaLimite = new Date();
    fechaLimite.setDate(fechaLimite.getDate() + 59);
    if (new Date(fechaCaducidad) <= fechaLimite) {
        Swal.fire('Error', 'Fecha de caducidad debe ser mayor a 59 días.', 'error');
        return false;
    }
    return true;
}

// ================================
// 3. ELIMINAR PRODUCTOS CADUCADOS
// ================================
document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener('click', function (event) {
        if (event.target.closest('.eliminar-producto')) {
            const button = event.target.closest('.eliminar-producto');
            const id = button.getAttribute('data-id');

            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('id', id);

                    const response = await fetch('php/productos_vencimiento/eliminar_producto.php?id=' + id, { // Corregido
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
// FIN ARCHIVO al 12/04/2025
// ================================