<?php
// ============================================
// CRUD CATEGORIA: ELIMINAR CATEGORIA
// ============================================
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

try {
    include '../../CONEXION/conexion.php';
    
    // Obtener ID de la categoría a eliminar
    $idCategoria = $_GET['id'] ?? $_POST['id'] ?? null;
    
    if (!$idCategoria) {
        throw new Exception('ID de categoría requerido');
    }
    
    // Verificar que la categoría existe y obtener datos de la imagen
    $stmt = $conn->prepare("SELECT imagen FROM CATEGORIA WHERE idCategoria = ?");
    $stmt->execute([$idCategoria]);
    $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$categoria) {
        throw new Exception('Categoría no encontrada');
    }
    
    // Verificar si hay productos asociados a esta categoría
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM PRODUCTO WHERE idCategoria = ?");
    $stmt->execute([$idCategoria]);
    $productosAsociados = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($productosAsociados['total'] > 0) {
        throw new Exception('No se puede eliminar la categoría porque tiene productos asociados');
    }
    
    // Llamar al stored procedure para eliminar categoría (mueve a tabla de eliminados)
    $stmt = $conn->prepare("CALL eliminarCategoria(?)");
    $stmt->execute([$idCategoria]);
    
    // Eliminar imagen física si existe
    if ($categoria['imagen'] && file_exists("../../../../" . $categoria['imagen'])) {
        unlink("../../../../" . $categoria['imagen']);
    }
    
    // Verificar si es una petición AJAX
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    // Si es AJAX o no tiene el header referer, devolver JSON
    if ($isAjax || !isset($_SERVER['HTTP_REFERER'])) {
        echo json_encode([
            'success' => true,
            'mensaje' => 'Categoría eliminada exitosamente'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        // Si viene desde el frontend (navegación directa), redirigir
        header("Location: ../../frontend/categorias.php?deleted=1");
        exit;
    }
    
} catch (Exception $e) {
    // Verificar si es una petición AJAX
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    // Si es AJAX o no tiene el header referer, devolver JSON
    if ($isAjax || !isset($_SERVER['HTTP_REFERER'])) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => true,
            'mensaje' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    } else {
        // Si viene desde el frontend (navegación directa), redirigir con error
        header("Location: ../../frontend/categorias.php?error=" . urlencode($e->getMessage()));
        exit;
    }
}
?>