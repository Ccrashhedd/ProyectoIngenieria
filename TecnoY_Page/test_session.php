<?php
session_start();

echo "<h3>Session Test</h3>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Session Data:</strong></p>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

echo "<h3>POST Data:</h3>";
echo "<pre>" . print_r($_POST, true) . "</pre>";

echo "<h3>Database Connection Test:</h3>";
try {
    require_once 'php/backend/CONEXION/conexion.php';
    echo "<p style='color: green;'>✅ Database connection successful</p>";
    
    if (isset($_SESSION['usuario'])) {
        echo "<p style='color: green;'>✅ User logged in: " . htmlspecialchars($_SESSION['usuario']) . "</p>";
        
        // Test cart query
        require_once 'php/backend/CRUD/CARRITO/verificarCarrito.php';
        $carritoData = obtenerCarritoUsuario($_SESSION['usuario']);
        echo "<p><strong>Cart Data:</strong></p>";
        echo "<pre>" . print_r($carritoData, true) . "</pre>";
    } else {
        echo "<p style='color: red;'>❌ No user session found</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
