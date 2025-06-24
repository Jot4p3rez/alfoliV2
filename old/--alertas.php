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

require 'php/conexion.php';
require 'php/csrf.php';

$alertas = [];
$dias = 59;

// === 1. INCUMPLIMIENTO INDIVIDUAL ===
$stmt = $pdo->prepare("CALL ObtCumpliAportes()");
$stmt->execute();
$indicadores = $stmt->fetchAll();
$stmt->closeCursor();

foreach ($indicadores as $item) {
    if ($item['estado'] === 'No Cumple') {
        $alertas[] = [
            'tipo' => 'cumplimiento',
            'icono' => 'fa-user-times',
            'color' => '#f44336',
            'titulo' => "Incumplimiento de {$item['hermano']}",
            'detalle' => "No aportó el artículo{$item['articulo']}{$item['mes']}.",
            'accion' => 'Notificar'
        ];
    }
}

// === 2. PRODUCTOS POR VENCER ===
$stmt = $pdo->prepare("CALL ObtArtProxAVencer(:dias)");
$stmt->bindParam(':dias', $dias, PDO::PARAM_INT);
$stmt->execute();
$vencimientos = $stmt->fetchAll();
$stmt->closeCursor();

foreach ($vencimientos as $item) {
    $alertas[] = [
        'tipo' => 'vencimiento',
        'icono' => 'fa-clock',
        'color' => '#ff9800',
        'titulo' => "Producto por vencer",
        'detalle' => "{$item['descripcion']} caduca el {$item['fecha_caducidad']}.",
        'accion' => 'Ver stock'
    ];
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Centro de Alertas</title>
    <link rel="shortcut icon" href="assets/logo.png" type="image/png">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/scripts.js"></script>

    <style>
        .alertas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .alerta-tarjeta {
            background: #fff;
            border-radius: 10px;
            padding: 1em;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            gap: 10px;
            border-left: 6px solid;
            transition: transform 0.2s;
        }

        .alerta-tarjeta:hover {
            transform: translateY(-4px);
        }

        .alerta-header {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: bold;
        }

        .alerta-header i {
            font-size: 1.4em;
        }

        .alerta-detalle {
            font-size: 0.95em;
            color: #444;
        }

        .alerta-accion {
            align-self: flex-end;
            background: #3498db;
            color: white;
            padding: 6px 10px;
            font-size: 0.85em;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .alerta-accion:hover {
            background: #2c80b4;
        }

        .alerta-tarjeta.enviada {
            background-color: #d4edda !important;
            border-color: #2ecc71 !important;
        }

        .alerta-tarjeta.enviada .alerta-header {
            color: #27ae60;
        }

        .alerta-tarjeta.enviada .alerta-accion {
            background-color: #aaa;
            cursor: default;
        }
    </style>
</head>

<body>
    <div class="container">
        <img src="assets/logo.png" class="logo" alt="Logo Alfolí">
        <h2>Centro de Alertas</h2>

        <?php if (empty($alertas)): ?>
            <p>🟢 Todo en orden. No hay alertas activas.</p>
        <?php else: ?>
            <div class="alertas-grid">
                <?php foreach ($alertas as $i => $alerta): ?>
                    <div class="alerta-tarjeta" id="alerta-<?= $i ?>" style="border-color: <?= $alerta['color'] ?>;">
                        <div class="alerta-header">
                            <i class="fas <?= $alerta['icono'] ?>" style="color: <?= $alerta['color'] ?>"></i>
                            <span><?= $alerta['titulo'] ?></span>
                        </div>
                        <div class="alerta-detalle"><?= $alerta['detalle'] ?></div>
                        <button class="alerta-accion"
                            onclick="accionAlerta('alerta-<?= $i ?>', '<?= $alerta['titulo'] ?>')"><?= $alerta['accion'] ?></button>
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

    <script>
        function accionAlerta(id, titulo) {
            Swal.fire({
                title: '¿Deseas continuar?',
                text: `Se realizará la acción recomendada para: ${titulo}`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, realizar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#2ecc71',
                cancelButtonColor: '#e74c3c'
            }).then((result) => {
                if (result.isConfirmed) {
                    const tarjeta = document.getElementById(id);
                    tarjeta.classList.add('enviada');
                    const boton = tarjeta.querySelector('button');
                    boton.innerText = "Realizado";
                    boton.disabled = true;

                    Swal.fire(
                        '¡Listo!',
                        `Se ha gestionado la alerta: ${titulo}`,
                        'success'
                    );
                }
            });
        }
    </script>
</body>

</html>