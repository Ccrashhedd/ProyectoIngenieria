-- Actualización de base de datos para sistema de carrito
-- Ejecutar este archivo en phpMyAdmin o MySQL

-- ==================================================
-- ELIMINACIÓN DE OBJETOS EXISTENTES (SI EXISTEN)
-- ==================================================

-- Eliminar triggers existentes
DROP TRIGGER IF EXISTS after_carrito_insert;
DROP TRIGGER IF EXISTS after_carrito_update;
DROP TRIGGER IF EXISTS after_carrito_delete;
DROP TRIGGER IF EXISTS after_factura_insert;

-- Eliminar procedimientos existentes
DROP PROCEDURE IF EXISTS finalizar_compra;
DROP PROCEDURE IF EXISTS AgregarAlCarrito;
DROP PROCEDURE IF EXISTS ActualizarCantidadCarrito;

-- Eliminar funciones existentes
DROP FUNCTION IF EXISTS ObtenerTotalCarrito;
DROP FUNCTION IF EXISTS ContarItemsCarrito;

-- ==================================================
-- CREACIÓN DE TABLAS
-- ==================================================

-- Tabla para items del carrito (temporal)
CREATE TABLE IF NOT EXISTS carrito (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    session_id VARCHAR(255) NULL,
    fecha_agregado DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizado DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    -- Evitar duplicados por usuario logueado
    UNIQUE KEY unique_user_producto (usuario_id, producto_id),
    -- Evitar duplicados por session (usuario invitado)
    UNIQUE KEY unique_session_producto (session_id, producto_id)
);

-- Tabla para facturas/órdenes
CREATE TABLE IF NOT EXISTS facturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    session_id VARCHAR(255) NULL,
    total DECIMAL(10,2) NOT NULL,
    estado ENUM('pendiente', 'pagada', 'enviada', 'entregada', 'cancelada') DEFAULT 'pendiente',
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    nombre_cliente VARCHAR(255) NOT NULL,
    email_cliente VARCHAR(255) NOT NULL,
    telefono_cliente VARCHAR(20) NULL,
    direccion_cliente TEXT NULL,
    notas TEXT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabla para detalle de cada factura
