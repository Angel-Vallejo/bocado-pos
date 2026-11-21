<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
    header("Location: /view/login.php");
    exit();
}

require_once __DIR__ . '/../model/PedidoModel.php';

$folio = isset($_GET['folio']) ? (int)$_GET['folio'] : 0;
$pedido = ($folio > 0) ? PedidoModel::obtenerPedidoPorFolio($folio) : null;

if (!$pedido) {
    header("Location: /view/panel_admin.php");
    exit();
}

$fechaHora = ($pedido['fecha_creacion'] instanceof DateTime)
    ? $pedido['fecha_creacion']->format('d/m/Y h:i A')
    : date('d/m/Y h:i A', strtotime($pedido['fecha_creacion']));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets Pedido #<?php echo (int)$pedido['pedidoID']; ?> - Bocado</title>
    <link href="/css/bootstrap.css" rel="stylesheet">
    <link rel="icon" type="image/jpeg" href="/img/favicon.jpg?v=2">
    <style>
        body {
            background-color: #212529;
            font-family: 'Courier New', Courier, monospace;
        }
        .ticket-paper {
            background-color: #ffffff;
            color: #111111;
            max-width: 340px;
            width: 100%;
            margin: 0 auto;
            padding: 20px 18px;
            border-radius: 4px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.4);
        }
        .ticket-divider {
            border-top: 1px dashed #333333;
            margin: 10px 0;
        }
        .ticket-badge {
            border: 1px solid #111111;
            padding: 2px 8px;
            font-weight: bold;
            font-size: 0.85rem;
            display: inline-block;
        }
        @media print {
            body {
                background-color: #ffffff !important;
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .ticket-paper {
                box-shadow: none !important;
                border: 1px solid #000000;
                page-break-inside: avoid;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body class="py-4">

    <!-- Barra superior de acciones (oculta al imprimir) -->
    <div class="container mb-4 no-print">
        <div class="d-flex justify-content-between align-items-center bg-dark text-white p-3 rounded border border-secondary">
            <div>
                <h5 class="mb-0 font-weight-bold" style="font-family: sans-serif;">
                    ✅ Pedido #<?php echo (int)$pedido['pedidoID']; ?> Registrado
                </h5>
                <small class="text-muted" style="font-family: sans-serif;">
                    Comprobantes generados para Cliente y Supervisor
                </small>
            </div>
            <div style="font-family: sans-serif;">
                <button type="button" onclick="window.print()" class="btn btn-warning font-weight-bold mr-2">
                    🖨️ Imprimir Tickets
                </button>
                <a href="/view/panel_admin.php?msg=pedido_creado&folio=<?php echo (int)$pedido['pedidoID']; ?>" class="btn btn-success font-weight-bold">
                    ⬅️ Volver a Caja
                </a>
            </div>
        </div>
    </div>

    <!-- Contenedor de los 2 Tickets -->
    <div class="container">
        <div class="row justify-content-center">
            
            <!-- =========================================================
                 1. TICKET DEL CLIENTE
                 ========================================================= -->
            <div class="col-md-5 mb-4 d-flex justify-content-center">
                <div class="ticket-paper">
                    <div class="text-center mb-2">
                        <div class="ticket-badge mb-2">TICKET DEL CLIENTE</div>
                        <h5 class="font-weight-bold mb-0">🍔 BOCADO</h5>
                        <small>Hamburguesería Artesanal</small>
                    </div>

                    <div class="ticket-divider"></div>

                    <div class="small">
                        <div class="d-flex justify-content-between">
                            <span><strong>Folio:</strong> #<?php echo (int)$pedido['pedidoID']; ?></span>
                            <span><?php echo $fechaHora; ?></span>
                        </div>
                        <div><strong>Destino:</strong> <?php echo htmlspecialchars($pedido['mesa_o_llevar']); ?></div>
                        <div><strong>Atendió:</strong> <?php echo htmlspecialchars($pedido['cajero']); ?></div>
                    </div>

                    <div class="ticket-divider"></div>

                    <!-- Productos con precios -->
                    <div class="small">
                        <?php foreach ($pedido['items'] as $item): ?>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between font-weight-bold">
                                    <span><?php echo (int)$item['cantidad']; ?>x <?php echo htmlspecialchars($item['producto']); ?></span>
                                    <span>$<?php echo number_format((float)$item['subtotal'], 2); ?></span>
                                </div>
                                <?php if (!empty($item['personalizacion'])): ?>
                                    <div class="pl-2 text-muted" style="font-size: 0.78rem;">
                                        * <?php echo htmlspecialchars($item['personalizacion']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="ticket-divider"></div>

                    <div class="d-flex justify-content-between font-weight-bold h6 mb-1">
                        <span>TOTAL PAGADO:</span>
                        <span>$<?php echo number_format((float)$pedido['total'], 2); ?></span>
                    </div>
                    <div class="small d-flex justify-content-between">
                        <span>Forma de pago:</span>
                        <strong><?php echo htmlspecialchars($pedido['forma_pago']); ?></strong>
                    </div>

                    <div class="ticket-divider"></div>

                    <div class="text-center small mt-2">
                        <p class="mb-0 font-weight-bold">¡Gracias por su preferencia!</p>
                        <small>Conserve este ticket para recoger su orden</small>
                    </div>
                </div>
            </div>

            <!-- =========================================================
                 2. TICKET DEL SUPERVISOR
                 ========================================================= -->
            <div class="col-md-5 mb-4 d-flex justify-content-center">
                <div class="ticket-paper">
                    <div class="text-center mb-2">
                        <div class="ticket-badge mb-2">TICKET DEL SUPERVISOR</div>
                        <h5 class="font-weight-bold mb-0">CONTROL DE ORDEN</h5>
                        <small>Copia Operativa / Entrega</small>
                    </div>

                    <div class="ticket-divider"></div>

                    <div class="small">
                        <div class="d-flex justify-content-between">
                            <span class="h6 font-weight-bold mb-0">FOLIO #<?php echo (int)$pedido['pedidoID']; ?></span>
                            <span><?php echo $fechaHora; ?></span>
                        </div>
                        <div class="mt-1"><strong>Destino:</strong> <?php echo htmlspecialchars($pedido['mesa_o_llevar']); ?></div>
                        <div><strong>Caja:</strong> <?php echo htmlspecialchars($pedido['cajero']); ?> | <strong>Pago:</strong> <?php echo htmlspecialchars($pedido['forma_pago']); ?></div>
                    </div>

                    <div class="ticket-divider"></div>

                    <!-- Productos enfocados en preparación y verificación -->
                    <div class="small">
                        <?php foreach ($pedido['items'] as $item): ?>
                            <div class="mb-2 pb-1 border-bottom">
                                <div class="font-weight-bold" style="font-size: 0.95rem;">
                                    [ ] <?php echo (int)$item['cantidad']; ?>x <?php echo htmlspecialchars($item['producto']); ?>
                                </div>
                                <?php if (!empty($item['personalizacion'])): ?>
                                    <div class="font-weight-bold pl-2 mt-1" style="font-size: 0.85rem;">
                                        NOTA: <?php echo htmlspecialchars($item['personalizacion']); ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-muted pl-2" style="font-size: 0.78rem;">
                                        Estándar (sin modificaciones)
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="d-flex justify-content-between small font-weight-bold mt-2">
                        <span>Importe cobrado:</span>
                        <span>$<?php echo number_format((float)$pedido['total'], 2); ?></span>
                    </div>

                    <div class="ticket-divider"></div>

                    <div class="text-center small mt-2">
                        <div>Estado inicial: <strong><?php echo htmlspecialchars($pedido['estado']); ?></strong></div>
                        <small>Verificar contenido antes de entregar al cliente</small>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        // Abre el cuadro de impresión automáticamente al generar los tickets
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>