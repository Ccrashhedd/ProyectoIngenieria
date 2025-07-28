<?php
/**
 * ============================================
 * SISTEMA DE PEDIDOS GENERALES (ADMIN)
 * Archivo: pedidosGeneral.php
 * Ubicación: /php/backend/CRUD/PEDIDOS/
 * ============================================
 */

session_start();
require_once '../../CONEXION/conexion.php';
require_once '../../UTILS/session_utils.php';

// Headers de respuesta
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Limpiar buffer de salida
if (ob_get_level()) {
    ob_end_clean();
}
ob_start();

try {
    // ============================================
    // FUNCIÓN PARA ENVIAR RESPUESTA JSON
    // ============================================
    function enviarRespuestaJSON($data) {
        ob_clean();
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }

    // ============================================
    // 1. VERIFICAR USUARIO AUTENTICADO Y ADMIN
    // ============================================
    requiereAdminAPI();

    // ============================================
    // 2. DETERMINAR ACCIÓN
    // ============================================
    $accion = $_GET['accion'] ?? $_POST['accion'] ?? 'obtener';

    switch ($accion) {
        case 'obtener':
            obtenerTodosPedidos($conn);
            break;
        
        case 'detalle':
            $idFactura = $_GET['idFactura'] ?? $_POST['idFactura'] ?? null;
            if (!$idFactura) {
                enviarRespuestaJSON([
                    'success' => false,
                    'mensaje' => 'ID de factura requerido'
                ]);
            }
            obtenerDetallePedidoAdmin($conn, $idFactura);
            break;
            
        case 'estadisticas':
            obtenerEstadisticasPedidos($conn);
            break;
            
        default:
            enviarRespuestaJSON([
                'success' => false,
                'mensaje' => 'Acción no válida'
            ]);
    }

} catch (Exception $e) {
    enviarRespuestaJSON([
        'success' => false,
        'mensaje' => 'Error del servidor: ' . $e->getMessage()
    ]);
}

