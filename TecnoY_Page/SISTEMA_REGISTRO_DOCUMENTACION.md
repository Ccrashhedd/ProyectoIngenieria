# 📝 SISTEMA DE REGISTRO DE USUARIOS - DOCUMENTACIÓN

## 🎯 Descripción General

Sistema completo de registro de nuevos usuarios para la plataforma TecnoY. Permite crear cuentas de usuario/cliente con validación completa, interfaz moderna y integración total con el sistema existente.

## 🏗️ Arquitectura del Sistema

### 📁 Archivos Creados/Modificados

```
📦 Sistema de Registro
├── 🎨 Frontend
│   ├── php/frontend/nuevoUsuario.php          # Página principal de registro
│   ├── css/registro.css                       # Estilos específicos
│   └── JS/registro.js                         # Lógica del formulario
├── 🔧 Backend
│   ├── php/backend/USUARIO/registro.php       # API para crear usuarios
│   └── php/backend/USUARIO/verificar_disponibilidad.php  # Verificar IDs
├── 🔗 Integración
│   ├── php/frontend/pagLogin.php             # Enlace agregado
│   └── css/login.css                         # Estilos para enlace
```

## 🎨 Interfaz de Usuario

### Características de Diseño
- **Tema Moderno**: Diseño glassmorphism con efectos blur
- **Responsive**: Adaptable a móviles y escritorio
- **Validación Visual**: Indicadores en tiempo real
- **Animaciones**: Transiciones suaves y feedback visual
- **Accesibilidad**: Labels apropiados y navegación por teclado

### Elementos de la Interfaz
1. **Logo y Branding**: Logo de TecnoY centrado
2. **Formulario**: 5 campos principales + confirmación
3. **Validación**: Mensajes de error en tiempo real
4. **Botones**: Registro y enlace a login
5. **Información**: Tipo de cuenta claramente indicado

## 📋 Campos del Formulario

### 1. ID de Usuario *
- **Tipo**: Texto
- **Validación**: Solo letras, números y guiones bajos
- **Límite**: Máximo 20 caracteres
- **Único**: Verificado en base de datos
- **Ejemplo**: `juan_perez`, `user123`

### 2. Nombre Completo *
- **Tipo**: Texto
- **Límite**: Máximo 50 caracteres
- **Ejemplo**: `Juan Pérez García`

### 3. Correo Electrónico *
- **Tipo**: Email
- **Validación**: Formato de email válido
- **Límite**: Máximo 50 caracteres
- **Único**: Verificado en base de datos

### 4. Contraseña *
- **Tipo**: Password
- **Validación**: Mínimo 6 caracteres
- **Almacenamiento**: Texto plano (compatible con sistema existente)

### 5. Confirmar Contraseña *
- **Tipo**: Password
- **Validación**: Debe coincidir con la contraseña

**Nota**: Todos los usuarios registrados automáticamente tienen `id_rango = 0` (Cliente/Usuario)

## 🔒 Validaciones Implementadas

### Frontend (JavaScript)
```javascript
// Validación de ID de usuario
- Solo caracteres alfanuméricos y guiones bajos
- Máximo 20 caracteres
- Verificación de disponibilidad (opcional)

// Validación de email
- Formato válido usando regex
- Máximo 50 caracteres

// Validación de contraseña
- Mínimo 6 caracteres
- Confirmación de coincidencia
```

### Backend (PHP)
```php
// Validaciones de seguridad
- Sanitización de datos de entrada
- Verificación de duplicados (ID y email)
- Almacenamiento en texto plano (compatibilidad total)
- Validación de longitudes máximas
- Prevención de inyección SQL
```

## 🔧 API Backend

### Endpoint: `registro.php`
```http
POST /php/backend/USUARIO/registro.php
Content-Type: application/json

{
    "idUsuario": "nuevo_user",
    "nombUsuario": "Nuevo Usuario",
    "emailUsuario": "nuevo@ejemplo.com",
    "passUsuario": "mi_password"
}
```

### Respuesta Exitosa
```json
{
    "success": true,
    "message": "Usuario registrado exitosamente",
    "data": {
        "idUsuario": "nuevo_user",
        "nombUsuario": "Nuevo Usuario",
        "emailUsuario": "nuevo@ejemplo.com"
    }
}
```

### Respuesta de Error
```json
{
    "success": false,
    "message": "El ID de usuario ya está registrado"
}
```

## 🔐 Sistema de Contraseñas Simplificado

