-- Funciones para la base de datos de la tienda

-- Drop if para seguridad 

-- DROP TRIGGERS

DROP TRIGGER IF EXISTS upNombMarc;
DROP TRIGGER IF EXISTS editUpNombMarc;
DROP TRIGGER IF EXISTS upNombCateg;
DROP TRIGGER IF EXISTS editUpNombCateg;
DROP TRIGGER IF EXISTS mkeIdProdElim;
DROP TRIGGER IF EXISTS mkeIdElimMarca;
DROP TRIGGER IF EXISTS mkeIdElimCategoria;
DROP TRIGGER IF EXISTS mkeIdFactura;
DROP TRIGGER IF EXISTS mkeIdDetalleFactura;


-- DROP PROCEDURES
DROP PROCEDURE IF EXISTS mkeIdProducto;
DROP PROCEDURE IF EXISTS updateIdProducto;
DROP PROCEDURE IF EXISTS agregarProducto;
DROP PROCEDURE IF EXISTS actualizarProducto;
DROP PROCEDURE IF EXISTS eliminarProducto;
DROP PROCEDURE IF EXISTS mkeIdMarca;
DROP PROCEDURE IF EXISTS updateIdMarca;
DROP PROCEDURE IF EXISTS agregarMarca;
DROP PROCEDURE IF EXISTS actualizarMarca;
DROP PROCEDURE IF EXISTS eliminarMarca;
DROP PROCEDURE IF EXISTS mkeIdCategoria;
DROP PROCEDURE IF EXISTS updateIdCategoria;
DROP PROCEDURE IF EXISTS agregarCategoria;
DROP PROCEDURE IF EXISTS actualizarCategoria;
DROP PROCEDURE IF EXISTS eliminarCategoria;





-- TRIGGERS

DELIMITER // -- Para que los nombres de marca sean mayuscula

CREATE TRIGGER upNombMarc 
BEFORE INSERT ON MARCA
FOR EACH ROW
BEGIN
    SET NEW.nombMarca = UPPER(NEW.nombMarca);
END;

DELIMITER ;



DELIMITER // -- En update tambien este en mayuscula marca

CREATE TRIGGER editUpNombMarc
BEFORE UPDATE ON MARCA
FOR EACH ROW
BEGIN
    SET NEW.nombMarca = UPPER(NEW.nombMarca);
END //

DELIMITER ;



DELIMITER // -- Para que los nombres de categoria sean mayuscula

CREATE TRIGGER upNombCateg
BEFORE INSERT ON CATEGORIA
FOR EACH ROW
BEGIN
    SET NEW.nombCategoria = UPPER(NEW.nombCategoria);
END //

DELIMITER ;


DELIMITER // -- En update tambien este en mayuscula categoria

CREATE TRIGGER editUpNombCateg
BEFORE UPDATE ON CATEGORIA
FOR EACH ROW
BEGIN
    SET NEW.nombCategoria = UPPER(NEW.nombCategoria);
END //

DELIMITER ;


-- /// TRIGGERS DE LAS TABLAS //////

-- Triggers Productos


DELIMITER //

CREATE TRIGGER mkeIdProdElim 
BEFORE INSERT ON P_ELIMINADOS
FOR EACH ROW
BEGIN
    SET NEW.idElimProduc = CONCAT('ELIM_', NEW.idProducto);
END //

DELIMITER ;


-- Triggers marcas


DELIMITER //

CREATE TRIGGER mkeIdElimMarca-- Genera un idCategoria unico al insertar una nueva categoria
BEFORE INSERT ON M_ELIMINADOS
FOR EACH ROW
BEGIN
    SET NEW.idElimMarca = CONCAT('ELIM_', NEW.idMarca);
END //

DELIMITER ;



-- Triggers categorias

DELIMITER //

CREATE TRIGGER mkeIdElimCategoria 
BEFORE INSERT ON CAT_ELIMINADOS
FOR EACH ROW
BEGIN
    SET NEW.idElimCateg = CONCAT('ELIM_', NEW.idCategoria);
END //

DELIMITER ;


-- Triggers Factura

DELIMITER //

CREATE TRIGGER mkeIdFactura -- Genera un idFactura unico al insertar una nueva factura
BEFORE INSERT ON FACTURA    
FOR EACH ROW
BEGIN
    SET NEW.idFactura = CONCAT('FACT-', LPAD((SELECT IFNULL(MAX(CAST(SUBSTRING(idFactura, 6) AS UNSIGNED)), 0) + 1 FROM FACTURA), 4, '0'));
