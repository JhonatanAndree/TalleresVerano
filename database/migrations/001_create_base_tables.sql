-- Migración inicial para las tablas base del sistema
-- Ruta: database/migrations/001_create_base_tables.sql

-- Añadir soporte para triggers
DELIMITER //

-- Actualizar estructura de usuarios
ALTER TABLE usuarios
ADD COLUMN reset_token VARCHAR(100) NULL,
ADD COLUMN reset_token_expires TIMESTAMP NULL,
ADD COLUMN last_login TIMESTAMP NULL,
ADD COLUMN failed_attempts INT DEFAULT 0,
ADD COLUMN locked_until TIMESTAMP NULL,
ADD COLUMN api_token VARCHAR(100) NULL,
ADD INDEX idx_email (email),
ADD INDEX idx_reset_token (reset_token);

-- Añadir índices a tablas principales
ALTER TABLE estudiantes
ADD INDEX idx_dni (dni),
ADD INDEX idx_padre_id (id_padre),
ADD FULLTEXT INDEX idx_nombre_apellido (nombre, apellido);

ALTER TABLE talleres
ADD INDEX idx_sede_aula (id_sede, id_aula),
ADD INDEX idx_docente (id_docente);

ALTER TABLE matriculas
ADD INDEX idx_estudiante_taller (estudiante_id, taller_id),
ADD INDEX idx_estado_fecha (estado, created_at);

ALTER TABLE pagos
ADD INDEX idx_transaction (transaction_id),
ADD INDEX idx_estado_fecha (estado, fecha);

-- Crear tabla de auditoría mejorada
CREATE TABLE IF NOT EXISTS auditoria (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED,
    accion VARCHAR(50) NOT NULL,
    tabla VARCHAR(50) NOT NULL,
    registro_id BIGINT UNSIGNED,
    datos_antiguos JSON,
    datos_nuevos JSON,
    ip VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    INDEX idx_usuario_accion (usuario_id, accion),
    INDEX idx_tabla_registro (tabla, registro_id)
);

-- Trigger para auditoría de usuarios
CREATE TRIGGER tr_usuarios_audit_insert AFTER INSERT ON usuarios
FOR EACH ROW
BEGIN
    INSERT INTO auditoria (usuario_id, accion, tabla, registro_id, datos_nuevos)
    VALUES (NEW.id, 'INSERT', 'usuarios', NEW.id, JSON_OBJECT(
        'nombre', NEW.nombre,
        'apellido', NEW.apellido,
        'email', NEW.email,
        'rol', NEW.rol
    ));
END //

CREATE TRIGGER tr_usuarios_audit_update AFTER UPDATE ON usuarios
FOR EACH ROW
BEGIN
    INSERT INTO auditoria (usuario_id, accion, tabla, registro_id, datos_antiguos, datos_nuevos)
    VALUES (NEW.id, 'UPDATE', 'usuarios', NEW.id,
        JSON_OBJECT(
            'nombre', OLD.nombre,
            'apellido', OLD.apellido,
            'email', OLD.email,
            'rol', OLD.rol
        ),
        JSON_OBJECT(
            'nombre', NEW.nombre,
            'apellido', NEW.apellido,
            'email', NEW.email,
            'rol', NEW.rol
        )
    );
END //

-- Procedimientos almacenados optimizados
CREATE PROCEDURE sp_dashboard_stats()
BEGIN
    SELECT
        (SELECT COUNT(*) FROM estudiantes WHERE deleted_at IS NULL) as total_estudiantes,
        (SELECT COUNT(*) FROM talleres WHERE deleted_at IS NULL) as total_talleres,
        (SELECT COUNT(*) FROM matriculas WHERE estado = 'activo' AND deleted_at IS NULL) as matriculas_activas,
        (SELECT SUM(monto) FROM pagos WHERE estado = 'completado' AND deleted_at IS NULL) as total_ingresos;
END //

CREATE PROCEDURE sp_verificar_cupos(IN taller_id INT)
BEGIN
    SELECT 
        t.capacidad_maxima,
        COUNT(m.id) as inscritos,
        t.capacidad_maxima - COUNT(m.id) as disponibles
    FROM talleres t
    LEFT JOIN matriculas m ON t.id = m.taller_id AND m.deleted_at IS NULL
    WHERE t.id = taller_id AND t.deleted_at IS NULL
    GROUP BY t.id, t.capacidad_maxima;
END //

DELIMITER ;

-- Índices para búsquedas frecuentes
CREATE INDEX idx_estudiantes_busqueda ON estudiantes(dni, nombre, apellido);
CREATE INDEX idx_pagos_busqueda ON pagos(transaction_id, estado, fecha);
CREATE INDEX idx_talleres_busqueda ON talleres(nombre, id_sede);

-- Vistas optimizadas
CREATE OR REPLACE VIEW v_estudiantes_activos AS
SELECT 
    e.*,
    t.nombre as taller_nombre,
    s.nombre as sede_nombre,
    p.estado as estado_pago
FROM estudiantes e
JOIN matriculas m ON e.id = m.estudiante_id
JOIN talleres t ON m.taller_id = t.id
JOIN sedes s ON t.id_sede = s.id
LEFT JOIN pagos p ON m.id = p.matricula_id
WHERE 
    e.deleted_at IS NULL 
    AND m.deleted_at IS NULL 
    AND m.estado = 'activo';

CREATE OR REPLACE VIEW v_dashboard_talleres AS
SELECT 
    t.id,
    t.nombre,
    s.nombre as sede,
    COUNT(m.id) as total_matriculas,
    COUNT(DISTINCT e.id) as total_estudiantes,
    t.capacidad_maxima,
    t.capacidad_maxima - COUNT(m.id) as cupos_disponibles
FROM talleres t
JOIN sedes s ON t.id_sede = s.id
LEFT JOIN matriculas m ON t.id = m.taller_id
LEFT JOIN estudiantes e ON m.estudiante_id = e.id
WHERE t.deleted_at IS NULL
GROUP BY t.id, t.nombre, s.nombre, t.capacidad_maxima;