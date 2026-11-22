USE BD_Hamburgueseria_Bocado;
GO

DECLARE @adminID INT = ISNULL((SELECT TOP 1 usuarioID FROM Usuarios WHERE rol = 'Administrador'), 1);

DECLARE @idClasica   INT = ISNULL((SELECT TOP 1 productoID FROM Productos WHERE nombre LIKE '%Clásica%' OR nombre LIKE '%Clasica%'), 1);
DECLARE @idHawaiana  INT = ISNULL((SELECT TOP 1 productoID FROM Productos WHERE nombre LIKE '%Hawaiana%'), 2);
DECLARE @idArgentina INT = ISNULL((SELECT TOP 1 productoID FROM Productos WHERE nombre LIKE '%Argentina%'), 3);
DECLARE @idBBQ       INT = ISNULL((SELECT TOP 1 productoID FROM Productos WHERE nombre LIKE '%BBQ%'), 4);
DECLARE @idPapas     INT = ISNULL((SELECT TOP 1 productoID FROM Productos WHERE nombre LIKE '%Papas%'), 5);
DECLARE @idRefresco  INT = ISNULL((SELECT TOP 1 productoID FROM Productos WHERE nombre LIKE '%Refresco%'), 6);

DECLARE @pedidoID INT;

-- =========================================================================
-- 20 ÓRDENES COMPLETADAS (ESTADO: 'Entregado')
-- =========================================================================

-- #1
INSERT INTO Pedidos (usuarioID, mesa_o_llevar, total, forma_pago, estado, fecha_creacion)
VALUES (@adminID, 'Mesa 1', 246.00, 'Efectivo', 'Entregado', DATEADD(MINUTE, -350, GETDATE()));
SET @pedidoID = SCOPE_IDENTITY();
INSERT INTO Detalle_Pedido (pedidoID, productoID, cantidad, precio_unitario, personalizacion, subtotal) VALUES
(@pedidoID, @idClasica, 2, 75.00, 'Sin Pepinillos', 150.00),
(@pedidoID, @idPapas, 1, 40.00, NULL, 40.00),
(@pedidoID, @idRefresco, 2, 28.00, NULL, 56.00);

-- #2
INSERT INTO Pedidos (usuarioID, mesa_o_llevar, total, forma_pago, estado, fecha_creacion)
VALUES (@adminID, 'Para llevar', 133.00, 'Tarjeta', 'Entregado', DATEADD(MINUTE, -335, GETDATE()));
SET @pedidoID = SCOPE_IDENTITY();
INSERT INTO Detalle_Pedido (pedidoID, productoID, cantidad, precio_unitario, personalizacion, subtotal) VALUES
(@pedidoID, @idHawaiana, 1, 93.00, 'Extra: Chiles en vinagre', 93.00),
(@pedidoID, @idPapas, 1, 40.00, NULL, 40.00);

-- #3
INSERT INTO Pedidos (usuarioID, mesa_o_llevar, total, forma_pago, estado, fecha_creacion)
VALUES (@adminID, 'Mesa 3', 249.00, 'Efectivo', 'Entregado', DATEADD(MINUTE, -310, GETDATE()));
SET @pedidoID = SCOPE_IDENTITY();
INSERT INTO Detalle_Pedido (pedidoID, productoID, cantidad, precio_unitario, personalizacion, subtotal) VALUES
(@pedidoID, @idArgentina, 1, 95.00, NULL, 95.00),
(@pedidoID, @idBBQ, 1, 98.00, 'Sin Cebolla caramelizada', 98.00),
(@pedidoID, @idRefresco, 2, 28.00, NULL, 56.00);

-- #4
INSERT INTO Pedidos (usuarioID, mesa_o_llevar, total, forma_pago, estado, fecha_creacion)
VALUES (@adminID, 'Mostrador', 138.00, 'Transferencia', 'Entregado', DATEADD(MINUTE, -290, GETDATE()));
SET @pedidoID = SCOPE_IDENTITY();
INSERT INTO Detalle_Pedido (pedidoID, productoID, cantidad, precio_unitario, personalizacion, subtotal) VALUES
(@pedidoID, @idBBQ, 1, 110.00, 'Extra: Chiles toreados', 110.00),
(@pedidoID, @idRefresco, 1, 28.00, NULL, 28.00);

