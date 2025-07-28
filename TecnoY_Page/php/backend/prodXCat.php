<?php
// ============================================
// ARCHIVO PARA OBTENER PRODUCTOS POR CATEGORÍA
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
    
    // Verificar que se haya enviado el parámetro categoria_id
    if (!isset($_GET['categoria_id']) || empty($_GET['categoria_id'])) {
        throw new Exception('ID de categoría requerido');
    }
    
    $categoriaId = $_GET['categoria_id'];
    
    // Obtener productos de la categoría específica
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
    $stmtProductos->bindParam(':idCategoria', $categoriaId, PDO::PARAM_STR);
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
    
    // Enviar respuesta JSON
    echo json_encode($productos, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Error en la consulta
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'mensaje' => 'Error al cargar productos: ' . $e->getMessage(),
        'debug_info' => [
            'file' => __FILE__,
            'line' => $e->getLine(),
            'categoria_id' => $_GET['categoria_id'] ?? 'no proporcionado'
        ]
    ], JSON_UNESCAPED_UNICODE);
}
?>
