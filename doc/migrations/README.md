# Migraciones de base de datos

Scripts para actualizar una base **ya instalada**. La instalación nueva usa `barberia_db.sql` en la raíz del proyecto (incluye el esquema completo).

## Ejecutar migraciones

Desde la raíz del proyecto:

```bash
php doc/migrations/run.php
```

Solo una migración (por número o nombre):

```bash
php doc/migrations/run.php 001
```

## Archivos

| Archivo | Descripción |
|---------|-------------|
| [001_marca_saas.sql](001_marca_saas.sql) | Claves `nombre_negocio` y `logo_url` en `configuracion` |
| [run.php](run.php) | Ejecutor en orden alfabético de `*.sql` |

## Seeds (datos de prueba)

No son migraciones de esquema; cargan datos de ejemplo:

```bash
php doc/migrations/seeds/barberia-demo.php
```

## Añadir una migración nueva

1. Crear `00X_descripcion.sql` en esta carpeta (prefijo numérico para el orden).
2. Ejecutar `php doc/migrations/run.php`.
3. Documentar la fila en la tabla de arriba.