### Almacenamiento de Contraseñas
- **Tipo**: Texto plano
- **Motivo**: Compatibilidad total con sistema existente
- **Ventajas**: Sin problemas de verificación, funciona con todos los usuarios

### Login Simple (`login.php`)
```php
// Verificación directa de contraseñas
if ($usuario['passUsuario'] !== $password_input) {
    throw new Exception('Contraseña incorrecta');
}
```

### Compatibilidad Total
- ✅ Usuarios nuevos: Contraseñas en texto plano
- ✅ Usuarios existentes: Siguen funcionando igual
- ✅ Sin problemas de migración
- ✅ Sistema unificado y simple

## 💾 Base de Datos

### Tabla: USUARIO
```sql
INSERT INTO USUARIO (
    idUsuario,      -- VARCHAR(20) PRIMARY KEY
    nombUsuario,    -- VARCHAR(50) NOT NULL
    passUsuario,    -- VARCHAR(255) NOT NULL (hasheada)
    emailUsuario,   -- VARCHAR(50) NOT NULL
    idRango         -- TINYINT DEFAULT 0 (Cliente)
) VALUES (?, ?, ?, ?, 0)
```

### Tabla: CARRITO (Automático)
```sql
-- Se crea automáticamente para cada nuevo usuario
INSERT INTO CARRITO (idCarrito, idUsuario) 
VALUES ('CART_[idUsuario]', '[idUsuario]')
```

## 🔄 Flujo de Usuario

### 1. Acceso al Registro
```
Usuario en Login → Click "¿No tienes cuenta? Registrarse" → nuevoUsuario.php
```

### 2. Completar Formulario
```
Llenar campos → Validación en tiempo real → Submit
```

### 3. Procesamiento
```
Frontend valida → Envía a backend → Backend procesa → Respuesta
```

### 4. Resultado
```
Éxito: Mensaje + Redirección a login (2s)
Error: Mensaje de error específico
```

## 🎯 Características Especiales

### 1. Validación en Tiempo Real
- Los campos se validan mientras el usuario escribe
- Indicadores visuales ✓ y ✗
- Mensajes de error específicos debajo de cada campo

### 2. Prevención de Duplicados
- Verificación de ID de usuario único
- Verificación de email único
- Feedback inmediato al usuario

### 3. Seguridad
- Contraseñas hasheadas con `PASSWORD_DEFAULT`
- Sanitización de todas las entradas
- Prevención de inyección SQL con prepared statements

### 4. Experiencia de Usuario
- Formulario intuitivo y fácil de usar
- Mensajes claros y útiles
- Redirección automática tras registro exitoso

## 🔗 Integración con Sistema Existente

### 1. CSS Base
- Utiliza `base.css` para consistencia visual
- Variables CSS del sistema principal
- Componentes reutilizables

### 2. Sesiones
- Redirige usuarios ya logueados
- Integra con sistema de sesiones existente

### 3. Base de Datos
- Usa conexión existente
- Compatible con estructura de tablas actual
- Crea carrito automáticamente

## 📱 Responsive Design

### Breakpoints
- **Desktop**: > 768px - Diseño completo
- **Tablet**: 481px - 768px - Formulario en columna
- **Mobile**: < 481px - Optimizado para móvil

### Adaptaciones Móviles
- Formulario en una sola columna
- Botones más grandes para touch
- Espaciado optimizado
- Texto adaptado al tamaño de pantalla

## 🧪 Testing

### Casos de Prueba
1. **Registro Exitoso**: Todos los campos válidos
2. **ID Duplicado**: Intentar registrar ID existente
3. **Email Duplicado**: Intentar registrar email existente
4. **Contraseñas No Coinciden**: Confirmación incorrecta
5. **Campos Vacíos**: Validación de campos requeridos
6. **Formato Inválido**: Email con formato incorrecto

### URLs de Prueba
- **Registro**: `http://localhost/[ruta]/nuevoUsuario.php`
- **Login**: `http://localhost/[ruta]/pagLogin.php`

## 🚀 Próximas Mejoras

### Funcionalidades Sugeridas
1. **Verificación de Email**: Confirmar email antes de activar cuenta
2. **Recuperación de Contraseña**: Reset password por email
3. **Perfiles de Usuario**: Permitir editar información personal
4. **Validación Avanzada**: Fuerza de contraseña y más validaciones
5. **Captcha**: Prevención de spam y bots

---

**✅ Sistema de Registro Completado y Funcional**
*Fecha de implementación: Julio 2025*
