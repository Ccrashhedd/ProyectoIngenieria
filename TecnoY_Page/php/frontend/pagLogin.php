<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - TecnoY</title>
    <link rel="stylesheet" href="../../css/login.css">
</head>
<body>
    <div class="particles"></div>
    
    <main class="login-main">
        <div class="login-container">
            <div class="login-header">
                <img src="../../image/logo2.png" alt="Logo TecnoY">
                <h1 class="login-title">TecnoY</h1>
                <p class="login-subtitle">Tecnología de Vanguardia</p>
                
                <div class="admin-info">
                    <strong>🧪 Credenciales de prueba:</strong><br>
                    <strong>Admin:</strong> <code>admin</code> / <code>admin123</code><br>
                    <strong>Usuario:</strong> <code>user1</code> / <code>user123</code>
                </div>
            </div>

            <!-- Área de mensajes -->
            <div id="messageArea"></div>

            <!-- Formulario de login -->
            <form id="loginForm">
                <div class="form-group">
                    <input 
                        type="text" 
                        id="userIn" 
                        name="userIn" 
                        class="form-input"
                        required
                        placeholder="Usuario o Email"
                        autocomplete="username"
                    >
                </div>

                <div class="form-group">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input"
                        required
                        placeholder="Contraseña"
                        autocomplete="current-password"
                    >
                </div>

                <button type="submit" id="loginBtn" class="login-button">
                    Iniciar Sesión
                </button>
            </form>

            <!-- Enlaces adicionales -->
            <div class="login-links">
                <a href="nuevoUsuario.php" class="register-link">
                    ¿No tienes cuenta? Registrarse
                </a>
                <a href="landingPage.php" class="logout-link">← Volver al inicio</a>
            </div>
        </div>
    </main>

    <footer class="footer-login">
        <div class="footer-content">
            <p>© 2024 TecnoY - Tecnología de Vanguardia</p>
            <p>Proyecto educativo - DS6 | Universidad Tecnológica de Panamá</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="../../JS/login.js"></script>
    
    <!-- Script para partículas -->
    <script>
        // Crear partículas flotantes
        function createParticles() {
            const particlesContainer = document.querySelector('.particles');
            for (let i = 0; i < 50; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 6 + 's';
                particle.style.animationDuration = (Math.random() * 3 + 3) + 's';
                particlesContainer.appendChild(particle);
            }
        }
        
        // Crear partículas cuando se cargue la página
        document.addEventListener('DOMContentLoaded', createParticles);
    </script>
</body>
</html>
