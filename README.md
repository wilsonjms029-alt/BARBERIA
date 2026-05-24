# AlCorte — Barbería

Sistema de reservas con **backend** y **frontend** separados. Las URLs en el navegador no cambian gracias a `.htaccess`.

## Estructura

```
.barberia/
├── backend/
│   ├── bootstrap.php      # Carga la base de datos
│   ├── config/
│   │   └── db.php         # Conexión PDO (MySQL)
│   └── api/
│       ├── auth.php       # Login / registro (JSON)
│       ├── horarios.php   # Horas disponibles (JSON)
│       ├── citas.php      # Crear reserva (POST)
│       └── red-test.php   # Prueba de red móvil (JSON)
├── frontend/
│   ├── bootstrap.php      # Incluye el backend
│   ├── index.php          # Reserva de citas
│   ├── login.php, admin.php, mis_cortes.php, …
│   └── assets/
│       ├── app.js
│       ├── styles.css
│       └── manifest.json
├── doc/                   # Documentación y migraciones
│   └── migrations/        # SQL incremental + seeds
├── barberia_db.sql
└── .htaccess              # Enruta /index.php → frontend, /api/* → backend
```

## URLs

| Uso | URL (ejemplo Laragon) |
|-----|------------------------|
| Reservas | `http://localhost/.barberia/index.php` |
| API auth | `http://localhost/.barberia/api/auth.php` |
| API horarios | `http://localhost/.barberia/api/horarios.php` |
| Admin | `http://localhost/.barberia/admin.php` |
| Acceso móvil (QR) | `http://localhost/.barberia/acceso-movil.php` |

Rutas antiguas (`api_auth.php`, `obtener_horarios.php`, `procesar_cita.php`) siguen funcionando por compatibilidad.

## Requisitos

- PHP 8+ con PDO MySQL
- Apache con `mod_rewrite` y `AllowOverride All` (Laragon por defecto)
- Base de datos `barberia_db` (importar `barberia_db.sql`)

### Marca SaaS (logo de la barbería)

En **Admin → Configuración** cada cliente sube su **logo** y **nombre del negocio**. En la app del cliente se ve:

`[Logo] Nombre Barbería` + **AlCorte** (plataforma, siempre visible).

Si la base ya existía, ejecuta las migraciones: `php doc/migrations/run.php` ([doc/migrations/README.md](doc/migrations/README.md)).

Más detalles en [doc/README.md](doc/README.md).
