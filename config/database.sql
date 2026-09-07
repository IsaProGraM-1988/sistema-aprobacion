-- config/database.sql
-- Base de datos: sistema_aprobacion_quillayes

CREATE DATABASE IF NOT EXISTS sistema_aprobacion_quillayes;
USE sistema_aprobacion_quillayes;

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('vendedor', 'aprobador', 'admin', 'kam') DEFAULT 'vendedor',
    cargo VARCHAR(100),
    subcanal VARCHAR(50),
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso DATETIME
);

-- Tabla de clientes
CREATE TABLE clientes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    codigo VARCHAR(20) UNIQUE NOT NULL,
    nombre VARCHAR(200) NOT NULL,
    subcanal VARCHAR(50),
    canal VARCHAR(50),
    pagador VARCHAR(100),
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla de materiales
CREATE TABLE materiales (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sku VARCHAR(20) UNIQUE NOT NULL,
    nombre VARCHAR(200) NOT NULL,
    familia VARCHAR(100),
    unidad_carga ENUM('UND', 'KG', 'LT'),
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla de precios
CREATE TABLE precios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sku VARCHAR(20),
    subcanal VARCHAR(50),
    precio_lista DECIMAL(15,2),
    fecha_vigencia DATE,
    fecha_termino DATE,
    FOREIGN KEY (sku) REFERENCES materiales(sku),
    UNIQUE KEY unique_precio (sku, subcanal)
);

-- Tabla de configuración de aprobadores
CREATE TABLE config_aprobadores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tramo INT NOT NULL,
    canal VARCHAR(50) NOT NULL,
    email_aprobador VARCHAR(100) NOT NULL,
    orden INT DEFAULT 0,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (email_aprobador) REFERENCES usuarios(email)
);

-- Tabla de configuración de tramos
CREATE TABLE config_tramos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    canal VARCHAR(50) NOT NULL,
    familia VARCHAR(100),
    tramo INT NOT NULL,
    descuento_min DECIMAL(5,2),
    descuento_max DECIMAL(5,2),
    aprobacion_automatica BOOLEAN DEFAULT FALSE,
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla principal de solicitudes
CREATE TABLE solicitudes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    codigo_solicitud VARCHAR(50) UNIQUE NOT NULL,
    id_vendedor INT,
    email_vendedor VARCHAR(100),
    id_cliente INT,
    codigo_cliente VARCHAR(20),
    nombre_cliente VARCHAR(200),
    subcanal VARCHAR(50),
    canal VARCHAR(50),
    fecha_inicio DATE NOT NULL,
    fecha_termino DATE NOT NULL,
    motivo TEXT,
    descuento_promedio DECIMAL(5,2),
    estado ENUM('borrador', 'enviada', 'en_aprobacion', 'aprobada', 'aprobada_parcial', 'rechazada') DEFAULT 'borrador',
    tramo_actual INT DEFAULT 1,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_envio DATETIME,
    fecha_cierre DATETIME,
    comentarios_aprobador TEXT,
    FOREIGN KEY (id_vendedor) REFERENCES usuarios(id),
    FOREIGN KEY (id_cliente) REFERENCES clientes(id)
);

-- Tabla de detalles de solicitud (SKUs)
CREATE TABLE solicitud_detalles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_solicitud INT,
    sku VARCHAR(20),
    nombre_material VARCHAR(200),
    familia VARCHAR(100),
    unidad_carga VARCHAR(10),
    precio_lista DECIMAL(15,2),
    precio_propuesto DECIMAL(15,2),
    porcentaje_descuento DECIMAL(5,2),
    volumen_esperado DECIMAL(15,2),
    FOREIGN KEY (id_solicitud) REFERENCES solicitudes(id) ON DELETE CASCADE,
    FOREIGN KEY (sku) REFERENCES materiales(sku)
);

-- Tabla de aprobaciones
CREATE TABLE aprobaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_solicitud INT,
    tramo INT,
    email_aprobador VARCHAR(100),
    estado ENUM('pendiente', 'aprobada', 'rechazada') DEFAULT 'pendiente',
    comentarios TEXT,
    fecha_asignacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_resolucion DATETIME,
    FOREIGN KEY (id_solicitud) REFERENCES solicitudes(id) ON DELETE CASCADE
);

-- Tabla de notificaciones
CREATE TABLE notificaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email_destino VARCHAR(100) NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    mensaje TEXT NOT NULL,
    tipo ENUM('info', 'success', 'warning', 'danger') DEFAULT 'info',
    leido BOOLEAN DEFAULT FALSE,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_lectura DATETIME
);

-- Tabla de logs
CREATE TABLE logs_sistema (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email_usuario VARCHAR(100),
    accion VARCHAR(255),
    detalles TEXT,
    ip VARCHAR(45),
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Insertar usuarios por defecto -- Contraseña: password
INSERT INTO usuarios (email, nombre, apellido, password, rol, cargo) VALUES 
('admin@quillayessurlat.cl', 'Administrador', 'Sistema', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Administrador');
