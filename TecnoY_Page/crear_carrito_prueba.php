<?php
/**
 * Script para agregar productos de prueba al carrito
 */

try {
    // Conectar a base de datos
    $dbname = 'proyectoingenieria';
    $host = 'localhost';
    $user = 'root';
    $password = '';
    
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $conn = new PDO($dsn, $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conectado a la base de datos\n";
    
    $idUsuario = 'test_user';
    
    // Verificar si hay productos en la base de datos
    $stmtProductos = $conn->prepare("SELECT idProducto, nombProducto, precio, stock FROM producto LIMIT 3");
    $stmtProductos->execute();
    $productos = $stmtProductos->fetchAll();
    
    if (empty($productos)) {
        echo "❌ No hay productos en la base de datos\n";
        exit(1);
    }
    
    echo "✅ Productos encontrados: " . count($productos) . "\n";
    
    // Crear carrito si no existe
    $idCarrito = 'CART_' . $idUsuario . '_' . time();
    
    try {
        $stmtCarrito = $conn->prepare("INSERT INTO carrito (idCarrito, idUsuario) VALUES (?, ?)");
        $stmtCarrito->execute([$idCarrito, $idUsuario]);
        echo "✅ Carrito creado: $idCarrito\n";
    } catch (Exception $e) {
        echo "⚠️ Error creando carrito (puede que ya exista): " . $e->getMessage() . "\n";
        
        // Obtener carrito existente
        $stmtGetCarrito = $conn->prepare("SELECT idCarrito FROM carrito WHERE idUsuario = ?");
        $stmtGetCarrito->execute([$idUsuario]);
        $carritoExistente = $stmtGetCarrito->fetch();
        
        if ($carritoExistente) {
            $idCarrito = $carritoExistente['idCarrito'];
            echo "✅ Usando carrito existente: $idCarrito\n";
        }
    }
    
    // Limpiar carrito anterior
    $stmtLimpiar = $conn->prepare("DELETE FROM carrito_detalle WHERE idCarrito = ?");
    $stmtLimpiar->execute([$idCarrito]);
    
    // Agregar productos al carrito
    $stmtAgregar = $conn->prepare("
        INSERT INTO carrito_detalle (idCarritoDetalle, idCarrito, idProducto, cantidad, precioTotal) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    foreach ($productos as $index => $producto) {
        $cantidad = rand(1, 3);
        $precioTotal = $producto['precio'] * $cantidad;
        $idDetalle = $idCarrito . '_ITEM_' . ($index + 1);
        
        $stmtAgregar->execute([
            $idDetalle,
            $idCarrito,
            $producto['idProducto'],
            $cantidad,
            $precioTotal
        ]);
        
        echo "✅ Agregado: {$producto['nombProducto']} x$cantidad = $$precioTotal\n";
    }
    
    echo "\n🎉 Carrito de prueba creado exitosamente!\n";
    echo "Usuario: $idUsuario\n";
    echo "Carrito: $idCarrito\n";
    echo "Productos: " . count($productos) . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
