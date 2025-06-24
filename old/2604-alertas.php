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
        <p>Selecciona las alertas que deseas activar e ingresa el correo destinatario. También puedes programar el envío de las alertas.</p>

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

            <h3>Programación de Alertas</h3>
            <p>Selecciona la frecuencia y la hora para recibir las alertas.</p>

            <div class="alerta-card">
                <div class="alerta-titulo"><i class="fas fa-calendar-alt"></i> Programar Alertas</div>
                <label>
                    <input type="checkbox" name="programar_alertas" id="programar_alertas"> Activar programación
                </label>

                <div id="opciones_programacion" style="display: none; margin-top: 15px;">
                    <label for="frecuencia_alerta">Frecuencia:</label>
                    <select name="frecuencia_alerta" id="frecuencia_alerta">
                        <option value="diaria">Diaria</option>
                        <option value="semanal">Semanal</option>
                        <option value="mensual">Mensual</option>
                    </select>

                    <label for="dia_semana_alerta" id="label_dia_semana" style="display: none;">Día de la Semana:</label>
                    <select name="dia_semana_alerta" id="dia_semana_alerta" style="display: none;">
                        <option value="Lunes">Lunes</option>
                        <option value="Martes">Martes</option>
                        <option value="Miércoles">Miércoles</option>
                        <option value="Jueves">Jueves</option>
                        <option value="Viernes">Viernes</option>
                        <option value="Sábado">Sábado</option>
                        <option value="Domingo">Domingo</option>
                    </select>

                    <label for="dia_mes_alerta" id="label_dia_mes" style="display: none;">Día del Mes:</label>
                    <input type="number" name="dia_mes_alerta" id="dia_mes_alerta" min="1" max="31" style="width: 60px; padding: 8px; border-radius: 6px; border: 1px solid #ccc;">

                    <label for="hora_alerta">Hora:</label>
                    <input type="time" name="hora_alerta" id="hora_alerta" style="padding: 8px; border-radius: 6px; border: 1px solid #ccc;">

                    <label><input type="checkbox" name="programar_todas" id="programar_todas"> Programar todas las alertas</label>
                    <p style="font-size: 0.9em; color: #777;">Si no selecciona "Programar todas", las alertas individuales se enviarán solo si están activadas y no se programarán.</p>
                </div>
            </div>

            <button type="submit" class="btn-enviar"><i class="fas fa-paper-plane"></i> Guardar Configuración de Alertas</button>
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

        document.addEventListener('DOMContentLoaded', function() {
            const programarCheckbox = document.getElementById('programar_alertas');
            const opcionesProgramacion = document.getElementById('opciones_programacion');
            const frecuenciaSelect = document.getElementById('frecuencia_alerta');
            const diaSemanaSelect = document.getElementById('dia_semana_alerta');
            const diaMesInput = document.getElementById('dia_mes_alerta');
            const labelDiaSemana = document.getElementById('label_dia_semana');
            const labelDiaMes = document.getElementById('label_dia_mes');

            programarCheckbox.addEventListener('change', function() {
                opcionesProgramacion.style.display = this.checked ? 'block' : 'none';
            });

            frecuenciaSelect.addEventListener('change', function() {
                diaSemanaSelect.style.display = this.value === 'semanal' ? 'block' : 'none';
                labelDiaSemana.style.display = this.value === 'semanal' ? 'block' : 'none';
                diaMesInput.style.display = this.value === 'mensual' ? 'inline-block' : 'none';
                labelDiaMes.style.display = this.value === 'mensual' ? 'inline-block' : 'none';
            });
        });
    </script>
</body>

</html>