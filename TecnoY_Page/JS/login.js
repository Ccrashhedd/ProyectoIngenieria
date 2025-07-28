/**
 * GESTIÓN DE LOGIN
 * Maneja el formulario de login y reset de contraseña
 */

class LoginManager {
    constructor() {
        this.init();
    }

    init() {
        document.addEventListener('DOMContentLoaded', () => {
            this.setupEventListeners();
        });
    }

    // Construir URL absoluta
    buildUrl(relativePath) {
        const baseUrl = window.location.origin;
        const projectPath = '/ProyectoIngenieria/ProyectoIngenieria/TecnoY_Page';
        return `${baseUrl}${projectPath}${relativePath}`;
    }

    setupEventListeners() {
        // Formulario de login
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => this.handleLogin(e));
        }

        // Modal de reset de contraseña
        const resetLink = document.getElementById('restablecerContraLink');
        if (resetLink) {
            resetLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.abrirModalReset();
            });
        }

        // Formulario de reset
        const formReset = document.getElementById('formNuevaContra');
        if (formReset) {
            formReset.addEventListener('submit', (e) => this.handleReset(e));
        }

        // Cerrar modal con ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.cerrarModalReset();
            }
        });

        // Cerrar modal al hacer click fuera
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('custom-modal')) {
                this.cerrarModalReset();
            }
        });
    }

    // ========================================
    // MANEJO DE LOGIN
    // ========================================

    async handleLogin(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        const submitBtn = document.getElementById('loginBtn');
        
        // Mostrar estado de carga
        this.setLoadingState(submitBtn, true, 'Iniciando sesión...');
        this.hideMessages();

        try {
            const url = this.buildUrl('/php/backend/USUARIO/login.php');
            console.log('🔍 URL de login:', url);
            
            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });

            console.log('📡 Response status:', response.status);
            
            const data = await response.json();
            console.log('📦 Response data:', data);

            if (data.success) {
                this.showMessage('success', data.mensaje);
                // Redirigir después de un breve delay
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1000);
            } else {
                this.showMessage('error', data.mensaje || 'Error desconocido');
            }

        } catch (error) {
            console.error('❌ Error en login:', error);
            this.showMessage('error', 'Error de conexión. Intente nuevamente.');
        } finally {
            this.setLoadingState(submitBtn, false, 'Entrar');
        }
    }

    // ========================================
    // MANEJO DE RESET DE CONTRASEÑA
    // ========================================

    abrirModalReset() {
        const modal = document.getElementById('restablecerContraModal');
        if (modal) {
            modal.style.display = 'flex';
        }
    }

    cerrarModalReset() {
        const modal = document.getElementById('restablecerContraModal');
        if (modal) {
            modal.style.display = 'none';
            document.getElementById('formNuevaContra').reset();
        }
    }

    async handleReset(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        const submitBtn = document.getElementById('resetBtn');
        
        // Validar contraseñas
        const nueva = document.getElementById('nuevaContra').value;
        const confirmar = document.getElementById('confirmarContra').value;
        
        if (nueva !== confirmar) {
            this.showMessage('error', 'Las contraseñas no coinciden');
            return;
        }
        
        if (nueva.length < 6) {
            this.showMessage('error', 'La contraseña debe tener al menos 6 caracteres');
            return;
        }

        // Mostrar estado de carga
        this.setLoadingState(submitBtn, true, 'Cambiando...');

        try {
            const response = await fetch('../backend/USUARIO/restContraUs.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showMessage('success', data.mensaje);
                this.cerrarModalReset();
            } else {
                this.showMessage('error', data.mensaje);
            }

        } catch (error) {
            console.error('Error en reset:', error);
            this.showMessage('error', 'Error de conexión. Intente nuevamente.');
        } finally {
            this.setLoadingState(submitBtn, false, 'Cambiar');
        }
    }

    // ========================================
    // UTILIDADES
    // ========================================

    showMessage(type, message) {
        this.hideMessages();
        
        const messageArea = document.getElementById('messageArea');
        if (messageArea) {
            const alertClass = type === 'error' ? 'alert-error' : 'alert-success';
            messageArea.innerHTML = `<div class="${alertClass}">${message}</div>`;
            
            // Auto-ocultar después de 5 segundos para mensajes de error
            if (type === 'error') {
                setTimeout(() => {
                    this.hideMessages();
                }, 5000);
            }
        }
    }

    hideMessages() {
        const messageArea = document.getElementById('messageArea');
        if (messageArea) {
            messageArea.innerHTML = '';
        }
    }

    setLoadingState(button, isLoading, text) {
        if (button) {
            button.disabled = isLoading;
            button.textContent = text;
        }
    }
}

// Funciones globales para mantener compatibilidad
function abrirModalReset() {
    loginManager.abrirModalReset();
}

function cerrarModalReset() {
    loginManager.cerrarModalReset();
}

// Crear instancia global
const loginManager = new LoginManager();
