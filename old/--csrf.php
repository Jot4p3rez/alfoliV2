<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function generarTokenCSRF()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validarTokenCSRF($token)
{
    if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        unset($_SESSION['csrf_token']); // Opcional: eliminar el token después de la validación
        return true;
    }
    return false;
}

?>