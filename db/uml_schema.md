# Esquema UML — Base de datos TechShop

```
┌─────────────────────────────┐
│          categorias          │
├─────────────────────────────┤
│ PK  id        SERIAL        │
│     nombre    VARCHAR(80)   │
└──────────────┬──────────────┘
               │ 1
               │ tiene muchos
               │ N
┌──────────────▼──────────────┐       ┌──────────────────────────────┐
│          productos           │       │           usuarios            │
├─────────────────────────────┤       ├──────────────────────────────┤
│ PK  id           SERIAL     │       │ PK  id            SERIAL     │
│     nombre       VARCHAR    │       │     nombre        VARCHAR    │
│     descripcion  TEXT       │       │     email         VARCHAR    │
│     precio       NUMERIC    │       │     password_hash VARCHAR    │
│     stock        INTEGER    │       │     rol           VARCHAR    │
│     icono        VARCHAR    │       │     created_at    TIMESTAMP  │
│ FK  categoria_id → cat.id   │       └──────────────┬───────────────┘
│     activo       BOOLEAN    │                      │ 1
│     created_at   TIMESTAMP  │                      │ realiza
└──────────────┬──────────────┘                      │ N
               │                       ┌─────────────▼───────────────┐
               │ N                     │           pedidos            │
               │ aparece en            ├─────────────────────────────┤
               │                       │ PK  id             SERIAL   │
               │            ┌──────────│ FK  usuario_id  → usu.id    │
               │            │          │     total          NUMERIC  │
               │            │          │     estado         VARCHAR  │
               │            │ 1        │     direccion_envio TEXT    │
               │            │ tiene    │     created_at     TIMESTAMP│
               │            │ muchas   └──────────────┬──────────────┘
               │            │ N                        │ 1
               │      ┌─────▼──────────────────────────▼──────────────┐
               │      │                lineas_pedido                   │
               │      ├───────────────────────────────────────────────┤
               └──────│ FK  producto_id  → pro.id                     │
                    N │ FK  pedido_id    → ped.id                     │
                      │ PK  id              SERIAL                    │
                      │     cantidad        INTEGER                   │
                      │     precio_unitario NUMERIC                   │
                      └───────────────────────────────────────────────┘
```

## Relaciones

| Relación                     | Cardinalidad | Descripción                                    |
|------------------------------|:------------:|------------------------------------------------|
| `categorias` → `productos`   | 1:N          | Una categoría tiene muchos productos           |
| `usuarios` → `pedidos`       | 1:N          | Un usuario puede tener muchos pedidos          |
| `pedidos` → `lineas_pedido`  | 1:N          | Un pedido tiene una o más líneas               |
| `productos` → `lineas_pedido`| 1:N          | Un producto puede aparecer en muchas líneas    |

## Tablas

### `categorias`
Agrupa los productos por tipo (Smartphones, Tablets, etc.).

### `usuarios`
Almacena los clientes y administradores. La contraseña **nunca** se guarda en claro:
se almacena el hash generado con `password_hash()` de PHP (bcrypt, coste 12).

### `productos`
Catálogo de artículos. `activo = FALSE` permite "desactivar" un producto sin borrarlo.
`icono` es un emoji que se usa en la interfaz como imagen placeholder.

### `pedidos`
Cabecera del pedido. El estado evoluciona de `pendiente` → `procesando` → `enviado` → `entregado`.
Si el usuario se elimina, `usuario_id` pasa a NULL (ON DELETE SET NULL).

### `lineas_pedido`
Detalle del pedido. Almacena `precio_unitario` en el momento de la compra para que
cambios futuros en el precio del producto no alteren el histórico.
Si se elimina el pedido, las líneas se eliminan también (ON DELETE CASCADE).
