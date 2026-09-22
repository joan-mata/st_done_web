# 🛍️ TechShop — Tienda Online

Práctica de Programació Web · **Serveis de Telecomunicacions** · Grado en Sistemas de Telecomunicaciones · UAB

---

## Descripción

Aplicación web de una tienda online sencilla construida con **PHP + PostgreSQL**, siguiendo la arquitectura **MVC** y las buenas prácticas del curso.

**Páginas principales:**
- **Catálogo** — listado de productos con filtro por categoría
- **Detalle de producto** — descripción, precio y botón "añadir al carrito"
- **Carrito** — gestión de artículos y resumen de la compra
- **Checkout** — dirección de envío y confirmación del pedido
- **Mi cuenta** — registro, login y historial de pedidos

---

## Estructura del proyecto

```
ST_done_web/
├── index.php                 ← Front Controller (punto de entrada único)
├── .env.example              ← Plantilla de variables de entorno
├── .htaccess                 ← Seguridad Apache
│
├── config/
│   └── database.php          ← Conexión PDO singleton
│
├── models/
│   ├── Producto.php
│   ├── Usuario.php
│   └── Pedido.php
│
├── controllers/
│   ├── ProductoController.php
│   ├── UsuarioController.php
│   ├── CarritoController.php
│   └── PedidoController.php
│
├── views/
│   ├── layout/               ← header.php y footer.php compartidos
│   ├── catalogo.php
│   ├── detalle_producto.php
│   ├── carrito.php
│   ├── checkout.php
│   ├── confirmacion.php
│   ├── login.php
│   ├── registro.php
│   ├── perfil.php
│   └── error.php
│
├── css/estilos.css
├── js/tienda.js
│
└── db/
    ├── init.sql              ← Schema + datos de prueba
    ├── seed_usuarios.php     ← Inserta usuarios con contraseñas hasheadas
    └── uml_schema.md         ← Diagrama UML de la base de datos
```

---

## Puesta en marcha

### 1. Requisitos

- PHP 8.1+
- PostgreSQL 14+
- Servidor web Apache (XAMPP, servidor UAB, etc.)

### 2. Base de datos

```bash
# Crear la base de datos
psql -U postgres -c "CREATE DATABASE tienda_uab;"

# Crear tablas e insertar productos
psql -U postgres -d tienda_uab -f db/init.sql
```

### 3. Variables de entorno

```bash
cp .env.example .env
```

Edita `.env` con tus credenciales:

```
DB_HOST=localhost
DB_PORT=5432
DB_NAME=tienda_uab
DB_USER=postgres
DB_PASSWORD=tu_password
```

> **Importante:** el fichero `.env` está en `.gitignore`. Nunca lo subas al repositorio.

### 4. Insertar usuarios de prueba

```bash
php db/seed_usuarios.php
```

Esto crea tres usuarios con contraseñas correctamente hasheadas con bcrypt:

| Email | Contraseña | Rol |
|---|---|---|
| admin@tienda.com | Admin123! | admin |
| juan@example.com | Cliente123! | cliente |
| maria@example.com | Cliente123! | cliente |

### 5. Arrancar

Coloca la carpeta en `htdocs/` (XAMPP) o en tu carpeta de trabajo del servidor UAB y accede desde el navegador:

```
http://localhost/ST_done_web/
```

---

## Base de datos

### Diagrama UML

```
┌─────────────┐       ┌──────────────────────┐       ┌─────────────┐
│  categorias │ 1   N │       productos       │       │   usuarios  │
│─────────────│───────│──────────────────────│       │─────────────│
│ id          │       │ id                   │       │ id          │
│ nombre      │       │ nombre               │       │ nombre      │
└─────────────┘       │ descripcion          │       │ email       │
                      │ precio               │       │ password_hash│
                      │ stock                │       │ rol         │
                      │ icono                │       │ created_at  │
                      │ categoria_id (FK)    │       └──────┬──────┘
                      │ activo               │              │ 1
                      │ created_at           │              │
                      └──────────┬───────────┘              │ N
                                 │ N                  ┌──────▼──────┐
                                 │                    │   pedidos   │
                                 │              ┌─────│─────────────│
                                 │              │     │ id          │
                                 │ N            │     │ usuario_id  │
                         ┌───────▼──────────────▼─┐  │ total       │
                         │      lineas_pedido      │  │ estado      │
                         │─────────────────────────│  │ direccion   │
                         │ id                      │  │ created_at  │
                         │ pedido_id (FK)          │  └─────────────┘
                         │ producto_id (FK)        │
                         │ cantidad                │
                         │ precio_unitario         │
                         └─────────────────────────┘
```

Ver descripción completa en [`db/uml_schema.md`](db/uml_schema.md).

---

## Seguridad

| Amenaza | Solución aplicada |
|---|---|
| SQL Injection | PDO con prepared statements en todas las consultas |
| XSS | `htmlspecialchars()` en todas las vistas |
| CSRF | Token aleatorio en sesión, verificado en cada POST |
| Contraseñas | `password_hash()` bcrypt, coste 12 |
| Session fixation | `session_regenerate_id()` al iniciar sesión |
| Credenciales expuestas | Fichero `.env` excluido de git |
| Acceso directo a PHP | `.htaccess` deniega acceso a `config/`, `models/`, `controllers/`, `db/` |

---

## Arquitectura MVC

```
Usuario → index.php (router) → Controller → Model → DB
                                     ↓
                                   View → HTML al navegador
```

- **Model** — solo habla con la base de datos. Sin HTML.
- **View** — solo genera HTML. Sin SQL.
- **Controller** — recibe la petición, llama al modelo, carga la vista.

---

## Tecnologías

- **PHP 8.1** — lógica del servidor
- **PostgreSQL** — base de datos relacional
- **PDO** — conexión segura a la BD
- **HTML5 / CSS3** — interfaz de usuario (sin frameworks)
- **JavaScript** — mejoras de UX (sin frameworks)

---

*Práctica de Programació Web · Serveis de Telecomunicacions · Prof. Joan Mata*
