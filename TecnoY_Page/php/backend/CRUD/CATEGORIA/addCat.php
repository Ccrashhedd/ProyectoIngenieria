<?php
// ============================================
// AGREGAR CATEGORÍA - CRUD
// ============================================

header('Content-Type: application/json');

try {
    include '../../CONEXION/conexion.php';
    
    if (!isset($conn) || !($conn instanceof PDO)) {
        throw new Exception('Conexión a base de datos no disponible');
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }
    
    $nombre = trim($_POST['nombre'] ?? '');
    
    if (empty($nombre)) {
        throw new Exception('El nombre de la categoría es requerido');
    }
    
    // Verificar si ya existe una categoría con ese nombre
    $stmt = $conn->prepare("SELECT COUNT(*) FROM CATEGORIA WHERE nombCategoria = ?");
    $stmt->execute([$nombre]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Ya existe una categoría con ese nombre');
    }
    
    $imagen = null;
    
    // Procesar imagen si se subió
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $imgTmp = $_FILES['imagen']['tmp_name'];
        $imgName = uniqid() . "_" . basename($_FILES['imagen']['name']);
        
        // Usar ruta absoluta basada en __DIR__ - subir 4 niveles desde backend/CRUD/CATEGORIA/
        $carpeta = dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . "image" . DIRECTORY_SEPARATOR . "img_categorias" . DIRECTORY_SEPARATOR;
        
        if (!is_dir($carpeta)) {
            if (!mkdir($carpeta, 0777, true)) {
                throw new Exception('No se pudo crear el directorio de imágenes');
            }
        }
        
        $destino = $carpeta . $imgName;
        
        if (move_uploaded_file($imgTmp, $destino)) {
            $imagen = "image/img_categorias/" . $imgName;
        } else {
            throw new Exception('Error al subir la imagen: No se pudo mover el archivo');
        }
    }
    
    // Generar ID único para la categoría
    $idCategoria = uniqid();
    
    // Insertar categoría
    $stmt = $conn->prepare("INSERT INTO CATEGORIA (idCategoria, nombCategoria, imagen) VALUES (?, ?, ?)");
    $stmt->execute([$idCategoria, $nombre, $imagen]);
    
    echo json_encode([
        'success' => true,
        'mensaje' => 'Categoría agregada exitosamente',
        'id' => $idCategoria
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'mensaje' => $e->getMessage()
    ]);
}
?>