CREATE TABLE IF NOT EXISTS detalle_factura (
    id INT AUTO_INCREMENT PRIMARY KEY,
    factura_id INT NOT NULL,
    producto_id INT NOT NULL,
    producto_nombre VARCHAR(255) NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (factura_id) REFERENCES facturas(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
);

-- ==================================================
-- ÍNDICES PARA MEJORAR RENDIMIENTO
-- ==================================================

CREATE INDEX IF NOT EXISTS idx_carrito_usuario ON carrito(usuario_id);
CREATE INDEX IF NOT EXISTS idx_carrito_session ON carrito(session_id);
CREATE INDEX IF NOT EXISTS idx_carrito_producto ON carrito(producto_id);
CREATE INDEX IF NOT EXISTS idx_facturas_usuario ON facturas(usuario_id);
CREATE INDEX IF NOT EXISTS idx_facturas_session ON facturas(session_id);
CREATE INDEX IF NOT EXISTS idx_facturas_estado ON facturas(estado);
CREATE INDEX IF NOT EXISTS idx_detalle_factura ON detalle_factura(factura_id);

-- ==================================================
-- TRIGGERS PARA MANEJO AUTOMÁTICO DE STOCK
-- ==================================================

DELIMITER $$

-- Trigger para disminuir stock al agregar al carrito
CREATE TRIGGER after_carrito_insert 
AFTER INSERT ON carrito
FOR EACH ROW
BEGIN
    DECLARE stock_actual INT;
    
    -- Verificar stock disponible
    SELECT stock INTO stock_actual FROM productos WHERE id = NEW.producto_id;
    
    IF stock_actual < NEW.cantidad THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Stock insuficiente para este producto';
    END IF;
    
    -- Decrementar stock
    UPDATE productos 
    SET stock = stock - NEW.cantidad 
    WHERE id = NEW.producto_id;
END$$

-- Trigger para ajustar stock al actualizar cantidad en carrito
CREATE TRIGGER after_carrito_update 
AFTER UPDATE ON carrito
FOR EACH ROW
BEGIN
    DECLARE diferencia INT;
    DECLARE stock_actual INT;
    
    SET diferencia = NEW.cantidad - OLD.cantidad;
    
    -- Solo procesar si hay cambio en cantidad
    IF diferencia != 0 THEN
        -- Si aumenta la cantidad, verificar stock
        IF diferencia > 0 THEN
            SELECT stock INTO stock_actual FROM productos WHERE id = NEW.producto_id;
            
            IF stock_actual < diferencia THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Stock insuficiente para incrementar la cantidad';
            END IF;
        END IF;
        
        -- Ajustar stock (restar si aumenta cantidad, sumar si disminuye)
        UPDATE productos 
        SET stock = stock - diferencia 
        WHERE id = NEW.producto_id;
    END IF;
END$$

-- Trigger para restaurar stock al eliminar del carrito
CREATE TRIGGER after_carrito_delete 
AFTER DELETE ON carrito
FOR EACH ROW
BEGIN
    -- Restaurar stock
    UPDATE productos 
    SET stock = stock + OLD.cantidad 
    WHERE id = OLD.producto_id;
END$$

DELIMITER ;

-- ==================================================
-- PROCEDIMIENTOS ALMACENADOS
-- ==================================================

-- Procedimiento para agregar producto al carrito
DELIMITER $$
CREATE PROCEDURE AgregarAlCarrito(
    IN p_usuario_id INT,
    IN p_session_id VARCHAR(255),
    IN p_producto_id INT,
    IN p_cantidad INT
)
BEGIN
    DECLARE v_existe INT DEFAULT 0;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Verificar si el producto ya existe en el carrito
    SELECT COUNT(*) INTO v_existe
    FROM carrito 
    WHERE producto_id = p_producto_id 
    AND (
        (p_usuario_id IS NOT NULL AND usuario_id = p_usuario_id) OR
        (p_usuario_id IS NULL AND session_id = p_session_id)
    );
    
    IF v_existe > 0 THEN
        -- Si existe, actualizar cantidad
        UPDATE carrito 
        SET cantidad = cantidad + p_cantidad,
            fecha_actualizado = CURRENT_TIMESTAMP
        WHERE producto_id = p_producto_id 
        AND (
            (p_usuario_id IS NOT NULL AND usuario_id = p_usuario_id) OR
            (p_usuario_id IS NULL AND session_id = p_session_id)
        );
    ELSE
        -- Si no existe, insertar nuevo
        INSERT INTO carrito (usuario_id, session_id, producto_id, cantidad)
        VALUES (p_usuario_id, p_session_id, p_producto_id, p_cantidad);
    END IF;
    
    COMMIT;
END$$
DELIMITER ;

-- Procedimiento para actualizar cantidad en carrito
DELIMITER $$
CREATE PROCEDURE ActualizarCantidadCarrito(
    IN p_carrito_id INT,
    IN p_nueva_cantidad INT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    IF p_nueva_cantidad <= 0 THEN
        -- Si la cantidad es 0 o negativa, eliminar del carrito
        DELETE FROM carrito WHERE id = p_carrito_id;
    ELSE
        -- Actualizar cantidad
        UPDATE carrito 
        SET cantidad = p_nueva_cantidad,
            fecha_actualizado = CURRENT_TIMESTAMP
        WHERE id = p_carrito_id;
    END IF;
    
    COMMIT;
END$$
DELIMITER ;

-- Procedimiento para finalizar compra
DELIMITER $$
CREATE PROCEDURE finalizar_compra(
    IN p_usuario_id INT,
    IN p_session_id VARCHAR(255),
    IN p_nombre_cliente VARCHAR(255),
    IN p_email_cliente VARCHAR(255),
    IN p_telefono_cliente VARCHAR(20),
    IN p_direccion_cliente TEXT,
    IN p_notas TEXT,
    OUT p_factura_id INT,
    OUT p_total DECIMAL(10,2)
)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_producto_id INT;
    DECLARE v_cantidad INT;
    DECLARE v_precio_unitario DECIMAL(10,2);
    DECLARE v_producto_nombre VARCHAR(255);
    DECLARE v_subtotal DECIMAL(10,2);
    
    DECLARE carrito_cursor CURSOR FOR
        SELECT c.producto_id, c.cantidad, p.precio, p.nombre
        FROM carrito c
        JOIN productos p ON c.producto_id = p.id
        WHERE (p_usuario_id IS NOT NULL AND c.usuario_id = p_usuario_id)
           OR (p_usuario_id IS NULL AND c.session_id = p_session_id);
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Inicializar total
    SET p_total = 0;
    
    -- Crear la factura
    INSERT INTO facturas (usuario_id, session_id, total, nombre_cliente, email_cliente, telefono_cliente, direccion_cliente, notas)
    VALUES (p_usuario_id, p_session_id, 0, p_nombre_cliente, p_email_cliente, p_telefono_cliente, p_direccion_cliente, p_notas);
    
    SET p_factura_id = LAST_INSERT_ID();
    
    -- Abrir cursor para procesar items del carrito
    OPEN carrito_cursor;
    
    read_loop: LOOP
        FETCH carrito_cursor INTO v_producto_id, v_cantidad, v_precio_unitario, v_producto_nombre;
        
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        SET v_subtotal = v_cantidad * v_precio_unitario;
        SET p_total = p_total + v_subtotal;
        
        -- Insertar detalle de factura
        INSERT INTO detalle_factura (factura_id, producto_id, producto_nombre, cantidad, precio_unitario, subtotal)
        VALUES (p_factura_id, v_producto_id, v_producto_nombre, v_cantidad, v_precio_unitario, v_subtotal);
        
    END LOOP;
    
    CLOSE carrito_cursor;
    
    -- Actualizar total en la factura
    UPDATE facturas SET total = p_total WHERE id = p_factura_id;
    
    -- Limpiar carrito (esto también restaurará el stock via trigger)
    DELETE FROM carrito 
    WHERE (p_usuario_id IS NOT NULL AND usuario_id = p_usuario_id)
       OR (p_usuario_id IS NULL AND session_id = p_session_id);
    
    COMMIT;
END$$
DELIMITER ;

-- ==================================================
-- FUNCIONES ÚTILES
-- ==================================================

-- Función para obtener el total del carrito
DELIMITER $$
CREATE FUNCTION ObtenerTotalCarrito(
    p_usuario_id INT,
    p_session_id VARCHAR(255)
) RETURNS DECIMAL(10,2)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE v_total DECIMAL(10,2) DEFAULT 0;
    
    SELECT COALESCE(SUM(c.cantidad * p.precio), 0) INTO v_total
    FROM carrito c
    INNER JOIN productos p ON c.producto_id = p.id
    WHERE (
        (p_usuario_id IS NOT NULL AND c.usuario_id = p_usuario_id) OR
        (p_usuario_id IS NULL AND c.session_id = p_session_id)
    );
    
    RETURN v_total;
END$$
DELIMITER ;

-- Función para contar items en carrito
DELIMITER $$
CREATE FUNCTION ContarItemsCarrito(
    p_usuario_id INT,
    p_session_id VARCHAR(255)
) RETURNS INT
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE v_count INT DEFAULT 0;
    
    SELECT COALESCE(SUM(cantidad), 0) INTO v_count
    FROM carrito
    WHERE (
        (p_usuario_id IS NOT NULL AND usuario_id = p_usuario_id) OR
        (p_usuario_id IS NULL AND session_id = p_session_id)
    );
    
    RETURN v_count;
END$$
DELIMITER ;

-- ==================================================
-- FINALIZACIÓN
-- ==================================================

-- Mensaje de confirmación
SELECT 'Sistema de carrito instalado correctamente' AS mensaje;
SELECT 'Tablas creadas: carrito, facturas, detalle_factura' AS info;
SELECT 'Triggers creados para manejo automático de stock' AS info2;
SELECT 'Procedimientos y funciones creados' AS info3;
