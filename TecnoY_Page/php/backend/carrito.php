<?php
session_start();
include 'CONEXION/conexion.php';

header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

switch ($accion) {
    case 'agregar':
        agregarAlCarrito();
        break;
    case 'obtener':
        obtenerCarrito();
        break;
    case 'actualizar':
        actualizarCantidad();
        break;
    case 'eliminar':
        eliminarDelCarrito();
        break;
    case 'vaciar':
        vaciarCarrito();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
}

function agregarAlCarrito() {
    global $conn;
    
    $producto_id = intval($_POST['producto_id']);
    $cantidad = intval($_POST['cantidad'] ?? 1);
    
    // Verificar que el producto existe y tiene stock suficiente
    $stmt = $conn->prepare("SELECT id, nombre, precio, stock FROM productos WHERE id = ?");
    $stmt->bind_param("i", $producto_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
        return;
    }
    
    $producto = $resultado->fetch_assoc();
    
    if ($producto['stock'] < $cantidad) {
        echo json_encode(['success' => false, 'message' => 'Stock insuficiente. Stock disponible: ' . $producto['stock']]);
        return;
    }
    
    $usuario_id = $_SESSION['usuario_id'] ?? null;
    $session_id = session_id();
    
    try {
        // Verificar si el producto ya está en el carrito
        if ($usuario_id) {
            $stmt = $conn->prepare("SELECT id, cantidad FROM carrito WHERE usuario_id = ? AND producto_id = ?");
            $stmt->bind_param("ii", $usuario_id, $producto_id);
        } else {
            $stmt = $conn->prepare("SELECT id, cantidad FROM carrito WHERE session_id = ? AND producto_id = ?");
            $stmt->bind_param("si", $session_id, $producto_id);
        }
        
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            // Producto ya existe en carrito - actualizar cantidad
            $item = $resultado->fetch_assoc();
            $nueva_cantidad = $item['cantidad'] + $cantidad;
            
            // Verificar si hay stock suficiente para la nueva cantidad
            // Necesitamos sumar la cantidad actual más la nueva
            $stock_necesario = $cantidad; // Solo la diferencia
            
            if ($producto['stock'] < $stock_necesario) {
                echo json_encode(['success' => false, 'message' => 'Stock insuficiente para agregar más unidades']);
                return;
            }
            
            $stmt = $conn->prepare("UPDATE carrito SET cantidad = cantidad + ? WHERE id = ?");
            $stmt->bind_param("ii", $cantidad, $item['id']);
            
        } else {
            // Agregar nuevo item al carrito
            if ($usuario_id) {
                $stmt = $conn->prepare("INSERT INTO carrito (usuario_id, producto_id, cantidad) VALUES (?, ?, ?)");
                $stmt->bind_param("iii", $usuario_id, $producto_id, $cantidad);
            } else {
                $stmt = $conn->prepare("INSERT INTO carrito (session_id, producto_id, cantidad) VALUES (?, ?, ?)");
                $stmt->bind_param("sii", $session_id, $producto_id, $cantidad);
            }
        }
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'message' => 'Producto agregado al carrito',
                'producto' => $producto['nombre'],
                'cantidad' => $cantidad
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al agregar al carrito: ' . $conn->error]);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function obtenerCarrito() {
    global $conn;
    
    $usuario_id = $_SESSION['usuario_id'] ?? null;
    $session_id = session_id();
    
    try {
        if ($usuario_id) {
            $stmt = $conn->prepare("
                SELECT c.id, c.cantidad, 
                       p.id as producto_id, p.nombre, p.descripcion, p.imagen, p.stock, p.precio,
                       (c.cantidad * p.precio) as subtotal
                FROM carrito c 
                JOIN productos p ON c.producto_id = p.id 
                WHERE c.usuario_id = ?
                ORDER BY c.fecha_agregado DESC
            ");
            $stmt->bind_param("i", $usuario_id);
        } else {
            $stmt = $conn->prepare("
                SELECT c.id, c.cantidad, 
                       p.id as producto_id, p.nombre, p.descripcion, p.imagen, p.stock, p.precio,
                       (c.cantidad * p.precio) as subtotal
                FROM carrito c 
                JOIN productos p ON c.producto_id = p.id 
                WHERE c.session_id = ?
                ORDER BY c.fecha_agregado DESC
            ");
            $stmt->bind_param("s", $session_id);
        }
        
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $carrito = [];
        $total = 0;
        
        while ($row = $resultado->fetch_assoc()) {
            $carrito[] = $row;
            $total += $row['subtotal'];
        }
        
        echo json_encode([
            'success' => true,
            'carrito' => $carrito,
            'total' => number_format($total, 2, '.', ''),
            'count' => count($carrito)
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function actualizarCantidad() {
    global $conn;
    
    $carrito_id = intval($_POST['carrito_id']);
    $nueva_cantidad = intval($_POST['cantidad']);
    
    if ($nueva_cantidad <= 0) {
        eliminarDelCarrito($carrito_id);
        return;
    }
    
    try {
        // Obtener información actual del item y producto
        $stmt = $conn->prepare("
            SELECT c.cantidad as cantidad_actual, c.producto_id, p.stock, p.nombre
            FROM carrito c 
            JOIN productos p ON c.producto_id = p.id 
            WHERE c.id = ?
        ");
        $stmt->bind_param("i", $carrito_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Item no encontrado en el carrito']);
            return;
        }
        
        $item = $resultado->fetch_assoc();
        $cantidad_actual = $item['cantidad_actual'];
        $stock_disponible = $item['stock'];
        $producto_nombre = $item['nombre'];
        
        // Calcular la diferencia de cantidad
        $diferencia = $nueva_cantidad - $cantidad_actual;
        
        // Si aumentamos la cantidad, verificar si hay stock suficiente
        if ($diferencia > 0 && $stock_disponible < $diferencia) {
            echo json_encode([
                'success' => false, 
                'message' => "Stock insuficiente. Solo hay {$stock_disponible} unidades disponibles"
            ]);
            return;
        }
        
        // Actualizar cantidad en carrito (el trigger se encarga del stock)
        $stmt = $conn->prepare("UPDATE carrito SET cantidad = ? WHERE id = ?");
        $stmt->bind_param("ii", $nueva_cantidad, $carrito_id);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'message' => "Cantidad de {$producto_nombre} actualizada a {$nueva_cantidad}",
                'nueva_cantidad' => $nueva_cantidad
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar cantidad: ' . $conn->error]);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function eliminarDelCarrito($carrito_id = null) {
    global $conn;
    
    if ($carrito_id === null) {
        $carrito_id = intval($_POST['carrito_id']);
    }
    
    try {
        // Obtener nombre del producto antes de eliminar
        $stmt = $conn->prepare("
            SELECT p.nombre 
            FROM carrito c 
            JOIN productos p ON c.producto_id = p.id 
            WHERE c.id = ?
        ");
        $stmt->bind_param("i", $carrito_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Item no encontrado en el carrito']);
            return;
        }
        
        $producto_nombre = $resultado->fetch_assoc()['nombre'];
        
        // Eliminar del carrito (el trigger restaura el stock automáticamente)
        $stmt = $conn->prepare("DELETE FROM carrito WHERE id = ?");
        $stmt->bind_param("i", $carrito_id);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'message' => "{$producto_nombre} eliminado del carrito"
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar del carrito: ' . $conn->error]);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function vaciarCarrito() {
    global $conn;
    
    $usuario_id = $_SESSION['usuario_id'] ?? null;
    $session_id = session_id();
    
    try {
        // Los triggers se encargarán de restaurar el stock automáticamente
        if ($usuario_id) {
            $stmt = $conn->prepare("DELETE FROM carrito WHERE usuario_id = ?");
            $stmt->bind_param("i", $usuario_id);
        } else {
            $stmt = $conn->prepare("DELETE FROM carrito WHERE session_id = ?");
            $stmt->bind_param("s", $session_id);
        }
        
        if ($stmt->execute()) {
            $items_eliminados = $stmt->affected_rows;
            echo json_encode([
                'success' => true, 
                'message' => "Carrito vaciado. {$items_eliminados} productos eliminados"
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al vaciar carrito: ' . $conn->error]);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
?>
