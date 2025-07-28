<?php
// ============================================
// CERRAR SESIÓN
// ============================================

session_start();

// Destruir todas las variables de sesión
session_unset();

// Destruir la sesión
session_destroy();

// Respuesta JSON para AJAX
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Sesión cerrada exitosamente'
]);
?>
