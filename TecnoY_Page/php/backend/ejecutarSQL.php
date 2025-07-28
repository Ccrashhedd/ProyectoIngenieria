<?php
// ============================================
// EJECUTAR SCRIPT SQL DE CREACIÓN DE TABLAS
// ============================================

include 'CONEXION/conexion.php';

echo "<h1>🗄️ Ejecutor de Script SQL</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .test { background: white; padding: 15px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .success { border-left: 4px solid #4CAF50; }
    .error { border-left: 4px solid #f44336; }
    .info { border-left: 4px solid #2196F3; }
    .warning { border-left: 4px solid #FF9800; }
    pre { background: #f9f9f9; padding: 10px; border-radius: 4px; overflow-x: auto; max-height: 300px; }
    h3 { color: #333; margin-top: 0; }
    .status { font-weight: bold; }
</style>";

if (isset($_GET['ejecutar'])) {
    echo "<div class='test warning'>";
    echo "<h3>⚡ Ejecutando Script SQL</h3>";
    
    try {
        // SQL directo para crear las tablas
        $sqlStatements = [
            // Drops para seguridad
            "DROP TABLE IF EXISTS CARRITO_DETALLE",
            "DROP TABLE IF EXISTS CARRITO",
            "DROP TABLE IF EXISTS P_ELIMINADOS",
            "DROP TABLE IF EXISTS M_ELIMINADOS",
            "DROP TABLE IF EXISTS CAT_ELIMINADOS",
            "DROP TABLE IF EXISTS DETALLE_FACTURA",
            "DROP TABLE IF EXISTS FACTURA",
            "DROP TABLE IF EXISTS PRODUCTO",
            "DROP TABLE IF EXISTS USUARIO",
            "DROP TABLE IF EXISTS MARC_CATEG",
            "DROP TABLE IF EXISTS CATEGORIA",
            "DROP TABLE IF EXISTS MARCA",
            "DROP TABLE IF EXISTS RANGO",
            
            // Creación de tablas
            "CREATE TABLE IF NOT EXISTS MARCA (
                idMarca VARCHAR(10) PRIMARY KEY,
                nombMarca VARCHAR(25) NOT NULL
            )",
            
            "CREATE TABLE IF NOT EXISTS CATEGORIA (
                idCategoria VARCHAR(10) PRIMARY KEY,
                nombCategoria VARCHAR(25) NOT NULL,
                imagen VARCHAR(255) DEFAULT NULL
            )",
            
            "CREATE TABLE IF NOT EXISTS MARC_CATEG(
                idMarcCat INT AUTO_INCREMENT,
                idMarca VARCHAR(10),
                idCategoria VARCHAR(10),
                PRIMARY KEY (idMarcCat),
                FOREIGN KEY (idMarca) REFERENCES MARCA(idMarca),
                FOREIGN KEY (idCategoria) REFERENCES CATEGORIA(idCategoria),
                UNIQUE KEY unique_marca_categoria (idMarca, idCategoria)
            )",
            
            "CREATE TABLE IF NOT EXISTS RANGO (
                idRango TINYINT PRIMARY KEY,
                nombRango VARCHAR(25) NOT NULL,
                CONSTRAINT idRango_CHK CHECK (idRango IN (0, 1))
            )",
            
            "CREATE TABLE IF NOT EXISTS USUARIO(
                idUsuario VARCHAR(20) PRIMARY KEY,
                nombUsuario VARCHAR(50) NOT NULL,
                passUsuario VARCHAR(50) UNIQUE NOT NULL,
                emailUsuario VARCHAR(50) NOT NULL,
                idRango TINYINT NOT NULL DEFAULT 0,
                FOREIGN KEY (idRango) REFERENCES RANGO(idRango)
            )",
            
            "CREATE TABLE IF NOT EXISTS PRODUCTO (
                idProducto VARCHAR(30) PRIMARY KEY,
                nombProducto VARCHAR(25) NOT NULL,
                modelo VARCHAR(25) NOT NULL,
                precio DECIMAL(10,2) NOT NULL,
                stock INT NOT NULL,
                imagen VARCHAR(255) DEFAULT NULL,
                idMarca VARCHAR(10) NOT NULL,
                idCategoria VARCHAR(10) NOT NULL,
                FOREIGN KEY (idMarca) REFERENCES MARCA(idMarca),
                FOREIGN KEY (idCategoria) REFERENCES CATEGORIA(idCategoria)
            )"
        ];
        
        $ejecutados = 0;
        $errores = 0;
        
        foreach ($sqlStatements as $sql) {
            try {
                $conn->exec(trim($sql));
                $ejecutados++;
                
                // Mostrar solo las creaciones importantes
                if (strpos($sql, 'CREATE TABLE') !== false) {
                    $tabla = preg_match('/CREATE TABLE.*?(\w+)\s*\(/', $sql, $matches);
                    if ($tabla) {
                        echo "✅ Tabla {$matches[1]} creada<br>";
                    }
                }
                
            } catch (Exception $e) {
                $errores++;
                echo "⚠️ Error en statement: " . $e->getMessage() . "<br>";
            }
        }
        
        echo "<br><strong>📊 Resumen:</strong><br>";
        echo "Statements ejecutados: $ejecutados<br>";
        echo "Errores: $errores<br>";
        
        if ($errores == 0) {
            echo "<br><span class='status' style='color: green;'>✅ ÉXITO: Todas las tablas creadas correctamente</span>";
        }
        
    } catch (Exception $e) {
        echo "<span class='status' style='color: red;'>❌ ERROR GENERAL:</span> " . $e->getMessage();
    }
    echo "</div>";
    
    // Insertar datos de muestra
    echo "<div class='test info'>";
    echo "<h3>📊 Insertando Datos de Muestra</h3>";
    
    try {
        // Rangos
        $conn->exec("INSERT IGNORE INTO RANGO (idRango, nombRango) VALUES (0, 'Usuario'), (1, 'Administrador')");
        
        // Categorías
        $categorias = [
            ['CAT001', 'Laptops y PCs', 'image/img_categorias/68433cf633e18_laptopsypc.png'],
            ['CAT002', 'Celulares', 'image/img_categorias/68433ce95d214_celulares.png'],
            ['CAT003', 'Pantallas', 'image/img_categorias/68434934ed490_pantallas.png'],
            ['CAT004', 'Almacenamiento', 'image/img_categorias/6840dbbaee885_almacenamiento.png'],
            ['CAT005', 'Consolas y Videojuegos', 'image/img_categorias/6843473f22f77_consolas y videojuegos.png']
        ];
        
        foreach ($categorias as $cat) {
            $stmt = $conn->prepare("INSERT IGNORE INTO CATEGORIA (idCategoria, nombCategoria, imagen) VALUES (?, ?, ?)");
            $stmt->execute($cat);
        }
        echo "✅ Categorías insertadas<br>";
        
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
            $stmt = $conn->prepare("INSERT IGNORE INTO MARCA (idMarca, nombMarca) VALUES (?, ?)");
            $stmt->execute($marca);
        }
        echo "✅ Marcas insertadas<br>";
        
        // Productos
        $productos = [
            ['PROD001', 'Laptop ASUS N56VJ', 'Intel Core i7', 1299.99, 15, 'image/img_productos/6832382d0716a_asusN56VJ.png', 'MRC001', 'CAT001'],
            ['PROD002', 'Huawei MateBook', 'AMD Ryzen 5', 899.99, 10, 'image/img_productos/683757079d94f_huawei-matebook.png', 'MRC004', 'CAT001'],
            ['PROD003', 'Galaxy A54 5G', '128GB Negro', 399.99, 25, 'image/img_productos/68432fdc65cee_Galaxy-A54-5G.png', 'MRC002', 'CAT002'],
            ['PROD004', 'iPhone 13', '128GB Azul', 999.99, 8, 'image/img_productos/684334d9ee00c_iphone-13.png', 'MRC003', 'CAT002'],
            ['PROD005', 'Monitor LG UltraGear', '27 4K Gaming', 599.99, 12, 'image/img_productos/684c6f2ca740c_LGultragear.png', 'MRC008', 'CAT003'],
            ['PROD006', 'SSD Kingston', '1TB NVMe', 129.99, 30, 'image/img_productos/684338944f067_kingston.png', 'MRC001', 'CAT004'],
            ['PROD007', 'PlayStation 5', 'Console Standard', 499.99, 5, 'image/img_productos/6849dd37e2795_ps5.png', 'MRC007', 'CAT005']
        ];
        
        foreach ($productos as $prod) {
            $stmt = $conn->prepare("INSERT IGNORE INTO PRODUCTO (idProducto, nombProducto, modelo, precio, stock, imagen, idMarca, idCategoria) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute($prod);
        }
        echo "✅ Productos insertados<br>";
        
        // Usuario admin
        $stmt = $conn->prepare("INSERT IGNORE INTO USUARIO (idUsuario, nombUsuario, passUsuario, emailUsuario, idRango) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['admin', 'Administrador', password_hash('admin123', PASSWORD_DEFAULT), 'admin@tecnoy.com', 1]);
        echo "✅ Usuario administrador creado<br>";
        
        echo "<br><span class='status' style='color: green;'>✅ Datos de muestra insertados correctamente</span>";
        
    } catch (Exception $e) {
        echo "<span class='status' style='color: red;'>❌ ERROR al insertar datos:</span> " . $e->getMessage();
    }
    echo "</div>";
}

