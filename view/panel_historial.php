<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
    header("Location: /view/login.php");
    exit();
}

require_once __DIR__ . '/../model/PedidoModel.php';

// Filtros por defecto: el día de hoy
$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-d');
$fechaFin    = $_GET['fecha_fin'] ?? date('Y-m-d');
$formaPago   = $_GET['forma_pago'] ?? '';

$corte       = PedidoModel::obtenerCortePorRango($fechaInicio, $fechaFin);
$tickets     = PedidoModel::obtenerTicketsHistorial($fechaInicio, $fechaFin, $formaPago);
$masVendidos = PedidoModel::obtenerMasVendidos($fechaInicio, $fechaFin);

$iconosPago = [
    'Efectivo'      => '💵 Efectivo',
    'Tarjeta'       => '💳 Tarjeta',
    'Transferencia' => '📲 Transferencia'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Ventas - Bocado POS</title>
    <link href="/css/bootstrap.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="/css/bocado.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="/img/favicon.png?v=2">
</head>
<body>

    <div class="container-fluid vh-100 overflow-hidden">
        <div class="row h-100">
            
            <!-- BARRA LATERAL -->
            <?php include __DIR__ . '/sidebar.php'; ?>

            <!-- CONTENIDO PRINCIPAL -->
            <div class="col-11 d-flex flex-column p-4 h-100 overflow-auto">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="font-weight-bold mb-0">Historial de Ventas y Corte</h4>
                        <small class="text-muted">Resumen financiero por rango de fechas y método de pago</small>
                    </div>
                    <div>
                        <span class="badge badge-dark p-2">
                            <i class="bi bi-person-fill mr-1"></i>
                            <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Administrador'); ?>
                        </span>
                    </div>
                </div>

                <!-- FILTROS POR FECHA Y MÉTODO DE PAGO -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body py-3">
                        <form method="GET" action="/view/panel_historial.php" class="row align-items-end">
                            <div class="col-md-3 mb-2 mb-md-0">
                                <label class="small font-weight-bold text-muted mb-1">Fecha Inicio</label>
                                <input type="date" name="fecha_inicio" class="form-control" value="<?php echo htmlspecialchars($fechaInicio); ?>">
                            </div>
                            <div class="col-md-3 mb-2 mb-md-0">
                                <label class="small font-weight-bold text-muted mb-1">Fecha Fin</label>
                                <input type="date" name="fecha_fin" class="form-control" value="<?php echo htmlspecialchars($fechaFin); ?>">
                            </div>
                            <div class="col-md-3 mb-2 mb-md-0">
                                <label class="small font-weight-bold text-muted mb-1">Método de Pago</label>
                                <select name="forma_pago" class="form-control">
                                    <option value="">Todos</option>
                                    <option value="Efectivo" <?php echo $formaPago === 'Efectivo' ? 'selected' : ''; ?>>Efectivo</option>
                                    <option value="Tarjeta" <?php echo $formaPago === 'Tarjeta' ? 'selected' : ''; ?>>Tarjeta</option>
                                    <option value="Transferencia" <?php echo $formaPago === 'Transferencia' ? 'selected' : ''; ?>>Transferencia</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary btn-block font-weight-bold">
                                    🔍 Consultar Corte
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- TARJETAS DE DESGLOSE FINANCIERO -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3 mb-md-0">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <small class="text-muted text-uppercase font-weight-bold">Ingreso Total</small>
                                <h4 class="font-weight-bold text-success mb-0 mt-1">
                                    $<?php echo number_format((float)$corte['ingreso_total'], 2); ?>
                                </h4>
                                <small class="text-muted"><?php echo (int)$corte['total_tickets']; ?> tickets cerrados</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3 mb-md-0">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <small class="text-muted text-uppercase font-weight-bold">💵 Efectivo</small>
                                <h4 class="font-weight-bold text-dark mb-0 mt-1">
                                    $<?php echo number_format((float)$corte['total_efectivo'], 2); ?>
                                </h4>
                                <small class="text-muted"><?php echo (int)$corte['cant_efectivo']; ?> pagos</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3 mb-md-0">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <small class="text-muted text-uppercase font-weight-bold">💳 Tarjeta</small>
                                <h4 class="font-weight-bold text-dark mb-0 mt-1">
                                    $<?php echo number_format((float)$corte['total_tarjeta'], 2); ?>
                                </h4>
                                <small class="text-muted"><?php echo (int)$corte['cant_tarjeta']; ?> pagos</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <small class="text-muted text-uppercase font-weight-bold">📲 Transferencia</small>
                                <h4 class="font-weight-bold text-dark mb-0 mt-1">
                                    $<?php echo number_format((float)$corte['total_transferencia'], 2); ?>
                                </h4>
                                <small class="text-muted"><?php echo (int)$corte['cant_transferencia']; ?> pagos</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABLA DE VENTAS + PRODUCTOS ESTRELLA -->
                <div class="row flex-grow-1 pb-3">
                    
                    <!-- Columna Izquierda: Registro Histórico -->
                    <div class="col-lg-8 mb-4 mb-lg-0">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header font-weight-bold">
                                Registro de Tickets Cerrados
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Folio</th>
                                                <th>Fecha y Hora</th>
                                                <th>Cajero</th>
                                                <th>Pago</th>
                                                <th>Total</th>
                                                <th class="text-right">Ticket</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($tickets)): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-5">
                                                        No hay ventas registradas en el rango de fechas seleccionado.
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($tickets as $tk): 
                                                    $fechaFormateada = ($tk['fecha_creacion'] instanceof DateTime)
                                                        ? $tk['fecha_creacion']->format('d/m/Y h:i A')
                                                        : date('d/m/Y h:i A', strtotime($tk['fecha_creacion']));

                                                    $jsonOrden = htmlspecialchars(json_encode([
                                                        'folio'  => (int)$tk['pedidoID'],
                                                        'hora'   => $fechaFormateada,
                                                        'mesa'   => $tk['mesa_o_llevar'],
                                                        'cajero' => $tk['cajero'],
                                                        'pago'   => $tk['forma_pago'],
                                                        'total'  => number_format((float)$tk['total'], 2),
                                                        'items'  => $tk['items']
                                                    ]), ENT_QUOTES, 'UTF-8');
                                                ?>
                                                    <tr>
                                                        <td class="font-weight-bold">#<?php echo (int)$tk['pedidoID']; ?></td>
                                                        <td><?php echo $fechaFormateada; ?></td>
                                                        <td><?php echo htmlspecialchars($tk['cajero']); ?></td>
                                                        <td>
                                                            <span class="badge badge-dark border px-2 py-1">
                                                                <?php echo $iconosPago[$tk['forma_pago']] ?? htmlspecialchars($tk['forma_pago']); ?>
                                                            </span>
                                                        </td>
                                                        <td class="font-weight-bold text-success">$<?php echo number_format((float)$tk['total'], 2); ?></td>
                                                        <td class="text-right">
                                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-orden="<?php echo $jsonOrden; ?>" onclick="verDetalleOrden(this)">
                                                                Detalle
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna Derecha: Top Productos Vendidos -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header font-weight-bold">
                                🏆 Más Vendidos del Periodo
                            </div>
                            <ul class="list-group list-group-flush">
                                <?php if (empty($masVendidos)): ?>
                                    <li class="list-group-item bg-transparent text-center text-muted py-5">
                                        Sin productos vendidos en este periodo.
                                    </li>
                                <?php else: ?>
                                    <?php foreach ($masVendidos as $top): ?>
                                        <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="font-weight-bold"><?php echo htmlspecialchars($top['nombre']); ?></div>
                                                <small class="text-muted">
                                                    <?php echo (int)$top['unidades_vendidas']; ?> 
                                                    <?php echo ((int)$top['unidades_vendidas'] === 1) ? 'unidad vendida' : 'unidades vendidas'; ?>
                                                </small>
                                            </div>
                                            <span class="badge badge-primary badge-pill">
                                                $<?php echo number_format((float)$top['ingreso_producto'], 2); ?>
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- MODAL DETALLE DE TICKET (Reutiliza /js/ordenes.js) -->
    <div id="modalDetalleOrden" class="modal-custom-backdrop d-none">
        <div class="card border-0 shadow-lg" style="width: 100%; max-width: 460px;">
            <div class="card-header d-flex justify-content-between align-items-center font-weight-bold">
                <span id="modalOrdenFolio">Detalle del Pedido</span>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="cerrarModalOrden()">✕</button>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3" id="modalOrdenMeta"></p>
                <ul class="list-group list-group-flush mb-3" id="modalOrdenItems"></ul>
                <div class="d-flex justify-content-between border-top pt-2 h5 font-weight-bold mb-0">
                    <span>Total Pagado</span>
                    <span class="text-success" id="modalOrdenTotal">$0.00</span>
                </div>
            </div>
            <div class="card-footer text-right">
                <button type="button" class="btn btn-secondary btn-sm" onclick="cerrarModalOrden()">Cerrar</button>
            </div>
        </div>
    </div>

    <script src="/js/ordenes.js"></script>
</body>
</html>