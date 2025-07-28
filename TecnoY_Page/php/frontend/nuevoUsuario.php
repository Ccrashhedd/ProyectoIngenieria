<?php
session_start();

// Redirigir si ya está logueado
if (isset($_SESSION['usuario'])) {
    if (isset($_SESSION['id_rango']) && $_SESSION['id_rango'] == 1) {
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
    <title>Crear Nueva Cuenta - TecnoY</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="../../css/base.css">
    <link rel="stylesheet" href="../../css/registro.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../../image/logo2.png">
</head>
<body>
    <div class="registro-container">
        <div class="registro-card">
            <!-- Header -->
            <div class="registro-header">
                <div class="registro-logo">
                    <img src="../../image/logo2.png" alt="Logo TecnoY">
                </div>
                <h1 class="registro-title">TecnoY</h1>
                <p class="registro-subtitle">Crear nueva cuenta de cliente</p>
            </div>

            <!-- Área de mensajes -->
            <div id="messageArea" class="message-area"></div>

            <!-- Formulario de registro -->
            <form id="registroForm" class="registro-form" novalidate>
                
                <!-- ID de Usuario -->
                <div class="form-group">
                    <label for="idUsuario" class="form-label">
                        ID de Usuario *
                        <small>(Solo letras, números y guiones bajos)</small>
                    </label>
                    <input 
                        type="text" 
                        id="idUsuario" 
                        name="idUsuario" 
                        class="form-input"
                        required
                        maxlength="20"
                        placeholder="Ej: juan_perez, user123"
                        autocomplete="username"
                    >
                    <div class="validation-indicator success">✓</div>
                    <div class="validation-indicator error">✗</div>
                </div>

                <!-- Nombre completo -->
                <div class="form-group">
                    <label for="nombUsuario" class="form-label">
                        Nombre Completo *
                    </label>
                    <input 
                        type="text" 
                        id="nombUsuario" 
                        name="nombUsuario" 
                        class="form-input"
                        required
                        maxlength="50"
                        placeholder="Ej: Juan Pérez García"
                        autocomplete="name"
                    >
                    <div class="validation-indicator success">✓</div>
                    <div class="validation-indicator error">✗</div>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="emailUsuario" class="form-label">
                        Correo Electrónico *
                    </label>
                    <input 
                        type="email" 
                        id="emailUsuario" 
                        name="emailUsuario" 
                        class="form-input"
                        required
                        maxlength="50"
                        placeholder="Ej: juan@ejemplo.com"
                        autocomplete="email"
                    >
                    <div class="validation-indicator success">✓</div>
                    <div class="validation-indicator error">✗</div>
                </div>

                <!-- Contraseña -->
                <div class="form-group">
                    <label for="passUsuario" class="form-label">
                        Contraseña *
                        <small>(Mínimo 6 caracteres)</small>
                    </label>
                    <input 
                        type="password" 
                        id="passUsuario" 
                        name="passUsuario" 
                        class="form-input"
                        required
                        minlength="6"
                        placeholder="Mínimo 6 caracteres"
                        autocomplete="new-password"
                    >
                    <div class="validation-indicator success">✓</div>
                    <div class="validation-indicator error">✗</div>
                </div>

                <!-- Confirmar contraseña -->
                <div class="form-group">
                    <label for="confirmPassword" class="form-label">
                        Confirmar Contraseña *
                    </label>
                    <input 
                        type="password" 
                        id="confirmPassword" 
                        name="confirmPassword" 
                        class="form-input"
                        required
                        minlength="6"
                        placeholder="Repetir contraseña"
                        autocomplete="new-password"
                    >
                    <div class="validation-indicator success">✓</div>
                    <div class="validation-indicator error">✗</div>
                </div>

                <!-- Información del tipo de cuenta -->
                <div class="form-group">
                    <div class="account-type-info">
                        <p><strong>🛍️ Tipo de cuenta:</strong> Cliente/Usuario</p>
                        <small>Tendrás acceso a compras, carrito y historial de pedidos</small>
                    </div>
                </div>

                <!-- Botón de registro -->
                <button type="submit" id="submitBtn" class="btn-registro">
                    Registrar Usuario
                </button>

                <!-- Link a login -->
                <a href="pagLogin.php" class="btn-login-link">
                    ¿Ya tienes cuenta? Iniciar Sesión
                </a>
            </form>

            <!-- Footer -->
            <div class="registro-footer">
                <p>
                    Al registrarte, aceptas nuestros 
                    <a href="#" onclick="alert('Términos y condiciones en desarrollo')">términos y condiciones</a>
                </p>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="../../JS/registro.js"></script>
    
    <!-- CSS adicional para errores de campo -->
    <style>
        .field-error {
            color: var(--error-color);
            font-size: 0.8rem;
            margin-top: 5px;
            display: none;
            animation: slideIn 0.3s ease-out;
        }
        
        .account-type-info {
            background: rgba(var(--info-color-rgb), 0.1);
            border: 1px solid rgba(var(--info-color-rgb), 0.2);
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        
        .account-type-info p {
            margin: 0 0 5px 0;
            color: var(--info-color);
            font-weight: 600;
        }
        
        .account-type-info small {
            color: var(--text-muted);
            font-size: 0.85rem;
        }
        
        /* Mejorar compatibilidad con backdrop-filter */
        .registro-card {
            -webkit-backdrop-filter: blur(10px);
            backdrop-filter: blur(10px);
        }
        
        .form-input {
            -webkit-backdrop-filter: blur(5px);
            backdrop-filter: blur(5px);
            /* Forzar color del texto para asegurar visibilidad */
            color: #333333 !important;
        }
        
        .form-input:focus {
            color: #333333 !important;
            background: rgba(255, 255, 255, 0.95) !important;
        }
        
        .form-input::placeholder {
            color: #888888 !important;
        }
        
        /* Asegurar visibilidad en modo oscuro si se activa */
        @media (prefers-color-scheme: dark) {
            .form-input {
                background: rgba(40, 40, 50, 0.95) !important;
                color: #ffffff !important;
            }
            
            .form-input:focus {
                background: rgba(50, 50, 60, 1) !important;
                color: #ffffff !important;
            }
        }
    </style>
</body>
</html>