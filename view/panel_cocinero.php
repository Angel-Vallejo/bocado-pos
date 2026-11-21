<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Cocinero') {
    header("Location: /view/login.php");
    exit();
}

require_once __DIR__ . '/../model/PedidoModel.php';

$comandas = PedidoModel::obtenerComandasActivas();

// Contadores dinámicos para la barra superior
$totalPendientes = count(array_filter($comandas, fn($c) => $c['estado'] === 'Pendiente'));
$totalPreparacion = count(array_filter($comandas, fn($c) => $c['estado'] === 'En preparacion'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cocina (KDS) - Bocado POS</title>
    <link href="/css/bootstrap.css" rel="stylesheet">
    <link href="/css/bocado.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="/img/favicon.png?v=2">
    <style>
        .comanda-card {
            border-width: 2px;
        }
        .nota-cocina {
            font-size: 0.9rem;
            color: #ff6b6b;
            font-weight: 700;
        }
    </style>
</head>
<body>

    <!-- 1. BARRA SUPERIOR -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <a class="navbar-brand font-weight-bold" href="/view/panel_cocinero.php">
            🍔 Bocado <span class="badge badge-warning text-dark ml-2">COCINA</span>
        </a>

        <div class="ml-auto d-flex align-items-center">
            <span class="text-light mr-3 d-none d-sm-inline">
                <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Cocinero'); ?>
            </span>
            <a href="/controller/logoutController.php" class="btn btn-outline-danger btn-sm font-weight-bold">
                Cerrar Sesión
            </a>
        </div>
    </nav>

    <!-- 2. RESUMEN Y TABLERO DE COMANDAS -->
    <div class="container-fluid py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <div>
                <h4 class="font-weight-bold mb-0">Comandas Activas</h4>
                <small class="text-muted">Ordenadas por llegada (FIFO) • Actualización automática cada 15s</small>
            </div>
            
            <div class="mt-2 mt-md-0">
                <span class="badge badge-warning text-dark p-2 mr-1">Pendientes: <?php echo $totalPendientes; ?></span>
                <span class="badge badge-info p-2 mr-2">En Preparación: <?php echo $totalPreparacion; ?></span>
                <button type="button" onclick="location.reload()" class="btn btn-secondary btn-sm">
                    Actualizar
                </button>
            </div>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'actualizado'): ?>
            <div class="alert alert-success py-2 px-3 mb-4 font-weight-bold">
                ✅ Estado de la comanda actualizado.
            </div>
        <?php endif; ?>

        <!-- 3. GRID DINÁMICO DE COMANDAS -->
        <div class="row">
            <?php if (empty($comandas)): ?>
                <div class="col-12 text-center py-5">
                    <h5 class="text-muted mb-1">No hay comandas pendientes en este momento</h5>
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
                            
                            <!-- Encabezado de la comanda -->
                            <div class="card-header <?php echo $enPreparacion ? 'bg-info text-white' : 'bg-warning text-dark'; ?> d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="font-weight-bold h5 mb-0">Pedido #<?php echo (int)$cmd['pedidoID']; ?></span>
                                    <small class="d-block font-weight-bold"><?php echo htmlspecialchars($cmd['mesa_o_llevar']); ?></small>
                                </div>
                                <span class="badge <?php echo $enPreparacion ? 'badge-light text-dark' : 'badge-dark'; ?>">
                                    <?php echo $hora; ?>
                                </span>
                            </div>

                            <!-- Lista de productos e indicaciones -->
                            <div class="card-body">
                                <div class="mb-2">
                                    <?php if ($enPreparacion): ?>
                                        <span class="badge badge-info">En Preparación</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning text-dark">Pendiente</span>
                                    <?php endif; ?>
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

                            <!-- Botón de cambio de estado -->
                            <div class="card-footer bg-transparent border-top-0">
                                <form action="/controller/pedidoController.php" method="POST">
                                    <input type="hidden" name="accion" value="cambiar_estado">
                                    <input type="hidden" name="origen" value="cocina">
                                    <input type="hidden" name="pedidoID" value="<?php echo (int)$cmd['pedidoID']; ?>">

                                    <?php if ($enPreparacion): ?>
                                        <input type="hidden" name="nuevo_estado" value="Listo">
                                        <button type="submit" class="btn btn-success btn-block font-weight-bold">
                                            Marcar como Listo
                                        </button>
                                    <?php else: ?>
                                        <input type="hidden" name="nuevo_estado" value="En preparacion">
                                        <button type="submit" class="btn btn-primary btn-block font-weight-bold">
                                            Empezar a Preparar
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Recarga automática cada 15 segundos para recibir nuevos pedidos de caja sin conexión a internet
        setTimeout(() => location.reload(), 15000);
    </script>

</body>
</html>