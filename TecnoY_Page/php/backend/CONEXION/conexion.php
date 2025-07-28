<?php
// ============================================
// ARCHIVO DE CONEXIÓN ACTUALIZADO
// Migrado a nueva estructura de base de datos
// ============================================

// Configuración de base de datos
$dbname = 'proyectoIngenieria';  // Base de datos migrada
$host = 'localhost';
$user = 'root';
$password = ''; // Contraseña por defecto en XAMPP

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

// Establecer conexión con PDO
try {
    $conn = new PDO($dsn, $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Retornar error en formato JSON para APIs
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'mensaje' => 'Error de conexión a la base de datos'
    ]);
    exit();
}

?>