<?php
// model/PedidoModel.php
require_once __DIR__ . '/../config/conexion.php';

class PedidoModel {

    // 1. Registrar nuevo pedido desde caja (ya existente)
    public static function registrarPedido($usuarioID, $mesaOLlevar, $formaPago, $total, $carrito) {
        global $conn;

        if (sqlsrv_begin_transaction($conn) === false) {
            return false;
        }

        $sqlPedido = "INSERT INTO Pedidos (usuarioID, mesa_o_llevar, total, forma_pago, estado) 
                      OUTPUT INSERTED.pedidoID
                      VALUES (?, ?, ?, ?, 'Pendiente')";
        $stmtPedido = sqlsrv_query($conn, $sqlPedido, array($usuarioID, $mesaOLlevar, $total, $formaPago));

        if ($stmtPedido === false || !($rowPedido = sqlsrv_fetch_array($stmtPedido, SQLSRV_FETCH_ASSOC))) {
            sqlsrv_rollback($conn);
            return false;
        }

        $pedidoID = (int)$rowPedido['pedidoID'];

        foreach ($carrito as $item) {
            $productoID = (int)$item['productoID'];
            $cantidad = (int)$item['cantidad'];
            $precioUnitario = (float)$item['precioUnitario'];
            $personalizacion = !empty($item['personalizacion']) ? $item['personalizacion'] : null;
            $subtotal = $precioUnitario * $cantidad;

            $sqlDetalle = "INSERT INTO Detalle_Pedido (pedidoID, productoID, cantidad, precio_unitario, personalizacion, subtotal)
                           OUTPUT INSERTED.detalleID
                           VALUES (?, ?, ?, ?, ?, ?)";
            $stmtDetalle = sqlsrv_query($conn, $sqlDetalle, array($pedidoID, $productoID, $cantidad, $precioUnitario, $personalizacion, $subtotal));

            if ($stmtDetalle === false || !($rowDetalle = sqlsrv_fetch_array($stmtDetalle, SQLSRV_FETCH_ASSOC))) {
                sqlsrv_rollback($conn);
                return false;
            }

            $detalleID = (int)$rowDetalle['detalleID'];

            if (!empty($item['extras']) && is_array($item['extras'])) {
                foreach ($item['extras'] as $extra) {
                    $sqlExtra = "INSERT INTO Detalle_Pedido_Extras (detalleID, extraID, precio_cobrado) VALUES (?, ?, ?)";
                    $stmtExtra = sqlsrv_query($conn, $sqlExtra, array($detalleID, (int)$extra['extraID'], (float)$extra['precio']));
                    if ($stmtExtra === false) {
                        sqlsrv_rollback($conn);
                        return false;
                    }
                }
            }
        }

        sqlsrv_commit($conn);
        return $pedidoID;
    }

