<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: index.html');
    exit;
}
$roleDescriptions = [
    'admin' => 'Administrador',
    'editor' => 'Moderador',
    'visualizador' => 'Consultas'
];
$rol = isset($_SESSION['rol']) ? $_SESSION['rol'] : '';
$rolDescription = isset($roleDescriptions[$rol]) ? $roleDescriptions[$rol] : 'Desconocido';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Menú Principal - Alfolí</title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="assets/scripts.js"></script>
    <link rel="shortcut icon" href="assets/logo.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>

<body>
    <div class="container">
        <header class="header">
            <div>
                <img src="assets/logo.png" alt="Logo Alfolí" class="logo">
                <h1>Bienvenido al Sistema de Alfolí</h1>
                <p>Hola, <strong><?php echo $_SESSION['nombre_completo']; ?></strong>, rol,
                    <strong><?php echo $rolDescription; ?></strong>
                </p>
            </div>
            <div class="centered-div">
                <a class="logout" href="php/logout.php">
                    <i class="fa-solid fa-right-from-bracket" style="margin-right: 5px;"></i> Cerrar Sesión</a>
            </div>
        </header>
        <nav class="menu-actions">
            <?php if (isset($_SESSION['rol'])): ?>
                <?php if ($_SESSION['rol'] === 'visualizador'): ?>
                    <a href="dashboard.php"><i class="fa-solid fa-chart-column"></i> Panel Dashboard</a>
                <?php elseif ($_SESSION['rol'] === 'editor'): ?>
                    <a href="detalle.php"><i class="fa-solid fa-table"></i> Ingreso Alfolí</a>
                    <a href="dashboard.php"><i class="fa-solid fa-chart-column"></i> Panel Dashboard</a>
                    <a href="productos_vencimiento.php"><i class="fa-solid fa-explosion"></i> Productos Caducados</a>

                <?php elseif ($_SESSION['rol'] === 'admin'): ?>
                    <a href="detalle.php"><i class="fa-solid fa-table"></i> Ingreso Alfolí</a>
                    <a href="dashboard.php"><i class="fa-solid fa-chart-column"></i> Panel Dashboard</a>
                    <a href="productos_vencimiento.php"><i class="fa-solid fa-explosion"></i> Productos Caducados</a>
                    <!--a href="editar_productos.php"><i class="fa-solid fa-pen-to-square"></i> Edi. Productos</a-->
                    <a href="usuarios.php"><i class="fa-solid fa-user-plus"></i> Usuarios</a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>
        <footer class="footer"> © 2025 Sistema Alfolí - Desarrollado por Aura Solutions Group SpA </footer>
    </div>
</body>

</html>