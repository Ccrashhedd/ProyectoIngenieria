<?php
/**
 * ============================================
 * SISTEMA DE FACTURACIÓN DEL CARRITO
 * Archivo: carritoFactura.php
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
ini_set('error_log', '../../../../logs/factura_debug.log');

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
    // 3. OBTENER DATOS DEL CARRITO
    // ============================================
    $carritoData = obtenerCarritoUsuario($idUsuario);
    
    if (!$carritoData['success'] || empty($carritoData['productos'])) {
        throw new Exception('El carrito está vacío. Agregue productos antes de proceder al pago.');
    }

    $productos = $carritoData['productos'];
    $subtotal = $carritoData['totalCarrito'];
    $impuestos = $carritoData['impuestos'];
    $totalFinal = $carritoData['totalFinal'];
    $idCarrito = $carritoData['idCarrito'];

    // ============================================
    // 4. GENERAR ID DE FACTURA
    // ============================================
    $idFactura = 'FACT_' . $idUsuario . '_' . date('Ymd_His') . '_' . rand(1000, 9999);

    // ============================================
    // 5. INICIAR TRANSACCIÓN
    // ============================================
    $conn->beginTransaction();

    try {
        // ============================================
        // 6. INSERTAR FACTURA
        // ============================================
        $stmtFactura = $conn->prepare("
            INSERT INTO FACTURA (idFactura, fecha, hora, idUsuario) 
            VALUES (?, CURDATE(), CURTIME(), ?)
        ");
        
        $stmtFactura->execute([
            $idFactura,
            $idUsuario
        ]);

        // ============================================
        // 7. INSERTAR DETALLES DE FACTURA
        // ============================================
        $stmtDetalle = $conn->prepare("
            INSERT INTO DETALLE_FACTURA (idDetalleFactura, idFactura, idProducto, cantidad, precio, total) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        foreach ($productos as $index => $producto) {
            $idDetalleFactura = $idFactura . '_DET_' . ($index + 1);
            
            $stmtDetalle->execute([
                $idDetalleFactura,
                $idFactura,
                $producto['idProducto'],
                $producto['cantidad'],
                $producto['precio'],
                $producto['precioTotal']
            ]);
        }

        // ============================================
        // 8. ACTUALIZAR STOCK DE PRODUCTOS
        // ============================================
        $stmtUpdateStock = $conn->prepare("
            UPDATE PRODUCTO 
            SET stock = stock - ? 
            WHERE idProducto = ? AND stock >= ?
        ");

        foreach ($productos as $producto) {
            $stmtUpdateStock->execute([
                $producto['cantidad'],
                $producto['idProducto'],
                $producto['cantidad']
            ]);

            // Verificar que se actualizó el stock
            if ($stmtUpdateStock->rowCount() === 0) {
                throw new Exception("Stock insuficiente para el producto: {$producto['nombre']}");
            }
        }

        // ============================================
        // 9. VACIAR CARRITO
        // ============================================
        $stmtVaciarCarrito = $conn->prepare("
            DELETE FROM CARRITO_DETALLE 
            WHERE idCarrito = ?
        ");
        
        $stmtVaciarCarrito->execute([$idCarrito]);

        // ============================================
        // 10. CONFIRMAR TRANSACCIÓN
        // ============================================
        $conn->commit();

        // ============================================
        // 11. RESPUESTA EXITOSA
        // ============================================
        echo json_encode([
            'success' => true,
            'mensaje' => 'Pago exitoso, pedido realizado',
            'factura' => [
                'idFactura' => $idFactura,
                'fecha' => date('Y-m-d H:i:s'),
                'subtotal' => $subtotal,
                'impuestos' => $impuestos,
                'total' => $totalFinal,
                'productos' => count($productos),
                'items' => $carritoData['totalItems']
            ],
            'redirect' => '/ProyectoIngenieria/ProyectoIngenieria/TecnoY_Page/php/frontend/landingPage.php'
        ], JSON_UNESCAPED_UNICODE);

    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollBack();
        throw $e;
    }

} catch (PDOException $e) {
    // Error de base de datos
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'mensaje' => 'Error de base de datos durante el procesamiento del pago',
        'debug' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // Error de validación
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'mensaje' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
