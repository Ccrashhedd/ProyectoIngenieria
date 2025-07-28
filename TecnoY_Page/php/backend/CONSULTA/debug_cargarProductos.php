<?php
// ============================================
// DEBUG: CARGAR PRODUCTOS POR CATEGORÍAS
// ============================================

// Habilitar errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔧 Debugging cargarProductosCategorias.php<br><br>";

// Test 1: Verificar inclusión de conexión
echo "📡 Test 1: Incluyendo conexión...<br>";
try {
    include '../CONEXION/conexion.php';
    echo "✅ Conexión incluida correctamente<br><br>";
} catch (Exception $e) {
    echo "❌ Error al incluir conexión: " . $e->getMessage() . "<br><br>";
    exit();
}

// Test 2: Verificar conexión PDO
echo "🔌 Test 2: Verificando conexión PDO...<br>";
try {
    if ($conn instanceof PDO) {
        echo "✅ Variable \$conn es una instancia de PDO<br>";
        
        $test = $conn->query("SELECT 1 as test");
        $result = $test->fetch();
        echo "✅ Consulta de prueba exitosa: " . $result['test'] . "<br><br>";
    } else {
        echo "❌ Variable \$conn no es PDO<br><br>";
        exit();
    }
} catch (Exception $e) {
    echo "❌ Error en conexión PDO: " . $e->getMessage() . "<br><br>";
    exit();
}

// Test 3: Verificar existencia de tablas
echo "🗄️ Test 3: Verificando tablas...<br>";
$tablasNecesarias = ['CATEGORIA', 'PRODUCTO', 'MARCA'];
foreach ($tablasNecesarias as $tabla) {
    try {
        $count = $conn->query("SELECT COUNT(*) as count FROM $tabla")->fetch()['count'];
        echo "✅ Tabla $tabla: $count registros<br>";
    } catch (Exception $e) {
        echo "❌ Error en tabla $tabla: " . $e->getMessage() . "<br>";
    }
}
echo "<br>";

// Test 4: Ejecutar consulta de categorías
echo "📋 Test 4: Ejecutando consulta de categorías...<br>";
try {
    $sqlCategorias = "
        SELECT 
            idCategoria,
            nombCategoria,
            imagen
        FROM CATEGORIA 
        ORDER BY nombCategoria ASC
    ";
    
    echo "SQL: " . htmlspecialchars($sqlCategorias) . "<br>";
    
    $stmtCategorias = $conn->prepare($sqlCategorias);
    $stmtCategorias->execute();
    
    echo "✅ Consulta de categorías preparada y ejecutada<br>";
    
    $categorias = $stmtCategorias->fetchAll();
    echo "✅ Categorías obtenidas: " . count($categorias) . "<br><br>";
    
    foreach ($categorias as $i => $cat) {
        echo "Categoría $i: ID={$cat['idCategoria']}, Nombre={$cat['nombCategoria']}<br>";
        if ($i >= 2) break; // Solo mostrar las primeras 3
    }
    
} catch (Exception $e) {
    echo "❌ Error en consulta de categorías: " . $e->getMessage() . "<br><br>";
    exit();
}

// Test 5: Ejecutar consulta de productos para una categoría
echo "<br>📦 Test 5: Ejecutando consulta de productos...<br>";
try {
    if (!empty($categorias)) {
        $categoriaTest = $categorias[0];
        
        $sqlProductos = "
            SELECT 
                p.idProducto,
                p.nombProducto,
                p.modelo,
                p.precio,
                p.stock,
                p.imagen,
                m.nombMarca
            FROM PRODUCTO p
            INNER JOIN MARCA m ON p.idMarca = m.idMarca
            WHERE p.idCategoria = :idCategoria
            ORDER BY p.nombProducto ASC
        ";
        
        echo "SQL: " . htmlspecialchars($sqlProductos) . "<br>";
        echo "Parámetro: idCategoria = {$categoriaTest['idCategoria']}<br>";
        
        $stmtProductos = $conn->prepare($sqlProductos);
        $stmtProductos->bindParam(':idCategoria', $categoriaTest['idCategoria']);
        $stmtProductos->execute();
        
        $productos = $stmtProductos->fetchAll();
        echo "✅ Productos obtenidos para '{$categoriaTest['nombCategoria']}': " . count($productos) . "<br>";
        
        foreach ($productos as $i => $prod) {
            echo "Producto $i: ID={$prod['idProducto']}, Nombre={$prod['nombProducto']}, Precio={$prod['precio']}<br>";
            if ($i >= 1) break; // Solo mostrar los primeros 2
        }
        
    } else {
        echo "⚠️ No hay categorías para probar productos<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error en consulta de productos: " . $e->getMessage() . "<br>";
}

// Test 6: Generar JSON de prueba
echo "<br>📄 Test 6: Generando JSON de prueba...<br>";
try {
    $resultado = [];
    
    foreach ($categorias as $categoria) {
        $sqlProductos = "
            SELECT 
                p.idProducto,
                p.nombProducto,
                p.modelo,
                p.precio,
                p.stock,
                p.imagen,
                m.nombMarca
            FROM PRODUCTO p
            INNER JOIN MARCA m ON p.idMarca = m.idMarca
            WHERE p.idCategoria = :idCategoria
            ORDER BY p.nombProducto ASC
        ";
        
        $stmtProductos = $conn->prepare($sqlProductos);
        $stmtProductos->bindParam(':idCategoria', $categoria['idCategoria']);
        $stmtProductos->execute();
        
        $productos = [];
        while ($producto = $stmtProductos->fetch()) {
            $productos[] = [
                'id' => $producto['idProducto'],
                'nombre' => $producto['nombProducto'],
                'descripcion' => $producto['modelo'],
                'precio' => floatval($producto['precio']),
                'imagen' => $producto['imagen'],
                'stock' => intval($producto['stock']),
                'marca' => $producto['nombMarca']
            ];
        }
        
        if (!empty($productos)) {
            $resultado[] = [
                'id' => $categoria['idCategoria'],
                'nombre' => $categoria['nombCategoria'],
                'imagen' => $categoria['imagen'],
                'productos' => $productos
            ];
        }
    }
    
    echo "✅ Resultado generado con " . count($resultado) . " categorías<br>";
    
    $json = json_encode($resultado, JSON_UNESCAPED_UNICODE);
    
    if ($json !== false) {
        echo "✅ JSON generado correctamente (" . strlen($json) . " caracteres)<br>";
        echo "<details><summary>Ver JSON (primeros 500 caracteres)</summary>";
        echo "<pre>" . htmlspecialchars(substr($json, 0, 500)) . "...</pre>";
        echo "</details>";
        
        echo "<br><strong>🎉 TODO FUNCIONA CORRECTAMENTE</strong><br>";
        echo "<a href='cargarProductosCategorias.php' style='background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Probar API Real</a>";
        
    } else {
        echo "❌ Error al generar JSON: " . json_last_error_msg();
    }
    
} catch (Exception $e) {
    echo "❌ Error al generar resultado: " . $e->getMessage();
}
?>