-- #5
INSERT INTO Pedidos (usuarioID, mesa_o_llevar, total, forma_pago, estado, fecha_creacion)
VALUES (@adminID, 'Mesa 2', 389.00, 'Tarjeta', 'Entregado', DATEADD(MINUTE, -270, GETDATE()));
SET @pedidoID = SCOPE_IDENTITY();
INSERT INTO Detalle_Pedido (pedidoID, productoID, cantidad, precio_unitario, personalizacion, subtotal) VALUES
(@pedidoID, @idClasica, 3, 75.00, NULL, 225.00),
(@pedidoID, @idPapas, 2, 40.00, NULL, 80.00),
(@pedidoID, @idRefresco, 3, 28.00, NULL, 84.00);

-- #6
INSERT INTO Pedidos (usuarioID, mesa_o_llevar, total, forma_pago, estado, fecha_creacion)
VALUES (@adminID, 'Para llevar', 210.00, 'Efectivo', 'Entregado', DATEADD(MINUTE, -250, GETDATE()));
SET @pedidoID = SCOPE_IDENTITY();
INSERT INTO Detalle_Pedido (pedidoID, productoID, cantidad, precio_unitario, personalizacion, subtotal) VALUES
(@pedidoID, @idHawaiana, 2, 85.00, 'Sin Piña', 170.00),
(@pedidoID, @idPapas, 1, 40.00, NULL, 40.00);

-- #7
INSERT INTO Pedidos (usuarioID, mesa_o_llevar, total, forma_pago, estado, fecha_creacion)
VALUES (@adminID, 'Mesa 4', 236.00, 'Tarjeta', 'Entregado', DATEADD(MINUTE, -230, GETDATE()));
SET @pedidoID = SCOPE_IDENTITY();
INSERT INTO Detalle_Pedido (pedidoID, productoID, cantidad, precio_unitario, personalizacion, subtotal) VALUES
(@pedidoID, @idArgentina, 1, 105.00, 'Extra: Aderezo chipotle', 105.00),
(@pedidoID, @idClasica, 1, 75.00, 'Sin Mostaza', 75.00),
(@pedidoID, @idRefresco, 2, 28.00, NULL, 56.00);

-- #8
INSERT INTO Pedidos (usuarioID, mesa_o_llevar, total, forma_pago, estado, fecha_creacion)
VALUES (@adminID, 'Mostrador', 103.00, 'Efectivo', 'Entregado', DATEADD(MINUTE, -215, GETDATE()));
SET @pedidoID = SCOPE_IDENTITY();
INSERT INTO Detalle_Pedido (pedidoID, productoID, cantidad, precio_unitario, personalizacion, subtotal) VALUES
(@pedidoID, @idClasica, 1, 75.00, NULL, 75.00),
(@pedidoID, @idRefresco, 1, 28.00, NULL, 28.00);

-- #9
INSERT INTO Pedidos (usuarioID, mesa_o_llevar, total, forma_pago, estado, fecha_creacion)
VALUES (@adminID, 'Mesa 5', 558.00, 'Transferencia', 'Entregado', DATEADD(MINUTE, -195, GETDATE()));
SET @pedidoID = SCOPE_IDENTITY();
INSERT INTO Detalle_Pedido (pedidoID, productoID, cantidad, precio_unitario, personalizacion, subtotal) VALUES
(@pedidoID, @idBBQ, 2, 98.00, NULL, 196.00),
(@pedidoID, @idHawaiana, 2, 85.00, NULL, 170.00),
(@pedidoID, @idPapas, 2, 40.00, NULL, 80.00),
(@pedidoID, @idRefresco, 4, 28.00, NULL, 112.00);

