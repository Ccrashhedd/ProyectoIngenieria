<?php
// ============================================
// CONSULTA: LISTAR TODOS LOS PRODUCTOS PARA ADMIN
// ============================================

// Configurar cabecera JSON primero
header('Content-Type: application/json');

try {
    include '../CONEXION/conexion.php';
    
    if (!isset($conn) || !($conn instanceof PDO)) {
        throw new Exception('Conexión a base de datos no disponible');
    }
    
    $sql = "
        SELECT 
            p.idProducto as id,
            p.nombProducto as nombre,
            p.modelo,
            p.precio,
            p.stock,
            p.imagen,
            p.idCategoria as categoria_id,
            c.nombCategoria as categoria,
            m.nombMarca as marca
        FROM PRODUCTO p
        INNER JOIN CATEGORIA c ON p.idCategoria = c.idCategoria
        INNER JOIN MARCA m ON p.idMarca = m.idMarca
        ORDER BY p.nombProducto ASC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $productos = $stmt->fetchAll();
    
    echo json_encode($productos, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'mensaje' => 'Error al obtener productos: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
