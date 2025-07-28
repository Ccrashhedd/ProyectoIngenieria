<?php
// ============================================
// DEBUG: PROBLEMA CON MARCAS
// ============================================

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Debug del Error de Marcas</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .test { background: white; padding: 15px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .success { border-left: 4px solid #4CAF50; }
    .error { border-left: 4px solid #f44336; }
    .info { border-left: 4px solid #2196F3; }
    pre { background: #f9f9f9; padding: 10px; border-radius: 4px; overflow-x: auto; max-height: 300px; }
    h3 { color: #333; margin-top: 0; }
    .status { font-weight: bold; }
</style>";

echo "<div class='test info'>";
echo "<h3>📡 Test 1: Verificar Conexión</h3>";
try {
    include '../CONEXION/conexion.php';
    echo "✅ Conexión incluida correctamente<br>";
    
    if ($conn instanceof PDO) {
        echo "✅ Variable \$conn es PDO<br>";
    } else {
        echo "❌ Variable \$conn no es PDO<br>";
        exit();
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    exit();
}
echo "</div>";

echo "<div class='test info'>";
echo "<h3>🗄️ Test 2: Verificar Tabla MARCA</h3>";
try {
    $result = $conn->query("DESCRIBE MARCA");
    echo "✅ Tabla MARCA existe<br>";
    echo "Estructura:<br>";
    while ($row = $result->fetch()) {
        echo "- {$row['Field']} ({$row['Type']})<br>";
    }
    
    $count = $conn->query("SELECT COUNT(*) as count FROM MARCA")->fetch()['count'];
    echo "<br>✅ Total de marcas: $count<br>";
    
} catch (Exception $e) {
    echo "❌ Error en tabla MARCA: " . $e->getMessage() . "<br>";
}
echo "</div>";

echo "<div class='test info'>";
echo "<h3>📋 Test 3: Consulta Simple de Marcas</h3>";
try {
    $sql = "SELECT idMarca, nombMarca FROM MARCA ORDER BY nombMarca ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    echo "✅ Consulta ejecutada correctamente<br>";
    echo "Marcas encontradas:<br>";
    
    while ($marca = $stmt->fetch()) {
        echo "- ID: {$marca['idMarca']}, Nombre: {$marca['nombMarca']}<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error en consulta: " . $e->getMessage() . "<br>";
}
echo "</div>";

echo "<div class='test info'>";
echo "<h3>📄 Test 4: Generar JSON de Marcas</h3>";
try {
    $sql = "SELECT idMarca as id, nombMarca as nombre FROM MARCA ORDER BY nombMarca ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $marcas = $stmt->fetchAll();
    
    echo "✅ Datos obtenidos: " . count($marcas) . " marcas<br>";
    
    $json = json_encode($marcas, JSON_UNESCAPED_UNICODE);
    
    if ($json !== false) {
        echo "✅ JSON generado correctamente<br>";
        echo "Longitud: " . strlen($json) . " caracteres<br>";
        echo "JSON válido: " . (json_decode($json) !== null ? 'Sí' : 'No') . "<br>";
        
        echo "<details><summary>Ver JSON Completo</summary>";
        echo "<pre>" . htmlspecialchars($json) . "</pre>";
        echo "</details>";
        
        // Verificar caracteres problemáticos
        if (preg_match('/[^\x20-\x7E\x{00A0}-\x{FFFF}]/u', $json)) {
            echo "⚠️ Se encontraron caracteres no estándar en el JSON<br>";
        } else {
            echo "✅ JSON contiene solo caracteres válidos<br>";
        }
        
    } else {
        echo "❌ Error al generar JSON: " . json_last_error_msg() . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
echo "</div>";

echo "<div class='test info'>";
echo "<h3>🔍 Test 5: Simular Respuesta API</h3>";
try {
    // Limpiar cualquier output previo
    ob_start();
    
    header('Content-Type: application/json');
    
    $sql = "SELECT idMarca as id, nombMarca as nombre FROM MARCA ORDER BY nombMarca ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $marcas = $stmt->fetchAll();
    
    $response = json_encode($marcas, JSON_UNESCAPED_UNICODE);
    
    // Obtener el contenido del buffer
    $output = ob_get_contents();
    ob_end_clean();
    
    if (!empty($output)) {
        echo "⚠️ Se detectó output adicional antes del JSON:<br>";
        echo "<pre>" . htmlspecialchars($output) . "</pre>";
        echo "Esto podría estar causando el error de parsing JSON<br>";
    } else {
        echo "✅ No hay output adicional antes del JSON<br>";
    }
    
    echo "Respuesta de la API:<br>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
echo "</div>";

echo "<div class='test success'>";
echo "<h3>🎯 Conclusión</h3>";
echo "<p>Si todos los tests pasaron correctamente, el problema podría estar en:</p>";
echo "<ul>";
echo "<li>Caracteres adicionales en la respuesta de la API</li>";
echo "<li>Warnings o errores PHP que se muestran antes del JSON</li>";
echo "<li>Codificación de caracteres</li>";
echo "<li>Espacios en blanco al final del archivo PHP</li>";
echo "</ul>";
echo "<br>";
echo "<a href='../SELECTS/obtenerMarcas.php' style='background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>Probar API Real</a>";
echo "</div>";
?>
