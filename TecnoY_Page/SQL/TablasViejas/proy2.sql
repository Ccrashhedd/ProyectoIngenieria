-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 11-07-2025 a las 21:26:57
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `proy2`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `ActualizarCantidadCarrito` (IN `p_carrito_id` INT, IN `p_nueva_cantidad` INT)   BEGIN
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `AgregarAlCarrito` (IN `p_usuario_id` INT, IN `p_session_id` VARCHAR(255), IN `p_producto_id` INT, IN `p_cantidad` INT)   BEGIN
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `finalizar_compra` (IN `p_usuario_id` INT, IN `p_session_id` VARCHAR(255), IN `p_nombre_cliente` VARCHAR(255), IN `p_email_cliente` VARCHAR(255), IN `p_telefono_cliente` VARCHAR(20), IN `p_direccion_cliente` TEXT, IN `p_notas` TEXT, OUT `p_factura_id` INT, OUT `p_total` DECIMAL(10,2))   BEGIN
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

--
-- Funciones
--
CREATE DEFINER=`root`@`localhost` FUNCTION `ContarItemsCarrito` (`p_usuario_id` INT, `p_session_id` VARCHAR(255)) RETURNS INT(11) DETERMINISTIC READS SQL DATA BEGIN
    DECLARE v_count INT DEFAULT 0;
    
    SELECT COALESCE(SUM(cantidad), 0) INTO v_count
    FROM carrito
    WHERE (
        (p_usuario_id IS NOT NULL AND usuario_id = p_usuario_id) OR
        (p_usuario_id IS NULL AND session_id = p_session_id)
    );
    
    RETURN v_count;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `ObtenerTotalCarrito` (`p_usuario_id` INT, `p_session_id` VARCHAR(255)) RETURNS DECIMAL(10,2) DETERMINISTIC READS SQL DATA BEGIN
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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito`
--

CREATE TABLE `carrito` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `session_id` varchar(255) DEFAULT NULL,
  `fecha_agregado` datetime DEFAULT current_timestamp(),
  `fecha_actualizado` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Disparadores `carrito`
--
DELIMITER $$
CREATE TRIGGER `after_carrito_delete` AFTER DELETE ON `carrito` FOR EACH ROW BEGIN
    -- Restaurar stock
    UPDATE productos 
    SET stock = stock + OLD.cantidad 
    WHERE id = OLD.producto_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_carrito_insert` AFTER INSERT ON `carrito` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_carrito_update` AFTER UPDATE ON `carrito` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `imagen`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(5, 'Computadoras', 'image/img_categorias/68433cf633e18_laptopsypc.png', '2025-05-24 15:02:45', '2025-06-06 14:09:42'),
(6, 'Celulares', 'image/img_categorias/68433ce95d214_celulares.png', '2025-05-28 12:30:54', '2025-06-06 14:09:29'),
(9, 'Almacenamiento', 'image/img_categorias/6840dbbaee885_almacenamiento.png', '2025-06-04 18:49:56', '2025-06-04 18:50:18'),
(10, 'Accesorios de Computadora', 'image/img_categorias/6840de0f603de_mesientotriste.png', '2025-06-04 19:00:15', '2025-06-06 14:09:55'),
(11, 'Impresoras y Escaneres', 'image/img_categorias/6840de5d8a0ac_impresorasyescaneres.png', '2025-06-04 19:01:33', '2025-06-06 14:10:01'),
(12, 'Audio y sonido', 'image/img_categorias/684346f576a3b_Aysond.png', '2025-06-06 14:52:21', '2025-06-06 14:52:21'),
(13, 'Consolas y Videojuegos', 'image/img_categorias/6843473f22f77_consolas y videojuegos.png', '2025-06-06 14:52:48', '2025-06-06 14:53:35'),
(14, 'Hogar inteligente', 'image/img_categorias/6843475bd279a_hogarintel.png', '2025-06-06 14:54:03', '2025-06-06 14:54:03'),
(15, 'Monitores y pantallas', 'image/img_categorias/68434934ed490_pantallas.png', '2025-06-06 15:01:56', '2025-06-06 15:01:56'),
(16, 'Energia y Carga', 'image/img_categorias/6843495a44984_energiaYcarga.png', '2025-06-06 15:02:34', '2025-06-06 15:02:34');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_factura`
--