END //

DELIMITER ;


-- Triggers Detalle Factura

DELIMITER //

CREATE TRIGGER mkeIdDetalleFactura -- Genera un idDetalleFactura unico al insertar un nuevo detalle de factura
BEFORE INSERT ON DETALLE_FACTURA
FOR EACH ROW
BEGIN
    SET NEW.idDetalleFactura = CONCAT('DET-', LPAD((SELECT IFNULL(MAX(CAST(SUBSTRING(idDetalleFactura, 5) AS UNSIGNED)), 0) + 1 FROM DETALLE_FACTURA), 4, '0'));
END //

DELIMITER ;



-- PROCEDURES

-- Procedure para idProducto

DELIMITER //

CREATE PROCEDURE mkeIdProducto(
    IN nombreProducto VARCHAR(25), 
    IN inIdMarca VARCHAR(20), 
    OUT nuIdProducto VARCHAR(30))
BEGIN
    DECLARE contadorProd INT;
    DECLARE codigoMarca VARCHAR(5);
    DECLARE numeroFormateado VARCHAR(5);
    DECLARE codigoProducto VARCHAR(5);
    DECLARE idProductoFinal VARCHAR(30);
    DECLARE nombreMarcaVar VARCHAR(50);

    -- Obtener el nombre de la marca
    SELECT nombMarca INTO nombreMarcaVar FROM MARCA WHERE idMarca = inIdMarca;
    
    -- Quitar espacios y obtener codigo de marca (primeros 2 caracteres)
    SET codigoMarca = UPPER(LEFT(REPLACE(nombreMarcaVar, ' ', ''), 2));
    
    -- Quitar espacios y obtener codigo de producto (primeros 4 caracteres)
    SET codigoProducto = LEFT(UPPER(REPLACE(nombreProducto, ' ', '')), 4);
    
    -- Contar productos existentes de esta marca + 1
    SET contadorProd = (SELECT IFNULL(COUNT(*), 0) + 1 FROM PRODUCTO P 
                       JOIN MARCA M ON P.idMarca = M.idMarca 
                       WHERE M.idMarca = inIdMarca);
    
    -- Formatear numero con LPAD
    SET numeroFormateado = LPAD(contadorProd, 4, '0');
    
    -- Crear ID final: numero + codigo marca + codigo producto
    SET idProductoFinal = CONCAT(numeroFormateado, codigoMarca, codigoProducto);
    SET nuIdProducto = idProductoFinal;
END//

DELIMITER ;

-- Procedure para actualizar idProducto
DELIMITER //

CREATE PROCEDURE updateIdProducto(
    IN oldIdProducto VARCHAR(30),
    IN newNombProducto VARCHAR(25),
    IN newMarca VARCHAR(10), 
    OUT newIdProducto VARCHAR(30))
BEGIN
    DECLARE numeroExistente INT;
    DECLARE codigoMarca VARCHAR(5);
    DECLARE codigoProducto VARCHAR(5);
    DECLARE numeroFormateado VARCHAR(5);
    DECLARE nombreMarcaVar VARCHAR(50);

    -- Obtener el numero del ID anterior (primeros 4 caracteres)
    SET numeroExistente = CAST(LEFT(oldIdProducto, 4) AS UNSIGNED);
    
    -- Obtener el nombre de la nueva marca
    SELECT nombMarca INTO nombreMarcaVar FROM MARCA WHERE idMarca = newMarca;
    
    -- Quitar espacios y obtener codigo de marca
    SET codigoMarca = UPPER(LEFT(REPLACE(nombreMarcaVar, ' ', ''), 2));
    
    -- Quitar espacios y obtener codigo de producto
    SET codigoProducto = LEFT(UPPER(REPLACE(newNombProducto, ' ', '')), 4);
    
    -- Formatear numero
    SET numeroFormateado = LPAD(numeroExistente, 4, '0');
    
    -- Crear nuevo ID: numero + codigo marca + codigo producto
    SET newIdProducto = CONCAT(numeroFormateado, codigoMarca, codigoProducto);
END //

DELIMITER ;


-- Procedure para agregar Producto
DELIMITER //

