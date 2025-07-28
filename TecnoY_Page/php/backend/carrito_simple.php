<?php
session_start();
include 'CONEXION/conexion.php';

header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

try {
    switch ($accion) {
        case 'obtener':
            obtenerCarrito();
            break;
        case 'agregar':
            agregarAlCarrito();
            break;
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Acción no válida',
                'carrito' => []
            ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error interno: ' . $e->getMessage(),
        'carrito' => []
    ]);
}

function obtenerCarrito() {
    // Por ahora, devolver un carrito vacío
    echo json_encode([
        'success' => true,
        'carrito' => [],
        'total' => 0
    ]);
}

function agregarAlCarrito() {
    // Por ahora, simular que se agregó correctamente
    echo json_encode([
        'success' => true,
        'message' => 'Producto agregado al carrito (simulado)'
    ]);
}
?>
