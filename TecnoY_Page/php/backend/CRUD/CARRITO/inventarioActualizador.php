<?php
/**
 * ============================================
 * ACTUALIZADOR DE INVENTARIO
 * Archivo: inventarioActualizador.php
 * Ubicación: /php/backend/CRUD/CARRITO/
 * 
 * Función: Solo actualizar el inventario/stock de productos
 * ============================================
 */

class InventarioActualizador {
    private $conn;
    
    public function __construct($conexion) {
        $this->conn = $conexion;
    }
    
    /**
     * Verifica que hay stock suficiente para todos los productos
     */
    public function verificarStock($productosCarrito) {
        try {
            $this->log("📊 Verificando stock para " . count($productosCarrito) . " productos");
            
            $stockInsuficiente = [];
            
            foreach ($productosCarrito as $item) {
                $stmt = $this->conn->prepare("SELECT stock, nombProducto FROM PRODUCTO WHERE idProducto = ?");
                $stmt->execute([$item['idProducto']]);
                $producto = $stmt->fetch();
                
                if (!$producto) {
                    throw new Exception("Producto no encontrado: {$item['idProducto']}");
                }
                
                $stockActual = intval($producto['stock']);
                $cantidadSolicitada = intval($item['cantidad']);
                
                if ($stockActual < $cantidadSolicitada) {
                    $stockInsuficiente[] = [
                        'idProducto' => $item['idProducto'],
                        'nombreProducto' => $producto['nombProducto'],
                        'stockActual' => $stockActual,
                        'cantidadSolicitada' => $cantidadSolicitada,
                        'faltante' => $cantidadSolicitada - $stockActual
                    ];
                }
                
                $this->log("📦 {$producto['nombProducto']}: Stock={$stockActual}, Solicitado={$cantidadSolicitada}");
            }
            
            if (!empty($stockInsuficiente)) {
                $this->log("❌ Stock insuficiente encontrado para " . count($stockInsuficiente) . " productos");
                return [
                    'success' => false,
                    'stockInsuficiente' => $stockInsuficiente
                ];
            }
            
            $this->log("✅ Stock verificado correctamente para todos los productos");
            return ['success' => true];
            
        } catch (Exception $e) {
            $this->log("❌ Error verificando stock: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Actualiza el stock de todos los productos
     */
    public function actualizarStock($productosCarrito) {
        try {
            $this->log("🔄 Iniciando actualización de inventario");
            
            // Preparar statement una sola vez
            $stmt = $this->conn->prepare("
                UPDATE PRODUCTO 
                SET stock = stock - ? 
                WHERE idProducto = ? AND stock >= ?
            ");
            
            $actualizaciones = [];
            $stockAnterior = [];
            
            foreach ($productosCarrito as $item) {
                $idProducto = $item['idProducto'];
                $cantidad = intval($item['cantidad']);
                
                // Obtener stock actual antes de actualizar
                $stmtStock = $this->conn->prepare("SELECT stock, nombProducto FROM PRODUCTO WHERE idProducto = ?");
                $stmtStock->execute([$idProducto]);
                $productoActual = $stmtStock->fetch();
                
                if (!$productoActual) {
                    throw new Exception("Producto no encontrado al actualizar: $idProducto");
                }
                
                $stockAntes = intval($productoActual['stock']);
                
                // Realizar la actualización
                $resultado = $stmt->execute([$cantidad, $idProducto, $cantidad]);
                $filasAfectadas = $stmt->rowCount();
                
                if ($filasAfectadas === 0) {
                    throw new Exception("No se pudo actualizar el stock para: {$productoActual['nombProducto']}. Posible stock insuficiente.");
                }
                
                // Verificar stock después de la actualización
                $stmtVerificar = $this->conn->prepare("SELECT stock FROM PRODUCTO WHERE idProducto = ?");
                $stmtVerificar->execute([$idProducto]);
                $stockDespues = intval($stmtVerificar->fetchColumn());
                
                $actualizaciones[] = [
                    'idProducto' => $idProducto,
                    'nombreProducto' => $productoActual['nombProducto'],
                    'cantidadDescontada' => $cantidad,
                    'stockAntes' => $stockAntes,
                    'stockDespues' => $stockDespues
                ];
                
                // Guardar para posible rollback
                $stockAnterior[$idProducto] = $stockAntes;
                
                $this->log("✅ Stock actualizado: {$productoActual['nombProducto']} - {$stockAntes} → {$stockDespues} (-{$cantidad})");
            }
            
            $this->log("✅ Inventario actualizado exitosamente (" . count($actualizaciones) . " productos)");
            
            return [
                'success' => true,
                'actualizaciones' => $actualizaciones,
                'stockAnterior' => $stockAnterior
            ];
            
        } catch (Exception $e) {
            $this->log("❌ Error actualizando inventario: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Revierte las actualizaciones de stock (para rollback)
     */
    public function revertirStock($stockAnterior) {
        try {
            $this->log("🔄 Iniciando rollback de inventario");
            
            $stmt = $this->conn->prepare("UPDATE PRODUCTO SET stock = ? WHERE idProducto = ?");
            $revertidos = 0;
            
            foreach ($stockAnterior as $idProducto => $stockOriginal) {
                $resultado = $stmt->execute([$stockOriginal, $idProducto]);
                if ($resultado && $stmt->rowCount() > 0) {
                    $revertidos++;
                    $this->log("↩️ Stock revertido: $idProducto → $stockOriginal");
                }
            }
            
            $this->log("✅ Rollback completado: $revertidos productos revertidos");
            
            return $revertidos;
            
        } catch (Exception $e) {
            $this->log("❌ Error en rollback de inventario: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Obtiene el estado actual del inventario para productos específicos
     */
    public function obtenerEstadoInventario($idsProductos) {
        try {
            $placeholders = str_repeat('?,', count($idsProductos) - 1) . '?';
            $stmt = $this->conn->prepare("
                SELECT idProducto, nombProducto, stock 
                FROM PRODUCTO 
                WHERE idProducto IN ($placeholders)
                ORDER BY nombProducto
            ");
            $stmt->execute($idsProductos);
            $inventario = $stmt->fetchAll();
            
            $this->log("📊 Estado de inventario obtenido para " . count($inventario) . " productos");
            
            return $inventario;
            
        } catch (Exception $e) {
            $this->log("❌ Error obteniendo estado de inventario: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Log helper
     */
    private function log($mensaje) {
        $archivo = __DIR__ . '/../../../../debug_factura.log';
        $timestamp = date('Y-m-d H:i:s');
        $logLine = "[$timestamp] [INVENTARIO-ACTUALIZADOR] $mensaje" . PHP_EOL;
        file_put_contents($archivo, $logLine, FILE_APPEND | LOCK_EX);
    }
}
?>
