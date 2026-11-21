USE BD_Hamburgueseria_Bocado;
GO

-- CATALOGO DE INGREDIENTES EXTRA CON COSTO ADICIONAL
CREATE TABLE Extras (
    extraID INT IDENTITY(1,1) PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    disponible BIT DEFAULT 1
);
GO

-- EXTRAS COBRADOS POR PEDIDO
CREATE TABLE Detalle_Pedido_Extras (
    detalleExtraID INT IDENTITY(1,1) PRIMARY KEY,
    detalleID INT NOT NULL,
    extraID INT NOT NULL,
    precio_cobrado DECIMAL(10,2) NOT NULL,
    CONSTRAINT FK_DetalleExtras_Detalle FOREIGN KEY (detalleID) REFERENCES Detalle_Pedido(detalleID),
    CONSTRAINT FK_DetalleExtras_Extras FOREIGN KEY (extraID) REFERENCES Extras(extraID)
);
GO

ALTER TABLE Productos ADD ingredientes_base VARCHAR(300) NULL;
GO