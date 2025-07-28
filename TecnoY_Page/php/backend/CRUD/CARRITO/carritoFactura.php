<?php
/**
 * ============================================
 * SISTEMA DE FACTURACIÓN COMPLETO - VERSIÓN FINAL
 * Archivo: carritoFactura.php
 * Ubicación: /php/backend/CRUD/CARRITO/
 * 
 * Descripción: Procesa el pago del carrito y genera la factura
 * Incluye: subtotal, ITBMS, total en la tabla FACTURA
 * ============================================
 */

// Configuración de errores para desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Limpiar cualquier output previo y configurar buffer
if (ob_get_level()) {
    ob_end_clean();
}
ob_start();

// Inicializar sesión
session_start();

// Headers para respuesta JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

/**
 * Envía respuesta JSON y termina la ejecución
 */
function enviarRespuesta($data, $httpCode = 200) {
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    http_response_code($httpCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}

/**
 * Registra mensajes de debug en archivo de log
 */
function logDebug($mensaje) {
    $archivo = __DIR__ . '/../../../../debug_factura.log';
    $timestamp = date('Y-m-d H:i:s');
    $logLine = "[$timestamp] [FACTURA] $mensaje" . PHP_EOL;
    
    file_put_contents($archivo, $logLine, FILE_APPEND | LOCK_EX);
    error_log("[FACTURA] " . $mensaje);
}

/**
 * Genera un ID único para la factura (optimizado para máximo 40 caracteres)
 */
function generarIdFactura($idUsuario, $conn) {
    $maxIntentos = 5;
    $intento = 0;
    
    do {
        // Formato optimizado: FAC + timestamp(10) + usuario hash(8) + random(4) = ~27 chars
        $timestamp = date('ymdHis'); // 12 caracteres: yymmddhhmmss
        $usuarioHash = substr(md5($idUsuario), 0, 6); // 6 caracteres hash del usuario
        $random = rand(1000, 9999); // 4 dígitos
        
        $idFactura = "FAC{$timestamp}{$usuarioHash}{$random}";
        
        // Verificar que no existe
        $stmt = $conn->prepare("SELECT COUNT(*) FROM FACTURA WHERE idFactura = ?");
        $stmt->execute([$idFactura]);
        $existe = $stmt->fetchColumn() > 0;
        
        $intento++;
        
        if (!$existe) {
            logDebug("✅ ID generado: $idFactura (longitud: " . strlen($idFactura) . ")");
            return $idFactura;
        }
        
        // Esperar un poco antes del siguiente intento
        usleep(10000); // 10ms
        
    } while ($intento < $maxIntentos);
    
    throw new Exception("No se pudo generar un ID único para la factura después de $maxIntentos intentos");
}

/**
 * Conecta a la base de datos con configuración específica
 */
function conectarBaseDatos() {
    try {
        $host = 'localhost';
        $dbname = 'proyectoingenieria';
        $user = 'root';
        $password = '';
        
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
        
        $conn = new PDO($dsn, $user, $password, $options);
        logDebug("✅ Conexión a base de datos establecida");
        
        return $conn;
        
    } catch (PDOException $e) {
        logDebug("❌ Error de conexión: " . $e->getMessage());
        throw new Exception("Error de conexión a la base de datos: " . $e->getMessage());
    }
}

// ============================================
// PROCESAMIENTO PRINCIPAL
// ============================================

try {
    logDebug("=== INICIO PROCESAMIENTO DE FACTURACIÓN ===");
    logDebug("Método: " . $_SERVER['REQUEST_METHOD']);
    logDebug("Sesión usuario: " . ($_SESSION['usuario'] ?? 'No definido'));
    logDebug("POST data: " . json_encode($_POST));

    // 1. VALIDAR MÉTODO DE PETICIÓN
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido. Solo se acepta POST.');
    }

    // 2. VALIDAR ACCIÓN
    $accion = $_POST['accion'] ?? '';
    if ($accion !== 'procesar_pago') {
        throw new Exception('Acción no válida. Se requiere "procesar_pago".');
    }

    // 3. OBTENER Y VALIDAR USUARIO
    $idUsuario = $_SESSION['usuario'] ?? $_POST['idUsuario'] ?? null;
    
    if (empty($idUsuario)) {
        enviarRespuesta([
            'success' => false,
            'mensaje' => 'Usuario no identificado. Debe iniciar sesión para realizar el pago.',
            'debug' => [
                'session_usuario' => isset($_SESSION['usuario']),
                'post_usuario' => isset($_POST['idUsuario']),
                'session_data' => $_SESSION ?? []
            ]
        ], 401);
    }

    logDebug("Usuario identificado: $idUsuario");

    // 4. CONECTAR A BASE DE DATOS
    $conn = conectarBaseDatos();

    // 5. INICIAR TRANSACCIÓN
    $conn->beginTransaction();
    logDebug("🔄 Transacción iniciada");

    try {
        // 6. OBTENER CARRITO DEL USUARIO
        logDebug("📦 Obteniendo carrito del usuario...");
        
        $stmtCarrito = $conn->prepare("
            SELECT idCarrito 
            FROM CARRITO 
            WHERE idUsuario = ?
        ");
        $stmtCarrito->execute([$idUsuario]);
        $carrito = $stmtCarrito->fetch();

        if (!$carrito) {
            throw new Exception('No se encontró un carrito para este usuario.');
        }

        $idCarrito = $carrito['idCarrito'];
        logDebug("✅ Carrito encontrado: $idCarrito");

        // 7. OBTENER PRODUCTOS DEL CARRITO
        logDebug("🛍️ Obteniendo productos del carrito...");
        
        $stmtProductos = $conn->prepare("
            SELECT 
                cd.idCarritoDetalle,
                cd.idProducto,
                cd.cantidad,
                cd.precioTotal,
                p.nombProducto,
                p.precio as precioUnitario,
                p.stock,
                p.modelo
            FROM CARRITO_DETALLE cd
            INNER JOIN PRODUCTO p ON cd.idProducto = p.idProducto
            WHERE cd.idCarrito = ?
            ORDER BY cd.idCarritoDetalle
        ");
        
        $stmtProductos->execute([$idCarrito]);
        $productosCarrito = $stmtProductos->fetchAll();

        if (empty($productosCarrito)) {
            throw new Exception('El carrito está vacío. Agregue productos antes de proceder al pago.');
        }

        logDebug("✅ Productos en carrito: " . count($productosCarrito));

        // 8. VALIDAR STOCK DISPONIBLE
        logDebug("📊 Validando stock disponible...");
        
        foreach ($productosCarrito as $item) {
            if ($item['cantidad'] > $item['stock']) {
                throw new Exception("Stock insuficiente para {$item['nombProducto']}. Stock disponible: {$item['stock']}, solicitado: {$item['cantidad']}");
            }
        }

        logDebug("✅ Stock validado correctamente");

        // 9. CALCULAR TOTALES
        $subtotal = 0;
        $totalItems = 0;
        
        foreach ($productosCarrito as $item) {
            $subtotal += floatval($item['precioTotal']);
            $totalItems += intval($item['cantidad']);
        }
        
        $impuestos = round($subtotal * 0.07, 2); // ITBMS 7%
        $total = round($subtotal + $impuestos, 2);
        
        logDebug("💰 Cálculos: Subtotal=$subtotal, Impuestos=$impuestos, Total=$total, Items=$totalItems");

        // 10. GENERAR ID DE FACTURA
        $idFactura = generarIdFactura($idUsuario, $conn);
        logDebug("📄 ID de factura generado: $idFactura");

        // 11. CREAR REGISTRO DE FACTURA (CON TOTALES)
        logDebug("🧾 Creando factura con totales...");
        logDebug("📄 Datos a insertar: ID=$idFactura, Usuario=$idUsuario, Subtotal=$subtotal, ITBMS=$impuestos, Total=$total");
        
        $stmtFactura = $conn->prepare("
            INSERT INTO FACTURA (idFactura, fecha, hora, idUsuario, subtotal, ITBMS, total) 
            VALUES (?, CURDATE(), CURTIME(), ?, ?, ?, ?)
        ");
        
        $resultFactura = $stmtFactura->execute([$idFactura, $idUsuario, $subtotal, $impuestos, $total]);
        
        if (!$resultFactura) {
            throw new Exception("Error al crear la factura");
        }
        
        logDebug("✅ Factura creada exitosamente con totales - continuando con detalles...");

        // 12. CREAR DETALLES DE FACTURA
        logDebug("📋 Creando detalles de factura...");
        logDebug("📋 Factura padre para detalles: $idFactura");
        
        $stmtDetalle = $conn->prepare("
            INSERT INTO DETALLE_FACTURA (idDetalleFactura, idFactura, idProducto, cantidad, precioTotal) 
            VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($productosCarrito as $index => $item) {
            // Formato optimizado para detalle: idFactura + D + índice
            $idDetalleFactura = $idFactura . 'D' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            
            logDebug("📋 Insertando detalle: $idDetalleFactura para factura: $idFactura");
            
            $resultDetalle = $stmtDetalle->execute([
                $idDetalleFactura,
                $idFactura,
                $item['idProducto'],
                $item['cantidad'],
                $item['precioTotal']
            ]);
            
            if (!$resultDetalle) {
                throw new Exception("Error al crear detalle de factura para producto: " . $item['nombProducto']);
            }
            
            logDebug("✅ Detalle creado: $idDetalleFactura - {$item['nombProducto']}");
        }

        // 13. ACTUALIZAR STOCK DE PRODUCTOS
        logDebug("📦 Actualizando inventario...");
        
        $stmtStock = $conn->prepare("
            UPDATE PRODUCTO 
            SET stock = stock - ? 
            WHERE idProducto = ? AND stock >= ?
        ");

        foreach ($productosCarrito as $item) {
            $resultStock = $stmtStock->execute([
                $item['cantidad'], 
                $item['idProducto'], 
                $item['cantidad']
            ]);
            
            if ($stmtStock->rowCount() === 0) {
                throw new Exception("No se pudo actualizar el stock para: " . $item['nombProducto'] . ". Posible stock insuficiente.");
            }
            
            logDebug("✅ Stock actualizado: {$item['nombProducto']} (-{$item['cantidad']})");
        }

        // 14. LIMPIAR CARRITO
        logDebug("🗑️ Limpiando carrito...");
        
        $stmtLimpiar = $conn->prepare("DELETE FROM CARRITO_DETALLE WHERE idCarrito = ?");
        $resultLimpiar = $stmtLimpiar->execute([$idCarrito]);
        
        if (!$resultLimpiar) {
            throw new Exception("Error al limpiar el carrito");
        }
        
        logDebug("✅ Carrito limpiado exitosamente");

        // 15. CONFIRMAR TRANSACCIÓN
        $conn->commit();
        logDebug("🎉 Transacción completada exitosamente");

        // 16. RESPUESTA EXITOSA
        enviarRespuesta([
            'success' => true,
            'mensaje' => '¡Pago procesado exitosamente! Su pedido ha sido registrado.',
            'datos' => [
                'idFactura' => $idFactura,
                'totalProductos' => count($productosCarrito),
                'totalItems' => $totalItems,
                'subtotal' => $subtotal,
                'impuestos' => $impuestos,
                'total' => $total,
                'fecha' => date('Y-m-d'),
                'hora' => date('H:i:s')
            ],
            'redirect' => 'landingPage.php'
        ]);

    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollBack();
        logDebug("❌ Transacción revertida: " . $e->getMessage());
        throw $e;
    }

} catch (PDOException $e) {
    logDebug("❌ Error de base de datos: " . $e->getMessage());
    logDebug("❌ Código de error: " . $e->getCode());
    logDebug("❌ SQL State: " . $e->errorInfo[0] ?? 'N/A');
    
    // Mensaje específico según el tipo de error
    $mensajeError = 'Error de base de datos durante el procesamiento del pago';
    
    if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
        $mensajeError = 'Error de integridad: problema con las relaciones de la base de datos';
    } elseif (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        $mensajeError = 'Error: ya existe un registro con esos datos';
    } elseif (strpos($e->getMessage(), "Table") !== false && strpos($e->getMessage(), "doesn't exist") !== false) {
        $mensajeError = 'Error: tabla de la base de datos no encontrada';
    }
    
    enviarRespuesta([
        'success' => false,
        'mensaje' => $mensajeError,
        'error_tecnico' => $e->getMessage(),
        'debug' => [
            'tipo' => 'PDOException',
            'codigo' => $e->getCode(),
            'archivo' => basename($e->getFile()),
            'linea' => $e->getLine(),
            'sql_state' => $e->errorInfo[0] ?? 'N/A',
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ], 500);

} catch (Exception $e) {
    logDebug("❌ Error general: " . $e->getMessage());
    
    enviarRespuesta([
        'success' => false,
        'mensaje' => $e->getMessage(),
        'debug' => [
            'tipo' => 'Exception',
            'archivo' => basename($e->getFile()),
            'linea' => $e->getLine(),
            'usuario' => $idUsuario ?? 'N/A',
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ], 400);
}
?>
