# Academia de Fútbol - Sistema de Gestión

Sistema de gestión para academia de fútbol: alumnos, pagos, carnés QR, asistencias y modo offline para profesores.

**Stack:** Laravel 11+ · MySQL 8.0 · Blade/Livewire · PWA (profesor)

## Documentación

- [`docs/Diseno_Base_Datos_Academia_Futbol.md`](docs/Diseno_Base_Datos_Academia_Futbol.md) — diseño completo de la BD (tablas, relaciones, índices, vistas).
- [`docs/Roadmap_Produccion_Academia_Futbol.md`](docs/Roadmap_Produccion_Academia_Futbol.md) — plan de fases, cronograma, checklist de seguridad.
- [`database/schema.sql`](database/schema.sql) — script SQL listo para ejecutar en MySQL Workbench o CLI.

## Estado actual

📍 **Fase 0: Preparación del entorno** — en curso.

## Setup rápido (próximos pasos)

```bash
composer create-project laravel/laravel .
composer require spatie/laravel-permission barryvdh/laravel-dompdf simplesoftwareio/simple-qrcode laravel/breeze --dev
cp .env.example .env
php artisan key:generate
```

Configurar `.env` con las credenciales de MySQL y luego importar `database/schema.sql`, o convertirlo a migraciones Laravel (ver tabla de equivalencias en el documento de diseño).
