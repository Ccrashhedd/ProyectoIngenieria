<?php
// ============================================
// ARCHIVO PARA OBTENER TODAS LAS CATEGORÍAS CON SUS PRODUCTOS
// Para el panel de administración
// ============================================

// Configurar cabecera JSON primero
header('Content-Type: application/json');

// Habilitar CORS si es necesario
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

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
                p.idProducto as id,
                p.nombProducto as nombre,
                p.modelo as descripcion,
                p.precio,
                p.stock,
                p.imagen,
                p.idMarca,
                m.nombMarca as marca
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
                'id' => $producto['id'],
                'nombre' => $producto['nombre'],
                'descripcion' => $producto['descripcion'],
                'precio' => floatval($producto['precio']),
                'imagen' => $producto['imagen'],
                'stock' => intval($producto['stock']),
                'marca' => $producto['marca'],
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
        'mensaje' => 'Error al cargar todas las categorías: ' . $e->getMessage(),
        'debug_info' => [
            'file' => __FILE__,
            'line' => $e->getLine()
        ]
    ], JSON_UNESCAPED_UNICODE);
}
?>
