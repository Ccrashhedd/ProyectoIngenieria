<?php
session_start();
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
    
    // Validar datos requeridos
    $camposRequeridos = ['idUsuario', 'nombUsuario', 'passUsuario', 'emailUsuario'];
    foreach ($camposRequeridos as $campo) {
        if (!isset($input[$campo]) || empty(trim($input[$campo]))) {
            throw new Exception("El campo $campo es requerido");
        }
    }
    
    $idUsuario = trim($input['idUsuario']);
    $nombUsuario = trim($input['nombUsuario']);
    $passUsuario = trim($input['passUsuario']);
    $emailUsuario = trim($input['emailUsuario']);
    
    // Validaciones adicionales
    if (strlen($idUsuario) > 20) {
        throw new Exception('El ID de usuario no puede exceder 20 caracteres');
    }
    
    if (strlen($nombUsuario) > 50) {
        throw new Exception('El nombre no puede exceder 50 caracteres');
    }
    
    if (strlen($emailUsuario) > 50) {
        throw new Exception('El email no puede exceder 50 caracteres');
    }
    
    if (strlen($passUsuario) < 6) {
        throw new Exception('La contraseña debe tener al menos 6 caracteres');
    }
    
    // Validar formato de email
    if (!filter_var($emailUsuario, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('El formato del email no es válido');
    }
    
    // Validar que el ID de usuario solo contenga caracteres alfanuméricos
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $idUsuario)) {
        throw new Exception('El ID de usuario solo puede contener letras, números y guiones bajos');
    }
    
    // Verificar que no exista el usuario
    $stmt = $conn->prepare("SELECT idUsuario FROM USUARIO WHERE idUsuario = ? OR emailUsuario = ?");
    $stmt->execute([$idUsuario, $emailUsuario]);
    
    if ($stmt->fetch()) {
        throw new Exception('El ID de usuario o email ya está registrado');
    }
    
    // Insertar nuevo usuario (contraseña en texto plano para compatibilidad)
    $stmt = $conn->prepare("
        INSERT INTO USUARIO (idUsuario, nombUsuario, passUsuario, emailUsuario, idRango) 
        VALUES (?, ?, ?, ?, 0)
    ");
    
    $resultado = $stmt->execute([$idUsuario, $nombUsuario, $passUsuario, $emailUsuario]);
    
    if (!$resultado) {
        throw new Exception('Error al crear el usuario');
    }
    
    // Crear carrito para el nuevo usuario
    try {
        $idCarrito = 'CART_' . $idUsuario;
        $stmtCarrito = $conn->prepare("INSERT INTO CARRITO (idCarrito, idUsuario) VALUES (?, ?)");
        $stmtCarrito->execute([$idCarrito, $idUsuario]);
    } catch (Exception $e) {
        // Error al crear carrito no debe bloquear el registro
        error_log("Error creando carrito para usuario $idUsuario: " . $e->getMessage());
    }
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Usuario registrado exitosamente',
        'data' => [
            'idUsuario' => $idUsuario,
            'nombUsuario' => $nombUsuario,
            'emailUsuario' => $emailUsuario
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
