<?php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

try {
    include '../CONEXION/conexion.php';
    
    $categoriaId = $_GET['categoria_id'] ?? null;
    
    if (!$categoriaId) {
        echo json_encode([], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $sql = "
        SELECT DISTINCT 
            m.idMarca as id,
            m.nombMarca as nombre
        FROM MARCA m
        INNER JOIN PRODUCTO p ON m.idMarca = p.idMarca
        WHERE p.idCategoria = :categoria_id
        ORDER BY m.nombMarca ASC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':categoria_id', $categoriaId);
    $stmt->execute();
    $marcas = $stmt->fetchAll();
    
    echo json_encode($marcas, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => true, 'mensaje' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
