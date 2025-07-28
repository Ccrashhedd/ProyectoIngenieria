<?php
// ============================================
// CONSULTA: OBTENER PRODUCTO POR ID
// ============================================

include '../CONEXION/conexion.php';

try {
    $productoId = $_GET['id'] ?? null;
    
    if (!$productoId) {
        throw new Exception('ID de producto requerido');
    }
    
    $sql = "
        SELECT 
            p.idProducto as id,
            p.nombProducto as nombre,
            p.modelo as descripcion,
            p.precio,
            p.stock,
            p.imagen,
            p.idCategoria as categoria_id,
            p.idMarca as marca_id,
            c.nombCategoria as categoria_nombre,
            m.nombMarca as marca_nombre
        FROM PRODUCTO p
        INNER JOIN CATEGORIA c ON p.idCategoria = c.idCategoria
        INNER JOIN MARCA m ON p.idMarca = m.idMarca
        WHERE p.idProducto = :productoId
    ";
    
    $stmt = prepareQuery($conn, $sql);
    $stmt->bindParam(':productoId', $productoId);
    $stmt->execute();
    
    $producto = $stmt->fetch();
    
    if (!$producto) {
        throw new Exception('Producto no encontrado');
    }
    
    header('Content-Type: application/json');
    echo json_encode($producto, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode([
        'error' => true,
        'mensaje' => 'Error al obtener producto: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
