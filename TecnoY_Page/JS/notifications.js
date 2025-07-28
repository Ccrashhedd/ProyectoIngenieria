/**
 * ============================================
 * SISTEMA DE NOTIFICACIONES
 * Sistema de notificaciones toast moderno y responsivo
 * ============================================
 */

class NotificationSystem {
    constructor() {
        this.container = this.createContainer();
        this.notifications = new Map();
        this.defaultDuration = 4000;
        this.maxNotifications = 5;
    }

    createContainer() {
        let container = document.getElementById('notifications-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'notifications-container';
            document.body.appendChild(container);
        }
        return container;
    }

    /**
     * Mostrar una notificación
     * @param {string} message - Mensaje a mostrar
     * @param {string} type - Tipo: 'success', 'error', 'warning', 'info'
     * @param {number} duration - Duración en ms (0 = permanente)
     * @param {object} options - Opciones adicionales
     */
    show(message, type = 'info', duration = null, options = {}) {
        const config = {
            message,
            type,
            duration: duration !== null ? duration : this.defaultDuration,
            persistent: duration === 0,
            showProgress: options.showProgress !== false,
            allowClose: options.allowClose !== false,
            icon: options.icon || this.getDefaultIcon(type),
            ...options
        };

        // Limitar número de notificaciones
        if (this.notifications.size >= this.maxNotifications) {
            const oldest = this.notifications.keys().next().value;
            this.remove(oldest);
        }

        const notification = this.createNotification(config);
        this.container.appendChild(notification.element);
        this.notifications.set(notification.id, notification);

        // Auto-remove si no es persistente
        if (!config.persistent) {
            setTimeout(() => {
                this.remove(notification.id);
            }, config.duration);
        }

        return notification.id;
    }

    createNotification(config) {
        const id = 'notification_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        
        const element = document.createElement('div');
        element.className = `notification ${config.type}`;
        element.dataset.id = id;

        element.innerHTML = `
            <div class="notification-content">
                <div class="notification-icon">
                    <i class="${config.icon}"></i>
                </div>
                <div class="notification-text">${config.message}</div>
                ${config.allowClose ? '<button class="notification-close">&times;</button>' : ''}
            </div>
            ${config.showProgress && !config.persistent ? '<div class="notification-progress"><div class="notification-progress-bar"></div></div>' : ''}
        `;

        // Event listeners
        if (config.allowClose) {
            const closeBtn = element.querySelector('.notification-close');
            closeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.remove(id);
            });
        }

        // Click para cerrar (opcional)
        if (config.clickToClose !== false) {
            element.addEventListener('click', () => {
                this.remove(id);
            });
        }

        return {
            id,
            element,
            config
        };
    }

    getDefaultIcon(type) {
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-times-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };
        return icons[type] || icons.info;
    }

    /**
     * Remover una notificación
     * @param {string} id - ID de la notificación
     */
    remove(id) {
        const notification = this.notifications.get(id);
        if (!notification) return;

        notification.element.classList.add('removing');
        
        setTimeout(() => {
            if (notification.element.parentNode) {
                notification.element.parentNode.removeChild(notification.element);
            }
            this.notifications.delete(id);
        }, 300);
    }

    /**
     * Limpiar todas las notificaciones
     */
    clear() {
        this.notifications.forEach((notification, id) => {
            this.remove(id);
        });
    }

    /**
     * Métodos de conveniencia
     */
    success(message, duration, options) {
        return this.show(message, 'success', duration, options);
    }

    error(message, duration, options) {
        return this.show(message, 'error', duration, options);
    }

    warning(message, duration, options) {
        return this.show(message, 'warning', duration, options);
    }

    info(message, duration, options) {
        return this.show(message, 'info', duration, options);
    }

    /**
     * Notificación persistente que requiere acción del usuario
     */
    persistent(message, type = 'info', options = {}) {
        return this.show(message, type, 0, { 
            persistent: true, 
            showProgress: false,
            ...options 
        });
    }

    /**
     * Notificación de carga/progreso
     */
    loading(message = 'Cargando...', options = {}) {
        return this.show(message, 'info', 0, {
            icon: 'fas fa-spinner fa-spin',
            allowClose: false,
            showProgress: false,
            clickToClose: false,
            ...options
        });
    }

    /**
     * Actualizar el mensaje de una notificación existente
     */
    update(id, message, type) {
        const notification = this.notifications.get(id);
        if (!notification) return;

        const textElement = notification.element.querySelector('.notification-text');
        if (textElement) {
            textElement.textContent = message;
        }

        if (type && type !== notification.config.type) {
            notification.element.className = `notification ${type}`;
            notification.config.type = type;
            
            const iconElement = notification.element.querySelector('.notification-icon i');
            if (iconElement) {
                iconElement.className = this.getDefaultIcon(type);
            }
        }
    }

    /**
     * Verificar si hay notificaciones activas
     */
    hasNotifications() {
        return this.notifications.size > 0;
    }

    /**
     * Obtener número de notificaciones activas
     */
    getCount() {
        return this.notifications.size;
    }
}

// Crear instancia global
const notifications = new NotificationSystem();

// Exportar para uso en módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NotificationSystem;
}

// Hacer disponible globalmente
window.notifications = notifications;
window.NotificationSystem = NotificationSystem;

// Auto-inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    // La instancia ya está creada, solo verificar que el container existe
    if (!document.getElementById('notifications-container')) {
        const container = document.createElement('div');
        container.id = 'notifications-container';
        document.body.appendChild(container);
    }
});
