-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 28-07-2025 a las 19:26:46
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `proyectoingenieria`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `actualizarCategoria` (IN `oldIdCategoria` VARCHAR(30), IN `newNombCategoria` VARCHAR(25), IN `newImagen` VARCHAR(255))   BEGIN
    DECLARE newIdCategoria VARCHAR(30);
    CALL updateIdCategoria(oldIdCategoria, newNombCategoria, newIdCategoria);
    UPDATE CATEGORIA
    SET nombCategoria = newNombCategoria,
        imagen = newImagen
    WHERE idCategoria = oldIdCategoria;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `actualizarMarca` (IN `oldIdMarca` VARCHAR(10), IN `newNombMarca` VARCHAR(50))   BEGIN
    DECLARE newIdMarca VARCHAR(10);
    CALL updateIdMarca(oldIdMarca, newNombMarca, newIdMarca);
    UPDATE MARCA
    SET nombMarca = newNombMarca
    WHERE idMarca = oldIdMarca;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `actualizarProducto` (IN `oldIdProducto` VARCHAR(30), IN `newNombProducto` VARCHAR(25), IN `newModelo` VARCHAR(25), IN `newPrecio` DECIMAL(10,2), IN `newStock` INT, IN `newImagen` VARCHAR(255), IN `newIdMarca` VARCHAR(10), IN `newIdCategoria` VARCHAR(10))   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `agregarCategoria` (IN `nombreCategoria` VARCHAR(25), IN `imagen` VARCHAR(255))   BEGIN
    DECLARE nuIdCategoria VARCHAR(30);
    CALL mkeIdCategoria(nombreCategoria, nuIdCategoria);
    INSERT INTO CATEGORIA (idCategoria, nombCategoria, imagen) VALUES (nuIdCategoria, nombreCategoria, imagen);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `agregarMarca` (IN `nombreMarca` VARCHAR(50))   BEGIN
    DECLARE nuIdMarca VARCHAR(10);
    CALL mkeIdMarca(nombreMarca, nuIdMarca);
    INSERT INTO MARCA (idMarca, nombMarca) VALUES (nuIdMarca, nombreMarca);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `agregarProducto` (IN `nombreProducto` VARCHAR(25), IN `modelo` VARCHAR(25), IN `precio` DECIMAL(10,2), IN `stock` INT, IN `imagen` VARCHAR(255), IN `idMarca` VARCHAR(10), IN `idCategoria` VARCHAR(10))   BEGIN
    DECLARE nuIdProducto VARCHAR(30);
    CALL mkeIdProducto(nombreProducto, idMarca, nuIdProducto);
    INSERT INTO PRODUCTO (idProducto, nombProducto, modelo, precio, stock, imagen, idMarca, idCategoria)
    VALUES (nuIdProducto, nombreProducto, modelo, precio, stock, imagen, idMarca, idCategoria);
    
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `eliminarCategoria` (IN `elim_idCategoria` VARCHAR(30))   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `eliminarMarca` (IN `elim_idMarca` VARCHAR(10))   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `eliminarProducto` (IN `elim_idProducto` VARCHAR(30))   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `mkeIdCategoria` (IN `nombreCategoria` VARCHAR(25), OUT `nuIdCategoria` VARCHAR(30))   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `mkeIdMarca` (IN `nombreMarca` VARCHAR(50), OUT `nuIdMarca` VARCHAR(10))   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `mkeIdProducto` (IN `nombreProducto` VARCHAR(25), IN `inIdMarca` VARCHAR(20), OUT `nuIdProducto` VARCHAR(30))   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `updateIdCategoria` (IN `oldIdCategoria` VARCHAR(30), IN `newNombCategoria` VARCHAR(25), OUT `newIdCategoria` VARCHAR(30))   BEGIN
    DECLARE partNumb INT;
    DECLARE partNomb VARCHAR(5);
    DECLARE idCategoria VARCHAR(30);
    SET partNumb = RIGHT(oldIdCategoria, 5);
    SET partNomb = LEFT(UPPER(newNombCategoria), 4);
    SET idCategoria = CONCAT(partNomb, partNumb);
    SET newIdCategoria = idCategoria;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `updateIdMarca` (IN `oldIdMarca` VARCHAR(10), IN `newNombMarca` VARCHAR(50), OUT `newIdMarca` VARCHAR(10))   BEGIN
    DECLARE partNumb INT;
    DECLARE partNomb VARCHAR(5);
    DECLARE idMarca VARCHAR(10);    
    SET partNumb = RIGHT(oldIdMarca, 5);
    SET partNomb = LEFT(UPPER(newNombMarca), 4);
    SET idMarca = CONCAT(partNomb, partNumb);
    SET newIdMarca = idMarca;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `updateIdProducto` (IN `oldIdProducto` VARCHAR(30), IN `newNombProducto` VARCHAR(25), IN `newMarca` VARCHAR(10), OUT `newIdProducto` VARCHAR(30))   BEGIN
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
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito`
--

CREATE TABLE `carrito` (
  `idCarrito` varchar(30) NOT NULL,
  `idUsuario` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `carrito`
--

INSERT INTO `carrito` (`idCarrito`, `idUsuario`) VALUES
('CART_SahidM', 'SahidM'),
('CART_USU-0001_1753715136', 'USU-0001'),
('CART_USU-0002_1753716503', 'USU-0002');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito_detalle`
--

