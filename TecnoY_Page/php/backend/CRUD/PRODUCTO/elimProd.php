<?php
// ============================================
// CRUD PRODUCTO: ELIMINAR PRODUCTO
// ============================================
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

try {
    include '../../CONEXION/conexion.php';
    
    // Obtener ID del producto a eliminar
    $idProducto = $_GET['id'] ?? $_POST['id'] ?? null;
    
    if (!$idProducto) {
        throw new Exception('ID de producto requerido');
    }
    
    // Verificar que el producto existe y obtener datos de la imagen
    $stmt = $conn->prepare("SELECT imagen FROM PRODUCTO WHERE idProducto = ?");
    $stmt->execute([$idProducto]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$producto) {
        throw new Exception('Producto no encontrado');
    }
    
    // Llamar al stored procedure para eliminar producto (mueve a tabla de eliminados)
    $stmt = $conn->prepare("CALL eliminarProducto(?)");
    $stmt->execute([$idProducto]);
    
    // Eliminar imagen física si existe
    if ($producto['imagen'] && file_exists("../../../../" . $producto['imagen'])) {
        unlink("../../../../" . $producto['imagen']);
    }
    
    echo json_encode([
        'success' => true,
        'mensaje' => 'Producto eliminado exitosamente'
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => true,
        'mensaje' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
