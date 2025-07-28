<?php 
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu Carrito | Tecno Y</title>
    <link rel="stylesheet" href="../../css/carrito.css">
    <link rel="stylesheet" href="../../css/headerDinamico.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header-carrito">
        <div class="header-content">
            <div class="header-left">
                <img src="../../image/logo2.png" alt="Logo Epsilon" class="logo-epsilon">
                <span class="empresa-nombre">Tecno Y</span>
            </div>

            <div class="header-right">
                <div id="header-right-content">
                    <!-- Contenido dinámico cargado por JavaScript -->
                </div>
                <nav class="header-links">
                    <a href="landingPage.php" class="back-link">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 12H5M12 19l-7-7 7-7"/>
                        </svg>
                        Volver a la tienda
                    </a>
                </nav>
            </div>
            
        </div>
    </header>
    
    <main class="carrito-main">
        <div class="carrito-container">
            <div class="page-header">
                <h1 class="page-title">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="8" cy="21" r="1"/>
                        <circle cx="19" cy="21" r="1"/>
                        <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57L20.42 9H5.12"/>
                    </svg>
                    Tu Carrito de Compras
                </h1>
                <p class="page-subtitle">Revisa tus productos antes de continuar</p>
            </div>
            
            <!-- Loading indicator -->
            <div id="loading-carrito" class="loading-container">
                <div class="loading-spinner"></div>
                <p class="loading-text">Cargando tu carrito...</p>
            </div>
            
            <!-- Carrito vacío -->
            <div id="carrito-vacio" class="carrito-vacio" style="display: none;">
                <div class="empty-cart-illustration">
                    <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="8" cy="21" r="1"/>
                        <circle cx="19" cy="21" r="1"/>
                        <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57L20.42 9H5.12"/>
                    </svg>
                </div>
                <h3 class="empty-title">Tu carrito está vacío</h3>
                <p class="empty-description">¡Explora nuestros productos y encuentra algo que te guste!</p>
                <a href="landingPage.php" class="btn-primary btn-large">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    </svg>
                    Ir a la Tienda
                </a>
            </div>
            
            <!-- Contenido del carrito -->
            <div id="carrito-contenido" class="carrito-contenido" style="display: none;">
                <div class="carrito-items-section">
                    <div class="section-header">
                        <h2>Productos en tu carrito</h2>
                        <span class="items-count" id="items-count">0 productos</span>
                    </div>
                    <div class="carrito-items" id="carrito-items">
                        <!-- Los items se cargarán aquí dinámicamente -->
                    </div>
                </div>
                
                <div class="carrito-sidebar">
                    <div class="resumen-card">
                        <h3 class="resumen-title">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 11H5a2 2 0 0 0-2 2v3c0 1.1.9 2 2 2h4m6-6h4a2 2 0 0 1 2 2v3c0 1.1-.9 2-2 2h-4m-6 0V9a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2z"/>
                            </svg>
                            Resumen del Pedido
                        </h3>
                        
                        <div class="resumen-details">
                            <div class="resumen-line">
                                <span>Subtotal:</span>
                                <span id="subtotal" class="amount">$0.00</span>
                            </div>
                            <div class="resumen-line">
                                <span>ITBMS (7%):</span>
                                <span id="impuestos" class="amount">$0.00</span>
                            </div>
                            <div class="resumen-line total-line">
                                <span><strong>Total:</strong></span>
                                <span id="total" class="amount total-amount"><strong>$0.00</strong></span>
                            </div>
                        </div>
                        
                        <div class="carrito-actions">
                            <button class="btn-secondary btn-full" onclick="vaciarCarrito()">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3,6 5,6 21,6"/>
                                    <path d="M19,6v14a2,2,0,0,1-2,2H7a2,2,0,0,1-2-2V6m3,0V4a2,2,0,0,1,2-2h4a2,2,0,0,1,2,2V6"/>
                                </svg>
                                Vaciar Carrito
                            </button>
                            <button class="btn-primary btn-full btn-checkout" onclick="procederCheckout()">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="1" y="3" width="15" height="13"/>
                                    <path d="M16 8l4-4-4-4"/>
                                </svg>
                                Proceder al Pago
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="footer-carrito">
        <div class="footer-content">
            <div class="footer-info">
                <p class="footer-brand">&copy; 2025 Tecno Y</p>
                <p class="footer-description">Página hecha con fines educativos</p>
            </div>
            <div class="footer-credits">
                <p>Todos los derechos reservados</p>
                <p>Proyecto Desarrollo de Software VI</p>
            </div>
        </div>
    </footer>
    
    <!-- Sistema de notificaciones -->
    <div id="notificaciones" class="notifications-container"></div>

    <script>
