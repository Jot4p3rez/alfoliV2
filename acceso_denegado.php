<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="shortcut icon" href="assets/logo.png" type="image/png">
    <title>Acceso Denegado</title>
    <link rel="stylesheet" href="assets/style.css">

    <style>
        body {
            font-family: sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h1 {
            color: #d32f2f;
            margin-bottom: 20px;
        }

        p {
            color: #777;
            margin-bottom: 15px;
        }

        a {
            color: #3f51b5;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Acceso Denegado</h1>
        <p>Lo sentimos, <strong><?php echo $_SESSION['nombre_completo'] ?? 'Invitado'; ?></strong>, no tienes los
            permisos necesarios para ver esta página.</p>
        <p><a href="javascript:history.back()">Volver a la página anterior</a></p>
        <?php if (isset($_SESSION['usuario'])): ?>
            <p><a href="home.php">Ir al Menú Principal</a></p>
        <?php else: ?>
            <p><a href="index.html">Volver a la página de inicio de sesión</a></p>
        <?php endif; ?>
        
    </div>
</body>

</html>