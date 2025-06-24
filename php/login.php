<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/../conexion.php';

function cleanInput($input)
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

$username = isset($_POST['username']) ? cleanInput($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : ''; // No sanitizamos password directamente para evitar afectar símbolos

if (empty($username) || empty($password)) {
    mostrarError('Datos incompletos', 'Por favor, completa ambos campos.');
    exit;
}

try {
    $stmt = $pdo->prepare("CALL ValidateUser(:username, :password)");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':password', $password, PDO::PARAM_STR);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    if ($user && isset($user['clave_hash']) && password_verify($password, $user['clave_hash'])) {

        switch ($user['activo']) {
            case 1:
                $_SESSION['usuario'] = $user['nombre_usuario'];
                $_SESSION['nombre_completo'] = $user['nombre_completo'];
                $_SESSION['rol'] = $user['rol'];
                $_SESSION['activo'] = $user['activo'];

                if ($user['cambiar_password']) {
                    header('Location: ../cambiar_password.php');
                    exit;
                }

                switch ($_SESSION['rol']) {
                    case 'admin':
                        header('Location: ../home.php');
                        break;
                    case 'editor':
                        header('Location: ../detalle.php');
                        break;
                    case 'visualizador':
                        header('Location: ../dashboard.php');
                        break;
                    default:
                        header('Location: ../acceso_denegado.php');
                        break;
                }
                exit;

            case 0:
                mostrarError('Cuenta inactiva', 'Tu cuenta ha sido desactivada. Por favor, contacta al administrador.', 'warning');
                break;

            case 9:
                mostrarError('Cuenta eliminada', 'Tu cuenta ha sido eliminada. No puedes acceder al sistema.', 'error');
                break;

            default:
                mostrarError('Error de autenticación', 'Error interno al verificar el estado de la cuenta.');
                break;
        }

    } else {
        mostrarError('Error de acceso', 'Usuario o contraseña incorrectos.', 'error');
    }

} catch (PDOException $e) {
    mostrarError('Error interno', 'Error al conectar o procesar la solicitud.');
    // En producción, no mostrar $e->getMessage() directamente
}

function mostrarError($titulo, $mensaje, $icono = 'error')
{
    echo "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <title>$titulo</title>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
        <script>
            Swal.fire({
                icon: '$icono',
                title: '$titulo',
                text: '$mensaje',
                confirmButtonText: 'Intentar de nuevo'
            }).then(() => {
                window.location.href = '../index.html';
            });
        </script>
    </body>
    </html>";
}
?>