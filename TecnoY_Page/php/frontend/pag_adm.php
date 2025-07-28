<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit;
}
include '../backend/CONEXION/conexion.php';

// Obtener categorías usando nueva estructura
try {
    $stmt = $conn->prepare("SELECT idCategoria as id, nombCategoria as nombre, imagen FROM CATEGORIA ORDER BY nombCategoria");
    $stmt->execute();
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $categorias = [];
    error_log("Error al cargar categorías: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Administrador</title>
    <link rel="stylesheet" href="../../css/pag_adm.css">
    <link rel="stylesheet" href="../../css/headerDinamico.css">
    <link rel="stylesheet" href="../../css/headerAdmin.css">
    <link rel="stylesheet" href="../../css/adminComponents.css">
</head>
<body>
    <!--Header-->
    <header class="header-admin">
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

    <!--Main content-->
    <main class="admin-main">
        <div class="main-content">
        <h2>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre_usuario'] ?? $_SESSION['usuario']); ?> (Administrador)</h2>
        <h3>Categorías registradas</h3>
        <div class="categoria-select-container">
            <select id="categoriaSelect" class="form-input">
                <option value="" disabled selected>Seleccione una categoría</option>
                <option value="todas" data-img="">Todas las categorías</option>
                <?php foreach ($categorias as $cat): ?>
                    <option 
                        value="<?= htmlspecialchars($cat['id']) ?>" 
                        data-img="../../<?= htmlspecialchars($cat['imagen']) ?>"
                    >
                        <?= htmlspecialchars($cat['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div id="categoriaPreview" class="categoria-preview"></div>
        </div>        <div id="productosCards" class="productos-cards"></div>
        </div>
    </main>
    <script>
const categoriaSelect = document.getElementById('categoriaSelect');
const categoriaPreview = document.getElementById('categoriaPreview');
const productosCards = document.getElementById('productosCards');

categoriaSelect.addEventListener('change', function() {
    const selected = categoriaSelect.options[categoriaSelect.selectedIndex];
    const img = selected.getAttribute('data-img');
    const valorSeleccionado = this.value; // Guardar referencia para usar en callbacks
    
    // Mostrar preview de la categoría seleccionada (solo para categorías individuales)
    if (valorSeleccionado === 'todas') {
        categoriaPreview.innerHTML = '<span style="color: var(--secondary-color); font-weight: bold;">Mostrando todas las categorías</span>';
    } else {
        categoriaPreview.innerHTML = img ? `<img src="${img}" alt="Categoría" style="height:60px;border-radius:8px;">` : '';
    }

    // Si no hay selección, limpiar productos
    if (!valorSeleccionado) {
        productosCards.innerHTML = '';
        return;
    }

    // Mostrar indicador de carga
    productosCards.innerHTML = '<div class="loading-state">Cargando productos...</div>';

    // Determinar qué endpoint usar según la selección
    let fetchUrl;
    if (valorSeleccionado === 'todas') {
        fetchUrl = '../backend/todasCategorias.php';
    } else {
        fetchUrl = `../backend/prodXCat.php?categoria_id=${valorSeleccionado}`;
    }

    // Hacer petición para obtener productos
    fetch(fetchUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            // Verificar si hay error en la respuesta
            if (data.error) {
                throw new Error(data.mensaje || 'Error al cargar productos');
            }
            
            // Si no hay datos
            if (!data || data.length === 0) {
                productosCards.innerHTML = '<p class="empty-state">No hay productos disponibles.</p>';
                return;
            }
            
            // Renderizar según el tipo de vista
            if (valorSeleccionado === 'todas') {
                renderTodasCategorias(data);
            } else {
                renderProductosCategoria(data);
            }
        })
        .catch(error => {
            console.error('Error al cargar productos:', error);
            productosCards.innerHTML = `
                <div class="error-state">
                    <p>Error al cargar productos: ${error.message}</p>
                    <button onclick="location.reload()" class="retry-btn">Reintentar</button>
                </div>
            `;
        });
});

// Función para renderizar productos de una sola categoría
function renderProductosCategoria(productos) {
    productosCards.innerHTML = productos.map(prod => `
        <div class="producto-card">
            <img src="../../${prod.imagen}" alt="${prod.nombre}" class="producto-img" 
                 style="width:90px;height:90px;object-fit:cover;border-radius:10px;"
                 onerror="this.src='../../image/placeholder.png'">
            <div class="producto-info">
                <h4>${prod.nombre}</h4>
                <p>${prod.descripcion || 'Sin descripción'}</p>
                <div class="producto-detalles">
                    <span class="producto-precio">$${parseFloat(prod.precio).toFixed(2)}</span>
                    <span class="producto-stock">Stock: ${prod.stock}</span>
                    <span class="producto-marca">Marca: ${prod.marca}</span>
                </div>
            </div>
        </div>
    `).join('');
}

// Función para renderizar todas las categorías con sus productos
function renderTodasCategorias(categorias) {
    let htmlContent = '';
    
    categorias.forEach(categoria => {
        if (categoria.productos && categoria.productos.length > 0) {
            htmlContent += `
                <div class="categoria-section">
                    <div class="categoria-header">
                        <img src="../../${categoria.imagen}" alt="${categoria.nombre}" class="categoria-icon">
                        <h3 class="categoria-titulo">${categoria.nombre}</h3>
                        <span class="categoria-count">${categoria.productos.length} producto${categoria.productos.length !== 1 ? 's' : ''}</span>
                    </div>
                    <div class="categoria-productos">
                        ${categoria.productos.map(prod => `
                            <div class="producto-card-mini">
                                <img src="../../${prod.imagen}" alt="${prod.nombre}" class="producto-img-mini" 
                                     onerror="this.src='../../image/placeholder.png'">
                                <div class="producto-info-mini">
                                    <h5>${prod.nombre}</h5>
                                    <p>${prod.descripcion || 'Sin descripción'}</p>
                                    <div class="producto-detalles-mini">
                                        <span class="producto-precio">$${parseFloat(prod.precio).toFixed(2)}</span>
                                        <span class="producto-stock">Stock: ${prod.stock}</span>
                                        <span class="producto-marca">${prod.marca}</span>
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        }
    });
    
    productosCards.innerHTML = htmlContent || '<p class="empty-state">No hay productos disponibles en ninguna categoría.</p>';
}

// Función para actualizar el select de categorías
function actualizarSelectCategorias() {
    fetch('../backend/SELECTS/obtenerCategorias.php')
        .then(response => response.json())
        .then(categorias => {
            const select = document.getElementById('categoriaSelect');
            const valorActual = select.value;
            
            // Limpiar opciones existentes excepto las fijas
            select.innerHTML = `
                <option value="" disabled selected>Seleccione una categoría</option>
                <option value="todas" data-img="">Todas las categorías</option>
            `;
            
            // Agregar nuevas categorías
            categorias.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat.id;
                option.setAttribute('data-img', '../../' + cat.imagen);
                option.textContent = cat.nombre;
                select.appendChild(option);
            });
            
            // Restaurar valor si aún existe
            if (valorActual && [...select.options].some(opt => opt.value === valorActual)) {
                select.value = valorActual;
            }
        })
        .catch(error => {
            console.error('Error al actualizar categorías:', error);
        });
}

// Exponer función globalmente para que otras páginas puedan usarla
window.actualizarSelectCategorias = actualizarSelectCategorias;

// Escuchar cambios en las categorías desde otras pestañas
window.addEventListener('storage', function(e) {
    if (e.key === 'categorias_updated') {
        console.log('Detectado cambio en categorías, actualizando...');
        actualizarSelectCategorias();
    }
});

// También escuchar el evento focus para actualizar cuando se regrese a la pestaña
window.addEventListener('focus', function() {
    // Verificar si ha pasado tiempo desde la última actualización
    const lastUpdate = localStorage.getItem('categorias_updated');
    if (lastUpdate) {
        const timeDiff = Date.now() - parseInt(lastUpdate);
        // Si han pasado menos de 30 segundos, actualizar
        if (timeDiff < 30000) {
            actualizarSelectCategorias();
        }
    }
});
</script>

<!-- Footer -->
<footer class="footer-admin">
    <div class="footer-content">
        <p>&copy; 2025 Tecno Y - Página hecha con fines educativos</p>
        <p>Todos los derechos reservados | Proyecto Desarrollo de Software VI</p>
    </div>
</footer>

<!-- Scripts para header dinámico -->
<script src="../../JS/usuarioSesion.js"></script>
<script>
// Configurar sesión manualmente ya que tenemos acceso a los datos PHP
document.addEventListener('DOMContentLoaded', function() {
    // Esperar a que usuarioSesion esté disponible
    setTimeout(() => {
        if (window.usuarioSesion) {
            usuarioSesion.establecerSesion(
                true, // flagSesion - siempre true en esta página (requiere login admin)
                <?php echo ($_SESSION['rol'] === 'admin') ? 1 : 0; ?>, // admin
                '<?php echo htmlspecialchars($_SESSION['usuario']); ?>' // usuario
            );
        }
    }, 100);
});
</script>

</body>
</html>
