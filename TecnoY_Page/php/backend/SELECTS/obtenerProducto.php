<?php
// ============================================
// OBTENER DATOS DE UN PRODUCTO ESPECÍFICO
// ============================================
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

try {
    include '../CONEXION/conexion.php';
    
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        throw new Exception('ID de producto requerido');
    }
    
    $stmt = $conn->prepare("
        SELECT 
            p.idProducto as id,
            p.nombProducto as nombre,
            p.modelo as descripcion,
            p.precio,
            p.stock,
            p.imagen,
            p.idCategoria as categoria_id,
            p.idMarca as marca_id,
            c.nombCategoria as categoria,
            m.nombMarca as marca
        FROM PRODUCTO p
        LEFT JOIN CATEGORIA c ON p.idCategoria = c.idCategoria
        LEFT JOIN MARCA m ON p.idMarca = m.idMarca
        WHERE p.idProducto = ?
    ");
    
    $stmt->execute([$id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$producto) {
        throw new Exception('Producto no encontrado');
    }
    
    echo json_encode($producto, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'mensaje' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
