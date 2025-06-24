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
    <title>Agregar Artículo Nuevo</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="shortcut icon" href="assets/logo.png" type="image/png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
    <script src="assets/scripts.js"></script>
</head>

<body>
    <div class="container">
        <img src="assets/logo.png" alt="Logo Alfolí" class="logo">
        <h2>Agregar Artículo</h2>
        <!-- <form action="php/guardar_articulo.php" method="POST" onsubmit="return validarFormularioArticulo();"-->
        <form id="formArticulo" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
            <label for="codigo_barra">Código de Barra (solo números):</label>
            <input type="number" name="codigo_barra" id="codigo_barra" max="9999999999999"
                oninput="if (this.value.length > 13) this.value = this.value.slice(0, 13);" required>
            <!--button type="button" id="btnEscanear">Escanear Código de Barra</button-->
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
                <button type="submit" name="guardar_y_agregar"><i class="fa-solid fa-plus"></i> Guardar y Agregar
                    Otro</button>
                <button type="button" onclick="limpiarFormulario()"><i class="fa-solid fa-xmark"></i> Cancelar</button>

            </div>

        </form>
        <div class="menu-actions" style="margin-top: 20px;">
            <a href="detalle.php" class="btn"><i class="fa-solid fa-arrow-left"></i> Volver a Detalles</a>
        </div>
    </div>
    <script>
        document.getElementById('formArticulo').addEventListener('submit', function (e) {
            e.preventDefault();

            const form = e.target;
            const formData = new FormData(form);

            // Detectar qué botón fue presionado
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
                        confirmButtonText: 'OK',
                    }).then(() => {
                        if (data.success) {
                            if (clickedButton === 'guardar_y_agregar') {
                                form.reset(); // limpia el formulario
                            } else {
                                window.location.href = 'detalle.php';
                            }
                        }
                    });
                })
                .catch(error => {
                    console.error('Error en la solicitud:', error);
                    Swal.fire('Error', 'Ocurrió un problema al guardar.', 'error');
                });
        });

    </script>
</body>

</html>