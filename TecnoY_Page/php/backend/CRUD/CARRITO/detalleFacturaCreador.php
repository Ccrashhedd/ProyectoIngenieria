<?php
/**
 * ============================================
 * CREADOR DE DETALLES DE FACTURA
 * Archivo: detalleFacturaCreador.php
 * Ubicación: /php/backend/CRUD/CARRITO/
 * 
 * Función: Solo crear los detalles de la factura
 * ============================================
 */

class DetalleFacturaCreador {
    private $conn;
    
    public function __construct($conexion) {
        $this->conn = $conexion;
    }
    
    /**
     * Genera ID único para detalle de factura
     */
    private function generarIdDetalle($idFactura, $indice) {
        return $idFactura . 'D' . str_pad($indice, 3, '0', STR_PAD_LEFT);
    }
    
    /**
     * Crea todos los detalles de una factura
     */
    public function crearDetalles($idFactura, $productosCarrito) {
        try {
            $this->log("📋 Iniciando creación de detalles para factura: $idFactura");
            
            // Preparar statement una sola vez
            $stmt = $this->conn->prepare("
                INSERT INTO DETALLE_FACTURA (idDetalleFactura, idFactura, idProducto, cantidad, precioTotal) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $detallesCreados = [];
            $totalDetalles = count($productosCarrito);
            
            foreach ($productosCarrito as $indice => $item) {
                // Generar ID del detalle
                $idDetalle = $this->generarIdDetalle($idFactura, $indice + 1);
                
                // Validar datos del producto
                if (empty($item['idProducto'])) {
                    throw new Exception("ID de producto vacío en índice $indice");
                }
                
                if (!isset($item['cantidad']) || $item['cantidad'] <= 0) {
                    throw new Exception("Cantidad inválida para producto {$item['idProducto']}");
                }
                
                if (!isset($item['precioTotal']) || $item['precioTotal'] <= 0) {
                    throw new Exception("Precio total inválido para producto {$item['idProducto']}");
                }
                
                // Insertar el detalle
                $resultado = $stmt->execute([
                    $idDetalle,
                    $idFactura,
                    $item['idProducto'],
                    intval($item['cantidad']),
                    floatval($item['precioTotal'])
                ]);
                
                if (!$resultado) {
                    throw new Exception("Error al insertar detalle para producto: {$item['idProducto']}");
                }
                
                $detallesCreados[] = [
                    'idDetalle' => $idDetalle,
                    'idProducto' => $item['idProducto'],
                    'cantidad' => $item['cantidad'],
                    'precioTotal' => $item['precioTotal'],
                    'nombreProducto' => $item['nombProducto'] ?? 'N/A'
                ];
                
                $this->log("✅ Detalle creado: $idDetalle - {$item['idProducto']} x{$item['cantidad']}");
            }
            
            $this->log("✅ Todos los detalles creados exitosamente ($totalDetalles items)");
            
            return [
                'success' => true,
                'detallesCreados' => $detallesCreados,
                'totalDetalles' => $totalDetalles
            ];
            
        } catch (Exception $e) {
            $this->log("❌ Error creando detalles: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Verifica que todos los detalles de una factura existen
     */
    public function verificarDetalles($idFactura) {
        $stmt = $this->conn->prepare("
            SELECT idDetalleFactura, idProducto, cantidad, precioTotal
            FROM DETALLE_FACTURA 
            WHERE idFactura = ?
            ORDER BY idDetalleFactura
        ");
        $stmt->execute([$idFactura]);
        $detalles = $stmt->fetchAll();
        
        $totalDetalles = count($detalles);
        $this->log("📊 Detalles verificados para $idFactura: $totalDetalles registros");
        
        return [
            'detalles' => $detalles,
            'count' => $totalDetalles
        ];
    }
    
    /**
     * Elimina todos los detalles de una factura (para rollback)
     */
    public function eliminarDetalles($idFactura) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM DETALLE_FACTURA WHERE idFactura = ?");
            $resultado = $stmt->execute([$idFactura]);
            $eliminados = $stmt->rowCount();
            
            $this->log("🗑️ Detalles eliminados para rollback: $eliminados registros de factura $idFactura");
            
            return $eliminados;
            
        } catch (Exception $e) {
            $this->log("❌ Error eliminando detalles para rollback: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Log helper
     */
    private function log($mensaje) {
        $archivo = __DIR__ . '/../../../../debug_factura.log';
        $timestamp = date('Y-m-d H:i:s');
        $logLine = "[$timestamp] [DETALLE-CREADOR] $mensaje" . PHP_EOL;
        file_put_contents($archivo, $logLine, FILE_APPEND | LOCK_EX);
    }
}
?>
