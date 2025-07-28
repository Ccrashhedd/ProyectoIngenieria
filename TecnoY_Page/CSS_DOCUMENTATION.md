# 🎨 DOCUMENTACIÓN DEL SISTEMA CSS - TecnoY

## 📁 Estructura de Archivos CSS

### **1. base.css**
**Propósito**: Variables globales, utilidades y componentes base reutilizables
**Ubicación**: `/css/base.css`
**Contenido**:
- Variables CSS (:root) para colores, espaciado, tipografía
- Reset CSS básico
- Componentes base: botones, formularios, tarjetas, alertas
- Clases utilitarias (spacing, text, display)
- Responsive helpers

### **2. pedidosGeneral.css**
**Propósito**: Estilos específicos para la página de gestión de pedidos
**Ubicación**: `/css/pedidosGeneral.css`
**Contenido**:
- Estilos del layout principal (.mainCont, .genCont)
- Header de pedidos (.header-pedidos)
- Dashboard de estadísticas (.stats-grid, .stat-card)
- Filtros (.filtros-container, .filtros-grid)
- Tabla de pedidos (.tablaPedidos, .theadPedidos, .tdPedidos)
- Modal de detalles (.modal-overlay, .modal-container)
- Estados de pedidos (.estado-badge)
- Responsive design

### **3. notifications.css**
**Propósito**: Sistema de notificaciones
**Ubicación**: `/css/notifications.css`
**Contenido**:
- Estilos para notificaciones toast
- Animaciones de entrada/salida
- Tipos de notificación (success, error, warning, info)

## 🎨 Variables CSS Globales

### **Colores**
```css
--primary-color: #2c3e50;     /* Azul oscuro principal */
--secondary-color: #3498db;   /* Azul claro secundario */
--success-color: #27ae60;     /* Verde éxito */
--warning-color: #f39c12;     /* Naranja advertencia */
--danger-color: #e74c3c;      /* Rojo peligro */
--info-color: #17a2b8;        /* Azul información */
```

### **Espaciado**
```css
--space-xs: 5px;
--space-sm: 10px;
--space-md: 15px;
--space-lg: 20px;
--space-xl: 30px;
```

### **Border Radius**
```css
--radius-sm: 4px;
--radius: 8px;
--radius-lg: 12px;
--radius-xl: 15px;
--radius-round: 50%;
```

### **Sombras**
```css
--shadow-sm: 0 2px 4px rgba(0,0,0,0.1);
--shadow: 0 2px 10px rgba(0,0,0,0.1);
--shadow-lg: 0 5px 20px rgba(0,0,0,0.15);
--shadow-xl: 0 10px 30px rgba(0,0,0,0.3);
```

## 🧩 Componentes Base

### **Botones**
```html
<!-- Botones básicos -->
<button class="btn btn-primary">Primario</button>
<button class="btn btn-success">Éxito</button>
<button class="btn btn-danger">Peligro</button>
<button class="btn btn-info">Información</button>

<!-- Tamaños -->
<button class="btn btn-primary btn-sm">Pequeño</button>
<button class="btn btn-primary">Normal</button>
<button class="btn btn-primary btn-lg">Grande</button>

<!-- Variantes -->
<button class="btn btn-outline">Contorno</button>
```

### **Formularios**
```html
<div class="form-group">
    <label class="form-label">Etiqueta</label>
    <input type="text" class="form-control" placeholder="Texto">
</div>
```

### **Tarjetas**
```html
<div class="card">
    <div class="card-header">
        <h3>Título</h3>
    </div>
    <div class="card-body">
        <p>Contenido</p>
    </div>
    <div class="card-footer">
        <button class="btn btn-primary">Acción</button>
    </div>
</div>
```

### **Alertas**
```html
<div class="alert alert-success">Mensaje de éxito</div>
<div class="alert alert-warning">Mensaje de advertencia</div>
<div class="alert alert-danger">Mensaje de error</div>
<div class="alert alert-info">Mensaje informativo</div>
```

### **Badges**
```html
<span class="badge badge-primary">Primario</span>
<span class="badge badge-success">Éxito</span>
<span class="badge badge-warning">Advertencia</span>
```

## 🔧 Clases Utilitarias

