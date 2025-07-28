<?php
/**
 * ============================================
 * SISTEMA DE FACTURACIÓN DEL CARRITO - VERSIÓN NUEVA
 * Archivo: carritoFactura.php
 * Ubicación: /php/backend/CRUD/CARRITO/
 * ============================================
 */

// Configurar reportes de error para desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Limpiar cualquier output previo
if (ob_get_level()) {
    ob_end_clean();
}
ob_start();

// Inicializar sesión
session_start();

// Headers de respuesta JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

/**
 * Función para enviar respuesta JSON limpia
 */
function enviarRespuesta($data, $httpCode = 200) {
    // Limpiar cualquier output pendiente
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Establecer código HTTP
    http_response_code($httpCode);
    
    // Enviar JSON y terminar
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Función para log de debug
 */
function logDebug($mensaje) {
    $archivo = __DIR__ . '/../../../debug.log';
    $timestamp = date('Y-m-d H:i:s');
    $logLine = "[$timestamp] [CARRITO_FACTURA] $mensaje" . PHP_EOL;
    
    // Escribir al archivo
    file_put_contents($archivo, $logLine, FILE_APPEND | LOCK_EX);
    
    // También escribir a error_log como respaldo
    error_log("[CARRITO_FACTURA] " . $mensaje);
}

try {
    logDebug("=== INICIO PROCESAMIENTO DE PAGO ===");
    logDebug("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
    logDebug("POST datos: " . print_r($_POST, true));
    logDebug("SESSION datos: " . print_r($_SESSION, true));

    // ============================================
    // 1. VALIDAR MÉTODO DE PETICIÓN
    // ============================================
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido. Solo se acepta POST.');
    }

    // ============================================
    // 2. CONECTAR A BASE DE DATOS
    // ============================================
    try {
        // Configuración de base de datos (usar nombre correcto)
        $dbname = 'proyectoingenieria';  // Base de datos en minúsculas
        $host = 'localhost';
        $user = 'root';
        $password = '';
        
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $conn = new PDO($dsn, $user, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        logDebug("✅ Conexión a base de datos establecida");
        
    } catch (PDOException $e) {
        logDebug("❌ Error de conexión: " . $e->getMessage());
        throw new Exception('Error de conexión a la base de datos: ' . $e->getMessage());
    }

    // ============================================
    // 3. VERIFICAR USUARIO
    // ============================================
    $idUsuario = $_SESSION['usuario'] ?? $_POST['idUsuario'] ?? '';
    
    // Para testing, usar usuario de prueba si no hay sesión
    if (empty($idUsuario)) {
        $idUsuario = 'test_user';
        logDebug("⚠️ Usando usuario de prueba: $idUsuario");
    }
    
    logDebug("Usuario ID: $idUsuario");

    // ============================================
    // 4. OBTENER CARRITO DEL USUARIO
    // ============================================
    
    // Primero verificar/crear carrito
    $stmtCarrito = $conn->prepare("SELECT idCarrito FROM carrito WHERE idUsuario = ?");
    $stmtCarrito->execute([$idUsuario]);
    $carrito = $stmtCarrito->fetch();
    
    if (!$carrito) {
        // Crear carrito para el usuario
        $idCarrito = 'CART_' . $idUsuario . '_' . time();
        $stmtCrearCarrito = $conn->prepare("INSERT INTO carrito (idCarrito, idUsuario) VALUES (?, ?)");
        $stmtCrearCarrito->execute([$idCarrito, $idUsuario]);
        logDebug("✅ Carrito creado: $idCarrito");
    } else {
        $idCarrito = $carrito['idCarrito'];
        logDebug("✅ Carrito encontrado: $idCarrito");
    }
    
    // Obtener productos del carrito
    $stmtProductos = $conn->prepare("
        SELECT 
            cd.idCarritoDetalle,
            cd.idProducto,
            cd.cantidad,
            cd.precioTotal,
            p.nombProducto,
            p.precio,
            p.stock
        FROM carrito_detalle cd
        JOIN producto p ON cd.idProducto = p.idProducto
        WHERE cd.idCarrito = ?
        ORDER BY cd.idCarritoDetalle
    ");
    
    $stmtProductos->execute([$idCarrito]);
    $productos = $stmtProductos->fetchAll();
    
    if (empty($productos)) {
        throw new Exception('El carrito está vacío. Agregue productos antes de proceder al pago.');
    }
    
    logDebug("✅ Productos en carrito: " . count($productos));

    // ============================================
    // 5. CALCULAR TOTALES
    // ============================================
    $subtotal = 0;
    $totalItems = 0;
    
    foreach ($productos as $producto) {
        $subtotal += floatval($producto['precioTotal']);
        $totalItems += intval($producto['cantidad']);
    }
    
    $impuestos = $subtotal * 0.07; // ITBMS 7%
    $totalFinal = $subtotal + $impuestos;
    
    logDebug("💰 Subtotal: $subtotal, Impuestos: $impuestos, Total: $totalFinal");

    // ============================================
    // 6. GENERAR ID DE FACTURA ÚNICO
    // ============================================
    $intentos = 0;
    do {
        $idFactura = 'FACT_' . $idUsuario . '_' . date('Ymd_His') . '_' . rand(1000, 9999) . '_' . $intentos;
        
        // Verificar que el ID no existe
        $stmtCheck = $conn->prepare("SELECT COUNT(*) as count FROM factura WHERE idFactura = ?");
        $stmtCheck->execute([$idFactura]);
        $exists = $stmtCheck->fetch()['count'] > 0;
        
        $intentos++;
        
        if ($intentos > 10) {
            throw new Exception("No se pudo generar un ID de factura único después de $intentos intentos");
        }
        
    } while ($exists);
    
    logDebug("📄 ID Factura generado (intento $intentos): $idFactura");

        // ============================================
        // 7. INICIAR TRANSACCIÓN (DESHABILITADA TEMPORALMENTE)
        // ============================================
        // $conn->beginTransaction();
        logDebug("🔄 Transacción deshabilitada para debug");    try {
        // ============================================
        // 8. CREAR FACTURA PRIMERO (SIN TRANSACCIÓN COMPLEJA)
        // ============================================
        logDebug("🧾 Creando factura...");
        
        // Generar ID único para la factura
        $idFactura = 'FACT_' . date('Ymd_His') . '_' . $idUsuario;
        logDebug("📄 ID Factura generado: $idFactura");
        
        // Insertar factura primero
        try {
            $stmtFactura = $conn->prepare("
                INSERT INTO factura (idFactura, fecha, hora, idUsuario) 
                VALUES (?, CURDATE(), CURTIME(), ?)
            ");
            
            $resultFactura = $stmtFactura->execute([$idFactura, $idUsuario]);
            logDebug("✅ Factura insertada, resultado: " . ($resultFactura ? 'true' : 'false'));
            
            // Verificar inmediatamente que la factura existe
            $stmtVerificar = $conn->prepare("SELECT idFactura FROM factura WHERE idFactura = ?");
            $stmtVerificar->execute([$idFactura]);
            $facturaExiste = $stmtVerificar->fetch();
            
            if (!$facturaExiste) {
                throw new Exception("Error: La factura no se insertó correctamente");
            }
            
            logDebug("✅ Factura verificada exitosamente: $idFactura");
            
        } catch (PDOException $e) {
            logDebug("❌ Error insertando factura: " . $e->getMessage());
            throw new Exception("Error al crear factura: " . $e->getMessage());
        }

        // ============================================
        // 9. AHORA INSERTAR DETALLES DE FACTURA
        // ============================================
        logDebug("📋 Insertando detalles de factura...");
        
        try {
            $stmtDetalle = $conn->prepare("
                INSERT INTO detalle_factura (idDetalleFactura, idFactura, idProducto, cantidad, precioTotal) 
                VALUES (?, ?, ?, ?, ?)
            ");

            foreach ($productos as $index => $producto) {
                $idDetalleFactura = $idFactura . '_DET_' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
                
                logDebug("📝 Insertando detalle: $idDetalleFactura para producto: " . $producto['idProducto']);
                
                $resultDetalle = $stmtDetalle->execute([
                    $idDetalleFactura,
                    $idFactura,
                    $producto['idProducto'],
                    $producto['cantidad'],
                    $producto['precioTotal']
                ]);
                
                if (!$resultDetalle) {
                    throw new Exception("Error al insertar detalle para producto: " . $producto['idProducto']);
                }
                
                logDebug("✅ Detalle insertado exitosamente: $idDetalleFactura");
            }
            
        } catch (PDOException $e) {
            logDebug("❌ Error insertando detalles: " . $e->getMessage());
            throw new Exception("Error al crear detalles de factura: " . $e->getMessage());
        }

        // ============================================
        // 10. ACTUALIZAR STOCK DE PRODUCTOS
        // ============================================
        logDebug("📦 Actualizando stock de productos...");
        
        try {
            $stmtStock = $conn->prepare("UPDATE producto SET stock = stock - ? WHERE idProducto = ?");
            
            foreach ($productos as $producto) {
                logDebug("📦 Actualizando stock para: " . $producto['idProducto'] . " (cantidad: " . $producto['cantidad'] . ")");
                
                $resultStock = $stmtStock->execute([$producto['cantidad'], $producto['idProducto']]);
                
                if (!$resultStock) {
                    throw new Exception("Error al actualizar stock para producto: " . $producto['idProducto']);
                }
            }
            
            logDebug("✅ Stock actualizado exitosamente");
            
        } catch (PDOException $e) {
            logDebug("❌ Error actualizando stock: " . $e->getMessage());
            throw new Exception("Error al actualizar inventario: " . $e->getMessage());
        }

        // ============================================
        // 11. VACIAR CARRITO DEL USUARIO
        // ============================================
        logDebug("🗑️ Vaciando carrito del usuario...");
        
        try {
            $stmtVaciar = $conn->prepare("DELETE FROM carrito_detalle WHERE idCarrito = ?");
            $resultVaciar = $stmtVaciar->execute([$idCarrito]);
            
            if (!$resultVaciar) {
                throw new Exception("Error al vaciar el carrito");
            }
            
            logDebug("✅ Carrito vaciado exitosamente");
            
        } catch (PDOException $e) {
            logDebug("❌ Error vaciando carrito: " . $e->getMessage());
            throw new Exception("Error al vaciar carrito: " . $e->getMessage());
        }

        // ============================================
        // 12. RESPUESTA EXITOSA
        // ============================================
        logDebug("🎉 Pago procesado exitosamente");
        
        enviarRespuestaJSON([
            'success' => true,
            'mensaje' => 'Pago exitoso, pedido realizado',
            'idFactura' => $idFactura,
            'totalProductos' => count($productos),
            'totalPagado' => $totalCarrito
        ]);

    } catch (Exception $e) {
        // Revertir transacción (DESHABILITADO TEMPORALMENTE)
        // $conn->rollBack();
        logDebug("❌ Error sin transacción: " . $e->getMessage());
        throw $e;
    }

} catch (PDOException $e) {
    // Error de base de datos
    logDebug("❌ Error PDO: " . $e->getMessage());
    
    // Sin transacciones temporalmente
    // if (isset($conn) && $conn->inTransaction()) {
    //     $conn->rollBack();
    // }
    
    enviarRespuesta([
        'success' => false,
        'mensaje' => 'Error de base de datos durante el procesamiento del pago',
        'error' => $e->getMessage(),
        'debug' => [
            'tipo' => 'PDOException',
            'archivo' => $e->getFile(),
            'linea' => $e->getLine()
        ]
    ], 500);

} catch (Exception $e) {
    // Error general
    logDebug("❌ Error general: " . $e->getMessage());
    
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    enviarRespuesta([
        'success' => false,
        'mensaje' => $e->getMessage(),
        'debug' => [
            'tipo' => 'Exception',
            'archivo' => $e->getFile(),
            'linea' => $e->getLine(),
            'usuario' => $idUsuario ?? 'N/A',
            'session' => $_SESSION ?? [],
            'post' => $_POST ?? []
        ]
    ], 400);
}

logDebug("=== FIN PROCESAMIENTO ===");
?>
