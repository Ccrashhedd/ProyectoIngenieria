<?php
/**
 * ============================================
 * SISTEMA DE ELIMINACIÓN DEL CARRITO
 * Archivo: elimCarrito.php
 * Ubicación: /php/backend/CRUD/CARRITO/
 * ============================================
 */

session_start();
require_once '../../CONEXION/conexion.php';
require_once 'verificarCarrito.php';

// Headers de respuesta
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '../../../../logs/carrito_debug.log');

try {
    // ============================================
    // 1. VALIDAR MÉTODO DE PETICIÓN
    // ============================================
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido. Solo se acepta POST.');
    }

    // ============================================
    // 2. VERIFICAR SESIÓN DE USUARIO
    // ============================================
    $idUsuario = $_SESSION['usuario'] ?? $_POST['idUsuario'] ?? '';
    
    if (empty($idUsuario)) {
        throw new Exception('Usuario no autenticado. Inicie sesión para continuar.');
    }

    // ============================================
    // 3. OBTENER Y VALIDAR DATOS
    // ============================================
    $accion = trim($_POST['accion'] ?? '');
    
    if (empty($accion)) {
        throw new Exception('Debe especificar una acción: eliminar_producto o vaciar_carrito');
    }

    // ============================================
    // 4. VERIFICAR/OBTENER CARRITO DEL USUARIO
    // ============================================
    $idCarrito = verificarOCrearCarrito($idUsuario);
    
    if (!$idCarrito) {
        throw new Exception('Error al acceder al carrito del usuario');
    }

    // ============================================
    // 5. PROCESAR SEGÚN LA ACCIÓN
    // ============================================
    switch ($accion) {
        
        case 'eliminar_producto':
            // ============================================
            // ELIMINAR UN PRODUCTO ESPECÍFICO
            // ============================================
            $idCarritoDetalle = trim($_POST['idCarritoDetalle'] ?? '');
            
            if (empty($idCarritoDetalle)) {
                throw new Exception('El ID del detalle del carrito es requerido');
            }

            // Verificar que el detalle pertenece al usuario
            $stmtVerificar = $conn->prepare("
                SELECT 
                    cd.idCarritoDetalle,
                    cd.cantidad,
                    p.nombProducto,
                    c.idUsuario
                FROM CARRITO_DETALLE cd
                JOIN CARRITO c ON cd.idCarrito = c.idCarrito
                JOIN PRODUCTO p ON cd.idProducto = p.idProducto
                WHERE cd.idCarritoDetalle = ?
            ");
            
            $stmtVerificar->execute([$idCarritoDetalle]);
            $detalle = $stmtVerificar->fetch();

            if (!$detalle) {
                throw new Exception('El item del carrito no existe');
            }

            if ($detalle['idUsuario'] !== $idUsuario) {
                throw new Exception('No tiene permisos para eliminar este item');
            }

            // Eliminar el producto del carrito
            $stmtEliminar = $conn->prepare("DELETE FROM CARRITO_DETALLE WHERE idCarritoDetalle = ?");
            $stmtEliminar->execute([$idCarritoDetalle]);

            if ($stmtEliminar->rowCount() === 0) {
                throw new Exception('No se pudo eliminar el producto del carrito');
            }

            $mensaje = "Producto '{$detalle['nombProducto']}' eliminado del carrito";
            break;

        case 'vaciar_carrito':
            // ============================================
            // VACIAR TODO EL CARRITO
            // ============================================
            
            // Contar productos antes de eliminar
            $stmtContar = $conn->prepare("
                SELECT COUNT(*) as total 
                FROM CARRITO_DETALLE cd 
                JOIN CARRITO c ON cd.idCarrito = c.idCarrito 
                WHERE c.idUsuario = ?
            ");
            $stmtContar->execute([$idUsuario]);
            $totalProductos = $stmtContar->fetchColumn();

            if ($totalProductos == 0) {
                throw new Exception('El carrito ya está vacío');
            }

            // Eliminar todos los productos del carrito del usuario
            $stmtVaciar = $conn->prepare("
                DELETE cd FROM CARRITO_DETALLE cd 
                JOIN CARRITO c ON cd.idCarrito = c.idCarrito 
                WHERE c.idUsuario = ?
            ");
            $stmtVaciar->execute([$idUsuario]);

            if ($stmtVaciar->rowCount() === 0) {
                throw new Exception('No se pudo vaciar el carrito');
            }

            $mensaje = "Carrito vaciado exitosamente. Se eliminaron {$totalProductos} producto(s)";
            break;

        default:
            throw new Exception('Acción no válida. Use: eliminar_producto o vaciar_carrito');
    }

    // ============================================
    // 6. OBTENER INFORMACIÓN ACTUALIZADA DEL CARRITO
    // ============================================
    $carritoActualizado = obtenerCarritoUsuario($idUsuario);

    // ============================================
    // 7. RESPUESTA EXITOSA
    // ============================================
    echo json_encode([
        'success' => true,
        'mensaje' => $mensaje,
        'accion' => $accion,
        'carrito' => [
            'totalItems' => $carritoActualizado['totalItems'],
            'totalCarrito' => $carritoActualizado['totalCarrito'],
            'totalFinal' => $carritoActualizado['totalFinal'],
            'productos' => $carritoActualizado['productos']
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    // Error de base de datos
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'mensaje' => 'Error de conexión a la base de datos',
        'debug' => $e->getMessage()
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
