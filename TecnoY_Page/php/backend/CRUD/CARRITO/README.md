# 🛒 Sistema de Carrito - Documentación

## Descripción General
Este sistema de carrito permite a los usuarios agregar, actualizar y eliminar productos de manera fácil y segura. Cada usuario tiene un carrito único y persistente.

## Estructura de Archivos

### Backend (`/php/backend/CRUD/CARRITO/`)
- `verificarCarrito.php` - Verifica y crea carritos automáticamente
- `insertCarrito.php` - Agregar productos al carrito
- `updCarrito.php` - Actualizar cantidades de productos
- `elimCarrito.php` - Eliminar productos o vaciar carrito
- `carritoController.php` - Controlador principal (recomendado)

### Frontend (`/JS/`)
- `carritoManager.js` - Clase JavaScript para manejo del carrito

## Características Principales

### ✅ Funcionalidades Implementadas
1. **Un carrito por usuario**: Cada usuario tiene un carrito único
2. **Verificación automática**: Si no existe carrito, se crea automáticamente
3. **Validaciones de stock**: No permite agregar más productos del stock disponible
4. **Cantidad mínima**: No permite cantidades menores a 1
5. **Actualización inteligente**: Si el producto ya existe, suma la cantidad
6. **Eliminación selectiva**: Elimina productos específicos o vacía todo el carrito
7. **Seguridad**: Verifica que el usuario solo modifique su propio carrito

### 🛡️ Validaciones de Seguridad
- Verificación de sesión de usuario
- Validación de permisos (solo el dueño puede modificar su carrito)
- Prevención de inyección SQL con prepared statements
- Validación de datos de entrada
- Control de stock disponible

## Uso desde PHP (Backend)

### Ejemplo 1: Agregar producto al carrito
```php
// POST a insertCarrito.php
$_POST = [
    'idProducto' => 'PROD-001',
    'cantidad' => 2
];
```

### Ejemplo 2: Actualizar cantidad
```php
// POST a updCarrito.php
$_POST = [
    'idCarritoDetalle' => 'CD_USU-0001_PROD-001_1234567890',
    'cantidad' => 5,
    'accion' => 'actualizar' // o 'incrementar', 'decrementar'
];
```

### Ejemplo 3: Eliminar producto
```php
// POST a elimCarrito.php
$_POST = [
    'accion' => 'eliminar_producto',
    'idCarritoDetalle' => 'CD_USU-0001_PROD-001_1234567890'
];
```

### Ejemplo 4: Usar el controlador principal (RECOMENDADO)
```php
// GET para obtener carrito
$url = "carritoController.php?accion=obtener";

// POST para agregar producto
$_POST = [
    'accion' => 'agregar',
    'idProducto' => 'PROD-001',
    'cantidad' => 1
];
```

## Uso desde JavaScript (Frontend)

### Ejemplo 1: Agregar producto
```javascript
// Usando la clase CarritoManager
await carritoManager.agregarProducto('PROD-001', 2);

// O usando fetch directamente
const formData = new FormData();
formData.append('accion', 'agregar');
formData.append('idProducto', 'PROD-001');
formData.append('cantidad', 2);

const response = await fetch('/ProyectoIngenieria/ProyectoIngenieria/TecnoY_Page/php/backend/CRUD/CARRITO/carritoController.php', {
    method: 'POST',
    body: formData
});
```

### Ejemplo 2: Obtener carrito
```javascript
// Usando CarritoManager
const carrito = await carritoManager.obtenerCarrito();
console.log(carrito);

// O usando fetch
const response = await fetch('/ProyectoIngenieria/ProyectoIngenieria/TecnoY_Page/php/backend/CRUD/CARRITO/carritoController.php?accion=obtener');
const data = await response.json();
```

### Ejemplo 3: Actualizar cantidad
```javascript
// Incrementar en 1
await carritoManager.actualizarCantidad('CD_USU-0001_PROD-001_1234567890', null, 'incrementar');

// Establecer cantidad específica
await carritoManager.actualizarCantidad('CD_USU-0001_PROD-001_1234567890', 5, 'actualizar');
```

