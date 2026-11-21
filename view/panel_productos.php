<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
    header("Location: /view/login.php");
    exit();
}

require_once __DIR__ . '/../model/ProductoModel.php';
$productos = ProductoModel::obtenerTodosProductos();
$extras = ProductoModel::obtenerTodosExtras();

$mensajes = [
    'prod_creado' => 'Producto registrado correctamente en el menú.',
    'prod_editado' => 'Datos del producto actualizados.',
    'extra_creado' => 'Ingrediente extra registrado.',
    'extra_editado' => 'Ingrediente extra actualizado.',
    'estado_actualizado' => 'Disponibilidad actualizada.'
];
$alerta = isset($_GET['msg']) && isset($mensajes[$_GET['msg']]) ? $mensajes[$_GET['msg']] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Menú - Bocado</title>
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
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h4 class="font-weight-bold mb-0">Catálogo de Productos e Ingredientes Extra</h4>
                        <small class="text-muted">Alta, edición de precios y disponibilidad del menú</small>
                    </div>
                    <div>
                        <span class="badge badge-dark p-2">
                            <i class="bi bi-person-fill mr-1"></i>
                            <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Administrador'); ?>
                        </span>
                    </div>
                </div>

                <?php if ($alerta): ?>
                    <div class="alert alert-success py-2 px-3 mb-4 font-weight-bold">
                        ✅ <?php echo htmlspecialchars($alerta); ?>
                    </div>
                <?php endif; ?>

                <!-- =========================================================
                     SECCIÓN A: PRODUCTOS DEL MENÚ
                     ========================================================= -->
                <div class="row mb-5">
                    
                    <!-- Formulario Producto -->
                    <div class="col-lg-4 mb-4 mb-lg-0">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header d-flex justify-content-between align-items-center font-weight-bold">
                                <span id="tituloFormProducto">
                                    <i class="bi bi-plus-square mr-1 text-warning"></i> Registrar Producto
                                </span>
                                <button type="button" id="btnCancelarProd" class="btn btn-xs btn-outline-secondary d-none" onclick="resetFormProducto()">
                                    Cancelar
                                </button>
                            </div>
                            <div class="card-body">
                                <form action="/controller/productoController.php" method="POST" id="formProducto">
                                    <input type="hidden" name="accion" id="prodAccion" value="crear_producto">
                                    <input type="hidden" name="productoID" id="prodID" value="">
                                    
                                    <div class="form-group">
                                        <label class="small font-weight-bold text-muted">Nombre del Producto</label>
                                        <input type="text" name="nombre" id="prodNombre" class="form-control" placeholder="Ej. Hamburguesa Hawaiana" required>
                                    </div>

                                    <div class="form-group">
                                        <label class="small font-weight-bold text-muted">Categoría</label>
                                        <select name="categoria" id="prodCategoria" class="form-control" onchange="toggleBloqueIngredientes()" required>
                                            <option value="Hamburguesas">Hamburguesas</option>
                                            <option value="Complementos">Complementos</option>
                                            <option value="Bebidas">Bebidas</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label class="small font-weight-bold text-muted">Precio Base ($ MXN)</label>
                                        <input type="number" name="precio" id="prodPrecio" step="0.50" min="0" class="form-control" placeholder="0.00" required>
                                    </div>

                                    <!-- Bloque dinámico: Solo visible cuando Categoría = Hamburguesas -->
                                    <div id="bloqueIngredientes" class="border rounded p-3 mb-3" style="background-color: rgba(0,0,0,0.15);">
                                        <label class="small font-weight-bold text-warning d-block mb-2">
                                            Ingredientes y Aderezos Incluidos:
                                        </label>
                                        <div class="row small">
                                            <?php 
                                            $baseItems = ['Lechuga', 'Queso', 'Jitomate', 'Catsup', 'Mayonesa', 'Mostaza', 'Pepinillos'];
                                            foreach ($baseItems as $item): 
                                            ?>
                                                <div class="col-6 mb-1">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input chk-base" 
                                                               id="chk_<?php echo $item; ?>" 
                                                               name="ingredientes[]" 
                                                               value="<?php echo $item; ?>" checked>
                                                        <label class="custom-control-label" for="chk_<?php echo $item; ?>">
                                                            <?php echo $item; ?>
                                                        </label>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>

                                        <div class="form-group mt-2 mb-0">
                                            <label class="small font-weight-bold text-muted mb-1">Ingredientes especiales (separados por coma)</label>
                                            <input type="text" name="ingredientes_especiales" id="prodEspeciales" class="form-control form-control-sm" placeholder="Ej. Piña, Jamón">
                                        </div>
                                    </div>

                                    <button type="submit" id="btnSubmitProd" class="btn btn-primary btn-block font-weight-bold mt-2">
                                        Guardar Producto
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Productos -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold">Productos en el Menú</span>
                                <span class="badge badge-info"><?php echo count($productos); ?> registrados</span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>ID</th>
                                                <th>Producto</th>
                                                <th>Categoría</th>
                                                <th>Precio</th>
                                                <th>Estado</th>
                                                <th class="text-right">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($productos)): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-4">
                                                        Aún no hay productos registrados en la base de datos.
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($productos as $p): 
                                                    $activo = ((int)$p['disponible'] === 1);
                                                ?>
                                                    <tr>
                                                        <td>#<?php echo $p['productoID']; ?></td>
                                                        <td>
                                                            <div class="font-weight-bold"><?php echo htmlspecialchars($p['nombre']); ?></div>
                                                            <?php if (!empty($p['ingredientes_base'])): ?>
                                                                <small class="text-muted d-block">
                                                                    <?php echo htmlspecialchars($p['ingredientes_base']); ?>
                                                                </small>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><span class="badge badge-secondary"><?php echo htmlspecialchars($p['categoria']); ?></span></td>
                                                        <td class="font-weight-bold text-success">$<?php echo number_format((float)$p['precio'], 2); ?></td>
                                                        <td>
                                                            <?php if ($activo): ?>
                                                                <span class="badge badge-success">Disponible</span>
                                                            <?php else: ?>
                                                                <span class="badge badge-danger">No disponible</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-right text-nowrap">
                                                            <!-- Botón Editar -->
                                                            <button type="button" class="btn btn-sm btn-outline-info" title="Editar"
                                                                onclick='cargarEditarProducto(
                                                                    <?php echo (int)$p["productoID"]; ?>,
                                                                    <?php echo json_encode($p["nombre"]); ?>,
                                                                    <?php echo json_encode($p["categoria"]); ?>,
                                                                    <?php echo (float)$p["precio"]; ?>,
                                                                    <?php echo json_encode($p["ingredientes_base"] ?? ""); ?>
                                                                )'>
                                                                <i class="bi bi-pencil"></i>
                                                            </button>

                                                            <!-- Form Activar / Desactivar -->
                                                            <form action="/controller/productoController.php" method="POST" class="d-inline">
                                                                <input type="hidden" name="accion" value="toggle_producto">
                                                                <input type="hidden" name="productoID" value="<?php echo $p['productoID']; ?>">
                                                                <input type="hidden" name="nuevo_estado" value="<?php echo $activo ? 0 : 1; ?>">
                                                                <button type="submit" class="btn btn-sm <?php echo $activo ? 'btn-outline-danger' : 'btn-outline-success'; ?>" 
                                                                        title="<?php echo $activo ? 'Desactivar' : 'Activar'; ?>">
                                                                    <i class="bi bi-power"></i>
                                                                </button>
                                                            </form>
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

                <!-- =========================================================
                     SECCIÓN B: INGREDIENTES EXTRA (Chiles con costo)
                     ========================================================= -->
                <div class="row pb-4">
                    
                    <!-- Formulario Extra -->
                    <div class="col-lg-4 mb-4 mb-lg-0">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header d-flex justify-content-between align-items-center font-weight-bold">
                                <span id="tituloFormExtra">
                                    <i class="bi bi-layers mr-1 text-info"></i> Registrar Ingrediente Extra
                                </span>
                                <button type="button" id="btnCancelarExtra" class="btn btn-xs btn-outline-secondary d-none" onclick="resetFormExtra()">
                                    Cancelar
                                </button>
                            </div>
                            <div class="card-body">
                                <form action="/controller/productoController.php" method="POST">
                                    <input type="hidden" name="accion" id="extraAccion" value="crear_extra">
                                    <input type="hidden" name="extraID" id="extraID" value="">
                                    
                                    <div class="form-group">
                                        <label class="small font-weight-bold text-muted">Nombre del Extra</label>
                                        <input type="text" name="nombre" id="extraNombre" class="form-control" placeholder="Ej. Chiles toreados, Chipotle" required>
                                    </div>

                                    <div class="form-group">
                                        <label class="small font-weight-bold text-muted">Costo Adicional ($ MXN)</label>
                                        <input type="number" name="precio" id="extraPrecio" step="0.50" min="0" class="form-control" placeholder="0.00" required>
                                    </div>

                                    <button type="submit" id="btnSubmitExtra" class="btn btn-info btn-block font-weight-bold mt-3">
                                        Guardar Extra
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Extras -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold">Ingredientes Extra Disponibles</span>
                                <span class="badge badge-info"><?php echo count($extras); ?> registrados</span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>ID</th>
                                                <th>Ingrediente Extra</th>
                                                <th>Costo Adicional</th>
                                                <th>Estado</th>
                                                <th class="text-right">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($extras)): ?>
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">
                                                        Aún no hay ingredientes extra registrados.
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($extras as $ex): 
                                                    $exActivo = ((int)$ex['disponible'] === 1);
                                                ?>
                                                    <tr>
                                                        <td>#<?php echo $ex['extraID']; ?></td>
                                                        <td class="font-weight-bold"><?php echo htmlspecialchars($ex['nombre']); ?></td>
                                                        <td class="font-weight-bold text-warning">+$<?php echo number_format((float)$ex['precio'], 2); ?></td>
                                                        <td>
                                                            <?php if ($exActivo): ?>
                                                                <span class="badge badge-success">Activo</span>
                                                            <?php else: ?>
                                                                <span class="badge badge-danger">Inactivo</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-right text-nowrap">
                                                            <button type="button" class="btn btn-sm btn-outline-info" title="Editar"
                                                                onclick='cargarEditarExtra(
                                                                    <?php echo (int)$ex["extraID"]; ?>,
                                                                    <?php echo json_encode($ex["nombre"]); ?>,
                                                                    <?php echo (float)$ex["precio"]; ?>
                                                                )'>
                                                                <i class="bi bi-pencil"></i>
                                                            </button>

                                                            <form action="/controller/productoController.php" method="POST" class="d-inline">
                                                                <input type="hidden" name="accion" value="toggle_extra">
                                                                <input type="hidden" name="extraID" value="<?php echo $ex['extraID']; ?>">
                                                                <input type="hidden" name="nuevo_estado" value="<?php echo $exActivo ? 0 : 1; ?>">
                                                                <button type="submit" class="btn btn-sm <?php echo $exActivo ? 'btn-outline-danger' : 'btn-outline-success'; ?>" 
                                                                        title="<?php echo $exActivo ? 'Desactivar' : 'Activar'; ?>">
                                                                    <i class="bi bi-power"></i>
                                                                </button>
                                                            </form>
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
        </div>
    </div>

    <!-- JavaScript nativo (100% offline, sin dependencias externas) -->
    <script>
        const ingredientesBaseEstandar = ['Lechuga', 'Queso', 'Jitomate', 'Catsup', 'Mayonesa', 'Mostaza', 'Pepinillos'];

        function toggleBloqueIngredientes() {
            const cat = document.getElementById('prodCategoria').value;
            const bloque = document.getElementById('bloqueIngredientes');
            if (cat === 'Hamburguesas') {
                bloque.classList.remove('d-none');
            } else {
                bloque.classList.add('d-none');
            }
        }

        function cargarEditarProducto(id, nombre, categoria, precio, ingredientesStr) {
            document.getElementById('prodAccion').value = 'editar_producto';
            document.getElementById('prodID').value = id;
            document.getElementById('prodNombre').value = nombre;
            document.getElementById('prodCategoria').value = categoria;
            document.getElementById('prodPrecio').value = precio;

            document.getElementById('tituloFormProducto').innerHTML = '<i class="bi bi-pencil-square mr-1 text-info"></i> Editando Producto #' + id;
            document.getElementById('btnSubmitProd').textContent = 'Actualizar Producto';
            document.getElementById('btnCancelarProd').classList.remove('d-none');

            toggleBloqueIngredientes();

            // Marcar casillas correspondientes y separar ingredientes especiales
            const lista = ingredientesStr ? ingredientesStr.split(',').map(s => s.trim()).filter(Boolean) : [];
            const checkboxes = document.querySelectorAll('.chk-base');
            checkboxes.forEach(chk => {
                chk.checked = lista.includes(chk.value);
            });

            const especiales = lista.filter(item => !ingredientesBaseEstandar.includes(item));
            document.getElementById('prodEspeciales').value = especiales.join(', ');
        }

        function resetFormProducto() {
            document.getElementById('prodAccion').value = 'crear_producto';
            document.getElementById('prodID').value = '';
            document.getElementById('prodNombre').value = '';
            document.getElementById('prodCategoria').value = 'Hamburguesas';
            document.getElementById('prodPrecio').value = '';
            document.getElementById('prodEspeciales').value = '';

            document.querySelectorAll('.chk-base').forEach(chk => chk.checked = true);
            toggleBloqueIngredientes();

            document.getElementById('tituloFormProducto').innerHTML = '<i class="bi bi-plus-square mr-1 text-warning"></i> Registrar Producto';
            document.getElementById('btnSubmitProd').textContent = 'Guardar Producto';
            document.getElementById('btnCancelarProd').classList.add('d-none');
        }

        function cargarEditarExtra(id, nombre, precio) {
            document.getElementById('extraAccion').value = 'editar_extra';
            document.getElementById('extraID').value = id;
            document.getElementById('extraNombre').value = nombre;
            document.getElementById('extraPrecio').value = precio;

            document.getElementById('tituloFormExtra').innerHTML = '<i class="bi bi-pencil-square mr-1 text-warning"></i> Editando Extra #' + id;
            document.getElementById('btnSubmitExtra').textContent = 'Actualizar Extra';
            document.getElementById('btnCancelarExtra').classList.remove('d-none');
        }

        function resetFormExtra() {
            document.getElementById('extraAccion').value = 'crear_extra';
            document.getElementById('extraID').value = '';
            document.getElementById('extraNombre').value = '';
            document.getElementById('extraPrecio').value = '';

            document.getElementById('tituloFormExtra').innerHTML = '<i class="bi bi-layers mr-1 text-info"></i> Registrar Ingrediente Extra';
            document.getElementById('btnSubmitExtra').textContent = 'Guardar Extra';
            document.getElementById('btnCancelarExtra').classList.add('d-none');
        }
    </script>

</body>
</html>