// Mostrar estado actual
echo "<div class='test success'>";
echo "<h3>📊 Estado Actual</h3>";
try {
    $tablas = ['CATEGORIA', 'MARCA', 'PRODUCTO', 'USUARIO', 'RANGO'];
    $totalRegistros = 0;
    
    foreach ($tablas as $tabla) {
        try {
            $count = $conn->query("SELECT COUNT(*) as count FROM $tabla")->fetch()['count'];
            echo "📁 $tabla: <strong>$count</strong> registros<br>";
            $totalRegistros += $count;
        } catch (Exception $e) {
            echo "❌ $tabla: No existe<br>";
        }
    }
    
    if ($totalRegistros > 0) {
        echo "<br><span class='status' style='color: green;'>✅ Base de datos configurada</span><br>";
        echo "<a href='CONSULTA/debug_cargarProductos.php' style='background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>Debug API</a>";
        echo "<a href='../frontend/landingPage.php' style='background: #FF9800; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>Ver Frontend</a>";
    } else {
        echo "<br><a href='?ejecutar=1' style='background: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ejecutar Script SQL</a>";
    }
    
} catch (Exception $e) {
    echo "<span class='status' style='color: red;'>❌ ERROR:</span> " . $e->getMessage() . "<br>";
    echo "<a href='?ejecutar=1' style='background: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ejecutar Script SQL</a>";
}
echo "</div>";
?>
