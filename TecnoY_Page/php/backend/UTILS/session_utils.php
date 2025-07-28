<?php
/**
 * ============================================
 * UTILIDADES DE VALIDACIÓN DE SESIÓN
 * Archivo: session_utils.php
 * Ubicación: /php/backend/UTILS/
 * ============================================
 */

/**
 * Verificar si el usuario actual es administrador
 * @return bool True si es admin, False en caso contrario
 */
function esUsuarioAdmin() {
    // Verificar que hay usuario logueado
    if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
        return false;
    }
    
    // Verificar admin con id_rango (método principal recomendado)
    if (isset($_SESSION['id_rango']) && $_SESSION['id_rango'] == 1) {
        return true;
    }
    
    // Verificar admin con variable admin (método de respaldo para compatibilidad)
    if (isset($_SESSION['admin']) && $_SESSION['admin'] == 1) {
        return true;
    }
    
    return false;
}

/**
 * Verificar si hay una sesión activa de usuario
 * @return bool True si hay sesión, False en caso contrario
 */
function hayUsuarioLogueado() {
    return isset($_SESSION['usuario']) && !empty($_SESSION['usuario']);
}

/**
 * Obtener el nombre del usuario actual
 * @return string|null Nombre del usuario o null si no hay sesión
 */
function obtenerUsuarioActual() {
    return $_SESSION['usuario'] ?? null;
}

/**
 * Obtener el tipo de usuario (admin o usuario normal)
 * @return string 'admin', 'usuario' o 'invitado'
 */
function obtenerTipoUsuario() {
    if (!hayUsuarioLogueado()) {
        return 'invitado';
    }
    
    return esUsuarioAdmin() ? 'admin' : 'usuario';
}

/**
 * Forzar redirección si no es admin
 * @param string $paginaRedirect Página a la que redirigir (por defecto: landingPage.php)
 */
function requiereAdmin($paginaRedirect = 'landingPage.php') {
    if (!esUsuarioAdmin()) {
        // Si hay parámetro debug, mostrar información
        if (isset($_GET['debug'])) {
            echo "<h1>🔐 Debug de Acceso</h1>";
            echo "<p><strong>❌ Acceso denegado.</strong> Esta página requiere permisos de administrador.</p>";
            echo "<h3>📊 Información de Sesión:</h3>";
            echo "<pre>" . print_r($_SESSION, true) . "</pre>";
            echo "<h3>✅ Validación Esperada:</h3>";
            echo "<ul>";
            echo "<li><code>\$_SESSION['id_rango'] = 1</code> (método principal)</li>";
            echo "<li><code>\$_SESSION['admin'] = 1</code> (método de respaldo)</li>";
            echo "</ul>";
            echo "<div style='margin-top: 20px;'>";
            echo "<a href='$paginaRedirect' style='padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px;'>🏠 Volver al Inicio</a>";
            echo " <a href='../../../test_session_admin.php' style='padding: 10px 20px; background: #e67e22; color: white; text-decoration: none; border-radius: 5px;'>🔧 Herramienta de Debug</a>";
            echo "</div>";
            echo "<style>body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }</style>";
            exit();
        }
        
        // Redirección normal
        header('Location: ' . $paginaRedirect);
        exit();
    }
}

/**
 * Enviar respuesta JSON para APIs que requieren admin
 * @param array $datosAdicionales Datos adicionales para incluir en la respuesta
 */
function requiereAdminAPI($datosAdicionales = []) {
    if (!esUsuarioAdmin()) {
        $respuesta = array_merge([
            'success' => false,
            'mensaje' => 'Acceso denegado. Solo para administradores.',
            'codigo' => 'ACCESS_DENIED',
            'tipoUsuario' => obtenerTipoUsuario()
        ], $datosAdicionales);
        
        header('Content-Type: application/json');
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit();
    }
}

/**
 * Logs de debug para la validación de sesión
 * @param string $contexto Contexto donde se llama (nombre del archivo o función)
 */
function debugSesion($contexto = 'UNKNOWN') {
    error_log("=== DEBUG SESIÓN [$contexto] ===");
    error_log("Usuario: " . ($_SESSION['usuario'] ?? 'NO SET'));
    error_log("ID Rango: " . ($_SESSION['id_rango'] ?? 'NO SET'));
    error_log("Admin: " . ($_SESSION['admin'] ?? 'NO SET'));
    error_log("Es Admin: " . (esUsuarioAdmin() ? 'SÍ' : 'NO'));
    error_log("Tipo Usuario: " . obtenerTipoUsuario());
}

/**
 * Información completa de la sesión para debugging
 * @return array Array con información de la sesión
 */
function obtenerInfoSesion() {
    return [
        'hayUsuario' => hayUsuarioLogueado(),
        'usuario' => obtenerUsuarioActual(),
        'esAdmin' => esUsuarioAdmin(),
        'tipoUsuario' => obtenerTipoUsuario(),
        'id_rango' => $_SESSION['id_rango'] ?? null,
        'admin' => $_SESSION['admin'] ?? null,
        'sesionCompleta' => $_SESSION
    ];
}
?>
