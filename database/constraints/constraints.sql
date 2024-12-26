-- Constraints y triggers para integridad de datos
-- Ruta: database/constraints/constraints.sql

DELIMITER //

-- Trigger para validar cupo antes de matrícula
CREATE TRIGGER tr_validar_cupo BEFORE INSERT ON matriculas
FOR EACH ROW
BEGIN
    DECLARE cupo_disponible INT;
    DECLARE capacidad_maxima INT;
    
    SELECT t.capacidad_maxima, 
           t.capacidad_maxima - COUNT(m.id) INTO capacidad_maxima, cupo_disponible
    FROM talleres t
    LEFT JOIN matriculas m ON t.id = m.taller_id AND m.deleted_at IS NULL
    WHERE t.id = NEW.taller_id
    GROUP BY t.id, t.capacidad_maxima;
    
    IF cupo_disponible <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No hay cupos disponibles en este taller';
    END IF;
END //

-- Trigger para validar conflictos de horario
CREATE TRIGGER tr_validar_horario BEFORE INSERT ON matriculas
FOR EACH ROW
BEGIN
    DECLARE conflicto INT;
    
    SELECT COUNT(*) INTO conflicto
    FROM matriculas m
    JOIN horarios h1 ON m.horario_id = h1.id
    JOIN horarios h2 ON NEW.horario_id = h2.id
    WHERE m.estudiante_id = NEW.estudiante_id
    AND m.deleted_at IS NULL
    AND (
        (h1.hora_inicio BETWEEN h2.hora_inicio AND h2.hora_fin) OR
        (h1.hora_fin BETWEEN h2.hora_inicio AND h2.hora_fin)
    );
    
    IF conflicto > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Existe conflicto de horarios';
    END IF;
END //

-- Trigger para validar edad del estudiante
CREATE TRIGGER tr_validar_edad BEFORE INSERT ON estudiantes
FOR EACH ROW
BEGIN
    IF NEW.edad < 3 OR NEW.edad > 17 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Edad fuera del rango permitido (3-17 años)';
    END IF;
END //

-- Trigger para validar pago antes de activar matrícula
CREATE TRIGGER tr_validar_pago BEFORE UPDATE ON matriculas
FOR EACH ROW
BEGIN
    DECLARE pago_confirmado INT;
    
    IF NEW.estado = 'activo' AND OLD.estado != 'activo' THEN
        SELECT COUNT(*) INTO pago_confirmado
        FROM pagos
        WHERE matricula_id = NEW.id
        AND estado = 'completado'
        AND deleted_at IS NULL;
        
        IF pago_confirmado = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'No se puede activar la matrícula sin pago confirmado';
        END IF;
    END IF;
END //

-- Trigger para validar docente disponible
CREATE TRIGGER tr_validar_docente BEFORE INSERT ON horarios
FOR EACH ROW
BEGIN
    DECLARE conflicto INT;
    
    SELECT COUNT(*) INTO conflicto
    FROM horarios h
    JOIN talleres t1 ON h.taller_id = t1.id
    JOIN talleres t2 ON NEW.taller_id = t2.id
    WHERE t1.docente_id = t2.docente_id
    AND h.deleted_at IS NULL
    AND (
        (h.hora_inicio BETWEEN NEW.hora_inicio AND NEW.hora_fin) OR
        (h.hora_fin BETWEEN NEW.hora_inicio AND NEW.hora_fin)
    );
    
    IF conflicto > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El docente tiene conflicto de horarios';
    END IF;
END //

DELIMITER ;

-- Constraints de integridad referencial
ALTER TABLE matriculas
ADD CONSTRAINT fk_matricula_estudiante
FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id),
ADD CONSTRAINT fk_matricula_taller
FOREIGN KEY (taller_id) REFERENCES talleres(id),
ADD CONSTRAINT fk_matricula_horario
FOREIGN KEY (horario_id) REFERENCES horarios(id);

ALTER TABLE talleres
ADD CONSTRAINT fk_taller_sede
FOREIGN KEY (id_sede) REFERENCES sedes(id),
ADD CONSTRAINT fk_taller_aula
FOREIGN KEY (id_aula) REFERENCES aulas(id),
ADD CONSTRAINT fk_taller_docente
FOREIGN KEY (id_docente) REFERENCES usuarios(id);

ALTER TABLE pagos
ADD CONSTRAINT fk_pago_matricula
FOREIGN KEY (matricula_id) REFERENCES matriculas(id),
ADD CONSTRAINT chk_monto_positivo
CHECK (monto > 0);

-- Índices adicionales para optimización
CREATE INDEX idx_matriculas_estado ON matriculas(estado);
CREATE INDEX idx_pagos_estado ON pagos(estado);
CREATE INDEX idx_horarios_tiempo ON horarios(hora_inicio, hora_fin);