// Sistema de notificaciones mejorado
const notifications = {
    show: function(message, type = 'info', duration = 5000) {
        const container = document.getElementById('notificaciones');
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        
        const icons = {
            success: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20,6 9,17 4,12"/></svg>`,
            error: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>`,
            info: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12,16v-4"/><path d="M12,8h.01"/></svg>`
        };
        
        notification.innerHTML = `
            <div class="notification-icon">${icons[type] || icons.info}</div>
            <span class="notification-message">${message}</span>
            <button class="notification-close" onclick="this.parentElement.remove()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        `;
        
        container.appendChild(notification);
        
        // Animación de entrada
        requestAnimationFrame(() => {
            notification.classList.add('notification-show');
        });
        
        // Auto-remover
        setTimeout(() => {
            if (notification.parentElement) {
                notification.classList.add('notification-hide');
                setTimeout(() => notification.remove(), 300);
            }
        }, duration);
    }
};

let carritoData = [];

// Cargar carrito al iniciar la página
document.addEventListener('DOMContentLoaded', function() {
    cargarCarrito();
});

function cargarCarrito() {
    const loading = document.getElementById('loading-carrito');
    const carritoVacio = document.getElementById('carrito-vacio');
    const carritoContenido = document.getElementById('carrito-contenido');
    
    loading.style.display = 'flex';
    carritoVacio.style.display = 'none';
    carritoContenido.style.display = 'none';
    
    fetch('../../php/backend/carrito.php?accion=obtener')
        .then(response => response.json())
        .then(data => {
            loading.style.display = 'none';
            
            if (data.success && data.carrito.length > 0) {
                carritoData = data.carrito;
                mostrarCarrito();
                carritoContenido.style.display = 'block';
            } else {
                carritoVacio.style.display = 'flex';
            }
        })
        .catch(error => {
            loading.style.display = 'none';
            console.error('Error:', error);
            notifications.show('Error al cargar el carrito', 'error');
            carritoVacio.style.display = 'flex';
        });
}

function mostrarCarrito() {
    const container = document.getElementById('carrito-items');
    const itemsCount = document.getElementById('items-count');
    container.innerHTML = '';
    
    let subtotal = 0;
    let totalItems = 0;
    
    carritoData.forEach((item, index) => {
        const itemSubtotal = parseFloat(item.precio) * parseInt(item.cantidad);
        subtotal += itemSubtotal;
        totalItems += parseInt(item.cantidad);
        
        const itemHtml = `
        <div class="carrito-item" data-carrito-id="${item.id}" style="animation-delay: ${index * 0.1}s">
            <div class="item-imagen">
                <img src="../../${item.imagen}" alt="${item.nombre}" onerror="this.src='../../image/default-product.png'">
            </div>
            <div class="item-details">
                <div class="item-info">
                    <h4 class="item-nombre">${item.nombre}</h4>
                    <p class="item-precio">$${parseFloat(item.precio).toFixed(2)}</p>
                    <p class="item-stock">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                        </svg>
                        Stock: ${item.stock}
                    </p>
                </div>
                <div class="item-controls">
                    <div class="item-cantidad">
                        <label class="cantidad-label">Cantidad:</label>
                        <div class="cantidad-controls">
                            <button class="btn-cantidad btn-decrease" onclick="cambiarCantidad(${item.id}, ${parseInt(item.cantidad) - 1})" ${parseInt(item.cantidad) <= 1 ? 'disabled' : ''}>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="5" y1="12" x2="19" y2="12"/>
                                </svg>
                            </button>
                            <input type="number" value="${item.cantidad}" min="1" max="${parseInt(item.stock) + parseInt(item.cantidad)}" 
                                   onchange="cambiarCantidad(${item.id}, this.value)" class="cantidad-input">
                            <button class="btn-cantidad btn-increase" onclick="cambiarCantidad(${item.id}, ${parseInt(item.cantidad) + 1})" ${parseInt(item.cantidad) >= parseInt(item.stock) + parseInt(item.cantidad) ? 'disabled' : ''}>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="5" x2="12" y2="19"/>
                                    <line x1="5" y1="12" x2="19" y2="12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="item-actions">
                        <div class="item-subtotal">
                            <span class="subtotal-label">Subtotal:</span>
                            <span class="subtotal-valor">$${itemSubtotal.toFixed(2)}</span>
                        </div>
                        <button class="btn-eliminar" onclick="eliminarItem(${item.id})" title="Eliminar producto">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3,6 5,6 21,6"/>
                                <path d="M19,6v14a2,2,0,0,1-2,2H7a2,2,0,0,1-2-2V6m3,0V4a2,2,0,0,1,2-2h4a2,2,0,0,1,2,2V6"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        `;
        
        container.innerHTML += itemHtml;
    });
    
    // Actualizar contador de items
    itemsCount.textContent = `${totalItems} producto${totalItems !== 1 ? 's' : ''}`;
    
    // Actualizar resumen
    const impuestos = subtotal * 0.07;
    const total = subtotal + impuestos;
    
    document.getElementById('subtotal').textContent = `$${subtotal.toFixed(2)}`;
    document.getElementById('impuestos').textContent = `$${impuestos.toFixed(2)}`;
    document.getElementById('total').textContent = `$${total.toFixed(2)}`;
    
    // Animar items
    setTimeout(() => {
        document.querySelectorAll('.carrito-item').forEach(item => {
            item.classList.add('item-loaded');
        });
    }, 100);
}

