<?php
// ============================================
// CRUD CATEGORIA: AGREGAR CATEGORIA
// ============================================

// Solo mostrar JSON si es una petición AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($isAjax) {
    header('Content-Type: application/json');
}

error_reporting(0);
ini_set('display_errors', 0);

try {
    include '../../CONEXION/conexion.php';
    
    // Verificar que sea POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }
    
    // Obtener datos del formulario
    $nombCategoria = $_POST['nombre'] ?? null;
    
    // Validaciones básicas
    if (!$nombCategoria) {
        throw new Exception('Nombre de categoría es obligatorio');
    }
    
    // Procesar imagen si se subió
    $imagen = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
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
    
    // Llamar al stored procedure para agregar categoría
    $stmt = $conn->prepare("CALL agregarCategoria(?, ?)");
    $stmt->execute([
        $nombCategoria,
        $imagen
    ]);
    
    // Si es una petición normal del formulario, redirigir
    if (!$isAjax) {
        header("Location: ../../frontend/categorias.php?success=1");
        exit;
    }
    
    // Si es AJAX, devolver JSON
    echo json_encode([
        'success' => true,
        'mensaje' => 'Categoría agregada exitosamente'
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Si es una petición normal del formulario, redirigir con error
    if (!$isAjax) {
        header("Location: ../../frontend/categorias.php?error=" . urlencode($e->getMessage()));
        exit;
    }
    
    // Si es AJAX, devolver JSON
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => true,
        'mensaje' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
