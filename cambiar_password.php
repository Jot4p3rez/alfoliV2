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

require_once $_SERVER['DOCUMENT_ROOT'] . '/../conexion.php';
require 'php/csrf.php';

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Cambiar Contraseña</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="shortcut icon" href="assets/logo.png" type="image/png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        form {
            max-width: 500px;
            margin: auto;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            background-color: #3f51b5;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background-color: #303f9f;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Es necesario cambiar tu contraseña</h2>

        <form id="formCambiarPassword">
            <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">

            <label for="nueva_clave">Nueva Contraseña:</label>
            <input type="password" name="nueva_clave" id="nueva_clave" required minlength="8">

            <label for="confirmar_clave">Confirmar Contraseña:</label>
            <input type="password" name="confirmar_clave" id="confirmar_clave" required minlength="8">

            <button type="submit">Actualizar Contraseña</button>
        </form>
    </div>
    <footer class="footer">
        © 2025 Sistema Alfolí — Desarrollado por Aura Solutions Group
    </footer>
    <script>
        document.getElementById('formCambiarPassword').addEventListener('submit', async function (e) {
            e.preventDefault();

            const nuevaClave = document.getElementById('nueva_clave').value;
            const confirmarClave = document.getElementById('confirmar_clave').value;

            if (nuevaClave !== confirmarClave) {
                Swal.fire('Error', 'Las contraseñas no coinciden.', 'error');
                return;
            }

            if (nuevaClave.length < 8) {
                Swal.fire('Error', 'La contraseña debe tener al menos 8 caracteres.', 'error');
                return;
            }

            const formData = new FormData(this);

            const response = await fetch('php/usuarios/actualizar_password_usuario.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                Swal.fire('Éxito', result.message, 'success')
                    .then(() => {
                        let redirectURL = 'home.php'; // URL por defecto para admin

                        // Redirigir según el rol almacenado en la sesión
                        if (sessionStorage.getItem('rol') === 'editor') {
                            redirectURL = 'detalle.php';
                        } else if (sessionStorage.getItem('rol') === 'visualizador') {
                            redirectURL = 'dashboard.php';
                        }

                        window.location.href = redirectURL;
                    });
            } else {
                Swal.fire('Error', result.message, 'error');
            }
        });

        // Al cargar la página de cambio de contraseña, guarda el rol en sessionStorage
        window.onload = function () {
            if (sessionStorage.getItem('rol') === null && '<?php echo $_SESSION['rol'] ?? ''; ?>') {
                sessionStorage.setItem('rol', '<?php echo $_SESSION['rol'] ?? ''; ?>');
            }
        };
    </script>
</body>

</html>