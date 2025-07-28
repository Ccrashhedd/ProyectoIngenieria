<?php 
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tecno Y | Electrónica para ti</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="../../css/landingPage.css">
    <link rel="stylesheet" href="../../css/priceSlider.css">
    <link rel="stylesheet" href="../../css/headerDinamico.css">
    
    <!-- Estilos adicionales para modal Bootstrap -->
    <style>
        .modal-content.bg-dark {
            background: rgba(26, 26, 26, 0.98) !important;
            border: 2px solid var(--primary-color, #0066ff);
            border-radius: 18px;
            box-shadow: 0 16px 64px rgba(0,212,255,0.3);
        }
        
        .modal-header.border-primary {
            border-bottom: 2px solid var(--primary-color, #0066ff) !important;
        }
        
        .modal-footer.border-primary {
            border-top: 2px solid var(--primary-color, #0066ff) !important;
        }
        
        .modal-producto-img {
            max-width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 12px;
            border: 3px solid var(--primary-color, #0066ff);
            box-shadow: 0 8px 24px rgba(0,212,255,0.2);
        }
        
        .modal-producto-nombre {
            background: linear-gradient(135deg, var(--primary-color, #0066ff), var(--secondary-color, #00d4ff));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .btn-close-white {
            filter: brightness(0) invert(1);
        }
        
        .modal-backdrop {
            backdrop-filter: blur(5px);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header-landing">
        <div class="header-left">
            <img src="../../image/logo2.png" alt="Logo Epsilon" class="logo-epsilon">
            <span class="empresa-nombre">Tecno Y</span>
        </div>
        <div class="header-right">
            <div id="header-right-content">
                <!-- Contenido dinámico cargado por JavaScript -->
            </div>
        </div>
    </header>

    <main class="main-landing">
        <section class="frase-empresa">
            <h2>Bienvenido, nuestro productos están a tu disposición</h2>
            <p class="frase">Tu pequeño centro tecnológico en La Chorrera</p>
        </section>

        <!-- Filtros para busqueda de productos -->
        <div class="filtros-container">
            <div class="filtros-busqueda">
                <label>Nombre:</label>
                <input type="text" id="filtroNombre" class="form-input" placeholder="Buscar por nombre">
            </div>

            <div class="categoria-select-container">
                <h3>Filtrar por categoría</h3>
                    <select id="categoriaSelectLanding" class="form-input">
                        <option value="" selected>Mostrar todas las categorías</option>
                    </select>
            </div>

            <div class="filtros-Marca">
                <h3>Filtrar por Marca</h3>
                    <select id="filtroMarca" class="form-input">
                        <option value="" selected>Mostrar todas las marcas</option>
                    </select>
            </div>

            <div class="filtros-Precio">
                <h3>Filtrar por Precio</h3>
                <div id="price-slider-container"></div>
            </div>
        </div>
        
        <div id="categorias-con-productos"></div>
    </main>
    
    <!-- Footer -->
    <footer class="footer-landing">
        <div class="footer-content">
            <p>&copy; 2025 Tecno Y - Página hecha con fines educativos</p>
            <p>Todos los derechos reservados | Proyecto Desarrollo de Software VI</p>
        </div>
    </footer>
    
    <!-- Modal de producto (Bootstrap) -->
    <div class="modal fade" id="modalProducto" tabindex="-1" aria-labelledby="modalProductoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-dark text-light border-primary">
                <div class="modal-header border-primary">
                    <h5 class="modal-title" id="modalProductoLabel">Detalles del Producto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 text-center">
                            <img id="modal-img" src="" alt="" class="img-fluid rounded border border-primary modal-producto-img">
                        </div>
                        <div class="col-md-6">
                            <h3 id="modal-nombre" class="modal-producto-nombre text-primary mb-3"></h3>
                            <p id="modal-descripcion" class="modal-producto-descripcion text-secondary mb-3"></p>
                            <div id="modal-precio" class="modal-producto-precio text-success fs-2 fw-bold mb-3"></div>
                            <div id="modal-stock" class="modal-producto-stock mb-3"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-primary justify-content-center">
                    <button id="btn-agregar-carrito" class="btn btn-success btn-lg me-2" onclick="agregarAlCarrito()">
                        <i class="fas fa-shopping-cart me-2"></i>Agregar al Carrito
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-lg" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Sistema de notificaciones -->
    <div id="notificaciones" class="notifications-container"></div>

    <!-- Scripts -->
    <!-- jQuery desde CDN (evita problemas de encoding) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../../JS/priceSlider.js"></script>
    <script>
// ============================================
// SISTEMA DE NOTIFICACIONES
// ============================================
const notifications = {
    show: function(message, type = 'info') {
        const container = document.getElementById('notificaciones');
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        
        const icon = type === 'success' ? 'OK' : type === 'error' ? 'ERROR' : 'INFO';
        notification.innerHTML = `
            <span class="notification-icon">${icon}</span>
            <span class="notification-message">${message}</span>
            <button class="notification-close" onclick="this.parentElement.remove()">×</button>
        `;
        
        container.appendChild(notification);
        
        // Auto-remover después de 5 segundos
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
        
        // Agregar animación de entrada
        setTimeout(() => {
            notification.classList.add('notification-show');
        }, 10);
    }
};

// ============================================
// VARIABLES GLOBALES
// ============================================
let categoriasData = [];
let productosData = [];
let filtroActual = {
    categoria: "",
    nombre: "",
    marca: "",
    precioMin: 0,
    precioMax: 2999
};

// ============================================
// FUNCIÓN PARA ACTUALIZAR CONTADOR DEL CARRITO
// ============================================
function actualizarContadorCarrito() {
    fetch('../backend/carrito_simple.php?accion=obtener')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const carritoLink = document.querySelector('.carrito-link');
                if (carritoLink) {
                    const totalItems = data.carrito.reduce((sum, item) => sum + parseInt(item.cantidad || 0), 0);
                    carritoLink.innerHTML = `Carrito (${totalItems})`;
                }
            }
        })
        .catch(error => {
            console.error('Error al actualizar contador del carrito:', error);
        });
}

// ============================================
// CARGA INICIAL DE DATOS
// ============================================
fetch('../backend/LoadProd.php')
    .then(res => res.json())
    .then(data => {
        categoriasData = data;
        // Llenar el select de categorías
        const selectCat = document.getElementById('categoriaSelectLanding');
        data.forEach(cat => {
            const opt = document.createElement('option');
            opt.value = cat.id;
            opt.textContent = cat.nombre;
            selectCat.appendChild(opt);
        });
        
        // Unir todos los productos en un solo array con referencia a su categoría
        productosData = [];
        data.forEach(cat => {
            cat.productos.forEach(prod => {
                productosData.push({
                    ...prod,
                    categoria_id: cat.id,
                    categoria_nombre: cat.nombre,
                    categoria_imagen: cat.imagen
                });
            });
        });
        
        // Llenar el select de marcas
        llenarSelectMarcas();
        
        renderProductos(); // Mostrar todos al inicio
        actualizarContadorCarrito(); // Inicializar contador del carrito
    })
    .catch(error => {
        console.error('Error al cargar productos:', error);
        notifications.show('Error al cargar productos', 'error');
    });

// ============================================
// FUNCIÓN PARA LLENAR SELECT DE MARCAS
// ============================================
function llenarSelectMarcas() {
    fetch('../backend/SELECTS/obtenerMarcas.php')
        .then(res => res.json())
        .then(marcas => {
            const selectMarca = document.getElementById('filtroMarca');
            
            marcas.forEach(marca => {
                const opt = document.createElement('option');
                opt.value = marca.id;
                opt.textContent = marca.nombre;
                selectMarca.appendChild(opt);
            });
        })
        .catch(error => {
            console.error('Error al cargar marcas:', error);
        });
}

// ============================================
// EVENT LISTENERS
// ============================================
document.getElementById('categoriaSelectLanding').addEventListener('change', function() {
    filtroActual.categoria = this.value;
    aplicarFiltros();
});

document.getElementById('filtroNombre').addEventListener('input', function() {
    filtroActual.nombre = this.value.toLowerCase();
    aplicarFiltros();
});

document.getElementById('filtroMarca').addEventListener('change', function() {
    filtroActual.marca = this.value;
    aplicarFiltros();
});

// Escuchar cambios en el slider de precio
document.addEventListener('priceRangeChanged', function(e) {
    filtroActual.precioMin = e.detail.min;
    filtroActual.precioMax = e.detail.max;
    aplicarFiltros();
});

// ============================================
// FUNCIÓN PARA APLICAR TODOS LOS FILTROS
// ============================================
// ============================================
// FUNCIÓN PARA APLICAR FILTROS
// ============================================
function aplicarFiltros() {
    // Construir parámetros de búsqueda
    const params = new URLSearchParams();
    
    if (filtroActual.nombre) {
        params.append('nombre', filtroActual.nombre);
    }
    
    if (filtroActual.categoria) {
        params.append('categoria_id', filtroActual.categoria);
    }
    
    if (filtroActual.marca) {
        params.append('marca_id', filtroActual.marca);
    }
    
    params.append('precio_min', filtroActual.precioMin);
    params.append('precio_max', filtroActual.precioMax);
    
    // Hacer consulta al backend
    const url = `../backend/CONSULTA/buscarProductos.php?${params.toString()}`;
    
    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                notifications.show('Error en la búsqueda: ' + data.mensaje, 'error');
                return;
            }
            
            // Renderizar resultados agrupados por categoría
            renderProductosFiltrados(data);
        })
        .catch(error => {
            console.error('Error en la búsqueda:', error);
            notifications.show('Error al buscar productos', 'error');
        });
}

// ============================================
// FUNCIÓN PARA RENDERIZAR PRODUCTOS
// ============================================
function renderProductos(productos = null) {
    const cont = document.getElementById('categorias-con-productos');
    cont.innerHTML = '';
    
    // Si no se pasan productos específicos, usar todos
    const productosMostrar = productos || productosData;

    if (productosMostrar.length === 0) {
        cont.innerHTML = `
            <div class="empty-state">
                <h3>No se encontraron productos</h3>
                <p>Intenta ajustar los filtros para encontrar lo que buscas</p>
            </div>
        `;
        return;
    }

    // Agrupar por categoría para mostrar igual que antes
    let cats = {};
    productosMostrar.forEach(prod => {
        if (!cats[prod.categoria_id]) {
            cats[prod.categoria_id] = {
                nombre: prod.categoria_nombre,
                imagen: prod.categoria_imagen,
                productos: []
            };
        }
        cats[prod.categoria_id].productos.push(prod);
    });

    Object.values(cats).forEach(cat => {
        let catHtml = `
        <section class="categoria-section">
            <h3 class="categoria-titulo">
                ${cat.imagen ? `<img src="../../${cat.imagen}" alt="${cat.nombre}" class="categoria-img">` : ''}
                ${cat.nombre}
                <span class="productos-count">(${cat.productos.length} productos)</span>
            </h3>
            <div class="productos-list-landing">
        `;
        
        cat.productos.forEach(prod => {
            catHtml += `
            <div class="producto-card-landing" onclick="mostrarDetalleProducto(${prod.id})">
                ${prod.imagen ? `<img src="../../${prod.imagen}" alt="${prod.nombre}" class="producto-img-landing">` : ''}
                <div class="producto-info-landing">
                    <strong>${prod.nombre}</strong>
                    <div class="producto-precio">$${parseFloat(prod.precio).toFixed(2)}</div>
                </div>
            </div>
            `;
        });
        catHtml += `</div></section>`;
        cont.innerHTML += catHtml;
    });
}

// ============================================
// FUNCIÓN PARA RENDERIZAR PRODUCTOS FILTRADOS
// ============================================
function renderProductosFiltrados(categorias) {
    const cont = document.getElementById('categorias-con-productos');
    cont.innerHTML = '';
    
    if (!categorias || categorias.length === 0) {
        cont.innerHTML = `
            <div class="empty-state">
                <h3>No se encontraron productos</h3>
                <p>Intenta ajustar los filtros para encontrar lo que buscas</p>
            </div>
        `;
        return;
    }

    categorias.forEach(cat => {
        let catHtml = `
        <section class="categoria-section">
            <h3 class="categoria-titulo">
                ${cat.imagen ? `<img src="../../${cat.imagen}" alt="${cat.nombre}" class="categoria-img">` : ''}
                ${cat.nombre}
                <span class="productos-count">(${cat.productos.length} productos)</span>
            </h3>
            <div class="productos-list-landing">
        `;
        
        cat.productos.forEach(prod => {
            catHtml += `
            <div class="producto-card-landing" onclick="mostrarDetalleProducto(${prod.id})">
                ${prod.imagen ? `<img src="../../${prod.imagen}" alt="${prod.nombre}" class="producto-img-landing">` : ''}
                <div class="producto-info-landing">
                    <strong>${prod.nombre}</strong>
                    <div class="producto-marca">Marca: ${prod.marca}</div>
                    <div class="producto-precio">$${parseFloat(prod.precio).toFixed(2)}</div>
                    <div class="producto-stock">Stock: ${prod.stock}</div>
                </div>
            </div>
            `;
        });
        catHtml += `</div></section>`;
        cont.innerHTML += catHtml;
    });
}

// ============================================
// FUNCIÓN PARA MOSTRAR DETALLE DEL PRODUCTO
// ============================================
function mostrarDetalleProducto(prodId) {
    console.log('Mostrando detalle del producto ID:', prodId);
    
    const prod = productosData.find(p => p.id == prodId);
    if (!prod) {
        console.error('Producto no encontrado:', prodId);
        notifications.show('Producto no encontrado', 'error');
        return;
    }
    
    console.log('Producto encontrado:', prod.nombre);
    
    // Llenar datos del modal
    document.getElementById('modal-img').src = `../../${prod.imagen}`;
    document.getElementById('modal-nombre').textContent = prod.nombre;
    document.getElementById('modal-descripcion').textContent = prod.descripcion;
    document.getElementById('modal-precio').textContent = `$${parseFloat(prod.precio).toFixed(2)}`;
    
    // Mostrar stock con estilo Bootstrap
    const stockElement = document.getElementById('modal-stock');
    const stock = parseInt(prod.stock);
    const btnAgregar = document.getElementById('btn-agregar-carrito');
    
    if (stock > 0) {
        stockElement.innerHTML = `<div class="alert alert-success d-inline-block"><i class="fas fa-box me-2"></i>En stock: ${stock} unidades</div>`;
        btnAgregar.disabled = false;
        btnAgregar.classList.remove('disabled');
    } else {
        stockElement.innerHTML = `<div class="alert alert-danger d-inline-block"><i class="fas fa-exclamation-triangle me-2"></i>Sin stock disponible</div>`;
        btnAgregar.disabled = true;
        btnAgregar.classList.add('disabled');
    }
    
    // Guardar ID del producto actual para el carrito
    window.currentProductId = prodId;
    
    // Mostrar modal usando Bootstrap - método más compatible
    try {
        // Método 1: Usar jQuery si está disponible
        if (typeof $ !== 'undefined') {
            $('#modalProducto').modal('show');
            console.log('Modal abierto con jQuery');
        } 
        // Método 2: Usar Bootstrap vanilla JS
        else if (typeof bootstrap !== 'undefined') {
            const modal = new bootstrap.Modal(document.getElementById('modalProducto'));
            modal.show();
            console.log('Modal abierto con Bootstrap JS');
        }
        // Método 3: Fallback manual
        else {
            document.getElementById('modalProducto').style.display = 'block';
            document.body.classList.add('modal-open');
            console.log('Modal abierto manualmente');
        }
    } catch (error) {
        console.error('Error al abrir modal:', error);
        // Fallback final
        document.getElementById('modalProducto').style.display = 'block';
    }
}

// ============================================
// FUNCIÓN PARA AGREGAR AL CARRITO
// ============================================
function agregarAlCarrito() {
    // Obtener el producto actual
    const prod = productosData.find(p => p.id == window.currentProductId);
    if (!prod) {
        notifications.show('Error: Producto no encontrado', 'error');
        return;
    }
    
    // Verificar stock
    if (prod.stock <= 0) {
        notifications.show('Producto sin stock disponible', 'error');
        return;
    }
    
    // Preparar datos para enviar
    const formData = new FormData();
    formData.append('accion', 'agregar');
    formData.append('producto_id', prod.id);
    formData.append('cantidad', 1);
    
    // Mostrar indicador de carga
    const btnAgregar = document.getElementById('btn-agregar-carrito');
    const textoOriginal = btnAgregar.innerHTML;
    btnAgregar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Agregando...';
    btnAgregar.disabled = true;
    
    fetch('../backend/carrito_simple.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            notifications.show(`${prod.nombre} agregado al carrito`, 'success');
            actualizarContadorCarrito();
            
            // Cerrar modal usando el método más compatible
            try {
                // Método 1: Usar jQuery si está disponible
                if (typeof $ !== 'undefined') {
                    $('#modalProducto').modal('hide');
                    console.log('Modal cerrado con jQuery');
                } 
                // Método 2: Usar Bootstrap vanilla JS
                else if (typeof bootstrap !== 'undefined') {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalProducto'));
                    if (modal) {
                        modal.hide();
                    }
                    console.log('Modal cerrado con Bootstrap JS');
                }
                // Método 3: Fallback manual
                else {
                    document.getElementById('modalProducto').style.display = 'none';
                    document.body.classList.remove('modal-open');
                    console.log('Modal cerrado manualmente');
                }
            } catch (error) {
                console.error('Error al cerrar modal:', error);
                // Fallback final
                document.getElementById('modalProducto').style.display = 'none';
            }
            
            // Actualizar stock en memoria para reflejar el cambio
            prod.stock -= 1;
        } else {
            notifications.show(data.message || 'Error al agregar al carrito', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        notifications.show('Error de conexión al agregar al carrito', 'error');
    })
    .finally(() => {
        // Restaurar botón
        btnAgregar.innerHTML = textoOriginal;
        btnAgregar.disabled = false;
    });
}

// ============================================
// INICIALIZACIÓN
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('Landing page cargada correctamente');
    
    // Verificar librerías disponibles
    console.log('Verificando librerias disponibles:');
    console.log('- jQuery:', typeof $ !== 'undefined' ? 'Disponible' : 'No disponible');
    console.log('- Bootstrap:', typeof bootstrap !== 'undefined' ? 'Disponible' : 'No disponible');
    
    // Verificar elementos del modal
    const modal = document.getElementById('modalProducto');
    if (modal) {
        console.log('Modal HTML encontrado');
    } else {
        console.error('Modal HTML no encontrado');
    }
    
    console.log('Bootstrap modales listos para usar');
});
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Scripts para header dinámico -->
    <script src="../../JS/usuarioSesion.js"></script>
    <script>
    // Configurar sesion si existe
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            if (window.usuarioSesion) {
                usuarioSesion.cargarDatosSesion();
            }
        }, 100);
    });
    </script>
</body>
</html>