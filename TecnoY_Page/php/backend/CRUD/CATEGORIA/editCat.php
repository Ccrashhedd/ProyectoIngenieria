<?php
// ============================================
// CRUD CATEGORIA: EDITAR CATEGORIA
// ============================================
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

try {
    include '../../CONEXION/conexion.php';
    
    // Verificar que sea POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }
    
    // Obtener datos del formulario
    $idCategoria = $_POST['id'] ?? null;
    $nombCategoria = $_POST['nombre'] ?? null;
    
    // Validaciones básicas
    if (!$idCategoria || !$nombCategoria) {
        throw new Exception('ID y nombre de categoría son obligatorios');
    }
    
    // Obtener datos actuales de la categoría para conservar la imagen si no se cambia
    $stmt = $conn->prepare("SELECT imagen FROM CATEGORIA WHERE idCategoria = ?");
    $stmt->execute([$idCategoria]);
    $categoriaActual = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$categoriaActual) {
        throw new Exception('Categoría no encontrada');
    }
    
    $imagen = $categoriaActual['imagen']; // Mantener imagen actual por defecto
    
    // Procesar nueva imagen si se subió
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        // Eliminar imagen anterior si existe
        if ($imagen && file_exists("../../../../" . $imagen)) {
            unlink("../../../../" . $imagen);
        }
        
        $imgTmp = $_FILES['imagen']['tmp_name'];
        $imgName = uniqid() . "_" . basename($_FILES['imagen']['name']);
        $carpeta = "../../../../image/img_categorias/";
        
        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0777, true);
        }
        
        $destino = $carpeta . $imgName;
        if (move_uploaded_file($imgTmp, $destino)) {
            $imagen = "image/img_categorias/" . $imgName;
        }
    }
    
    // Llamar al stored procedure para actualizar categoría
    $stmt = $conn->prepare("CALL actualizarCategoria(?, ?, ?)");
    $stmt->execute([
        $idCategoria,       // oldIdCategoria
        $nombCategoria,     // newNombCategoria
        $imagen            // newImagen
    ]);
    
    echo json_encode([
        'success' => true,
        'mensaje' => 'Categoría actualizada exitosamente'
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