<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: index.html');
    exit;
}

// Solo acceso admin
if ($_SESSION['rol'] !== 'admin') {
    header('Location: home.php');
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/../conexion.php';
require 'php/csrf.php';


// Verificamos que venga el ID del usuario
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: usuarios.php');
    exit;
}

$id = $_GET['id'];

// Traemos datos del usuario actual
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
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
    <title>Editar Usuario</title>
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

        input,
        select {
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
            <h2>Editar Usuario</h2>
            <p>Hola, <strong><?php echo $_SESSION['nombre_completo']; ?></strong></p>
        </header>

        <form id="formEditarUsuario">
            <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">

            <input type="hidden" name="id" value="<?php echo htmlspecialchars($usuario['id']); ?>">

            <label for="nombre_usuario">Nombre de Usuario:</label>
            <input type="text" name="nombre_usuario" id="nombre_usuario"
                value="<?php echo htmlspecialchars($usuario['nombre_usuario']); ?>" required>

            <label for="nombre_completo">Nombre Completo:</label>
            <input type="text" name="nombre_completo" id="nombre_completo"
                value="<?php echo htmlspecialchars($usuario['nombre_completo']); ?>" required>

            <label for="email">Correo Electrónico:</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($usuario['email']); ?>"
                required>

            <label for="rol">Rol:</label>
            <select name="rol" id="rol" required>
                <option value="admin" <?php if ($usuario['rol'] === 'admin')
                    echo 'selected'; ?>>Administrador</option>
                <option value="editor" <?php if ($usuario['rol'] === 'editor')
                    echo 'selected'; ?>>Moderador</option>
                <option value="visualizador" <?php if ($usuario['rol'] === 'visualizador')
                    echo 'selected'; ?>>
                    Consultas</option>
            </select>

            <button type="submit">Guardar Cambios</button>
        </form>

        <div class="menu-actions">
            <a href="usuarios.php" class="btn"><i class="fa-solid fa-arrow-left"></i> Volver a Usuarios</a>
        </div>

    </div>
    <footer class="footer">
        © 2025 Sistema Alfolí — Desarrollado por Aura Solutions Group
    </footer>
    <script>
        document.getElementById('formEditarUsuario').addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            const response = await fetch('php/usuarios/actualizar_usuario.php', {
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