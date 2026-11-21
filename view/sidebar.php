<?php
// Detecta el nombre del archivo actual (ej. "panel_admin.php")
$paginaActual = basename($_SERVER['PHP_SELF']);
?>

<div class="col-1 bg-dark d-flex flex-column align-items-center py-4 text-white">
    <a href="/view/panel_admin.php" class="text-white text-decoration-none mb-4 h4 font-weight-bold" title="Bocado POS">
        🍔
    </a>
    
    <ul class="nav nav-pills flex-column mb-auto text-center">
        <li class="nav-item mb-3">
            <a href="/view/panel_admin.php" 
               class="nav-link py-2 px-3 <?php echo ($paginaActual == 'panel_admin.php') ? 'active text-white' : 'text-secondary'; ?>" 
               title="Nuevo Pedido">
                <i class="bi bi-plus-circle" style="font-size: 1.35rem;"></i>
            </a>
        </li>
        <li class="nav-item mb-3">
            <a href="/view/panel_ordenes.php" 
               class="nav-link py-2 px-3 <?php echo ($paginaActual == 'panel_ordenes.php') ? 'active text-white' : 'text-secondary'; ?>" 
               title="Órdenes Activas">
                <i class="bi bi-receipt" style="font-size: 1.35rem;"></i>
            </a>
        </li>
        <li class="nav-item mb-3">
            <a href="/view/panel_historial.php" 
               class="nav-link py-2 px-3 <?php echo ($paginaActual == 'panel_historial.php') ? 'active text-white' : 'text-secondary'; ?>" 
               title="Historial de Ventas">
                <i class="bi bi-bar-chart" style="font-size: 1.35rem;"></i>
            </a>
        </li>
	<li class="nav-item mb-3">
   	 <a href="/view/panel_productos.php" 
       		class="nav-link py-2 px-3 <?php echo ($paginaActual == 'panel_productos.php') ? 'active text-white' : 'text-secondary'; ?>" 
       		title="Menú y Extras">
        <i class="bi bi-card-list" style="font-size: 1.35rem;"></i>
    </a>
</li>
    </ul>

    <div class="mt-auto">
        <a href="/controller/logoutController.php" class="nav-link text-danger py-2 px-3" title="Cerrar Sesión">
            <i class="bi bi-box-arrow-right" style="font-size: 1.35rem;"></i>
        </a>
    </div>
</div>