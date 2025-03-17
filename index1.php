<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AeroLine - Sistema de Gestión de Vuelos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)),
            url('img/alaAvion.jpg') no-repeat center center;
            background-size: cover;
            height: 100vh;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
        }
        .nav-tabs .nav-link.active {
            font-weight: bold;
            color: #0d6efd;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="#">✈️ AeroLine</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">

                <li class="nav-item">
                    <a class="nav-link" href="Vistas/HTMLcontacto.php">Contacto</a>
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
                    <ul class="nav nav-tabs mb-4" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" id="client-tab" data-bs-toggle="tab" data-bs-target="#client-login">
                                <i class="fas fa-user"></i> Cliente
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="pilot-tab" data-bs-toggle="tab" data-bs-target="#pilot-login">
                                <i class="fas fa-plane"></i> Administrador
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- Formulario Cliente -->
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
                                <button type="button" onclick="loginClient()" class="btn btn-primary w-100">Iniciar Sesión</button><br><br>
                            </form>

                            <div style="text-align: center"><a href="Vistas/HTMLregistro.php">Regístrate</a></div>
                            <div id="clientLoginMessage" class="mt-3 text-center"></div>
                        </div>

                        <!-- Formulario Administrador -->
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
                                <button type="button" onclick="loginPilot()" class="btn btn-primary w-100">Iniciar Sesión</button>
                            </form>

                            <div id="pilotLoginMessage" class="mt-3 text-center"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="bg-dark text-light py-4">
    <div class="container text-center">
        <p>&copy; 2025 AeroLine - Sistema de Gestión de Vuelos</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
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
                document.getElementById("clientLoginMessage").innerHTML = data;
                if (data == "0") {
                    window.location.href = "Vistas/HTMLcliente.php";
                }
            });
    }

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
                document.getElementById("pilotLoginMessage").innerHTML = data;
                if (data == "0") {
                    window.location.href = "php/panel_piloto.php";
                }
            });
    }
</script>
    </body>
</html>
