<?php
// ============================================
// CONSULTA: OBTENER CATEGORÍA POR ID
// ============================================

include '../CONEXION/conexion.php';

try {
    $categoriaId = $_GET['id'] ?? null;
    
    if (!$categoriaId) {
        throw new Exception('ID de categoría requerido');
    }
    
    $sql = "
        SELECT 
            idCategoria as id,
            nombCategoria as nombre,
            imagen
        FROM CATEGORIA 
        WHERE idCategoria = :categoriaId
    ";
    
    $stmt = prepareQuery($conn, $sql);
    $stmt->bindParam(':categoriaId', $categoriaId);
    $stmt->execute();
    
    $categoria = $stmt->fetch();
    
    if (!$categoria) {
        throw new Exception('Categoría no encontrada');
    }
    
    header('Content-Type: application/json');
    echo json_encode($categoria, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode([
        'error' => true,
        'mensaje' => 'Error al obtener categoría: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
