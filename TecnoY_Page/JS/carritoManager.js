/**
 * ============================================
 * SISTEMA JAVASCRIPT PARA MANEJO DEL CARRITO
 * Archivo: carritoManager.js
 * Ubicación: /JS/
 * ============================================
 */

class CarritoManager {
    constructor() {
        this.baseUrl = this.detectBaseUrl();
        this.carritoUrl = `${this.baseUrl}/php/backend/CRUD/CARRITO/carritoController.php`;
        this.carritoData = [];
        this.totalItems = 0;
        this.totalCarrito = 0;
        
        // Inicializar eventos
        this.init();
    }

    // Detectar la URL base del proyecto
    detectBaseUrl() {
        const currentPath = window.location.pathname;
        
        if (currentPath.includes('/ProyectoIngenieria/ProyectoIngenieria/TecnoY_Page/')) {
            const index = currentPath.indexOf('/ProyectoIngenieria/ProyectoIngenieria/TecnoY_Page/');
            return currentPath.substring(0, index) + '/ProyectoIngenieria/ProyectoIngenieria/TecnoY_Page';
        }
        
        return '/ProyectoIngenieria/ProyectoIngenieria/TecnoY_Page';
    }

    // Inicializar el sistema
    init() {
        console.log('🛒 CarritoManager inicializado');
        console.log('📍 Base URL:', this.baseUrl);
        
        // Cargar carrito al iniciar
        this.obtenerCarrito();
        
        // Actualizar contador cada 30 segundos
        setInterval(() => this.actualizarContador(), 30000);
    }

    // ==========================================
    // MÉTODOS PRINCIPALES
    // ==========================================

    /**
     * Agregar producto al carrito
     * @param {string} idProducto - ID del producto
     * @param {number} cantidad - Cantidad a agregar (mínimo 1)
     * @returns {Promise} - Promesa con el resultado
     */
    async agregarProducto(idProducto, cantidad = 1) {
        try {
            if (!idProducto) {
                throw new Error('ID del producto es requerido');
            }

            if (cantidad < 1) {
                throw new Error('La cantidad debe ser al menos 1');
            }

            console.log(`🛒 Agregando producto ${idProducto} (cantidad: ${cantidad})`);

            const formData = new FormData();
            formData.append('accion', 'agregar');
            formData.append('idProducto', idProducto);
            formData.append('cantidad', cantidad);

            const response = await fetch(this.carritoUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                console.log('✅ Producto agregado:', data);
                await this.obtenerCarrito(); // Actualizar carrito
                this.mostrarNotificacion(data.mensaje, 'success');
                this.actualizarContador();
                return data;
            } else {
                console.error('❌ Error al agregar:', data.mensaje);
                this.mostrarNotificacion(data.mensaje, 'error');
                throw new Error(data.mensaje);
            }

        } catch (error) {
            console.error('💥 Error en agregarProducto:', error);
            this.mostrarNotificacion(error.message, 'error');
            throw error;
        }
    }

    /**
     * Actualizar cantidad de un producto
     * @param {string} idCarritoDetalle - ID del detalle del carrito
     * @param {number} nuevaCantidad - Nueva cantidad
     * @param {string} accion - Tipo de acción: 'actualizar', 'incrementar', 'decrementar'
     * @returns {Promise} - Promesa con el resultado
     */
    async actualizarCantidad(idCarritoDetalle, nuevaCantidad = null, accion = 'actualizar') {
        try {
            if (!idCarritoDetalle) {
                throw new Error('ID del detalle del carrito es requerido');
            }

            console.log(`🔄 Actualizando cantidad: ${idCarritoDetalle}`);

            const formData = new FormData();
            formData.append('accion', 'actualizar');
            formData.append('idCarritoDetalle', idCarritoDetalle);
            formData.append('accion', accion); // actualizar, incrementar, decrementar
            
            if (nuevaCantidad !== null) {
                formData.append('cantidad', nuevaCantidad);
            }

            const response = await fetch(this.carritoUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                console.log('✅ Cantidad actualizada:', data);
                await this.obtenerCarrito(); // Actualizar carrito
                this.mostrarNotificacion(data.mensaje, 'success');
                this.actualizarContador();
                return data;
            } else {
                console.error('❌ Error al actualizar:', data.mensaje);
                this.mostrarNotificacion(data.mensaje, 'error');
                throw new Error(data.mensaje);
            }

        } catch (error) {
            console.error('💥 Error en actualizarCantidad:', error);
            this.mostrarNotificacion(error.message, 'error');
            throw error;
        }
    }

