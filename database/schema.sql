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
 '$2y$12$IY8tz4ShQK7vHc5jrbPBh.2SNRPKDPk4qO7fb8eg5dWG4iGyG1BzK',
 'admin', 1);