### Ejemplo 4: Eliminar producto
```javascript
await carritoManager.eliminarProducto('CD_USU-0001_PROD-001_1234567890');
```

### Ejemplo 5: Vaciar carrito
```javascript
await carritoManager.vaciarCarrito();
```

## Integración en Landing Page

### HTML - Botón Agregar al Carrito
```html
<button onclick="agregarAlCarrito('PROD-001', 1)" class="btn-agregar-carrito">
    Agregar al Carrito
</button>

<span id="carrito-contador" class="carrito-contador">0</span>
```

### JavaScript - Función de Agregar
```javascript
async function agregarAlCarrito(idProducto, cantidad = 1) {
    try {
        await carritoManager.agregarProducto(idProducto, cantidad);
        
        // El contador se actualiza automáticamente
        console.log('Producto agregado exitosamente');
        
    } catch (error) {
        console.error('Error al agregar producto:', error);
        alert('Error al agregar producto: ' + error.message);
    }
}
```

### Actualización Automática del Contador
```javascript
// El CarritoManager actualiza automáticamente estos elementos:
// - .carrito-contador, #carrito-contador, .cart-count
// - .carrito-total, #carrito-total, .cart-total

// Para obtener los datos actuales:
const datos = carritoManager.getDatos();
console.log(`Items: ${datos.totalItems}, Total: $${datos.totalCarrito}`);
```

## Respuestas de la API

### Respuesta Exitosa
```json
{
    "success": true,
    "mensaje": "Producto agregado al carrito",
    "producto": {
        "id": "PROD-001",
        "nombre": "Nombre del Producto",
        "precio": 299.99,
        "cantidadAgregada": 2
    },
    "carrito": {
        "totalItems": 5,
        "totalCarrito": 599.98,
        "totalFinal": 641.98
    }
}
```

### Respuesta de Error
```json
{
    "success": false,
    "mensaje": "Stock insuficiente. Solo hay 3 unidades disponibles"
}
```

### Carrito Completo
```json
{
    "success": true,
    "idCarrito": "CART_USU-0001_1234567890",
    "productos": [
        {
            "idDetalle": "CD_USU-0001_PROD-001_1234567890",
            "idProducto": "PROD-001",
            "nombre": "Producto 1",
            "modelo": "Modelo A",
            "marca": "Marca X",
            "categoria": "Categoría Y",
            "precio": 299.99,
            "cantidad": 2,
            "precioTotal": 599.98,
            "stock": 10,
            "imagen": "image/productos/producto1.jpg"
        }
    ],
    "totalItems": 2,
    "totalCarrito": 599.98,
    "impuestos": 41.99,
    "totalFinal": 641.97
}
```

## Notas Importantes

1. **Sesión Requerida**: El usuario debe estar logueado
2. **Stock Automático**: Se verifica automáticamente el stock disponible
3. **Precio Calculado**: Los precios totales se calculan automáticamente
4. **Impuestos**: Se calcula ITBMS del 7% automáticamente
5. **IDs Únicos**: Los IDs se generan automáticamente para evitar conflictos
6. **Persistencia**: El carrito se mantiene entre sesiones
7. **Actualización Automática**: Los contadores visuales se actualizan automáticamente

## Solución de Problemas

### Error 404
- Verificar que las rutas contengan `/ProyectoIngenieria/ProyectoIngenieria/TecnoY_Page/`

### Error de Sesión
- Asegurar que el usuario esté logueado
- Verificar que `$_SESSION['usuario']` esté definida

### Error de Stock
- Verificar que el producto tenga stock disponible
- Revisar la tabla PRODUCTO en la base de datos

### Error de Base de Datos
- Verificar conexión en `conexion.php`
- Asegurar que las tablas CARRITO y CARRITO_DETALLE existan
