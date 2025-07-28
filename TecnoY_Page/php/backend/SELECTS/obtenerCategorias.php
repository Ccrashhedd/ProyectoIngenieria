<?php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

try {
    include '../CONEXION/conexion.php';
    
    $sql = "SELECT idCategoria as id, nombCategoria as nombre, imagen FROM CATEGORIA ORDER BY nombCategoria ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $categorias = $stmt->fetchAll();
    
    echo json_encode($categorias, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => true, 'mensaje' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
