<?php
/**
 * ============================================
 * SISTEMA DE INSERCIÓN AL CARRITO
 * Archivo: insertCarrito.php
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
    $idProducto = trim($_POST['idProducto'] ?? '');
    $cantidad = intval($_POST['cantidad'] ?? 1);

    // Validaciones básicas
    if (empty($idProducto)) {
        throw new Exception('El ID del producto es requerido');
    }

    if ($cantidad < 1) {
        throw new Exception('La cantidad debe ser al menos 1');
    }

    // ============================================
    // 4. VERIFICAR QUE EL PRODUCTO EXISTE
    // ============================================
    $stmtProducto = $conn->prepare("SELECT idProducto, nombProducto, precio, stock FROM PRODUCTO WHERE idProducto = ?");
    $stmtProducto->execute([$idProducto]);
    $producto = $stmtProducto->fetch();

    if (!$producto) {
        throw new Exception('El producto no existe');
    }

    // Verificar stock disponible
    if ($producto['stock'] < $cantidad) {
        throw new Exception("Stock insuficiente. Solo hay {$producto['stock']} unidades disponibles");
    }

    // ============================================
    // 5. VERIFICAR/CREAR CARRITO
    // ============================================
    $idCarrito = verificarOCrearCarrito($idUsuario);
    
    if (!$idCarrito) {
        throw new Exception('Error al acceder al carrito del usuario');
    }

    // ============================================
    // 6. VERIFICAR SI EL PRODUCTO YA ESTÁ EN EL CARRITO
    // ============================================
    $productoEnCarrito = verificarProductoEnCarrito($idUsuario, $idProducto);
    
    if ($productoEnCarrito['encontrado']) {
        // El producto ya existe, actualizar cantidad
        $nuevaCantidad = $productoEnCarrito['cantidad'] + $cantidad;
        
        // Verificar que la nueva cantidad no exceda el stock
        if ($nuevaCantidad > $producto['stock']) {
            throw new Exception("No se puede agregar más cantidad. Stock disponible: {$producto['stock']}, cantidad actual en carrito: {$productoEnCarrito['cantidad']}");
        }
        
        $nuevoPrecioTotal = $nuevaCantidad * $producto['precio'];
        
        // Actualizar cantidad existente
        $stmtUpdate = $conn->prepare("
            UPDATE CARRITO_DETALLE 
            SET cantidad = ?, precioTotal = ? 
            WHERE idCarritoDetalle = ?
        ");
        
        $stmtUpdate->execute([
            $nuevaCantidad, 
            $nuevoPrecioTotal, 
            $productoEnCarrito['idDetalle']
        ]);
        
        $mensaje = "Cantidad actualizada en el carrito";
        
    } else {
        // ============================================
        // 7. AGREGAR NUEVO PRODUCTO AL CARRITO
        // ============================================
        $idCarritoDetalle = 'CD_' . $idUsuario . '_' . $idProducto . '_' . time();
        $precioTotal = $cantidad * $producto['precio'];
        
        $stmtInsert = $conn->prepare("
            INSERT INTO CARRITO_DETALLE (idCarritoDetalle, idCarrito, idProducto, cantidad, precioTotal) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmtInsert->execute([
            $idCarritoDetalle,
            $idCarrito,
            $idProducto,
            $cantidad,
            $precioTotal
        ]);
        
        $mensaje = "Producto agregado al carrito";
    }

    // ============================================
    // 8. OBTENER INFORMACIÓN ACTUALIZADA DEL CARRITO
    // ============================================
    $carritoActualizado = obtenerCarritoUsuario($idUsuario);

    // ============================================
    // 9. RESPUESTA EXITOSA
    // ============================================
    echo json_encode([
        'success' => true,
        'mensaje' => $mensaje,
        'producto' => [
            'id' => $producto['idProducto'],
            'nombre' => $producto['nombProducto'],
            'precio' => floatval($producto['precio']),
            'cantidadAgregada' => $cantidad
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
