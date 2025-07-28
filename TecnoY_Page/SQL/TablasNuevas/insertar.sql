-- =====================================================
-- SCRIPT DE INSERCIÓN DE DATOS PARA TecnoY
-- =====================================================
-- Este script utiliza los procedimientos almacenados para probar
-- que triggers y procedures estén funcionando correctamente

-- =====================================================
-- INSERCIÓN DE DATOS BÁSICOS (SIN PROCEDURES)
-- =====================================================

-- Insertar RANGOS (tabla sin procedure)
INSERT INTO RANGO (idRango, nombRango) VALUES 
(0, 'Cliente'),
(1, 'Administrador')
ON DUPLICATE KEY UPDATE nombRango = VALUES(nombRango);

-- Insertar USUARIOS (tabla sin procedure)
INSERT INTO USUARIO (idUsuario, nombUsuario, passUsuario, emailUsuario, idRango) VALUES
('USU-0001', 'admin', 'Admin123', 'admin@tecnoy.com', 1),
('USU-0002', 'usuario', 'User123', 'usuario@tecnoy.com', 0),
('USU-0003', 'cliente1', 'Cliente123', 'cliente1@email.com', 0),
('USU-0004', 'vendedor1', 'Vend123', 'vendedor@tecnoy.com', 1);

-- =====================================================
-- INSERCIÓN DE MARCAS USANDO PROCEDURES
-- =====================================================

-- Marcas de Computadoras
CALL agregarMarca('ASUS');
CALL agregarMarca('HP');
CALL agregarMarca('HUAWEI');
CALL agregarMarca('LENOVO');
CALL agregarMarca('DELL');

-- Marcas de Celulares
CALL agregarMarca('SAMSUNG');
CALL agregarMarca('APPLE');
CALL agregarMarca('XIAOMI');
CALL agregarMarca('MOTOROLA');

-- Marcas de Almacenamiento
CALL agregarMarca('SEAGATE');
CALL agregarMarca('WESTERN DIGITAL');
CALL agregarMarca('SANDISK');
CALL agregarMarca('KINGSTON');

-- Marcas de Accesorios
CALL agregarMarca('LOGITECH');
CALL agregarMarca('REDRAGON');
CALL agregarMarca('MICROSOFT');
CALL agregarMarca('TP-LINK');

-- Marcas de Audio
CALL agregarMarca('SONY');
CALL agregarMarca('JBL');
CALL agregarMarca('HYPERX');

-- Marcas de Gaming
CALL agregarMarca('PLAYSTATION');
CALL agregarMarca('XBOX');
CALL agregarMarca('NINTENDO');
CALL agregarMarca('ACTIVISION');

-- Marcas de Hogar Inteligente
CALL agregarMarca('AMAZON');
CALL agregarMarca('GOOGLE');
CALL agregarMarca('TAPO');

-- Marcas de Impresoras
CALL agregarMarca('EPSON');
CALL agregarMarca('BROTHER');
CALL agregarMarca('CANON');

-- Marcas de Monitores
CALL agregarMarca('LG');
CALL agregarMarca('BENQ');
CALL agregarMarca('AOC');

-- Marcas de Energía
CALL agregarMarca('ANKER');
CALL agregarMarca('BELKIN');

-- =====================================================
-- INSERCIÓN DE CATEGORÍAS USANDO PROCEDURES
-- =====================================================

CALL agregarCategoria('Computadoras', 'image/img_categorias/68433cf633e18_laptopsypc.png');
CALL agregarCategoria('Celulares', 'image/img_categorias/68433ce95d214_celulares.png');
CALL agregarCategoria('Almacenamiento', 'image/img_categorias/6840dbbaee885_almacenamiento.png');
CALL agregarCategoria('Accesorios de Computadora', 'image/img_categorias/6840de0f603de_mesientotriste.png');
CALL agregarCategoria('Impresoras y Escaneres', 'image/img_categorias/6840de5d8a0ac_impresorasyescaneres.png');
CALL agregarCategoria('Audio y sonido', 'image/img_categorias/684346f576a3b_Aysond.png');
CALL agregarCategoria('Consolas y Videojuegos', 'image/img_categorias/6843473f22f77_consolas y videojuegos.png');
CALL agregarCategoria('Hogar inteligente', 'image/img_categorias/6843475bd279a_hogarintel.png');
CALL agregarCategoria('Monitores y pantallas', 'image/img_categorias/68434934ed490_pantallas.png');
CALL agregarCategoria('Energia y Carga', 'image/img_categorias/6843495a44984_energiaYcarga.png');

