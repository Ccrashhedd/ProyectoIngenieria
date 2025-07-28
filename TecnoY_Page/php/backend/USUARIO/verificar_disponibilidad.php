<?php
header('Content-Type: application/json; charset=utf-8');

try {
    // Incluir conexión a la base de datos
    include '../CONEXION/conexion.php';
    
    // Verificar que la solicitud sea POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }
    
    // Obtener datos del formulario
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    
    // Validar que se envió el ID de usuario
    if (!isset($input['idUsuario']) || empty(trim($input['idUsuario']))) {
        throw new Exception('ID de usuario requerido');
    }
    
    $idUsuario = trim($input['idUsuario']);
    
    // Verificar si existe el usuario
    $stmt = $conn->prepare("SELECT idUsuario FROM USUARIO WHERE idUsuario = ?");
    $stmt->execute([$idUsuario]);
    
    $existe = $stmt->fetch() !== false;
    
    // Respuesta
    echo json_encode([
        'success' => true,
        'disponible' => !$existe,
        'message' => $existe ? 'ID de usuario ya está en uso' : 'ID de usuario disponible'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'disponible' => false,
        'message' => $e->getMessage()
    ]);
}
?>
