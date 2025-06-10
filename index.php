<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AeroLine - Sistema de Gestión de Vuelos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        /* imagen  */
        /*la pongo aqui para poder tocarle mas facilmente el estilo*/
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)),
            url('img/alaAvion.jpg') no-repeat center center;
            background-size: cover;
            height: 100vh; /* toda la pantalla */
        }


        .login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2); /* Sombra */
        }

        /* pagina actva */
        .nav-tabs .nav-link.active {
            font-weight: bold; /* negrita */
            color: #0d6efd; /
        }
    </style>
</head>
<body>

<!-- Barra de navegación -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="#">✈️ AeroLine</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="Vistas/HTMLcontacto.php">Contacto</a> <!-- contacto -->
                </li>
            </ul>
        </div>
    </div>
</nav>

<section class="hero-section d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="login-container">
                    <!-- Pestañas de inicio de sesión -->
                    <ul class="nav nav-tabs mb-4" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" id="client-tab" data-bs-toggle="tab" data-bs-target="#client-login">
                                <i class="fas fa-user"></i> Cliente
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="pilot-tab" data-bs-toggle="tab" data-bs-target="#pilot-login">
                                <i class="fas fa-plane"></i> Piloto
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- Formulario de inicio de sesión para clientes -->
                        <div class="tab-pane fade show active" id="client-login">
                            <form id="clientLoginForm">
                                <div class="mb-3">
                                    <label for="clientEmail" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="clientEmail" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="clientPassword" class="form-label">Contraseña</label>
                                    <input type="password" class="form-control" id="clientPassword" name="contrasena" required>
                                </div>
                                <button type="button" onclick="loginClient()" class="btn btn-primary w-100">Iniciar Sesión</button> <!-- inicio cliente -->
                                <br><br>
                            </form>

                            <!-- Registro -->
                            <div style="text-align: center"><a href="Vistas/HTMLregistro.php">Regístrate</a></div>
                            <div id="clientLoginMessage" class="mt-3 text-center"></div> <!-- respuesta -->
                        </div>

                        <!-- Piloto -->
                        <div class="tab-pane fade" id="pilot-login">
                            <form id="pilotLoginForm">
                                <div class="mb-3">
                                    <label for="pilotId" class="form-label">Usuario</label>
                                    <input type="text" class="form-control" id="pilotId" name="pilotId" required>
                                </div>
                                <div class="mb-3">
                                    <label for="pilotPassword" class="form-label">Contraseña</label>
                                    <input type="password" class="form-control" id="pilotPassword" name="contrasena" required>
                                </div>
                                <button type="button" onclick="loginPilot()" class="btn btn-primary w-100">Iniciar Sesión</button> <!-- inicio piloto -->
                            </form>

                            <div id="pilotLoginMessage" class="mt-3 text-center"></div> <!-- respuesta -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Frase para abrir el login de administrador -->
<div style="position: fixed; bottom: 10px; right: 10px; font-size: 13px;">
    <a href="#" class="text-white text-decoration-none" data-bs-toggle="modal" data-bs-target="#adminLoginModal">
        ¿Eres administrador? Pincha aquí.
    </a>
</div>

<!-- Modal de login de administrador -->
<div class="modal fade" id="adminLoginModal" tabindex="-1" aria-labelledby="adminLoginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow">
            <div class="modal-header">
                <h5 class="modal-title" id="adminLoginModalLabel">
                    <i class="fas fa-user-shield"></i> Acceso de Administrador
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="adminLoginForm">
                    <div class="mb-3">
                        <label for="adminId" class="form-label">ID de Administrador</label>
                        <input type="text" class="form-control" id="adminId" name="adminId" required>
                    </div>
                    <div class="mb-3">
                        <label for="adminPassword" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="adminPassword" name="adminPassword" required>
                    </div>
                    <div id="adminLoginMessage" class="mt-3 text-center"></div> <!-- respuesta -->
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" onclick="loginAdmin()" class="btn btn-primary">Iniciar Sesión</button>
            </div>
        </div>
    </div>
</div>

<!-- Pie de página -->
<footer class="bg-dark text-light py-4">
    <div class="container text-center">
        <p>&copy; 2025 AeroLine - Sistema de Gestión de Vuelos</p>
    </div>
</footer>

<!-- Scripts de Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Función para iniciar sesión como cliente
    function loginClient() {
        const formData = new FormData();
        formData.append("email", document.getElementById("clientEmail").value);
        formData.append("contrasena", document.getElementById("clientPassword").value);

        fetch("php/login_cliente.php", {
            method: "POST",
            body: formData
        })
            .then(response => response.text())
            .then(data => {
                document.getElementById("clientLoginMessage").innerHTML = data; // respuesta
                if (data == "0") {
                    window.location.href = "Vistas/buscar-vuelos.php"; //paso a cliente
                }
            });
    }

    // Función para iniciar sesión como piloto
    function loginPilot() {
        const formData = new FormData();
        formData.append("pilotId", document.getElementById("pilotId").value);
        formData.append("contrasena", document.getElementById("pilotPassword").value);

        fetch("php/login_piloto.php", {
            method: "POST",
            body: formData
        })
            .then(response => response.text())
            .then(data => {
                document.getElementById("pilotLoginMessage").innerHTML = data; // respuesta
                if (data == "0") {
                    window.location.href = "Vistas/panel-piloto.php"; // Cambiado a panel_piloto.php
                }
            });
    }

    // Función para iniciar sesión como administrador
    function loginAdmin() {
        const formData = new FormData();
        formData.append("adminId", document.getElementById("adminId").value);
        formData.append("adminPassword", document.getElementById("adminPassword").value);

        fetch("php/login_admin.php", {
            method: "POST",
            body: formData
        })
            .then(response => response.text())
            .then(data => {
                document.getElementById("adminLoginMessage").innerHTML = data; // respuesta
                if (data == "0") {
                    window.location.href = "php/panel_admin.php"; // Redirige al panel de administrador
                }
            });
    }
</script>

</body>
</html>