CREATE PROCEDURE agregarProducto(
    IN nombreProducto VARCHAR(25), 
    IN modelo VARCHAR(25), 
    IN precio DECIMAL(10,2), 
    IN stock INT, 
    IN imagen VARCHAR(255),
    IN idMarca VARCHAR(10), 
    IN idCategoria VARCHAR(10))
BEGIN
    DECLARE nuIdProducto VARCHAR(30);
    CALL mkeIdProducto(nombreProducto, idMarca, nuIdProducto);
    INSERT INTO PRODUCTO (idProducto, nombProducto, modelo, precio, stock, imagen, idMarca, idCategoria)
    VALUES (nuIdProducto, nombreProducto, modelo, precio, stock, imagen, idMarca, idCategoria);
    
END//

DELIMITER ;

-- Procedure para actualizar Producto

DELIMITER //

CREATE PROCEDURE actualizarProducto(
    IN oldIdProducto VARCHAR(30), 
    IN newNombProducto VARCHAR(25), 
    IN newModelo VARCHAR(25), 
    IN newPrecio DECIMAL(10,2), 
    IN newStock INT, 
    IN newImagen VARCHAR(255),
    IN newIdMarca VARCHAR(10), 
    IN newIdCategoria VARCHAR(10))
BEGIN
    DECLARE newIdProducto VARCHAR(30);
    CALL updateIdProducto(oldIdProducto, newNombProducto, newIdMarca, newIdProducto);
    UPDATE PRODUCTO
    SET nombProducto = newNombProducto, 
        modelo = newModelo, 
        precio = newPrecio, 
        stock = newStock, 
        imagen = newImagen,
        idMarca = newIdMarca, 
        idCategoria = newIdCategoria
    WHERE idProducto = oldIdProducto;
END //

DELIMITER ;

-- Procedure para eliminar producto

DELIMITER //

CREATE PROCEDURE eliminarProducto(
    IN elim_idProducto VARCHAR(30))
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    INSERT INTO P_ELIMINADOS (idProducto, nombProducto, modelo, precio, stock, imagen, idMarca, idCategoria)
    SELECT elim_idProducto, nombProducto, modelo, precio, stock, imagen, idMarca, idCategoria
    FROM PRODUCTO
    WHERE idProducto = elim_idProducto;

    DELETE FROM PRODUCTO WHERE idProducto = elim_idProducto;
    
    COMMIT;
END //

DELIMITER ;


-- Procedure para idMarca

DELIMITER //

CREATE PROCEDURE mkeIdMarca(
    IN nombreMarca VARCHAR(50), 
    OUT nuIdMarca VARCHAR(10))
BEGIN
    DECLARE contadorMarca INT;
    DECLARE codigoMarca VARCHAR(5);
    DECLARE idMarcaFinal VARCHAR(10);

    -- Contar marcas existentes + 1
    SET contadorMarca = (SELECT IFNULL(COUNT(*), 0) + 1 FROM MARCA);
    
    -- Quitar espacios y obtener codigo de marca (primeros 4 caracteres)
    SET codigoMarca = LEFT(UPPER(REPLACE(nombreMarca, ' ', '')), 4);
    
    -- Crear ID final: codigo + numero
    SET idMarcaFinal = CONCAT(codigoMarca, contadorMarca);
    SET nuIdMarca = idMarcaFinal;
END //  

DELIMITER ;

-- Procedure para actualizar idMarca

DELIMITER //

CREATE PROCEDURE updateIdMarca(
    IN oldIdMarca VARCHAR(10),
    IN newNombMarca VARCHAR(50),
    OUT newIdMarca VARCHAR(10))
BEGIN
    DECLARE partNumb INT;
    DECLARE partNomb VARCHAR(5);
    DECLARE idMarca VARCHAR(10);    
    SET partNumb = RIGHT(oldIdMarca, 5);
    SET partNomb = LEFT(UPPER(newNombMarca), 4);
    SET idMarca = CONCAT(partNomb, partNumb);
    SET newIdMarca = idMarca;
END //

DELIMITER ;

-- Procedure para agregar Marca

DELIMITER //

CREATE PROCEDURE agregarMarca(
    IN nombreMarca VARCHAR(50))
BEGIN
    DECLARE nuIdMarca VARCHAR(10);
    CALL mkeIdMarca(nombreMarca, nuIdMarca);
    INSERT INTO MARCA (idMarca, nombMarca) VALUES (nuIdMarca, nombreMarca);
END //

DELIMITER ;

