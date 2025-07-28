/**
 * SISTEMA DE REGISTRO DE USUARIOS
 * Maneja la validación y envío del formulario de registro
 */

class RegistroManager {
    constructor() {
        this.form = document.getElementById('registroForm');
        this.messageArea = document.getElementById('messageArea');
        this.submitBtn = document.getElementById('submitBtn');
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupValidation();
    }

    setupEventListeners() {
        // Submit del formulario
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        
        // Validación en tiempo real
        const inputs = this.form.querySelectorAll('.form-input');
        inputs.forEach(input => {
            input.addEventListener('blur', () => this.validateField(input));
            input.addEventListener('input', () => this.clearFieldError(input));
        });
        
        // Validación especial para confirmación de contraseña
        const confirmPassword = document.getElementById('confirmPassword');
        if (confirmPassword) {
            confirmPassword.addEventListener('input', () => this.validatePasswordMatch());
        }
    }

    setupValidation() {
        // Configurar mensajes de validación personalizados
        const idUsuario = document.getElementById('idUsuario');
        const email = document.getElementById('emailUsuario');
        const password = document.getElementById('passUsuario');

        if (idUsuario) {
            idUsuario.addEventListener('input', () => {
                const value = idUsuario.value;
                if (value && !/^[a-zA-Z0-9_]+$/.test(value)) {
                    this.setFieldError(idUsuario, 'Solo se permiten letras, números y guiones bajos');
                }
            });
        }

        if (email) {
            email.addEventListener('input', () => {
                const value = email.value;
                if (value && !this.isValidEmail(value)) {
                    this.setFieldError(email, 'Formato de email inválido');
                }
            });
        }

        if (password) {
            password.addEventListener('input', () => {
                const value = password.value;
                if (value.length > 0 && value.length < 6) {
                    this.setFieldError(password, 'Mínimo 6 caracteres');
                }
            });
        }
    }

    async handleSubmit(e) {
        e.preventDefault();
        
        if (!this.validateForm()) {
            return;
        }

        this.setLoading(true);
        this.clearMessages();

        try {
            const formData = new FormData(this.form);
            const data = Object.fromEntries(formData.entries());

            const response = await fetch('../backend/USUARIO/registro.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                this.showMessage('success', result.message);
                this.form.reset();
                
                // Redirigir al login después de 2 segundos
                setTimeout(() => {
                    window.location.href = 'pagLogin.php';
                }, 2000);
            } else {
                throw new Error(result.message);
            }

        } catch (error) {
            console.error('Error en registro:', error);
            this.showMessage('error', error.message || 'Error al registrar usuario');
        } finally {
            this.setLoading(false);
        }
    }

    validateForm() {
        let isValid = true;
        const inputs = this.form.querySelectorAll('.form-input[required]');

        inputs.forEach(input => {
            if (!this.validateField(input)) {
                isValid = false;
            }
        });

        // Validar coincidencia de contraseñas
        if (!this.validatePasswordMatch()) {
            isValid = false;
        }

        return isValid;
    }

    validateField(input) {
        const value = input.value.trim();
        const fieldName = input.name;

        // Limpiar errores previos
        this.clearFieldError(input);

        // Validar campo requerido
        if (input.hasAttribute('required') && !value) {
            this.setFieldError(input, 'Este campo es requerido');
            return false;
        }

        // Validaciones específicas por campo
        switch (fieldName) {
            case 'idUsuario':
                if (value.length > 20) {
                    this.setFieldError(input, 'Máximo 20 caracteres');
                    return false;
                }
                if (!/^[a-zA-Z0-9_]+$/.test(value)) {
                    this.setFieldError(input, 'Solo letras, números y guiones bajos');
                    return false;
                }
                break;

            case 'nombUsuario':
                if (value.length > 50) {
                    this.setFieldError(input, 'Máximo 50 caracteres');
                    return false;
                }
                break;

            case 'emailUsuario':
                if (value.length > 50) {
                    this.setFieldError(input, 'Máximo 50 caracteres');
                    return false;
                }
                if (!this.isValidEmail(value)) {
                    this.setFieldError(input, 'Formato de email inválido');
                    return false;
                }
                break;

            case 'passUsuario':
                if (value.length < 6) {
                    this.setFieldError(input, 'Mínimo 6 caracteres');
                    return false;
                }
                break;
        }

        // Marcar como válido
        this.setFieldValid(input);
        return true;
    }

