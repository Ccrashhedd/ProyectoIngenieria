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
    
    // Si viene desde el frontend (GET), redirigir
    if (isset($_GET['id'])) {
        header("Location: ../../frontend/categorias.php?deleted=1");
        exit;
    }
    
    // Si es AJAX (POST), devolver JSON
    echo json_encode([
        'success' => true,
        'mensaje' => 'Categoría eliminada exitosamente'
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Si viene desde el frontend (GET), redirigir con error
    if (isset($_GET['id'])) {
        header("Location: ../../frontend/categorias.php?error=" . urlencode($e->getMessage()));
        exit;
    }
    
    // Si es AJAX (POST), devolver JSON
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => true,
        'mensaje' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>