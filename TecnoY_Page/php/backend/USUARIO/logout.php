<?php
/**
 * ============================================
 * SISTEMA DE LOGOUT
 * Archivo: logout.php
 * Ubicación: /php/backend/USUARIO/
 * ============================================
 */

// Configuración inicial
session_start();

// Headers de respuesta
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Verificar si hay una sesión activa
    if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
        throw new Exception('No hay una sesión activa para cerrar');
    }

    // Guardar información antes de cerrar (para logging)
    $usuario_id = $_SESSION['usuario'] ?? 'desconocido';
    $usuario_nombre = $_SESSION['nombre_usuario'] ?? 'desconocido';

    // Destruir todas las variables de sesión
    $_SESSION = array();

    // Destruir la cookie de sesión si existe
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Destruir la sesión
    session_destroy();

    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'mensaje' => 'Sesión cerrada exitosamente',
        'redirect' => '/ProyectoIngenieria/TecnoY_Page/php/frontend/landingPage.php',
        'usuario_cerrado' => $usuario_nombre
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // Error en logout
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'mensaje' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
