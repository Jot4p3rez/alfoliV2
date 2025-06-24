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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio | Sistema de Alfolí</title>
    <link rel="stylesheet" href="assets/shome.css">
    <link rel="shortcut icon" href="assets/logo.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="assets/scripts.js"></script>
</head>

<body>
    <header class="header">
        <div class="header-left">
            <img src="assets/logo.png" alt="Logo Alfolí" class="logo">
            <div>
                <h1>SISTEMA <strong>DE</strong> ALFOLÍ</h1>
                <strong>Usuario: <?php echo strtoupper($_SESSION['usuario']); ?></strong>
            </div>
        </div>

        <div class="header-right">
            <div class="datetime-display">
                <div id="fecha"></div>
                <div id="hora"></div>
            </div>
            <div class="logout">
                <a class="logout-btn" href="php/logout.php">
                    <i class="fa-solid fa-right-from-bracket" style="margin-right: 5px;"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="welcome-card">
            <div class="welcome-text">
                <strong>
                    <h2>¡Bienvenido de vuelta, <?php echo $_SESSION['nombre_completo']; ?>!</h2>
                </strong>Que tengas un bendecido día<br>
                <br>
                <small></small>

            </div>
            <div class="grid-container">
                <?php if ($rol === 'visualizador' || $rol === 'editor' || $rol === 'admin'): ?>
                    <a href="dashboard.php" class="card">
                        <i class="fa-solid fa-chart-line"></i>
                        <span>Panel Dashboard</span>
                    </a>
                <?php endif; ?>
                <?php if ($rol === 'editor' || $rol === 'admin'): ?>
                    <a href="detalle.php" class="card">
                        <i class="fa-solid fa-table"></i>
                        <span>Ingreso Alfolí</span>
                    </a>
                <?php endif; ?>
                <?php if ($rol === 'editor' || $rol === 'admin'): ?>
                    <a href="productos_vencimiento.php" class="card">
                        <!--i class="fa-solid fa-skull-crossbones"></i-->
                        <i class="fa-solid fa-recycle"></i>
                        <span>Productos Caducados</span>
                    </a>
                <?php endif; ?>
                <?php if ($rol === 'visualizador' || $rol === 'editor' || $rol === 'admin'): ?>
                    <a href="alertas.php" class="card">
                        <i class="fa-solid fa-bell"></i>
                        <span>Control de alertas</span>
                    </a>
                <?php endif; ?>
                <?php if ($rol === 'visualizador' || $rol === 'editor' || $rol === 'admin'): ?>
                    <a href="notificaciones.php" class="card">
                        <i class="fa-solid fa-business-time"></i>
                        <span>Notificaciones</span>
                    </a>
                <?php endif; ?>
                <?php if ($rol === 'admin'): ?>
                    <a href="usuarios.php" class="card">
                        <i class="fa-solid fa-users-gear"></i>
                        <span>Gestión de Usuarios</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="footer">
        © 2025 Sistema Alfolí — Desarrollado por Aura Solutions Group
    </footer>


    <script>

        actualizarFechaHora();
        setInterval(actualizarFechaHora, 60000);
    </script>
</body>

</html>