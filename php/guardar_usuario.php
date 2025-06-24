<?php
session_start();

require 'csrf.php';

if (!verificarTokenCSRF($_POST['csrf_token'])) {
  echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido.']);
  exit;
}

if (!isset($_SESSION['usuario'])) {
  header('Location: ../index.html');
  exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/../conexion.php';

$nombre_usuario = trim($_POST['nombre_usuario']);
$nombre_completo = trim($_POST['nombre_completo']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

if ($password !== $confirm_password) {
  echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Las contraseñas no coinciden.'
      }).then(() => {
        window.location.href = '../crear_usuario.php';
      });
    </script>";
  exit;
}

// Verificar si el usuario o email ya existen
$sql = "SELECT COUNT(*) FROM usuarios WHERE nombre_usuario = :nombre_usuario OR email = :email";
$stmt = $pdo->prepare($sql);
$stmt->execute([
  'nombre_usuario' => $nombre_usuario,
  'email' => $email
]);
$existe = $stmt->fetchColumn();

if ($existe > 0) {
  echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'El nombre de usuario o correo ya están registrados.'
      }).then(() => {
        window.location.href = '../crear_usuario.php';
      });
    </script>";
  exit;
}

// Crear hash de la contraseña
$clave_hash = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO usuarios (nombre_usuario, clave_hash, nombre_completo, email) VALUES (:nombre_usuario, :clave_hash, :nombre_completo, :email)";
$stmt = $pdo->prepare($sql);

try {
  $stmt->execute([
    'nombre_usuario' => $nombre_usuario,
    'clave_hash' => $clave_hash,
    'nombre_completo' => $nombre_completo,
    'email' => $email
  ]);

  echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
      Swal.fire({
        icon: 'success',
        title: '¡Éxito!',
        text: 'Usuario creado exitosamente.'
      }).then(() => {
        window.location.href = '../home.php';
      });
    </script>";
} catch (Exception $e) {
  echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Error al guardar el usuario: {$e->getMessage()}'
      }).then(() => {
        window.location.href = '../crear_usuario.php';
      });
    </script>";
}
?>