<?php
// ============================================
// ARCHIVO DE COMPATIBILIDAD - LoadProd.php
// Carga productos por categorías
// ============================================

// Configurar cabecera JSON primero
header('Content-Type: application/json');

// Habilitar manejo de errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    // Incluir conexión desde la ruta correcta
    include 'CONEXION/conexion.php';
    
    if (!isset($conn) || !($conn instanceof PDO)) {
        throw new Exception('Conexión a base de datos no disponible');
    }
    
    // Obtener todas las categorías
    $sqlCategorias = "
        SELECT 
            idCategoria,
            nombCategoria,
            imagen
        FROM CATEGORIA 
        ORDER BY nombCategoria ASC
    ";
    
    $stmtCategorias = $conn->prepare($sqlCategorias);
    $stmtCategorias->execute();
    $resultado = [];
    
    while ($categoria = $stmtCategorias->fetch()) {
        // Para cada categoría, obtener sus productos
        $sqlProductos = "
            SELECT 
                p.idProducto,
                p.nombProducto,
                p.modelo,
                p.precio,
                p.stock,
                p.imagen,
                p.idMarca,
                m.nombMarca
            FROM PRODUCTO p
            INNER JOIN MARCA m ON p.idMarca = m.idMarca
            WHERE p.idCategoria = :idCategoria
            ORDER BY p.nombProducto ASC
        ";
        
        $stmtProductos = $conn->prepare($sqlProductos);
        $stmtProductos->bindParam(':idCategoria', $categoria['idCategoria'], PDO::PARAM_STR);
        $stmtProductos->execute();
        
        $productos = [];
        while ($producto = $stmtProductos->fetch()) {
            $productos[] = [
                'id' => $producto['idProducto'],
                'nombre' => $producto['nombProducto'],
                'descripcion' => $producto['modelo'],
                'precio' => floatval($producto['precio']),
                'imagen' => $producto['imagen'],
                'stock' => intval($producto['stock']),
                'marca' => $producto['nombMarca'],
                'marca_id' => $producto['idMarca']
            ];
        }
        
        // Solo agregar categorías que tienen productos
        if (!empty($productos)) {
            $resultado[] = [
                'id' => $categoria['idCategoria'],
                'nombre' => $categoria['nombCategoria'],
                'imagen' => $categoria['imagen'],
                'productos' => $productos
            ];
        }
    }
    
    // Enviar respuesta JSON
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Error en la consulta
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'mensaje' => 'Error al cargar productos: ' . $e->getMessage(),
        'debug_info' => [
            'file' => __FILE__,
            'line' => $e->getLine()
        ]
    ], JSON_UNESCAPED_UNICODE);
}
?>