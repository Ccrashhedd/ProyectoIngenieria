<?php
/**
 * ============================================
 * SISTEMA DE ACTUALIZACIÓN DEL CARRITO
 * Archivo: updCarrito.php
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
    $idCarritoDetalle = trim($_POST['idCarritoDetalle'] ?? '');
    $nuevaCantidad = intval($_POST['cantidad'] ?? 0);
    $accion = trim($_POST['accion'] ?? 'actualizar'); // actualizar, incrementar, decrementar

    // Validaciones básicas
    if (empty($idCarritoDetalle)) {
        throw new Exception('El ID del detalle del carrito es requerido');
    }

    if ($nuevaCantidad < 1 && $accion === 'actualizar') {
        throw new Exception('La cantidad debe ser al menos 1. Para eliminar use la función eliminar.');
    }

    // ============================================
    // 4. VERIFICAR QUE EL DETALLE PERTENECE AL USUARIO
    // ============================================
    $stmtVerificar = $conn->prepare("
        SELECT 
            cd.idCarritoDetalle,
            cd.idProducto,
            cd.cantidad,
            cd.precioTotal,
            p.nombProducto,
            p.precio,
            p.stock,
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
        throw new Exception('No tiene permisos para modificar este item');
    }

    // ============================================
    // 5. DETERMINAR LA NUEVA CANTIDAD SEGÚN LA ACCIÓN
    // ============================================
    $cantidadActual = $detalle['cantidad'];
    
    switch ($accion) {
        case 'incrementar':
            $nuevaCantidad = $cantidadActual + 1;
            break;
            
        case 'decrementar':
            $nuevaCantidad = $cantidadActual - 1;
            if ($nuevaCantidad < 1) {
                throw new Exception('La cantidad no puede ser menor a 1. Para eliminar use la función eliminar.');
            }
            break;
            
        case 'actualizar':
            // $nuevaCantidad ya está definida
            break;
            
        default:
            throw new Exception('Acción no válida. Use: actualizar, incrementar o decrementar');
    }

    // ============================================
    // 6. VERIFICAR STOCK DISPONIBLE
    // ============================================
    if ($nuevaCantidad > $detalle['stock']) {
        throw new Exception("Stock insuficiente. Solo hay {$detalle['stock']} unidades disponibles");
    }

    // ============================================
    // 7. ACTUALIZAR CANTIDAD Y PRECIO TOTAL
    // ============================================
    $nuevoPrecioTotal = $nuevaCantidad * $detalle['precio'];
    
    $stmtUpdate = $conn->prepare("
        UPDATE CARRITO_DETALLE 
        SET cantidad = ?, precioTotal = ? 
        WHERE idCarritoDetalle = ?
    ");
    
    $stmtUpdate->execute([
        $nuevaCantidad,
        $nuevoPrecioTotal,
        $idCarritoDetalle
    ]);

    // Verificar que se actualizó correctamente
    if ($stmtUpdate->rowCount() === 0) {
        throw new Exception('No se pudo actualizar el item del carrito');
    }

    // ============================================
    // 8. OBTENER INFORMACIÓN ACTUALIZADA DEL CARRITO
    // ============================================
    $carritoActualizado = obtenerCarritoUsuario($idUsuario);

    // ============================================
    // 9. PREPARAR MENSAJE DE RESPUESTA
    // ============================================
    $diferenciaCantidad = $nuevaCantidad - $cantidadActual;
    $accionTexto = '';
    
    if ($diferenciaCantidad > 0) {
        $accionTexto = "Cantidad aumentada en {$diferenciaCantidad}";
    } elseif ($diferenciaCantidad < 0) {
        $accionTexto = "Cantidad disminuida en " . abs($diferenciaCantidad);
    } else {
        $accionTexto = "Cantidad actualizada";
    }

    // ============================================
    // 10. RESPUESTA EXITOSA
    // ============================================
    echo json_encode([
        'success' => true,
        'mensaje' => $accionTexto,
        'detalleActualizado' => [
            'idCarritoDetalle' => $idCarritoDetalle,
            'idProducto' => $detalle['idProducto'],
            'nombreProducto' => $detalle['nombProducto'],
            'cantidadAnterior' => $cantidadActual,
            'cantidadNueva' => $nuevaCantidad,
            'precioUnitario' => floatval($detalle['precio']),
            'precioTotalNuevo' => $nuevoPrecioTotal
        ],
        'carrito' => [
            'totalItems' => $carritoActualizado['totalItems'],
            'totalCarrito' => $carritoActualizado['totalCarrito'],
            'totalFinal' => $carritoActualizado['totalFinal']
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
