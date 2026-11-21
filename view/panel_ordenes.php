<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
    header("Location: /view/login.php");
    exit();
}
require_once __DIR__ . '/../model/PedidoModel.php';

// Parámetros de filtro (por defecto muestra los pedidos del día actual)
$fechaFiltro = $_GET['fecha'] ?? date('Y-m-d');
$estadoFiltro = $_GET['estado'] ?? '';
$folioFiltro = trim($_GET['folio'] ?? '');

$resumen = PedidoModel::obtenerResumenDia($fechaFiltro);
$pedidos = PedidoModel::obtenerPedidosFiltrados($fechaFiltro, $estadoFiltro, $folioFiltro);

// Colores de etiqueta según el estado en la BD
$badgesEstado = [
    'Pendiente'      => 'badge-warning text-dark',
    'En preparacion' => 'badge-info',
    'Listo'          => 'badge-success',
    'Entregado'      => 'badge-secondary',
    'Cancelado'      => 'badge-danger'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Órdenes - Bocado</title>
    <link href="/css/bootstrap.css" rel="stylesheet">
    <link href="/css/bocado.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
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
                        <h4 class="font-weight-bold mb-0">Órdenes e Historial de Ventas</h4>
                        <small class="text-muted">Monitoreo de pedidos en curso y corte del día</small>
                    </div>
                    <div>
                        <span class="badge badge-dark p-2">
                            <i class="bi bi-person-fill mr-1"></i>
                            <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Administrador'); ?>
                        </span>
                    </div>
                </div>

                <?php if (isset($_GET['msg']) && $_GET['msg'] === 'actualizado'): ?>
                    <div class="alert alert-success py-2 px-3 mb-3 font-weight-bold">
                        ✅ Estado de la orden actualizado correctamente.
                    </div>
                <?php endif; ?>

                <!-- TARJETAS DE RESUMEN (KPIs reales del día) -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <small class="text-muted text-uppercase font-weight-bold">Ventas del Día</small>
                                <h3 class="font-weight-bold text-success mb-0 mt-1">
                                    $<?php echo number_format((float)$resumen['ventas_dia'], 2); ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <small class="text-muted text-uppercase font-weight-bold">Órdenes Completadas</small>
                                <h3 class="font-weight-bold text-dark mb-0 mt-1">
                                    <?php echo (int)$resumen['completadas']; ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <small class="text-muted text-uppercase font-weight-bold">Activas en Cocina</small>
                                <h3 class="font-weight-bold text-info mb-0 mt-1">
                                    <?php echo (int)$resumen['activas_cocina']; ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BARRA DE FILTROS -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body py-3">
                        <form method="GET" action="/view/panel_ordenes.php" class="row">
                            <div class="col-md-4 mb-2 mb-md-0">
                                <input type="text" name="folio" class="form-control" placeholder="Buscar por # de pedido..." 
                                       value="<?php echo htmlspecialchars($folioFiltro); ?>">
                            </div>
                            <div class="col-md-3 mb-2 mb-md-0">
                                <select name="estado" class="form-control">
                                    <option value="">Todos los estados</option>
                                    <option value="Pendiente" <?php echo $estadoFiltro === 'Pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                    <option value="En preparacion" <?php echo $estadoFiltro === 'En preparacion' ? 'selected' : ''; ?>>En Preparación</option>
                                    <option value="Listo" <?php echo $estadoFiltro === 'Listo' ? 'selected' : ''; ?>>Listo para entregar</option>
                                    <option value="Entregado" <?php echo $estadoFiltro === 'Entregado' ? 'selected' : ''; ?>>Entregado</option>
                                    <option value="Cancelado" <?php echo $estadoFiltro === 'Cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2 mb-md-0">
                                <input type="date" name="fecha" class="form-control" value="<?php echo htmlspecialchars($fechaFiltro); ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-block font-weight-bold">Filtrar</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- TABLA DINÁMICA DE ÓRDENES -->
                <div class="card border-0 shadow-sm flex-grow-1">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Folio</th>
                                        <th>Hora</th>
                                        <th>Detalle del Pedido</th>
                                        <th>Método Pago</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th class="text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($pedidos)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-5">
                                                No se encontraron órdenes con los filtros seleccionados.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($pedidos as $ped): 
                                            $hora = ($ped['fecha_creacion'] instanceof DateTime) 
                                                ? $ped['fecha_creacion']->format('h:i A') 
                                                : date('h:i A', strtotime($ped['fecha_creacion']));

                                            // Resumen legible de productos para la celda
                                            $resumenItems = array_map(fn($i) => $i['cantidad'] . 'x ' . $i['producto'], $ped['items']);
                                            $textoDetalle = !empty($resumenItems) ? implode(', ', $resumenItems) : 'Sin detalle';
                                            $badgeClase = $badgesEstado[$ped['estado']] ?? 'badge-secondary';

                                            // Datos JSON para el modal "Ver"
                                            $jsonOrden = htmlspecialchars(json_encode([
                                                'folio'  => (int)$ped['pedidoID'],
                                                'hora'   => $hora,
                                                'mesa'   => $ped['mesa_o_llevar'],
                                                'cajero' => $ped['cajero'],
                                                'pago'   => $ped['forma_pago'],
                                                'total'  => number_format((float)$ped['total'], 2),
                                                'items'  => $ped['items']
                                            ]), ENT_QUOTES, 'UTF-8');
                                        ?>
                                            <tr>
                                                <td class="font-weight-bold">#<?php echo (int)$ped['pedidoID']; ?></td>
                                                <td>
                                                    <div><?php echo $hora; ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($ped['mesa_o_llevar']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($textoDetalle); ?></td>
                                                <td><?php echo htmlspecialchars($ped['forma_pago']); ?></td>
                                                <td class="font-weight-bold">$<?php echo number_format((float)$ped['total'], 2); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $badgeClase; ?> px-2 py-1">
                                                        <?php echo htmlspecialchars($ped['estado']); ?>
                                                    </span>
                                                </td>
                                                <td class="text-right text-nowrap">
                                                    <?php if ($ped['estado'] === 'Listo'): ?>
                                                        <form action="/controller/pedidoController.php" method="POST" class="d-inline">
                                                            <input type="hidden" name="accion" value="cambiar_estado">
                                                            <input type="hidden" name="pedidoID" value="<?php echo (int)$ped['pedidoID']; ?>">
                                                            <input type="hidden" name="nuevo_estado" value="Entregado">
                                                            <button type="submit" class="btn btn-sm btn-outline-success font-weight-bold">Entregar</button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <?php if ($ped['estado'] === 'Pendiente'): ?>
                                                        <form action="/controller/pedidoController.php" method="POST" class="d-inline" onsubmit="return confirm('¿Cancelar el pedido #<?php echo (int)$ped['pedidoID']; ?>?');">
                                                            <input type="hidden" name="accion" value="cambiar_estado">
                                                            <input type="hidden" name="pedidoID" value="<?php echo (int)$ped['pedidoID']; ?>">
                                                            <input type="hidden" name="nuevo_estado" value="Cancelado">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">Cancelar</button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-orden="<?php echo $jsonOrden; ?>" onclick="verDetalleOrden(this)">
                                                        Ver
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
        </div>
    </div>

    <!-- MODAL DETALLE DE ORDEN -->
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