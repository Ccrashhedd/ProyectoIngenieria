<?php
/**
 * ============================================
 * SISTEMA DE PEDIDOS PERSONALES
 * Archivo: pedidosPersonal.php
 * Ubicación: /php/backend/CRUD/PEDIDOS/
 * ============================================
 */

session_start();
require_once '../../CONEXION/conexion.php';

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
    // 1. VERIFICAR USUARIO AUTENTICADO
    // ============================================
    if (!isset($_SESSION['usuario'])) {
        enviarRespuestaJSON([
            'success' => false,
            'mensaje' => 'Usuario no autenticado'
        ]);
    }

    $idUsuario = $_SESSION['usuario'];

    // ============================================
    // 2. DETERMINAR ACCIÓN
    // ============================================
    $accion = $_GET['accion'] ?? $_POST['accion'] ?? 'obtener';

    switch ($accion) {
        case 'obtener':
            obtenerPedidosPersonales($conn, $idUsuario);
            break;
        
        case 'detalle':
            $idFactura = $_GET['idFactura'] ?? $_POST['idFactura'] ?? null;
            if (!$idFactura) {
                enviarRespuestaJSON([
                    'success' => false,
                    'mensaje' => 'ID de factura requerido'
                ]);
            }
            obtenerDetallePedido($conn, $idFactura, $idUsuario);
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
// FUNCIÓN PARA OBTENER PEDIDOS PERSONALES
// ============================================
function obtenerPedidosPersonales($conn, $idUsuario) {
    try {
        $stmt = $conn->prepare("
            SELECT 
                f.idFactura,
                f.fecha,
                f.hora,
                COUNT(df.idDetalleFactura) as totalProductos,
                SUM(df.precioTotal) as totalPedido,
                CONCAT(f.fecha, ' ', f.hora) as fechaCompleta
            FROM factura f
            LEFT JOIN detalle_factura df ON f.idFactura = df.idFactura
            WHERE f.idUsuario = ?
            GROUP BY f.idFactura
            ORDER BY f.fecha DESC, f.hora DESC
        ");
        
        $stmt->execute([$idUsuario]);
        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formatear datos para el frontend
        $pedidosFormateados = [];
        foreach ($pedidos as $pedido) {
            $pedidosFormateados[] = [
                'idFactura' => $pedido['idFactura'],
                'fecha' => $pedido['fecha'],
                'hora' => $pedido['hora'],
                'fechaCompleta' => $pedido['fechaCompleta'],
                'totalProductos' => (int)$pedido['totalProductos'],
                'totalPedido' => (float)$pedido['totalPedido'],
                'estado' => 'Completado' // Por defecto, puedes agregar campo estado en la BD
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
// FUNCIÓN PARA OBTENER DETALLE DE PEDIDO
// ============================================
function obtenerDetallePedido($conn, $idFactura, $idUsuario) {
    try {
        // Verificar que la factura pertenece al usuario
        $stmtVerificar = $conn->prepare("
            SELECT idFactura FROM factura 
            WHERE idFactura = ? AND idUsuario = ?
        ");
        $stmtVerificar->execute([$idFactura, $idUsuario]);
        
        if (!$stmtVerificar->fetch()) {
            enviarRespuestaJSON([
                'success' => false,
                'mensaje' => 'Pedido no encontrado o no autorizado'
            ]);
        }
        
        // Obtener información general de la factura
        $stmtFactura = $conn->prepare("
            SELECT 
                f.idFactura,
                f.fecha,
                f.hora,
                u.nombre_usuario,
                u.email
            FROM factura f
            JOIN usuario u ON f.idUsuario = u.idUsuario
            WHERE f.idFactura = ?
        ");
        $stmtFactura->execute([$idFactura]);
        $factura = $stmtFactura->fetch(PDO::FETCH_ASSOC);
        
        // Obtener detalles de los productos
        $stmtDetalles = $conn->prepare("
            SELECT 
                df.idDetalleFactura,
                df.cantidad,
                df.precioTotal,
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
?>