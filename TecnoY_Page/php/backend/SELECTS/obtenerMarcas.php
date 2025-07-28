<?php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

try {
    include '../CONEXION/conexion.php';
    
    $sql = "SELECT idMarca as id, nombMarca as nombre FROM MARCA ORDER BY nombMarca ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $marcas = $stmt->fetchAll();
    
    echo json_encode($marcas, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => true, 'mensaje' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
