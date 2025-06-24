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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Artículo Nuevo</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="shortcut icon" href="assets/logo.png" type="image/png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script src="assets/scripts.js"></script>
</head>

<body>
    <div class="container">
        <img src="assets/logo.png" alt="Logo Alfolí" class="logo">
        <h2>Agregar Artículo</h2>

        <form id="formArticulo" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">

            <label for="codigo_barra">Código de Barra / QR:</label>
            <input type="number" name="codigo_barra" id="codigo_barra" max="9999999999999"
                oninput="if (this.value.length > 13) this.value = this.value.slice(0, 13);" required>

            <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                <button type="button" id="btnEscanearQR"><i class="fa-solid fa-qrcode"></i> Escanear QR</button>
                <!-- Si quieres también el de código de barras:
                <button type="button" id="btnEscanear"><i class="fa-solid fa-barcode"></i> Escanear Código</button>
                -->
            </div>

            <div id="qr-reader" style="width: 100%; max-width: 400px; margin-bottom: 10px; display: none;"></div>

            <div id="contenedorCamara" style="display:none; margin-top: 10px;">
                <video id="camara" width="300" height="200"></video>
                <button type="button" id="btnDetenerEscaneo">Detener Escaneo</button>
            </div>
            <div id="resultadoEscaneo" style="margin-top: 10px;"></div>

            <label for="descripcion">Descripción del Artículo (máx. 150 caracteres):</label>
            <input type="text" name="descripcion" maxlength="150" required>

            <label for="cantidad">Cantidad del mes (solo números):</label>
            <input type="number" name="cantidad" min="1" max="9" required>

            <label for="mes_articulo">Mes del Artículo:</label>
            <select name="mes_articulo" required>
                <option value="">Seleccione</option>
                <?php
                $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                foreach ($meses as $mes) {
                    echo "<option value='$mes'>$mes</option>";
                }
                ?>
            </select>

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

    <footer class="footer">
        © 2025 Sistema Alfolí - Desarrollado por Aura Solutions Group SpA
    </footer>

    <script>
        // ✅ Lector QR con html5-qrcode
        const btnEscanearQR = document.getElementById('btnEscanearQR');
        const qrReader = document.getElementById('qr-reader');
        let qrScannerActivo = false;
        let html5QrCode;

        btnEscanearQR.addEventListener('click', () => {
            if (qrScannerActivo && html5QrCode) {
                html5QrCode.stop().then(() => {
                    qrReader.style.display = 'none';
                    qrScannerActivo = false;
                });
                return;
            }

            qrReader.style.display = 'block';
            html5QrCode = new Html5Qrcode("qr-reader");

            html5QrCode.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: 250 },
                (decodedText) => {
                    document.getElementById('codigo_barra').value = decodedText;
                    html5QrCode.stop().then(() => {
                        qrReader.style.display = 'none';
                        qrScannerActivo = false;
                    });
                },
                (errorMessage) => {
                    // console.log("QR Scan error:", errorMessage);
                }
            ).then(() => {
                qrScannerActivo = true;
            }).catch(err => {
                console.error("Error al iniciar escáner QR:", err);
                Swal.fire('Error', 'No se pudo acceder a la cámara.', 'error');
            });
        });

        // ✅ Envío del formulario con fetch
        document.getElementById('formArticulo').addEventListener('submit', function (e) {
            e.preventDefault();

            const form = e.target;
            const formData = new FormData(form);
            const clickedButton = document.activeElement.name;
            formData.append(clickedButton, true);

            fetch('php/guardar_articulo.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    Swal.fire({
                        icon: data.success ? 'success' : 'error',
                        title: data.success ? 'Éxito' : 'Error',
                        text: data.message,
                    }).then(() => {
                        if (data.success) {
                            if (clickedButton === 'guardar_y_agregar') {
                                form.reset();
                            } else {
                                window.location.href = 'detalle.php';
                            }
                        }
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Ocurrió un problema al guardar.', 'error');
                });
        });

        function limpiarFormulario() {
            document.getElementById('formArticulo').reset();
        }
    </script>
</body>

</html>
