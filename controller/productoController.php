<?php
// controller/productoController.php
session_start();
require_once __DIR__ . '/../model/ProductoModel.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
    header("Location: /view/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $accion = $_POST['accion'] ?? '';

    // Función auxiliar para armar la cadena limpia de ingredientes de hamburguesas
    $construirIngredientes = function($categoria) {
        if ($categoria !== 'Hamburguesas') {
            return null;
        }
        $checks = $_POST['ingredientes'] ?? [];
        $especialesRaw = trim($_POST['ingredientes_especiales'] ?? '');
        
        if (!empty($especialesRaw)) {
            $partes = array_filter(array_map('trim', explode(',', $especialesRaw)));
            $checks = array_merge($checks, $partes);
        }
        return !empty($checks) ? implode(', ', $checks) : null;
    };

    switch ($accion) {
        case 'crear_producto':
            $nombre = trim($_POST['nombre'] ?? '');
            $categoria = trim($_POST['categoria'] ?? '');
            $precio = floatval($_POST['precio'] ?? 0);
            $ingredientes = $construirIngredientes($categoria);

            if ($nombre !== '' && $categoria !== '' && $precio >= 0) {
                ProductoModel::crearProducto($nombre, $categoria, $precio, $ingredientes);
                header("Location: /view/panel_productos.php?msg=prod_creado");
                exit();
            }
            break;

        case 'editar_producto':
            $id = intval($_POST['productoID'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? '');
            $categoria = trim($_POST['categoria'] ?? '');
            $precio = floatval($_POST['precio'] ?? 0);
            $ingredientes = $construirIngredientes($categoria);

            if ($id > 0 && $nombre !== '' && $categoria !== '' && $precio >= 0) {
                ProductoModel::actualizarProducto($id, $nombre, $categoria, $precio, $ingredientes);
                header("Location: /view/panel_productos.php?msg=prod_editado");
                exit();
            }
            break;

        case 'toggle_producto':
            $id = intval($_POST['productoID'] ?? 0);
            $nuevoEstado = intval($_POST['nuevo_estado'] ?? 0);
            if ($id > 0) {
                ProductoModel::cambiarEstadoProducto($id, $nuevoEstado);
                header("Location: /view/panel_productos.php?msg=estado_actualizado");
                exit();
            }
            break;

        case 'crear_extra':
            $nombre = trim($_POST['nombre'] ?? '');
            $precio = floatval($_POST['precio'] ?? 0);
            if ($nombre !== '' && $precio >= 0) {
                ProductoModel::crearExtra($nombre, $precio);
                header("Location: /view/panel_productos.php?msg=extra_creado");
                exit();
            }
            break;

        case 'editar_extra':
            $id = intval($_POST['extraID'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? '');
            $precio = floatval($_POST['precio'] ?? 0);
            if ($id > 0 && $nombre !== '' && $precio >= 0) {
                ProductoModel::actualizarExtra($id, $nombre, $precio);
                header("Location: /view/panel_productos.php?msg=extra_editado");
                exit();
            }
            break;

        case 'toggle_extra':
            $id = intval($_POST['extraID'] ?? 0);
            $nuevoEstado = intval($_POST['nuevo_estado'] ?? 0);
            if ($id > 0) {
                ProductoModel::cambiarEstadoExtra($id, $nuevoEstado);
                header("Location: /view/panel_productos.php?msg=estado_actualizado");
                exit();
            }
            break;
    }
}

header("Location: /view/panel_productos.php");
exit();
?>