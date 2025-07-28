<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit;
}
include '../backend/CONEXION/conexion.php';

// Obtener categorías usando nueva estructura
try {
    $stmt = $conn->prepare("SELECT idCategoria as id, nombCategoria as nombre FROM CATEGORIA ORDER BY nombCategoria");
    $stmt->execute();
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $categorias = [];
    $error_msg = "Error al cargar categorías: " . $e->getMessage();
}

// Insertar nuevo producto usando stored procedure
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'])) {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $categoria_id = $_POST['categoria_id'];
    $stock = $_POST['stock'];
    $imagen = null;

    // Procesar imagen si se subió
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $imgTmp = $_FILES['imagen']['tmp_name'];
        $imgName = uniqid() . "_" . basename($_FILES['imagen']['name']);
        $carpeta = "../../image/img_productos/";
        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0777, true);
        }
        $destino = $carpeta . $imgName;
        if (move_uploaded_file($imgTmp, $destino)) {
            $imagen = "image/img_productos/" . $imgName;
        }
    }

    try {
        // Obtener o crear marca genérica si no se especifica
        $idMarca = null;
        $stmt = $conn->prepare("SELECT idMarca FROM MARCA WHERE nombMarca = 'GENÉRICA' LIMIT 1");
        $stmt->execute();
        $marcaGenerica = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$marcaGenerica) {
            // Crear marca genérica usando el procedure
            $stmt = $conn->prepare("CALL agregarMarca(?)");
            $stmt->execute(['GENÉRICA']);
            
            // Obtener el ID de la marca recién creada
            $stmt = $conn->prepare("SELECT idMarca FROM MARCA WHERE nombMarca = 'GENÉRICA' LIMIT 1");
            $stmt->execute();
            $marcaGenerica = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        $idMarca = $marcaGenerica['idMarca'];
        
        // Llamar al stored procedure para agregar producto
        $stmt = $conn->prepare("CALL agregarProducto(?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $nombre,
            $descripcion, 
            $precio,
            $stock,
            $imagen,
            $idMarca,
            $categoria_id
        ]);
        
        header("Location: productos.php?success=1");
        exit;
    } catch (Exception $e) {
        $error_msg = "Error al insertar producto: " . $e->getMessage();
    }
}

