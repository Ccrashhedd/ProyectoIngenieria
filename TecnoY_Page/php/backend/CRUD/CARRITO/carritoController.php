<?php
/**
 * ============================================
 * CONTROLADOR PRINCIPAL DEL CARRITO
 * Archivo: carritoController.php
 * Ubicación: /php/backend/CRUD/CARRITO/
 * ============================================
 * Este archivo unifica todas las operaciones del carrito
 * para facilitar su uso desde el frontend
 */

session_start();
require_once '../../CONEXION/conexion.php';

// Definir constante para evitar ejecución independiente de verificarCarrito.php
define('INCLUDED_FROM_CONTROLLER', true);
require_once 'verificarCarrito.php';

// Headers de respuesta
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Configuración de errores (sin mostrar en output para mantener JSON limpio)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Cambio: no mostrar errores en el output
ini_set('log_errors', 1);
ini_set('error_log', '../../../../logs/carrito_debug.log');

// Capturar cualquier output previo y descartarlo
ob_start();

// ============================================
// FUNCIÓN HELPER PARA RESPUESTAS JSON LIMPIAS
// ============================================
function enviarRespuestaJSON($data, $httpCode = 200) {
    // Limpiar cualquier output previo
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Establecer código HTTP si es diferente de 200
    if ($httpCode !== 200) {
        http_response_code($httpCode);
    }
    
    // Enviar JSON limpio
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit; // Asegurar que no se ejecute más código
}