    // 2. Obtener KPIs del día seleccionado (Ventas, Completadas, Activas en Cocina)
    public static function obtenerResumenDia($fecha) {
        global $conn;
        $sql = "SELECT 
                    ISNULL(SUM(CASE WHEN estado <> 'Cancelado' THEN total ELSE 0 END), 0) AS ventas_dia,
                    COUNT(CASE WHEN estado = 'Entregado' THEN 1 END) AS completadas,
                    COUNT(CASE WHEN estado IN ('Pendiente', 'En preparacion') THEN 1 END) AS activas_cocina
                FROM Pedidos
                WHERE CAST(fecha_creacion AS DATE) = ?";
        $stmt = sqlsrv_query($conn, $sql, array($fecha));
        if ($stmt === false || !($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))) {
            return ['ventas_dia' => 0, 'completadas' => 0, 'activas_cocina' => 0];
        }
        return $row;
    }

    // 3. Listar pedidos filtrados junto con el detalle de sus productos
    public static function obtenerPedidosFiltrados($fecha, $estado = '', $folio = '') {
        global $conn;
        $condiciones = ["CAST(p.fecha_creacion AS DATE) = ?"];
        $params = [$fecha];

        if ($estado !== '') {
            $condiciones[] = "p.estado = ?";
            $params[] = $estado;
        }

        if ($folio !== '') {
            $folioLimpio = (int)str_replace('#', '', $folio);
            $condiciones[] = "p.pedidoID = ?";
            $params[] = $folioLimpio;
        }

        $where = implode(" AND ", $condiciones);
        $sql = "SELECT p.pedidoID, p.mesa_o_llevar, p.total, p.forma_pago, p.estado, p.fecha_creacion,
                       u.nombre AS cajero
                FROM Pedidos p
                INNER JOIN Usuarios u ON p.usuarioID = u.usuarioID
                WHERE $where
                ORDER BY p.pedidoID DESC";

        $stmt = sqlsrv_query($conn, $sql, $params);
        if ($stmt === false) return [];

        $pedidos = [];
        $ids = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $id = (int)$row['pedidoID'];
            $row['items'] = [];
            $pedidos[$id] = $row;
            $ids[] = $id;
        }

        if (empty($ids)) return [];

        // Traer los productos de los pedidos encontrados
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sqlDet = "SELECT dp.pedidoID, dp.cantidad, dp.precio_unitario, dp.personalizacion, dp.subtotal,
                          pr.nombre AS producto
                   FROM Detalle_Pedido dp
                   INNER JOIN Productos pr ON dp.productoID = pr.productoID
                   WHERE dp.pedidoID IN ($placeholders)
                   ORDER BY dp.detalleID ASC";
        $stmtDet = sqlsrv_query($conn, $sqlDet, $ids);

        if ($stmtDet !== false) {
            while ($det = sqlsrv_fetch_array($stmtDet, SQLSRV_FETCH_ASSOC)) {
                $pid = (int)$det['pedidoID'];
                if (isset($pedidos[$pid])) {
                    $pedidos[$pid]['items'][] = $det;
                }
            }
        }

        return array_values($pedidos);
    }

    // 4. Actualizar estado del pedido (Entregado, Cancelado, En preparacion, Listo)
    public static function actualizarEstadoPedido($pedidoID, $nuevoEstado) {
        global $conn;
        $estadosValidos = ['Pendiente', 'En preparacion', 'Listo', 'Entregado', 'Cancelado'];
        if (!in_array($nuevoEstado, $estadosValidos, true)) {
            return false;
        }

        if ($nuevoEstado === 'Listo') {
            $sql = "UPDATE Pedidos SET estado = ?, fecha_listo = GETDATE() WHERE pedidoID = ?";
        } elseif ($nuevoEstado === 'Entregado') {
            $sql = "UPDATE Pedidos SET estado = ?, fecha_entregado = GETDATE() WHERE pedidoID = ?";
        } else {
            $sql = "UPDATE Pedidos SET estado = ? WHERE pedidoID = ?";
        }

        $stmt = sqlsrv_query($conn, $sql, array($nuevoEstado, $pedidoID));
        return $stmt !== false;
    }
