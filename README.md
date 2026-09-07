# Academia de Fútbol - Sistema de Gestión

Sistema de gestión para academia de fútbol: alumnos, pagos, carnés QR, asistencias y modo offline para profesores.

**Stack:** Laravel 11+ · MySQL 8.0 · Blade/Livewire · PWA (profesor)

## Documentación

- [`docs/Diseno_Base_Datos_Academia_Futbol.md`](docs/Diseno_Base_Datos_Academia_Futbol.md) — diseño completo de la BD (tablas, relaciones, índices, vistas).
- [`docs/Roadmap_Produccion_Academia_Futbol.md`](docs/Roadmap_Produccion_Academia_Futbol.md) — plan de fases, cronograma, checklist de seguridad.
- [`database/schema.sql`](database/schema.sql) — script SQL de las 9 tablas propias + vistas (ya importado en la BD local).

## Estado actual

✅ **Fase 0: Preparación del entorno** — completa.

- Proyecto Laravel 11 instalado.
- BD `academia_futbol` creada en MySQL 8.0 (servicio `MYSQL80`), schema importado con datos semilla (planes + admin).
- Paquetes instalados: `spatie/laravel-permission`, `barryvdh/laravel-dompdf`, `simplesoftwareio/simple-qrcode`.
- Tablas de Spatie Permission migradas (roles/permisos).

📍 Siguiente: **Fase 1 — Módulo de Alumnos + Auth** (ver roadmap).

### Notas importantes de configuración

- Este proyecto **no usa** la tabla `users` por defecto de Laravel. El auth vive en la tabla `usuarios` (columna `rol`: admin/profesor/padre) definida en el diseño de BD. El modelo `User` se remapea a `usuarios` en Fase 1.
- Credenciales de BD en `.env` (no versionado). Motor: MySQL 8.0 real (no MariaDB de XAMPP) — ver servicio Windows `MYSQL80`.
- Admin semilla: `admin@academia.com` / `Cambiar2024!` — **cambiar en cuanto exista login funcional**.

## Setup local (para otro desarrollador)

```bash
composer install
cp .env.example .env
php artisan key:generate
# Configurar DB_* en .env apuntando a tu MySQL 8.0
mysql -u root -p < database/schema.sql
php artisan migrate --force
```
