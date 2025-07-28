<?php
// ============================================
// CRUD PRODUCTO: EDITAR PRODUCTO
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
    $idProducto = $_POST['id'] ?? null;
    $nombProducto = $_POST['nombre'] ?? null;
    $modelo = $_POST['descripcion'] ?? null;
    $precio = $_POST['precio'] ?? null;
    $stock = $_POST['stock'] ?? 0;
    $idMarca = $_POST['marca_id'] ?? null;
    $idCategoria = $_POST['categoria_id'] ?? null;
    
    // Validaciones básicas
    if (!$idProducto || !$nombProducto || !$modelo || !$precio || !$idCategoria) {
        throw new Exception('Faltan datos obligatorios');
    }
    
    // Obtener datos actuales del producto para conservar la imagen si no se cambia
    $stmt = $conn->prepare("SELECT imagen, idMarca FROM PRODUCTO WHERE idProducto = ?");
    $stmt->execute([$idProducto]);
    $productoActual = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$productoActual) {
        throw new Exception('Producto no encontrado');
    }
    
    $imagen = $productoActual['imagen']; // Mantener imagen actual por defecto
    
    // Procesar nueva imagen si se subió
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        // Eliminar imagen anterior si existe
        if ($imagen && file_exists("../../../../" . $imagen)) {
            unlink("../../../../" . $imagen);
        }
        
        $imgTmp = $_FILES['imagen']['tmp_name'];
        $imgName = uniqid() . "_" . basename($_FILES['imagen']['name']);
        $carpeta = "../../../../image/img_productos/";
        
        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0777, true);
        }
        
        $destino = $carpeta . $imgName;
        if (move_uploaded_file($imgTmp, $destino)) {
            $imagen = "image/img_productos/" . $imgName;
        }
    }
    
    // Si no se especifica marca, usar la marca actual
    if (!$idMarca) {
        $idMarca = $productoActual['idMarca'];
    }
    
    // Llamar al stored procedure para actualizar producto
    $stmt = $conn->prepare("CALL actualizarProducto(?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $idProducto,        // oldIdProducto
        $nombProducto,      // newNombProducto
        $modelo,            // newModelo
        $precio,            // newPrecio
        $stock,             // newStock
        $imagen,            // newImagen
        $idMarca,           // newIdMarca
        $idCategoria        // newIdCategoria
    ]);
    
    echo json_encode([
        'success' => true,
        'mensaje' => 'Producto actualizado exitosamente'
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
