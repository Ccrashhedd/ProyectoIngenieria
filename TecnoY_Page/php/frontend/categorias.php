<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit;
}
include '../backend/CONEXION/conexion.php';

// Manejar eliminación por GET (para compatibilidad con modales)
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    // Redirigir al archivo CRUD correspondiente
    header("Location: ../backend/CRUD/CATEGORIA/elimCat.php?id=" . $id);
    exit;
}

// Listar categorías usando nueva estructura
try {
    $stmt = $conn->prepare("SELECT idCategoria as id, nombCategoria as nombre, imagen FROM CATEGORIA ORDER BY nombCategoria");
    $stmt->execute();
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $categorias = [];
    $error_msg = "Error al cargar categorías: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Categorías</title>
    <link rel="stylesheet" href="../../css/categorias.css">
    <link rel="stylesheet" href="../../css/headerDinamico.css">
    <link rel="stylesheet" href="../../css/headerAdmin.css">
    <link rel="stylesheet" href="../../css/adminComponents.css">
    <link rel="stylesheet" href="../../css/modales.css">
</head>
<body>
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
    <main class="categorias-main">

<div class="categorias-container">
    <div class="back-nav">
        <a href="pag_adm.php" class="back-button">⟵ Volver</a>
    </div>
    <h2 class="page-title">Categorías</h2>
    <form method="POST" enctype="multipart/form-data" class="form-categoria" id="form-categoria" action="../backend/CRUD/CATEGORIA/addCat.php">
        <div class="form-group">
            <label class="form-label">Nombre de la categoría:</label>
            <input type="text" name="nombre" class="form-input" required>
        </div>
        <div class="form-group">
            <label class="form-label">Imagen:</label>
            <input type="file" name="imagen" class="form-input" accept="image/*">
        </div>
        <div class="form-actions">
            <button type="button" class="btn btn-primary" onclick="abrirModalAgregarCategoria()">Agregar Categoría</button>
            <button type="button" class="btn btn-cancelar" onclick="cancelarOperacion()">Cancelar</button>
        </div>
    </form>

    <h3 class="section-title">Listado de categorías</h3>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Categoría agregada exitosamente</div>
    <?php endif; ?>
    
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Categoría eliminada exitosamente</div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>
    
    <?php if (isset($error_msg)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>
    
    <ul class="categories-list">
        <?php foreach ($categorias as $cat): ?>
            <li class="category-card">
                <div class="category-img">
                    <?php if ($cat['imagen']): ?>
                        <img src="../../<?= htmlspecialchars($cat['imagen']) ?>" alt="<?= htmlspecialchars($cat['nombre']) ?>">
                    <?php endif; ?>
                </div>
                <div class="category-info">
                    <span class="category-name"><?= htmlspecialchars($cat['nombre']) ?></span>
                </div>
                <div class="category-actions">
                    <button type="button" class="btn btn-warning btn-small" title="Editar"
                        onclick="abrirModalEditarCategoria(<?= htmlspecialchars(json_encode($cat['id'])) ?>, <?= htmlspecialchars(json_encode($cat['nombre'])) ?>, <?= htmlspecialchars(json_encode($cat['imagen'])) ?>)">&#9998;</button>
                    <button type="button" class="btn btn-danger btn-small"
                        onclick="mostrarModalEliminar(<?= htmlspecialchars(json_encode($cat['id'])) ?>, <?= htmlspecialchars(json_encode($cat['nombre'])) ?>)"
                        title="Eliminar">&#128465;</button>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<!-- Modal de edición de categoría -->
<div id="modalEditarCategoria" class="custom-modal" style="display:none;">
  <div class="modal-content">
    <span class="close-modal" onclick="cerrarModalEditarCategoria()">&times;</span>
    <h3>Editar categoría</h3>
    <form id="formEditarCategoria" enctype="multipart/form-data">
      <input type="hidden" name="id" id="edit-cat-id">
      <div class="form-group">
        <label>Nombre:</label>
        <input type="text" name="nombre" id="edit-cat-nombre" class="form-input" required>
      </div>
      <div class="form-group">
        <label>Imagen:</label>
        <input type="file" name="imagen" id="edit-cat-imagen" class="form-input" accept="image/*">
        <img id="edit-cat-preview" src="" alt="Vista previa" class="img-preview-modal">
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
        <button type="button" class="btn btn-cancelar" onclick="cerrarModalEditarCategoria()">Cancelar</button>
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

<!-- Modal de confirmación para agregar -->
<div id="modalConfirmarAgregar" class="custom-modal" style="display:none;">
  <div class="modal-content" style="max-width:340px;">
    <span class="close-modal" onclick="cerrarModalAgregarCategoria()">&times;</span>
    <h3 style="color:var(--primary-color);margin-bottom:12px;">Confirmar agregar</h3>
    <p>¿Estás seguro de agregar esta categoría?</p>
    <div class="form-actions" style="margin-top:18px;display:flex;gap:12px;justify-content:center;">
      <button class="btn btn-primary" id="btnConfirmarAgregar">Agregar</button>
      <button class="btn btn-cancelar" type="button" onclick="cerrarModalAgregarCategoria()">Cancelar</button>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="../../JS/categoriasModales.js"></script>
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

    </main>

</body>
</html>