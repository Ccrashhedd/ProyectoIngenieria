<?php

require_once '../CONEXION/conexion.php';
/**
 * ============================================
 * SISTEMA DE LOGIN
 * Archivo: login.php
 * Ubicación: /php/backend/USUARIO/
 * ============================================
 */

// Configuración inicial
session_start();

// Headers de respuesta
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Configuración de errores (activar para depuración)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '../../../logs/login_debug.log');

// Usar la conexión del archivo de conexión
$pdo = $conn;

try {
    // ============================================
    // 1. VALIDAR MÉTODO DE PETICIÓN
    // ============================================
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido. Solo se acepta POST.');
    }

    // ============================================
    // 3. OBTENER Y VALIDAR DATOS
    // ============================================
    $usuario_input = trim($_POST['userIn'] ?? '');
    $password_input = $_POST['password'] ?? '';

    // Log de depuración
    error_log("Login attempt - Usuario: $usuario_input");

    // Validaciones
    if (empty($usuario_input)) {
        throw new Exception('El usuario es requerido');
    }
    
    if (empty($password_input)) {
        throw new Exception('La contraseña es requerida');
    }

    // ============================================
    // 4. BUSCAR USUARIO EN BD
    // ============================================
    $sql = "SELECT 
                idUsuario,
                nombUsuario,
                passUsuario,
                emailUsuario,
                idRango
            FROM USUARIO 
            WHERE idUsuario = ? OR emailUsuario = ? OR nombUsuario = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_input, $usuario_input, $usuario_input]);
    $usuario = $stmt->fetch();

    // ============================================
    // 5. VERIFICAR CREDENCIALES
    // ============================================
    if (!$usuario) {
        throw new Exception('Usuario no encontrado');
    }

    if ($usuario['passUsuario'] !== $password_input) {
        throw new Exception('Contraseña incorrecta');
    }

    // ============================================
    // 6. CREAR SESIÓN
    // ============================================
    $_SESSION['usuario'] = $usuario['idUsuario'];
    $_SESSION['nombre_usuario'] = $usuario['nombUsuario'];
    $_SESSION['email'] = $usuario['emailUsuario'];
    $_SESSION['id_rango'] = $usuario['idRango'];
    $_SESSION['rol'] = ($usuario['idRango'] == 1) ? 'admin' : 'usuario';
    $_SESSION['logueado'] = true;

    // ============================================
    // 7. DETERMINAR REDIRECCIÓN
    // ============================================
    $base_url = '/ProyectoIngenieria/ProyectoIngenieria/TecnoY_Page/php/frontend/';
    
    if ($usuario['idRango'] == 1) {
        // Administrador
        $redirect_url = $base_url . 'pag_adm.php';
    } else {
        // Usuario normal
        $redirect_url = $base_url . 'landingPage.php';
    }

    // ============================================
    // 8. RESPUESTA EXITOSA
    // ============================================
    echo json_encode([
        'success' => true,
        'mensaje' => 'Inicio de sesión exitoso',
        'redirect' => $redirect_url,
        'usuario' => [
            'id' => $usuario['idUsuario'],
            'nombre' => $usuario['nombUsuario'],
            'email' => $usuario['emailUsuario'],
            'rol' => ($usuario['idRango'] == 1) ? 'admin' : 'usuario'
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    // Error de base de datos
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'mensaje' => 'Error de conexión a la base de datos'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // Error de validación
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'mensaje' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>