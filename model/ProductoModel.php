<?php
// model/ProductoModel.php
require_once __DIR__ . '/../config/conexion.php';

class ProductoModel {

    // ================= PRODUCTOS =================

    public static function obtenerTodosProductos() {
        global $conn;
        $sql = "SELECT productoID, nombre, categoria, precio, disponible, ingredientes_base 
                FROM Productos 
                ORDER BY categoria ASC, nombre ASC";
        $stmt = sqlsrv_query($conn, $sql);
        if ($stmt === false) return [];

        $productos = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $productos[] = $row;
        }
        return $productos;
    }

    public static function crearProducto($nombre, $categoria, $precio, $ingredientesBase) {
        global $conn;
        $sql = "INSERT INTO Productos (nombre, categoria, precio, disponible, ingredientes_base) 
                VALUES (?, ?, ?, 1, ?)";
        $params = array($nombre, $categoria, $precio, $ingredientesBase);
        $stmt = sqlsrv_query($conn, $sql, $params);
        return $stmt !== false;
    }

    public static function actualizarProducto($productoID, $nombre, $categoria, $precio, $ingredientesBase) {
        global $conn;
        $sql = "UPDATE Productos 
                SET nombre = ?, categoria = ?, precio = ?, ingredientes_base = ? 
                WHERE productoID = ?";
        $params = array($nombre, $categoria, $precio, $ingredientesBase, $productoID);
        $stmt = sqlsrv_query($conn, $sql, $params);
        return $stmt !== false;
    }

    public static function cambiarEstadoProducto($productoID, $nuevoEstado) {
        global $conn;
        $sql = "UPDATE Productos SET disponible = ? WHERE productoID = ?";
        $params = array($nuevoEstado, $productoID);
        $stmt = sqlsrv_query($conn, $sql, $params);
        return $stmt !== false;
    }

    // ================= EXTRAS =================

    public static function obtenerTodosExtras() {
        global $conn;
        $sql = "SELECT extraID, nombre, precio, disponible FROM Extras ORDER BY nombre ASC";
        $stmt = sqlsrv_query($conn, $sql);
        if ($stmt === false) return [];

        $extras = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $extras[] = $row;
        }
        return $extras;
    }

    public static function crearExtra($nombre, $precio) {
        global $conn;
        $sql = "INSERT INTO Extras (nombre, precio, disponible) VALUES (?, ?, 1)";
        $params = array($nombre, $precio);
        $stmt = sqlsrv_query($conn, $sql, $params);
        return $stmt !== false;
    }

    public static function actualizarExtra($extraID, $nombre, $precio) {
        global $conn;
        $sql = "UPDATE Extras SET nombre = ?, precio = ? WHERE extraID = ?";
        $params = array($nombre, $precio, $extraID);
        $stmt = sqlsrv_query($conn, $sql, $params);
        return $stmt !== false;
    }

    public static function cambiarEstadoExtra($extraID, $nuevoEstado) {
        global $conn;
        $sql = "UPDATE Extras SET disponible = ? WHERE extraID = ?";
        $params = array($nuevoEstado, $extraID);
        $stmt = sqlsrv_query($conn, $sql, $params);
        return $stmt !== false;
    }
}
?>