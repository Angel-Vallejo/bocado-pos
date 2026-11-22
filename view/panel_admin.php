<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
    header("Location: /view/login.php");
    exit();
}
require_once __DIR__ . '/../model/ProductoModel.php';
$productosActivos = array_filter(ProductoModel::obtenerTodosProductos(), fn($p) => (int)$p['disponible'] === 1);
$extrasActivos = array_filter(ProductoModel::obtenerTodosExtras(), fn($e) => (int)$e['disponible'] === 1);
?>
<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administrador - Bocado</title>
    <link href="/css/bootstrap.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="/css/bocado.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="/img/favicon.png?v=2">
</head>
<body>

    <div class="container-fluid vh-100 overflow-hidden">
        <div class="row h-100">
            
            <?php include __DIR__ . '/sidebar.php'; ?>

            <!-- CATÁLOGO DE PRODUCTOS -->
            <div class="col-7 d-flex flex-column p-4 h-100">
                
                <?php if (isset($_GET['msg']) && $_GET['msg'] === 'pedido_creado'): ?>
                    <div class="alert alert-success py-2 px-3 mb-3 font-weight-bold d-flex justify-content-between">
                        <span>✅ Pedido #<?php echo htmlspecialchars($_GET['folio'] ?? ''); ?> enviado a cocina.</span>
                        <a href="/view/panel_admin.php" class="text-white small">Cerrar</a>
                    </div>
                <?php endif; ?>

                <div class="row mb-4">
                    <div class="col-md-8 mb-2 mb-md-0">
                        <input type="text" id="inputBuscar" class="form-control" placeholder="Buscar productos..." onkeyup="filtrarProductos()">
                    </div>
                    <div class="col-md-4">
                        <select id="selectCategoria" class="form-control" onchange="filtrarProductos()">
                            <option value="Todas">Todas las categorías</option>
                            <option value="Hamburguesas">Hamburguesas</option>
                            <option value="Complementos">Complementos</option>
                            <option value="Bebidas">Bebidas</option>
                        </select>
                    </div>
                </div>

                <div class="row overflow-auto" style="max-height: calc(100vh - 130px);">
                    <?php foreach ($productosActivos as $prod): 
                        $jsonProd = htmlspecialchars(json_encode([
                            'id' => (int)$prod['productoID'],
                            'nombre' => $prod['nombre'],
                            'categoria' => $prod['categoria'],
                            'precio' => (float)$prod['precio'],
                            'ingredientes' => $prod['ingredientes_base'] ?? ''
                        ]), ENT_QUOTES, 'UTF-8');
                    ?>
                        <div class="col-md-4 mb-3 item-producto" 
                             data-nombre="<?php echo htmlspecialchars(strtolower($prod['nombre'])); ?>" 
                             data-categoria="<?php echo htmlspecialchars($prod['categoria']); ?>">
                            
                            <div class="card h-100 shadow-sm producto-card" data-prod="<?php echo $jsonProd; ?>" onclick="seleccionarProducto(this)">
                                <div class="card-body d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="card-title font-weight-bold mb-1"><?php echo htmlspecialchars($prod['nombre']); ?></h6>
                                        <?php if (!empty($prod['ingredientes_base'])): ?>
                                            <small class="text-muted d-block mb-2"><?php echo htmlspecialchars($prod['ingredientes_base']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <span class="text-success font-weight-bold">$<?php echo number_format((float)$prod['precio'], 2); ?></span>
                                        <span class="badge badge-secondary"><?php echo htmlspecialchars($prod['categoria']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- TICKET ACTUAL / CARRITO -->
            <div class="col-4 bg-white border-left d-flex flex-column p-4 h-100 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="font-weight-bold mb-0">Nuevo Pedido</h5>
                    <span class="badge badge-dark" id="contadorArticulos">0 artículos</span>
                </div>
                
                <div class="flex-grow-1 overflow-auto mb-3">
                    <ul class="list-group list-group-flush" id="listaCarrito">
                        <li class="list-group-item bg-transparent text-center text-muted py-5">Selecciona productos del menú.</li>
                    </ul>
                </div>

                <div class="mt-auto border-top pt-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span id="txtSubtotal">$0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 h5 font-weight-bold">
                        <span>Total</span>
                        <span class="text-success" id="txtTotal">$0.00</span>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-danger btn-block font-weight-bold" onclick="vaciarCarrito()">Cancelar</button>
                        </div>
                        <div class="col-6">
                            <button type="button" id="btnAbrirCobro" class="btn btn-primary btn-block font-weight-bold" onclick="abrirModalCobro()" disabled>Cobrar</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- MODAL PERSONALIZAR HAMBURGUESA -->
    <div id="modalPersonalizar" class="modal-custom-backdrop d-none">
        <div class="card border-0 shadow-lg" style="width: 100%; max-width: 480px;">
            <div class="card-header d-flex justify-content-between align-items-center font-weight-bold">
                <span id="modalProdTitulo">Personalizar</span>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="cerrarModal('modalPersonalizar')">✕</button>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-2">Desmarca los ingredientes que el cliente <strong>no</strong> desea:</p>
                <div class="row mb-3" id="contenedorIngredientesBase"></div>
                <hr>
                <p class="small font-weight-bold text-warning mb-2">Extras (Chiles con costo adicional):</p>
                <?php foreach ($extrasActivos as $ex): ?>
                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox" class="custom-control-input chk-extra-modal" 
                               id="ex_<?php echo $ex['extraID']; ?>" value="<?php echo $ex['extraID']; ?>"
                               data-nombre="<?php echo htmlspecialchars($ex['nombre']); ?>" data-precio="<?php echo (float)$ex['precio']; ?>"
                               onchange="recalcularModal()">
                        <label class="custom-control-label d-flex justify-content-between" for="ex_<?php echo $ex['extraID']; ?>">
                            <span><?php echo htmlspecialchars($ex['nombre']); ?></span>
                            <span class="text-warning font-weight-bold">+$<?php echo number_format((float)$ex['precio'], 2); ?></span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <span class="font-weight-bold h5 mb-0 text-success" id="modalPrecioTotal">$0.00</span>
                <div>
                    <button type="button" class="btn btn-outline-secondary mr-2" onclick="cerrarModal('modalPersonalizar')">Cancelar</button>
                    <button type="button" class="btn btn-primary font-weight-bold" onclick="confirmarHamburguesa()">Agregar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL COBRO -->
    <div id="modalCobro" class="modal-custom-backdrop d-none">
        <div class="card border-0 shadow-lg" style="width: 100%; max-width: 430px;">
            <div class="card-header d-flex justify-content-between align-items-center font-weight-bold">
                <span>Cobrar Orden</span>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="cerrarModal('modalCobro')">✕</button>
            </div>
            <form action="/controller/pedidoController.php" method="POST">
                <input type="hidden" name="accion" value="crear_pedido">
                <input type="hidden" name="carrito_json" id="inputCarritoJson">
                <div class="card-body">
                    <div class="text-center mb-3 p-2 rounded" style="background: rgba(0,0,0,0.2);">
                        <small class="text-muted d-block">TOTAL A COBRAR</small>
                        <span class="h3 font-weight-bold text-success" id="cobroTotalDisplay">$0.00</span>
                    </div>
                    <div class="form-group">
 		    <label class="small font-weight-bold text-muted">Destino de la Orden</label>
   		    <select name="mesa_o_llevar" class="form-control" required>
 		       <option value="Mostrador">Mostrador</option>
   		       <option value="Para llevar">Para llevar</option>
    		       <option value="Mesa 1">Mesa 1</option>
    		       <option value="Mesa 2">Mesa 2</option>
		       <option value="Mesa 3">Mesa 3</option>
        	       <option value="Mesa 4">Mesa 4</option>
       		       <option value="Mesa 5">Mesa 5</option>
		     </select>
		    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold text-muted">Forma de Pago</label>
                        <select name="forma_pago" id="selectFormaPago" class="form-control" onchange="toggleCalculadoraCambio()">
                            <option value="Efectivo">Efectivo</option>
                            <option value="Tarjeta">Tarjeta</option>
                            <option value="Transferencia">Transferencia</option>
                        </select>
                    </div>
                    <div id="bloqueEfectivo" class="border rounded p-2 mt-3" style="background: rgba(0,0,0,0.15);">
                        <input type="number" id="inputEfectivoRecibido" step="0.50" class="form-control form-control-sm mb-2" placeholder="Efectivo recibido ($)" oninput="calcularCambio()">
                        <div class="d-flex justify-content-between small font-weight-bold">
                            <span>Cambio:</span>
                            <span id="txtCambio" class="text-info">$0.00</span>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end">
                    <button type="button" class="btn btn-outline-secondary mr-2" onclick="cerrarModal('modalCobro')">Volver</button>
                    <button type="submit" class="btn btn-success font-weight-bold">✅ Confirmar Pedido</button>
                </div>
            </form>
        </div>
    </div>

    <script src="/js/pos.js"></script>
</body>
</html>