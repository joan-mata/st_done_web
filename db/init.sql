-- =============================================================================
-- TechShop — Esquema completo e datos iniciales
-- =============================================================================
-- Uso:
--   1. Crear la BD:  CREATE DATABASE tienda_uab;
--   2. Conectar:     \c tienda_uab
--   3. Ejecutar:     \i db/init.sql
--   4. Usuarios:     php db/seed_usuarios.php
--
-- Este archivo crea todas las tablas desde cero y carga los datos de prueba.
-- Si la BD ya existe y solo necesitas aplicar cambios, usa db/update.sql.
-- =============================================================================


-- -----------------------------------------------------------------------------
-- Limpieza (permite re-ejecutar el script sin errores)
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS devoluciones  CASCADE;
DROP TABLE IF EXISTS lineas_pedido CASCADE;
DROP TABLE IF EXISTS pedidos       CASCADE;
DROP TABLE IF EXISTS productos     CASCADE;
DROP TABLE IF EXISTS categorias    CASCADE;
DROP TABLE IF EXISTS usuarios      CASCADE;


-- -----------------------------------------------------------------------------
-- TABLA: categorias
-- -----------------------------------------------------------------------------
CREATE TABLE categorias (
    id     SERIAL      PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL UNIQUE
);


-- -----------------------------------------------------------------------------
-- TABLA: usuarios
-- -----------------------------------------------------------------------------
CREATE TABLE usuarios (
    id            SERIAL       PRIMARY KEY,
    nombre        VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    rol           VARCHAR(20)  NOT NULL DEFAULT 'cliente'
                      CHECK (rol IN ('cliente', 'admin')),
    telefono      VARCHAR(20),
    direccion     VARCHAR(255),
    ciudad        VARCHAR(100),
    codigo_postal VARCHAR(10),
    created_at    TIMESTAMP    NOT NULL DEFAULT NOW()
);


-- -----------------------------------------------------------------------------
-- TABLA: productos
-- -----------------------------------------------------------------------------
CREATE TABLE productos (
    id           SERIAL        PRIMARY KEY,
    nombre       VARCHAR(150)  NOT NULL,
    descripcion  TEXT,
    precio       NUMERIC(10,2) CHECK (precio >= 0),
    stock        INTEGER       NOT NULL DEFAULT 0 CHECK (stock >= 0),
    icono        VARCHAR(10)   NOT NULL DEFAULT '📦',
    imagen_url   VARCHAR(255)  NOT NULL DEFAULT '/img/placeholder.svg',
    categoria_id INTEGER       REFERENCES categorias (id) ON DELETE SET NULL,
    activo       BOOLEAN       NOT NULL DEFAULT TRUE,
    created_at   TIMESTAMP     NOT NULL DEFAULT NOW()
);


-- -----------------------------------------------------------------------------
-- TABLA: pedidos
-- -----------------------------------------------------------------------------
CREATE TABLE pedidos (
    id                     SERIAL        PRIMARY KEY,
    usuario_id             INTEGER       REFERENCES usuarios (id) ON DELETE SET NULL,
    nombre_destinatario    VARCHAR(150)  NOT NULL,
    telefono_envio         VARCHAR(20),
    calle                  VARCHAR(255)  NOT NULL,
    ciudad                 VARCHAR(100)  NOT NULL,
    codigo_postal          VARCHAR(10)   NOT NULL,
    pais                   VARCHAR(80)   NOT NULL DEFAULT 'España',
    total                  NUMERIC(10,2) CHECK (total >= 0),
    estado                 VARCHAR(30)   NOT NULL DEFAULT 'pendiente'
                               CHECK (estado IN ('pendiente','procesando','enviado',
                                                 'entregado','cancelado','devuelto')),
    fecha_estimada_entrega TIMESTAMP,
    created_at             TIMESTAMP     NOT NULL DEFAULT NOW()
);


-- -----------------------------------------------------------------------------
-- TABLA: lineas_pedido
-- -----------------------------------------------------------------------------
CREATE TABLE lineas_pedido (
    id              SERIAL        PRIMARY KEY,
    pedido_id       INTEGER       NOT NULL REFERENCES pedidos   (id) ON DELETE CASCADE,
    producto_id     INTEGER       REFERENCES productos (id) ON DELETE SET NULL,
    cantidad        INTEGER       CHECK (cantidad > 0),
    precio_unitario NUMERIC(10,2) CHECK (precio_unitario >= 0)
);


-- -----------------------------------------------------------------------------
-- TABLA: devoluciones
-- -----------------------------------------------------------------------------
CREATE TABLE devoluciones (
    id          SERIAL       PRIMARY KEY,
    pedido_id   INTEGER      NOT NULL REFERENCES pedidos (id) ON DELETE CASCADE,
    motivo      VARCHAR(100) NOT NULL,
    descripcion TEXT,
    estado      VARCHAR(30)  NOT NULL DEFAULT 'solicitada'
                    CHECK (estado IN ('solicitada','aprobada','rechazada','completada')),
    created_at  TIMESTAMP    NOT NULL DEFAULT NOW()
);


