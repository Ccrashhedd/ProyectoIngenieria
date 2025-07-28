<?php
// ============================================
// SCRIPT PARA MIGRAR DATOS DE ESTRUCTURA ANTIGUA A NUEVA
// ============================================

include 'CONEXION/conexion.php';

echo "<h1>🔄 Migración de Datos - Estructura Antigua → Nueva</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .test { background: white; padding: 15px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .success { border-left: 4px solid #4CAF50; }
    .error { border-left: 4px solid #f44336; }
    .info { border-left: 4px solid #2196F3; }
    .warning { border-left: 4px solid #FF9800; }
    h3 { color: #333; margin-top: 0; }
    .status { font-weight: bold; }
</style>";

// Verificar si existen las tablas antiguas
echo "<div class='test info'>";
echo "<h3>📋 Paso 1: Verificar Tablas Antiguas</h3>";
$tablasAntiguas = ['categorias', 'marcas', 'productos'];
$datosAntiguos = [];

foreach ($tablasAntiguas as $tabla) {
    try {
        $count = $conn->query("SELECT COUNT(*) as count FROM $tabla")->fetch()['count'];
        echo "Tabla <strong>$tabla</strong>: $count registros<br>";
        $datosAntiguos[$tabla] = $count;
    } catch (Exception $e) {
        echo "Tabla <strong>$tabla</strong>: No existe<br>";
        $datosAntiguos[$tabla] = 0;
    }
}
echo "</div>";

if (isset($_GET['migrar'])) {
    echo "<div class='test warning'>";
    echo "<h3>🔄 Ejecutando Migración</h3>";
    
    try {
        // Migrar Categorías
        if ($datosAntiguos['categorias'] > 0) {
            echo "Migrando categorías...<br>";
            $stmt = $conn->prepare("
                INSERT IGNORE INTO CATEGORIA (idCategoria, nombCategoria, imagen)
                SELECT id, nombre, imagen FROM categorias
            ");
            $stmt->execute();
            echo "✅ Categorías migradas<br>";
        }
        
        // Migrar Marcas
        if ($datosAntiguos['marcas'] > 0) {
            echo "Migrando marcas...<br>";
            $stmt = $conn->prepare("
                INSERT IGNORE INTO MARCA (idMarca, nombMarca)
                SELECT id, nombre FROM marcas
            ");
            $stmt->execute();
            echo "✅ Marcas migradas<br>";
        }
        
        // Migrar Productos
        if ($datosAntiguos['productos'] > 0) {
            echo "Migrando productos...<br>";
            $stmt = $conn->prepare("
                INSERT IGNORE INTO PRODUCTO (idProducto, nombProducto, modelo, precio, stock, imagen, idMarca, idCategoria)
                SELECT id, nombre, descripcion, precio, stock, imagen, marca_id, categoria_id FROM productos
            ");
            $stmt->execute();
            echo "✅ Productos migrados<br>";
        }
        
        echo "<br><span class='status' style='color: green;'>✅ MIGRACIÓN COMPLETADA</span>";
        
    } catch (Exception $e) {
        echo "<span class='status' style='color: red;'>❌ ERROR en migración:</span> " . $e->getMessage();
    }
    echo "</div>";
}

// Si no hay datos antiguos, crear datos de muestra
if (array_sum($datosAntiguos) == 0) {
    echo "<div class='test warning'>";
    echo "<h3>📊 No hay datos antiguos - Crear Datos de Muestra</h3>";
    
    if (isset($_GET['crear_muestra'])) {
        try {
            // Insertar rangos
            $conn->exec("INSERT IGNORE INTO RANGO (idRango, nombRango) VALUES (0, 'Usuario'), (1, 'Administrador')");
            
            // Insertar categorías de muestra
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
            
            // Insertar marcas de muestra
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
            
            // Insertar productos de muestra
            $productos = [
                ['PROD001', 'Laptop ASUS N56VJ', 'Intel Core i7', 1299.99, 15, 'image/img_productos/6832382d0716a_asusN56VJ.png', 'MRC001', 'CAT001'],
                ['PROD002', 'Huawei MateBook', 'AMD Ryzen 5', 899.99, 10, 'image/img_productos/683757079d94f_huawei-matebook.png', 'MRC004', 'CAT001'],
                ['PROD003', 'Samsung Galaxy A54', '5G 128GB', 399.99, 25, 'image/img_productos/68432fdc65cee_Galaxy-A54-5G.png', 'MRC002', 'CAT002'],
                ['PROD004', 'iPhone 13', '128GB', 999.99, 8, 'image/img_productos/684334d9ee00c_iphone-13.png', 'MRC003', 'CAT002'],
                ['PROD005', 'Monitor LG UltraGear', '27 4K Gaming', 599.99, 12, 'image/img_productos/684c6f2ca740c_LGultragear.png', 'MRC008', 'CAT003']
            ];
            
            foreach ($productos as $prod) {
                $stmt = $conn->prepare("INSERT IGNORE INTO PRODUCTO (idProducto, nombProducto, modelo, precio, stock, imagen, idMarca, idCategoria) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute($prod);
            }
            
            // Crear usuario administrador de muestra
            $stmt = $conn->prepare("INSERT IGNORE INTO USUARIO (idUsuario, nombUsuario, passUsuario, emailUsuario, idRango) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute(['admin', 'Administrador', password_hash('admin123', PASSWORD_DEFAULT), 'admin@tecnoy.com', 1]);
            
            echo "<span class='status' style='color: green;'>✅ ÉXITO:</span> Datos de muestra creados correctamente<br>";
            echo "<strong>Usuario admin creado:</strong> admin / admin123";
            
        } catch (Exception $e) {
            echo "<span class='status' style='color: red;'>❌ ERROR:</span> " . $e->getMessage();
        }
    } else {
        echo "<p>No se detectaron datos en las tablas antiguas.</p>";
        echo "<a href='?crear_muestra=1' style='background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Crear Datos de Muestra</a>";
    }
    echo "</div>";
} else {
    echo "<div class='test info'>";
    echo "<h3>🔄 Migrar Datos Existentes</h3>";
    echo "<p>Se encontraron datos en las tablas antiguas. ¿Desea migrarlos a la nueva estructura?</p>";
    echo "<a href='?migrar=1' style='background: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Migrar Datos</a>";
    echo "</div>";
}

// Verificar estado actual
echo "<div class='test success'>";
echo "<h3>📊 Estado Actual de la Base de Datos</h3>";
try {
    $cats = $conn->query("SELECT COUNT(*) as count FROM CATEGORIA")->fetch()['count'];
    $marcas = $conn->query("SELECT COUNT(*) as count FROM MARCA")->fetch()['count'];
    $prods = $conn->query("SELECT COUNT(*) as count FROM PRODUCTO")->fetch()['count'];
    
    echo "📁 Categorías: <strong>$cats</strong><br>";
    echo "🏷️ Marcas: <strong>$marcas</strong><br>";
    echo "📦 Productos: <strong>$prods</strong><br>";
    
    if ($cats > 0 && $marcas > 0 && $prods > 0) {
        echo "<br><span class='status' style='color: green;'>✅ ¡Base de datos lista para usar!</span><br>";
        echo "<a href='verificarMigracion.php' style='background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>Probar APIs</a>";
        echo "<a href='../frontend/landingPage.php' style='background: #FF9800; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>Ver Frontend</a>";
    }
    
} catch (Exception $e) {
    echo "<span class='status' style='color: red;'>❌ ERROR:</span> " . $e->getMessage();
}
echo "</div>";
?>
