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

require 'php/csrf.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Alertas</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="shortcut icon" href="assets/logo.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .alertas-wrapper {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 25px;
        }

        .alerta-card {
            background: #f9f9f9;
            border-radius: 12px;
            padding: 1em;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            gap: 12px;
            border-left: 6px solid #3498db;
            position: relative;
            transition: all 0.3s ease;
        }

        .alerta-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
        }

        .alerta-titulo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: bold;
            color: #2c3e50;
        }

        .alerta-titulo i {
            font-size: 1.3em;
            color: #3498db;
        }

        .alerta-card input[type="email"] {
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #ccc;
            width: 100%;
        }

        .alerta-card input[type="checkbox"] {
            transform: scale(1.2);
            margin-right: 8px;
        }

        .alerta-card label {
            font-size: 0.95em;
            color: #333;
        }

        .btn-enviar {
            margin-top: 30px;
            background: #2ecc71;
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 1em;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn-enviar:hover {
            background: #27ae60;
        }
    </style>
</head>

<body>
    <div class="container">
        <img src="assets/logo.png" class="logo" alt="Logo Alfolí">
        <h2>Configura tus Alertas</h2>
        <p>Selecciona las alertas que deseas activar e ingresa el correo destinatario.</p>

        <form method="POST" action="php/procesar_alertas.php" onsubmit="return validarCorreos();">
            <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">

            <div class="alertas-wrapper">
                <div class="alerta-card">
                    <div class="alerta-titulo"><i class="fas fa-box"></i> Alerta de Stock</div>
                    <label><input type="checkbox" name="activar_stock" id="activar_stock"> Activar esta alerta</label>
                    <input type="email" name="correo_stock" placeholder="Correo para notificación de stock">
                </div>

                <div class="alerta-card">
                    <div class="alerta-titulo"><i class="fas fa-exclamation-triangle"></i> Productos Vencidos</div>
                    <label><input type="checkbox" name="activar_vencidos" id="activar_vencidos"> Activar esta
                        alerta</label>
                    <input type="email" name="correo_vencidos" placeholder="Correo para productos vencidos">
                </div>

                <div class="alerta-card">
                    <div class="alerta-titulo"><i class="fas fa-clock"></i> Por Vencer</div>
                    <label><input type="checkbox" name="activar_por_vencer" id="activar_por_vencer"> Activar esta
                        alerta</label>
                    <input type="email" name="correo_por_vencer" placeholder="Correo para productos por vencer">
                </div>

                <div class="alerta-card">
                    <div class="alerta-titulo"><i class="fas fa-user-times"></i> Incumplimientos</div>
                    <label><input type="checkbox" name="activar_incumplimientos" id="activar_incumplimientos"> Activar
                        esta alerta</label>
                    <input type="email" name="correo_incumplimientos"
                        placeholder="Correo para alertas de participantes">
                </div>
            </div>

            <button type="submit" class="btn-enviar"><i class="fas fa-paper-plane"></i> Enviar Alertas
                Seleccionadas</button>
        </form>
    </div>
    <div class="menu-actions" style="margin-top: 30px;">
        <a href="home.php" class="btn"><i class="fa-solid fa-house"></i> Volver al Menú Principal</a>
    </div>
    <footer class="footer">
        © 2025 Sistema Alfolí — Desarrollado por Aura Solutions Group
    </footer>

    <?php if (isset($_SESSION['mensaje'])): ?>
        <script>
            Swal.fire({
                icon: '<?php echo $_SESSION["mensaje"]["tipo"]; ?>',
                title: '<?php echo $_SESSION["mensaje"]["tipo"] === "success" ? "Éxito" : ($_SESSION["mensaje"]["tipo"] === "info" ? "Info" : "Error"); ?>',
                html: '<?php echo $_SESSION["mensaje"]["texto"]; ?>',
                confirmButtonText: 'Aceptar'
            });
        </script>
        <?php unset($_SESSION['mensaje']); ?>
    <?php endif; ?>

    <script>
        function validarCorreos() {
            const tipos = ['stock', 'vencidos', 'por_vencer', 'incumplimientos'];
            let errores = [];

            tipos.forEach((tipo) => {
                const chk = document.getElementById(`activar_${tipo}`);
                const correo = document.querySelector(`[name="correo_${tipo}"]`);

                if (chk && chk.checked) {
                    const email = correo.value.trim();
                    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!email || !regex.test(email)) {
                        errores.push(`✖ Correo inválido para alerta de ${tipo.replace('_', ' ')}`);
                    }
                }
            });

            if (errores.length > 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Errores encontrados',
                    html: errores.join('<br>'),
                    confirmButtonText: 'Corregir'
                });
                return false;
            }

            return true;
        }
    </script>
</body>

</html>