# 🚀 Roadmap de Producción - Sistema Academia de Fútbol

**Stack:** PHP Laravel + MySQL (Workbench)  
**Fecha:** Septiembre 2026  
**Versión:** 1.0  

---

## 📑 Índice
1. [Stack Tecnológico Recomendado](#stack-tecnológico-recomendado)
2. [Fases de Desarrollo](#fases-de-desarrollo)
3. [Cronograma Detallado](#cronograma-detallado)
4. [Checklist de Seguridad](#checklist-de-seguridad)
5. [Herramientas de Productividad](#herramientas-de-productividad)
6. [Configuración de Entornos](#configuración-de-entornos)
7. [Checklist Pre-Producción Final](#checklist-pre-producción-final)

---

## 🛠️ Stack Tecnológico Recomendado

### Core

| Capa | Tecnología | Justificación |
|------|-----------|---------------|
| **Backend** | Laravel 11+ | Framework PHP robusto, ORM Eloquent para MySQL, middleware de roles, queues para tareas pesadas |
| **Base de Datos** | MySQL 8.0 (Workbench) | Soporte nativo en Laravel, transacciones ACID, buena documentación |
| **Frontend Admin** | Blade + Livewire | Sin necesidad de SPA separada para el panel admin. Livewire da interactividad sin escribir JS puro |
| **Frontend Padre** | Blade + Alpine.js | Ligero, rápido de cargar en móviles con datos limitados |
| **App Profesor** | PWA (Progressive Web App) | Funciona offline con service workers, se instala como app nativa sin publicar en stores |
| **QR** | `simplesoftwareio/simple-qrcode` | Paquete Laravel nativo para generar QR |
| **PDF** | `barryvdh/laravel-dompdf` | Genera PDFs desde Blade templates |
| **Auth** | Laravel Breeze o Fortify | Autenticación lista con roles. Breeze si quieres UI incluida, Fortify si solo backend |
| **Roles** | `spatie/laravel-permission` | Estándar de la industria para roles y permisos en Laravel |
| **Cache Offline** | IndexedDB + Service Worker | Para la PWA del profesor |
| **Servidor** | VPS (DigitalOcean / Hetzner) | Más control que shared hosting. Laravel Forge para automatizar deploy |

### Herramientas Complementarias

| Herramienta | Uso | Por qué |
|------------|-----|---------|
| **Laravel Forge** | Deploy automático | Configura servidor, SSL, queues, cron en 1 clic |
| **Laravel Horizon** | Monitor de queues | Visualiza jobs en cola (emails, sincronización offline) |
| **Laravel Telescope** | Debug en desarrollo | Ve queries lentas, requests, logs en tiempo real |
| **Laravel Pint** | Formato de código | Mantiene el código limpio y consistente |
| **PHPStan / Larastan** | Análisis estático | Detecta bugs antes de ejecutar el código |
| **Pest PHP** | Testing | Tests más legibles que PHPUnit |
| **GitHub Actions** | CI/CD | Corre tests automáticamente en cada push |
| **Sentry** | Monitoreo de errores | Te avisa cuando algo falla en producción |
| **MySQL Workbench** | Diseño BD | Modelado visual del esquema, exporta SQL directo |

---

## 📋 Fases de Desarrollo

---

### **FASE 0: Preparación del Entorno** ⏱️ 1 semana

> Antes de escribir una sola línea de código de negocio.

| # | Tarea | Detalle |
|---|-------|---------|
| 0.1 | Instalar Laravel | `composer create-project laravel/laravel academia-futbol` |
| 0.2 | Configurar MySQL | Crear BD en Workbench, configurar `.env` con credenciales |
| 0.3 | Instalar paquetes base | `spatie/permission`, `dompdf`, `simple-qrcode`, `laravel-breeze` |
| 0.4 | Configurar Git | Repo en GitHub/GitLab, `.gitignore` con `.env`, `vendor/`, `node_modules/` |
| 0.5 | Crear estructura de carpetas | Policies, Services, Requests, Enums, DTOs |
| 0.6 | Configurar entornos | `.env.local`, `.env.staging`, `.env.production` |
| 0.7 | Diseñar BD en Workbench | Exportar SQL → Convertir a migraciones Laravel |
| 0.8 | Crear seeders base | Roles (admin, profesor, padre), planes iniciales, admin por defecto |

**Entregable:** Proyecto Laravel funcional con BD conectada, auth básica y roles creados.

---

### **FASE 1: Módulo de Alumnos + Auth** ⏱️ 2 semanas

> El corazón del sistema. Sin alumnos no hay nada.

| # | Tarea | Módulo | Prioridad |
|---|-------|--------|-----------|
| 1.1 | Migraciones: `users`, `alumnos` | Alumnos | CRÍTICA |
| 1.2 | Modelo `User` con roles (Spatie) | Auth | CRÍTICA |
| 1.3 | Modelo `Alumno` con UUID automático | Alumnos | CRÍTICA |
| 1.4 | Relación `User hasMany Alumnos` (padre → hijos) | Alumnos | CRÍTICA |
| 1.5 | CRUD Alumnos (admin) | Alumnos | CRÍTICA |
| 1.6 | Cálculo automático de categoría por fecha nacimiento | Alumnos | ALTA |
| 1.7 | Campo `alergias_enfermedades` con validación | Alumnos | ALTA |
| 1.8 | Upload de ficha médica PDF (solo admin ve) | Alumnos | ALTA |
| 1.9 | Registro de padre (auto-registro o admin lo crea) | Auth | ALTA |
| 1.10 | Middleware por rol (`admin`, `profesor`, `padre`) | Auth | CRÍTICA |
| 1.11 | Form Requests con validación server-side | Alumnos | ALTA |
| 1.12 | Tests: crear alumno, validar UUID, calcular categoría | Testing | MEDIA |

**Archivos Laravel clave:**
```
app/Models/User.php          → Roles con Spatie
app/Models/Alumno.php         → UUID auto, categoría calculada
app/Http/Controllers/AlumnoController.php
app/Http/Requests/StoreAlumnoRequest.php
app/Policies/AlumnoPolicy.php → Quién puede ver/editar cada alumno
database/migrations/create_alumnos_table.php
```

**Entregable:** Admin puede crear alumnos, padres ven solo sus hijos, UUID generado automáticamente.

---

### **FASE 2: Módulo de Pagos + Planes** ⏱️ 2-3 semanas

> La lógica más compleja del sistema. Hacerla bien aquí evita meses de bugs.

| # | Tarea | Módulo | Prioridad |
|---|-------|--------|-----------|
| 2.1 | Migraciones: `planes`, `pagos`, `auditoria_pagos` | Pagos | CRÍTICA |
| 2.2 | Modelo `Plan` (mensual, pack, trimestral) | Pagos | ALTA |
| 2.3 | Modelo `Pago` con lógica de expiración inteligente | Pagos | CRÍTICA |
| 2.4 | **Service `PagoService`** con regla de negocio: | Pagos | CRÍTICA |
|     | → Si al día: `fecha_expiracion_anterior + dias_plan` | | |
|     | → Si moroso: `hoy + dias_plan` | | |
| 2.5 | Registro de pago: monto, método, admin responsable | Pagos | ALTA |
| 2.6 | Estado del alumno calculado: Verde/Ámbar/Rojo | Pagos | ALTA |
| 2.7 | Vista admin: listado de pagos con filtros | Pagos | ALTA |
| 2.8 | Vista padre: ver estado de pago de sus hijos | Pagos | MEDIA |
| 2.9 | Auditoría automática (Observer en modelo Pago) | Pagos | MEDIA |
| 2.10 | Tests: lógica expiración al día, lógica moroso, pack sesiones | Testing | CRÍTICA |

**Archivos Laravel clave:**
```
app/Services/PagoService.php          → ⭐ TODA la lógica de expiración aquí
app/Models/Pago.php
app/Models/Plan.php
app/Observers/PagoObserver.php        → Registra auditoría automática
app/Enums/EstadoPago.php              → confirmado, pendiente, rechazado
app/Enums/MetodoPago.php              → efectivo, transferencia, tarjeta
app/Http/Controllers/PagoController.php
```

**Punto crítico:** La lógica de expiración NO va en el controller. Va en un Service dedicado con tests unitarios. Si esta lógica falla, todo el sistema falla.

```php
// app/Services/PagoService.php (estructura conceptual)
class PagoService
{
    public function registrarPago(Alumno $alumno, Plan $plan, array $datos): Pago
    {
        $fechaExpiracionActual = $alumno->fecha_expiracion;
        
        if ($fechaExpiracionActual && $fechaExpiracionActual >= now()) {
            // AL DÍA → suma desde la expiración anterior
            $nuevaExpiracion = $fechaExpiracionActual->addDays($plan->duracion_dias);
        } else {
            // MOROSO → suma desde hoy (no regala días)
            $nuevaExpiracion = now()->addDays($plan->duracion_dias);
        }
        
        // Crear pago, actualizar alumno, generar auditoría...
    }
}
```

**Entregable:** Admin registra pagos, sistema calcula expiración correctamente, padre ve su estado.

---

### **FASE 3: Módulo QR + Carné PDF** ⏱️ 1-2 semanas

> Depende de Fase 2. El QR solo se genera si hay pago confirmado.

| # | Tarea | Módulo | Prioridad |
|---|-------|--------|-----------|
| 3.1 | Migración: `carnes_qr` | QR | ALTA |
| 3.2 | Generar QR con UUID encriptado (`Crypt::encryptString`) | QR | CRÍTICA |
| 3.3 | Trigger: generar QR automáticamente al confirmar primer pago | QR | CRÍTICA |
| 3.4 | Template Blade para PDF del carné | QR | ALTA |
| 3.5 | PDF incluye: nombre, categoría, foto, QR, fecha expiración en grande | QR | ALTA |
| 3.6 | Nota en PDF: "Válido hasta [FECHA]. Estado final se confirma con QR." | QR | ALTA |
| 3.7 | Ruta de descarga protegida (solo padre del alumno o admin) | QR | ALTA |
| 3.8 | Regenerar PDF en cada descarga (fecha siempre actualizada) | QR | MEDIA |
| 3.9 | Versionado de QR (v1, v2...) | QR | BAJA |

**Archivos Laravel clave:**
```
app/Services/CarneService.php         → Genera QR + PDF
resources/views/pdf/carne.blade.php   → Template del carné
app/Http/Controllers/CarneController.php
```

**Entregable:** Al pagar, se genera QR. Padre descarga PDF con fecha de expiración. QR contiene solo UUID encriptado.

---

### **FASE 4: Módulo Escaneo + Asistencias** ⏱️ 2 semanas

> La app del profesor. Donde todo se junta.

| # | Tarea | Módulo | Prioridad |
|---|-------|--------|-----------|
| 4.1 | Migración: `asistencias` | Asistencias | ALTA |
| 4.2 | API endpoint: `POST /api/escanear/{uuid_encriptado}` | Escaneo | CRÍTICA |
| 4.3 | Desencriptar UUID → buscar alumno → validar estado | Escaneo | CRÍTICA |
| 4.4 | Respuesta JSON diferenciada por rol: | Escaneo | CRÍTICA |
|     | → Profesor: nombre, categoría, estado (color), alergias | | |
|     | → Admin: todo lo anterior + monto deuda | | |
| 4.5 | Registrar asistencia automáticamente al escanear | Asistencias | ALTA |
| 4.6 | Campo `origen`: escaneo_qr o manual | Asistencias | MEDIA |
| 4.7 | Vista profesor: escáner QR (cámara del móvil) | Escaneo | ALTA |
| 4.8 | Vista profesor: lista de asistencia del día | Asistencias | MEDIA |
| 4.9 | Evitar duplicados: mismo alumno no puede marcar 2 veces en 1 hora | Asistencias | ALTA |
| 4.10 | API: `GET /api/alumnos-dia` (descarga lista para offline) | Offline | ALTA |

**Archivos Laravel clave:**
```
app/Http/Controllers/Api/EscaneoController.php
app/Http/Controllers/Api/AsistenciaController.php
app/Http/Resources/EscaneoProfesorResource.php   → Filtra datos por rol
app/Http/Resources/EscaneoAdminResource.php
```

**Para el escáner QR en el navegador del profesor:**
```
// Librería JS recomendada: html5-qrcode
// Se integra en la vista Blade del profesor
// npm install html5-qrcode
```

**Entregable:** Profesor escanea QR → ve estado en colores → asistencia registrada automáticamente.

---

### **FASE 5: Módulo Offline (PWA)** ⏱️ 2 semanas

> Convierte la app del profesor en una PWA que funciona sin internet.

| # | Tarea | Módulo | Prioridad |
|---|-------|--------|-----------|
| 5.1 | Crear `manifest.json` para PWA | Offline | ALTA |
| 5.2 | Crear Service Worker con Workbox | Offline | ALTA |
| 5.3 | Endpoint `/api/sync/descargar` → devuelve alumnos del día con estado | Offline | CRÍTICA |
| 5.4 | Almacenar datos en IndexedDB (Dexie.js) | Offline | ALTA |
| 5.5 | Escaneo offline: validar UUID contra IndexedDB | Offline | ALTA |
| 5.6 | Cola de asistencias pendientes en IndexedDB | Offline | ALTA |
| 5.7 | Endpoint `/api/sync/subir` → recibe asistencias offline acumuladas | Offline | ALTA |
| 5.8 | Sincronización automática al detectar conexión (`navigator.onLine`) | Offline | ALTA |
| 5.9 | Indicador visual: "Última sync: hace 2 horas" | Offline | MEDIA |
| 5.10 | Migración: `configuracion_offline` | Offline | MEDIA |

**Archivos clave:**
```
public/manifest.json
public/sw.js                          → Service Worker (Workbox)
resources/js/offline/db.js            → Dexie.js para IndexedDB
resources/js/offline/sync.js          → Lógica de sincronización
app/Http/Controllers/Api/SyncController.php
```

**Entregable:** Profesor instala PWA, descarga datos al inicio del día, escanea sin internet, sincroniza al volver a tener conexión.

---

### **FASE 6: Módulo de Reportes** ⏱️ 1-2 semanas

| # | Tarea | Módulo | Prioridad |
|---|-------|--------|-----------|
| 6.1 | Reporte deudores con filtros (fecha corte + categoría) | Reportes | ALTA |
| 6.2 | Columnas: alumno, categoría, días de mora, última asistencia, contacto padre | Reportes | ALTA |
| 6.3 | Reporte de asistencias por período | Reportes | MEDIA |
| 6.4 | Reporte de ingresos por método de pago | Reportes | MEDIA |
| 6.5 | Exportar a Excel (`maatwebsite/excel`) | Reportes | MEDIA |
| 6.6 | Dashboard KPI: alumnos activos, tasa morosidad, ingresos mes | Reportes | BAJA |

**Entregable:** Admin genera reportes filtrados, exporta a Excel, ve dashboard.

---

### **FASE 7: Testing, QA y Hardening** ⏱️ 2 semanas

| # | Tarea | Tipo |
|---|-------|------|
| 7.1 | Tests unitarios: lógica expiración (mínimo 10 casos) | Unit |
| 7.2 | Tests feature: flujo registro → pago → QR → escaneo | Feature |
| 7.3 | Tests de autorización: profesor no accede a pagos, padre no ve otros hijos | Auth |
| 7.4 | Test de carga: 100 escaneos simultáneos | Performance |
| 7.5 | Penetration testing básico (OWASP Top 10) | Security |
| 7.6 | Revisión de queries N+1 (Telescope) | Performance |
| 7.7 | Validar que no hay rutas expuestas sin middleware | Security |
| 7.8 | Code review completo | Quality |

---

### **FASE 8: Deploy a Producción** ⏱️ 1 semana

| # | Tarea | Detalle |
|---|-------|---------|
| 8.1 | Contratar VPS | DigitalOcean ($12/mes) o Hetzner ($5/mes) |
| 8.2 | Configurar con Laravel Forge | Nginx, PHP 8.3, MySQL 8, SSL, queues |
| 8.3 | Migrar BD | `php artisan migrate --force` |
| 8.4 | Seeders de producción | Roles, planes, admin inicial |
| 8.5 | Configurar dominio + SSL (Let's Encrypt) | HTTPS obligatorio |
| 8.6 | Configurar backups automáticos | BD cada 6 horas, archivos diario |
| 8.7 | Configurar Sentry | Monitoreo de errores en tiempo real |
| 8.8 | Configurar cron de Laravel | `php artisan schedule:run` |
| 8.9 | Smoke test en producción | Flujo completo: registro → pago → QR → escaneo |
| 8.10 | Capacitación a usuarios | Admin y profesores |

---

## 📅 Cronograma Detallado

```
SEMANA   FASE                              ENTREGABLE
─────────────────────────────────────────────────────────────────
  S1     Fase 0: Entorno + BD              Proyecto configurado, BD diseñada
─────────────────────────────────────────────────────────────────
  S2     Fase 1: Alumnos + Auth            CRUD alumnos, roles, UUID
  S3     Fase 1: Alumnos + Auth            Validaciones, ficha médica, tests
─────────────────────────────────────────────────────────────────
  S4     Fase 2: Pagos + Planes            Modelos, service de expiración
  S5     Fase 2: Pagos + Planes            Vistas admin/padre, auditoría
  S6     Fase 2: Pagos + Planes (buffer)   Tests lógica expiración
─────────────────────────────────────────────────────────────────
  S7     Fase 3: QR + Carné PDF            QR generado, PDF dinámico
  S8     Fase 3: QR + Carné (buffer)       Descarga protegida, versionado
─────────────────────────────────────────────────────────────────
  S9     Fase 4: Escaneo + Asistencias     API escaneo, vista profesor
  S10    Fase 4: Escaneo + Asistencias     Asistencia automática, duplicados
─────────────────────────────────────────────────────────────────
  S11    Fase 5: Offline (PWA)             Service Worker, IndexedDB
  S12    Fase 5: Offline (PWA)             Sync, indicador estado
─────────────────────────────────────────────────────────────────
  S13    Fase 6: Reportes                  Deudores, asistencias, exportar
  S14    Fase 6: Reportes (buffer)         Dashboard KPI
─────────────────────────────────────────────────────────────────
  S15    Fase 7: Testing + QA              Tests completos, seguridad
  S16    Fase 7: Testing + QA              Fixes, optimización
─────────────────────────────────────────────────────────────────
  S17    Fase 8: Deploy                    Servidor, dominio, SSL
  S18    Fase 8: Capacitación + Go Live    Usuarios capacitados, sistema en vivo
─────────────────────────────────────────────────────────────────

TOTAL: ~18 semanas (4.5 meses) trabajando a ritmo constante
       ~14 semanas si trabajas full-time y sin buffers
```

### Diagrama Visual del Cronograma

```
MES 1        MES 2        MES 3        MES 4        MES 5
|████████████|████████████|████████████|████████████|██████------|
 F0  F1       F2           F3   F4      F5    F6     F7  F8
 ▲            ▲                  ▲             ▲          ▲
 BD lista     Pagos OK     Escaneo OK   Reportes  PRODUCCIÓN
```

---

## 🔒 Checklist de Seguridad

### Nivel 1: Obligatorio antes de producción

| # | Control | Implementación en Laravel | Estado |
|---|---------|--------------------------|--------|
| ✅ | **Rate limiting activo** | `Route::middleware('throttle:60,1')` en rutas API. En `RouteServiceProvider` o en `bootstrap/app.php` (L11). Para login: `throttle:5,1` (5 intentos/min). | ⬜ |
| ✅ | **API keys en backend, nunca en frontend** | Todo en `.env`. Nunca pasar claves a JavaScript. Si necesitas datos en JS, hazlo via endpoint protegido. | ⬜ |
| ✅ | **Variables de entorno protegidas** | `.env` en `.gitignore`. Nunca commitear credenciales. Usar `php artisan env:encrypt` en L11 para encriptar `.env` en producción. | ⬜ |
| ✅ | **Validación de inputs en TODOS los endpoints** | Usar Form Requests: `StoreAlumnoRequest`, `StorePagoRequest`, etc. Nunca confiar en `$request->all()`. Siempre `$request->validated()`. | ⬜ |
| ✅ | **Rutas realmente protegidas** | Middleware `auth` + `role:admin` en rutas admin. Middleware `auth:sanctum` en API. Policies en cada controller: `$this->authorize('view', $alumno)`. | ⬜ |
| ✅ | **Errores que no expongan info interna** | `APP_DEBUG=false` en producción. Handler de excepciones personalizado que devuelve mensajes genéricos. Nunca mostrar stack traces al usuario. | ⬜ |
| ✅ | **Endpoints debug desactivados en producción** | Desinstalar Telescope en prod (o protegerlo con IP/auth). No exponer `/api/test`, `phpinfo()`, ni rutas de prueba. | ⬜ |
| ✅ | **Logs para detectar ataques** | Loguear: intentos de login fallidos, escaneos de QR inválidos, accesos denegados por policy. Usar canales de log separados. Rotar logs diariamente. | ⬜ |
| ✅ | **HTTPS obligatorio** | `APP_URL=https://...` en `.env`. Middleware `\Illuminate\Http\Middleware\RequireHttps` o forzar en Nginx. Redirigir HTTP → HTTPS. | ⬜ |
| ✅ | **CSRF en formularios** | Laravel lo hace por defecto con `@csrf`. Verificar que NINGÚN formulario lo omita. En API usar Sanctum tokens en vez de CSRF. | ⬜ |

### Nivel 2: Protección de datos de menores

| # | Control | Implementación | Estado |
|---|---------|---------------|--------|
| ✅ | **UUID en vez de DNI en URLs** | `Route::get('/alumno/{alumno:uuid}')` usando route model binding por UUID. Nunca `/alumno/12345678A`. | ⬜ |
| ✅ | **QR solo contiene UUID encriptado** | `Crypt::encryptString($alumno->uuid)` al generar. `Crypt::decryptString()` al escanear. Si falla → log de intento fraudulento. | ⬜ |
| ✅ | **Ficha médica solo para admin** | Policy: solo role `admin` puede acceder a `ficha_medica_pdf`. Storage en `storage/app/private/fichas/` (no público). | ⬜ |
| ✅ | **Profesor no ve deudas en dinero** | API Resource separado por rol: `EscaneoProfesorResource` (sin monto) vs `EscaneoAdminResource` (con monto). | ⬜ |
| ✅ | **Padre solo ve sus hijos** | Policy: `$user->id === $alumno->padre_id`. Scope global: `Alumno::where('padre_id', auth()->id())`. | ⬜ |
| ✅ | **Datos encriptados en caché offline** | IndexedDB encriptado con Web Crypto API. Al cerrar sesión del profesor, borrar caché local. | ⬜ |

### Nivel 3: Infraestructura

| # | Control | Implementación | Estado |
|---|---------|---------------|--------|
| ✅ | **Backups automáticos de BD** | Paquete `spatie/laravel-backup`. Programar cada 6 horas. Guardar en S3 o disco externo. Probar restauración mensualmente. | ⬜ |
| ✅ | **Firewall del servidor** | UFW: solo puertos 22 (SSH), 80, 443 abiertos. Cambiar puerto SSH del default. Deshabilitar login root por SSH. | ⬜ |
| ✅ | **Actualizar dependencias** | `composer audit` periódicamente. Renovar paquetes con vulnerabilidades conocidas. Suscribirse a advisories de Laravel. | ⬜ |
| ✅ | **Monitoreo de uptime** | UptimeRobot (gratis) o Better Uptime. Alerta si el servidor cae. | ⬜ |
| ✅ | **Headers de seguridad HTTP** | Configurar en Nginx: `X-Frame-Options`, `X-Content-Type-Options`, `Strict-Transport-Security`, `Content-Security-Policy`. | ⬜ |
| ✅ | **Limitar tamaño de uploads** | `upload_max_filesize` en php.ini. Validar en Laravel: `'ficha_medica' => 'file|mimes:pdf|max:5120'` (5MB max). | ⬜ |

### Nivel 4: Base de datos MySQL

| # | Control | Implementación | Estado |
|---|---------|---------------|--------|
| ✅ | **Usuario MySQL dedicado** | No usar `root` para la app. Crear usuario con permisos solo sobre la BD de la academia. Sin `GRANT`, `DROP DATABASE`. | ⬜ |
| ✅ | **Prepared statements** | Laravel Eloquent los usa por defecto. Nunca usar `DB::raw()` con input del usuario sin bindings. | ⬜ |
| ✅ | **Soft deletes** | Nunca borrar registros de alumnos o pagos. Usar `SoftDeletes` de Laravel para "archivar". | ⬜ |
| ✅ | **Índices en columnas de búsqueda** | Índices en: `uuid`, `dni`, `fecha_expiracion`, `padre_id`, `estado`. Verificar con `EXPLAIN` en queries lentas. | ⬜ |
| ✅ | **Contraseñas hasheadas** | Laravel usa bcrypt por defecto en `Hash::make()`. Nunca almacenar passwords en texto plano. | ⬜ |

---

## ⚡ Herramientas de Productividad

### Durante el desarrollo

| Herramienta | Qué resuelve | Cuándo usarla |
|------------|-------------|---------------|
| **Laravel Debugbar** | Ver queries, tiempo de carga, memory en cada request | Desarrollo. Detectar queries N+1 antes de que sean problema |
| **Telescope** | Dashboard de requests, jobs, logs, mails, cache | Desarrollo y staging. Desactivar en producción |
| **Tinker** (`artisan tinker`) | Probar lógica rápido en consola | Cuando necesitas probar un Service sin montar toda la request |
| **Pest** | Tests legibles y rápidos | Testear lógica de expiración, policies, validaciones |
| **Pint** | Formateo automático del código | Correr antes de cada commit: `./vendor/bin/pint` |
| **IDE Helper** (`barryvdh/ide-helper`) | Autocomplete de modelos en VS Code/PHPStorm | Al crear o modificar modelos |

### Comandos artisan que vas a usar constantemente

```bash
# Migraciones
php artisan make:migration create_alumnos_table
php artisan migrate
php artisan migrate:rollback

# Modelos con todo incluido
php artisan make:model Alumno -mfsc
# -m = migration, -f = factory, -s = seeder, -c = controller

# Validación
php artisan make:request StoreAlumnoRequest

# Policies (autorización)
php artisan make:policy AlumnoPolicy --model=Alumno

# Tests
php artisan make:test PagoServiceTest --unit
php artisan make:test FlujoRegistroPagoTest

# Producción
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Estructura de carpetas recomendada

```
app/
├── Enums/
│   ├── EstadoPago.php          → confirmado, pendiente, rechazado
│   ├── MetodoPago.php          → efectivo, transferencia, tarjeta
│   ├── CategoriaAlumno.php     → pre_benjamin, benjamin, alevin...
│   └── RolUsuario.php          → admin, profesor, padre
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── AlumnoController.php
│   │   │   ├── PagoController.php
│   │   │   └── ReporteController.php
│   │   ├── Profesor/
│   │   │   ├── EscaneoController.php
│   │   │   └── AsistenciaController.php
│   │   ├── Padre/
│   │   │   ├── HijoController.php
│   │   │   └── EstadoPagoController.php
│   │   └── Api/
│   │       ├── EscaneoApiController.php
│   │       └── SyncController.php
│   ├── Requests/
│   │   ├── StoreAlumnoRequest.php
│   │   ├── StorePagoRequest.php
│   │   └── ...
│   ├── Resources/
│   │   ├── EscaneoProfesorResource.php
│   │   └── EscaneoAdminResource.php
│   └── Middleware/
│       └── EnsureUserHasRole.php
├── Models/
│   ├── User.php
│   ├── Alumno.php
│   ├── Plan.php
│   ├── Pago.php
│   ├── Asistencia.php
│   ├── CarneQr.php
│   └── ConfiguracionOffline.php
├── Observers/
│   └── PagoObserver.php         → Auditoría automática
├── Policies/
│   ├── AlumnoPolicy.php
│   └── PagoPolicy.php
├── Services/
│   ├── PagoService.php          → ⭐ Lógica de expiración
│   ├── CarneService.php         → Genera QR + PDF
│   ├── CategoriaService.php     → Calcula categoría por edad
│   └── SyncService.php          → Lógica offline
└── ...
```

---

## 🌍 Configuración de Entornos

### Tres entornos obligatorios

| Entorno | Propósito | APP_DEBUG | APP_ENV | BD |
|---------|-----------|-----------|---------|-----|
| **Local** | Desarrollo en tu máquina | `true` | `local` | `academia_dev` |
| **Staging** | Pruebas antes de producción | `true` | `staging` | `academia_staging` |
| **Producción** | Usuarios reales | `false` | `production` | `academia_prod` |

### Variables `.env` críticas en producción

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tudominio.com

# Nunca usar root
DB_USERNAME=academia_app
DB_PASSWORD=contraseña_larga_aleatoria_32_chars

# Regenerar en producción
APP_KEY=base64:...

# Logs
LOG_CHANNEL=daily
LOG_LEVEL=warning

# Cache
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Mail (para notificaciones)
MAIL_MAILER=smtp
```

---

## ✅ Checklist Pre-Producción Final

Antes de dar acceso a usuarios reales, marcar TODO:

### Código
- [ ] Todos los Form Requests tienen validación
- [ ] Todas las rutas tienen middleware de auth + rol
- [ ] Todas las queries usan Eloquent (no raw SQL con input de usuario)
- [ ] Policies aplicadas en cada controller
- [ ] Soft Deletes en modelos críticos (Alumno, Pago)
- [ ] Tests de lógica de expiración pasan (mínimo 10 casos)
- [ ] Tests de autorización pasan (profesor no ve deudas, padre solo ve hijos)

### Seguridad
- [ ] `APP_DEBUG=false`
- [ ] `.env` no está en el repositorio
- [ ] HTTPS activo y forzado
- [ ] Rate limiting en login y API
- [ ] Headers de seguridad configurados en Nginx
- [ ] Telescope desactivado o protegido
- [ ] Rutas de debug/test eliminadas
- [ ] Usuario MySQL sin permisos de root
- [ ] Firewall con solo puertos 22/80/443

### Infraestructura
- [ ] Backups automáticos configurados y probados
- [ ] Monitoreo de uptime activo
- [ ] Sentry configurado para errores
- [ ] Cron de Laravel funcionando (`schedule:run`)
- [ ] Queue worker corriendo (Supervisor)
- [ ] SSL renovación automática (certbot)
- [ ] Logs rotando diariamente

### Datos
- [ ] Seeders de producción listos (roles, planes, admin)
- [ ] BD migrada sin errores
- [ ] Índices creados en columnas de búsqueda
- [ ] No hay datos de prueba en producción

### Negocio
- [ ] Flujo completo probado: registro → pago → QR → escaneo → asistencia
- [ ] Flujo offline probado: sin internet → escaneo → sync
- [ ] Reportes generan datos correctos
- [ ] Admin capacitado
- [ ] Profesores capacitados en uso de PWA

---

## 📝 Notas Finales

### Errores comunes a evitar

1. **No meter lógica de negocio en controllers.** Todo en Services. El controller solo recibe, valida y responde.
2. **No usar `$request->all()`.** Siempre `$request->validated()` con Form Requests.
3. **No confiar en validación solo del frontend.** Siempre validar en backend.
4. **No hacer queries N+1.** Usar `with()` (eager loading) en relaciones.
5. **No generar el QR al crear alumno.** Solo después del primer pago confirmado.
6. **No sumar días desde `hoy` si el alumno está al día.** Sumar desde `fecha_expiracion` anterior.
7. **No mostrar montos de deuda al profesor.** Solo estados de color.
8. **No almacenar passwords en texto plano.** Laravel hashea por defecto, no desactivarlo.
9. **No deployar con `APP_DEBUG=true`.** Expone rutas, queries, variables de entorno.
10. **No olvidar el modo offline.** Sin él, el sistema es inútil en un campo de fútbol.

### Orden de prioridad si tienes poco tiempo

Si necesitas algo funcional rápido, el MVP mínimo es:

```
Fase 0 (entorno) → Fase 1 (alumnos) → Fase 2 (pagos) → Fase 4 (escaneo)
```

Eso te da: registro de alumnos, pagos con lógica correcta, y escaneo QR funcional. El PDF del carné (Fase 3), offline (Fase 5) y reportes (Fase 6) pueden esperar una segunda iteración.

---

*Documento generado: Septiembre 2026*  
*Stack: Laravel 11+ / MySQL 8.0 / PWA*  
*Tiempo estimado: 14-18 semanas*