// Obtener productos usando nueva estructura
try {
    $stmt = $conn->prepare("
        SELECT 
            p.idProducto as id, 
            p.nombProducto as nombre, 
            p.imagen, 
            p.stock, 
            c.nombCategoria as categoria, 
            p.idCategoria as categoria_id,
            p.precio,
            p.modelo as descripcion
        FROM PRODUCTO p
        JOIN CATEGORIA c ON p.idCategoria = c.idCategoria
        ORDER BY p.nombProducto
    ");
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $productos = [];
    $error_msg = "Error al cargar productos: " . $e->getMessage();
}

// Eliminar producto usando stored procedure
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    try {
        // Obtener la ruta de la imagen antes de eliminar
        $stmt = $conn->prepare("SELECT imagen FROM PRODUCTO WHERE idProducto = ?");
        $stmt->execute([$id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $imagen = $resultado['imagen'] ?? null;

        // Llamar al stored procedure para eliminar producto (mueve a tabla de eliminados)
        $stmt = $conn->prepare("CALL eliminarProducto(?)");
        $stmt->execute([$id]);

        // Eliminar la imagen física si existe
        if ($imagen && file_exists("../../" . $imagen)) {
            unlink("../../" . $imagen);
        }
        
        header("Location: productos.php?deleted=1");
        exit;
    } catch (Exception $e) {
        $error_msg = "Error al eliminar producto: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Productos</title>
    <link rel="stylesheet" href="../../css/productos.css">
    <link rel="stylesheet" href="../../css/headerDinamico.css">
    <link rel="stylesheet" href="../../css/headerAdmin.css">
    <link rel="stylesheet" href="../../css/adminComponents.css">
</head>
<body>

    <!--Header-->
    <header class="headCont">
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

    <main class="productos-main">

<div class="productos-container">
    <div class="back-nav">
        <a href="pag_adm.php" class="back-button">⟵ Volver</a>
        <a href="#listado-productos" class="back-button">Lista de productos</a>
    </div>
    <h2 class="page-title">Productos</h2>

    <form method="POST" enctype="multipart/form-data" class="form-producto">
        <div class="form-group">
            <label class="form-label">Categoría:</label>
            <select name="categoria_id" class="form-input" required>
                <option value="" disabled selected>Seleccione una categoría</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Nombre del producto:</label>
            <input type="text" name="nombre" class="form-input" required>
        </div>
        <div class="form-group">
            <label class="form-label">Descripción:</label>
            <textarea name="descripcion" class="form-textarea" required></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Precio:</label>
            <input type="number" step="0.01" name="precio" class="form-input" required>
        </div>
        <div class="form-group">
            <label class="form-label">Stock:</label>
            <input type="number" min="0" name="stock" class="form-input" value="0" required>
        </div>
        <div class="form-group">
            <label class="form-label">Imagen:</label>
            <input type="file" name="imagen" class="form-input" accept="image/*">
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Agregar Producto</button>
            <button type="button" class="btn btn-cancelar" onclick="window.location.href='pag_adm.php'">Cancelar</button>
        </div>
    </form>

    <div class="categoria-select-container">
        <select id="categoriaFiltro" class="form-input">
            <option value="" selected>Mostrar todas las categorías</option>
            <?php foreach ($categorias as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <h3 id="listado-productos" class="section-title">Listado de productos</h3>
    <ul class="productos-list" id="productosList">
        <?php foreach ($productos as $prod): ?>
            <li class="producto-card" data-categoria-id="<?= $prod['categoria_id'] ?>">
                <div class="producto-img">
                    <?php if (!empty($prod['imagen'])): ?>
                        <img src="../../<?= htmlspecialchars($prod['imagen']) ?>" alt="<?= htmlspecialchars($prod['nombre']) ?>" class="producto-img-thumb">
                    <?php endif; ?>
                </div>
                <div class="producto-info">
                    <strong><?= htmlspecialchars($prod['nombre']) ?></strong>
                    <span class="producto-categoria"> | Categoría: <?= htmlspecialchars($prod['categoria']) ?></span>
                    <span class="producto-stock"> | En stock: 
                        <span class="stock-<?= $prod['stock'] > 0 ? 'disponible' : 'agotado' ?>">
                            <?= $prod['stock'] > 0 ? $prod['stock'] . '' : 'Agotado' ?>
                        </span>
                    </span>
                </div>
                <div class="product-actions">
                    <a href="#" class="btn btn-warning btn-small" title="Editar"
                       onclick="abrirModalEditar(<?= $prod['id'] ?>); return false;">&#9998;</a>
                    <a href="#" class="btn btn-danger btn-small" onclick="confirmarEliminar(<?= $prod['id'] ?>, '<?= htmlspecialchars(addslashes($prod['nombre'])) ?>'); return false;" title="Eliminar">&#128465;</a>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<!-- Modal de edición de producto -->
<div id="modalEditarProducto" class="custom-modal" style="display:none;">
  <div class="modal-content">
    <span class="close-modal" onclick="cerrarModalEditar()">&times;</span>
    <h3>Editar producto</h3>
    <form id="formEditarProducto" enctype="multipart/form-data">
      <input type="hidden" name="id" id="edit-id">
      <div class="form-group">
        <label>Categoría:</label>
        <select name="categoria_id" id="edit-categoria" class="form-input" required>
          <?php foreach ($categorias as $cat): ?>
            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Nombre:</label>
        <input type="text" name="nombre" id="edit-nombre" class="form-input" required>
      </div>
      <div class="form-group">
        <label>Descripción:</label>
        <textarea name="descripcion" id="edit-descripcion" class="form-textarea" required></textarea>
      </div>
      <div class="form-group">
        <label>Precio:</label>
        <input type="number" step="0.01" name="precio" id="edit-precio" class="form-input" required>
      </div>
      <div class="form-group">
        <label>Stock:</label>
        <input type="number" min="0" name="stock" id="edit-stock" class="form-input" required>
      </div>
      <div class="form-group">
        <label>Imagen:</label>
        <input type="file" name="imagen" id="edit-imagen" class="form-input" accept="image/*">
        <img id="edit-preview" src="" alt="Vista previa" style="max-width:60px;max-height:60px;margin-top:8px;display:none;">
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
        <button type="button" class="btn btn-cancelar" onclick="cerrarModalEditar()">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal de confirmación -->
<div id="modalConfirmarEliminar" class="custom-modal" style="display:none;">
  <div class="modal-content" style="max-width:340px;">
    <span class="close-modal" onclick="cerrarModalEliminar()">&times;</span>
    <h3 id="modal-eliminar-titulo" style="color:var(--danger-color);margin-bottom:12px;">Confirmar eliminación</h3>
    <p id="modal-eliminar-mensaje">¿Estás seguro de eliminar este elemento?</p>
    <div class="form-actions" style="margin-top:18px;display:flex;gap:12px;justify-content:center;">
      <button class="btn btn-danger" id="btnConfirmarEliminar">Eliminar</button>
      <button class="btn btn-cancelar" type="button" onclick="cerrarModalEliminar()">Cancelar</button>
    </div>
  </div>
</div>

<script>
// Estilos CSS para modales (añadir dinámicamente)
const modalStyles = `
<style>
.custom-modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    align-items: center;
    justify-content: center;
}

.custom-modal .modal-content {
    background-color: white;
    margin: auto;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    max-width: 500px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    position: relative;
}

.close-modal {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    position: absolute;
    right: 10px;
    top: 10px;
}

.close-modal:hover,
.close-modal:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

.custom-modal .form-group {
    margin-bottom: 15px;
}

.custom-modal .form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.custom-modal .form-input, 
.custom-modal .form-textarea {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-sizing: border-box;
}

.custom-modal .form-textarea {
    height: 80px;
    resize: vertical;
}

.custom-modal .form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
}

.custom-modal .btn {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}

.custom-modal .btn-primary {
    background-color: #007bff;
    color: white;
}

.custom-modal .btn-danger {
    background-color: #dc3545;
    color: white;
}

.custom-modal .btn-cancelar {
    background-color: #6c757d;
    color: white;
}

.custom-modal .btn:hover {
    opacity: 0.9;
}
</style>
`;

// Insertar estilos en el head
document.head.insertAdjacentHTML('beforeend', modalStyles);

function abrirModalEditar(id) {
    console.log('Abriendo modal para editar producto ID:', id);
    
    fetch('../backend/SELECTS/obtenerProducto.php?id=' + id)
        .then(res => {
            if (!res.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return res.json();
        })
        .then(prod => {
            console.log('Datos del producto:', prod);
            
            const modal = document.getElementById('modalEditarProducto');
            if (!modal) {
                console.error('Modal no encontrado');
                return;
            }
            
            // Rellenar campos del formulario
            document.getElementById('edit-id').value = prod.id || '';
            document.getElementById('edit-nombre').value = prod.nombre || '';
            document.getElementById('edit-descripcion').value = prod.descripcion || '';
            document.getElementById('edit-precio').value = prod.precio || '';
            document.getElementById('edit-stock').value = prod.stock || '';
            
            // Seleccionar categoría
            const categoriaSelect = document.getElementById('edit-categoria');
            if (categoriaSelect && prod.categoria_id) {
                categoriaSelect.value = prod.categoria_id;
            }
            
            // Mostrar imagen preview si existe
            const preview = document.getElementById('edit-preview');
            if (preview) {
                preview.src = prod.imagen ? '../../' + prod.imagen : '';
                preview.style.display = prod.imagen ? 'block' : 'none';
            }
            
            // Mostrar modal
            modal.style.display = 'flex';
        })
        .catch(error => {
            console.error('Error al cargar datos del producto:', error);
            alert('Error al cargar los datos del producto: ' + error.message);
        });
}

function cerrarModalEditar() {
    const modal = document.getElementById('modalEditarProducto');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Confirmar eliminación de producto
let idProductoEliminar;
function confirmarEliminar(id, nombre) {
    console.log('Confirmando eliminación del producto ID:', id, 'Nombre:', nombre);
    
    idProductoEliminar = id;
    
    const modal = document.getElementById('modalConfirmarEliminar');
    const mensaje = document.getElementById('modal-eliminar-mensaje');
    
    if (modal && mensaje) {
        mensaje.innerText = '¿Estás seguro de eliminar el producto "' + nombre + '"?';
        modal.style.display = 'flex';
    } else {
        console.error('Modal de confirmación no encontrado');
    }
}

function cerrarModalEliminar() {
    const modal = document.getElementById('modalConfirmarEliminar');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Event listeners cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Botón confirmar eliminación
    const btnConfirmar = document.getElementById('btnConfirmarEliminar');
    if (btnConfirmar) {
        btnConfirmar.onclick = function() {
            if (idProductoEliminar) {
                window.location.href = '?eliminar=' + idProductoEliminar;
            }
        };
    }
    
    // Formulario de edición
    const formEditar = document.getElementById('formEditarProducto');
    if (formEditar) {
        formEditar.onsubmit = function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('../backend/CRUD/PRODUCTO/editProd.php', {
                method: 'POST',
                body: formData
            })
            .then(r => {
                if (!r.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return r.json();
            })
            .then(data => {
                if(data.success){
                    alert('Producto actualizado exitosamente');
                    location.reload();
                } else {
                    alert('Error al editar el producto: ' + (data.mensaje || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión: ' + error.message);
            });
        };
    }
    
    // Cerrar modales al hacer clic fuera de ellos
    window.onclick = function(event) {
        const modalEditar = document.getElementById('modalEditarProducto');
        const modalEliminar = document.getElementById('modalConfirmarEliminar');
        
        if (event.target === modalEditar) {
            cerrarModalEditar();
        }
        if (event.target === modalEliminar) {
            cerrarModalEliminar();
        }
    };
});

// Filtro por categoría
document.addEventListener('DOMContentLoaded', function() {
    const filtroCategoria = document.getElementById('categoriaFiltro');
    if (filtroCategoria) {
        filtroCategoria.addEventListener('change', function() {
            const catId = this.value;
            document.querySelectorAll('.producto-card').forEach(card => {
                if (!catId || card.getAttribute('data-categoria-id') === catId) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});
</script>

<!-- Scripts para header dinámico -->
<script src="../../JS/usuarioSesion.js"></script>
<script>
// Configurar sesión manualmente ya que tenemos acceso a los datos PHP
document.addEventListener('DOMContentLoaded', function() {
    // Esperar a que usuarioSesion esté disponible
    setTimeout(() => {
        if (window.usuarioSesion) {
            usuarioSesion.establecerSesion(
                true, // flagSesion - siempre true en esta página (requiere login)
                <?php echo ($_SESSION['rol'] === 'admin') ? 1 : 0; ?>, // admin
                '<?php echo htmlspecialchars($_SESSION['usuario']); ?>' // usuario
            );
        }
    }, 100);
});
</script>

    </main>

</body>
</html>