// ============================================
// FUNCIÓN PARA OBTENER TODOS LOS PEDIDOS
// ============================================
function obtenerTodosPedidos($conn) {
    try {
        $stmt = $conn->prepare("
            SELECT 
                f.idFactura,
                f.fecha,
                f.hora,
                f.idUsuario,
                u.nombre_usuario,
                u.email,
                COUNT(df.idDetalleFactura) as totalProductos,
                SUM(df.precioTotal) as totalPedido,
                CONCAT(f.fecha, ' ', f.hora) as fechaCompleta
            FROM factura f
            JOIN usuario u ON f.idUsuario = u.idUsuario
            LEFT JOIN detalle_factura df ON f.idFactura = df.idFactura
            GROUP BY f.idFactura
            ORDER BY f.fecha DESC, f.hora DESC
        ");
        
        $stmt->execute();
        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formatear datos para el frontend
        $pedidosFormateados = [];
        foreach ($pedidos as $pedido) {
            $pedidosFormateados[] = [
                'idFactura' => $pedido['idFactura'],
                'fecha' => $pedido['fecha'],
                'hora' => $pedido['hora'],
                'fechaCompleta' => $pedido['fechaCompleta'],
                'cliente' => [
                    'idUsuario' => $pedido['idUsuario'],
                    'nombre' => $pedido['nombre_usuario'],
                    'email' => $pedido['email']
                ],
                'totalProductos' => (int)$pedido['totalProductos'],
                'totalPedido' => (float)$pedido['totalPedido'],
                'estado' => 'Completado'
            ];
        }
        
        enviarRespuestaJSON([
            'success' => true,
            'pedidos' => $pedidosFormateados,
            'totalPedidos' => count($pedidosFormateados)
        ]);
        
    } catch (PDOException $e) {
        enviarRespuestaJSON([
            'success' => false,
            'mensaje' => 'Error al obtener pedidos: ' . $e->getMessage()
        ]);
    }
}

// ============================================
// FUNCIÓN PARA OBTENER DETALLE DE PEDIDO (ADMIN)
// ============================================
function obtenerDetallePedidoAdmin($conn, $idFactura) {
    try {
        // Obtener información general de la factura
        $stmtFactura = $conn->prepare("
            SELECT 
                f.idFactura,
                f.fecha,
                f.hora,
                f.idUsuario,
                u.nombre_usuario,
                u.email
            FROM factura f
            JOIN usuario u ON f.idUsuario = u.idUsuario
            WHERE f.idFactura = ?
        ");
        $stmtFactura->execute([$idFactura]);
        $factura = $stmtFactura->fetch(PDO::FETCH_ASSOC);
        
        if (!$factura) {
            enviarRespuestaJSON([
                'success' => false,
                'mensaje' => 'Factura no encontrada'
            ]);
        }
        
        // Obtener detalles de los productos
        $stmtDetalles = $conn->prepare("
            SELECT 
                df.idDetalleFactura,
                df.cantidad,
                df.precioTotal,
                df.idProducto,
                p.nombProducto,
                p.precio as precioUnitario,
                p.imagen,
                m.nombre as marca_nombre
            FROM detalle_factura df
            JOIN producto p ON df.idProducto = p.idProducto
            LEFT JOIN marca m ON p.marca_id = m.id
            WHERE df.idFactura = ?
            ORDER BY df.idDetalleFactura
        ");
        $stmtDetalles->execute([$idFactura]);
        $detalles = $stmtDetalles->fetchAll(PDO::FETCH_ASSOC);
        
        // Calcular totales
        $subtotal = 0;
        foreach ($detalles as $detalle) {
            $subtotal += $detalle['precioTotal'];
        }
        $impuesto = $subtotal * 0.07; // 7% ITBMS
        $total = $subtotal + $impuesto;
        
        enviarRespuestaJSON([
            'success' => true,
            'factura' => $factura,
            'productos' => $detalles,
            'totales' => [
                'subtotal' => $subtotal,
                'impuesto' => $impuesto,
                'total' => $total
            ]
        ]);
        
    } catch (PDOException $e) {
        enviarRespuestaJSON([
            'success' => false,
            'mensaje' => 'Error al obtener detalle del pedido: ' . $e->getMessage()
        ]);
    }
}

// ============================================
// FUNCIÓN PARA OBTENER ESTADÍSTICAS
// ============================================
function obtenerEstadisticasPedidos($conn) {
    try {
        // Total de pedidos
        $stmtTotal = $conn->prepare("SELECT COUNT(*) as total FROM factura");
        $stmtTotal->execute();
        $totalPedidos = $stmtTotal->fetch()['total'];
        
        // Ventas totales
        $stmtVentas = $conn->prepare("
            SELECT SUM(df.precioTotal) as ventasTotal
            FROM detalle_factura df
        ");
        $stmtVentas->execute();
        $ventasTotal = $stmtVentas->fetch()['ventasTotal'] ?? 0;
        
        // Pedidos por mes
        $stmtMes = $conn->prepare("
            SELECT 
                DATE_FORMAT(fecha, '%Y-%m') as mes,
                COUNT(*) as cantidad,
                SUM(df.precioTotal) as ventas
            FROM factura f
            LEFT JOIN detalle_factura df ON f.idFactura = df.idFactura
            GROUP BY DATE_FORMAT(fecha, '%Y-%m')
            ORDER BY mes DESC
            LIMIT 12
        ");
        $stmtMes->execute();
        $pedidosPorMes = $stmtMes->fetchAll(PDO::FETCH_ASSOC);
        
        enviarRespuestaJSON([
            'success' => true,
            'estadisticas' => [
                'totalPedidos' => (int)$totalPedidos,
                'ventasTotal' => (float)$ventasTotal,
                'pedidosPorMes' => $pedidosPorMes
            ]
        ]);
        
    } catch (PDOException $e) {
        enviarRespuestaJSON([
            'success' => false,
            'mensaje' => 'Error al obtener estadísticas: ' . $e->getMessage()
        ]);
    }
}
?>