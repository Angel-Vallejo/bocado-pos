-- 1. CREA LA BASE DE DATOS
CREATE DATABASE BD_Hamburgueseria_Bocado;
GO

USE BD_Hamburgueseria_Bocado;
GO

-- 2. CREACIÓN TABLA DE USUARIOS
CREATE TABLE Usuarios (
    usuarioID INT IDENTITY(1,1) PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    rol VARCHAR(20) NOT NULL CHECK (rol IN ('Administrador', 'Cocinero')),
    activo BIT DEFAULT 1
);
GO

-- 3. CREACIÓN TABLA DE PRODUCTOS
CREATE TABLE Productos (
    productoID INT IDENTITY(1,1) PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    categoria VARCHAR(50) NOT NULL CHECK (categoria IN ('Hamburguesas', 'Complementos', 'Bebidas')),
    precio DECIMAL(10,2) NOT NULL,
    disponible BIT DEFAULT 1
);
GO

-- 4. CREACIÓN TABLA DE PEDIDOS
CREATE TABLE Pedidos (
    pedidoID INT IDENTITY(1,1) PRIMARY KEY, -- FOLIO
    usuarioID INT NOT NULL,                 -- QUE ADMINISTRADOR COBRÓ EL PEDIDO
    mesa_o_llevar VARCHAR(50) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    forma_pago VARCHAR(20) NOT NULL CHECK (forma_pago IN ('Efectivo', 'Tarjeta', 'Transferencia')),
    estado VARCHAR(20) NOT NULL DEFAULT 'Pendiente' 
        CHECK (estado IN ('Pendiente', 'En preparacion', 'Listo', 'Entregado', 'Cancelado')),
    fecha_creacion DATETIME DEFAULT GETDATE(),
    fecha_listo DATETIME NULL,
    fecha_entregado DATETIME NULL,
    CONSTRAINT FK_Pedidos_Usuarios FOREIGN KEY (usuarioID) REFERENCES Usuarios(usuarioID)
);
GO

-- 5. CREACIÓN TABLA DETALLE DE PEDIDO
CREATE TABLE Detalle_Pedido (
    detalleID INT IDENTITY(1,1) PRIMARY KEY,
    pedidoID INT NOT NULL,
    productoID INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    personalizacion VARCHAR(500) NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    CONSTRAINT FK_Detalle_Pedidos FOREIGN KEY (pedidoID) REFERENCES Pedidos(pedidoID),
    CONSTRAINT FK_Detalle_Productos FOREIGN KEY (productoID) REFERENCES Productos(productoID)
);
GO