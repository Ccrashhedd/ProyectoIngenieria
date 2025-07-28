<?php
/**
 * ============================================
 * VERIFICACIÓN DE SESIÓN
 * Archivo: verificar_sesion.php
 * Ubicación: /php/backend/USUARIO/
 * ============================================
 */

// Iniciar sesión
session_start();

// Headers de respuesta
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Desactivar errores para respuesta JSON limpia
error_reporting(0);
ini_set('display_errors', 0);

try {
    // Verificar si hay sesión activa
    $logueado = isset($_SESSION['usuario']) && !empty($_SESSION['usuario']);
    $admin = 0;
    $usuario = null;

    if ($logueado) {
        $admin = (isset($_SESSION['id_rango']) && $_SESSION['id_rango'] == 1) ? 1 : 0;
        $usuario = $_SESSION['usuario'];
    }

    // Respuesta con estado de sesión
    echo json_encode([
        'logueado' => $logueado,
        'admin' => $admin,
        'usuario' => $usuario,
        'nombre_usuario' => $_SESSION['nombre_usuario'] ?? null,
        'email' => $_SESSION['email'] ?? null,
        'rol' => $_SESSION['rol'] ?? null
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // En caso de error, asumir no logueado
    echo json_encode([
        'logueado' => false,
        'admin' => 0,
        'usuario' => null,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
