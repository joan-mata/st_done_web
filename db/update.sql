-- =============================================================================
-- TechShop — Migraciones sobre BD existente
-- =============================================================================
-- ADVERTENCIA: Ejecutar solo si ya tienes la BD creada.
--              Si empiezas desde cero, usa init.sql.
--
-- Uso:
--   psql -U <usuario> -d <base_de_datos> -f db/update.sql
-- =============================================================================


-- =============================================================================
-- 1. TABLA usuarios — añadir columnas de contacto y dirección
-- =============================================================================
ALTER TABLE usuarios
    ADD COLUMN IF NOT EXISTS telefono      VARCHAR(20),
    ADD COLUMN IF NOT EXISTS direccion     VARCHAR(255),
    ADD COLUMN IF NOT EXISTS ciudad        VARCHAR(100),
    ADD COLUMN IF NOT EXISTS codigo_postal VARCHAR(10);


-- =============================================================================
-- 2. TABLA pedidos — reestructurar dirección de envío
--    Se reemplaza la columna monolítica `direccion_envio` por campos separados
--    y se añaden nuevas columnas de envío.
-- =============================================================================

-- 2a. Nuevas columnas de envío
ALTER TABLE pedidos
    ADD COLUMN IF NOT EXISTS nombre_destinatario VARCHAR(150),
    ADD COLUMN IF NOT EXISTS telefono_envio      VARCHAR(20),
    ADD COLUMN IF NOT EXISTS calle               VARCHAR(255),
    ADD COLUMN IF NOT EXISTS ciudad              VARCHAR(100),
    ADD COLUMN IF NOT EXISTS codigo_postal       VARCHAR(10),
    ADD COLUMN IF NOT EXISTS pais                VARCHAR(80) DEFAULT 'España';

-- 2b. Columna para fecha estimada de entrega
ALTER TABLE pedidos
    ADD COLUMN IF NOT EXISTS fecha_estimada_entrega TIMESTAMP;

-- 2c. Eliminar columna antigua (ya no se usa; los datos históricos deben
--     migrarse a `calle` antes de ejecutar este paso en producción)
ALTER TABLE pedidos
    DROP COLUMN IF EXISTS direccion_envio;


-- =============================================================================
-- 3. TABLA pedidos — corregir constraint de estado
--    El constraint anterior no incluía 'devuelto'. Se elimina y se vuelve a
--    crear con el valor añadido.
-- =============================================================================

-- 3a. Eliminar el constraint antiguo (nombre generado por PostgreSQL por defecto)
--     Si el constraint tiene un nombre personalizado en tu BD, ajusta la línea
--     siguiente. Puedes consultarlo con:
--       SELECT conname FROM pg_constraint WHERE conrelid = 'pedidos'::regclass;
ALTER TABLE pedidos
    DROP CONSTRAINT IF EXISTS pedidos_estado_check;

-- 3b. Añadir el constraint actualizado con todos los estados válidos
ALTER TABLE pedidos
    ADD CONSTRAINT pedidos_estado_check
        CHECK (estado IN ('pendiente','procesando','enviado',
                          'entregado','cancelado','devuelto'));


-- =============================================================================
-- 4. TABLA productos — añadir imagen_url si no existe
--    (puede que ya estuviera en tu esquema; IF NOT EXISTS lo maneja sin error)
-- =============================================================================
ALTER TABLE productos
    ADD COLUMN IF NOT EXISTS imagen_url VARCHAR(255) NOT NULL DEFAULT '/img/placeholder.svg';


-- =============================================================================
-- 5. TABLA devoluciones — crear si no existe
-- =============================================================================
CREATE TABLE IF NOT EXISTS devoluciones (
    id          SERIAL       PRIMARY KEY,
    pedido_id   INTEGER      NOT NULL REFERENCES pedidos (id) ON DELETE CASCADE,
    motivo      VARCHAR(100) NOT NULL,
    descripcion TEXT,
    estado      VARCHAR(30)  NOT NULL DEFAULT 'solicitada'
                    CHECK (estado IN ('solicitada','aprobada','rechazada','completada')),
    created_at  TIMESTAMP    NOT NULL DEFAULT NOW()
);


-- =============================================================================
-- 6. ÍNDICES — crear los que falten (IF NOT EXISTS evita errores duplicados)
-- =============================================================================
CREATE INDEX IF NOT EXISTS idx_productos_categoria  ON productos     (categoria_id);
CREATE INDEX IF NOT EXISTS idx_productos_activo     ON productos     (activo);
CREATE INDEX IF NOT EXISTS idx_pedidos_usuario      ON pedidos       (usuario_id);
CREATE INDEX IF NOT EXISTS idx_pedidos_estado       ON pedidos       (estado);
CREATE INDEX IF NOT EXISTS idx_lineas_pedido_pedido ON lineas_pedido (pedido_id);
