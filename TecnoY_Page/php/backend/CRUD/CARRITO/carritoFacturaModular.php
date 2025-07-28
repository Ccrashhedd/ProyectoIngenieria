<?php
/**
 * ============================================
 * COORDINADOR DE FACTURACIÓN MODULAR - VERSION FINAL
 * Archivo: carritoFacturaModular.php
 * Ubicación: /php/backend/CRUD/CARRITO/
 * 
 * Descripción: Coordina todo el proceso de facturación usando módulos separados
 * Módulos: facturaCreador.php, detalleFacturaCreador.php, inventarioActualizador.php
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

// Importar los módulos especializados
require_once 'facturaCreador.php';
require_once 'detalleFacturaCreador.php';
require_once 'inventarioActualizador.php';

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
    $logLine = "[$timestamp] [COORDINADOR-MODULAR] $mensaje" . PHP_EOL;
    
    file_put_contents($archivo, $logLine, FILE_APPEND | LOCK_EX);
    error_log("[COORDINADOR-MODULAR] " . $mensaje);
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
// PROCESAMIENTO PRINCIPAL - ARQUITECTURA MODULAR
// ============================================

try {
    logDebug("=== INICIO PROCESAMIENTO MODULAR DE FACTURACIÓN ===");
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
        throw new Exception('Acción no válida. Se esperaba "procesar_pago".');
    }

    // 3. VALIDAR SESIÓN
    if (!isset($_SESSION['usuario'])) {
        throw new Exception('Usuario no autenticado. Debe iniciar sesión para proceder con el pago.');
    }

    $idUsuario = $_SESSION['usuario'];
    logDebug("👤 Usuario autenticado: $idUsuario");

    // 4. CONECTAR A BASE DE DATOS
    $conn = conectarBaseDatos();

    // 5. INICIAR TRANSACCIÓN
    $conn->beginTransaction();
    logDebug("🔄 Transacción iniciada");

    try {
        // 6. CREAR INSTANCIAS DE LOS MÓDULOS
        logDebug("🔧 Creando módulos especializados...");
        $facturaCreador = new FacturaCreador($conn);
        $detalleFacturaCreador = new DetalleFacturaCreador($conn);
        $inventarioActualizador = new InventarioActualizador($conn);
        logDebug("✅ Módulos especializados creados");

        // 7. OBTENER CARRITO DEL USUARIO
        logDebug("🛒 Buscando carrito del usuario...");
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

        // 8. OBTENER PRODUCTOS DEL CARRITO
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

        // 9. VERIFICAR STOCK CON MÓDULO ESPECIALIZADO
        logDebug("📊 Verificando stock con módulo especializado...");
        $verificacionStock = $inventarioActualizador->verificarStock($productosCarrito);
        
        if (!$verificacionStock['success']) {
            $mensaje = "Stock insuficiente para los siguientes productos:\n";
            foreach ($verificacionStock['stockInsuficiente'] as $item) {
                $mensaje .= "• {$item['nombreProducto']}: Disponible {$item['stockActual']}, Solicitado {$item['cantidadSolicitada']}\n";
            }
            throw new Exception($mensaje);
        }

        // 10. CREAR FACTURA PRINCIPAL CON MÓDULO ESPECIALIZADO
        logDebug("🧾 Creando factura principal con módulo...");
        $resultadoFactura = $facturaCreador->crearFactura($idUsuario, $productosCarrito);
        
        if (!$resultadoFactura['success']) {
            throw new Exception("Error al crear factura: " . ($resultadoFactura['error'] ?? 'Error desconocido'));
        }
        
        $idFactura = $resultadoFactura['idFactura'];
        $totales = $resultadoFactura['totales'];
        logDebug("✅ Factura creada: $idFactura");

        // 11. CREAR DETALLES DE FACTURA CON MÓDULO ESPECIALIZADO
        logDebug("📋 Creando detalles de factura con módulo...");
        $resultadoDetalles = $detalleFacturaCreador->crearDetalles($idFactura, $productosCarrito);
        
        if (!$resultadoDetalles['success']) {
            throw new Exception("Error al crear detalles de factura: " . ($resultadoDetalles['error'] ?? 'Error desconocido'));
        }
        
        logDebug("✅ Detalles de factura creados: " . count($resultadoDetalles['detallesCreados']));

        // 12. ACTUALIZAR INVENTARIO CON MÓDULO ESPECIALIZADO
        logDebug("📦 Actualizando inventario con módulo...");
        $resultadoInventario = $inventarioActualizador->actualizarStock($productosCarrito);
        
        if (!$resultadoInventario['success']) {
            throw new Exception("Error al actualizar inventario: " . ($resultadoInventario['error'] ?? 'Error desconocido'));
        }
        
        logDebug("✅ Inventario actualizado: " . count($resultadoInventario['actualizaciones']));

        // 13. LIMPIAR CARRITO
        logDebug("🗑️ Limpiando carrito...");
        $stmtLimpiar = $conn->prepare("DELETE FROM CARRITO_DETALLE WHERE idCarrito = ?");
        $resultLimpiar = $stmtLimpiar->execute([$idCarrito]);
        
        if (!$resultLimpiar) {
            throw new Exception("Error al limpiar el carrito");
        }
        
        logDebug("✅ Carrito limpiado exitosamente");

        // 14. CONFIRMAR TRANSACCIÓN
        $conn->commit();
        logDebug("🎉 Transacción modular completada exitosamente");

        // 15. RESPUESTA EXITOSA CON DATOS COMPLETOS
        enviarRespuesta([
            'success' => true,
            'mensaje' => '¡Pago procesado exitosamente! Su pedido ha sido registrado.',
            'datos' => [
                'idFactura' => $idFactura,
                'totalProductos' => count($productosCarrito),
                'totalItems' => $totales['totalItems'],
                'subtotal' => $totales['subtotal'],
                'impuestos' => $totales['impuestos'],
                'total' => $totales['total'],
                'fecha' => date('Y-m-d'),
                'hora' => date('H:i:s'),
                'detalles' => $resultadoDetalles['detalles'],
                'actualizacionesInventario' => $resultadoInventario['actualizaciones']
            ],
            'redirect' => 'landingPage.php'
        ]);

    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollBack();
        logDebug("❌ Transacción modular revertida: " . $e->getMessage());
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
