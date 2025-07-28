<?php
// ============================================
// CRUD PRODUCTO: AGREGAR PRODUCTO
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
    $nombProducto = $_POST['nombre'] ?? null;
    $modelo = $_POST['descripcion'] ?? null;
    $precio = $_POST['precio'] ?? null;
    $stock = $_POST['stock'] ?? 0;
    $idMarca = $_POST['marca_id'] ?? null;
    $idCategoria = $_POST['categoria_id'] ?? null;
    
    // Validaciones básicas
    if (!$nombProducto || !$modelo || !$precio || !$idCategoria) {
        throw new Exception('Faltan datos obligatorios');
    }
    
    // Procesar imagen si se subió
    $imagen = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
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
    
    // Si no se especifica marca, buscar una marca por defecto o crear una genérica
    if (!$idMarca) {
        // Buscar si existe una marca "GENÉRICA" o similar
        $stmt = $conn->prepare("SELECT idMarca FROM MARCA WHERE nombMarca = 'GENÉRICA' LIMIT 1");
        $stmt->execute();
        $marcaGenerica = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$marcaGenerica) {
            // Crear marca genérica usando el procedure
            $stmt = $conn->prepare("CALL agregarMarca(?)");
            $stmt->execute(['GENÉRICA']);
            
            // Obtener el ID de la marca recién creada
            $stmt = $conn->prepare("SELECT idMarca FROM MARCA WHERE nombMarca = 'GENÉRICA' LIMIT 1");
            $stmt->execute();
            $marcaGenerica = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        $idMarca = $marcaGenerica['idMarca'];
    }
    
    // Llamar al stored procedure para agregar producto
    $stmt = $conn->prepare("CALL agregarProducto(?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $nombProducto,
        $modelo, 
        $precio,
        $stock,
        $imagen,
        $idMarca,
        $idCategoria
    ]);
    
    echo json_encode([
        'success' => true,
        'mensaje' => 'Producto agregado exitosamente'
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
