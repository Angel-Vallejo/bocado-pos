<?php
// controller/authController.php
session_start();

require_once __DIR__ . '/../model/UsuarioModel.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $usuario = trim($_POST['usuario'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($usuario) || empty($password)) {
        header("Location: /view/login.php?error=1");
        exit();
    }

    // 1. EL CONTROLADOR LE PIDE AL MODELO LOS DATOS
    $row = UsuarioModel::obtenerPorUsuario($usuario);

    // 2. Si el usuario existe en la BD y la contraseña coincide
    if ($row && password_verify($password, $row['password_hash'])) {
        
        session_regenerate_id(true);
        
        // Guardamos las variables de sesión
        $_SESSION['usuarioID'] = $row['usuarioID'];
        $_SESSION['nombre'] = $row['nombre'];
        $_SESSION['rol'] = $row['rol']; // 'Administrador' o 'Cocinero'

        // 3. Redirección según el rol (RF-01)
        if ($_SESSION['rol'] === 'Administrador') {
            header("Location: /view/panel_admin.php");
            exit();
        } elseif ($_SESSION['rol'] === 'Cocinero') {
            header("Location: /view/panel_cocinero.php");
            exit();
        } else {
            header("Location: /view/login.php?error=1");
            exit();
        }
        
    } else {
        // Credenciales inválidas
        header("Location: /view/login.php?error=1");
        exit();
    }

} else {
    header("Location: /view/login.php");
    exit();
}
?>