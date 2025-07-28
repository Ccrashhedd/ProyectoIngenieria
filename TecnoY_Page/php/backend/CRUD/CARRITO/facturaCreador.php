<?php
/**
 * ============================================
 * CREADOR DE FACTURA PRINCIPAL
 * Archivo: facturaCreador.php
 * Ubicación: /php/backend/CRUD/CARRITO/
 * 
 * Función: Solo crear el registro principal de la factura
 * ============================================
 */

class FacturaCreador {
    private $conn;
    
    public function __construct($conexion) {
        $this->conn = $conexion;
    }
    
    /**
     * Genera un ID único para la factura
     */
    private function generarIdFactura($idUsuario) {
        $maxIntentos = 5;
        $intento = 0;
        
        do {
            // Formato: FAC + fecha(8) + usuario hash(6) + random(4) = ~22 chars
            $fecha = date('ymdHis'); // 12 caracteres
            $usuarioHash = substr(md5($idUsuario), 0, 4); // 4 caracteres
            $random = rand(100, 999); // 3 dígitos
            
            $idFactura = "FAC{$fecha}{$usuarioHash}{$random}";
            
            // Verificar que no existe
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM FACTURA WHERE idFactura = ?");
            $stmt->execute([$idFactura]);
            $existe = $stmt->fetchColumn() > 0;
            
            $intento++;
            
            if (!$existe) {
                $this->log("✅ ID de factura generado: $idFactura");
                return $idFactura;
            }
            
            usleep(5000); // 5ms de espera
            
        } while ($intento < $maxIntentos);
        
        throw new Exception("No se pudo generar un ID único para la factura");
    }
    
    /**
     * Calcula los totales de la factura
     */
    public function calcularTotales($productosCarrito) {
        $subtotal = 0;
        $totalItems = 0;
        
        foreach ($productosCarrito as $item) {
            $subtotal += floatval($item['precioTotal']);
            $totalItems += intval($item['cantidad']);
        }
        
        $impuestos = round($subtotal * 0.07, 2); // ITBMS 7%
        $total = round($subtotal + $impuestos, 2);
        
        $this->log("💰 Totales calculados - Subtotal: $subtotal, ITBMS: $impuestos, Total: $total");
        
        return [
            'subtotal' => $subtotal,
            'impuestos' => $impuestos,
            'total' => $total,
            'totalItems' => $totalItems
        ];
    }
    
    /**
     * Crea la factura principal
     */
    public function crearFactura($idUsuario, $productosCarrito) {
        try {
            // Calcular totales primero
            $totales = $this->calcularTotales($productosCarrito);
            
            // Generar ID único
            $idFactura = $this->generarIdFactura($idUsuario);
            
            // Crear el registro de factura
            $stmt = $this->conn->prepare("
                INSERT INTO FACTURA (idFactura, fecha, hora, idUsuario, subtotal, ITBMS, total) 
                VALUES (?, CURDATE(), CURTIME(), ?, ?, ?, ?)
            ");
            
            $resultado = $stmt->execute([
                $idFactura, 
                $idUsuario, 
                $totales['subtotal'], 
                $totales['impuestos'], 
                $totales['total']
            ]);
            
            if (!$resultado) {
                throw new Exception("Error al insertar la factura en la base de datos");
            }
            
            $this->log("✅ Factura creada exitosamente: $idFactura");
            
            return [
                'success' => true,
                'idFactura' => $idFactura,
                'totales' => $totales,
                'fecha' => date('Y-m-d'),
                'hora' => date('H:i:s')
            ];
            
        } catch (Exception $e) {
            $this->log("❌ Error creando factura: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Verifica que una factura existe
     */
    public function verificarFactura($idFactura) {
        $stmt = $this->conn->prepare("SELECT idFactura, total FROM FACTURA WHERE idFactura = ?");
        $stmt->execute([$idFactura]);
        $factura = $stmt->fetch();
        
        if ($factura) {
            $this->log("✅ Factura verificada: $idFactura");
            return $factura;
        } else {
            $this->log("❌ Factura no encontrada: $idFactura");
            return false;
        }
    }
    
    /**
     * Log helper
     */
    private function log($mensaje) {
        $archivo = __DIR__ . '/../../../../debug_factura.log';
        $timestamp = date('Y-m-d H:i:s');
        $logLine = "[$timestamp] [FACTURA-CREADOR] $mensaje" . PHP_EOL;
        file_put_contents($archivo, $logLine, FILE_APPEND | LOCK_EX);
    }
}
?>
