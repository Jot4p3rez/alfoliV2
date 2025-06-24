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

require_once $_SERVER['DOCUMENT_ROOT'] . '/../conexion.php';
require 'php/csrf.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: usuarios.php');
    exit;
}

$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT nombre_usuario, nombre_completo FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch();

if (!$usuario) {
    header('Location: usuarios.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Resetear Contraseña</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="shortcut icon" href="assets/logo.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
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

        .container {
            padding: 20px;
        }

        .menu-actions {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <header class="header">
            <img src="assets/logo.png" alt="Logo Alfolí" class="logo">
            <h2>Resetear Contraseña de Usuario</h2>
            <p>Usuario: <strong><?php echo htmlspecialchars($usuario['nombre_usuario']); ?></strong></p>
            <p>Nombre Completo: <strong><?php echo htmlspecialchars($usuario['nombre_completo']); ?></strong></p>
        </header>

        <form id="formResetPassword">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">

            <label for="nueva_clave">Nueva Contraseña:</label>
            <input type="password" name="nueva_clave" id="nueva_clave" required minlength="8">

            <label for="confirmar_clave">Confirmar Contraseña:</label>
            <input type="password" name="confirmar_clave" id="confirmar_clave" required minlength="8">

            <button type="submit">Actualizar Contraseña</button>
        </form>

        <div class="menu-actions">
            <a href="usuarios.php" class="btn"><i class="fa-solid fa-arrow-left"></i> Volver a Usuarios</a>
        </div>
    </div>
    <footer class="footer">
        © 2025 Sistema Alfolí — Desarrollado por Aura Solutions Group
    </footer>

    <script>
        document.getElementById('formResetPassword').addEventListener('submit', async function (e) {
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

            const response = await fetch('php/usuarios/resetear_password.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                Swal.fire('Éxito', result.message, 'success')
                    .then(() => {
                        window.location.href = 'usuarios.php';
                    });
            } else {
                Swal.fire('Error', result.message, 'error');
            }
        });
    </script>
</body>

</html>