function cambiarCantidad(carritoId, nuevaCantidad) {
    nuevaCantidad = parseInt(nuevaCantidad);
    
    if (nuevaCantidad <= 0) {
        eliminarItem(carritoId);
        return;
    }
    
    const formData = new FormData();
    formData.append('accion', 'actualizar');
    formData.append('carrito_id', carritoId);
    formData.append('cantidad', nuevaCantidad);
    
    fetch('../../php/backend/carrito.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            notifications.show(data.message, 'success');
            cargarCarrito();
        } else {
            notifications.show(data.message, 'error');
            cargarCarrito();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        notifications.show('Error al actualizar cantidad', 'error');
        cargarCarrito();
    });
}

function eliminarItem(carritoId) {
    if (!confirm('¿Estás seguro de que quieres eliminar este producto del carrito?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('accion', 'eliminar');
    formData.append('carrito_id', carritoId);
    
    fetch('../../php/backend/carrito.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            notifications.show(data.message, 'success');
            cargarCarrito();
        } else {
            notifications.show(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        notifications.show('Error al eliminar producto', 'error');
    });
}

function vaciarCarrito() {
    if (!confirm('¿Estás seguro de que quieres vaciar todo el carrito?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('accion', 'vaciar');
    
    fetch('../../php/backend/carrito.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            notifications.show(data.message, 'success');
            cargarCarrito();
        } else {
            notifications.show(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        notifications.show('Error al vaciar carrito', 'error');
    });
}

function procederCheckout() {
    notifications.show('Redirigiendo al proceso de pago...', 'info');
    // Aquí irá la lógica del checkout
}
    </script>

    <!-- Scripts para header dinámico -->
    <script src="../../JS/usuarioSesion.js"></script>
    <script>
    // Configurar sesión si existe
    document.addEventListener('DOMContentLoaded', function() {
        // Esperar a que usuarioSesion esté disponible
        setTimeout(() => {
            if (window.usuarioSesion) {
                <?php if (isset($_SESSION['usuario']) && isset($_SESSION['rol'])): ?>
                    usuarioSesion.establecerSesion(
                        true, // flagSesion
                        <?php echo ($_SESSION['rol'] === 'admin') ? 1 : 0; ?>, // admin
                        '<?php echo htmlspecialchars($_SESSION['usuario']); ?>' // usuario
                    );
                <?php else: ?>
                    // Cargar dinámicamente desde el servidor
                    usuarioSesion.cargarDatosSesion();
                <?php endif; ?>
            }
        }, 100);
    });
    </script>
</body>
</html>