CREATE TABLE `detalle_factura` (
  `id` int(11) NOT NULL,
  `factura_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `producto_nombre` varchar(255) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas`
--

CREATE TABLE `facturas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `estado` enum('pendiente','pagada','enviada','entregada','cancelada') DEFAULT 'pendiente',
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `nombre_cliente` varchar(255) NOT NULL,
  `email_cliente` varchar(255) NOT NULL,
  `telefono_cliente` varchar(20) DEFAULT NULL,
  `direccion_cliente` text DEFAULT NULL,
  `notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `categoria_id` int(11) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `precio`, `imagen`, `categoria_id`, `stock`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'ASUS 2801', 'computadora potente, tan potente que es inexistente', 800.00, 'image/img_productos/6832382d0716a_asusN56VJ.png', 5, 10, '2025-05-24 16:20:45', '2025-07-11 14:22:15'),
(2, 'HP victus', 'Laptop gaming victus más rapida que la mia', 900.00, 'image/img_productos/68433d8876a43_victus.png', 5, 10, '2025-05-28 12:32:11', '2025-07-11 14:24:56'),
(3, 'Huawei Matebook ', 'La mejor laptop al precio más caro', 1500.00, 'image/img_productos/683757079d94f_huawei-matebook.png', 5, 10, '2025-05-28 13:33:43', '2025-06-30 13:23:02'),
(4, 'Laptop BLU Dynamax', 'hecha con materiales de Elon Musk, esta pantalla es de un solo uso y se desecha, cómprala y notarás la diferencia', 1975.00, 'image/img_productos/68375789e263b_pcfuturo.png', 5, 10, '2025-05-28 13:35:53', '2025-06-30 15:07:36'),
(5, 'PC +turbo version', 'El mejor pc para los trabajos más delicados', 2000.00, 'image/img_productos/683757de4c171_rrrrr.png', 5, 10, '2025-05-28 13:37:18', '2025-06-30 15:01:29'),
(6, 'lenovo Astrum 5g', 'laptop lenovo, nueva en el mercado a partir del 2027, la calidad de video aumenta gracias a su RTX 9050.', 850.99, 'image/img_productos/683761580a4aa_lenovoPC.png', 5, 10, '2025-05-28 14:17:44', '2025-06-30 13:23:02'),
(8, 'HP pro desk ', 'esta computadora es... no se, preguntenle a chat GPT', 700.99, 'image/img_productos/6840d7bb937ae_hp pro desk.png', 5, 10, '2025-06-04 18:33:15', '2025-06-30 13:23:02'),
(9, 'Motorola G85 5G pro max 2025', '5G, 256GB, cámara dual 50MP + gran angular\r\nGran capacidad y velocidad con estilo moderno.', 299.99, 'image/img_productos/68432f6a48779_motorola.png', 6, 10, '2025-06-06 13:11:54', '2025-06-30 13:23:02'),
(10, 'Samsung Galaxy A54 5G', 'Pantalla Super AMOLED de 6.4\", cámara triple 50MP\r\nPotente, moderno y con conectividad 5G al alcance de tu mano.', 379.99, 'image/img_productos/68432fdc65cee_Galaxy-A54-5G.png', 6, 10, '2025-06-06 13:13:48', '2025-06-30 13:23:02'),
(11, 'iPhone 13', 'Chip A15 Bionic, doble cámara de 12MP, Face ID\r\nElegancia y rendimiento premium en un solo dispositivo.', 799.00, 'image/img_productos/684334d9ee00c_iphone-13.png', 6, 10, '2025-06-06 13:35:05', '2025-06-30 13:23:02'),
(12, 'Xiaomi Redmi Note 13 Pro', 'Pantalla AMOLED 120Hz, Snapdragon 7s Gen 2\r\nRendimiento fluido y pantalla impresionante para todo momento.', 349.99, 'image/img_productos/68433549dd16e_xiaomi13pro.png', 6, 10, '2025-06-06 13:36:57', '2025-06-30 13:23:02'),
(13, 'Seagate Expansion 1TB', 'Disco duro externo portátil USB 3.0\r\nLleva tus archivos a todas partes con seguridad y facilidad.', 49.99, 'image/img_productos/684337cb37bad_expancion1t.png', 9, 10, '2025-06-06 13:47:39', '2025-06-30 13:23:02'),
(14, 'WD My Passport 2TB', 'Diseño compacto, protección por contraseña. Tu información segura y siempre contigo.', 74.99, 'image/img_productos/684338003b60f_WD.png', 9, 10, '2025-06-06 13:48:32', '2025-06-30 13:23:02'),
(15, 'SanDisk Extreme SSD 1TB', 'Velocidad de lectura hasta 1050MB/s, resistente a golpes\r\nUltra rápido y resistente, ideal para los más exigentes.', 119.99, 'image/img_productos/68433872439b5_sandisk.png', 9, 10, '2025-06-06 13:50:26', '2025-06-30 13:23:02'),
(16, 'Kingston DataTraveler 128GB', 'USB 3.2, pequeño y confiable para uso diario\r\nSolución confiable para guardar y mover tus archivos con rapidez.', 18.50, 'image/img_productos/684338944f067_kingston.png', 9, 10, '2025-06-06 13:51:00', '2025-06-30 13:23:02'),
(17, 'Logitech MX Master 3S', 'Mouse inalámbrico ergonómico con múltiples funciones\r\n\r\nControl total y comodidad premium para largas jornadas.', 99.99, 'image/img_productos/68433b990cfec_logitic.png', 10, 10, '2025-06-06 14:03:53', '2025-06-30 13:23:02'),
(18, 'Redragon Kumara K552', 'Teclado mecánico compacto para gaming\r\nHaz que cada pulsación cuente en tus partidas.', 42.00, 'image/img_productos/68433c1339cb0_redragon teclao.png', 10, 10, '2025-06-06 14:05:55', '2025-07-07 13:36:21'),
(19, 'Microsoft Surface Arc ', 'Ultradelgado, plegable, diseño minimalista \r\nDiseño moderno que se adapta a tu estilo en movimiento. (es horrible. att. Frontend)', 79.99, 'image/img_productos/68433c4be7736_microsoft maus.png', 10, 10, '2025-06-06 14:06:51', '2025-06-30 13:23:02'),
(20, 'TP-Link Archer T3U', 'Adaptador Wi-Fi USB AC1300 de doble banda\r\nConéctate rápido y sin cables con señal estable.', 22.99, 'image/img_productos/68433c76295c0_tplink.png', 10, 10, '2025-06-06 14:07:34', '2025-06-30 13:23:02'),
(21, 'Epson EcoTank L3210', 'Multifuncional sin cartuchos, ideal para oficinas\r\nImprime más y ahorra más sin sacrificar calidad.', 189.99, 'image/img_productos/68433ff5a86b5_epson.png', 11, 10, '2025-06-06 14:22:29', '2025-06-30 13:23:02'),
(22, 'HP DeskJet 2775', 'Impresora multifunción económica para el hogar\r\nSolución completa para tu hogar a un gran precio.', 74.99, 'image/img_productos/68434019315f3_HP desk.png', 11, 10, '2025-06-06 14:23:05', '2025-06-30 13:23:02'),
(23, 'Brother HL-L2370DW', 'Impresora láser monocromática, conexión Wi-Fi\r\nImpresión rápida y eficiente para tu productividad.', 149.99, 'image/img_productos/68434039b103a_brother.png', 11, 10, '2025-06-06 14:23:37', '2025-06-30 13:23:02'),
(24, 'Canon CanoScan LiDE 300', 'Escáner plano de alta resolución, diseño compacto\r\nEscanea tus documentos con claridad y estilo.', 79.99, 'image/img_productos/684340ad251a7_Canon.png', 11, 10, '2025-06-06 14:25:33', '2025-06-30 13:23:02'),
(25, 'Sony WH-1000XM5', 'Auriculares inalámbricos con cancelación de ruido y hasta 30h de batería\r\nSumérgete en tu música con el mejor sonido sin distracciones.', 349.99, 'image/img_productos/6849d8710e653_sony-wh-1000xm5.png', 12, 10, '2025-06-11 14:26:41', '2025-06-30 13:23:02'),
(26, 'JBL Flip 6', 'Bocina Bluetooth portátil, resistente al agua, 12h de reproducción\r\nLleva tu música a cualquier parte con estilo y potencia.', 129.00, 'image/img_productos/6849d8e9636b9_jbl.png', 12, 10, '2025-06-11 14:28:41', '2025-06-30 13:23:02'),
(27, 'Apple AirPods Pro (2.ª gen)', 'Cancelación activa de ruido, modo ambiente, estuche MagSafe\r\nSonido envolvente y comodidad total, al estilo Apple.', 249.00, 'image/img_productos/6849d9529e2a3_airbuds.png', 12, 10, '2025-06-11 14:30:26', '2025-06-30 13:23:02'),
(28, 'HyperX Cloud II', 'Auriculares con micrófono para gaming, sonido envolvente 7.1\r\n Juega con audio profesional y comunicación clara.', 99.99, 'image/img_productos/6849d975efa91_HyperX.png', 12, 10, '2025-06-11 14:31:01', '2025-06-30 13:23:02'),
(29, 'Elden Ring GameKey', 'Compra una Key única para descargar este juego, mata tus neuronas hasta las 3 de la mañana y arrepiéntete de esta compra tras morir una y otra vez jugando Elden Ring en multijugador', 25.99, 'image/img_productos/6849dca73ba73_Elden.png', 13, 10, '2025-06-11 14:44:39', '2025-07-07 13:36:17'),
(30, 'PlayStation 5 Standard', 'Consola con unidad de disco, 825GB SSD, DualSense\r\nVive la nueva generación de videojuegos con potencia y realismo.', 499.99, 'image/img_productos/6849dd37e2795_ps5.png', 13, 10, '2025-06-11 14:47:03', '2025-06-30 13:23:02'),
(31, 'Xbox Series S', 'Consola digital, 512GB SSD, rendimiento de nueva generación\r\nAccede al mundo Xbox a un precio accesible y veloz.', 299.99, 'image/img_productos/6849dd7b6e914_xbox.png', 13, 10, '2025-06-11 14:48:11', '2025-06-30 13:23:02'),
(32, 'Nintendo Switch OLED', 'Pantalla OLED de 7\", modo portátil y dock, 64GB\r\nDiversión híbrida donde quieras con una pantalla espectacular.', 349.99, 'image/img_productos/6849de551bcef_nintendo.png', 13, 10, '2025-06-11 14:51:49', '2025-06-30 13:23:02'),
(33, 'Controlador DualSense', 'Compatible con PS5, gatillos adaptativos, retroalimentación háptica\r\n Siente cada momento del juego con este control revolucionario.', 69.99, 'image/img_productos/6849dec828295_dualsense.png', 13, 10, '2025-06-11 14:53:44', '2025-06-30 13:23:02'),
(34, 'Call of Duty MW3', 'el más infravalorado de toda la saga, no lo supieron apreciar, pero era un juegazo, diviértete esperando lo que todo el mundo sabe que pasará (RIP Soap).', 79.99, 'image/img_productos/6849df5087db1_codmw3.png', 13, 10, '2025-06-11 14:56:00', '2025-06-30 13:23:02'),
(35, 'Amazon Echo Dot (5.ª gen)', 'Asistente Alexa, altavoz compacto, sensor de temperatura\r\nControla tu hogar con solo tu voz.', 49.99, 'image/img_productos/684c6c3cd339a_amazonwea.png', 14, 10, '2025-06-13 13:21:48', '2025-06-30 13:23:02'),
(36, 'TP-Link Tapo C200', 'Cámara Wi-Fi HD con visión nocturna, rotación y detección de movimiento\r\nSeguridad inteligente y accesible para tu hogar.', 34.99, 'image/img_productos/684c6c6188fe5_camara.png', 14, 10, '2025-06-13 13:22:25', '2025-06-30 13:23:02'),
(37, 'Google Nest Hub (2.ª gen)', 'Pantalla táctil de 7\", control de dispositivos, reloj inteligente\r\nTu casa conectada y organizada desde un solo lugar.', 99.00, 'image/img_productos/684c6c9bb46e3_Google.png', 14, 10, '2025-06-13 13:23:23', '2025-06-30 13:23:02'),
(38, 'Xiaomi Mi Smart Plug', 'Enchufe inteligente con Wi-Fi y control por app\r\nAutomatiza cualquier equipo eléctrico con tu celular.', 19.99, 'image/img_productos/684c6ce2ef882_xiaomiPlug.png', 14, 10, '2025-06-13 13:24:34', '2025-06-30 13:23:02'),
(39, 'Anker PowerCore 20,000mAh', 'Cargador portátil USB con carga rápida\r\nMantén tus dispositivos cargados todo el día, estés donde estés.', 49.99, 'image/img_productos/684c6df36745f_anker.png', 16, 10, '2025-06-13 13:29:07', '2025-06-30 13:23:02'),
(40, 'Base de carga inalámbrica Belkin', 'Compatible con Qi, hasta 10W, diseño antideslizante\r\nCarga tus dispositivos sin cables y sin complicaciones.', 29.99, 'image/img_productos/684c6e114e1ce_belkin.png', 16, 10, '2025-06-13 13:29:37', '2025-06-30 13:23:02'),
(41, 'Cargador rápido Samsung USB-C 25W', 'Compatible con carga rápida Super Fast Charging\r\nEnergía rápida y segura para tu Galaxy y más.', 19.99, 'image/img_productos/684c6e54db93a_samsungCargador.png', 16, 10, '2025-06-13 13:30:44', '2025-06-30 13:23:02'),
(42, 'Regleta inteligente Kasa', '6 tomas controlables por app, compatible con Alexa y Google\r\nDomina tu energía desde el celular con eficiencia total.', 39.99, 'image/img_productos/684c6e7848bd6_reglaKasa.png', 16, 10, '2025-06-13 13:31:20', '2025-06-30 13:23:02'),
(43, 'LG UltraGear 27GN800-B', 'Monitor gaming 27\", QHD, 144Hz, 1ms\r\nFluidez y detalle para dominar en cada partida.', 299.99, 'image/img_productos/684c6f2ca740c_LGultragear.png', 15, 10, '2025-06-13 13:34:20', '2025-06-30 13:23:02'),
(44, 'Samsung Smart Monitor M8', '32\", 4K UHD, apps integradas, altavoces y cámara\r\nUn monitor todo-en-uno para trabajo, estudio y streaming.', 689.99, 'image/img_productos/684c6f4ec8c4b_samsungM8.png', 15, 10, '2025-06-13 13:34:54', '2025-06-30 13:23:02'),
(45, 'BenQ GW2480', '24\", Full HD, panel IPS, cuidado visual\r\nComodidad visual con diseño sin bordes y calidad de imagen.', 129.99, 'image/img_productos/684c6f904611e_BenQ.png', 15, 10, '2025-06-13 13:36:00', '2025-06-30 13:23:02'),
(46, 'AOC C24G1A', 'Monitor curvo 24\", 165Hz, 1ms, FreeSync\r\nInmersión total con curvatura envolvente y alta velocidad.', 169.99, 'image/img_productos/684c6facbd53b_AOCPantalla.png', 15, 10, '2025-06-13 13:36:28', '2025-06-30 13:23:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nomb_user` varchar(50) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `rol` enum('admin','consulta') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nomb_user`, `contraseña`, `rol`) VALUES
(1, 'admin', 'Admin123', 'admin'),
(2, 'usuario', 'User123', 'consulta');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_producto` (`usuario_id`,`producto_id`),
  ADD UNIQUE KEY `unique_session_producto` (`session_id`,`producto_id`),
  ADD KEY `idx_carrito_usuario` (`usuario_id`),
  ADD KEY `idx_carrito_session` (`session_id`),
  ADD KEY `idx_carrito_producto` (`producto_id`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `detalle_factura`
--
ALTER TABLE `detalle_factura`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`),
  ADD KEY `idx_detalle_factura` (`factura_id`);

--
-- Indices de la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_facturas_usuario` (`usuario_id`),
  ADD KEY `idx_facturas_session` (`session_id`),
  ADD KEY `idx_facturas_estado` (`estado`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomb_user` (`nomb_user`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `carrito`
--
ALTER TABLE `carrito`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `detalle_factura`
--
ALTER TABLE `detalle_factura`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `facturas`
--
ALTER TABLE `facturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD CONSTRAINT `carrito_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carrito_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `detalle_factura`
--
ALTER TABLE `detalle_factura`
  ADD CONSTRAINT `detalle_factura_ibfk_1` FOREIGN KEY (`factura_id`) REFERENCES `facturas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_factura_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD CONSTRAINT `facturas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
