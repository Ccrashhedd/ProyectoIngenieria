// ============================================
// SISTEMA DINÁMICO DE SESIÓN DE USUARIO
// ============================================

class UsuarioSesion {
    constructor() {
        this.usuario = null;
        this.flagSesion = false;
        this.admin = 0;
        this.containerElement = null;
        this.baseUrl = this.detectBaseUrl();
        
        this.init();
    }

    // Detectar la URL base del proyecto
    detectBaseUrl() {
        const currentPath = window.location.pathname;
        const scriptSrc = document.currentScript?.src || '';
        
        // Para XAMPP: http://localhost/ProyectoIngenieria/TecnoY_Page/...
        if (currentPath.includes('/ProyectoIngenieria/TecnoY_Page/')) {
            const index = currentPath.indexOf('/ProyectoIngenieria/TecnoY_Page/');
            return currentPath.substring(0, index) + '/ProyectoIngenieria/TecnoY_Page';
        }
        
        // Si estamos en una página dentro de php/frontend/
        if (currentPath.includes('/php/frontend/')) {
            return currentPath.substring(0, currentPath.indexOf('/php/frontend/'));
        }
        
        // Si estamos en la raíz del proyecto
        if (currentPath.includes('/TecnoY_Page/')) {
            return currentPath.substring(0, currentPath.indexOf('/TecnoY_Page/')) + '/TecnoY_Page';
        }
        
        // Fallback - intentar detectar desde el script
        if (scriptSrc.includes('/JS/')) {
            const jsIndex = scriptSrc.indexOf('/JS/');
            return scriptSrc.substring(0, jsIndex);
        }
        
        // Último fallback
        return '';
    }

    // Construir URL completa
    buildUrl(path) {
        return this.baseUrl + path;
    }

