CREATE DATABASE IF NOT EXISTS hospital;
USE hospital;

CREATE TABLE IF NOT EXISTS medicos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    documento VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    especialidad VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS registros (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    medico_id INT NOT NULL,
    tipo VARCHAR(10) NOT NULL, -- 'ENTRADA' o 'SALIDA'
    FOREIGN KEY (medico_id) REFERENCES medicos(id)
);

-- Insertar 5 médicos de ejemplo
INSERT INTO medicos (documento, nombre, especialidad) VALUES
('101001', 'Dra. Elena Rostova', 'Cardiologia'),
('101002', 'Dr. Carlos Mendoza', 'Pediatria'),
('101003', 'Dra. Sofia Ramirez', 'Neurologia'),
('101004', 'Dr. Juan Pablo Torres', 'Urgencias'),
('101005', 'Dra. Maria Fernanda Lopez', 'Ginecologia');