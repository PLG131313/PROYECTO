CREATE DATABASE IF NOT EXISTS plopezg129_WebTFG;
USE plopezg129_WebTFG;

-- Tabla Clientes
CREATE TABLE clientes (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          nombre VARCHAR(100) NOT NULL,
                          email VARCHAR(150) UNIQUE NOT NULL,
                          telefono VARCHAR(15),
                          contrasena VARCHAR(255) NOT NULL
);

-- Tabla Pilotos
CREATE TABLE pilotos (
                         id INT AUTO_INCREMENT PRIMARY KEY,
                         nombre VARCHAR(100) NOT NULL,
                         email VARCHAR(150) UNIQUE NOT NULL,
                         telefono VARCHAR(15),
                         licencia VARCHAR(50) UNIQUE NOT NULL,
                         contrasena VARCHAR(255) NOT NULL
);
-- Tabla Reservas
CREATE TABLE reservas (
                          idReserva INT AUTO_INCREMENT PRIMARY KEY,
                          fecha_reserva TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                          fecha_vuelo DATETIME NOT NULL,
                          idCliente INT,
                          idPiloto INT,
                          clase_vuelo VARCHAR(50) NOT NULL,
                          ciudad_inicial VARCHAR(100) NOT NULL,
                          ciudad_final VARCHAR(100) NOT NULL,
                          FOREIGN KEY (idCliente) REFERENCES clientes(id) ON DELETE CASCADE,
                          FOREIGN KEY (idPiloto) REFERENCES pilotos(id) ON DELETE CASCADE
);

-- Tabla Viajes
CREATE TABLE viajes (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        origen VARCHAR(100) NOT NULL,
                        destino VARCHAR(100) NOT NULL,
                        fecha DATETIME NOT NULL,
                        precio DECIMAL(10,2) NOT NULL,
                        id_piloto INT,
                        FOREIGN KEY (id_piloto) REFERENCES pilotos(id) ON DELETE SET NULL
);



-- Tabla Comentarios
CREATE TABLE comentarios (
                             id INT AUTO_INCREMENT PRIMARY KEY,
                             id_cliente INT,
                             id_viaje INT,
                             comentario TEXT NOT NULL,
                             calificacion INT CHECK (calificacion BETWEEN 1 AND 5),
                             fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                             FOREIGN KEY (id_cliente) REFERENCES clientes(id) ON DELETE CASCADE,
                             FOREIGN KEY (id_viaje) REFERENCES viajes(id) ON DELETE CASCADE
);
--Un Cliente puede hacer muchas Reservas (1:N) → clientes (id) ⟶ reservas (idCliente)
--Un Piloto puede tener muchas Reservas (1:N) → pilotos (id) ⟶ reservas (idPiloto)
--Un Piloto puede realizar varios Viajes (1:N) → pilotos (id) ⟶ viajes (id_piloto)
--Un Cliente puede dejar varios Comentarios sobre diferentes Viajes (1:N) → clientes (id) ⟶ comentarios (id_cliente)
--Un Viaje puede recibir varios Comentarios de diferentes Clientes (1:N) → viajes (id) ⟶ comentarios (id_viaje)
