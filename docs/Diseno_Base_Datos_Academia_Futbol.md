# 🗄️ Diseño de Base de Datos - Sistema Academia de Fútbol

**Motor:** MySQL 8.0 (Workbench)  
**Framework destino:** Laravel 11+  
**Charset:** utf8mb4 (soporte emojis y caracteres especiales)  
**Collation:** utf8mb4_unicode_ci  

---

## 📑 Índice
1. [Diagrama de Entidades y Relaciones](#diagrama-de-entidades-y-relaciones)
2. [Mapa de Relaciones](#mapa-de-relaciones)
3. [Detalle de Cada Tabla](#detalle-de-cada-tabla)
4. [Decisiones de Diseño](#decisiones-de-diseño)
5. [Índices y Performance](#índices-y-performance)
6. [Vistas Útiles](#vistas-útiles)
7. [Script SQL Completo](#script-sql-completo)
8. [Equivalencia con Migraciones Laravel](#equivalencia-con-migraciones-laravel)

---

## 🔗 Diagrama de Entidades y Relaciones

```
┌──────────────┐       1:N        ┌──────────────┐
│   usuarios   │─────────────────▶│   alumnos    │
│  (padre/     │                  │              │
│   prof/admin)│                  └──────┬───────┘
└──────┬───────┘                         │
       │                                 │ 1:N
       │                                 ▼
       │                          ┌──────────────┐       N:1      ┌──────────────┐
       │                          │    pagos     │◀──────────────│   planes     │
       │                          └──────┬───────┘               └──────────────┘
       │                                 │
       │                                 │ 1:1 (trigger)
       │                                 ▼
       │                          ┌──────────────┐
       │                          │  carnes_qr   │
       │                          └──────────────┘
       │
       │  1:N (profesor)           ┌──────────────┐
       └──────────────────────────▶│ asistencias  │◀─── alumno (N:1)
                                   └──────────────┘
                                   
       ┌──────────────┐
       │  auditoria   │◀─── pago (N:1) + admin (N:1)
       │  _pagos      │
       └──────────────┘

       ┌──────────────┐
       │ config       │◀─── profesor (1:1)
       │ _offline     │
       └──────────────┘

       ┌──────────────┐
       │ intentos     │
       │ _escaneo     │◀─── profesor (N:1)
       │ _fallidos    │
       └──────────────┘
```

---

## 🔀 Mapa de Relaciones

| Tabla Origen | Relación | Tabla Destino | FK | Descripción |
|-------------|----------|---------------|-----|-------------|
| `usuarios` | 1 → N | `alumnos` | `alumnos.padre_id` | Un padre tiene varios hijos |
| `alumnos` | 1 → N | `pagos` | `pagos.alumno_id` | Un alumno tiene varios pagos |
| `planes` | 1 → N | `pagos` | `pagos.plan_id` | Un plan se usa en varios pagos |
| `usuarios` (admin) | 1 → N | `pagos` | `pagos.admin_id` | Un admin registra varios pagos |
| `alumnos` | 1 → N | `carnes_qr` | `carnes_qr.alumno_id` | Un alumno puede tener varias versiones de carné |
| `pagos` | 1 → 1 | `carnes_qr` | `carnes_qr.pago_id` | Un carné se genera por un pago específico |
| `alumnos` | 1 → N | `asistencias` | `asistencias.alumno_id` | Un alumno tiene muchas asistencias |
| `usuarios` (prof) | 1 → N | `asistencias` | `asistencias.profesor_id` | Un profesor registra muchas asistencias |
| `pagos` | 1 → N | `auditoria_pagos` | `auditoria_pagos.pago_id` | Un pago tiene varios registros de auditoría |
| `usuarios` (admin) | 1 → N | `auditoria_pagos` | `auditoria_pagos.admin_id` | Un admin genera varios registros de auditoría |
| `usuarios` (prof) | 1 → 1 | `configuracion_offline` | `configuracion_offline.profesor_id` | Cada profesor tiene una config offline |
| `usuarios` (prof) | 1 → N | `intentos_escaneo_fallidos` | `intentos_escaneo_fallidos.profesor_id` | Un profesor puede tener varios intentos fallidos |

---

## 📋 Detalle de Cada Tabla

---

### 1. `usuarios`

> Tabla central de autenticación. Almacena padres, profesores y administradores en una sola tabla (patrón que Laravel espera con su modelo `User`).

| Columna | Tipo | Null | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK estándar de Laravel |
| `uuid` | CHAR(36) | NO | — | Identificador público. Nunca exponer `id` en URLs |
| `nombre` | VARCHAR(100) | NO | — | Nombre completo |
| `email` | VARCHAR(150) | NO | — | Login. Único |
| `email_verified_at` | TIMESTAMP | SÍ | NULL | Laravel lo usa para verificación de email |
| `password` | VARCHAR(255) | NO | — | Hash bcrypt. Nunca texto plano |
| `telefono` | VARCHAR(20) | SÍ | NULL | Contacto. Opcional para admin/prof |
| `documento_identidad` | VARCHAR(20) | SÍ | NULL | DNI/CE del padre. No es PK. Puede ser NULL para profesores |
| `rol` | ENUM('superadmin','admin','profesor','padre') | NO | 'padre' | Rol principal. Spatie maneja permisos granulares, pero este campo es para queries rápidas |
| `activo` | TINYINT(1) | NO | 1 | Desactivar usuario sin borrar datos |
| `remember_token` | VARCHAR(100) | SÍ | NULL | Laravel "recuérdame" |
| `created_at` | TIMESTAMP | SÍ | CURRENT_TIMESTAMP | — |
| `updated_at` | TIMESTAMP | SÍ | CURRENT_TIMESTAMP | — |
| `deleted_at` | TIMESTAMP | SÍ | NULL | Soft delete. Nunca se borra un usuario |

**Índices:**
- `UNIQUE` en `uuid`
- `UNIQUE` en `email`
- `INDEX` en `rol` (filtrar por tipo de usuario)
- `INDEX` en `documento_identidad`

**¿Por qué una sola tabla y no tres?**  
Laravel espera un modelo `User` único para autenticación. Separar en 3 tablas complica el login, los middlewares y Spatie. El campo `rol` + Spatie Permission resuelve la separación lógica sin duplicar estructura.

---

### 2. `alumnos`

> Los menores de edad. Separados de `usuarios` porque un alumno NO se loguea. Su padre sí.

| Columna | Tipo | Null | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK interna |
| `uuid` | CHAR(36) | NO | — | ⭐ Identificador público y base del QR. Nunca exponer `id` ni `dni` |
| `padre_id` | BIGINT UNSIGNED | NO | — | FK → `usuarios.id`. El padre/tutor responsable |
| `nombre_completo` | VARCHAR(150) | NO | — | Nombre y apellidos del menor |
| `dni` | VARCHAR(20) | SÍ | NULL | Documento del menor. Único pero NO es PK. Puede ser NULL si aún no tiene |
| `fecha_nacimiento` | DATE | NO | — | Obligatorio para calcular categoría automáticamente |
| `categoria` | ENUM('sub_8','sub_10','sub_12','sub_14','sub_17','mayores') | NO | — | ⚙️ Sugerida automáticamente por edad (`fecha_nacimiento`), pero el admin puede fijarla manualmente |
| `sexo` | ENUM('M','F') | SÍ | NULL | Para separar equipos si aplica |
| `alergias_enfermedades` | TEXT | SÍ | NULL | ⚠️ Visible para el profesor al escanear QR. Campo libre: "Asma, alergia al polen" |
| `estado_salud_alerta` | TINYINT(1) | NO | 0 | Flag rápido: 1 = tiene alerta médica. El profesor ve un ícono rojo sin leer todo el texto |
| `ficha_medica_path` | VARCHAR(500) | SÍ | NULL | Ruta al PDF en `storage/app/private/fichas/`. Solo admin accede |
| `foto_path` | VARCHAR(500) | SÍ | NULL | Foto para el carné. Ruta en storage |
| `estado` | ENUM('sin_pago','activo','suspendido','baja') | NO | 'sin_pago' | `sin_pago` = recién registrado, sin QR aún |
| `fecha_expiracion` | DATE | SÍ | NULL | ⭐ Fecha hasta la que tiene derecho a entrenar. Se actualiza con cada pago |
| `sesiones_restantes` | INT UNSIGNED | SÍ | NULL | Solo para planes tipo "pack". NULL si es plan mensual |
| `created_at` | TIMESTAMP | SÍ | CURRENT_TIMESTAMP | — |
| `updated_at` | TIMESTAMP | SÍ | CURRENT_TIMESTAMP | — |
| `deleted_at` | TIMESTAMP | SÍ | NULL | Soft delete |

**Índices:**
- `UNIQUE` en `uuid`
- `UNIQUE` en `dni` (permite NULL pero si tiene valor, no se repite)
- `INDEX` en `padre_id`
- `INDEX` en `categoria` (filtrar reportes por categoría)
- `INDEX` en `estado`
- `INDEX` en `fecha_expiracion` (queries de morosos)

**Columnas clave:**
- `uuid`: Es lo que va dentro del QR. Encriptado con `Crypt::encryptString()`.
- `fecha_expiracion`: Se actualiza con la lógica inteligente (al día suma desde expiración, moroso suma desde hoy).
- `estado_salud_alerta`: Booleano rápido para que la vista del profesor muestre un ícono sin parsear el texto de alergias.
- `categoria`: Sugerida por `CategoriaService` según la edad (si nació hace 7 años o menos → `sub_8`, 8-9 → `sub_10`, etc.), pero es solo un valor por defecto: el admin la puede sobrescribir manualmente al crear o editar al alumno.

---

### 3. `planes`

> Catálogo de productos que vende la academia. Tabla de referencia que rara vez cambia.

| Columna | Tipo | Null | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK |
| `nombre` | VARCHAR(100) | NO | — | "Mensual", "Pack 10 clases", "Trimestral" |
| `tipo` | ENUM('mensual','pack','trimestral','anual') | NO | — | Define cómo se calcula la expiración |
| `duracion_dias` | INT UNSIGNED | SÍ | NULL | 30 para mensual, 90 para trimestral. NULL para pack (se mide en sesiones) |
| `sesiones_max` | INT UNSIGNED | SÍ | NULL | 10 para pack. NULL para mensual (ilimitadas en el período) |
| `precio` | DECIMAL(10,2) | NO | — | Precio en moneda local |
| `moneda` | VARCHAR(3) | NO | 'PEN' | ISO 4217: PEN (soles), EUR, USD |
| `descripcion` | VARCHAR(255) | SÍ | NULL | Texto opcional para mostrar al padre |
| `activo` | TINYINT(1) | NO | 1 | Desactivar plan sin borrarlo. Los pagos históricos siguen referenciándolo |
| `created_at` | TIMESTAMP | SÍ | CURRENT_TIMESTAMP | — |
| `updated_at` | TIMESTAMP | SÍ | CURRENT_TIMESTAMP | — |

**Datos iniciales (seeder):**

| nombre | tipo | duracion_dias | sesiones_max | precio |
|--------|------|---------------|-------------|--------|
| Mensual | mensual | 30 | NULL | 150.00 |
| Pack 10 Clases | pack | NULL | 10 | 120.00 |
| Trimestral | trimestral | 90 | NULL | 400.00 |

**¿Por qué `duracion_dias` y `sesiones_max` son nullable?**  
Un plan mensual no tiene límite de sesiones (va por días). Un pack no tiene límite de días (va por sesiones). Son mutuamente excluyentes según el `tipo`.

---

### 4. `pagos`

> Registro de cada transacción. Tabla más consultada del sistema junto con `alumnos`.

| Columna | Tipo | Null | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK |
| `uuid` | CHAR(36) | NO | — | Referencia pública del pago. Para recibos |
| `alumno_id` | BIGINT UNSIGNED | NO | — | FK → `alumnos.id` |
| `plan_id` | BIGINT UNSIGNED | NO | — | FK → `planes.id` |
| `admin_id` | BIGINT UNSIGNED | NO | — | FK → `usuarios.id`. Quién registró el pago |
| `monto` | DECIMAL(10,2) | NO | — | Monto cobrado. Puede diferir del precio del plan (descuentos, promo) |
| `moneda` | VARCHAR(3) | NO | 'PEN' | — |
| `metodo_pago` | ENUM('efectivo','transferencia','tarjeta','yape','plin','otro') | NO | — | Incluye métodos locales de Perú |
| `numero_operacion` | VARCHAR(100) | SÍ | NULL | Nro de transferencia, voucher, etc. |
| `fecha_pago` | DATE | NO | — | Cuándo pagó el padre |
| `fecha_inicio_vigencia` | DATE | NO | — | Desde cuándo cuenta el servicio |
| `fecha_expiracion` | DATE | SÍ | NULL | ⭐ Calculada con lógica inteligente. NULL si es pack (se mide en sesiones) |
| `sesiones_otorgadas` | INT UNSIGNED | SÍ | NULL | Solo para packs. NULL si es mensual |
| `estado` | ENUM('confirmado','pendiente','anulado') | NO | 'pendiente' | Solo `confirmado` activa el QR y la vigencia |
| `notas` | TEXT | SÍ | NULL | Observaciones del admin |
| `created_at` | TIMESTAMP | SÍ | CURRENT_TIMESTAMP | — |
| `updated_at` | TIMESTAMP | SÍ | CURRENT_TIMESTAMP | — |
| `deleted_at` | TIMESTAMP | SÍ | NULL | Soft delete. Nunca borrar pagos |

**Índices:**
- `UNIQUE` en `uuid`
- `INDEX` en `alumno_id`
- `INDEX` en `admin_id`
- `INDEX` en `estado`
- `INDEX` en `fecha_pago`
- `INDEX` en `fecha_expiracion`
- `INDEX COMPUESTO` en (`alumno_id`, `estado`, `fecha_expiracion`) → Query más frecuente: "¿este alumno está al día?"

**Columna clave `fecha_expiracion`:**  
Aquí se aplica la regla de negocio más importante:
```
SI alumno.fecha_expiracion >= HOY → nueva = alumno.fecha_expiracion + plan.duracion_dias
SI alumno.fecha_expiracion < HOY  → nueva = HOY + plan.duracion_dias
```
Esta lógica va en `PagoService.php`, no en la BD. La BD solo almacena el resultado.

---

### 5. `carnes_qr`

> Registro de cada carné generado. Permite versionado y auditoría.

| Columna | Tipo | Null | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK |
| `alumno_id` | BIGINT UNSIGNED | NO | — | FK → `alumnos.id` |
| `pago_id` | BIGINT UNSIGNED | NO | — | FK → `pagos.id`. El pago que disparó la generación |
| `uuid_encriptado` | TEXT | NO | — | UUID del alumno encriptado con AES. Es el contenido del QR |
| `version` | INT UNSIGNED | NO | 1 | v1, v2, v3... Se incrementa con cada regeneración |
| `fecha_generacion` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Cuándo se creó este carné |
| `fecha_expiracion` | DATE | NO | — | Coincide con la del pago. Se imprime en el PDF |
| `activo` | TINYINT(1) | NO | 1 | Solo 1 carné activo por alumno. Al generar nuevo, el anterior se desactiva |
| `created_at` | TIMESTAMP | SÍ | CURRENT_TIMESTAMP | — |
| `updated_at` | TIMESTAMP | SÍ | CURRENT_TIMESTAMP | — |

**Índices:**
- `INDEX` en `alumno_id`
- `INDEX` en (`alumno_id`, `activo`) → Buscar carné activo del alumno

**¿Por qué no guardar la imagen QR en la BD?**  
El QR se genera al vuelo desde `uuid_encriptado` con la librería. Guardar imágenes en BD es lento y pesado. El PDF también se genera dinámicamente en cada descarga.

---

### 6. `asistencias`

> Cada escaneo o registro manual. Tabla de alto volumen: crece rápido.

| Columna | Tipo | Null | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK |
| `alumno_id` | BIGINT UNSIGNED | NO | — | FK → `alumnos.id` |
| `profesor_id` | BIGINT UNSIGNED | NO | — | FK → `usuarios.id` (rol profesor) |
| `fecha` | DATE | NO | — | Fecha del entrenamiento. Separada de hora para queries por día |
| `hora` | TIME | NO | — | Hora del escaneo |
| `origen` | ENUM('escaneo_qr','manual') | NO | 'escaneo_qr' | Cómo se registró |
| `sincronizado` | TINYINT(1) | NO | 1 | 0 = registrado offline, pendiente de subir. 1 = ya en servidor |
| `dispositivo_sync` | VARCHAR(255) | SÍ | NULL | Identificador del dispositivo que hizo el sync (para debug) |
| `created_at` | TIMESTAMP | SÍ | CURRENT_TIMESTAMP | — |

**Índices:**
- `INDEX` en `alumno_id`
- `INDEX` en `profesor_id`
- `INDEX` en `fecha`
- `UNIQUE COMPUESTO` en (`alumno_id`, `fecha`) → **Evita duplicados**: un alumno no puede tener 2 asistencias el mismo día
- `INDEX` en `sincronizado` → Encontrar rápido los pendientes de sync

**¿Por qué separar `fecha` y `hora`?**  
La query más común es "asistencias del día X" o "asistencias del mes Y". Filtrar por `DATE(datetime_column)` rompe índices. Con `fecha` como columna separada, el índice funciona directo.

---

### 7. `auditoria_pagos`

> Log inmutable de toda acción sobre pagos. Nunca se edita ni borra.

| Columna | Tipo | Null | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK |
| `pago_id` | BIGINT UNSIGNED | NO | — | FK → `pagos.id` |
| `admin_id` | BIGINT UNSIGNED | NO | — | FK → `usuarios.id`. Quién hizo la acción |
| `accion` | ENUM('creado','confirmado','editado','anulado') | NO | — | Qué se hizo |
| `datos_anteriores` | JSON | SÍ | NULL | Snapshot del pago ANTES del cambio |
| `datos_nuevos` | JSON | SÍ | NULL | Snapshot DESPUÉS del cambio |
| `ip_origen` | VARCHAR(45) | SÍ | NULL | IPv4 o IPv6. Para rastrear accesos sospechosos |
| `user_agent` | VARCHAR(500) | SÍ | NULL | Navegador/dispositivo |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Inmutable |

**Índices:**
- `INDEX` en `pago_id`
- `INDEX` en `admin_id`
- `INDEX` en `created_at` → Filtrar auditoría por rango de fechas

**¿Por qué JSON para datos anteriores/nuevos?**  
Permite almacenar cualquier campo que cambie sin tener que crear columnas para cada uno. En Laravel se hace con `$pago->getOriginal()` vs `$pago->getAttributes()` en el Observer.

---

### 8. `configuracion_offline`

> Estado de sincronización de cada profesor. Una fila por profesor.

| Columna | Tipo | Null | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK |
| `profesor_id` | BIGINT UNSIGNED | NO | — | FK → `usuarios.id`. UNIQUE: 1 config por profesor |
| `fecha_ultima_sync` | TIMESTAMP | SÍ | NULL | Última vez que descargó datos |
| `cantidad_registros_cache` | INT UNSIGNED | NO | 0 | Cuántos alumnos tiene en caché |
| `hash_datos` | VARCHAR(64) | SÍ | NULL | SHA-256 de los datos descargados. Para detectar si hay cambios |
| `version_app` | VARCHAR(20) | SÍ | NULL | Versión de la PWA del profesor |
| `asistencias_pendientes_sync` | INT UNSIGNED | NO | 0 | Cuántas asistencias tiene offline sin subir |
| `created_at` | TIMESTAMP | SÍ | CURRENT_TIMESTAMP | — |
| `updated_at` | TIMESTAMP | SÍ | CURRENT_TIMESTAMP | — |

**Índices:**
- `UNIQUE` en `profesor_id`

---

### 9. `intentos_escaneo_fallidos`

> Seguridad: registra QR inválidos, manipulados o desconocidos.

| Columna | Tipo | Null | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK |
| `profesor_id` | BIGINT UNSIGNED | SÍ | NULL | FK → `usuarios.id`. NULL si no se pudo identificar |
| `contenido_qr` | TEXT | NO | — | Lo que se leyó del QR (podría ser basura) |
| `motivo_fallo` | ENUM('uuid_no_encontrado','desencriptacion_fallida','alumno_baja','qr_expirado') | NO | — | Por qué falló |
| `ip_origen` | VARCHAR(45) | SÍ | NULL | — |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | — |

**Índices:**
- `INDEX` en `profesor_id`
- `INDEX` en `created_at`
- `INDEX` en `motivo_fallo`

---

### 10. Tablas de Spatie Permission (se crean automáticas)

> Al instalar `spatie/laravel-permission`, se crean estas tablas con su propia migración. No las crees manualmente.

```
roles                    → admin, profesor, padre
permissions              → crear_alumno, ver_pagos, escanear_qr, ver_reportes...
model_has_roles          → Relaciona usuario ↔ rol
model_has_permissions    → Relaciona usuario ↔ permiso específico
role_has_permissions     → Relaciona rol ↔ permisos del rol
```

---

## 🧠 Decisiones de Diseño

### 1. ¿Por qué `id` BIGINT AUTO_INCREMENT + `uuid` CHAR(36)?

Laravel trabaja internamente con `id` numérico para relaciones (FKs, joins). Es más rápido que hacer joins por UUID.  
El `uuid` se usa en URLs, APIs y el QR. Así nunca expones el `id` secuencial (si alguien ve `/alumno/45`, sabe que hay al menos 44 más).

### 2. ¿Por qué ENUM en vez de tabla de referencia para `rol`, `estado`, `metodo_pago`?

Cuando los valores son pocos y estables (no cambian cada semana), ENUM es más eficiente que hacer un JOIN a otra tabla. Si en el futuro necesitas más flexibilidad (ej: 15 métodos de pago), migras a tabla de referencia.

### 3. ¿Por qué `fecha_expiracion` está en `alumnos` Y en `pagos`?

- En `pagos`: es el dato histórico. "Este pago dio cobertura hasta tal fecha."
- En `alumnos`: es el dato operativo actual. "¿El alumno puede entrenar HOY?" Se actualiza con cada pago.  
La de `alumnos` es la que consulta el profesor al escanear. Es redundancia deliberada por performance.

### 4. ¿Por qué Soft Deletes en `usuarios`, `alumnos`, `pagos`?

Nunca se borra un registro financiero ni de un menor. La ley exige trazabilidad. `deleted_at` marca como "archivado" sin perder datos. Laravel filtra automáticamente los soft-deleted en queries normales.

### 5. ¿Por qué `UNIQUE(alumno_id, fecha)` en asistencias?

Un alumno solo puede tener UNA asistencia por día. Si el profesor escanea dos veces por error, la BD rechaza el duplicado. Sin este constraint, tendrías datos basura.

### 6. ¿Por qué `estado` en alumnos si ya tengo `fecha_expiracion`?

`fecha_expiracion` dice CUÁNDO vence. `estado` dice EN QUÉ SITUACIÓN está:
- `sin_pago`: registrado pero nunca pagó → no tiene QR
- `activo`: pagó y está vigente
- `suspendido`: pagó pero venció → puede renovar
- `baja`: se fue de la academia → archivado

Un alumno puede tener `estado = activo` pero `fecha_expiracion = ayer` (hay que recalcular). El estado se actualiza vía cron job diario o al escanear.

### 7. ¿Por qué `moneda` en `planes` y `pagos`?

Si la academia opera en Perú (PEN/soles) pero algún día acepta pagos en dólares o expande a otro país, la columna ya existe. Cuesta 3 bytes y evita una migración futura.

---

## ⚡ Índices y Performance

### Queries más frecuentes y sus índices

| Query | Frecuencia | Índice que lo cubre |
|-------|-----------|-------------------|
| "¿Este alumno está al día?" (escaneo QR) | Cada entrenamiento, por cada alumno | `alumnos.uuid` + `alumnos.fecha_expiracion` |
| "Dame los morosos" (reporte admin) | Diario | `alumnos(estado, fecha_expiracion, categoria)` |
| "Asistencias de hoy" (profesor) | Cada día | `asistencias(fecha, profesor_id)` |
| "Pagos de este mes" (admin) | Semanal | `pagos(fecha_pago, estado)` |
| "Hijos de este padre" (vista padre) | Cada login | `alumnos(padre_id)` |
| "¿Ya escaneé a este alumno hoy?" (anti-duplicado) | Cada escaneo | `asistencias(alumno_id, fecha)` UNIQUE |

### Índice compuesto más importante

```sql
-- Este índice cubre la query del escaneo QR (la más crítica en tiempo):
CREATE INDEX idx_alumno_estado_expiracion 
ON alumnos (uuid, estado, fecha_expiracion);

-- Este índice cubre el reporte de morosos:
CREATE INDEX idx_morosos 
ON alumnos (estado, fecha_expiracion, categoria);
```

---

## 👁️ Vistas Útiles

### Vista: Morosos actuales

```sql
CREATE VIEW v_morosos AS
SELECT 
    a.id,
    a.nombre_completo,
    a.categoria,
    a.fecha_expiracion,
    DATEDIFF(CURDATE(), a.fecha_expiracion) AS dias_mora,
    u.nombre AS padre_nombre,
    u.telefono AS padre_telefono,
    u.email AS padre_email,
    (SELECT MAX(asi.fecha) FROM asistencias asi WHERE asi.alumno_id = a.id) AS ultima_asistencia
FROM alumnos a
INNER JOIN usuarios u ON u.id = a.padre_id
WHERE a.fecha_expiracion < CURDATE()
  AND a.estado != 'baja'
  AND a.deleted_at IS NULL;
```

### Vista: Estado para escaneo del profesor

```sql
CREATE VIEW v_escaneo_profesor AS
SELECT 
    a.uuid,
    a.nombre_completo,
    a.categoria,
    a.alergias_enfermedades,
    a.estado_salud_alerta,
    a.estado,
    a.fecha_expiracion,
    CASE 
        WHEN a.estado = 'sin_pago' THEN 'ROJO'
        WHEN a.estado = 'baja' THEN 'ROJO'
        WHEN a.fecha_expiracion >= CURDATE() THEN 'VERDE'
        WHEN DATEDIFF(CURDATE(), a.fecha_expiracion) <= 7 THEN 'AMBAR'
        ELSE 'ROJO'
    END AS semaforo
FROM alumnos a
WHERE a.deleted_at IS NULL;
```

### Vista: Resumen de ingresos por mes

```sql
CREATE VIEW v_ingresos_mensuales AS
SELECT 
    DATE_FORMAT(p.fecha_pago, '%Y-%m') AS mes,
    p.metodo_pago,
    COUNT(*) AS cantidad_pagos,
    SUM(p.monto) AS total_recaudado,
    p.moneda
FROM pagos p
WHERE p.estado = 'confirmado'
  AND p.deleted_at IS NULL
GROUP BY DATE_FORMAT(p.fecha_pago, '%Y-%m'), p.metodo_pago, p.moneda;
```

---

## 💾 Script SQL Completo

> Copiar y ejecutar en MySQL Workbench. Crea la BD completa con todas las tablas, FK, índices y vistas.

```sql
-- ============================================
-- BD: SISTEMA DE GESTIÓN ACADEMIA DE FÚTBOL
-- Motor: MySQL 8.0
-- Charset: utf8mb4
-- ============================================

CREATE DATABASE IF NOT EXISTS academia_futbol
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE academia_futbol;

-- ============================================
-- TABLA 1: USUARIOS
-- Padres, profesores y administradores
-- ============================================
CREATE TABLE usuarios (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    uuid            CHAR(36) NOT NULL,
    nombre          VARCHAR(100) NOT NULL,
    email           VARCHAR(150) NOT NULL,
    email_verified_at TIMESTAMP NULL DEFAULT NULL,
    password        VARCHAR(255) NOT NULL,
    telefono        VARCHAR(20) NULL DEFAULT NULL,
    documento_identidad VARCHAR(20) NULL DEFAULT NULL,
    rol             ENUM('superadmin','admin','profesor','padre') NOT NULL DEFAULT 'padre',
    activo          TINYINT(1) NOT NULL DEFAULT 1,
    remember_token  VARCHAR(100) NULL DEFAULT NULL,
    created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      TIMESTAMP NULL DEFAULT NULL,
    
    PRIMARY KEY (id),
    UNIQUE KEY uk_usuarios_uuid (uuid),
    UNIQUE KEY uk_usuarios_email (email),
    INDEX idx_usuarios_rol (rol),
    INDEX idx_usuarios_documento (documento_identidad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA 2: ALUMNOS
-- Menores de edad (no se loguean)
-- ============================================
CREATE TABLE alumnos (
    id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    uuid                CHAR(36) NOT NULL,
    padre_id            BIGINT UNSIGNED NOT NULL,
    nombre_completo     VARCHAR(150) NOT NULL,
    dni                 VARCHAR(20) NULL DEFAULT NULL,
    fecha_nacimiento    DATE NOT NULL,
    categoria           ENUM('sub_8','sub_10','sub_12','sub_14','sub_17','mayores') NOT NULL,
    sexo                ENUM('M','F') NULL DEFAULT NULL,
    alergias_enfermedades TEXT NULL DEFAULT NULL,
    estado_salud_alerta TINYINT(1) NOT NULL DEFAULT 0,
    ficha_medica_path   VARCHAR(500) NULL DEFAULT NULL,
    foto_path           VARCHAR(500) NULL DEFAULT NULL,
    estado              ENUM('sin_pago','activo','suspendido','baja') NOT NULL DEFAULT 'sin_pago',
    fecha_expiracion    DATE NULL DEFAULT NULL,
    sesiones_restantes  INT UNSIGNED NULL DEFAULT NULL,
    created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at          TIMESTAMP NULL DEFAULT NULL,
    
    PRIMARY KEY (id),
    UNIQUE KEY uk_alumnos_uuid (uuid),
    UNIQUE KEY uk_alumnos_dni (dni),
    INDEX idx_alumnos_padre (padre_id),
    INDEX idx_alumnos_categoria (categoria),
    INDEX idx_alumnos_estado (estado),
    INDEX idx_alumnos_expiracion (fecha_expiracion),
    INDEX idx_alumnos_estado_exp_cat (estado, fecha_expiracion, categoria),
    
    CONSTRAINT fk_alumnos_padre
        FOREIGN KEY (padre_id) REFERENCES usuarios (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA 3: PLANES
-- Catálogo de servicios
-- ============================================
CREATE TABLE planes (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre          VARCHAR(100) NOT NULL,
    tipo            ENUM('mensual','pack','trimestral','anual') NOT NULL,
    duracion_dias   INT UNSIGNED NULL DEFAULT NULL,
    sesiones_max    INT UNSIGNED NULL DEFAULT NULL,
    precio          DECIMAL(10,2) NOT NULL,
    moneda          VARCHAR(3) NOT NULL DEFAULT 'PEN',
    descripcion     VARCHAR(255) NULL DEFAULT NULL,
    activo          TINYINT(1) NOT NULL DEFAULT 1,
    created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    INDEX idx_planes_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA 4: PAGOS
-- Registro de transacciones
-- ============================================
CREATE TABLE pagos (
    id                      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    uuid                    CHAR(36) NOT NULL,
    alumno_id               BIGINT UNSIGNED NOT NULL,
    plan_id                 BIGINT UNSIGNED NOT NULL,
    admin_id                BIGINT UNSIGNED NOT NULL,
    monto                   DECIMAL(10,2) NOT NULL,
    moneda                  VARCHAR(3) NOT NULL DEFAULT 'PEN',
    metodo_pago             ENUM('efectivo','transferencia','tarjeta','yape','plin','otro') NOT NULL,
    numero_operacion        VARCHAR(100) NULL DEFAULT NULL,
    fecha_pago              DATE NOT NULL,
    fecha_inicio_vigencia   DATE NOT NULL,
    fecha_expiracion        DATE NULL DEFAULT NULL,
    sesiones_otorgadas      INT UNSIGNED NULL DEFAULT NULL,
    estado                  ENUM('confirmado','pendiente','anulado') NOT NULL DEFAULT 'pendiente',
    notas                   TEXT NULL DEFAULT NULL,
    created_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at              TIMESTAMP NULL DEFAULT NULL,
    
    PRIMARY KEY (id),
    UNIQUE KEY uk_pagos_uuid (uuid),
    INDEX idx_pagos_alumno (alumno_id),
    INDEX idx_pagos_admin (admin_id),
    INDEX idx_pagos_estado (estado),
    INDEX idx_pagos_fecha (fecha_pago),
    INDEX idx_pagos_expiracion (fecha_expiracion),
    INDEX idx_pagos_alumno_estado_exp (alumno_id, estado, fecha_expiracion),
    
    CONSTRAINT fk_pagos_alumno
        FOREIGN KEY (alumno_id) REFERENCES alumnos (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    CONSTRAINT fk_pagos_plan
        FOREIGN KEY (plan_id) REFERENCES planes (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    CONSTRAINT fk_pagos_admin
        FOREIGN KEY (admin_id) REFERENCES usuarios (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA 5: CARNÉS QR
-- Registro de carnés generados
-- ============================================
CREATE TABLE carnes_qr (
    id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    alumno_id           BIGINT UNSIGNED NOT NULL,
    pago_id             BIGINT UNSIGNED NOT NULL,
    uuid_encriptado     TEXT NOT NULL,
    version             INT UNSIGNED NOT NULL DEFAULT 1,
    fecha_generacion    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion    DATE NOT NULL,
    activo              TINYINT(1) NOT NULL DEFAULT 1,
    created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    INDEX idx_carnes_alumno (alumno_id),
    INDEX idx_carnes_alumno_activo (alumno_id, activo),
    
    CONSTRAINT fk_carnes_alumno
        FOREIGN KEY (alumno_id) REFERENCES alumnos (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    CONSTRAINT fk_carnes_pago
        FOREIGN KEY (pago_id) REFERENCES pagos (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA 6: ASISTENCIAS
-- Registro de asistencia a entrenamientos
-- ============================================
CREATE TABLE asistencias (
    id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    alumno_id           BIGINT UNSIGNED NOT NULL,
    profesor_id         BIGINT UNSIGNED NOT NULL,
    fecha               DATE NOT NULL,
    hora                TIME NOT NULL,
    origen              ENUM('escaneo_qr','manual') NOT NULL DEFAULT 'escaneo_qr',
    sincronizado        TINYINT(1) NOT NULL DEFAULT 1,
    dispositivo_sync    VARCHAR(255) NULL DEFAULT NULL,
    created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    UNIQUE KEY uk_asistencia_alumno_fecha (alumno_id, fecha),
    INDEX idx_asistencias_profesor (profesor_id),
    INDEX idx_asistencias_fecha (fecha),
    INDEX idx_asistencias_sync (sincronizado),
    
    CONSTRAINT fk_asistencias_alumno
        FOREIGN KEY (alumno_id) REFERENCES alumnos (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    CONSTRAINT fk_asistencias_profesor
        FOREIGN KEY (profesor_id) REFERENCES usuarios (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA 7: AUDITORÍA DE PAGOS
-- Log inmutable de cambios en pagos
-- ============================================
CREATE TABLE auditoria_pagos (
    id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    pago_id             BIGINT UNSIGNED NOT NULL,
    admin_id            BIGINT UNSIGNED NOT NULL,
    accion              ENUM('creado','confirmado','editado','anulado') NOT NULL,
    datos_anteriores    JSON NULL DEFAULT NULL,
    datos_nuevos        JSON NULL DEFAULT NULL,
    ip_origen           VARCHAR(45) NULL DEFAULT NULL,
    user_agent          VARCHAR(500) NULL DEFAULT NULL,
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    INDEX idx_auditoria_pago (pago_id),
    INDEX idx_auditoria_admin (admin_id),
    INDEX idx_auditoria_fecha (created_at),
    
    CONSTRAINT fk_auditoria_pago
        FOREIGN KEY (pago_id) REFERENCES pagos (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    CONSTRAINT fk_auditoria_admin
        FOREIGN KEY (admin_id) REFERENCES usuarios (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA 8: CONFIGURACIÓN OFFLINE
-- Estado de sync de cada profesor
-- ============================================
CREATE TABLE configuracion_offline (
    id                          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    profesor_id                 BIGINT UNSIGNED NOT NULL,
    fecha_ultima_sync           TIMESTAMP NULL DEFAULT NULL,
    cantidad_registros_cache    INT UNSIGNED NOT NULL DEFAULT 0,
    hash_datos                  VARCHAR(64) NULL DEFAULT NULL,
    version_app                 VARCHAR(20) NULL DEFAULT NULL,
    asistencias_pendientes_sync INT UNSIGNED NOT NULL DEFAULT 0,
    created_at                  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    UNIQUE KEY uk_offline_profesor (profesor_id),
    
    CONSTRAINT fk_offline_profesor
        FOREIGN KEY (profesor_id) REFERENCES usuarios (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA 9: INTENTOS DE ESCANEO FALLIDOS
-- Seguridad y detección de fraude
-- ============================================
CREATE TABLE intentos_escaneo_fallidos (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    profesor_id     BIGINT UNSIGNED NULL DEFAULT NULL,
    contenido_qr    TEXT NOT NULL,
    motivo_fallo    ENUM('uuid_no_encontrado','desencriptacion_fallida','alumno_baja','qr_expirado') NOT NULL,
    ip_origen       VARCHAR(45) NULL DEFAULT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    INDEX idx_intentos_profesor (profesor_id),
    INDEX idx_intentos_fecha (created_at),
    INDEX idx_intentos_motivo (motivo_fallo),
    
    CONSTRAINT fk_intentos_profesor
        FOREIGN KEY (profesor_id) REFERENCES usuarios (id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- VISTAS
-- ============================================

-- Vista: Morosos actuales
CREATE VIEW v_morosos AS
SELECT 
    a.id,
    a.uuid,
    a.nombre_completo,
    a.categoria,
    a.fecha_expiracion,
    DATEDIFF(CURDATE(), a.fecha_expiracion) AS dias_mora,
    a.estado,
    u.nombre AS padre_nombre,
    u.telefono AS padre_telefono,
    u.email AS padre_email,
    (SELECT MAX(asi.fecha) 
     FROM asistencias asi 
     WHERE asi.alumno_id = a.id) AS ultima_asistencia
FROM alumnos a
INNER JOIN usuarios u ON u.id = a.padre_id
WHERE a.fecha_expiracion < CURDATE()
  AND a.estado NOT IN ('baja', 'sin_pago')
  AND a.deleted_at IS NULL;

-- Vista: Semáforo para escaneo del profesor
CREATE VIEW v_escaneo_profesor AS
SELECT 
    a.uuid,
    a.nombre_completo,
    a.categoria,
    a.alergias_enfermedades,
    a.estado_salud_alerta,
    a.estado,
    a.fecha_expiracion,
    a.sesiones_restantes,
    CASE 
        WHEN a.estado IN ('sin_pago', 'baja') THEN 'ROJO'
        WHEN a.fecha_expiracion IS NOT NULL AND a.fecha_expiracion >= CURDATE() THEN 'VERDE'
        WHEN a.fecha_expiracion IS NOT NULL AND DATEDIFF(CURDATE(), a.fecha_expiracion) <= 7 THEN 'AMBAR'
        WHEN a.sesiones_restantes IS NOT NULL AND a.sesiones_restantes > 0 THEN 'VERDE'
        WHEN a.sesiones_restantes IS NOT NULL AND a.sesiones_restantes = 0 THEN 'ROJO'
        ELSE 'ROJO'
    END AS semaforo
FROM alumnos a
WHERE a.deleted_at IS NULL;

-- Vista: Ingresos mensuales
CREATE VIEW v_ingresos_mensuales AS
SELECT 
    DATE_FORMAT(p.fecha_pago, '%Y-%m') AS mes,
    p.metodo_pago,
    COUNT(*) AS cantidad_pagos,
    SUM(p.monto) AS total_recaudado,
    p.moneda
FROM pagos p
WHERE p.estado = 'confirmado'
  AND p.deleted_at IS NULL
GROUP BY DATE_FORMAT(p.fecha_pago, '%Y-%m'), p.metodo_pago, p.moneda;

-- ============================================
-- DATOS INICIALES (SEEDERS)
-- ============================================

-- Planes base
INSERT INTO planes (nombre, tipo, duracion_dias, sesiones_max, precio, moneda, descripcion) VALUES
('Mensual',         'mensual',    30,   NULL, 150.00, 'PEN', 'Acceso ilimitado por 30 días'),
('Pack 10 Clases',  'pack',       NULL, 10,   120.00, 'PEN', '10 sesiones de entrenamiento'),
('Trimestral',      'trimestral', 90,   NULL, 400.00, 'PEN', 'Acceso ilimitado por 3 meses'),
('Anual',           'anual',      365,  NULL, 1400.00,'PEN', 'Acceso ilimitado por 1 año');

-- Admin por defecto (password: Cambiar2024!)
-- En producción, cambiar inmediatamente después del primer login
INSERT INTO usuarios (uuid, nombre, email, password, rol, activo) VALUES
(UUID(), 'Administrador Principal', 'admin@academia.com', 
 '$2y$12$LJ3m4ys3Gp0Y5v8Gu.X5/.S8rE6YdF0hV7nC8yXzPm9R1L0Aq6WeW', 
 'admin', 1);
```

---

## 🔄 Equivalencia con Migraciones Laravel

Cuando pases de Workbench a Laravel, cada tabla se convierte en una migración. Referencia rápida:

| MySQL | Laravel Migration |
|-------|------------------|
| `BIGINT UNSIGNED AUTO_INCREMENT` | `$table->id()` |
| `CHAR(36) NOT NULL` (UUID) | `$table->uuid()` |
| `VARCHAR(100)` | `$table->string('nombre', 100)` |
| `TEXT NULL` | `$table->text('campo')->nullable()` |
| `ENUM('a','b','c')` | `$table->enum('campo', ['a','b','c'])` |
| `DECIMAL(10,2)` | `$table->decimal('monto', 10, 2)` |
| `DATE` | `$table->date('fecha')` |
| `TIMESTAMP NULL` | `$table->timestamp('campo')->nullable()` |
| `TINYINT(1) DEFAULT 0` | `$table->boolean('campo')->default(false)` |
| `JSON NULL` | `$table->json('campo')->nullable()` |
| `INT UNSIGNED NULL` | `$table->unsignedInteger('campo')->nullable()` |
| `FOREIGN KEY` | `$table->foreignId('padre_id')->constrained('usuarios')` |
| `UNIQUE KEY` | `$table->unique('campo')` |
| `INDEX` | `$table->index('campo')` |
| `UNIQUE compuesto` | `$table->unique(['alumno_id', 'fecha'])` |
| `ON DELETE RESTRICT` | `->constrained()->restrictOnDelete()` |
| `ON DELETE SET NULL` | `->constrained()->nullOnDelete()` |
| `SOFT DELETES` | `$table->softDeletes()` |
| `TIMESTAMPS` | `$table->timestamps()` |

### Orden de migraciones (importante por las FK)

```
1. create_usuarios_table        → Sin dependencias
2. create_alumnos_table         → Depende de usuarios
3. create_planes_table          → Sin dependencias
4. create_pagos_table           → Depende de alumnos, planes, usuarios
5. create_carnes_qr_table       → Depende de alumnos, pagos
6. create_asistencias_table     → Depende de alumnos, usuarios
7. create_auditoria_pagos_table → Depende de pagos, usuarios
8. create_configuracion_offline → Depende de usuarios
9. create_intentos_escaneo      → Depende de usuarios
```

---

## 📌 Rangos de Categoría por Edad (sugerencia automática)

Implementado en `app/Services/CategoriaService.php`. Es solo el valor por defecto:
el admin puede fijar manualmente la categoría de un alumno al crearlo o editarlo,
por ejemplo si juega en una categoría distinta a la que le tocaría por edad.

| Categoría | Edad Mínima | Edad Máxima |
|-----------|-------------|-------------|
| Sub-8 | 5 | 7 |
| Sub-10 | 8 | 9 |
| Sub-12 | 10 | 11 |
| Sub-14 | 12 | 13 |
| Sub-17 | 14 | 16 |
| Mayores | 17 | — |

> Estos rangos pueden variar según la academia. Crear tabla de configuración si se necesita flexibilidad.

---

*Documento generado: Septiembre 2026*  
*Compatible con: MySQL 8.0 + Laravel 11+*  
*Total: 9 tablas propias + 5 tablas Spatie (auto-generadas) + 3 vistas*