### **Espaciado**
```html
<!-- Margin bottom -->
<div class="mb-sm">Margen inferior pequeño</div>
<div class="mb-md">Margen inferior medio</div>
<div class="mb-lg">Margen inferior grande</div>

<!-- Margin top -->
<div class="mt-sm">Margen superior pequeño</div>

<!-- Padding -->
<div class="p-sm">Padding pequeño</div>
<div class="p-md">Padding medio</div>
<div class="p-lg">Padding grande</div>
```

### **Display y Flexbox**
```html
<div class="d-flex justify-center align-center">Centrado</div>
<div class="d-flex justify-between">Espaciado entre elementos</div>
<div class="d-grid gap-lg">Grid con gap</div>
```

### **Texto**
```html
<p class="text-center">Texto centrado</p>
<p class="text-primary">Texto color primario</p>
<p class="text-muted">Texto atenuado</p>
```

## 📱 Responsive Design

### **Breakpoints**
- **Desktop**: > 1200px
- **Tablet**: 768px - 1200px
- **Mobile**: < 768px
- **Small Mobile**: < 480px

### **Clases Responsive**
```html
<!-- Ocultar en móvil -->
<div class="d-md-none">Solo visible en móvil</div>

<!-- Ocultar en móvil pequeño -->
<div class="d-sm-none">Oculto en móviles pequeños</div>
```

## 🎯 Página de Pedidos Generales

### **Estructura Principal**
```html
<main class="mainCont">
    <div class="genCont">
        <div class="header-pedidos">...</div>
        <div class="estadisticas-resumen">...</div>
        <div class="filtros-container">...</div>
        <div class="contTabla">...</div>
    </div>
</main>
```

### **Estados de Pedidos**
```html
<span class="estado-badge estado-completado">Completado</span>
<span class="estado-badge estado-pendiente">Pendiente</span>
<span class="estado-badge estado-procesando">Procesando</span>
<span class="estado-badge estado-enviado">Enviado</span>
<span class="estado-badge estado-cancelado">Cancelado</span>
```

### **Modal de Detalles**
```html
<div class="modal-overlay" id="modal-detalle">
    <div class="modal-container">
        <div class="modal-header">...</div>
        <div class="modal-body">...</div>
        <div class="modal-footer">...</div>
    </div>
</div>
```

## 🔄 Animaciones

### **Transiciones Disponibles**
```css
--transition-fast: all 0.15s ease;    /* Rápida */
--transition: all 0.3s ease;          /* Normal */
--transition-slow: all 0.5s ease;     /* Lenta */
```

### **Animaciones Predefinidas**
- `fadeIn`: Aparición con opacidad
- `slideUp`: Deslizamiento hacia arriba
- `modalSlide`: Animación de modal
- `spin`: Rotación (para loaders)
- `pulse`: Pulsación (para loading)

## 🎨 Mejores Prácticas

### **1. Usar Variables CSS**
```css
/* ✅ Correcto */
color: var(--primary-color);
padding: var(--space-lg);

/* ❌ Evitar */
color: #2c3e50;
padding: 20px;
```

### **2. Aprovechar Clases Base**
```html
<!-- ✅ Correcto -->
<button class="btn btn-primary">Click</button>

<!-- ❌ Evitar crear estilos duplicados -->
<button class="mi-boton-azul">Click</button>
```

### **3. Responsive First**
```css
/* ✅ Mobile first */
.elemento {
    padding: var(--space-sm);
}

@media (min-width: 768px) {
    .elemento {
        padding: var(--space-lg);
    }
}
```

### **4. Organización de Archivos**
- **base.css**: Siempre primero
- **componente.css**: Específico por página
- **vendor.css**: Librerías externas al final

## 📋 Checklist de Implementación

- [x] ✅ Variables CSS definidas
- [x] ✅ Componentes base creados
- [x] ✅ Responsive design implementado
- [x] ✅ Animaciones y transiciones
- [x] ✅ Accesibilidad (focus, outline)
- [x] ✅ Print styles
- [x] ✅ Cross-browser compatibility
- [x] ✅ Performance optimizado

## 🚀 Próximos Pasos

1. **Implementar tema oscuro** usando variables CSS
2. **Crear más componentes base** (modales, tabs, acordeones)
3. **Optimizar rendimiento** con CSS crítico
4. **Añadir más utilidades** según necesidades

---

**Archivo generado automáticamente el 28 de Julio, 2025**
**Sistema TecnoY - Versión 2.0**