    init() {
        // Esperar a que el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.cargarDatosSesion());
        } else {
            this.cargarDatosSesion();
        }
    }

    // Cargar datos de sesión desde el backend
    async cargarDatosSesion() {
        try {
            const url = this.buildUrl('/php/backend/USUARIO/verificar_sesion.php');
            console.log('🔍 Verificando sesión desde:', url);
            
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            
            console.log('📡 Respuesta del servidor:', response.status, response.statusText);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            console.log('✅ Datos de sesión cargados:', data);
            
            this.flagSesion = data.logueado || false;
            this.admin = data.admin || 0;
            this.usuario = data.usuario || null;
            
            console.log('👤 Estado final:', {
                flagSesion: this.flagSesion,
                admin: this.admin,
                usuario: this.usuario
            });
            
            this.renderizarHeader();
            this.bindEventListeners();
            
        } catch (error) {
            console.error('❌ Error al cargar datos de sesión:', error);
            
            // En caso de error, asumir que no hay sesión
            this.flagSesion = false;
            this.admin = 0;
            this.usuario = null;
            this.renderizarHeader();
            this.bindEventListeners();
        }
    }

    // Establecer datos de sesión manualmente (para páginas que ya tienen la info)
    establecerSesion(flagSesion, admin, usuario) {
        this.flagSesion = flagSesion;
        this.admin = admin;
        this.usuario = usuario;
        this.renderizarHeader();
        this.bindEventListeners();
    }

    // Renderizar el header según el estado de la sesión
    renderizarHeader() {
        this.containerElement = document.getElementById('header-right-content');
        
        if (!this.containerElement) {
            console.warn('Elemento header-right-content no encontrado');
            return;
        }

        if (this.flagSesion && this.admin == 1) {
            // Usuario administrador
            this.containerElement.innerHTML = `
                <div class="user-info">
                    <span class="usuario-nombre">Bienvenido, ${this.usuario}</span>
                    <span class="user-role admin">Administrador</span>
                </div>
                <div class="navbar-controls">
                    <button id="btnCatalogo" class="nav-btn" title="Ver catálogo">📋 Catálogo</button>
                    <button id="btnCarrito" class="nav-btn" title="Ver carrito">🛒 Carrito</button>
                </div>
                <div class="sesion-admin">
                    <button id="btnProductos" class="admin-btn" title="Gestionar productos">📦 Productos</button>
                    <button id="btnCategyMarc" class="admin-btn" title="Gestionar categorías y marcas">🏷️ Categorías</button>
                    <button id="btnPedidos" class="admin-btn" title="Ver pedidos">📋 Pedidos</button>
                    <button id="btnCerrarSesion" class="logout-btn" title="Cerrar sesión">🚪 Cerrar Sesión</button>
                </div>
            `;
        } else if (this.flagSesion && this.admin == 0) {
            // Usuario normal
            this.containerElement.innerHTML = `
                <div class="user-info">
                    <span class="usuario-nombre">Bienvenido, ${this.usuario}</span>
                    <span class="user-role user">Usuario</span>
                </div>
                <div class="navbar-controls">
                    <button id="btnCatalogo" class="nav-btn" title="Ver catálogo">📋 Catálogo</button>
                    <button id="btnCarrito" class="nav-btn" title="Ver carrito">🛒 Carrito</button>
                </div>
                <div class="sesion-usuario">
                    <button id="btnHistorialClient" class="user-btn" title="Ver historial de compras">📜 Historial</button>
                    <button id="btnCerrarSesion" class="logout-btn" title="Cerrar sesión">🚪 Cerrar Sesión</button>
                </div>
            `;
        } else {
            // Usuario invitado (sin sesión)
            this.containerElement.innerHTML = `
                <div class="navbar-controls">
                    <button id="btnCatalogo" class="nav-btn" title="Ver catálogo">📋 Catálogo</button>
                    <button id="btnCarrito" class="nav-btn" title="Ver carrito">🛒 Carrito</button>
                </div>
                <div class="sesion-invitado">
                    <button id="btnIniciarSesion" class="login-btn" title="Iniciar sesión">👤 Iniciar Sesión</button>
                </div>
            `;
        }
    }

    // Vincular eventos a los botones
    bindEventListeners() {
        // Botones de navegación
        this.bindEvent('btnCatalogo', () => this.irACatalogo());
        this.bindEvent('btnCarrito', () => this.irACarrito());
        
        // Botones de administrador
        this.bindEvent('btnProductos', () => this.irAProductos());
        this.bindEvent('btnCategyMarc', () => this.irACategorias());
        this.bindEvent('btnPedidos', () => this.irAPedidos());
        
        // Botones de usuario
        this.bindEvent('btnHistorialClient', () => this.irAHistorial());
        
        // Botones de sesión
        this.bindEvent('btnIniciarSesion', () => this.irALogin());
        this.bindEvent('btnCerrarSesion', () => this.cerrarSesion());
    }

    // Método auxiliar para vincular eventos
    bindEvent(elementId, callback) {
        const element = document.getElementById(elementId);
        if (element) {
            element.addEventListener('click', callback);
        }
    }

    // Métodos de navegación
    irACatalogo() {
        window.location.href = this.buildUrl('/php/frontend/landingPage.php');
    }

    irACarrito() {
        window.location.href = this.buildUrl('/php/frontend/carrito.php');
    }

    irAProductos() {
        window.location.href = this.buildUrl('/php/frontend/productos.php');
    }

    irACategorias() {
        window.location.href = this.buildUrl('/php/frontend/categorias.php');
    }

    irAPedidos() {
        window.location.href = this.buildUrl('/php/frontend/pedidos.php');
    }

    irAHistorial() {
        window.location.href = this.buildUrl('/php/frontend/historial.php');
    }

    irALogin() {
        window.location.href = this.buildUrl('/php/frontend/pagLogin.php');
    }

    // Cerrar sesión
    async cerrarSesion() {
        if (confirm('¿Estás seguro de que quieres cerrar sesión?')) {
            try {
                const url = this.buildUrl('/php/backend/USUARIO/logout.php');
                console.log('🚪 Cerrando sesión en:', url);
                
                const response = await fetch(url, {
                    method: 'POST'
                });
                
                const data = await response.json();
                console.log('📤 Respuesta de logout:', data);
                
                if (data.success) {
                    // Actualizar estado local
                    this.flagSesion = false;
                    this.admin = 0;
                    this.usuario = null;
                    
                    // Renderizar header como invitado
                    this.renderizarHeader();
                    this.bindEventListeners();
                    
                    // Redirigir a landing page
                    window.location.href = this.buildUrl('/php/frontend/landingPage.php');
                } else {
                    alert('Error al cerrar sesión: ' + (data.mensaje || 'Error desconocido'));
                }
            } catch (error) {
                console.error('Error al cerrar sesión:', error);
                alert('Error al cerrar sesión');
            }
        }
    }

    // Método público para refrescar el header
    refrescar() {
        this.cargarDatosSesion();
    }
}

// Instancia global
let usuarioSesion;

// Inicializar cuando se cargue la página
document.addEventListener('DOMContentLoaded', function() {
    usuarioSesion = new UsuarioSesion();
});

// Exportar para uso global
window.UsuarioSesion = UsuarioSesion;