-- =====================================================
-- INSERCIÓN DE PRODUCTOS USANDO PROCEDURES
-- =====================================================

-- COMPUTADORAS
CALL agregarProducto('ASUS 2801', 'N56VJ', 800.00, 10, 'image/img_productos/6832382d0716a_asusN56VJ.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'ASUS' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'COMPUTADORAS' LIMIT 1));

CALL agregarProducto('HP Victus', 'Gaming Laptop', 900.00, 10, 'image/img_productos/68433d8876a43_victus.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'HP' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'COMPUTADORAS' LIMIT 1));

CALL agregarProducto('Huawei Matebook', 'D15-2024', 1500.00, 10, 'image/img_productos/683757079d94f_huawei-matebook.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'HUAWEI' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'COMPUTADORAS' LIMIT 1));

CALL agregarProducto('Laptop BLU Dynamax', 'Future-2025', 1975.00, 10, 'image/img_productos/68375789e263b_pcfuturo.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'DELL' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'COMPUTADORAS' LIMIT 1));

CALL agregarProducto('PC pro + turbo', 'ProVersion', 2000.00, 10, 'image/img_productos/683757de4c171_rrrrr.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'HP' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'COMPUTADORAS' LIMIT 1));

CALL agregarProducto('Lenovo Astrum 5g', 'Astrum-5G', 850.99, 10, 'image/img_productos/683761580a4aa_lenovoPC.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'LENOVO' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'COMPUTADORAS' LIMIT 1));

CALL agregarProducto('HP Pro Desk', 'ProDesk-2024', 700.99, 10, 'image/img_productos/6840d7bb937ae_hp pro desk.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'HP' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'COMPUTADORAS' LIMIT 1));

-- CELULARES
CALL agregarProducto('Motorola G85 5G', 'G85-Pro-Max', 299.99, 10, 'image/img_productos/68432f6a48779_motorola.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'MOTOROLA' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'CELULARES' LIMIT 1));

CALL agregarProducto('Samsung Galaxy A54', 'A54-5G', 379.99, 10, 'image/img_productos/68432fdc65cee_Galaxy-A54-5G.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'SAMSUNG' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'CELULARES' LIMIT 1));

CALL agregarProducto('iPhone 13', 'iPhone-13', 799.00, 10, 'image/img_productos/684334d9ee00c_iphone-13.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'APPLE' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'CELULARES' LIMIT 1));

CALL agregarProducto('Xiaomi Redmi Note 13', 'Note-13-Pro', 349.99, 10, 'image/img_productos/68433549dd16e_xiaomi13pro.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'XIAOMI' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'CELULARES' LIMIT 1));

-- ALMACENAMIENTO
CALL agregarProducto('Seagate Expansion', 'EXT-1TB', 49.99, 10, 'image/img_productos/684337cb37bad_expancion1t.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'SEAGATE' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'ALMACENAMIENTO' LIMIT 1));

CALL agregarProducto('WD My Passport', 'MyPass-2TB', 74.99, 10, 'image/img_productos/684338003b60f_WD.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'WESTERN DIGITAL' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'ALMACENAMIENTO' LIMIT 1));

CALL agregarProducto('SanDisk Extreme SSD', 'Extreme-1TB', 119.99, 10, 'image/img_productos/68433872439b5_sandisk.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'SANDISK' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'ALMACENAMIENTO' LIMIT 1));

CALL agregarProducto('Kingston DataTraveler', 'DT-128GB', 18.50, 10, 'image/img_productos/684338944f067_kingston.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'KINGSTON' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'ALMACENAMIENTO' LIMIT 1));

-- ACCESORIOS DE COMPUTADORA
CALL agregarProducto('Logitech MX Master', 'MX-3S', 99.99, 10, 'image/img_productos/68433b990cfec_logitic.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'LOGITECH' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'ACCESORIOS DE COMPUTADORA' LIMIT 1));

CALL agregarProducto('Redragon Kumara', 'K552', 42.00, 10, 'image/img_productos/68433c1339cb0_redragon teclao.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'REDRAGON' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'ACCESORIOS DE COMPUTADORA' LIMIT 1));

CALL agregarProducto('Microsoft Surface Arc', 'Arc-Mouse', 79.99, 10, 'image/img_productos/68433c4be7736_microsoft maus.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'MICROSOFT' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'ACCESORIOS DE COMPUTADORA' LIMIT 1));

CALL agregarProducto('TP-Link Archer T3U', 'T3U-AC1300', 22.99, 10, 'image/img_productos/68433c76295c0_tplink.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'TP-LINK' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'ACCESORIOS DE COMPUTADORA' LIMIT 1));

-- IMPRESORAS Y ESCÁNERES
CALL agregarProducto('Epson EcoTank', 'L3210', 189.99, 10, 'image/img_productos/68433ff5a86b5_epson.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'EPSON' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'IMPRESORAS Y ESCANERES' LIMIT 1));

CALL agregarProducto('HP DeskJet', '2775', 74.99, 10, 'image/img_productos/68434019315f3_HP desk.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'HP' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'IMPRESORAS Y ESCANERES' LIMIT 1));

CALL agregarProducto('Brother HL-L2370DW', 'HL-L2370DW', 149.99, 10, 'image/img_productos/68434039b103a_brother.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'BROTHER' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'IMPRESORAS Y ESCANERES' LIMIT 1));

CALL agregarProducto('Canon CanoScan', 'LiDE-300', 79.99, 10, 'image/img_productos/684340ad251a7_Canon.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'CANON' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'IMPRESORAS Y ESCANERES' LIMIT 1));

-- AUDIO Y SONIDO
CALL agregarProducto('Sony WH-1000XM5', 'WH-1000XM5', 349.99, 10, 'image/img_productos/6849d8710e653_sony-wh-1000xm5.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'SONY' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'AUDIO Y SONIDO' LIMIT 1));

CALL agregarProducto('JBL Flip 6', 'Flip-6', 129.00, 10, 'image/img_productos/6849d8e9636b9_jbl.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'JBL' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'AUDIO Y SONIDO' LIMIT 1));

CALL agregarProducto('Apple AirPods Pro', 'AirPods-Pro-2', 249.00, 10, 'image/img_productos/6849d9529e2a3_airbuds.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'APPLE' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'AUDIO Y SONIDO' LIMIT 1));

CALL agregarProducto('HyperX Cloud II', 'Cloud-II', 99.99, 10, 'image/img_productos/6849d975efa91_HyperX.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'HYPERX' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'AUDIO Y SONIDO' LIMIT 1));

-- CONSOLAS Y VIDEOJUEGOS
CALL agregarProducto('Elden Ring GameKey', 'Elden-Ring', 25.99, 10, 'image/img_productos/6849dca73ba73_Elden.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'ACTIVISION' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'CONSOLAS Y VIDEOJUEGOS' LIMIT 1));

CALL agregarProducto('PlayStation 5', 'PS5-Standard', 499.99, 10, 'image/img_productos/6849dd37e2795_ps5.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'PLAYSTATION' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'CONSOLAS Y VIDEOJUEGOS' LIMIT 1));

CALL agregarProducto('Xbox Series S', 'Series-S', 299.99, 10, 'image/img_productos/6849dd7b6e914_xbox.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'XBOX' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'CONSOLAS Y VIDEOJUEGOS' LIMIT 1));

CALL agregarProducto('Nintendo Switch OLED', 'Switch-OLED', 349.99, 10, 'image/img_productos/6849de551bcef_nintendo.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'NINTENDO' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'CONSOLAS Y VIDEOJUEGOS' LIMIT 1));

CALL agregarProducto('Controlador DualSense', 'DualSense', 69.99, 10, 'image/img_productos/6849dec828295_dualsense.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'PLAYSTATION' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'CONSOLAS Y VIDEOJUEGOS' LIMIT 1));

CALL agregarProducto('Call of Duty MW3', 'MW3-2023', 79.99, 10, 'image/img_productos/6849df5087db1_codmw3.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'ACTIVISION' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'CONSOLAS Y VIDEOJUEGOS' LIMIT 1));

-- HOGAR INTELIGENTE
CALL agregarProducto('Amazon Echo Dot', 'Echo-Dot-5', 49.99, 10, 'image/img_productos/684c6c3cd339a_amazonwea.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'AMAZON' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'HOGAR INTELIGENTE' LIMIT 1));

CALL agregarProducto('TP-Link Tapo C200', 'Tapo-C200', 34.99, 10, 'image/img_productos/684c6c6188fe5_camara.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'TAPO' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'HOGAR INTELIGENTE' LIMIT 1));

CALL agregarProducto('Google Nest Hub', 'Nest-Hub-2', 99.00, 10, 'image/img_productos/684c6c9bb46e3_Google.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'GOOGLE' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'HOGAR INTELIGENTE' LIMIT 1));

CALL agregarProducto('Xiaomi Mi Smart Plug', 'Smart-Plug', 19.99, 10, 'image/img_productos/684c6ce2ef882_xiaomiPlug.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'XIAOMI' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'HOGAR INTELIGENTE' LIMIT 1));

-- ENERGÍA Y CARGA
CALL agregarProducto('Anker PowerCore', 'PowerCore-20K', 49.99, 10, 'image/img_productos/684c6df36745f_anker.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'ANKER' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'ENERGIA Y CARGA' LIMIT 1));

CALL agregarProducto('Belkin Wireless Charger', 'Wireless-10W', 29.99, 10, 'image/img_productos/684c6e114e1ce_belkin.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'BELKIN' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'ENERGIA Y CARGA' LIMIT 1));

CALL agregarProducto('Samsung USB-C Charger', 'USB-C-25W', 19.99, 10, 'image/img_productos/684c6e54db93a_samsungCargador.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'SAMSUNG' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'ENERGIA Y CARGA' LIMIT 1));

CALL agregarProducto('Regleta inteligente', 'Kasa-Smart', 39.99, 10, 'image/img_productos/684c6e7848bd6_reglaKasa.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'TP-LINK' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'ENERGIA Y CARGA' LIMIT 1));

-- MONITORES Y PANTALLAS
CALL agregarProducto('LG UltraGear', '27GN800-B', 299.99, 10, 'image/img_productos/684c6f2ca740c_LGultragear.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'LG' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'MONITORES Y PANTALLAS' LIMIT 1));

CALL agregarProducto('Samsung Smart Monitor', 'M8-32-4K', 689.99, 10, 'image/img_productos/684c6f4ec8c4b_samsungM8.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'SAMSUNG' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'MONITORES Y PANTALLAS' LIMIT 1));

CALL agregarProducto('BenQ GW2480', 'GW2480', 129.99, 10, 'image/img_productos/684c6f904611e_BenQ.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'BENQ' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'MONITORES Y PANTALLAS' LIMIT 1));

CALL agregarProducto('AOC C24G1A', 'C24G1A', 169.99, 10, 'image/img_productos/684c6facbd53b_AOCPantalla.png',
    (SELECT idMarca FROM MARCA WHERE nombMarca = 'AOC' LIMIT 1),
    (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'MONITORES Y PANTALLAS' LIMIT 1));

-- =====================================================
-- INSERCIÓN DE RELACIONES MARCA-CATEGORÍA (SIN PROCEDURE)
-- =====================================================

-- Marcas que manejan Computadoras
INSERT INTO MARC_CATEG (idMarca, idCategoria) VALUES
((SELECT idMarca FROM MARCA WHERE nombMarca = 'ASUS' LIMIT 1), 
 (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'COMPUTADORAS' LIMIT 1)),
((SELECT idMarca FROM MARCA WHERE nombMarca = 'HP' LIMIT 1), 
 (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'COMPUTADORAS' LIMIT 1)),
((SELECT idMarca FROM MARCA WHERE nombMarca = 'LENOVO' LIMIT 1), 
 (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'COMPUTADORAS' LIMIT 1)),
((SELECT idMarca FROM MARCA WHERE nombMarca = 'DELL' LIMIT 1), 
 (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'COMPUTADORAS' LIMIT 1));

-- Marcas que manejan Celulares
INSERT INTO MARC_CATEG (idMarca, idCategoria) VALUES
((SELECT idMarca FROM MARCA WHERE nombMarca = 'SAMSUNG' LIMIT 1), 
 (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'CELULARES' LIMIT 1)),
((SELECT idMarca FROM MARCA WHERE nombMarca = 'APPLE' LIMIT 1), 
 (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'CELULARES' LIMIT 1)),
((SELECT idMarca FROM MARCA WHERE nombMarca = 'XIAOMI' LIMIT 1), 
 (SELECT idCategoria FROM CATEGORIA WHERE nombCategoria = 'CELULARES' LIMIT 1));

-- =====================================================
-- SCRIPT COMPLETADO
-- =====================================================

-- Mostrar estadísticas de inserción
SELECT 'RESUMEN DE INSERCIÓN:' as Info;
SELECT COUNT(*) as 'Total Marcas' FROM MARCA;
SELECT COUNT(*) as 'Total Categorías' FROM CATEGORIA;
SELECT COUNT(*) as 'Total Productos' FROM PRODUCTO;
SELECT COUNT(*) as 'Total Usuarios' FROM USUARIO;
SELECT COUNT(*) as 'Relaciones Marca-Categoría' FROM MARC_CATEG;

SELECT 'Inserciones completadas usando procedimientos almacenados!' as Status;
