<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - Sistema de Pedidos</title>
    <!-- Bootstrap 5 vía CDN para no lidiar con archivos locales aún -->
    <link href="/css/bootstrap.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="/img/favicon.png?v=2">
    <style>
        /* imagen de fondo*/
        .bg-login-image {
            background-image: url('https://images.unsplash.com/photo-1550547660-d9450f859349?q=80&w=1000&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
        }
    </style>
</head>
<body>
    <!-- Contenedor que ocupa el 100% de la altura de la ventana -->
    <div class="container-fluid vh-100">
        <div class="row h-100">
            
            <!-- Columna Izquierda: Imagen (Desaparece en celulares con d-none d-md-block) -->
            <div class="col-md-6 d-none d-md-block bg-login-image"></div>
            
            <!-- Columna Derecha: Formulario centrado vertical y horizontalmente -->
            <div class="col-md-6 d-flex align-items-center justify-content-center bg-light">
                
                <div class="w-75" style="max-width: 400px;">
                    <div class="text-center mb-5">
                        <h2 class="fw-bold">Panel de Operaciones</h2>
                        <p class="text-muted">Ingresa tus credenciales de empleado</p>
                    </div>
                    
                    <!--Alerta de error dinámica-->
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger text-center" role="alert">
                            Usuario o contraseña incorrectos.
                        </div>
                    <?php endif; ?>

                    <!--El formulario apunta directamente al controlador-->
                    <form action="/controller/authController.php" method="POST">
                        
                        <div class="mb-3">
                            <label for="usuario" class="form-label fw-semibold">Usuario</label>
                            <input type="text" class="form-control form-control-lg" id="usuario" name="usuario" required autofocus>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">Contraseña</label>
                            <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-dark btn-lg">Iniciar Sesión</button>
                        </div>
                        
                    </form>
                </div>

            </div>
        </div>
    </div>
</body>
</html>