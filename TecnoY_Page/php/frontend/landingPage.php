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
    
    <!-- Estilos adicionales para modal personalizado -->
    <style>
        /* Overlay del modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
            z-index: 1000;
            display: flex;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideIn {
            from { 
                opacity: 0;
                transform: translateY(-50px) scale(0.9);
            }
            to { 
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        /* Container del modal */
        .modal-container {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            border: 2px solid var(--primary-color, #0066ff);
            border-radius: 18px;
            box-shadow: 0 16px 64px rgba(0,212,255,0.3);
            max-width: 900px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideIn 0.3s ease;
        }
        
        /* Header del modal */
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 30px;
            border-bottom: 2px solid var(--primary-color, #0066ff);
        }
        
        .modal-header h2 {
            color: var(--primary-color, #0066ff);
            margin: 0;
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .modal-close-btn {
            background: none;
            border: none;
            color: #fff;
            font-size: 2rem;
            cursor: pointer;
            padding: 0;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        .modal-close-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: rotate(90deg);
        }
        
        /* Body del modal */
        .modal-body {
            padding: 30px;
        }
        
        .modal-content-grid {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 30px;
            align-items: start;
        }
        
        /* Sección de imagen */
        .modal-image-section {
            text-align: center;
        }
        
        .modal-producto-img {
            max-width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 12px;
            border: 3px solid var(--primary-color, #0066ff);
            box-shadow: 0 8px 24px rgba(0,212,255,0.2);
            transition: transform 0.3s ease;
        }
        
        .modal-producto-img:hover {
            transform: scale(1.05);
        }
        
        /* Sección de información */
        .modal-info-section {
            color: #fff;
        }
        
        .modal-producto-nombre {
            background: linear-gradient(135deg, var(--primary-color, #0066ff), var(--secondary-color, #00d4ff));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: bold;
            font-size: 1.8rem;
            margin-bottom: 20px;
        }
        
        .modal-info-item {
            margin-bottom: 15px;
        }
        
        .modal-label {
            font-weight: bold;
            color: var(--secondary-color, #00d4ff);
            display: inline-block;
            min-width: 90px;
        }
        
        .modal-value {
            color: #fff;
        }
        
        .modal-descripcion {
            color: #ccc;
            margin-top: 8px;
            line-height: 1.5;
        }
        
        .modal-precio {
            font-size: 2rem;
            font-weight: bold;
            color: #28a745;
            margin: 20px 0;
        }
        
        .modal-stock {
            margin: 20px 0;
        }
        
        /* Alertas de stock personalizadas */
        .stock-alert {
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .stock-alert.success {
            background: rgba(40, 167, 69, 0.2);
            border: 1px solid #28a745;
            color: #28a745;
        }
        
        .stock-alert.warning {
            background: rgba(255, 193, 7, 0.2);
            border: 1px solid #ffc107;
            color: #ffc107;
        }
        
        .stock-alert.danger {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid #dc3545;
            color: #dc3545;
        }
        
        /* Footer del modal */
        .modal-footer {
            display: flex;
            justify-content: center;
            gap: 15px;
            padding: 20px 30px;
            border-top: 2px solid var(--primary-color, #0066ff);
        }
        
        /* Botones del modal */
        .btn-agregar-carrito {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-agregar-carrito:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
        }
        
        .btn-agregar-carrito:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            background: #6c757d;
        }
        
        .btn-cerrar {
            background: transparent;
            color: #dc3545;
            border: 2px solid #dc3545;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-cerrar:hover {
            background: #dc3545;
            color: white;
            transform: translateY(-2px);
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .modal-container {
                width: 95%;
                margin: 20px;
            }
            
            .modal-content-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .modal-header,
            .modal-body,
            .modal-footer {
                padding: 20px;
            }
            
            .modal-footer {
                flex-direction: column;
            }
            
            .btn-agregar-carrito,
            .btn-cerrar {
                width: 100%;
                justify-content: center;
            }
        }
        
        /* Spinner para botón de carga */
        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #fff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Sistema de notificaciones */
        .notifications-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
        }
        
        .notification {
            background: rgba(0, 0, 0, 0.9);
            border: 2px solid;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            transform: translateX(100%);
            transition: transform 0.3s ease;
        }
        
        .notification.notification-show {
            transform: translateX(0);
        }
        
        .notification-success {
            border-color: #28a745;
            background: rgba(40, 167, 69, 0.2);
        }
        
        .notification-error {
            border-color: #dc3545;
            background: rgba(220, 53, 69, 0.2);
        }
        
        .notification-info {
            border-color: #17a2b8;
            background: rgba(23, 162, 184, 0.2);
        }
        
        .notification-icon {
            font-weight: bold;
            min-width: 50px;
        }
        
        .notification-message {
            flex: 1;
        }
        
        .notification-close {
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            padding: 0;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background 0.3s ease;
        }
        
        .notification-close:hover {
            background: rgba(255, 255, 255, 0.2);
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
                <label for="filtroNombre">Nombre:</label>
                <input type="text" id="filtroNombre" class="form-input" placeholder="Buscar por nombre">
            </div>

            <div class="filtros-categoria">
                <label for="categoriaSelectLanding">Filtrar por categoría:</label>
                <select id="categoriaSelectLanding" class="form-input">
                    <option value="" selected>Mostrar todas las categorías</option>
                </select>
            </div>

            <div class="filtros-marca">
                <label for="filtroMarca">Filtrar por Marca:</label>
                <select id="filtroMarca" class="form-input">
                    <option value="" selected>Mostrar todas las marcas</option>
                </select>
            </div>

            <div class="filtros-precio">
                <label>Filtrar por Precio:</label>
                <div id="price-slider-container"></div>
            </div>

            <div class="filtros-acciones">
                <button type="button" id="btnResetFiltros" class="btn-reset-filtros">
                    <i class="fas fa-undo-alt"></i> Limpiar Filtros
                </button>
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
    
    <!-- Modal de producto (Personalizado sin Bootstrap) -->
    <div id="modalProducto" class="modal-overlay" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <h2 id="modalProductoLabel">Detalles del Producto</h2>
                <!-- Botón clásico (X) para cerrar el modal -->
                <button type="button" class="modal-close-btn" onclick="cerrarModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="modal-content-grid">
                    <!-- Imagen del producto -->
                    <div class="modal-image-section">
                        <img id="modal-img" src="" alt="" class="modal-producto-img">
                    </div>
                    <!-- Información del producto -->
                    <div class="modal-info-section">
                        <!-- Nombre del producto -->
                        <h3 id="modal-nombre" class="modal-producto-nombre"></h3>
                        
                        <!-- Marca del producto -->
                        <div class="modal-info-item">
                            <span class="modal-label">Marca: </span>
                            <span id="modal-marca" class="modal-value"></span>
                        </div>
                        
                        <!-- Categoría del producto -->
                        <div class="modal-info-item">
                            <span class="modal-label">Categoría: </span>
                            <span id="modal-categoria" class="modal-value"></span>
                        </div>
                        
                        <!-- Descripción del producto -->
                        <div class="modal-info-item">
                            <span class="modal-label">Descripción:</span>
                            <p id="modal-descripcion" class="modal-descripcion"></p>
                        </div>
                        
                        <!-- Precio del producto -->
                        <div id="modal-precio" class="modal-precio"></div>
                        
                        <!-- Stock disponible -->
                        <div id="modal-stock" class="modal-stock"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <!-- Botón para agregar al carrito -->
                <button id="btn-agregar-carrito" class="btn-agregar-carrito" onclick="agregarAlCarrito()">
                    <i class="fas fa-shopping-cart"></i> Agregar al Carrito
                </button>
                <!-- Botón para cerrar el modal -->
                <button type="button" class="btn-cerrar" onclick="cerrarModal()">
                    <i class="fas fa-times"></i> Cerrar
                </button>
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
        notification.className = 'notification notification-' + type;
        
        const icon = type === 'success' ? 'OK' : type === 'error' ? 'ERROR' : 'INFO';
        notification.innerHTML = '<span class="notification-icon">' + icon + '</span>' +
            '<span class="notification-message">' + message + '</span>' +
            '<button class="notification-close" onclick="this.parentElement.remove()">×</button>';
        
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
let marcasData = [];
let filtroActual = {
    categoria: "",
    nombre: "",
    marca: "",
    precioMin: 0,
    precioMax: 2999
};

// Objeto para almacenar rangos de precios por categoría/marca
let rangosPrecios = {
    global: { min: 0, max: 2999 },
    porCategoria: {},
    porMarca: {}
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
                    carritoLink.innerHTML = 'Carrito (' + totalItems + ')';
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
    .then(res => {
        console.log('Respuesta del servidor recibida:', res.status);
        if (!res.ok) {
            throw new Error('HTTP error! status: ' + res.status);
        }
        return res.json();
    })
    .then(data => {
        console.log('Datos recibidos del backend:', data);
        
        if (data.error) {
            throw new Error(data.mensaje || 'Error en el backend');
        }
        
        categoriasData = data;
        console.log('Categorías cargadas:', categoriasData.length);
        
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
        
        console.log('Total de productos cargados:', productosData.length);
        console.log('Primeros 3 productos:', productosData.slice(0, 3));
        
        // Calcular rangos de precios
        calcularRangosPrecios();
        
        // Llenar el select de marcas
        llenarSelectMarcas();
        
        renderProductos(); // Mostrar todos al inicio
        actualizarContadorCarrito(); // Inicializar contador del carrito
        
        // Mostrar notificación de éxito
        notifications.show('Productos cargados correctamente (' + productosData.length + ' productos)', 'success');
    })
    .catch(error => {
        console.error('Error al cargar productos:', error);
        notifications.show('Error al cargar productos: ' + error.message, 'error');
        
        // Mostrar información adicional de debug
        console.error('URL intentada: ../backend/LoadProd.php');
        console.error('Error detallado:', error);
    });

// ============================================
// FUNCIÓN PARA LLENAR SELECT DE MARCAS
// ============================================
function llenarSelectMarcas() {
    fetch('../backend/SELECTS/obtenerMarcas.php')
        .then(res => res.json())
        .then(marcas => {
            marcasData = marcas;
            const selectMarca = document.getElementById('filtroMarca');
            
            marcas.forEach(marca => {
                const opt = document.createElement('option');
                opt.value = marca.id;
                opt.textContent = marca.nombre;
                selectMarca.appendChild(opt);
            });
            
            // Ahora que las marcas están cargadas, calcular sus rangos de precio
            calcularRangosPorMarca();
        })
        .catch(error => {
            console.error('Error al cargar marcas:', error);
        });
}

// ============================================
// FUNCIÓN PARA CALCULAR RANGOS DE PRECIOS
// ============================================
function calcularRangosPrecios() {
    // Calcular rango global
    let minGlobal = Infinity;
    let maxGlobal = -Infinity;
    
    productosData.forEach(prod => {
        const precio = parseFloat(prod.precio);
        if (precio < minGlobal) minGlobal = precio;
        if (precio > maxGlobal) maxGlobal = precio;
    });
    
    rangosPrecios.global = { 
        min: Math.floor(minGlobal), 
        max: Math.ceil(maxGlobal) 
    };
    
    // Calcular rangos por categoría
    categoriasData.forEach(cat => {
        let minCat = Infinity;
        let maxCat = -Infinity;
        
        cat.productos.forEach(prod => {
            const precio = parseFloat(prod.precio);
            if (precio < minCat) minCat = precio;
            if (precio > maxCat) maxCat = precio;
        });
        
        if (minCat !== Infinity && maxCat !== -Infinity) {
            rangosPrecios.porCategoria[cat.id] = {
                min: Math.floor(minCat),
                max: Math.ceil(maxCat)
            };
        }
    });
    
    // Solo calcular rangos por marca si marcasData está disponible
    if (marcasData && marcasData.length > 0) {
        calcularRangosPorMarca();
    }
    
    console.log('Rangos de precios calculados:', rangosPrecios);
}

// ============================================
// FUNCIÓN SEPARADA PARA CALCULAR RANGOS POR MARCA
// ============================================
function calcularRangosPorMarca() {
    rangosPrecios.porMarca = {};
    
    marcasData.forEach(marca => {
        let minMarca = Infinity;
        let maxMarca = -Infinity;
        
        productosData
            .filter(prod => prod.marca_id == marca.id)
            .forEach(prod => {
                const precio = parseFloat(prod.precio);
                if (precio < minMarca) minMarca = precio;
                if (precio > maxMarca) maxMarca = precio;
            });
        
        if (minMarca !== Infinity && maxMarca !== -Infinity) {
            rangosPrecios.porMarca[marca.id] = {
                min: Math.floor(minMarca),
                max: Math.ceil(maxMarca)
            };
        }
    });
    
    console.log('Rangos por marca calculados:', rangosPrecios.porMarca);
}

// ============================================
// FUNCIÓN PARA ACTUALIZAR SLIDER DE PRECIOS
// ============================================
function actualizarSliderPrecios(tipo, id = null) {
    let rango;
    
    if (tipo === 'categoria' && id) {
        rango = rangosPrecios.porCategoria[id] || rangosPrecios.global;
    } else if (tipo === 'marca' && id) {
        rango = rangosPrecios.porMarca[id] || rangosPrecios.global;
    } else {
        rango = rangosPrecios.global;
    }
    
    // Actualizar el slider de precios si existe
    if (window.priceSlider) {
        window.priceSlider.updateRange(rango.min, rango.max);
    }
    
    // Actualizar filtro actual
    filtroActual.precioMin = rango.min;
    filtroActual.precioMax = rango.max;
    
    console.log('Slider actualizado para ' + tipo + ':', rango);
}

// ============================================
// FUNCIÓN PARA FILTRAR MARCAS POR CATEGORÍA
// ============================================
function filtrarMarcasPorCategoria(categoriaId) {
    const selectMarca = document.getElementById('filtroMarca');
    
    // Limpiar opciones actuales (excepto la primera)
    while (selectMarca.children.length > 1) {
        selectMarca.removeChild(selectMarca.lastChild);
    }
    
    if (!categoriaId) {
        // Si no hay categoría seleccionada, mostrar todas las marcas
        if (marcasData && marcasData.length > 0) {
            marcasData.forEach(marca => {
                const opt = document.createElement('option');
                opt.value = marca.id;
                opt.textContent = marca.nombre;
                selectMarca.appendChild(opt);
            });
        }
        return;
    }
    
    // Buscar la categoría específica
    const categoria = categoriasData.find(cat => cat.id == categoriaId);
    if (!categoria) {
        console.warn('Categoría no encontrada:', categoriaId);
        return;
    }
    
    // Obtener marcas únicas de los productos en esta categoría
    const marcasEnCategoria = new Set();
    categoria.productos.forEach(prod => {
        if (prod.marca_id) {
            marcasEnCategoria.add(parseInt(prod.marca_id));
        }
    });
    
    console.log('Marcas encontradas en categoría:', Array.from(marcasEnCategoria));
    
    // Agregar solo las marcas que tienen productos en esta categoría
    if (marcasData && marcasData.length > 0) {
        marcasData
            .filter(marca => marcasEnCategoria.has(parseInt(marca.id)))
            .forEach(marca => {
                const opt = document.createElement('option');
                opt.value = marca.id;
                opt.textContent = marca.nombre;
                selectMarca.appendChild(opt);
            });
    }
}

// ============================================
// FUNCIÓN PARA FILTRAR CATEGORÍAS POR MARCA (Solo usar si no hay categoría seleccionada)
// ============================================
function filtrarCategoriasPorMarca(marcaId) {
    // Esta función solo debe ejecutarse si no hay categoría seleccionada
    if (filtroActual.categoria) {
        return;
    }
    
    const selectCategoria = document.getElementById('categoriaSelectLanding');
    
    // Limpiar opciones actuales (excepto la primera)
    while (selectCategoria.children.length > 1) {
        selectCategoria.removeChild(selectCategoria.lastChild);
    }
    
    if (!marcaId) {
        // Si no hay marca seleccionada, mostrar todas las categorías
        if (categoriasData && categoriasData.length > 0) {
            categoriasData.forEach(cat => {
                const opt = document.createElement('option');
                opt.value = cat.id;
                opt.textContent = cat.nombre;
                selectCategoria.appendChild(opt);
            });
        }
        return;
    }
    
    // Filtrar categorías que tengan productos de esta marca
    const categoriasConMarca = new Set();
    
    productosData
        .filter(prod => prod.marca_id == marcaId)
        .forEach(prod => {
            categoriasConMarca.add(parseInt(prod.categoria_id));
        });
    
    console.log('Categorías con la marca seleccionada:', Array.from(categoriasConMarca));
    
    // Agregar solo las categorías que tienen productos de esta marca
    if (categoriasData && categoriasData.length > 0) {
        categoriasData
            .filter(cat => categoriasConMarca.has(parseInt(cat.id)))
            .forEach(cat => {
                const opt = document.createElement('option');
                opt.value = cat.id;
                opt.textContent = cat.nombre;
                selectCategoria.appendChild(opt);
            });
    }
}

// ============================================
// FUNCIÓN PARA RESETEAR TODOS LOS FILTROS
// ============================================
function resetearFiltros() {
    console.log('Reseteando todos los filtros...');
    
    // Reset de valores de filtro
    filtroActual = {
        categoria: "",
        nombre: "",
        marca: "",
        precioMin: rangosPrecios.global.min,
        precioMax: rangosPrecios.global.max
    };
    
    // Reset de controles de UI
    document.getElementById('categoriaSelectLanding').value = "";
    document.getElementById('filtroMarca').value = "";
    document.getElementById('filtroNombre').value = "";
    
    // Restaurar todas las opciones de marcas (sin filtrar por categoría)
    const selectMarca = document.getElementById('filtroMarca');
    while (selectMarca.children.length > 1) {
        selectMarca.removeChild(selectMarca.lastChild);
    }
    
    // Recargar marcas desde el array original
    if (marcasData && marcasData.length > 0) {
        marcasData.forEach(marca => {
            const opt = document.createElement('option');
            opt.value = marca.id;
            opt.textContent = marca.nombre;
            selectMarca.appendChild(opt);
        });
    }
    
    // Restaurar todas las opciones de categorías
    const selectCategoria = document.getElementById('categoriaSelectLanding');
    while (selectCategoria.children.length > 1) {
        selectCategoria.removeChild(selectCategoria.lastChild);
    }
    
    // Recargar categorías desde el array original
    if (categoriasData && categoriasData.length > 0) {
        categoriasData.forEach(cat => {
            const opt = document.createElement('option');
            opt.value = cat.id;
            opt.textContent = cat.nombre;
            selectCategoria.appendChild(opt);
        });
    }
    
    // Reset del slider de precios al rango global
    actualizarSliderPrecios('global');
    
    // Mostrar todos los productos usando la vista original
    renderProductos();
    
    console.log('Filtros reseteados - mostrando todos los productos');
}

// ============================================
// EVENT LISTENERS
// ============================================
document.getElementById('categoriaSelectLanding').addEventListener('change', function() {
    console.log('Categoría seleccionada:', this.value);
    filtroActual.categoria = this.value;
    
    if (this.value) {
        // Si se selecciona una categoría, limpiar filtro de marca para evitar conflictos
        document.getElementById('filtroMarca').value = "";
        filtroActual.marca = "";
        
        // Actualizar slider de precios según la categoría
        actualizarSliderPrecios('categoria', this.value);
        
        // Filtrar marcas disponibles en esta categoría
        filtrarMarcasPorCategoria(this.value);
    } else {
        // Si se deselecciona la categoría, restaurar todas las marcas
        filtrarMarcasPorCategoria(null); // Esto restaurará todas las marcas
        actualizarSliderPrecios('global');
    }
    
    // Aplicar filtros
    aplicarFiltros();
});

document.getElementById('filtroNombre').addEventListener('input', function() {
    console.log('Nombre ingresado:', this.value);
    filtroActual.nombre = this.value.toLowerCase();
    aplicarFiltros();
});

document.getElementById('filtroMarca').addEventListener('change', function() {
    console.log('Marca seleccionada:', this.value);
    filtroActual.marca = this.value;
    
    if (this.value && !filtroActual.categoria) {
        // Solo actualizar slider y filtrar categorías si no hay categoría seleccionada
        actualizarSliderPrecios('marca', this.value);
        filtrarCategoriasPorMarca(this.value);
    }
    
    // Aplicar filtros
    aplicarFiltros();
});

// Escuchar cambios en el slider de precio
document.addEventListener('priceRangeChanged', function(e) {
    filtroActual.precioMin = e.detail.min;
    filtroActual.precioMax = e.detail.max;
    aplicarFiltros();
});

// Event listener para el botón de reset
document.getElementById('btnResetFiltros').addEventListener('click', function() {
    resetearFiltros();
});

// ============================================
// FUNCIÓN PARA APLICAR FILTROS
// ============================================
function aplicarFiltros() {
    // Si hay filtro de categoría activo, aplicar filtrado directo en frontend
    if (filtroActual.categoria) {
        aplicarFiltroPorCategoria();
        return;
    }
    
    // Si hay filtro de marca activo pero no de categoría, aplicar filtrado por marca
    if (filtroActual.marca && !filtroActual.categoria) {
        aplicarFiltroPorMarca();
        return;
    }
    
    // Para otros filtros (nombre, precio sin categoría/marca específica), usar backend
    aplicarFiltroGeneral();
}

// ============================================
// FUNCIÓN PARA FILTRO DIRECTO POR CATEGORÍA
// ============================================
function aplicarFiltroPorCategoria() {
    console.log('Aplicando filtro directo por categoría:', filtroActual.categoria);
    
    // Encontrar la categoría seleccionada
    const categoriaSeleccionada = categoriasData.find(cat => cat.id == filtroActual.categoria);
    
    if (!categoriaSeleccionada) {
        console.error('Categoría no encontrada:', filtroActual.categoria);
        return;
    }
    
    // Filtrar productos de esa categoría específica
    let productosFiltrados = [...categoriaSeleccionada.productos];
    
    // Aplicar filtros adicionales dentro de la categoría
    if (filtroActual.nombre) {
        productosFiltrados = productosFiltrados.filter(prod => 
            prod.nombre.toLowerCase().includes(filtroActual.nombre) ||
            (prod.descripcion && prod.descripcion.toLowerCase().includes(filtroActual.nombre))
        );
    }
    
    if (filtroActual.marca) {
        productosFiltrados = productosFiltrados.filter(prod => 
            prod.marca_id == filtroActual.marca
        );
    }
    
    // Filtrar por precio
    productosFiltrados = productosFiltrados.filter(prod => {
        const precio = parseFloat(prod.precio);
        return precio >= filtroActual.precioMin && precio <= filtroActual.precioMax;
    });
    
    // Renderizar solo esta categoría con sus productos filtrados
    const categoriaParaRender = [{
        id: categoriaSeleccionada.id,
        nombre: categoriaSeleccionada.nombre,
        imagen: categoriaSeleccionada.imagen,
        productos: productosFiltrados
    }];
    
    console.log('Mostrando ' + productosFiltrados.length + ' productos de la categoría "' + categoriaSeleccionada.nombre + '"');
    renderProductosFiltrados(categoriaParaRender);
}

// ============================================
// FUNCIÓN PARA FILTRO DIRECTO POR MARCA
// ============================================
function aplicarFiltroPorMarca() {
    console.log('Aplicando filtro directo por marca:', filtroActual.marca);
    
    // Filtrar todos los productos por marca
    let productosFiltrados = productosData.filter(prod => prod.marca_id == filtroActual.marca);
    
    // Aplicar filtros adicionales
    if (filtroActual.nombre) {
        productosFiltrados = productosFiltrados.filter(prod => 
            prod.nombre.toLowerCase().includes(filtroActual.nombre) ||
            (prod.descripcion && prod.descripcion.toLowerCase().includes(filtroActual.nombre))
        );
    }
    
    // Filtrar por precio
    productosFiltrados = productosFiltrados.filter(prod => {
        const precio = parseFloat(prod.precio);
        return precio >= filtroActual.precioMin && precio <= filtroActual.precioMax;
    });
    
    console.log('Mostrando ' + productosFiltrados.length + ' productos de la marca seleccionada');
    renderProductos(productosFiltrados);
}

// ============================================
// FUNCIÓN PARA FILTRO GENERAL (BACKEND)
// ============================================
function aplicarFiltroGeneral() {
    // Construir parámetros de búsqueda para el backend
    const params = new URLSearchParams();
    
    if (filtroActual.nombre) {
        params.append('nombre', filtroActual.nombre);
    }
    
    params.append('precio_min', filtroActual.precioMin);
    params.append('precio_max', filtroActual.precioMax);
    
    // Debugging
    console.log('Aplicando filtro general con parámetros:', {
        nombre: filtroActual.nombre,
        precio_min: filtroActual.precioMin,
        precio_max: filtroActual.precioMax
    });
    
    // Hacer consulta al backend
    const url = '../backend/CONSULTA/buscarProductos.php?' + params.toString();
    console.log('URL de consulta:', url);
    
    fetch(url)
        .then(res => res.json())
        .then(data => {
            console.log('Respuesta del servidor:', data);
            
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
    console.log('🎨 INICIANDO renderProductos...');
    const cont = document.getElementById('categorias-con-productos');
    cont.innerHTML = '';
    
    // Si no se pasan productos específicos, usar todos
    const productosMostrar = productos || productosData;
    
    console.log('📊 Productos a mostrar:', {
        longitud: productosMostrar.length,
        primeros3: productosMostrar.slice(0, 3).map(p => ({ id: p.id, nombre: p.nombre }))
    });

    if (productosMostrar.length === 0) {
        cont.innerHTML = '<div class="empty-state">' +
            '<h3>No se encontraron productos</h3>' +
            '<p>Intenta ajustar los filtros para encontrar lo que buscas</p>' +
            '</div>';
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

    console.log('🏷️ Categorías agrupadas:', Object.keys(cats).length);

    Object.values(cats).forEach(cat => {
        console.log('🏗️ Generando HTML para categoría:', cat.nombre, 'con', cat.productos.length, 'productos');
        
        let catHtml = '<section class="categoria-section">' +
            '<h3 class="categoria-titulo">' +
            (cat.imagen ? '<img src="../../' + cat.imagen + '" alt="' + cat.nombre + '" class="categoria-img">' : '') +
            cat.nombre +
            '<span class="productos-count">(' + cat.productos.length + ' productos)</span>' +
            '</h3>' +
            '<div class="productos-list-landing">';
        
        cat.productos.forEach(prod => {
            console.log('🛍️ Generando tarjeta para producto ID:', prod.id, 'Nombre:', prod.nombre);
            catHtml += '<div class="producto-card-landing producto" data-id="' + prod.id + '" style="cursor: pointer;" title="Haz clic para ver detalles">' +
                (prod.imagen ? '<img src="../../' + prod.imagen + '" alt="' + prod.nombre + '" class="producto-img-landing">' : '') +
                '<div class="producto-info-landing">' +
                '<strong>' + prod.nombre + '</strong>' +
                '<div class="producto-precio">$' + parseFloat(prod.precio).toFixed(2) + '</div>' +
                '</div>' +
                '</div>';
        });
        catHtml += '</div></section>';
        cont.innerHTML += catHtml;
    });
    
    console.log('✅ renderProductos completado');
}

// ============================================
// FUNCIÓN PARA RENDERIZAR PRODUCTOS FILTRADOS
// ============================================
function renderProductosFiltrados(categorias) {
    const cont = document.getElementById('categorias-con-productos');
    cont.innerHTML = '';
    
    if (!categorias || categorias.length === 0) {
        cont.innerHTML = '<div class="empty-state">' +
            '<h3>No se encontraron productos</h3>' +
            '<p>Intenta ajustar los filtros para encontrar lo que buscas</p>' +
            '</div>';
        return;
    }

    categorias.forEach(cat => {
        let catHtml = '<section class="categoria-section">' +
            '<h3 class="categoria-titulo">' +
            (cat.imagen ? '<img src="../../' + cat.imagen + '" alt="' + cat.nombre + '" class="categoria-img">' : '') +
            cat.nombre +
            '<span class="productos-count">(' + cat.productos.length + ' productos)</span>' +
            '</h3>' +
            '<div class="productos-list-landing">';
        
        cat.productos.forEach(prod => {
            catHtml += '<div class="producto-card-landing producto" data-id="' + prod.id + '" style="cursor: pointer;" title="Haz clic para ver detalles">' +
                (prod.imagen ? '<img src="../../' + prod.imagen + '" alt="' + prod.nombre + '" class="producto-img-landing">' : '') +
                '<div class="producto-info-landing">' +
                '<strong>' + prod.nombre + '</strong>' +
                '<div class="producto-marca">Marca: ' + prod.marca + '</div>' +
                '<div class="producto-precio">$' + parseFloat(prod.precio).toFixed(2) + '</div>' +
                '<div class="producto-stock">Stock: ' + prod.stock + '</div>' +
                '</div>' +
                '</div>';
        });
        catHtml += '</div></section>';
        cont.innerHTML += catHtml;
    });
}

// ============================================
// FUNCIÓN PARA MOSTRAR DETALLE DEL PRODUCTO
// ============================================
function mostrarDetalleProducto(prodId) {
    console.log('🔍 INICIANDO mostrarDetalleProducto con ID:', prodId);
    
    // 1. VERIFICAR DATOS CARGADOS - Siguiendo documentación
    if (!productosData || productosData.length === 0) {
        console.warn('⚠️ Los datos aún no están cargados. Esperando...');
        notifications.show('Cargando información del producto, espera un momento...', 'info');
        
        // Intentar nuevamente después de un breve delay
        setTimeout(() => {
            if (productosData && productosData.length > 0) {
                console.log('✅ Datos cargados, reintentando mostrar producto...');
                mostrarDetalleProducto(prodId); // Recursión controlada
            } else {
                console.error('❌ Los datos no se han cargado después del timeout');
                notifications.show('Error: No se pudo cargar la información del producto', 'error');
            }
        }, 1500);
        
        return; // Salir de la función actual
    }
    
    // 2. BÚSQUEDA: Encontrar producto por ID en array de memoria - Siguiendo documentación
    const producto = productosData.find(p => p.id == prodId);
    
    // 3. VALIDACIÓN: Verificar que el producto existe - Siguiendo documentación
    if (!producto) {
        console.error('❌ Producto no encontrado:', prodId);
        console.log('🔍 Productos disponibles:', productosData.map(p => ({ id: p.id, nombre: p.nombre })));
        
        // Buscar en datos originales de categorías como fallback
        console.log('🔄 Buscando en datos originales de categorías...');
        let prodEncontrado = null;
        
        categoriasData.forEach(cat => {
            cat.productos.forEach(prod => {
                if (prod.id == prodId) {
                    prodEncontrado = {
                        ...prod,
                        categoria_id: cat.id,
                        categoria_nombre: cat.nombre,
                        categoria_imagen: cat.imagen
                    };
                }
            });
        });
        
        if (prodEncontrado) {
            console.log('✅ Producto encontrado en categoriasData:', prodEncontrado.nombre);
            mostrarModalConProducto(prodEncontrado);
            return;
        }
        
        notifications.show('Producto no encontrado', 'error');
        return;
    }
    
    console.log('✅ Producto encontrado:', producto.nombre);
    console.log('📦 Datos del producto:', producto);
    
    // 4. LLAMAR A FUNCIÓN AUXILIAR PARA MOSTRAR EL MODAL
    mostrarModalConProducto(producto);
}

// ============================================
// FUNCIÓN AUXILIAR PARA MOSTRAR MODAL CON DATOS - Siguiendo documentación
// ============================================
function mostrarModalConProducto(producto) {
    console.log('🎯 Mostrando modal para:', producto.nombre);
    
    // 3. POBLACIÓN: Llenar elementos del modal con datos del producto - Siguiendo documentación
    document.getElementById('modal-img').src = '../../' + producto.imagen;
    document.getElementById('modal-img').alt = producto.nombre;
    document.getElementById('modal-nombre').textContent = producto.nombre;
    document.getElementById('modal-descripcion').textContent = producto.descripcion || producto.modelo || 'Sin descripción disponible';
    document.getElementById('modal-precio').textContent = '$' + parseFloat(producto.precio).toFixed(2);
    
    // Mostrar marca del producto
    const marcaElement = document.getElementById('modal-marca');
    if (producto.marca_nombre) {
        marcaElement.textContent = producto.marca_nombre;
    } else if (producto.marca) {
        marcaElement.textContent = producto.marca;
    } else {
        // Si no tiene marca_nombre, buscarla en marcasData
        const marca = marcasData.find(m => m.id == producto.marca_id);
        marcaElement.textContent = marca ? marca.nombre : 'Marca no especificada';
    }
    
    // Mostrar categoría del producto
    const categoriaElement = document.getElementById('modal-categoria');
    categoriaElement.textContent = producto.categoria_nombre || 'Categoría no especificada';
    
    // 4. LÓGICA CONDICIONAL: Manejo de stock - Siguiendo documentación
    const stockElement = document.getElementById('modal-stock');
    const btnAgregar = document.getElementById('btn-agregar-carrito');
    const stock = parseInt(producto.stock);
    
    if (stock > 10) {
        stockElement.innerHTML = '<div class="stock-alert success">' +
            '<i class="fas fa-box"></i>' +
            '<strong>En stock:</strong> ' + stock + ' unidades disponibles' +
            '</div>';
        btnAgregar.disabled = false;
        btnAgregar.classList.remove('disabled');
        btnAgregar.textContent = '🛒 Agregar al Carrito';
    } else if (stock > 0) {
        stockElement.innerHTML = '<div class="stock-alert warning">' +
            '<i class="fas fa-exclamation-triangle"></i>' +
            '<strong>Stock limitado:</strong> Solo ' + stock + ' unidades disponibles' +
            '</div>';
        btnAgregar.disabled = false;
        btnAgregar.classList.remove('disabled');
        btnAgregar.textContent = '🛒 Agregar al Carrito';
    } else {
        stockElement.innerHTML = '<div class="stock-alert danger">' +
            '<i class="fas fa-times-circle"></i>' +
            '<strong>Sin stock disponible</strong>' +
            '</div>';
        btnAgregar.disabled = true;
        btnAgregar.classList.add('disabled');
        btnAgregar.textContent = '❌ Agotado';
    }
    
    // 5. PERSISTENCIA: Guardar ID para otras funciones - Siguiendo documentación
    window.currentProductId = producto.id;
    
    // 6. VISUALIZACIÓN: Mostrar el modal - Siguiendo documentación
    abrirModal();
}

// ============================================
// FUNCIÓN PARA AGREGAR AL CARRITO
// ============================================
function agregarAlCarrito() {
    /* 
    ==========================================
    FUNCIONALIDAD DEL CARRITO DE COMPRAS
    ==========================================
    
    Esta función maneja la adición de productos al carrito de compras.
    Actualmente implementa:
    
    1. VALIDACIONES BÁSICAS:
       - Verificar que el producto existe
       - Verificar stock disponible
       - Validar cantidad a agregar
    
    2. COMUNICACIÓN CON BACKEND:
       - Enviar datos del producto al servidor
       - Manejar respuestas de éxito/error
       - Actualizar estado del carrito
    
    3. FUNCIONALIDADES ADICIONALES A IMPLEMENTAR:
       - Selector de cantidad en el modal
       - Validación de usuario logueado
       - Descuentos y promociones
       - Cálculo de envío
       - Gestión de wishlist/favoritos
       - Comparación de productos
       - Notificaciones push al usuario
       - Integración con sistema de pagos
       - Historial de compras del usuario
    
    ==========================================
    */
    
    // Obtener el producto actual
    const prod = productosData.find(p => p.id == window.currentProductId);
    if (!prod) {
        notifications.show('Error: Producto no encontrado', 'error');
        return;
    }
    
    // Verificar stock disponible
    if (prod.stock <= 0) {
        notifications.show('Producto sin stock disponible', 'error');
        return;
    }
    
    /* 
    TODO: Implementar selector de cantidad
    - Agregar input numérico en el modal
    - Validar cantidad máxima según stock
    - Permitir modificar cantidad antes de agregar
    */
    
    // Preparar datos para enviar al backend
    const formData = new FormData();
    formData.append('accion', 'agregar');
    formData.append('producto_id', prod.id);
    formData.append('cantidad', 1); // TODO: Obtener del selector de cantidad
    
    /* 
    TODO: Validaciones adicionales antes del envío
    - Verificar si el usuario está logueado
    - Aplicar descuentos disponibles
    - Calcular subtotales
    - Verificar límites de compra por usuario
    */
    
    // Mostrar indicador de carga en el botón
    const btnAgregar = document.getElementById('btn-agregar-carrito');
    const textoOriginal = btnAgregar.innerHTML;
    btnAgregar.innerHTML = '<span class="spinner"></span> Agregando...';
    btnAgregar.disabled = true;
    
    // Realizar petición al backend
    fetch('../backend/carrito_simple.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mostrar notificación de éxito
            notifications.show(prod.nombre + ' agregado al carrito', 'success');
            
            // Actualizar contador del carrito en el header
            actualizarContadorCarrito();
            
            /* 
            TODO: Funcionalidades adicionales al agregar exitosamente
            - Mostrar productos recomendados/relacionados
            - Sugerir productos complementarios
            - Ofrecer opciones de compra rápida
            - Actualizar wishlist si el producto estaba ahí
            - Enviar analytics del evento
            - Mostrar preview del carrito
            */
            
            // Cerrar modal usando el método más compatible
            cerrarModal();
            
            // Actualizar stock en memoria para reflejar el cambio inmediato
            prod.stock -= 1;
            
            /* 
            TODO: Actualizar UI adicional
            - Refrescar vista de productos si es necesario
            - Actualizar indicador de stock en tarjetas de productos
            - Mostrar badge "En el carrito" en el producto
            */
            
        } else {
            notifications.show(data.message || 'Error al agregar al carrito', 'error');
            
            /* 
            TODO: Manejo avanzado de errores
            - Mostrar sugerencias según el tipo de error
            - Reautenticar usuario si la sesión expiró
            - Refrescar stock si cambió
            - Ofrecer alternativas (wishlist, notificar cuando haya stock)
            */
        }
    })
    .catch(error => {
        console.error('Error:', error);
        notifications.show('Error de conexión al agregar al carrito', 'error');
        
        /* 
        TODO: Manejo de errores de red
        - Implementar retry automático
        - Guardar en localStorage para retry posterior
        - Mostrar modo offline si aplica
        */
    })
    .finally(() => {
        // Restaurar estado original del botón
        btnAgregar.innerHTML = textoOriginal;
        btnAgregar.disabled = false;
    });
}

// ============================================
// FUNCIONES PARA MODAL PERSONALIZADO - Siguiendo documentación
// ============================================
function abrirModal() {
    const modal = document.getElementById('modalProducto');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden'; // Prevenir scroll del body
        console.log('✅ Modal personalizado abierto');
    } else {
        console.error('❌ Modal no encontrado');
    }
}

// Función principal de cierre - Siguiendo documentación
function cerrarModal() {
    const modal = document.getElementById('modalProducto');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto'; // Restaurar scroll del body
        // Opcional: limpiar datos globales - Siguiendo documentación
        window.currentProductId = null;
        console.log('✅ Modal personalizado cerrado');
    }
}

// Cierre al hacer clic fuera del contenido - Siguiendo documentación
document.addEventListener('click', function(event) {
    const modal = document.getElementById('modalProducto');
    // Solo cerrar si se hace clic en el fondo del modal, no en el contenido
    if (event.target === modal) {
        cerrarModal();
    }
});

// Cierre con tecla ESC - Siguiendo documentación
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('modalProducto');
        // Solo cerrar si el modal está visible
        if (modal && modal.style.display === 'flex') {
            cerrarModal();
        }
    }
});

// ============================================
// INICIALIZACIÓN Y EVENTOS MEJORADOS - Siguiendo documentación
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('📋 Sistema de modales inicializado - Siguiendo documentación');
    
    // Verificar elementos del modal personalizado
    const modal = document.getElementById('modalProducto');
    if (modal) {
        console.log('✅ Modal personalizado HTML encontrado');
        
        // Verificar elementos internos del modal
        const elementos = [
            'modal-img', 'modal-nombre', 'modal-marca', 'modal-categoria',
            'modal-descripcion', 'modal-precio', 'modal-stock', 'btn-agregar-carrito'
        ];
        
        elementos.forEach(id => {
            const elemento = document.getElementById(id);
            if (elemento) {
                console.log('✓ Elemento ' + id + ' encontrado');
            } else {
                console.error('✗ Elemento ' + id + ' NO encontrado');
            }
        });
        
    } else {
        console.error('❌ Modal personalizado HTML no encontrado');
    }
    
    // Manejar clics en productos dinámicamente - Siguiendo documentación
    document.addEventListener('click', function(event) {
        // Solo procesar clics en elementos con la clase 'producto'
        if (event.target.closest('.producto')) {
            event.preventDefault();
            event.stopPropagation();
            
            const producto = event.target.closest('.producto');
            const idProducto = producto.getAttribute('data-id');
            
            console.log('🎯 Clic detectado en producto:', idProducto);
            
            if (idProducto) {
                // Guardar ID del producto actual - Siguiendo documentación
                window.currentProductId = idProducto;
                mostrarDetalleProducto(idProducto);
            } else {
                console.error('❌ No se encontró ID del producto');
            }
        }
    });
    
    // Configurar botón de cierre del modal - Siguiendo documentación
    const botonCerrar = document.querySelector('#modalProducto .close');
    if (botonCerrar) {
        botonCerrar.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            cerrarModal();
        });
    }
    
    console.log('✅ Eventos del modal configurados correctamente');
    
    // Función de prueba mejorada para el modal - Siguiendo documentación
    window.probarModal = function() {
        console.log('🧪 Ejecutando prueba del modal...');
        
        // Verificar si los productos están cargados
        if (!productosData || productosData.length === 0) {
            console.error('❌ No hay productos cargados aún. Intentando recargar...');
            
            // Mostrar notificación de que se están cargando los productos
            notifications.show('Cargando productos, espera un momento...', 'info');
            
            // Intentar recargar después de un segundo
            setTimeout(() => {
                if (productosData && productosData.length > 0) {
                    console.log('✅ Productos disponibles:', productosData.length);
                    const primerProducto = productosData[0];
                    console.log('🎯 Usando producto para prueba:', primerProducto);
                    window.currentProductId = primerProducto.id; // Guardar ID - Siguiendo documentación
                    mostrarDetalleProducto(primerProducto.id);
                } else {
                    notifications.show('Error: No se pudieron cargar los productos', 'error');
                    console.error('❌ Los productos no se han cargado correctamente');
                }
            }, 1000);
            
            return;
        }
        
        // Usar el primer producto disponible
        const primerProducto = productosData[0];
        console.log('🎯 Mostrando modal para el producto:', primerProducto.nombre);
        console.log('📊 Datos del producto:', primerProducto);
        
        // Guardar ID del producto actual - Siguiendo documentación
        window.currentProductId = primerProducto.id;
        
        // Llamar a la función del modal
        mostrarDetalleProducto(primerProducto.id);
    };
    
    console.log('✅ Función de prueba disponible: probarModal()');
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