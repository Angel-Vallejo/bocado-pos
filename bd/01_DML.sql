USE BD_Hamburgueseria_Bocado;
GO

-- 1. CATÁLOGO DE 6 PRODUCTOS BASE

INSERT INTO Productos (nombre, categoria, precio, disponible, ingredientes_base) VALUES
(
    'Hamburguesa Clásica Bocado',
    'Hamburguesas',
    75.00,
    1,
    'Lechuga, Queso, Jitomate, Catsup, Mayonesa, Mostaza, Pepinillos'
),
(
    'Hamburguesa Hawaiana',
    'Hamburguesas',
    85.00,
    1,
    'Lechuga, Queso, Jitomate, Catsup, Mayonesa, Piña, Jamón'
),
(
    'Hamburguesa Argentina',
    'Hamburguesas',
    95.00,
    1,
    'Lechuga, Queso, Jitomate, Mayonesa, Mostaza, Chorizo, Chimichurri'
),
(
    'Hamburguesa BBQ Tocino',
    'Hamburguesas',
    98.00,
    1,
    'Queso, Mayonesa, Pepinillos, Tocino, Cebolla caramelizada, Salsa BBQ'
),
(
    'Papas Fritas Clásicas',
    'Complementos',
    40.00,
    1,
    NULL
),
(
    'Refresco de Cola 600ml',
    'Bebidas',
    28.00,
    1,
    NULL
);
GO


-- 2. INGREDIENTES EXTRA CON COSTO ADICIONAL (Chiles / Aderezos)

INSERT INTO Extras (nombre, precio, disponible) VALUES
('Chiles en vinagre', 8.00, 1),
('Aderezo chipotle', 10.00, 1),
('Chiles toreados', 12.00, 1);
GO