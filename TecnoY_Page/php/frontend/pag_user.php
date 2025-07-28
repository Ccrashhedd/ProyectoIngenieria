<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'usuario') {
    header("Location: login.php");
    exit;
}
include '../backend/CONEXION/conexion.php';

// Obtener categorías para navegación
try {
    $stmt = $conn->prepare("SELECT idCategoria as id, nombCategoria as nombre, imagen FROM CATEGORIA ORDER BY nombCategoria");
    $stmt->execute();
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $categorias = [];
    error_log("Error al cargar categorías: " . $e->getMessage());
}

// Obtener productos destacados (últimos 6)
try {
    $stmt = $conn->prepare("
        SELECT p.idProducto as id, p.nombProducto as nombre, p.precio, p.imagen, 
               c.nombCategoria as categoria, m.nombMarca as marca
        FROM PRODUCTO p 
        INNER JOIN CATEGORIA c ON p.idCategoria = c.idCategoria 
        INNER JOIN MARCA m ON p.idMarca = m.idMarca 
        ORDER BY p.idProducto DESC 
        LIMIT 6
    ");
    $stmt->execute();
    $productos_destacados = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $productos_destacados = [];
    error_log("Error al cargar productos destacados: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Usuario - Tecno Y</title>
    <link rel="stylesheet" href="../../css/landingPage.css">
    <link rel="stylesheet" href="../../css/headerDinamico.css">
</head>
<body>
    <!--Header-->
    <header class="header">
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
    <main class="main-content">
        <section class="welcome-section">
            <div class="welcome-container">
                <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre_usuario'] ?? $_SESSION['usuario']); ?>!</h1>
                <p>Explora nuestros productos y categorías</p>
            </div>
        </section>

        <!-- Navegación rápida -->
        <section class="quick-nav">
            <div class="nav-container">
                <h2>¿Qué estás buscando?</h2>
                <div class="nav-buttons">
                    <a href="productos.php" class="nav-btn primary">Ver Todos los Productos</a>
                    <a href="categorias.php" class="nav-btn secondary">Explorar por Categorías</a>
                    <a href="carrito.php" class="nav-btn tertiary">Mi Carrito</a>
                </div>
            </div>
        </section>

        <!-- Categorías -->
        <section class="categorias-section">
            <div class="section-container">
                <h2>Categorías</h2>
                <div class="categorias-grid">
                    <?php foreach ($categorias as $categoria): ?>
                    <div class="categoria-card">
                        <div class="categoria-image">
                            <?php if ($categoria['imagen']): ?>
                                <img src="../../<?= htmlspecialchars($categoria['imagen']) ?>" 
                                     alt="<?= htmlspecialchars($categoria['nombre']) ?>">
                            <?php else: ?>
                                <div class="placeholder-image">📱</div>
                            <?php endif; ?>
                        </div>
                        <h3><?= htmlspecialchars($categoria['nombre']) ?></h3>
                        <a href="productos.php?categoria=<?= urlencode($categoria['id']) ?>" class="categoria-link">Ver Productos</a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Productos destacados -->
        <section class="productos-destacados">
            <div class="section-container">
                <h2>Productos Destacados</h2>
                <div class="productos-grid">
                    <?php foreach ($productos_destacados as $producto): ?>
                    <div class="producto-card">
                        <div class="producto-image">
                            <?php if ($producto['imagen']): ?>
                                <img src="../../<?= htmlspecialchars($producto['imagen']) ?>" 
                                     alt="<?= htmlspecialchars($producto['nombre']) ?>">
                            <?php else: ?>
                                <div class="placeholder-image">📷</div>
                            <?php endif; ?>
                        </div>
                        <div class="producto-info">
                            <h3><?= htmlspecialchars($producto['nombre']) ?></h3>
                            <p class="producto-categoria"><?= htmlspecialchars($producto['categoria']) ?> - <?= htmlspecialchars($producto['marca']) ?></p>
                            <p class="producto-precio">$<?= number_format($producto['precio'], 2) ?></p>
                            <button class="btn-agregar-carrito" data-id="<?= $producto['id'] ?>">Agregar al Carrito</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Información adicional -->
        <section class="info-section">
            <div class="section-container">
                <div class="info-grid">
                    <div class="info-card">
                        <h3>🚚 Envío Gratis</h3>
                        <p>En compras superiores a $50</p>
                    </div>
                    <div class="info-card">
                        <h3>🔒 Compra Segura</h3>
                        <p>Tus datos están protegidos</p>
                    </div>
                    <div class="info-card">
                        <h3>🎯 Productos Originales</h3>
                        <p>Garantía de autenticidad</p>
                    </div>
                    <div class="info-card">
                        <h3>📞 Soporte 24/7</h3>
                        <p>Estamos aquí para ayudarte</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <p>© 2024 TechStore - Tecnología de Vanguardia</p>
            <p>Proyecto educativo - DS6 | Universidad Tecnológica de Panamá</p>
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
                        true, // flagSesion - siempre true en esta página (requiere login)
                        <?php echo ($_SESSION['rol'] === 'admin') ? 1 : 0; ?>, // admin
                        '<?php echo htmlspecialchars($_SESSION['usuario']); ?>' // usuario
                    );
                }
            }, 100);

            // Funcionalidad básica del carrito (ejemplo)
            document.querySelectorAll('.btn-agregar-carrito').forEach(btn => {
                btn.addEventListener('click', function() {
                    const productoId = this.dataset.id;
                    // Aquí irían las funciones de carrito
                    alert('Producto agregado al carrito (funcionalidad pendiente)');
                });
            });
        });
    </script>

    <style>
        .welcome-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 0;
            text-align: center;
        }

        .welcome-container h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .quick-nav {
            padding: 2rem 0;
            background: #f8f9fa;
        }

        .nav-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .nav-btn {
            padding: 0.8rem 2rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-btn.primary {
            background: #007bff;
            color: white;
        }

        .nav-btn.secondary {
            background: #28a745;
            color: white;
        }

        .nav-btn.tertiary {
            background: #ffc107;
            color: #212529;
        }

        .categorias-grid, .productos-grid {
            display: grid;
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .categorias-grid {
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        }

        .productos-grid {
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        }

        .categoria-card, .producto-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 1rem;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .categoria-card:hover, .producto-card:hover {
            transform: translateY(-5px);
        }

        .categoria-image img, .producto-image img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 4px;
        }

        .placeholder-image {
            width: 100%;
            height: 120px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            border-radius: 4px;
        }

        .categoria-link, .btn-agregar-carrito {
            background: #007bff;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .categoria-link:hover, .btn-agregar-carrito:hover {
            background: #0056b3;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .info-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
        }

        .section-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .producto-precio {
            font-size: 1.2rem;
            font-weight: bold;
            color: #28a745;
            margin: 0.5rem 0;
        }

        .producto-categoria {
            color: #666;
            font-size: 0.9rem;
        }
    </style>

</body>
</html>
