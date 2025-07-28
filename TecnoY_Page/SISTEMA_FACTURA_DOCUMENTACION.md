# 📋 DOCUMENTACIÓN DEL SISTEMA DE FACTURACIÓN

## 📁 Archivo: `carritoFactura.php`
**Ubicación:** `/php/backend/CRUD/CARRITO/carritoFactura.php`

## 🎯 Propósito
Este archivo procesa el pago del carrito de compras y genera la factura correspondiente, siguiendo el flujo:
```
CARRITO → FACTURA → DETALLE_FACTURA → ACTUALIZAR STOCK → LIMPIAR CARRITO
```

## 🗃️ Estructura de Base de Datos Utilizada

### Tabla FACTURA
```sql
CREATE TABLE FACTURA (
    idFactura VARCHAR(30) PRIMARY KEY,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    idUsuario VARCHAR(20) NOT NULL,
    FOREIGN KEY (idUsuario) REFERENCES USUARIO(idUsuario)
);
```

### Tabla DETALLE_FACTURA
```sql
CREATE TABLE DETALLE_FACTURA (
    idDetalleFactura VARCHAR(50) PRIMARY KEY,
    idFactura VARCHAR(30) NOT NULL,
    idProducto VARCHAR(30) NOT NULL,
    cantidad INT NOT NULL,
    precioTotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (idFactura) REFERENCES FACTURA(idFactura),
    FOREIGN KEY (idProducto) REFERENCES PRODUCTO(idProducto)
);
```

## 🔄 Flujo de Procesamiento

1. **Validación de Entrada**
   - Verificar método POST
   - Validar acción 'procesar_pago'
   - Verificar usuario autenticado

2. **Obtención de Datos**
   - Conectar a base de datos
   - Obtener carrito del usuario
   - Recuperar productos del carrito
   - Validar stock disponible

3. **Cálculos Financieros**
   - Calcular subtotal
   - Aplicar impuestos (7% ITBMS)
   - Calcular total final

4. **Procesamiento de Transacción**
   - Generar ID único de factura
   - Crear registro en FACTURA
   - Crear registros en DETALLE_FACTURA
   - Actualizar stock de productos
   - Limpiar carrito del usuario

5. **Respuesta**
   - Confirmar transacción
   - Enviar respuesta JSON con detalles

## 🔧 Funciones Principales

### `generarIdFactura($idUsuario, $conn)`
Genera un ID único para la factura con formato:
```
FACT_{idUsuario}_{timestamp}_{random}
```

### `conectarBaseDatos()`
Establece conexión PDO con configuración específica para UTF-8.

### `enviarRespuesta($data, $httpCode)`
Envía respuesta JSON limpia y termina la ejecución.

### `logDebug($mensaje)`
Registra mensajes de debug en archivo de log.

## 📤 Formato de Respuesta

### Respuesta Exitosa
```json
{
    "success": true,
    "mensaje": "¡Pago procesado exitosamente! Su pedido ha sido registrado.",
    "datos": {
        "idFactura": "FACT_usuario_20250728_123456_7890",
        "totalProductos": 3,
        "totalItems": 5,
        "subtotal": 150.00,
        "impuestos": 10.50,
        "total": 160.50,
        "fecha": "2025-07-28",
        "hora": "14:30:15"
    },
    "redirect": "landingPage.php"
}
```

### Respuesta de Error
```json
{
    "success": false,
    "mensaje": "Descripción del error",
    "debug": {
        "tipo": "Exception",
        "archivo": "carritoFactura.php",
        "linea": 123,
        "usuario": "username",
        "timestamp": "2025-07-28 14:30:15"
    }
}
```

## 🔒 Seguridad

- **Transacciones:** Uso de transacciones PDO para atomicidad
- **Validación de Stock:** Verificación antes de procesar
- **SQL Injection:** Prepared statements en todas las consultas
- **Sesiones:** Verificación de usuario autenticado
- **Log de Errores:** Registro detallado para debugging

## 🧪 Pruebas

Para probar el sistema, usar el archivo `test_factura_debug.php` que verifica:
- Conexión a base de datos
- Existencia de tablas
- Estructura de tablas
- Relaciones (Foreign Keys)
- Generación de IDs únicos
- Estado de sesión

## 📊 Relaciones de Tablas

```
USUARIO (1) ──────→ (N) FACTURA
                        │
                        │ (1)
                        ↓
                    (N) DETALLE_FACTURA
                        │
                        │ (N)
                        ↓
                    (1) PRODUCTO
```

## 🚀 Uso desde Frontend

```javascript
const formData = new FormData();
formData.append('accion', 'procesar_pago');

fetch('../backend/CRUD/CARRITO/carritoFactura.php', {
    method: 'POST',
    body: formData
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        // Procesar éxito
        console.log('Factura creada:', data.datos.idFactura);
    } else {
        // Manejar error
        console.error('Error:', data.mensaje);
    }
});
```

## 📝 Logs

Los logs se guardan en:
- `debug_factura.log` (en el directorio del proyecto)
- Error log del sistema (PHP error_log)

Cada entrada incluye timestamp y prefijo `[FACTURA]` para fácil identificación.