// 5. Corte de caja por rango de fechas (excluye pedidos cancelados)
    public static function obtenerCortePorRango($fechaInicio, $fechaFin) {
        global $conn;
        $sql = "SELECT 
                    COUNT(pedidoID) AS total_tickets,
                    ISNULL(SUM(total), 0) AS ingreso_total,
                    COUNT(CASE WHEN forma_pago = 'Efectivo' THEN 1 END) AS cant_efectivo,
                    ISNULL(SUM(CASE WHEN forma_pago = 'Efectivo' THEN total ELSE 0 END), 0) AS total_efectivo,
                    COUNT(CASE WHEN forma_pago = 'Tarjeta' THEN 1 END) AS cant_tarjeta,
                    ISNULL(SUM(CASE WHEN forma_pago = 'Tarjeta' THEN total ELSE 0 END), 0) AS total_tarjeta,
                    COUNT(CASE WHEN forma_pago = 'Transferencia' THEN 1 END) AS cant_transferencia,
                    ISNULL(SUM(CASE WHEN forma_pago = 'Transferencia' THEN total ELSE 0 END), 0) AS total_transferencia
                FROM Pedidos
                WHERE estado <> 'Cancelado'
                  AND CAST(fecha_creacion AS DATE) BETWEEN ? AND ?";

        $stmt = sqlsrv_query($conn, $sql, array($fechaInicio, $fechaFin));
        if ($stmt === false || !($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))) {
            return [
                'total_tickets' => 0, 'ingreso_total' => 0,
                'cant_efectivo' => 0, 'total_efectivo' => 0,
                'cant_tarjeta' => 0, 'total_tarjeta' => 0,
                'cant_transferencia' => 0, 'total_transferencia' => 0
            ];
        }
        return $row;
    }

    // 6. Listado histórico de tickets cobrados junto con su detalle
    public static function obtenerTicketsHistorial($fechaInicio, $fechaFin, $formaPago = '') {
        global $conn;
        $condiciones = [
            "p.estado <> 'Cancelado'",
            "CAST(p.fecha_creacion AS DATE) BETWEEN ? AND ?"
        ];
        $params = [$fechaInicio, $fechaFin];

        if ($formaPago !== '') {
            $condiciones[] = "p.forma_pago = ?";
            $params[] = $formaPago;
        }

        $where = implode(" AND ", $condiciones);
        $sql = "SELECT p.pedidoID, p.mesa_o_llevar, p.total, p.forma_pago, p.fecha_creacion,
                       u.nombre AS cajero
                FROM Pedidos p
                INNER JOIN Usuarios u ON p.usuarioID = u.usuarioID
                WHERE $where
                ORDER BY p.pedidoID DESC";

        $stmt = sqlsrv_query($conn, $sql, $params);
        if ($stmt === false) return [];

        $tickets = [];
        $ids = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $id = (int)$row['pedidoID'];
            $row['items'] = [];
            $tickets[$id] = $row;
            $ids[] = $id;
        }

        if (empty($ids)) return [];

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sqlDet = "SELECT dp.pedidoID, dp.cantidad, dp.personalizacion, dp.subtotal,
                          pr.nombre AS producto
                   FROM Detalle_Pedido dp
                   INNER JOIN Productos pr ON dp.productoID = pr.productoID
                   WHERE dp.pedidoID IN ($placeholders)
                   ORDER BY dp.detalleID ASC";
        $stmtDet = sqlsrv_query($conn, $sqlDet, $ids);

        if ($stmtDet !== false) {
            while ($det = sqlsrv_fetch_array($stmtDet, SQLSRV_FETCH_ASSOC)) {
                $pid = (int)$det['pedidoID'];
                if (isset($tickets[$pid])) {
                    $tickets[$pid]['items'][] = $det;
                }
            }
        }

        return array_values($tickets);
    }

    // 7. Top 5 productos más vendidos en el periodo seleccionado
    public static function obtenerMasVendidos($fechaInicio, $fechaFin) {
        global $conn;
        $sql = "SELECT TOP 5 
                    pr.nombre,
                    SUM(dp.cantidad) AS unidades_vendidas,
                    SUM(dp.subtotal) AS ingreso_producto
                FROM Detalle_Pedido dp
                INNER JOIN Pedidos p ON dp.pedidoID = p.pedidoID
                INNER JOIN Productos pr ON dp.productoID = pr.productoID
                WHERE p.estado <> 'Cancelado'
                  AND CAST(p.fecha_creacion AS DATE) BETWEEN ? AND ?
                GROUP BY pr.nombre
                ORDER BY unidades_vendidas DESC, ingreso_producto DESC";

        $stmt = sqlsrv_query($conn, $sql, array($fechaInicio, $fechaFin));
        if ($stmt === false) return [];

        $top = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $top[] = $row;
        }
        return $top;
    }
// 8. Obtener comandas activas para cocina en orden FIFO (primero en entrar, primero en salir)
    public static function obtenerComandasActivas() {
        global $conn;
        $sql = "SELECT pedidoID, mesa_o_llevar, estado, fecha_creacion
                FROM Pedidos
                WHERE estado IN ('Pendiente', 'En preparacion')
                ORDER BY pedidoID ASC";
        $stmt = sqlsrv_query($conn, $sql);
        if ($stmt === false) return [];

        $comandas = [];
        $ids = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $id = (int)$row['pedidoID'];
            $row['items'] = [];
            $comandas[$id] = $row;
            $ids[] = $id;
        }

        if (empty($ids)) return [];

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sqlDet = "SELECT dp.pedidoID, dp.cantidad, dp.personalizacion, pr.nombre AS producto
                   FROM Detalle_Pedido dp
                   INNER JOIN Productos pr ON dp.productoID = pr.productoID
                   WHERE dp.pedidoID IN ($placeholders)
                   ORDER BY dp.detalleID ASC";
        $stmtDet = sqlsrv_query($conn, $sqlDet, $ids);

        if ($stmtDet !== false) {
            while ($det = sqlsrv_fetch_array($stmtDet, SQLSRV_FETCH_ASSOC)) {
                $pid = (int)$det['pedidoID'];
                if (isset($comandas[$pid])) {
                    $comandas[$pid]['items'][] = $det;
                }
            }
        }

        return array_values($comandas);
    }
}

?>