-- -----------------------------------------------------------------------------
-- ÍNDICES
-- -----------------------------------------------------------------------------
CREATE INDEX idx_productos_categoria  ON productos     (categoria_id);
CREATE INDEX idx_productos_activo     ON productos     (activo);
CREATE INDEX idx_pedidos_usuario      ON pedidos       (usuario_id);
CREATE INDEX idx_pedidos_estado       ON pedidos       (estado);
CREATE INDEX idx_lineas_pedido_pedido ON lineas_pedido (pedido_id);


-- =============================================================================
-- DATOS DE PRUEBA
-- =============================================================================

-- Categorías ----------------------------------------------------------------
INSERT INTO categorias (nombre) VALUES
    ('Smartphones'),
    ('Tablets'),
    ('Auriculares'),
    ('Accesorios');


-- Productos -----------------------------------------------------------------
INSERT INTO productos (nombre, descripcion, precio, stock, icono, imagen_url, categoria_id) VALUES

-- Smartphones (categoria_id = 1)
('iPhone 15 Pro',
 'Apple iPhone 15 Pro 256 GB, chip A17 Pro, cámara 48 MP de titanio. Hasta 23 h de reproducción de vídeo.',
 999.99,  15, '📱', '/img/iphone15pro.jpg',    1),

('Samsung Galaxy S24',
 'Samsung Galaxy S24 128 GB, Snapdragon 8 Gen 3, pantalla Dynamic AMOLED 2X a 120 Hz.',
 849.99,  20, '📱', '/img/samsungs24.jpg',     1),

('Xiaomi 14',
 'Xiaomi 14 256 GB, Snapdragon 8 Gen 3, cámara Leica triple, carga rápida de 90 W.',
 699.99,  12, '📱', '/img/xiaomi14.jpg',       1),

-- Tablets (categoria_id = 2)
('iPad Air M2',
 'Apple iPad Air 11 pulgadas, chip M2, 256 GB Wi-Fi, pantalla Liquid Retina con True Tone.',
 749.99,  10, '💻', '/img/ipadair.jpg',        2),

('Samsung Galaxy Tab S9',
 'Samsung Galaxy Tab S9 128 GB, Snapdragon 8 Gen 2, pantalla AMOLED 11 pulgadas, S Pen incluido.',
 649.99,   8, '💻', '/img/galaxytabs9.jpg',    2),

-- Auriculares (categoria_id = 3)
('AirPods Pro 2ª gen',
 'Apple AirPods Pro de 2ª generación con cancelación activa de ruido adaptativa y chip H2.',
 279.99,  25, '🎧', '/img/airpodspro.jpg',     3),

('Sony WH-1000XM5',
 'Auriculares over-ear Sony con la mejor cancelación de ruido del mercado. Hasta 30 h de batería.',
 329.99,  18, '🎧', '/img/sonywh1000xm5.jpg',  3),

('JBL Tune 770NC',
 'Auriculares JBL inalámbricos con cancelación de ruido adaptativa y 70 h de autonomía.',
 109.99,  30, '🎧', '/img/jbltune770.jpg',     3),

-- Accesorios (categoria_id = 4)
('Cable USB-C 2 m',
 'Cable USB-C de carga rápida 100 W, 2 metros, recubrimiento de nylon trenzado.',
    19.99, 100, '🔌', '/img/cableusbc.jpeg',     4),

('Cargador GaN 65 W',
 'Cargador compacto GaN 65 W con 2× USB-C y 1× USB-A. Compatible con MacBook, iPad y smartphones.',
    49.99,  50, '🔌', '/img/cargador65w.jpg',    4),

('Funda iPhone 15 Pro',
 'Funda transparente reforzada MagSafe compatible con iPhone 15 Pro. Bordes elevados anti-caída.',
    24.99,  60, '🛡️', '/img/funda_iphone15.jpg', 4),

('MagSafe 15 W',
 'Cargador MagSafe oficial Apple 15 W para iPhone 12 y posteriores. Cable USB-C incluido.',
    39.99,  40, '🔌', '/img/magsafe.jpg',        4);


-- Usuarios ------------------------------------------------------------------
-- IMPORTANTE: ejecuta `php db/seed_usuarios.php` para insertar los usuarios
--             de prueba. Los passwords se hashean con password_hash() en PHP
--             y no se pueden generar de forma segura desde SQL puro.
--
-- Credenciales de prueba:
--   admin@tienda.com  / Admin123!   (rol: admin)
--   juan@example.com  / Cliente123! (rol: cliente)
--   maria@example.com / Cliente123! (rol: cliente)
