<?php
// model/UsuarioModel.php

// Requerimos la conexión global
require_once __DIR__ . '/../config/conexion.php';

class UsuarioModel {
    
    // Método para buscar a un usuario por su nombre de usuario en la BD
    public static function obtenerPorUsuario($usuario) {
        global $conn; // Viene de conexion.php

        $sql = "SELECT usuarioID, nombre, password_hash, rol FROM Usuarios WHERE usuario = ? AND activo = 1";
        $params = array($usuario);
        
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            return false; // Error en la consulta
        }

        // Devolvemos la fila encontrada como un arreglo asociativo
        return sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    }
}
?>