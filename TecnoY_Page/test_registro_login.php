<?php
// Script para probar el registro de usuario
require_once 'php/backend/CONEXION/conexion.php';

echo "<h1>🧪 Prueba de Registro y Login</h1>";

// Datos de prueba
$testUser = [
    'idUsuario' => 'test_user_' . date('His'),
    'nombUsuario' => 'Usuario de Prueba',
    'emailUsuario' => 'test' . date('His') . '@test.com',
    'passUsuario' => 'test123'
];

echo "<h2>📝 Registrando usuario de prueba...</h2>";
echo "<ul>";
echo "<li><strong>ID:</strong> {$testUser['idUsuario']}</li>";
echo "<li><strong>Nombre:</strong> {$testUser['nombUsuario']}</li>";
echo "<li><strong>Email:</strong> {$testUser['emailUsuario']}</li>";
echo "<li><strong>Contraseña:</strong> {$testUser['passUsuario']}</li>";
echo "</ul>";

try {
    // Simular el registro (SIN hash - texto plano)
    $stmt = $conn->prepare("
        INSERT INTO USUARIO (idUsuario, nombUsuario, passUsuario, emailUsuario, idRango) 
        VALUES (?, ?, ?, ?, 0)
    ");
    
    $resultado = $stmt->execute([
        $testUser['idUsuario'], 
        $testUser['nombUsuario'], 
        $testUser['passUsuario'], // Contraseña en texto plano
        $testUser['emailUsuario']
    ]);
    
    if ($resultado) {
        echo "<div style='color: green; padding: 10px; border: 1px solid green; margin: 10px 0;'>";
        echo "✅ <strong>Usuario registrado exitosamente!</strong><br>";
        echo "Contraseña almacenada: {$testUser['passUsuario']} (texto plano)<br>";
        echo "</div>";
        
        // Ahora probar la verificación de contraseña
        echo "<h2>🔐 Probando verificación de contraseña...</h2>";
        
        // Simular login
        $stmt2 = $conn->prepare("SELECT passUsuario FROM USUARIO WHERE idUsuario = ?");
        $stmt2->execute([$testUser['idUsuario']]);
        $user_data = $stmt2->fetch();
        
        if ($user_data) {
            $stored_password = $user_data['passUsuario'];
            
            echo "<p><strong>Contraseña almacenada:</strong> {$stored_password}</p>";
            
            // Verificar con comparación directa
            $verification_result = ($testUser['passUsuario'] === $stored_password);
            
            if ($verification_result) {
                echo "<div style='color: green; padding: 10px; border: 1px solid green; margin: 10px 0;'>";
                echo "✅ <strong>Verificación de contraseña EXITOSA!</strong><br>";
                echo "Comparación directa: TRUE<br>";
                echo "</div>";
            } else {
                echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px 0;'>";
                echo "❌ <strong>Error en verificación de contraseña!</strong><br>";
                echo "Comparación directa: FALSE<br>";
                echo "</div>";
            }
        }
        
    } else {
        echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px 0;'>";
        echo "❌ <strong>Error al registrar usuario</strong>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px 0;'>";
    echo "❌ <strong>Error:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<h2>🔗 Enlaces de prueba:</h2>";
echo "<ul>";
echo "<li><a href='php/frontend/nuevoUsuario.php'>Registro Manual</a></li>";
echo "<li><a href='php/frontend/pagLogin.php'>Login</a></li>";
echo "</ul>";

echo "<h2>📋 Credenciales para probar login manual:</h2>";
echo "<div style='background: #f0f0f0; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>Usuario:</strong> {$testUser['idUsuario']}<br>";
echo "<strong>Contraseña:</strong> {$testUser['passUsuario']}";
echo "</div>";

echo "<p><em>Nota: Este usuario se creó con contraseña en texto plano y debería funcionar perfectamente con el login.</em></p>";
?>