CREATE TABLE `carrito_detalle` (
  `idCarritoDetalle` varchar(50) NOT NULL,
  `idCarrito` varchar(30) NOT NULL,
  `idProducto` varchar(30) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precioTotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `carrito_detalle`
--

INSERT INTO `carrito_detalle` (`idCarritoDetalle`, `idCarrito`, `idProducto`, `cantidad`, `precioTotal`) VALUES
('CD_USU-0001_0001REREDR_1753715143', 'CART_USU-0001_1753715136', '0001REREDR', 2, 84.00),
('CD_USU-0001_0001TPTP-L_1753715144', 'CART_USU-0001_1753715136', '0001TPTP-L', 1, 22.99),
('CD_USU-0002_0001REREDR_1753716512', 'CART_USU-0002_1753716503', '0001REREDR', 1, 42.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `idCategoria` varchar(10) NOT NULL,
  `nombCategoria` varchar(25) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`idCategoria`, `nombCategoria`, `imagen`) VALUES
('ACCE4', 'ACCESORIOS DE COMPUTADORA', 'image/img_categorias/6840de0f603de_mesientotriste.png'),
('ALMA3', 'ALMACENAMIENTO', 'image/img_categorias/6840dbbaee885_almacenamiento.png'),
('AUDI6', 'AUDIO Y SONIDO', 'image/img_categorias/684346f576a3b_Aysond.png'),
('CELU2', 'CELULARES', 'image/img_categorias/68433ce95d214_celulares.png'),
('COMP1', 'COMPUTADORAS', 'image/img_categorias/68433cf633e18_laptopsypc.png'),
('CONS7', 'CONSOLAS Y VIDEOJUEGOS', 'image/img_categorias/6843473f22f77_consolas y videojuegos.png'),
('ENER10', 'ENERGIA Y CARGA', 'image/img_categorias/6843495a44984_energiaYcarga.png'),
('HOGA8', 'HOGAR INTELIGENTE', 'image/img_categorias/6843475bd279a_hogarintel.png'),
('IMPR5', 'IMPRESORAS Y ESCANERES', 'image/img_categorias/6840de5d8a0ac_impresorasyescaneres.png'),
('MONI9', 'MONITORES Y PANTALLAS', 'image/img_categorias/68434934ed490_pantallas.png');

--
-- Disparadores `categoria`
--
DELIMITER $$
CREATE TRIGGER `editUpNombCateg` BEFORE UPDATE ON `categoria` FOR EACH ROW BEGIN
    SET NEW.nombCategoria = UPPER(NEW.nombCategoria);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `upNombCateg` BEFORE INSERT ON `categoria` FOR EACH ROW BEGIN
    SET NEW.nombCategoria = UPPER(NEW.nombCategoria);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cat_eliminados`
--

CREATE TABLE `cat_eliminados` (
  `idElimCateg` varchar(20) NOT NULL,
  `idCategoria` varchar(10) NOT NULL,
  `nombCategoria` varchar(25) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `fechaEliminacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Disparadores `cat_eliminados`
--
DELIMITER $$
CREATE TRIGGER `mkeIdElimCategoria` BEFORE INSERT ON `cat_eliminados` FOR EACH ROW BEGIN
    SET NEW.idElimCateg = CONCAT('ELIM_', NEW.idCategoria);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_factura`
--

CREATE TABLE `detalle_factura` (
  `idDetalleFactura` varchar(70) NOT NULL,
  `idFactura` varchar(45) NOT NULL,
  `idProducto` varchar(30) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precioTotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_factura`
--

INSERT INTO `detalle_factura` (`idDetalleFactura`, `idFactura`, `idProducto`, `cantidad`, `precioTotal`) VALUES
('FAC250728192228a1bb888D001', 'FAC250728192228a1bb888', '0001MIMICR', 1, 79.99);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `factura`
--

CREATE TABLE `factura` (
  `idFactura` varchar(45) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `idUsuario` varchar(20) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `ITBMS` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `factura`
--

INSERT INTO `factura` (`idFactura`, `fecha`, `hora`, `idUsuario`, `subtotal`, `ITBMS`, `total`) VALUES
('FAC250728192228a1bb888', '2025-07-28', '12:22:28', 'SahidM', 79.99, 5.60, 85.59);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marca`
--

CREATE TABLE `marca` (
  `idMarca` varchar(10) NOT NULL,
  `nombMarca` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `marca`
--

INSERT INTO `marca` (`idMarca`, `nombMarca`) VALUES
('ACTI24', 'ACTIVISION'),
('AMAZ25', 'AMAZON'),
('ANKE34', 'ANKER'),
('AOC33', 'AOC'),
('APPL7', 'APPLE'),
('ASUS1', 'ASUS'),
('BELK35', 'BELKIN'),
('BENQ32', 'BENQ'),
('BROT29', 'BROTHER'),
('CANO30', 'CANON'),
('DELL5', 'DELL'),
('EPSO28', 'EPSON'),
('GOOG26', 'GOOGLE'),
('HP2', 'HP'),
('HUAW3', 'HUAWEI'),
('HYPE20', 'HYPERX'),
('JBL19', 'JBL'),
('KING13', 'KINGSTON'),
('LENO4', 'LENOVO'),
('LG31', 'LG'),
('LOGI14', 'LOGITECH'),
('MICR16', 'MICROSOFT'),
('MOTO9', 'MOTOROLA'),
('NINT23', 'NINTENDO'),
('PLAY21', 'PLAYSTATION'),
('REDR15', 'REDRAGON'),
('SAMS6', 'SAMSUNG'),
('SAND12', 'SANDISK'),
('SEAG10', 'SEAGATE'),
('SONY18', 'SONY'),
('TAPO27', 'TAPO'),
('TP-L17', 'TP-LINK'),
('WEST11', 'WESTERN DIGITAL'),
('XBOX22', 'XBOX'),
('XIAO8', 'XIAOMI');

--
-- Disparadores `marca`
--
DELIMITER $$
CREATE TRIGGER `editUpNombMarc` BEFORE UPDATE ON `marca` FOR EACH ROW BEGIN
    SET NEW.nombMarca = UPPER(NEW.nombMarca);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marc_categ`
--

CREATE TABLE `marc_categ` (
  `idMarcCat` int(11) NOT NULL,
  `idMarca` varchar(10) DEFAULT NULL,
  `idCategoria` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `marc_categ`
--

INSERT INTO `marc_categ` (`idMarcCat`, `idMarca`, `idCategoria`) VALUES
(6, 'APPL7', 'CELU2'),
(1, 'ASUS1', 'COMP1'),
(4, 'DELL5', 'COMP1'),
(2, 'HP2', 'COMP1'),
(3, 'LENO4', 'COMP1'),
(5, 'SAMS6', 'CELU2'),
(7, 'XIAO8', 'CELU2');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `m_eliminados`
--

CREATE TABLE `m_eliminados` (
  `idElimMarca` varchar(20) NOT NULL,
  `idMarca` varchar(10) NOT NULL,
  `nombMarca` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Disparadores `m_eliminados`
--
DELIMITER $$
CREATE TRIGGER `mkeIdElimMarca` BEFORE INSERT ON `m_eliminados` FOR EACH ROW BEGIN
    SET NEW.idElimMarca = CONCAT('ELIM_', NEW.idMarca);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `idProducto` varchar(30) NOT NULL,
  `nombProducto` varchar(25) NOT NULL,
  `modelo` varchar(25) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `idMarca` varchar(10) NOT NULL,
  `idCategoria` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`idProducto`, `nombProducto`, `modelo`, `precio`, `stock`, `imagen`, `idMarca`, `idCategoria`) VALUES
('0001ACELDE', 'Elden Ring GameKey', 'Elden-Ring', 25.99, 10, 'image/img_productos/6849dca73ba73_Elden.png', 'ACTI24', 'CONS7'),
('0001AMAMAZ', 'Amazon Echo Dot', 'Echo-Dot-5', 49.99, 10, 'image/img_productos/684c6c3cd339a_amazonwea.png', 'AMAZ25', 'HOGA8'),
('0001ANANKE', 'Anker PowerCore', 'PowerCore-20K', 49.99, 10, 'image/img_productos/684c6df36745f_anker.png', 'ANKE34', 'ENER10'),
('0001AOAOCC', 'AOC C24G1A', 'C24G1A', 169.99, 10, 'image/img_productos/684c6facbd53b_AOCPantalla.png', 'AOC33', 'MONI9'),
('0001APIPHO', 'iPhone 13', 'iPhone-13', 799.00, 10, 'image/img_productos/684334d9ee00c_iphone-13.png', 'APPL7', 'CELU2'),
('0001ASASUS', 'ASUS 2801', 'N56VJ', 800.00, 10, 'image/img_productos/6832382d0716a_asusN56VJ.png', 'ASUS1', 'COMP1'),
('0001BEBELK', 'Belkin Wireless Charger', 'Wireless-10W', 29.99, 10, 'image/img_productos/684c6e114e1ce_belkin.png', 'BELK35', 'ENER10'),
('0001BEBENQ', 'BenQ GW2480', 'GW2480', 129.99, 10, 'image/img_productos/684c6f904611e_BenQ.png', 'BENQ32', 'MONI9'),
('0001BRBROT', 'Brother HL-L2370DW', 'HL-L2370DW', 149.99, 10, 'image/img_productos/68434039b103a_brother.png', 'BROT29', 'IMPR5'),
('0001CACANO', 'Canon CanoScan', 'LiDE-300', 79.99, 10, 'image/img_productos/684340ad251a7_Canon.png', 'CANO30', 'IMPR5'),
('0001DELAPT', 'Laptop BLU Dynamax', 'Future-2025', 1975.00, 10, 'image/img_productos/68375789e263b_pcfuturo.png', 'DELL5', 'COMP1'),
('0001EPEPSO', 'Epson EcoTank', 'L3210', 189.99, 10, 'image/img_productos/68433ff5a86b5_epson.png', 'EPSO28', 'IMPR5'),
('0001GOGOOG', 'Google Nest Hub', 'Nest-Hub-2', 99.00, 10, 'image/img_productos/684c6c9bb46e3_Google.png', 'GOOG26', 'HOGA8'),
('0001HPHPVI', 'HP Victus', 'Gaming Laptop', 900.00, 10, 'image/img_productos/68433d8876a43_victus.png', 'HP2', 'COMP1'),
('0001HUHUAW', 'Huawei Matebook', 'D15-2024', 1500.00, 10, 'image/img_productos/683757079d94f_huawei-matebook.png', 'HUAW3', 'COMP1'),
('0001HYHYPE', 'HyperX Cloud II', 'Cloud-II', 99.99, 10, 'image/img_productos/6849d975efa91_HyperX.png', 'HYPE20', 'AUDI6'),
('0001JBJBLF', 'JBL Flip 6', 'Flip-6', 129.00, 10, 'image/img_productos/6849d8e9636b9_jbl.png', 'JBL19', 'AUDI6'),
('0001KIKING', 'Kingston DataTraveler', 'DT-128GB', 18.50, 10, 'image/img_productos/684338944f067_kingston.png', 'KING13', 'ALMA3'),
('0001LELENO', 'Lenovo Astrum 5g', 'Astrum-5G', 850.99, 10, 'image/img_productos/683761580a4aa_lenovoPC.png', 'LENO4', 'COMP1'),
('0001LGLGUL', 'LG UltraGear', '27GN800-B', 299.99, 10, 'image/img_productos/684c6f2ca740c_LGultragear.png', 'LG31', 'MONI9'),
('0001LOLOGI', 'Logitech MX Master', 'MX-3S', 99.99, 10, 'image/img_productos/68433b990cfec_logitic.png', 'LOGI14', 'ACCE4'),
('0001MIMICR', 'Microsoft Surface Arc', 'Arc-Mouse', 79.99, 9, 'image/img_productos/68433c4be7736_microsoft maus.png', 'MICR16', 'ACCE4'),
('0001MOMOTO', 'Motorola G85 5G', 'G85-Pro-Max', 299.99, 10, 'image/img_productos/68432f6a48779_motorola.png', 'MOTO9', 'CELU2'),
('0001NININT', 'Nintendo Switch OLED', 'Switch-OLED', 349.99, 10, 'image/img_productos/6849de551bcef_nintendo.png', 'NINT23', 'CONS7'),
('0001PLPLAY', 'PlayStation 5', 'PS5-Standard', 499.99, 10, 'image/img_productos/6849dd37e2795_ps5.png', 'PLAY21', 'CONS7'),
('0001REREDR', 'Redragon Kumara', 'K552', 42.00, 10, 'image/img_productos/68433c1339cb0_redragon teclao.png', 'REDR15', 'ACCE4'),
('0001SASAMS', 'Samsung Galaxy A54', 'A54-5G', 379.99, 10, 'image/img_productos/68432fdc65cee_Galaxy-A54-5G.png', 'SAMS6', 'CELU2'),
('0001SASAND', 'SanDisk Extreme SSD', 'Extreme-1TB', 119.99, 10, 'image/img_productos/68433872439b5_sandisk.png', 'SAND12', 'ALMA3'),
('0001SESEAG', 'Seagate Expansion', 'EXT-1TB', 49.99, 10, 'image/img_productos/684337cb37bad_expancion1t.png', 'SEAG10', 'ALMA3'),
('0001SOSONY', 'Sony WH-1000XM5', 'WH-1000XM5', 349.99, 10, 'image/img_productos/6849d8710e653_sony-wh-1000xm5.png', 'SONY18', 'AUDI6'),
('0001TATP-L', 'TP-Link Tapo C200', 'Tapo-C200', 34.99, 10, 'image/img_productos/684c6c6188fe5_camara.png', 'TAPO27', 'HOGA8'),
('0001TPTP-L', 'TP-Link Archer T3U', 'T3U-AC1300', 22.99, 10, 'image/img_productos/68433c76295c0_tplink.png', 'TP-L17', 'ACCE4'),
('0001WEWDMY', 'WD My Passport', 'MyPass-2TB', 74.99, 10, 'image/img_productos/684338003b60f_WD.png', 'WEST11', 'ALMA3'),
('0001XBXBOX', 'Xbox Series S', 'Series-S', 299.99, 10, 'image/img_productos/6849dd7b6e914_xbox.png', 'XBOX22', 'CONS7'),
('0001XIXIAO', 'Xiaomi Redmi Note 13', 'Note-13-Pro', 349.99, 10, 'image/img_productos/68433549dd16e_xiaomi13pro.png', 'XIAO8', 'CELU2'),
('0002ACCALL', 'Call of Duty MW3', 'MW3-2023', 79.99, 10, 'image/img_productos/6849df5087db1_codmw3.png', 'ACTI24', 'CONS7'),
('0002APAPPL', 'Apple AirPods Pro', 'AirPods-Pro-2', 249.00, 10, 'image/img_productos/6849d9529e2a3_airbuds.png', 'APPL7', 'AUDI6'),
('0002HPPCPR', 'PC pro + turbo', 'ProVersion', 2000.00, 10, 'image/img_productos/683757de4c171_rrrrr.png', 'HP2', 'COMP1'),
('0002PLCONT', 'Controlador DualSense', 'DualSense', 69.99, 10, 'image/img_productos/6849dec828295_dualsense.png', 'PLAY21', 'CONS7'),
('0002SASAMS', 'Samsung USB-C Charger', 'USB-C-25W', 19.99, 10, 'image/img_productos/684c6e54db93a_samsungCargador.png', 'SAMS6', 'ENER10'),
('0002TPREGL', 'Regleta inteligente', 'Kasa-Smart', 39.99, 10, 'image/img_productos/684c6e7848bd6_reglaKasa.png', 'TP-L17', 'ENER10'),
('0002XIXIAO', 'Xiaomi Mi Smart Plug', 'Smart-Plug', 19.99, 10, 'image/img_productos/684c6ce2ef882_xiaomiPlug.png', 'XIAO8', 'HOGA8'),
('0003HPHPPR', 'HP Pro Desk', 'ProDesk-2024', 700.99, 10, 'image/img_productos/6840d7bb937ae_hp pro desk.png', 'HP2', 'COMP1'),
('0003SASAMS', 'Samsung Smart Monitor', 'M8-32-4K', 689.99, 10, 'image/img_productos/684c6f4ec8c4b_samsungM8.png', 'SAMS6', 'MONI9'),
('0004HPHPDE', 'HP DeskJet', '2775', 74.99, 10, 'image/img_productos/68434019315f3_HP desk.png', 'HP2', 'IMPR5');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `p_eliminados`
--

CREATE TABLE `p_eliminados` (
  `idElimProduc` varchar(50) NOT NULL,
  `idProducto` varchar(30) NOT NULL,
  `nombProducto` varchar(25) NOT NULL,
  `modelo` varchar(25) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `idMarca` varchar(10) NOT NULL,
  `idCategoria` varchar(10) NOT NULL,
  `fechaEliminacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Disparadores `p_eliminados`
--
DELIMITER $$
CREATE TRIGGER `mkeIdProdElim` BEFORE INSERT ON `p_eliminados` FOR EACH ROW BEGIN
    SET NEW.idElimProduc = CONCAT('ELIM_', NEW.idProducto);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rango`
--

CREATE TABLE `rango` (
  `idRango` tinyint(4) NOT NULL,
  `nombRango` varchar(25) NOT NULL
) ;

--
-- Volcado de datos para la tabla `rango`
--

INSERT INTO `rango` (`idRango`, `nombRango`) VALUES
(0, 'Cliente'),
(1, 'Administrador');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `idUsuario` varchar(20) NOT NULL,
  `nombUsuario` varchar(50) NOT NULL,
  `passUsuario` varchar(50) NOT NULL,
  `emailUsuario` varchar(50) NOT NULL,
  `idRango` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`idUsuario`, `nombUsuario`, `passUsuario`, `emailUsuario`, `idRango`) VALUES
('SahidM', 'Sahid Eliel Morales Filos', 'hola123', 'Sahid1@gmail.com', 0),
('test_user_184256', 'Usuario de Prueba', 'test123', 'test184256@test.com', 0),
('USU-0001', 'admin', 'Admin123', 'admin@tecnoy.com', 1),
('USU-0002', 'usuario', 'User123', 'usuario@tecnoy.com', 0),
('USU-0003', 'cliente1', 'Cliente123', 'cliente1@email.com', 0),
('USU-0004', 'vendedor1', 'Vend123', 'vendedor@tecnoy.com', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD PRIMARY KEY (`idCarrito`),
  ADD KEY `idUsuario` (`idUsuario`);

--
-- Indices de la tabla `carrito_detalle`
--
ALTER TABLE `carrito_detalle`
  ADD PRIMARY KEY (`idCarritoDetalle`),
  ADD KEY `idCarrito` (`idCarrito`),
  ADD KEY `idProducto` (`idProducto`);

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`idCategoria`);

--
-- Indices de la tabla `cat_eliminados`
--
ALTER TABLE `cat_eliminados`
  ADD PRIMARY KEY (`idElimCateg`),
  ADD UNIQUE KEY `idCategoria` (`idCategoria`);

--
-- Indices de la tabla `detalle_factura`
--
ALTER TABLE `detalle_factura`
  ADD PRIMARY KEY (`idDetalleFactura`),
  ADD KEY `idFactura` (`idFactura`),
  ADD KEY `idProducto` (`idProducto`);

--
-- Indices de la tabla `factura`
--
ALTER TABLE `factura`
  ADD PRIMARY KEY (`idFactura`),
  ADD KEY `idUsuario` (`idUsuario`);

--
-- Indices de la tabla `marca`
--
ALTER TABLE `marca`
  ADD PRIMARY KEY (`idMarca`);

--
-- Indices de la tabla `marc_categ`
--
ALTER TABLE `marc_categ`
  ADD PRIMARY KEY (`idMarcCat`),
  ADD UNIQUE KEY `unique_marca_categoria` (`idMarca`,`idCategoria`),
  ADD KEY `idCategoria` (`idCategoria`);

--
-- Indices de la tabla `m_eliminados`
--
ALTER TABLE `m_eliminados`
  ADD PRIMARY KEY (`idElimMarca`),
  ADD UNIQUE KEY `idMarca` (`idMarca`),
  ADD UNIQUE KEY `nombMarca` (`nombMarca`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`idProducto`),
  ADD KEY `idMarca` (`idMarca`),
  ADD KEY `idCategoria` (`idCategoria`);

--
-- Indices de la tabla `p_eliminados`
--
ALTER TABLE `p_eliminados`
  ADD PRIMARY KEY (`idElimProduc`),
  ADD UNIQUE KEY `idProducto` (`idProducto`);

--
-- Indices de la tabla `rango`
--
ALTER TABLE `rango`
  ADD PRIMARY KEY (`idRango`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`idUsuario`),
  ADD UNIQUE KEY `passUsuario` (`passUsuario`),
  ADD KEY `idRango` (`idRango`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `marc_categ`
--
ALTER TABLE `marc_categ`
  MODIFY `idMarcCat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD CONSTRAINT `carrito_ibfk_1` FOREIGN KEY (`idUsuario`) REFERENCES `usuario` (`idUsuario`);

--
-- Filtros para la tabla `carrito_detalle`
--
ALTER TABLE `carrito_detalle`
  ADD CONSTRAINT `carrito_detalle_ibfk_1` FOREIGN KEY (`idCarrito`) REFERENCES `carrito` (`idCarrito`),
  ADD CONSTRAINT `carrito_detalle_ibfk_2` FOREIGN KEY (`idProducto`) REFERENCES `producto` (`idProducto`);

--
-- Filtros para la tabla `detalle_factura`
--
ALTER TABLE `detalle_factura`
  ADD CONSTRAINT `detalle_factura_ibfk_1` FOREIGN KEY (`idFactura`) REFERENCES `factura` (`idFactura`),
  ADD CONSTRAINT `detalle_factura_ibfk_2` FOREIGN KEY (`idProducto`) REFERENCES `producto` (`idProducto`);

--
-- Filtros para la tabla `factura`
--
ALTER TABLE `factura`
  ADD CONSTRAINT `factura_ibfk_1` FOREIGN KEY (`idUsuario`) REFERENCES `usuario` (`idUsuario`);

--
-- Filtros para la tabla `marc_categ`
--
ALTER TABLE `marc_categ`
  ADD CONSTRAINT `marc_categ_ibfk_1` FOREIGN KEY (`idMarca`) REFERENCES `marca` (`idMarca`),
  ADD CONSTRAINT `marc_categ_ibfk_2` FOREIGN KEY (`idCategoria`) REFERENCES `categoria` (`idCategoria`);

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `producto_ibfk_1` FOREIGN KEY (`idMarca`) REFERENCES `marca` (`idMarca`),
  ADD CONSTRAINT `producto_ibfk_2` FOREIGN KEY (`idCategoria`) REFERENCES `categoria` (`idCategoria`);

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`idRango`) REFERENCES `rango` (`idRango`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