    /**
     * Eliminar producto del carrito
     * @param {string} idCarritoDetalle - ID del detalle del carrito
     * @returns {Promise} - Promesa con el resultado
     */
    async eliminarProducto(idCarritoDetalle) {
        try {
            if (!idCarritoDetalle) {
                throw new Error('ID del detalle del carrito es requerido');
            }

            console.log(`🗑️ Eliminando producto: ${idCarritoDetalle}`);

            const formData = new FormData();
            formData.append('accion', 'eliminar');
            formData.append('idCarritoDetalle', idCarritoDetalle);

            const response = await fetch(this.carritoUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                console.log('✅ Producto eliminado:', data);
                await this.obtenerCarrito(); // Actualizar carrito
                this.mostrarNotificacion(data.mensaje, 'success');
                this.actualizarContador();
                return data;
            } else {
                console.error('❌ Error al eliminar:', data.mensaje);
                this.mostrarNotificacion(data.mensaje, 'error');
                throw new Error(data.mensaje);
            }

        } catch (error) {
            console.error('💥 Error en eliminarProducto:', error);
            this.mostrarNotificacion(error.message, 'error');
            throw error;
        }
    }

    /**
     * Vaciar todo el carrito
     * @returns {Promise} - Promesa con el resultado
     */
    async vaciarCarrito() {
        try {
            if (!confirm('¿Estás seguro de que quieres vaciar todo el carrito?')) {
                return null;
            }

            console.log('🗑️ Vaciando carrito completo');

            const formData = new FormData();
            formData.append('accion', 'vaciar');

            const response = await fetch(this.carritoUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                console.log('✅ Carrito vaciado:', data);
                await this.obtenerCarrito(); // Actualizar carrito
                this.mostrarNotificacion(data.mensaje, 'success');
                this.actualizarContador();
                return data;
            } else {
                console.error('❌ Error al vaciar:', data.mensaje);
                this.mostrarNotificacion(data.mensaje, 'error');
                throw new Error(data.mensaje);
            }

        } catch (error) {
            console.error('💥 Error en vaciarCarrito:', error);
            this.mostrarNotificacion(error.message, 'error');
            throw error;
        }
    }

    /**
     * Obtener carrito completo
     * @returns {Promise} - Promesa con los datos del carrito
     */
    async obtenerCarrito() {
        try {
            console.log('📦 Obteniendo carrito');

            const response = await fetch(`${this.carritoUrl}?accion=obtener`, {
                method: 'GET'
            });

            const data = await response.json();

            if (data.success) {
                this.carritoData = data.productos || [];
                this.totalItems = data.totalItems || 0;
                this.totalCarrito = data.totalCarrito || 0;
                
                console.log('✅ Carrito obtenido:', data);
                return data;
            } else {
                console.warn('⚠️ No se pudo obtener el carrito:', data.message);
                return data;
            }

        } catch (error) {
            console.error('💥 Error en obtenerCarrito:', error);
            throw error;
        }
    }

    /**
     * Verificar si un producto está en el carrito
     * @param {string} idProducto - ID del producto
     * @returns {Promise} - Promesa con la información del producto
     */
    async verificarProducto(idProducto) {
        try {
            const response = await fetch(`${this.carritoUrl}?accion=verificar_producto&idProducto=${idProducto}`, {
                method: 'GET'
            });

            const data = await response.json();
            
            if (data.success) {
                return data.producto;
            } else {
                throw new Error(data.mensaje);
            }

        } catch (error) {
            console.error('💥 Error en verificarProducto:', error);
            throw error;
        }
    }

    // ==========================================
    // MÉTODOS DE UTILIDAD
    // ==========================================

    /**
     * Actualizar contador visual del carrito
     */
    actualizarContador() {
        const contadores = document.querySelectorAll('.carrito-contador, #carrito-contador, .cart-count');
        
        contadores.forEach(contador => {
            if (contador) {
                contador.textContent = this.totalItems;
                contador.style.display = this.totalItems > 0 ? 'block' : 'none';
            }
        });

        // Actualizar precio total si existe
        const totales = document.querySelectorAll('.carrito-total, #carrito-total, .cart-total');
        totales.forEach(total => {
            if (total) {
                total.textContent = `$${this.totalCarrito.toFixed(2)}`;
            }
        });
    }

    /**
     * Mostrar notificación
     * @param {string} mensaje - Mensaje a mostrar
     * @param {string} tipo - Tipo de notificación: 'success', 'error', 'info'
     */
    mostrarNotificacion(mensaje, tipo = 'info') {
        // Buscar sistema de notificaciones existente
        if (window.notifications && typeof window.notifications.show === 'function') {
            window.notifications.show(mensaje, tipo);
            return;
        }

        // Fallback: mostrar alerta simple
        if (tipo === 'error') {
            alert(`Error: ${mensaje}`);
        } else {
            console.log(`📢 ${mensaje}`);
        }
    }

    /**
     * Obtener datos del carrito (solo lectura)
     * @returns {object} - Datos actuales del carrito
     */
    getDatos() {
        return {
            productos: [...this.carritoData],
            totalItems: this.totalItems,
            totalCarrito: this.totalCarrito
        };
    }
}

// ==========================================
// INICIALIZACIÓN GLOBAL
// ==========================================

// Crear instancia global cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    if (!window.carritoManager) {
        window.carritoManager = new CarritoManager();
        console.log('🛒 CarritoManager disponible globalmente');
    }
});

// Exportar para uso en módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CarritoManager;
}
