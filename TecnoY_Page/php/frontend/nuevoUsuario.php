<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <header class="headCont">

    </header>

    <main class="mainCont">
        <div class="genCont">
            <div class="formCont">
                <form class="nuevoUsuarioForm" action="../Backend/USUARIO/crearUsuario.php" method="post">
                    <h1>Nuevo Usuario</h1>
                    <div class="inputCont">
                        <label for="nombre">Nombre:</label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>
                    <div class="inputCont">
                        <label for="correo">Correo Electrónico:</label>
                        <input type="email" id="correo" name="correo" required>
                    </div>
                    <div class="inputCont">
                        <label for="usuario">Usuario:</label>
                        <input type="text" id="usuario" name="usuario" required>
                    </div>
                    <div class="inputCont">
                        <label for="contrasena">Contraseña:</label>
                        <input type="password" id="contrasena" name="contrasena" required>
                    </div>
                    <button type="submit">Crear Cuenta</button>

                </form>

            </div>

        </div>

    </main>


    <footer class="footCont">

    </footer>
    
</body>
</html>