-- #10
INSERT INTO Pedidos (usuarioID, mesa_o_llevar, total, forma_pago, estado, fecha_creacion)
VALUES (@adminID, 'Para llevar', 175.00, 'Efectivo', 'Entregado', DATEADD(MINUTE, -180, GETDATE()));
SET @pedidoID = SCOPE_IDENTITY();
INSERT INTO Detalle_Pedido (pedidoID, productoID, cantidad, precio_unitario, personalizacion, subtotal) VALUES
(@pedidoID, @idArgentina, 1, 107.00, 'Sin Jitomate | Extra: Chiles toreados', 107.00),
(@pedidoID, @idPapas, 1, 40.00, NULL, 40.00),
(@pedidoID, @idRefresco, 1, 28.00, NULL, 28.00);

-- #11
INSERT INTO Pedidos (usuarioID, mesa_o_llevar, total, forma_pago, estado, fecha_creacion)
VALUES (@adminID, 'Mesa 1', 234.00, 'Tarjeta', 'Entregado', DATEADD(MINUTE, -160, GETDATE()));
SET @pedidoID = SCOPE_IDENTITY();
INSERT INTO Detalle_Pedido (pedidoID, productoID, cantidad, precio_unitario, personalizacion, subtotal) VALUES
(@pedidoID, @idHawaiana, 1, 95.00, 'Extra: Aderezo chipotle', 95.00),
(@pedidoID, @idClasica, 1, 83.00, 'Extra: Chiles en vinagre', 83.00),
(@pedidoID, @idRefresco, 2, 28.00, NULL, 56.00);

-- #12
INSERT INTO Pedidos (usuarioID, mesa_o_llevar, total, forma_pago, estado, fecha_creacion)
VALUES (@adminID, 'Mostrador', 136.00, 'Efectivo', 'Entregado', DATEADD(MINUTE, -145, GETDATE()));
SET @pedidoID = SCOPE_IDENTITY();
INSERT INTO Detalle_Pedido (pedidoID, productoID, cantidad, precio_unitario, personalizacion, subtotal) VALUES
(@pedidoID, @idPapas, 2, 40.00, NULL, 80.00),
(@pedidoID, @idRefresco, 2, 28.00, NULL, 56.00);

-- #13
INSERT INTO Pedidos (usuarioID, mesa_o_llevar, total, forma_pago, estado, fecha_creacion)
VALUES (@adminID, 'Mesa 3', 286.00, 'Transferencia', 'Entregado', DATEADD(MINUTE, -130, GETDATE()));
SET @pedidoID = SCOPE_IDENTITY();
INSERT INTO Detalle_Pedido (pedidoID, productoID, cantidad, precio_unitario, personalizacion, subtotal) VALUES
(@pedidoID, @idArgentina, 2, 95.00, 'Sin Mayonesa', 190.00),
(@pedidoID, @idPapas, 1, 40.00, NULL, 40.00),
(@pedidoID, @idRefresco, 2, 28.00, NULL, 56.00);

-- #14
INSERT INTO Pedidos (usuarioID, mesa_o_llevar, total, forma_pago, estado, fecha_creacion)
VALUES (@adminID, 'Para llevar', 213.00, 'Tarjeta', 'Entregado', DATEADD(MINUTE, -115, GETDATE()));
SET @pedidoID = SCOPE_IDENTITY();
INSERT INTO Detalle_Pedido (pedidoID, productoID, cantidad, precio_unitario, personalizacion, subtotal) VALUES
(@pedidoID, @idBBQ, 1, 98.00, NULL, 98.00),
(@pedidoID, @idClasica, 1, 75.00, 'Sin Catsup', 75.00),
(@pedidoID, @idPapas, 1, 40.00, NULL, 40.00);

-- #15
INSERT INTO Pedidos (usuarioID, mesa_o_llevar, total, forma_pago, estado, fecha_creacion)
VALUES (@adminID, 'Mesa 2', 113.00, 'Efectivo', 'Entregado', DATEADD(MINUTE, -95, GETDATE()));
SET @pedidoID = SCOPE_IDENTITY();
INSERT INTO Detalle_Pedido (pedidoID, productoID, cantidad, precio_unitario, personalizacion, subtotal) VALUES
(@pedidoID, @idHawaiana, 1, 85.00, NULL, 85.00),
(@pedidoID, @idRefresco, 1, 28.00, NULL, 28.00);

