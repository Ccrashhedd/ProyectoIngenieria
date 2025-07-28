<?php
/**
 * Sistema de verificación y creación automática de carritos
 * Garantiza que cada usuario tenga un carrito único y persistente
 */

include_once __DIR__ . '/../../CONEXION/conexion.php';

/**
 * Verifica si el usuario tiene un carrito, si no lo crea automáticamente
 * @param string $idUsuario - ID del usuario
 * @return string|false - ID del carrito o false en caso de error
 */
function verificarOCrearCarrito($idUsuario) {
    global $conn;
    
    try {
        // Verificar si el usuario ya tiene un carrito
        $stmt = $conn->prepare("SELECT idCarrito FROM CARRITO WHERE idUsuario = ?");
        $stmt->execute([$idUsuario]);
        $resultado = $stmt->fetch();
        
        if ($resultado) {
            // El usuario ya tiene carrito, devolver su ID
            return $resultado['idCarrito'];
        } else {
            // El usuario no tiene carrito, crear uno nuevo
            return crearNuevoCarrito($idUsuario);
        }
        
    } catch (Exception $e) {
        error_log("Error en verificarOCrearCarrito: " . $e->getMessage());
        return false;
    }
}

/**
 * Crea un nuevo carrito para el usuario
 * @param string $idUsuario - ID del usuario
 * @return string|false - ID del nuevo carrito o false en caso de error
 */
function crearNuevoCarrito($idUsuario) {
    global $conn;
    
    try {
        // Generar ID único para el carrito
        $idCarrito = 'CART_' . $idUsuario . '_' . time();
        
        // Insertar nuevo carrito
        $stmt = $conn->prepare("INSERT INTO CARRITO (idCarrito, idUsuario) VALUES (?, ?)");
        $stmt->execute([$idCarrito, $idUsuario]);
        
        return $idCarrito;
        
    } catch (Exception $e) {
        error_log("Error en crearNuevoCarrito: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtiene el carrito del usuario (lo crea si no existe)
 * @param string $idUsuario - ID del usuario
 * @return array - Información del carrito y sus productos
 */
function obtenerCarritoUsuario($idUsuario) {
    global $conn;
    
    try {
        // Verificar/crear carrito
        $idCarrito = verificarOCrearCarrito($idUsuario);
        
        if (!$idCarrito) {
            return [
                'success' => false,
                'message' => 'Error al acceder al carrito',
                'carrito' => []
            ];
        }
        
        // Obtener productos del carrito
        $stmt = $conn->prepare("
            SELECT 
                cd.idCarritoDetalle,
                cd.idProducto,
                cd.cantidad,
                cd.precioTotal,
                p.nombProducto,
                p.modelo,
                p.precio,
                p.stock,
                p.imagen,
                m.nombMarca,
                c.nombCategoria
            FROM CARRITO_DETALLE cd
            JOIN PRODUCTO p ON cd.idProducto = p.idProducto
            JOIN MARCA m ON p.idMarca = m.idMarca
            JOIN CATEGORIA c ON p.idCategoria = c.idCategoria
            WHERE cd.idCarrito = ?
            ORDER BY cd.idCarritoDetalle DESC
        ");
        
        $stmt->execute([$idCarrito]);
        $productos = [];
        $totalCarrito = 0;
        $totalItems = 0;
        
        while ($row = $stmt->fetch()) {
            $productos[] = [
                'idDetalle' => $row['idCarritoDetalle'],
                'idProducto' => $row['idProducto'],
                'nombre' => $row['nombProducto'],
                'modelo' => $row['modelo'],
                'marca' => $row['nombMarca'],
                'categoria' => $row['nombCategoria'],
                'precio' => floatval($row['precio']),
                'cantidad' => intval($row['cantidad']),
                'precioTotal' => floatval($row['precioTotal']),
                'stock' => intval($row['stock']),
                'imagen' => $row['imagen']
            ];
            
            $totalCarrito += floatval($row['precioTotal']);
            $totalItems += intval($row['cantidad']);
        }
        
        return [
            'success' => true,
            'idCarrito' => $idCarrito,
            'productos' => $productos,
            'totalItems' => $totalItems,
            'totalCarrito' => $totalCarrito,
            'impuestos' => $totalCarrito * 0.07, // ITBMS 7%
            'totalFinal' => $totalCarrito * 1.07
        ];
        
    } catch (Exception $e) {
        error_log("Error en obtenerCarritoUsuario: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error al obtener carrito: ' . $e->getMessage(),
            'carrito' => []
        ];
    }
}

/**
 * Verifica si un producto específico está en el carrito del usuario
 * @param string $idUsuario - ID del usuario
 * @param string $idProducto - ID del producto
 * @return array - Información del producto en el carrito
 */
function verificarProductoEnCarrito($idUsuario, $idProducto) {
    global $conn;
    
    try {
        $idCarrito = verificarOCrearCarrito($idUsuario);
        
        if (!$idCarrito) {
            return ['encontrado' => false];
        }
        
        $stmt = $conn->prepare("
            SELECT idCarritoDetalle, cantidad, precioTotal 
            FROM CARRITO_DETALLE 
            WHERE idCarrito = ? AND idProducto = ?
        ");
        
        $stmt->execute([$idCarrito, $idProducto]);
        $detalle = $stmt->fetch();
        
        if ($detalle) {
            return [
                'encontrado' => true,
                'idDetalle' => $detalle['idCarritoDetalle'],
                'cantidad' => intval($detalle['cantidad']),
                'precioTotal' => floatval($detalle['precioTotal'])
            ];
        } else {
            return ['encontrado' => false];
        }
        
    } catch (Exception $e) {
        error_log("Error en verificarProductoEnCarrito: " . $e->getMessage());
        return ['encontrado' => false, 'error' => $e->getMessage()];
    }
}

// Funciones para uso directo via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !defined('INCLUDED_FROM_CONTROLLER')) {
    header('Content-Type: application/json');
    
    // Verificar si la sesión ya está iniciada antes de llamar session_start()
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $accion = $_POST['accion'] ?? '';
    $idUsuario = $_SESSION['usuario'] ?? $_POST['idUsuario'] ?? '';
    
    if (empty($idUsuario)) {
        echo json_encode(['success' => false, 'message' => 'Usuario no especificado']);
        exit;
    }
    
    switch ($accion) {
        case 'verificar':
            $idCarrito = verificarOCrearCarrito($idUsuario);
            echo json_encode([
                'success' => $idCarrito !== false,
                'idCarrito' => $idCarrito,
                'message' => $idCarrito ? 'Carrito verificado/creado' : 'Error al acceder al carrito'
            ]);
            break;
            
        case 'obtener':
            $resultado = obtenerCarritoUsuario($idUsuario);
            echo json_encode($resultado);
            break;
            
        case 'verificarProducto':
            $idProducto = $_POST['idProducto'] ?? '';
            if (empty($idProducto)) {
                echo json_encode(['success' => false, 'message' => 'Producto no especificado']);
                exit;
            }
            
            $resultado = verificarProductoEnCarrito($idUsuario, $idProducto);
            echo json_encode(['success' => true, 'producto' => $resultado]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
    exit; // Asegurar que no se ejecute código adicional
}
?>
