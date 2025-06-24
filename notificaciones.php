<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: index.html');
    exit;
}

if (!in_array($_SESSION['rol'], ['admin', 'visualizador', 'editor'])) {
    header('Location: acceso_denegado.php');
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/../conexion.php';
require 'php/csrf.php';

$stmt = $pdo->prepare("CALL ObtCumpAportes()");
$stmt->execute();
$indicadores = $stmt->fetchAll();
$stmt->closeCursor();
$pendientes = array_filter($indicadores, fn($i) => $i['estado'] === 'No Cumple');

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>Notificaciones Alfolí</title>
    <link rel="stylesheet" href="assets/style.css" />
    <link rel="shortcut icon" href="assets/logo.png" type="image/png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/scripts.js"></script>
    <style>
        .notificaciones-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 25px;
        }

        .notificacion-tarjeta {
            background: #fff;
            border-left: 6px solid #f39c12;
            border-radius: 10px;
            padding: 1em;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .notificacion-header {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: bold;
            color: #e67e22;
        }

        .notificacion-header i {
            font-size: 1.4em;
        }

        .notificacion-detalle {
            font-size: 0.95em;
            color: #444;
        }

        .notificacion-accion {
            align-self: flex-end;
            background: #2ecc71;
            color: white;
            padding: 6px 10px;
            font-size: 0.85em;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .notificacion-accion:hover {
            background: #27ae60;
        }

        .enviada {
            background-color: #d4edda !important;
            border-color: #2ecc71 !important;
        }

        .enviada .notificacion-header {
            color: #27ae60;
        }

        .enviada .notificacion-accion {
            background: #aaa;
            cursor: default;
        }
    </style>
</head>

<body>
    <div class="container">
        <img src="assets/logo.png" class="logo" alt="Logo">
        <h2>Centro de Notificaciones</h2>

        <form method="POST" action="php/proc_notificaciones.php" style="margin-top: 20px;">
            <button type="submit" class="btn" style="background: #2ecc71; color: white;">
                <i class="fas fa-envelope"></i> Notificar a encargados por correo
            </button>
        </form>

        <?php if (empty($pendientes)): ?>
            <p>✅ No hay hermanos con notificaciones pendientes.</p>
        <?php else: ?>
            <div class="notificaciones-grid">
                <?php foreach ($pendientes as $p): ?>
                    <div class="notificacion-tarjeta" id="noti-<?= md5($p['hermano'] . $p['mes']) ?>">
                        <div class="notificacion-header">
                            <i class="fas fa-bell"></i>
                            <span><?= $p['hermano'] ?>, (<?= $p['mes'] ?>)</span>
                        </div>
                        <div class="notificacion-detalle">
                            No cumplió con el aporte del artículo <b><?= $p['articulo'] ?></b>.
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="menu-actions">
        <a href="home.php" class="btn"><i class="fa-solid fa-house"></i> Volver al Menú Principal</a>
    </div>

    <footer class="footer">
        © 2025 Sistema Alfolí — Desarrollado por Aura Solutions Group
    </footer>

    <?php if (isset($_SESSION['mensaje'])): ?>
        <script>
            Swal.fire({
                icon: '<?php echo $_SESSION["mensaje"]["tipo"]; ?>',
                title: '<?php echo $_SESSION["mensaje"]["tipo"] === "success" ? "¡Éxito!" : "Error"; ?>',
                text: '<?php echo $_SESSION["mensaje"]["texto"]; ?>',
                confirmButtonText: 'Aceptar'
            });
        </script>
        <?php unset($_SESSION['mensaje']); ?>
    <?php endif; ?>
</body>

</html>