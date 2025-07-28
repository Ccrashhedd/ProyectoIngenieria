<?php
// ============================================
// CONSULTA: LISTAR TODAS LAS CATEGORÍAS PARA ADMIN
// ============================================

include '../CONEXION/conexion.php';

try {
    $sql = "
        SELECT 
            idCategoria as id,
            nombCategoria as nombre,
            imagen
        FROM CATEGORIA 
        ORDER BY nombCategoria ASC
    ";
    
    $stmt = executeSelect($conn, $sql);
    $categorias = $stmt->fetchAll();
    
    header('Content-Type: application/json');
    echo json_encode($categorias, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'mensaje' => 'Error al obtener categorías: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
