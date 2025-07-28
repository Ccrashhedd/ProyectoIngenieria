<?php
// ============================================
// CONFIGURACIÓN AUTOMÁTICA DE BASE DE DATOS
// ============================================

include 'CONEXION/conexion.php';

echo "<h1>🔧 Configuración Automática de Base de Datos</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .result { background: white; padding: 15px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .success { border-left: 4px solid #4CAF50; }
    .error { border-left: 4px solid #f44336; }
    .info { border-left: 4px solid #2196F3; }
    pre { background: #f9f9f9; padding: 10px; border-radius: 4px; overflow-x: auto; }
    .status { font-weight: bold; }
</style>";

try {
    echo "<div class='result info'>";
    echo "<h3>🗄️ Creando Estructura de Base de Datos</h3>";
    
    // Crear tablas en orden correcto
    $sqlStatements = [
        // Eliminar tablas existentes
        "DROP TABLE IF EXISTS PRODUCTO",
        "DROP TABLE IF EXISTS MARC_CATEG", 
        "DROP TABLE IF EXISTS USUARIO",
        "DROP TABLE IF EXISTS CATEGORIA",
        "DROP TABLE IF EXISTS MARCA",
        "DROP TABLE IF EXISTS RANGO",
        
        // Crear RANGO
        "CREATE TABLE RANGO (
            idRango TINYINT PRIMARY KEY,
            nombRango VARCHAR(25) NOT NULL
        )",
        
        // Crear MARCA
        "CREATE TABLE MARCA (
            idMarca VARCHAR(10) PRIMARY KEY,
            nombMarca VARCHAR(25) NOT NULL
        )",
        
        // Crear CATEGORIA
        "CREATE TABLE CATEGORIA (
            idCategoria VARCHAR(10) PRIMARY KEY,
            nombCategoria VARCHAR(25) NOT NULL,
            imagen VARCHAR(255) DEFAULT NULL
        )",
        
        // Crear USUARIO
        "CREATE TABLE USUARIO (
            idUsuario VARCHAR(20) PRIMARY KEY,
            nombUsuario VARCHAR(50) NOT NULL,
            passUsuario VARCHAR(255) NOT NULL,
            emailUsuario VARCHAR(50) NOT NULL,
            idRango TINYINT NOT NULL DEFAULT 0,
            FOREIGN KEY (idRango) REFERENCES RANGO(idRango)
        )",
        
        // Crear PRODUCTO
        "CREATE TABLE PRODUCTO (
            idProducto VARCHAR(30) PRIMARY KEY,
            nombProducto VARCHAR(50) NOT NULL,
            modelo VARCHAR(50) NOT NULL,
            precio DECIMAL(10,2) NOT NULL,
            stock INT NOT NULL,
            imagen VARCHAR(255) DEFAULT NULL,
            idMarca VARCHAR(10) NOT NULL,
            idCategoria VARCHAR(10) NOT NULL,
            FOREIGN KEY (idMarca) REFERENCES MARCA(idMarca),
            FOREIGN KEY (idCategoria) REFERENCES CATEGORIA(idCategoria)
        )"
    ];
    
    foreach ($sqlStatements as $i => $sql) {
        try {
            $conn->exec($sql);
            if (strpos($sql, 'CREATE TABLE') !== false) {
                preg_match('/CREATE TABLE (\w+)/', $sql, $matches);
                echo "✅ Tabla {$matches[1]} creada<br>";
            }
        } catch (Exception $e) {
            echo "⚠️ Error en statement: " . $e->getMessage() . "<br>";
        }
    }
    echo "</div>";
    
    echo "<div class='result info'>";
    echo "<h3>📊 Insertando Datos de Ejemplo</h3>";
    
    // Insertar datos
    
    // Rangos
    $conn->exec("INSERT INTO RANGO (idRango, nombRango) VALUES (0, 'Usuario'), (1, 'Administrador')");
    echo "✅ Rangos insertados<br>";
    
    // Marcas
    $marcas = [
        ['MRC001', 'ASUS'],
        ['MRC002', 'Samsung'], 
        ['MRC003', 'Apple'],
        ['MRC004', 'Xiaomi'],
        ['MRC005', 'HP'],
        ['MRC006', 'Lenovo'],
        ['MRC007', 'Sony'],
        ['MRC008', 'LG']
    ];
    
    foreach ($marcas as $marca) {
        $stmt = $conn->prepare("INSERT INTO MARCA (idMarca, nombMarca) VALUES (?, ?)");
        $stmt->execute($marca);
    }
    echo "✅ " . count($marcas) . " marcas insertadas<br>";
    
    // Categorías
    $categorias = [
        ['CAT001', 'Laptops y PCs', 'image/img_categorias/68433cf633e18_laptopsypc.png'],
        ['CAT002', 'Celulares', 'image/img_categorias/68433ce95d214_celulares.png'],
        ['CAT003', 'Pantallas', 'image/img_categorias/68434934ed490_pantallas.png'],
        ['CAT004', 'Almacenamiento', 'image/img_categorias/6840dbbaee885_almacenamiento.png'],
        ['CAT005', 'Consolas', 'image/img_categorias/6843473f22f77_consolas y videojuegos.png']
    ];
    
    foreach ($categorias as $cat) {
        $stmt = $conn->prepare("INSERT INTO CATEGORIA (idCategoria, nombCategoria, imagen) VALUES (?, ?, ?)");
        $stmt->execute($cat);
    }
    echo "✅ " . count($categorias) . " categorías insertadas<br>";
    
    // Productos
    $productos = [
        ['PROD001', 'Laptop ASUS N56VJ', 'Intel Core i7 8GB RAM', 1299.99, 15, 'image/img_productos/6832382d0716a_asusN56VJ.png', 'MRC001', 'CAT001'],
        ['PROD002', 'Huawei MateBook', 'AMD Ryzen 5 16GB RAM', 899.99, 10, 'image/img_productos/683757079d94f_huawei-matebook.png', 'MRC004', 'CAT001'],
        ['PROD003', 'PC Gamer Futuro', 'Intel i9 32GB RTX 4090', 2999.99, 5, 'image/img_productos/68375789e263b_pcfuturo.png', 'MRC005', 'CAT001'],
        ['PROD004', 'Samsung Galaxy A54', '5G 128GB Negro', 399.99, 25, 'image/img_productos/68432fdc65cee_Galaxy-A54-5G.png', 'MRC002', 'CAT002'],
        ['PROD005', 'iPhone 13', '128GB Azul Sierra', 999.99, 8, 'image/img_productos/684334d9ee00c_iphone-13.png', 'MRC003', 'CAT002'],
        ['PROD006', 'Xiaomi 13 Pro', '256GB Negro', 799.99, 12, 'image/img_productos/68433549dd16e_xiaomi13pro.png', 'MRC004', 'CAT002'],
        ['PROD007', 'Monitor LG UltraGear', '27 4K Gaming 144Hz', 599.99, 12, 'image/img_productos/684c6f2ca740c_LGultragear.png', 'MRC008', 'CAT003'],
        ['PROD008', 'Samsung Monitor M8', '32 4K Smart Monitor', 799.99, 8, 'image/img_productos/684c6f4ec8c4b_samsungM8.png', 'MRC002', 'CAT003'],
        ['PROD009', 'SSD Kingston 1TB', 'NVMe PCIe 4.0', 129.99, 30, 'image/img_productos/684338944f067_kingston.png', 'MRC006', 'CAT004'],
        ['PROD010', 'HDD WD Blue 2TB', 'SATA 7200RPM', 89.99, 20, 'image/img_productos/68433872439b5_WD.png', 'MRC008', 'CAT004'],
        ['PROD011', 'PlayStation 5', 'Console Standard Edition', 499.99, 5, 'image/img_productos/6849dd37e2795_ps5.png', 'MRC007', 'CAT005'],
        ['PROD012', 'Xbox Series X', 'Console 1TB SSD', 499.99, 6, 'image/img_productos/6849dd7b6e914_xbox.png', 'MRC002', 'CAT005']
    ];
    
    foreach ($productos as $prod) {
        $stmt = $conn->prepare("INSERT INTO PRODUCTO (idProducto, nombProducto, modelo, precio, stock, imagen, idMarca, idCategoria) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute($prod);
    }
    echo "✅ " . count($productos) . " productos insertados<br>";
    
    // Usuario admin
    $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO USUARIO (idUsuario, nombUsuario, passUsuario, emailUsuario, idRango) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['admin', 'Administrador', $adminPass, 'admin@tecnoy.com', 1]);
    echo "✅ Usuario administrador creado (admin / admin123)<br>";
    
    echo "</div>";
    
    echo "<div class='result success'>";
    echo "<h3>🎉 Configuración Completada</h3>";
    echo "<p><strong>Base de datos configurada exitosamente!</strong></p>";
    echo "<p>📊 Resumen:</p>";
    echo "<ul>";
    echo "<li>📁 " . count($categorias) . " categorías</li>";
    echo "<li>🏷️ " . count($marcas) . " marcas</li>";
    echo "<li>📦 " . count($productos) . " productos</li>";
    echo "<li>👤 1 usuario administrador</li>";
    echo "</ul>";
    echo "<br>";
    echo "<a href='LoadProd.php' style='background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>✅ Probar API</a>";
    echo "<a href='../frontend/landingPage.php' style='background: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>🚀 Ver Frontend</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='result error'>";
    echo "<h3>❌ Error en Configuración</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "</div>";
}
?>