-- #16
INSERT INTO Pedidos (usuarioID, mesa_o_llevar, total, forma_pago, estado, fecha_creacion)
VALUES (@adminID, 'Mesa 4', 259.00, 'Tarjeta', 'Entregado', DATEADD(MINUTE, -80, GETDATE()));
SET @pedidoID = SCOPE_IDENTITY();
INSERT INTO Detalle_Pedido (pedidoID, productoID, cantidad, precio_unitario, personalizacion, subtotal) VALUES
(@pedidoID, @idBBQ, 1, 108.00, 'Extra: Aderezo chipotle', 108.00),
(@pedidoID, @idArgentina, 1, 95.00, NULL, 95.00),
(@pedidoID, @idRefresco, 2, 28.00, NULL, 56.00);

-- #17
INSERT INTO Pedidos (usuarioID, mesa_o_llevar, total, forma_pago, estado, fecha_creacion)
VALUES (@adminID, 'Mostrador', 190.00, 'Efectivo', 'Entregado', DATEADD(MINUTE, -65, GETDATE()));
SET @pedidoID = SCOPE_IDENTITY();
INSERT INTO Detalle_Pedido (pedidoID, productoID, cantidad, precio_unitario, personalizacion, subtotal) VALUES
(@pedidoID, @idClasica, 2, 75.00, 'Sin Lechuga', 150.00),
(@pedidoID, @idPapas, 1, 40.00, NULL, 40.00);

-- #18
INSERT INTO Pedidos (usuarioID, mesa_o_llevar, total, forma_pago, estado, fecha_creacion)
VALUES (@adminID, 'Para llevar', 331.00, 'Transferencia', 'Entregado', DATEADD(MINUTE, -50, GETDATE()));
SET @pedidoID = SCOPE_IDENTITY();
INSERT INTO Detalle_Pedido (pedidoID, productoID, cantidad, precio_unitario, personalizacion, subtotal) VALUES
(@pedidoID, @idHawaiana, 1, 97.00, 'Extra: Chiles toreados', 97.00),
(@pedidoID, @idBBQ, 1, 98.00, 'Sin Pepinillos', 98.00),
(@pedidoID, @idPapas, 2, 40.00, NULL, 80.00),
(@pedidoID, @idRefresco, 2, 28.00, NULL, 56.00);

-- #19
INSERT INTO Pedidos (usuarioID, mesa_o_llevar, total, forma_pago, estado, fecha_creacion)
VALUES (@adminID, 'Mesa 5', 236.00, 'Efectivo', 'Entregado', DATEADD(MINUTE, -30, GETDATE()));
SET @pedidoID = SCOPE_IDENTITY();
INSERT INTO Detalle_Pedido (pedidoID, productoID, cantidad, precio_unitario, personalizacion, subtotal) VALUES
(@pedidoID, @idArgentina, 1, 95.00, NULL, 95.00),
(@pedidoID, @idClasica, 1, 85.00, 'Extra: Aderezo chipotle', 85.00),
(@pedidoID, @idRefresco, 2, 28.00, NULL, 56.00);

-- #20
INSERT INTO Pedidos (usuarioID, mesa_o_llevar, total, forma_pago, estado, fecha_creacion)
VALUES (@adminID, 'Mesa 1', 287.00, 'Tarjeta', 'Entregado', DATEADD(MINUTE, -15, GETDATE()));
SET @pedidoID = SCOPE_IDENTITY();
INSERT INTO Detalle_Pedido (pedidoID, productoID, cantidad, precio_unitario, personalizacion, subtotal) VALUES
(@pedidoID, @idBBQ, 1, 106.00, 'Extra: Chiles en vinagre', 106.00),
(@pedidoID, @idHawaiana, 1, 85.00, NULL, 85.00),
(@pedidoID, @idPapas, 1, 40.00, NULL, 40.00),
(@pedidoID, @idRefresco, 2, 28.00, NULL, 56.00);
GO