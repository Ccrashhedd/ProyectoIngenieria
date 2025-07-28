<?php 
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tecno Y | Electrónica para ti</title>
    <link rel="stylesheet" href="../../css/landingPage.css">
    <link rel="stylesheet" href="../../css/priceSlider.css">
    <link rel="stylesheet" href="../../css/headerDinamico.css">
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
    
    <!-- Modal de producto -->
    <div id="modalProducto" class="modal-producto" style="display:none;">
        <div class="modal-producto-content">
            <span class="modal-close" onclick="cerrarModalProducto()">&times;</span>
            <img id="modal-img" src="" alt="" class="modal-producto-img">
            <h3 id="modal-nombre" class="modal-producto-nombre"></h3>
            <p id="modal-descripcion" class="modal-producto-descripcion"></p>
            <div id="modal-precio" class="modal-producto-precio"></div>
            <div id="modal-stock" class="modal-producto-stock"></div>
            <div class="modal-producto-actions">
                <button id="btn-agregar-carrito" class="btn-agregar-carrito" onclick="agregarAlCarrito()">
                    Agregar al Carrito
                </button>
                <button class="btn-cancelar" onclick="cerrarModalProducto()">
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    <!-- Sistema de notificaciones -->
    <div id="notificaciones" class="notifications-container"></div>

    <!-- Scripts -->
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
        
        const icon = type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️';
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
                    carritoLink.innerHTML = `🛒 Tu Carrito (${totalItems})`;
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
                <h3>😔 No se encontraron productos</h3>
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
                <h3>😔 No se encontraron productos</h3>
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
    const prod = productosData.find(p => p.id == prodId);
    if (!prod) {
        notifications.show('Producto no encontrado', 'error');
        return;
    }
    
    document.getElementById('modal-img').src = `../../${prod.imagen}`;
    document.getElementById('modal-nombre').textContent = prod.nombre;
    document.getElementById('modal-descripcion').textContent = prod.descripcion;
    document.getElementById('modal-precio').textContent = `$${parseFloat(prod.precio).toFixed(2)}`;
    
    // Mostrar stock con estilo
    const stockElement = document.getElementById('modal-stock');
    const stock = parseInt(prod.stock);
    const btnAgregar = document.getElementById('btn-agregar-carrito');
    
    if (stock > 0) {
        stockElement.innerHTML = `<span class="stock-disponible">📦 En stock: ${stock} unidades</span>`;
        btnAgregar.disabled = false;
        btnAgregar.style.opacity = '1';
    } else {
        stockElement.innerHTML = `<span class="stock-agotado">❌ Sin stock disponible</span>`;
        btnAgregar.disabled = true;
        btnAgregar.style.opacity = '0.5';
    }
    
    // Guardar ID del producto actual para el carrito
    window.currentProductId = prodId;
    
    document.getElementById('modalProducto').style.display = 'flex';
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
    btnAgregar.innerHTML = '⏳ Agregando...';
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
            cerrarModalProducto();
            
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
// FUNCIONES DEL MODAL
// ============================================
function cerrarModalProducto() {
    document.getElementById('modalProducto').style.display = 'none';
}

// Cerrar modal al hacer clic fuera del contenido
document.addEventListener('click', function(e) {
    if (e.target.id === 'modalProducto') {
        cerrarModalProducto();
    }
});

// Cerrar modal con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        cerrarModalProducto();
    }
});

// ============================================
// INICIALIZACIÓN
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Landing page cargada correctamente');
});
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