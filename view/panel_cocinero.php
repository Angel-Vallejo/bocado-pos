<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Cocinero') {
    header("Location: /view/login.php");
    exit();
}

require_once __DIR__ . '/../model/PedidoModel.php';

$hoy = date('Y-m-d');
$comandas       = PedidoModel::obtenerComandasActivas(); // Pendiente y En preparacion (FIFO)
$listasEntregar = PedidoModel::obtenerPedidosFiltrados($hoy, 'Listo');
$historialHoy   = PedidoModel::obtenerPedidosFiltrados($hoy, 'Entregado');

$totalPendientes  = count(array_filter($comandas, fn($c) => $c['estado'] === 'Pendiente'));
$totalPreparacion = count(array_filter($comandas, fn($c) => $c['estado'] === 'En preparacion'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor y Cocina - Bocado POS</title>
    <link href="/css/bootstrap.css" rel="stylesheet">
    <link href="/css/bocado.css" rel="stylesheet">
    <link rel="icon" type="image/jpeg" href="/img/favicon.jpg?v=2">
    <style>
        .comanda-card { border-width: 2px; }
        .nota-cocina { font-size: 0.9rem; color: #ff6b6b; font-weight: 700; }
        .modal-custom-backdrop {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.78);
            z-index: 1050;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>

    <!-- 1. BARRA SUPERIOR -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <a class="navbar-brand font-weight-bold" href="/view/panel_cocinero.php">
            🍔 Bocado <span class="badge badge-warning text-dark ml-2">SUPERVISOR / COCINA</span>
        </a>

        <div class="ml-auto d-flex align-items-center">
            <button type="button" class="btn btn-outline-info btn-sm font-weight-bold mr-3" onclick="abrirHistorial()">
                📋 Historial de Hoy (<?php echo count($historialHoy); ?>)
            </button>
            <span class="text-light mr-3 d-none d-md-inline">
                👨‍🍳 <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Supervisor'); ?>
            </span>
            <a href="/controller/logoutController.php" class="btn btn-outline-danger btn-sm font-weight-bold">
                Cerrar Sesión
            </a>
        </div>
    </nav>

    <div class="container-fluid py-4">

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'actualizado'): ?>
            <div class="alert alert-success py-2 px-3 mb-4 font-weight-bold">
                ✅ Estado de la orden actualizado correctamente.
            </div>
        <?php endif; ?>

        <!-- =========================================================
             2. BANDEJA DE PREPARACIÓN (PENDIENTES Y EN PREPARACIÓN)
             ========================================================= -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom">
            <div>
                <h4 class="font-weight-bold mb-0">Órdenes en Cocina</h4>
                <small class="text-muted">Ordenadas por llegada (FIFO) • Actualización automática cada 15s</small>
            </div>
            
            <div class="mt-2 mt-md-0">
                <span class="badge badge-warning text-dark p-2 mr-1">Pendientes: <?php echo $totalPendientes; ?></span>
                <span class="badge badge-info p-2 mr-2">En Preparación: <?php echo $totalPreparacion; ?></span>
                <button type="button" onclick="location.reload()" class="btn btn-secondary btn-sm">
                    🔄 Actualizar
                </button>
            </div>
        </div>

        <div class="row mb-5">
            <?php if (empty($comandas)): ?>
                <div class="col-12 text-center py-4">
                    <h6 class="text-muted mb-1">No hay órdenes pendientes de preparar</h6>
                    <small class="text-muted">Las nuevas órdenes cobradas en caja aparecerán aquí automáticamente.</small>
                </div>
            <?php else: ?>
                <?php foreach ($comandas as $cmd): 
                    $enPreparacion = ($cmd['estado'] === 'En preparacion');
                    $hora = ($cmd['fecha_creacion'] instanceof DateTime)
                        ? $cmd['fecha_creacion']->format('h:i A')
                        : date('h:i A', strtotime($cmd['fecha_creacion']));
                ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card comanda-card <?php echo $enPreparacion ? 'border-info' : 'border-warning'; ?> shadow-sm h-100">
                            <div class="card-header <?php echo $enPreparacion ? 'bg-info text-white' : 'bg-warning text-dark'; ?> d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="font-weight-bold h5 mb-0">Pedido #<?php echo (int)$cmd['pedidoID']; ?></span>
                                    <small class="d-block font-weight-bold"><?php echo htmlspecialchars($cmd['mesa_o_llevar']); ?></small>
                                </div>
                                <span class="badge <?php echo $enPreparacion ? 'badge-light text-dark' : 'badge-dark'; ?>">
                                    <?php echo $hora; ?>
                                </span>
                            </div>

                            <div class="card-body">
                                <div class="mb-2">
                                    <span class="badge <?php echo $enPreparacion ? 'badge-info' : 'badge-warning text-dark'; ?>">
                                        <?php echo $enPreparacion ? 'En Preparación' : 'Pendiente'; ?>
                                    </span>
                                </div>

                                <ul class="list-group list-group-flush">
                                    <?php foreach ($cmd['items'] as $item): ?>
                                        <li class="list-group-item bg-transparent px-0">
                                            <div class="font-weight-bold">
                                                <?php echo (int)$item['cantidad']; ?>x <?php echo htmlspecialchars($item['producto']); ?>
                                            </div>
                                            <?php if (!empty($item['personalizacion'])): ?>
                                                <div class="nota-cocina mt-1">
                                                    ⚠️ <?php echo htmlspecialchars($item['personalizacion']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>

                            <div class="card-footer bg-transparent border-top-0">
                                <form action="/controller/pedidoController.php" method="POST">
                                    <input type="hidden" name="accion" value="cambiar_estado">
                                    <input type="hidden" name="origen" value="cocina">
                                    <input type="hidden" name="pedidoID" value="<?php echo (int)$cmd['pedidoID']; ?>">

                                    <?php if ($enPreparacion): ?>
                                        <input type="hidden" name="nuevo_estado" value="Listo">
                                        <button type="submit" class="btn btn-success btn-block font-weight-bold">
                                            ✅ Marcar Orden Terminada
                                        </button>
                                    <?php else: ?>
                                        <input type="hidden" name="nuevo_estado" value="En preparacion">
                                        <button type="submit" class="btn btn-primary btn-block font-weight-bold">
                                            🔥 Iniciar Preparación
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- =========================================================
             3. BANDEJA DE ENTREGA (ÓRDENES TERMINADAS / LISTAS)
             ========================================================= -->
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
            <div>
                <h4 class="font-weight-bold mb-0 text-success">Listas para Entregar al Cliente</h4>
                <small class="text-muted">Verifica el ticket del cliente y registra la entrega</small>
            </div>
            <span class="badge badge-success p-2">Por entregar: <?php echo count($listasEntregar); ?></span>
        </div>

        <div class="row">
            <?php if (empty($listasEntregar)): ?>
                <div class="col-12 text-center py-4">
                    <small class="text-muted">No hay órdenes terminadas esperando entrega en este momento.</small>
                </div>
            <?php else: ?>
                <?php foreach ($listasEntregar as $lista): 
                    $horaLista = ($lista['fecha_creacion'] instanceof DateTime)
                        ? $lista['fecha_creacion']->format('h:i A')
                        : date('h:i A', strtotime($lista['fecha_creacion']));
                ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card comanda-card border-success shadow-sm h-100">
                            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="font-weight-bold h5 mb-0">Pedido #<?php echo (int)$lista['pedidoID']; ?></span>
                                    <small class="d-block font-weight-bold"><?php echo htmlspecialchars($lista['mesa_o_llevar']); ?></small>
                                </div>
                                <span class="badge badge-light text-dark"><?php echo $horaLista; ?></span>
                            </div>

                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($lista['items'] as $item): ?>
                                        <li class="list-group-item bg-transparent px-0 py-1">
                                            <span class="font-weight-bold">
                                                <?php echo (int)$item['cantidad']; ?>x <?php echo htmlspecialchars($item['producto']); ?>
                                            </span>
                                            <?php if (!empty($item['personalizacion'])): ?>
                                                <small class="text-warning d-block"><?php echo htmlspecialchars($item['personalizacion']); ?></small>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>

                            <div class="card-footer bg-transparent border-top-0">
                                <form action="/controller/pedidoController.php" method="POST">
                                    <input type="hidden" name="accion" value="cambiar_estado">
                                    <input type="hidden" name="origen" value="cocina">
                                    <input type="hidden" name="pedidoID" value="<?php echo (int)$lista['pedidoID']; ?>">
                                    <input type="hidden" name="nuevo_estado" value="Entregado">
                                    <button type="submit" class="btn btn-outline-success btn-block font-weight-bold">
                                        📦 Registrar Entrega al Cliente
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

    <!-- =========================================================
         4. MODAL HISTORIAL DE ÓRDENES ENTREGADAS HOY
         ========================================================= -->
    <div id="modalHistorialSupervisor" class="modal-custom-backdrop d-none">
        <div class="card border-0 shadow-lg" style="width: 100%; max-width: 720px; max-height: 85vh;">
            <div class="card-header d-flex justify-content-between align-items-center font-weight-bold">
                <span>📋 Historial de Órdenes Entregadas Hoy (<?php echo date('d/m/Y'); ?>)</span>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="cerrarHistorial()">✕</button>
            </div>
            <div class="card-body overflow-auto p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th>Folio</th>
                                <th>Hora</th>
                                <th>Destino</th>
                                <th>Detalle Entregado</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($historialHoy)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        Aún no se han entregado órdenes el día de hoy.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($historialHoy as $hist): 
                                    $horaHist = ($hist['fecha_creacion'] instanceof DateTime)
                                        ? $hist['fecha_creacion']->format('h:i A')
                                        : date('h:i A', strtotime($hist['fecha_creacion']));
                                    $resumen = implode(', ', array_map(fn($i) => $i['cantidad'] . 'x ' . $i['producto'], $hist['items']));
                                ?>
                                    <tr>
                                        <td class="font-weight-bold">#<?php echo (int)$hist['pedidoID']; ?></td>
                                        <td><?php echo $horaHist; ?></td>
                                        <td><?php echo htmlspecialchars($hist['mesa_o_llevar']); ?></td>
                                        <td><?php echo htmlspecialchars($resumen); ?></td>
                                        <td class="font-weight-bold text-success">$<?php echo number_format((float)$hist['total'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-right">
                <button type="button" class="btn btn-secondary btn-sm" onclick="cerrarHistorial()">Cerrar</button>
            </div>
        </div>
    </div>

    <script>
        let historialAbierto = false;

        function abrirHistorial() {
            historialAbierto = true;
            document.getElementById('modalHistorialSupervisor').classList.remove('d-none');
        }

        function cerrarHistorial() {
            historialAbierto = false;
            document.getElementById('modalHistorialSupervisor').classList.add('d-none');
        }

        // Recarga automática cada 15 segundos solo si no se está consultando el historial
        setTimeout(() => {
            if (!historialAbierto) {
                location.reload();
            }
        }, 15000);
    </script>

</body>
</html>