try {
    // ============================================
    // 1. VERIFICAR SESIÓN DE USUARIO
    // ============================================
    $idUsuario = $_SESSION['usuario'] ?? $_GET['idUsuario'] ?? $_POST['idUsuario'] ?? '';
    
    if (empty($idUsuario)) {
        throw new Exception('Usuario no autenticado. Inicie sesión para continuar.');
    }

    // ============================================
    // 2. DETERMINAR ACCIÓN A REALIZAR
    // ============================================
    $accion = '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $accion = $_GET['accion'] ?? 'obtener';
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $accion = $_POST['accion'] ?? '';
    }

    if (empty($accion)) {
        throw new Exception('Debe especificar una acción');
    }

    // ============================================
    // 3. PROCESAR SEGÚN LA ACCIÓN
    // ============================================
    switch ($accion) {
        
        // ==========================================
        // OBTENER CARRITO COMPLETO
        // ==========================================
        case 'obtener':
        case 'get':
            $resultado = obtenerCarritoUsuario($idUsuario);
            enviarRespuestaJSON($resultado);

        // ==========================================
        // VERIFICAR/CREAR CARRITO
        // ==========================================
        case 'verificar':
            $idCarrito = verificarOCrearCarrito($idUsuario);
            enviarRespuestaJSON([
                'success' => $idCarrito !== false,
                'idCarrito' => $idCarrito,
                'mensaje' => $idCarrito ? 'Carrito verificado/creado exitosamente' : 'Error al acceder al carrito'
            ]);

        // ==========================================
        // AGREGAR PRODUCTO AL CARRITO
        // ==========================================
        case 'agregar':
        case 'add':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Esta acción requiere método POST');
            }

            $idProducto = trim($_POST['idProducto'] ?? '');
            $cantidad = intval($_POST['cantidad'] ?? 1);

            if (empty($idProducto)) {
                throw new Exception('El ID del producto es requerido');
            }

            if ($cantidad < 1) {
                throw new Exception('La cantidad debe ser al menos 1');
            }

            // Lógica de inserción inline
            // Verificar que el producto existe
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

            // Verificar/crear carrito
            $idCarrito = verificarOCrearCarrito($idUsuario);
            
            if (!$idCarrito) {
                throw new Exception('Error al acceder al carrito del usuario');
            }

            // Verificar si el producto ya está en el carrito
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
                // Agregar nuevo producto al carrito
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

            // Obtener información actualizada del carrito
            $carritoActualizado = obtenerCarritoUsuario($idUsuario);

            // Respuesta exitosa
            enviarRespuestaJSON([
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
            ]);

        // ==========================================
        // ACTUALIZAR CANTIDAD
        // ==========================================
        case 'actualizar':
        case 'update':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Esta acción requiere método POST');
            }

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

            // Verificar que el detalle pertenece al usuario
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

            // Determinar la nueva cantidad según la acción
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

            // Verificar stock disponible
            if ($nuevaCantidad > $detalle['stock']) {
                throw new Exception("Stock insuficiente. Solo hay {$detalle['stock']} unidades disponibles");
            }

            // Actualizar cantidad y precio total
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

            // Obtener información actualizada del carrito
            $carritoActualizado = obtenerCarritoUsuario($idUsuario);

            // Preparar mensaje de respuesta
            $diferenciaCantidad = $nuevaCantidad - $cantidadActual;
            $accionTexto = '';
            
            if ($diferenciaCantidad > 0) {
                $accionTexto = "Cantidad aumentada en {$diferenciaCantidad}";
            } elseif ($diferenciaCantidad < 0) {
                $accionTexto = "Cantidad disminuida en " . abs($diferenciaCantidad);
            } else {
                $accionTexto = "Cantidad actualizada";
            }

            // Respuesta exitosa
            enviarRespuestaJSON([
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
            ]);
            break;

        // ==========================================
        // ELIMINAR PRODUCTO O VACIAR CARRITO
        // ==========================================
        case 'eliminar':
        case 'delete':
        case 'vaciar':
        case 'clear':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Esta acción requiere método POST');
            }

            if ($accion === 'vaciar' || $accion === 'clear') {
                // VACIAR TODO EL CARRITO
                $idCarrito = verificarOCrearCarrito($idUsuario);
                
                if (!$idCarrito) {
                    throw new Exception('Error al acceder al carrito del usuario');
                }

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

            } else {
                // ELIMINAR UN PRODUCTO ESPECÍFICO
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
            }

            // Obtener información actualizada del carrito
            $carritoActualizado = obtenerCarritoUsuario($idUsuario);

            // Respuesta exitosa
            enviarRespuestaJSON([
                'success' => true,
                'mensaje' => $mensaje,
                'accion' => $accion,
                'carrito' => [
                    'totalItems' => $carritoActualizado['totalItems'],
                    'totalCarrito' => $carritoActualizado['totalCarrito'],
                    'totalFinal' => $carritoActualizado['totalFinal'],
                    'productos' => $carritoActualizado['productos']
                ]
            ]);
            break;

        // ==========================================
        // CONTAR ITEMS EN EL CARRITO
        // ==========================================
        case 'contar':
        case 'count':
            $carritoInfo = obtenerCarritoUsuario($idUsuario);
            enviarRespuestaJSON([
                'success' => true,
                'totalItems' => $carritoInfo['totalItems'],
                'totalCarrito' => $carritoInfo['totalCarrito']
            ]);
            break;

        // ==========================================
        // VERIFICAR PRODUCTO ESPECÍFICO
        // ==========================================
        case 'verificar_producto':
            $idProducto = $_GET['idProducto'] ?? $_POST['idProducto'] ?? '';
            
            if (empty($idProducto)) {
                throw new Exception('El ID del producto es requerido');
            }

            $resultado = verificarProductoEnCarrito($idUsuario, $idProducto);
            enviarRespuestaJSON([
                'success' => true,
                'producto' => $resultado
            ]);
            break;

        // ==========================================
        // ACCIÓN NO VÁLIDA
        // ==========================================
        default:
            throw new Exception('Acción no válida. Acciones disponibles: obtener, verificar, agregar, actualizar, eliminar, vaciar, contar, verificar_producto');
    }

} catch (Exception $e) {
    // Error de validación o lógica
    enviarRespuestaJSON([
        'success' => false,
        'mensaje' => $e->getMessage(),
        'accion' => $accion ?? 'desconocida'
    ], 400);

} catch (PDOException $e) {
    // Error de base de datos
    enviarRespuestaJSON([
        'success' => false,
        'mensaje' => 'Error de conexión a la base de datos',
        'debug' => $e->getMessage()
    ], 500);
}
?>
