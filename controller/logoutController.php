<?php
// controller/logoutController.php
session_start();

// 1. Limpiar todas las variables de sesión
$_SESSION = array();

// 2. Destruir la cookie de sesión si existe (por seguridad adicional)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destruir completamente la sesión en el servidor
session_destroy();

// 4. Redirigir al usuario al login de manera absoluta (Cumpliendo RF-03)
header("Location: /view/login.php");
exit();
?>