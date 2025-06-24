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

    <form id="form_alertas" method="POST" action="php/procesar_alertas.php" onsubmit="return validarEnvio();">
        <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">

        <div class="alertas-wrapper">
            <?php
            $tipos_alertas = [
                'stock' => 'Alerta de Stock',
                'vencidos' => 'Productos Vencidos',
                'por_vencer' => 'Productos Por Vencer',
                'incumplimientos' => 'Incumplimientos'
            ];
            foreach ($tipos_alertas as $tipo => $nombre):
            ?>
            <div class="alerta-card">
                <div class="alerta-titulo"><i class="fas fa-bell"></i> <?php echo $nombre; ?></div>
                <label><input type="checkbox" name="activar_<?php echo $tipo; ?>" id="activar_<?php echo $tipo; ?>"> Activar esta alerta</label>
                <input type="email" name="correo_<?php echo $tipo; ?>" placeholder="Correo para <?php echo strtolower($nombre); ?>">
            </div>
            <?php endforeach; ?>
        </div>

        <h3>Programación de Alertas</h3>
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
                <input type="number" name="dia_mes_alerta" id="dia_mes_alerta" min="1" max="31" style="width: 60px;">

                <label for="hora_alerta">Hora:</label>
                <input type="time" name="hora_alerta" id="hora_alerta">

                <label><input type="checkbox" name="programar_todas" id="programar_todas"> Programar todas las alertas</label>
            </div>
        </div>

        <div id="botones_alerta" style="margin-top: 30px; display: none;">
            <button id="btn_enviar_ahora" type="submit" class="btn-enviar" style="background: #3498db; margin-right: 10px;">
                <i class="fas fa-paper-plane"></i> Enviar Alertas Ahora
            </button>
            <button id="btn_guardar_programacion" type="submit" class="btn-enviar" style="background: #2ecc71;">
                <i class="fas fa-save"></i> Guardar Configuración de Alertas
            </button>
        </div>
    </form>
</div>

<div class="menu-actions">
    <a href="home.php" class="btn"><i class="fa-solid fa-house"></i> Volver al Menú Principal</a>
</div>

<footer class="footer">
    © 2025 Sistema Alfolí — Desarrollado por Aura Solutions Group
</footer>

<script>
function validarEnvio() {
    const tipos = ['stock', 'vencidos', 'por_vencer', 'incumplimientos'];
    let algunaSeleccionada = tipos.some(tipo => document.getElementById(`activar_${tipo}`).checked);
    if (!algunaSeleccionada) {
        Swal.fire('Debes seleccionar al menos una alerta para continuar.');
        return false;
    }
    return true;
}

document.addEventListener('DOMContentLoaded', function() {
    const programar = document.getElementById('programar_alertas');
    const opcionesProgramacion = document.getElementById('opciones_programacion');
    const frecuencia = document.getElementById('frecuencia_alerta');
    const diaSemana = document.getElementById('dia_semana_alerta');
    const diaMes = document.getElementById('dia_mes_alerta');
    const labelDiaSemana = document.getElementById('label_dia_semana');
    const labelDiaMes = document.getElementById('label_dia_mes');
    const botones = document.getElementById('botones_alerta');
    const btnEnviarAhora = document.getElementById('btn_enviar_ahora');
    const btnGuardarProgramacion = document.getElementById('btn_guardar_programacion');

    function actualizarBotones() {
        const tipos = ['stock', 'vencidos', 'por_vencer', 'incumplimientos'];
        let algunaSeleccionada = tipos.some(tipo => document.getElementById(`activar_${tipo}`).checked);

        if (!algunaSeleccionada) {
            botones.style.display = 'none';
            return;
        }

        botones.style.display = 'flex';
        if (programar.checked) {
            btnEnviarAhora.style.display = 'none';
            btnGuardarProgramacion.style.display = 'inline-block';
        } else {
            btnEnviarAhora.style.display = 'inline-block';
            btnGuardarProgramacion.style.display = 'none';
        }
    }

    programar.addEventListener('change', () => {
        opcionesProgramacion.style.display = programar.checked ? 'block' : 'none';
        actualizarBotones();
    });

    frecuencia.addEventListener('change', () => {
        const value = frecuencia.value;
        diaSemana.style.display = labelDiaSemana.style.display = (value === 'semanal') ? 'block' : 'none';
        diaMes.style.display = labelDiaMes.style.display = (value === 'mensual') ? 'block' : 'none';
    });

    document.querySelectorAll('input[type="checkbox"]').forEach(chk => {
        chk.addEventListener('change', actualizarBotones);
    });

    actualizarBotones();
});
</script>
</body>
</html>
