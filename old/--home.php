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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Home | Sistema Alfolí</title>
    <link rel="stylesheet" href="assets/shome.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

    <header class="header">
        <div class="branding">
            <img src="assets/logo.png" alt="Logo Alfolí" class="logo">

            <div>
                <strong>Sistema de<br>Alfolí</strong><br>
                José Luis Pérez<br>
                <small>Administrador</small>
            </div>
            <div>
                <h1>Sistema de Alfolí</h1>
                <p>Hola, <strong><?php echo $_SESSION['nombre_completo']; ?></strong>, rol,
                    <strong><?php echo $rolDescription; ?></strong>
                </p>
            </div>
        </div>
        <div class="logout">
            <button class="logout-btn">Cerrar Sesión</button>
        </div>
    </header>

    <main class="main-container">
        <div class="welcome-card">
            <h2>¡Bienvenido de vuelta, José!</h2>
            <p>Que tengas un excelente día.</p>
            <div class="button-container">
                <?php if (isset($_SESSION['rol'])): ?>
                    <?php if ($_SESSION['rol'] === 'visualizador'): ?>
                        <a href="dashboard.php"><i class="fas fa-chart-line"></i> Panel Dashboard</a>
                        <a href="#" class="menu-button"><i class="fas fa-table-cells"></i> Ingreso Alfolí</a>
                        <a href="#" class="menu-button"><i class="fas fa-chart-line"></i> Panel Dashboard</a>
                        <a href="#" class="menu-button"><i class="fas fa-user-cog"></i> Gestión de Roles</a>
                        <a href="#" class="menu-button"><i class="fas fa-key"></i> Restablecimientos de Contraseñas</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

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
    </main>

    <footer class="footer">
        © 2025 Sistema Alfolí – Desarrollado por Aura Solutions Group SpA
    </footer>

</body>

</html>