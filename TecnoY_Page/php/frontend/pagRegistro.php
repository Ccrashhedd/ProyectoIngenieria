<?php
session_start();
include '../backend/CONEXION/conexion.php';

// Redirigir si ya está logueado
if (isset($_SESSION['usuario'])) {
    if ($_SESSION['rol'] === 'admin') {
        header("Location: pag_adm.php");
    } else {
        header("Location: pag_user.php");
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Tecno Y</title>
    <link rel="stylesheet" href="../../css/login.css">
</head>
<body>
    <header class="headCont">
        <div class="login-container">
            <div class="login-header">
                <img src="../../image/logo2.png" alt="Logo Epsilon" style="width:80px;display:block;margin:0 auto 10px auto;">
                <h1 class="login-title">Tecno Y</h1>
                <p>Crear nueva cuenta</p>
            </div>
        </div>
    </header>

    <main class="mainCont">
        <div class="formCont">
            <form class="formLogin" id="registroForm">
                <h1>Registro</h1>
                
                <div id="errorMessage" class="error-message" style="display: none;"></div>
                <div id="successMessage" class="success-message" style="display: none;"></div>

                <div class="inputCont">
                    <label for="idUsuario">ID de Usuario:</label>
                    <input type="text" id="idUsuario" name="idUsuario" required 
                           maxlength="20" placeholder="Ej: user123">
                </div>
                
                <div class="inputCont">
                    <label for="nombUsuario">Nombre Completo:</label>
                    <input type="text" id="nombUsuario" name="nombUsuario" required 
                           maxlength="50" placeholder="Ej: Juan Pérez">
                </div>
                
                <div class="inputCont">
                    <label for="emailUsuario">Correo Electrónico:</label>
                    <input type="email" id="emailUsuario" name="emailUsuario" required 
                           maxlength="50" placeholder="Ej: usuario@email.com">
                </div>
                
                <div class="inputCont">
                    <label for="passUsuario">Contraseña:</label>
                    <input type="password" id="passUsuario" name="passUsuario" required 
                           minlength="6" maxlength="50" placeholder="Mínimo 6 caracteres">
                </div>
                
                <div class="inputCont">
                    <label for="confirmarPass">Confirmar Contraseña:</label>
                    <input type="password" id="confirmarPass" name="confirmarPass" required 
                           minlength="6" maxlength="50" placeholder="Repita la contraseña">
                </div>
                
                <button type="submit" id="registroBtn">Registrarse</button>

                <div class="opcionesCuenta">
                    <a href="login.php">Ya tengo cuenta - Iniciar Sesión</a>
                </div>
                
                <div class="link-volver">
                    <a href="landingPage.php" class="logout-link">Regresar como invitado</a>
                </div>
            </form>
        </div>
    </main>

    <footer class="footCont">
        <div class="footer-content">
            <p>© 2024 TechStore - Tecnología de Vanguardia</p>
            <p>Proyecto educativo - DS6 | Universidad Tecnológica de Panamá</p>
        </div>
    </footer>

    <script src="../../JS/registro.js"></script>

</body>
</html>
