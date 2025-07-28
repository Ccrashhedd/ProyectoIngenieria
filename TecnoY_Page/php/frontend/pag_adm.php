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
    categoriaPreview.innerHTML = img ? `<img src="${img}" alt="" style="height:60px;border-radius:8px;">` : '';

    if (!this.value) {
        productosCards.innerHTML = '';
        return;
    }

    fetch(`../../php/backend/prodXCat.php?categoria_id=${this.value}`)
        .then(res => res.json())
        .then(data => {
            if (data.length === 0) {
                productosCards.innerHTML = '<p class="empty-state">No hay productos en esta categoría.</p>';
                return;
            }
            productosCards.innerHTML = data.map(prod => `
                <div class="producto-card">
                    <img src="../../${prod.imagen}" alt="${prod.nombre}" class="producto-img" style="width:90px;height:90px;object-fit:cover;border-radius:10px;">
                    <div class="producto-info">
                        <h4>${prod.nombre}</h4>
                        <p>${prod.descripcion}</p>
                        <span class="producto-precio">$${parseFloat(prod.precio).toFixed(2)}</span>
                    </div>
                </div>
            `).join('');
        });
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
