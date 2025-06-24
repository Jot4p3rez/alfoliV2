<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/../conexion.php';

$username = $_POST['username'];
$password = $_POST['password'];

$stmt = $pdo->prepare("CALL ValidateUser(?, ?)");
$stmt->bindParam(1, $username, PDO::PARAM_STR);
$stmt->bindParam(2, $password, PDO::PARAM_STR);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt->closeCursor();

if ($user) {
    if (password_verify($password, $user['clave_hash'])) {
        if ($user['activo'] == 1) {
            $_SESSION['usuario'] = $user['nombre_usuario'];
            $_SESSION['nombre_completo'] = $user['nombre_completo'];
            $_SESSION['rol'] = $user['rol'];
            $_SESSION['activo'] = $user['activo'];

            if ($user['cambiar_password']) {
                header('Location: ../cambiar_password.php');
                exit;
            }

            if ($_SESSION['rol'] == 'admin') {
                header('Location: ../home.php');
                exit;
            } elseif ($_SESSION['rol'] == 'editor') {
                header('Location: ../detalle.php');
                exit;
            } elseif ($_SESSION['rol'] == 'visualizador') {
                header('Location: ../dashboard.php');
                exit;
            } else {
                header('Location: ../acceso_denegado.php');// en el caso que creen directo sin el formulario de usuario
                exit;
            }
        } elseif ($user['activo'] == 0) {
            echo "
            <!DOCTYPE html>
            <html lang='es'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no'>
                <title>Cuenta inactiva</title>
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            </head>
            <body>
                <script>
                    Swal.fire({
                        icon: 'warning',
                        title: 'Cuenta inactiva',
                        text: 'Tu cuenta ha sido desactivada. Por favor, contacta al administrador.',
                        confirmButtonText: 'Entendido'
                    }).then(() => {
                        window.location.href = '../index.html';
                    });
                </script>
            </body>
            </html>";
            exit;
        } elseif ($user['activo'] == 9) {
            echo "
            <!DOCTYPE html>
            <html lang='es'>
            <head>
                <meta charset='UTF-8'>
                <title>Cuenta eliminada</title>
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            </head>
            <body>
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Cuenta eliminada',
                        text: 'Tu cuenta ha sido eliminada. No puedes acceder al sistema.',
                        confirmButtonText: 'Entendido'
                    }).then(() => {
                        window.location.href = '../index.html';
                    });
                </script>
            </body>
            </html>";
            exit;
        } else {
            echo "
            <!DOCTYPE html>
            <html lang='es'>
            <head>
                <meta charset='UTF-8'>
                <title>Error de autenticación</title>
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            </head>
            <body>
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de autenticación',
                        text: 'Error interno al verificar el estado de la cuenta.',
                        confirmButtonText: 'Intentar de nuevo'
                    }).then(() => {
                        window.location.href = '../index.html';
                    });
                </script>
            </body>
            </html>";
            exit;
        }
    } else {
        echo "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <title>Error de autenticación</title>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error de acceso',
                    text: 'Usuario o contraseña incorrectos.',
                    confirmButtonText: 'Intentar de nuevo'
                }).then(() => {
                    window.location.href = '../index.html';
                });
            </script>
        </body>
        </html>";
        exit;
    }
} else {
    echo "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <title>Error de autenticación</title>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error de acceso',
                text: 'Usuario o contraseña incorrectos.',
                confirmButtonText: 'Intentar de nuevo'
            }).then(() => {
                window.location.href = '../index.html';
            });
        </script>
    </body>
    </html>";
    exit;
}
?>