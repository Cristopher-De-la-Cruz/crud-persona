CREATE DATABASE crud_test_persona;

USE crud_test_persona;

CREATE TABLE personas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tipo_documento INT,
    numero_documento VARCHAR(20),
    nombre VARCHAR(50),
    apellido VARCHAR(50),
    sexo CHAR(1),
    direccion TEXT,
    fecha_nacimiento DATE
);

INSERT INTO personas (tipo_documento, numero_documento, nombre, apellido, sexo, direccion, fecha_nacimiento) VALUES	
	(1, '12345678', 'Cristopher', 'Fulanus', 'H', 'Direccion #321', '2012-12-12');