    validatePasswordMatch() {
        const password = document.getElementById('passUsuario');
        const confirmPassword = document.getElementById('confirmPassword');

        if (!confirmPassword) return true; // Si no existe el campo, no validar

        const passwordValue = password.value;
        const confirmValue = confirmPassword.value;

        if (confirmValue && passwordValue !== confirmValue) {
            this.setFieldError(confirmPassword, 'Las contraseñas no coinciden');
            return false;
        }

        if (confirmValue) {
            this.setFieldValid(confirmPassword);
        }
        return true;
    }

    setFieldError(input, message) {
        input.classList.add('error');
        input.classList.remove('valid');
        
        // Actualizar o crear mensaje de error
        let errorMsg = input.parentNode.querySelector('.field-error');
        if (!errorMsg) {
            errorMsg = document.createElement('div');
            errorMsg.className = 'field-error';
            input.parentNode.appendChild(errorMsg);
        }
        errorMsg.textContent = message;
        errorMsg.style.display = 'block';
    }

    setFieldValid(input) {
        input.classList.remove('error');
        input.classList.add('valid');
        this.clearFieldError(input);
    }

    clearFieldError(input) {
        input.classList.remove('error');
        const errorMsg = input.parentNode.querySelector('.field-error');
        if (errorMsg) {
            errorMsg.style.display = 'none';
        }
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    showMessage(type, message) {
        this.clearMessages();
        
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${type}`;
        messageDiv.textContent = message;
        
        this.messageArea.appendChild(messageDiv);
        
        // Auto-ocultar después de 5 segundos para mensajes de éxito
        if (type === 'success') {
            setTimeout(() => {
                messageDiv.remove();
            }, 5000);
        }
    }

    clearMessages() {
        this.messageArea.innerHTML = '';
    }

    setLoading(loading) {
        if (loading) {
            this.submitBtn.disabled = true;
            this.submitBtn.innerHTML = '<span class="loading">Registrando...</span>';
        } else {
            this.submitBtn.disabled = false;
            this.submitBtn.innerHTML = 'Registrar Usuario';
        }
    }
}

// Utilidades adicionales
const RegistroUtils = {
    // Generar sugerencias de ID de usuario basadas en el nombre
    generarSugerenciasId(nombreCompleto) {
        if (!nombreCompleto) return [];
        
        const nombres = nombreCompleto.toLowerCase().split(' ');
        const sugerencias = [];
        
        if (nombres.length >= 2) {
            const primer = nombres[0];
            const segundo = nombres[1];
            
            sugerencias.push(primer + segundo);
            sugerencias.push(primer.substring(0, 3) + segundo.substring(0, 3));
            sugerencias.push(primer + segundo.substring(0, 3));
        }
        
        if (nombres.length >= 1) {
            const nombre = nombres[0];
            sugerencias.push(nombre + '123');
            sugerencias.push(nombre + '_user');
        }
        
        return sugerencias.slice(0, 3); // Máximo 3 sugerencias
    },

    // Verificar disponibilidad de ID de usuario
    async verificarDisponibilidadId(idUsuario) {
        try {
            const response = await fetch('../backend/USUARIO/verificar_disponibilidad.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ idUsuario })
            });
            
            const result = await response.json();
            return result.disponible;
        } catch (error) {
            console.error('Error verificando disponibilidad:', error);
            return true; // En caso de error, asumir disponible
        }
    }
};

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    new RegistroManager();
});

// Exportar para uso global
window.RegistroManager = RegistroManager;
window.RegistroUtils = RegistroUtils;