-- Procedure para actualizar Marca
DELIMITER //

CREATE PROCEDURE actualizarMarca(
    IN oldIdMarca VARCHAR(10), 
    IN newNombMarca VARCHAR(50))
BEGIN
    DECLARE newIdMarca VARCHAR(10);
    CALL updateIdMarca(oldIdMarca, newNombMarca, newIdMarca);
    UPDATE MARCA
    SET nombMarca = newNombMarca
    WHERE idMarca = oldIdMarca;
END //

DELIMITER ;

-- Procedure para eliminar Marca
DELIMITER //

CREATE PROCEDURE eliminarMarca(
    IN elim_idMarca VARCHAR(10))
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    INSERT INTO M_ELIMINADOS (idMarca, nombMarca)
    SELECT elim_idMarca, nombMarca
    FROM MARCA
    WHERE idMarca = elim_idMarca;

    DELETE FROM MARCA WHERE idMarca = elim_idMarca;
    
    COMMIT;
END //

DELIMITER ;


-- Procedure para idCategoria

DELIMITER //

CREATE PROCEDURE mkeIdCategoria(
    IN nombreCategoria VARCHAR(25),  
    OUT nuIdCategoria VARCHAR(30))
BEGIN
    DECLARE contadorCategoria INT;
    DECLARE codigoCategoria VARCHAR(5);
    DECLARE idCategoriaFinal VARCHAR(30);

    -- Contar categorias existentes + 1
    SET contadorCategoria = (SELECT IFNULL(COUNT(*), 0) + 1 FROM CATEGORIA);
    
    -- Quitar espacios y obtener codigo de categoria (primeros 4 caracteres)
    SET codigoCategoria = LEFT(UPPER(REPLACE(nombreCategoria, ' ', '')), 4);
    
    -- Crear ID final: codigo + numero
    SET idCategoriaFinal = CONCAT(codigoCategoria, contadorCategoria);
    SET nuIdCategoria = idCategoriaFinal;
END //

DELIMITER ;

-- Procedure para actualizar idCategoria

DELIMITER //

CREATE PROCEDURE updateIdCategoria(
    IN oldIdCategoria VARCHAR(30),
    IN newNombCategoria VARCHAR(25),
    OUT newIdCategoria VARCHAR(30))
BEGIN
    DECLARE partNumb INT;
    DECLARE partNomb VARCHAR(5);
    DECLARE idCategoria VARCHAR(30);
    SET partNumb = RIGHT(oldIdCategoria, 5);
    SET partNomb = LEFT(UPPER(newNombCategoria), 4);
    SET idCategoria = CONCAT(partNomb, partNumb);
    SET newIdCategoria = idCategoria;
END //

DELIMITER ;


-- Procedure para agregar Categoria

DELIMITER //

CREATE PROCEDURE agregarCategoria(
    IN nombreCategoria VARCHAR(25),
    IN imagen VARCHAR(255))
BEGIN
    DECLARE nuIdCategoria VARCHAR(30);
    CALL mkeIdCategoria(nombreCategoria, nuIdCategoria);
    INSERT INTO CATEGORIA (idCategoria, nombCategoria, imagen) VALUES (nuIdCategoria, nombreCategoria, imagen);
END //

DELIMITER ;

-- Procedure para actualizar Categoria

DELIMITER //

CREATE PROCEDURE actualizarCategoria(
    IN oldIdCategoria VARCHAR(30), 
    IN newNombCategoria VARCHAR(25),
    IN newImagen VARCHAR(255))
BEGIN
    DECLARE newIdCategoria VARCHAR(30);
    CALL updateIdCategoria(oldIdCategoria, newNombCategoria, newIdCategoria);
    UPDATE CATEGORIA
    SET nombCategoria = newNombCategoria,
        imagen = newImagen
    WHERE idCategoria = oldIdCategoria;
END //

DELIMITER ;

-- Procedure para eliminar Categoria

DELIMITER //

CREATE PROCEDURE eliminarCategoria(
    IN elim_idCategoria VARCHAR(30))
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    INSERT INTO CAT_ELIMINADOS (idCategoria, nombCategoria, imagen)
    SELECT elim_idCategoria, nombCategoria, imagen
    FROM CATEGORIA
    WHERE idCategoria = elim_idCategoria;

    DELETE FROM CATEGORIA WHERE idCategoria = elim_idCategoria;
    
    COMMIT;
END //

DELIMITER ;








