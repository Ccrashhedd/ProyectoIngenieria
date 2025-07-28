<?php
/**
 * Sistema centralizado de manejo del carrito
 * Punto único de acceso para todas las operaciones del carrito
 */

// Configurar headers antes de cualquier output
header('Content-Type: application/json; charset=utf-8');

// Evitar que errores PHP contaminen la respuesta JSON
error_reporting(0);
ini_set('display_errors', 0);

session_start();

// Incluir archivos necesarios
include_once __DIR__ . '/CONEXION/conexion.php';
include_once __DIR__ . '/CRUD/CARRITO/verificarCarrito.php';
include_once __DIR__ . '/CRUD/CARRITO/insertCarrito.php';
include_once __DIR__ . '/CRUD/CARRITO/updCarrito.php';
include_once __DIR__ . '/CRUD/CARRITO/elimCarrito.php';

// Verificar que el usuario esté logueado
$idUsuario = $_SESSION['usuario'] ?? null;

if (!$idUsuario) {
    echo json_encode([
        'success' => false,
        'message' => 'Debes iniciar sesión para usar el carrito',
        'code' => 'NOT_LOGGED_IN',
        'redirect' => 'login.php'
    ]);
    exit;
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

try {
    switch ($accion) {
        // === VERIFICACIÓN Y OBTENCIÓN ===
        case 'verificar':
            $idCarrito = verificarOCrearCarrito($idUsuario);
            echo json_encode([
                'success' => $idCarrito !== false,
                'idCarrito' => $idCarrito,
                'message' => $idCarrito ? 'Carrito verificado/creado correctamente' : 'Error al acceder al carrito'
            ]);
            break;
            
        case 'obtener':
            $resultado = obtenerCarritoUsuario($idUsuario);
            echo json_encode($resultado);
            break;
            
        case 'verificarProducto':
            $idProducto = $_POST['idProducto'] ?? $_GET['idProducto'] ?? '';
            if (empty($idProducto)) {
                throw new Exception('ID de producto no especificado');
            }
            
            $resultado = verificarProductoEnCarrito($idUsuario, $idProducto);
            echo json_encode(['success' => true, 'producto' => $resultado]);
            break;
            
        // === INSERCIÓN DE PRODUCTOS ===
        case 'agregar':
            $idProducto = $_POST['idProducto'] ?? '';
            $cantidad = intval($_POST['cantidad'] ?? 1);
            
            if (empty($idProducto)) {
                throw new Exception('ID de producto no especificado');
            }
            
            $resultado = agregarProductoAlCarrito($idUsuario, $idProducto, $cantidad);
            echo json_encode($resultado);
            break;
            
        case 'agregarMultiples':
            $productos = json_decode($_POST['productos'] ?? '[]', true);
            
            if (empty($productos) || !is_array($productos)) {
                throw new Exception('Lista de productos no válida');
            }
            
            $resultado = agregarMultiplesProductos($idUsuario, $productos);
            echo json_encode($resultado);
            break;
            
        // === ACTUALIZACIÓN DE CANTIDADES ===
        case 'actualizar':
            $idCarritoDetalle = $_POST['idCarritoDetalle'] ?? '';
            $nuevaCantidad = intval($_POST['cantidad'] ?? 0);
            
            if (empty($idCarritoDetalle)) {
                throw new Exception('ID de detalle del carrito no especificado');
            }
            
            $resultado = actualizarCantidadProducto($idUsuario, $idCarritoDetalle, $nuevaCantidad);
            echo json_encode($resultado);
            break;
            
        case 'incrementar':
            $idCarritoDetalle = $_POST['idCarritoDetalle'] ?? '';
            $incremento = intval($_POST['incremento'] ?? 1);
            
            if (empty($idCarritoDetalle)) {
                throw new Exception('ID de detalle del carrito no especificado');
            }
            
            $resultado = incrementarCantidad($idUsuario, $idCarritoDetalle, $incremento);
            echo json_encode($resultado);
            break;
            
        case 'decrementar':
            $idCarritoDetalle = $_POST['idCarritoDetalle'] ?? '';
            $decremento = intval($_POST['decremento'] ?? 1);
            
            if (empty($idCarritoDetalle)) {
                throw new Exception('ID de detalle del carrito no especificado');
            }
            
            $resultado = decrementarCantidad($idUsuario, $idCarritoDetalle, $decremento);
            echo json_encode($resultado);
            break;
            
        case 'actualizarMultiples':
            $actualizaciones = json_decode($_POST['actualizaciones'] ?? '{}', true);
            
            if (empty($actualizaciones) || !is_array($actualizaciones)) {
                throw new Exception('Actualizaciones no válidas');
            }
            
            $resultado = actualizarMultiplesProductos($idUsuario, $actualizaciones);
            echo json_encode($resultado);
            break;
            
        case 'recalcular':
            $resultado = recalcularCarrito($idUsuario);
            echo json_encode($resultado);
            break;
            
        // === ELIMINACIÓN DE PRODUCTOS ===
        case 'eliminar':
            $idCarritoDetalle = $_POST['idCarritoDetalle'] ?? '';
            
            if (empty($idCarritoDetalle)) {
                throw new Exception('ID de detalle del carrito no especificado');
            }
            
            $resultado = eliminarProductoDelCarrito($idUsuario, $idCarritoDetalle);
            echo json_encode($resultado);
            break;
            
        case 'eliminarPorProducto':
            $idProducto = $_POST['idProducto'] ?? '';
            
            if (empty($idProducto)) {
                throw new Exception('ID de producto no especificado');
            }
            
            $resultado = eliminarProductoPorId($idUsuario, $idProducto);
            echo json_encode($resultado);
            break;
            
        case 'eliminarMultiples':
            $idsCarritoDetalle = json_decode($_POST['idsCarritoDetalle'] ?? '[]', true);
            
            if (empty($idsCarritoDetalle) || !is_array($idsCarritoDetalle)) {
                throw new Exception('Lista de productos no válida');
            }
            
            $resultado = eliminarMultiplesProductos($idUsuario, $idsCarritoDetalle);
            echo json_encode($resultado);
            break;
            
        case 'vaciar':
            $resultado = vaciarCarrito($idUsuario);
            echo json_encode($resultado);
            break;
            
        case 'limpiarNoDisponibles':
            $resultado = limpiarProductosNoDisponibles($idUsuario);
            echo json_encode($resultado);
            break;
            
        // === ESTADÍSTICAS Y UTILIDADES ===
        case 'estadisticas':
            $carrito = obtenerCarritoUsuario($idUsuario);
            
            if ($carrito['success']) {
                echo json_encode([
                    'success' => true,
                    'estadisticas' => [
                        'total_productos' => count($carrito['productos']),
                        'total_items' => $carrito['totalItems'],
                        'subtotal' => $carrito['totalCarrito'],
                        'impuestos' => $carrito['impuestos'],
                        'total_final' => $carrito['totalFinal'],
                        'carrito_vacio' => count($carrito['productos']) === 0
                    ]
                ]);
            } else {
                echo json_encode($carrito);
            }
            break;
            
        case 'conteo':
            $carrito = obtenerCarritoUsuario($idUsuario);
            
            if ($carrito['success']) {
                echo json_encode([
                    'success' => true,
                    'count' => $carrito['totalItems'],
                    'productos' => count($carrito['productos'])
                ]);
            } else {
                echo json_encode(['success' => false, 'count' => 0]);
            }
            break;
            
        default:
            throw new Exception('Acción no válida: ' . $accion);
    }
    
} catch (Exception $e) {
    error_log("Error en carritoManager: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'code' => 'GENERAL_ERROR'
    ]);
}
?>
