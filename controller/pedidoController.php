<?php
// controller/pedidoController.php
session_start();
require_once __DIR__ . '/../model/PedidoModel.php';

if (!isset($_SESSION['rol'])) {
    header("Location: /view/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $accion = $_POST['accion'] ?? '';

    // 1. Crear pedido desde caja (solo Administrador)
    if ($accion === 'crear_pedido' && $_SESSION['rol'] === 'Administrador') {
        $usuarioID = (int)($_SESSION['usuarioID'] ?? 0);
        $mesaOLlevar = trim($_POST['mesa_o_llevar'] ?? 'Mostrador');
        $formaPago = trim($_POST['forma_pago'] ?? 'Efectivo');
        $carrito = json_decode($_POST['carrito_json'] ?? '[]', true);

        if ($usuarioID <= 0 || empty($carrito) || !is_array($carrito)) {
            header("Location: /view/panel_admin.php?error=carrito_vacio");
            exit();
        }

        $total = 0;
        foreach ($carrito as $item) {
            $total += ((float)$item['precioUnitario']) * ((int)$item['cantidad']);
        }

        $folioGenerado = PedidoModel::registrarPedido($usuarioID, $mesaOLlevar, $formaPago, $total, $carrito);

        if ($folioGenerado !== false) {
            header("Location: /view/panel_admin.php?msg=pedido_creado&folio=" . $folioGenerado);
            exit();
        } else {
            header("Location: /view/panel_admin.php?error=error_bd");
            exit();
        }
    }

    // 2. Cambiar estado de una orden (Entregar / Cancelar / Cocina)
    if ($accion === 'cambiar_estado') {
        $pedidoID = (int)($_POST['pedidoID'] ?? 0);
        $nuevoEstado = trim($_POST['nuevo_estado'] ?? '');
        $origen = $_POST['origen'] ?? 'ordenes';

        if ($pedidoID > 0 && $nuevoEstado !== '') {
            PedidoModel::actualizarEstadoPedido($pedidoID, $nuevoEstado);
        }

        if ($origen === 'cocina') {
            header("Location: /view/panel_cocinero.php?msg=actualizado");
        } else {
            header("Location: /view/panel_ordenes.php?msg=actualizado");
        }
        exit();
    }
}

header("Location: /view/panel_admin.php");
exit();
?>