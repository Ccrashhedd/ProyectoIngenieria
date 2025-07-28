<?php
// ============================================
// CONSULTA: BÚSQUEDA Y FILTROS DE PRODUCTOS
// ============================================

include '../CONEXION/conexion.php';

// Configurar cabecera JSON
header('Content-Type: application/json');

try {
    // Obtener parámetros de búsqueda
    $nombre = isset($_GET['nombre']) ? trim($_GET['nombre']) : '';
    $categoria_id = isset($_GET['categoria_id']) ? intval($_GET['categoria_id']) : 0;
    $marca_id = isset($_GET['marca_id']) ? intval($_GET['marca_id']) : 0;
    $precio_min = isset($_GET['precio_min']) ? floatval($_GET['precio_min']) : 0;
    $precio_max = isset($_GET['precio_max']) ? floatval($_GET['precio_max']) : 999999;
    
    // Construir consulta con filtros
    $sql = "
        SELECT 
            c.idCategoria,
            c.nombCategoria,
            c.imagen as categoria_imagen,
            p.idProducto,
            p.nombProducto,
            p.modelo,
            p.precio,
            p.stock,
            p.imagen,
            m.nombMarca
        FROM CATEGORIA c
        INNER JOIN PRODUCTO p ON c.idCategoria = p.idCategoria
        INNER JOIN MARCA m ON p.idMarca = m.idMarca
        WHERE 1=1
    ";
    
    $params = [];
    
    if (!empty($nombre)) {
        $sql .= " AND (p.nombProducto LIKE :nombre OR p.modelo LIKE :nombre OR m.nombMarca LIKE :nombre)";
        $params[':nombre'] = '%' . $nombre . '%';
    }
    
    if ($categoria_id > 0) {
        $sql .= " AND c.idCategoria = :categoria_id";
        $params[':categoria_id'] = $categoria_id;
    }
    
    if ($marca_id > 0) {
        $sql .= " AND m.idMarca = :marca_id";
        $params[':marca_id'] = $marca_id;
    }
    
    $sql .= " AND p.precio BETWEEN :precio_min AND :precio_max";
    $params[':precio_min'] = $precio_min;
    $params[':precio_max'] = $precio_max;
    
    $sql .= " ORDER BY c.nombCategoria ASC, p.nombProducto ASC";
    
    $stmt = $conn->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    
    // Organizar resultados por categoría
    $resultado = [];
    $categorias = [];
    
    while ($row = $stmt->fetch()) {
        $categoria_id = $row['idCategoria'];
        
        if (!isset($categorias[$categoria_id])) {
            $categorias[$categoria_id] = [
                'id' => $categoria_id,
                'nombre' => $row['nombCategoria'],
                'imagen' => $row['categoria_imagen'],
                'productos' => []
            ];
        }
        
        $categorias[$categoria_id]['productos'][] = [
            'id' => $row['idProducto'],
            'nombre' => $row['nombProducto'],
            'descripcion' => $row['modelo'],
            'precio' => floatval($row['precio']),
            'imagen' => $row['imagen'],
            'stock' => intval($row['stock']),
            'marca' => $row['nombMarca']
        ];
    }
    
    // Convertir a array indexado
    $resultado = array_values($categorias);
    
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'mensaje' => 'Error en la búsqueda: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
