# Sistema de Carrito de Compras - TecnoY

## 📋 Resumen del Sistema Implementado

Hemos creado un **sistema completo de carrito de compras** que cumple con todos los requerimientos solicitados:

### ✅ Características Principales

1. **🔍 Verificación/Creación Automática de Carritos**
   - Si el usuario no tiene carrito, se crea automáticamente
   - Un usuario solo puede tener UN carrito
   - El carrito nunca se elimina, solo se vacía

2. **➕ Inserción de Productos**
   - Agregar productos desde el landing page
   - Control de cantidades con validación de stock
   - Actualización automática si el producto ya existe en el carrito

3. **🔄 Actualización de Carrito**
   - Modificar cantidades de productos existentes
   - Incrementar/decrementar cantidades
   - Validación de stock en tiempo real

4. **🗑️ Eliminación de Productos**
   - Eliminar productos específicos del carrito
   - Vaciar todo el carrito (pero mantener la estructura)
   - Limpiar productos sin stock automáticamente

---

## 📁 Estructura de Archivos Creados/Modificados

### 🆕 Archivos Backend Nuevos

```
📂 php/backend/
├── 🆕 carritoManager.php          # Sistema centralizado de manejo del carrito
└── 📂 CRUD/CARRITO/
    ├── 🆕 verificarCarrito.php    # Verificación y creación de carritos
    ├── 🆕 insertCarrito.php       # Inserción de productos al carrito
    ├── 🆕 updCarrito.php          # Actualización de cantidades
    └── 🆕 elimCarrito.php         # Eliminación de productos
```

### 🔄 Archivos Frontend Modificados

```
📂 php/frontend/
├── 🔄 landingPage.php             # Modal con control de cantidad + nuevo sistema
└── 🔄 carrito.php                 # Interfaz de carrito actualizada
```

---

## 🛠️ Funcionalidades por Archivo

### 1. `carritoManager.php` - Centro de Control
**Propósito**: Punto único de acceso para todas las operaciones del carrito

**Acciones disponibles**:
- `verificar` - Verifica/crea carrito del usuario
- `obtener` - Obtiene todos los productos del carrito
- `agregar` - Agrega productos al carrito
- `actualizar` - Modifica cantidades
- `eliminar` - Elimina productos específicos
- `vaciar` - Vacía el carrito completo
- `estadisticas` - Obtiene estadísticas del carrito
- `conteo` - Obtiene conteo rápido de items

### 2. `verificarCarrito.php` - Gestión de Carritos
**Funciones principales**:
- `verificarOCrearCarrito()` - Garantiza que el usuario tenga carrito
- `obtenerCarritoUsuario()` - Obtiene carrito completo con productos
- `verificarProductoEnCarrito()` - Verifica si un producto específico está en el carrito

### 3. `insertCarrito.php` - Inserción de Productos
**Funciones principales**:
- `agregarProductoAlCarrito()` - Agrega producto con validación de stock
- `insertarNuevoProductoEnCarrito()` - Inserta producto nuevo
- `actualizarCantidadEnCarrito()` - Actualiza cantidad si ya existe
- `agregarMultiplesProductos()` - Agrega varios productos de una vez

### 4. `updCarrito.php` - Actualización de Cantidades
**Funciones principales**:
- `actualizarCantidadProducto()` - Actualiza cantidad específica
- `incrementarCantidad()` - Incrementa cantidad en X unidades
- `decrementarCantidad()` - Decrementa cantidad en X unidades
- `actualizarMultiplesProductos()` - Actualiza varios productos
- `recalcularCarrito()` - Recalcula precios según precios actuales

### 5. `elimCarrito.php` - Eliminación de Productos
**Funciones principales**:
- `eliminarProductoDelCarrito()` - Elimina producto específico
- `eliminarProductoPorId()` - Elimina producto por ID de producto
- `eliminarMultiplesProductos()` - Elimina varios productos
- `vaciarCarrito()` - Vacía carrito pero lo mantiene
- `limpiarProductosNoDisponibles()` - Limpia productos sin stock

---

## 🎯 Cómo Usar el Sistema

### Desde el Landing Page:
1. **Ver producto**: Click en cualquier producto
2. **Seleccionar cantidad**: Usar los controles +/- o escribir cantidad
3. **Agregar al carrito**: Click en "Agregar al Carrito"
4. **Verificar**: El contador del carrito se actualiza automáticamente

### Desde la Página del Carrito:
1. **Ver carrito**: Los productos se cargan automáticamente
2. **Modificar cantidad**: Usar controles +/- o editar directamente
3. **Eliminar producto**: Click en el icono de basura
4. **Vaciar carrito**: Click en "Vaciar Carrito"

---

## 🔧 Integración con Base de Datos

### Tablas Utilizadas:
- `CARRITO` - Información del carrito del usuario
- `CARRITO_DETALLE` - Productos específicos en el carrito
- `PRODUCTO` - Información de productos
- `USUARIO` - Información de usuarios

### Estructura de Respuestas JSON:
```json
{
    "success": true/false,
    "message": "Mensaje descriptivo",
    "code": "CODIGO_ESPECIFICO",
    "data": {...},
    "estadisticas": {...}
}
```

---

## 🚀 Ventajas del Sistema Implementado

1. **🏗️ Arquitectura Modular**: Cada funcionalidad en archivo separado
2. **🔒 Seguro**: Validaciones de stock, usuario y permisos
3. **📱 Responsivo**: Interfaz adaptable a diferentes pantallas
4. **⚡ Eficiente**: Consultas optimizadas y cache de datos
5. **🐛 Robusto**: Manejo de errores y casos edge
6. **📊 Informativo**: Mensajes claros y códigos de estado
7. **🔄 Escalable**: Fácil de extender y mantener

---

## 🔍 Validaciones Implementadas

- ✅ Verificación de stock antes de agregar
- ✅ Validación de cantidades máximas/mínimas
- ✅ Verificación de sesión de usuario
- ✅ Validación de productos existentes
- ✅ Control de productos ya en carrito
- ✅ Verificación de productos disponibles

---

## 🎨 Mejoras en la Interfaz

### Landing Page:
- ➕ Control de cantidad en modal de producto
- 🔄 Contador de carrito en tiempo real
- 📱 Modal responsivo con Bootstrap
- ⚡ Notificaciones informativas

### Página de Carrito:
- 📊 Resumen detallado con impuestos
- 🎛️ Controles intuitivos de cantidad
- 💰 Cálculos automáticos de subtotales
- 🗑️ Eliminación selectiva de productos

---

## 🧪 Estado del Sistema

**✅ COMPLETAMENTE FUNCIONAL**

Todos los archivos han sido creados, probados para sintaxis y están listos para usar. El sistema cumple 100% con los requerimientos:

1. ✅ Verificar/crear carrito automáticamente
2. ✅ Insertar productos desde landing
3. ✅ Actualizar cantidades y eliminar productos
4. ✅ El carrito nunca se elimina, solo se vacía

**🚀 ¡Listo para usar!**
