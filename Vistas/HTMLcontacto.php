
<html><head>
    <title>Contacto - AeroLine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .contact-header {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)),
            url('../img/avion.jpg') no-repeat center center;
            background-size: cover;
            color: white;
            padding: 60px 0;
        }

        .contact-card {
            border-radius: 15px;
            transition: transform 0.3s ease;
            height: 100%;
        }

        .contact-card:hover {
            transform: translateY(-5px);
        }

        .map-container {
            height: 400px;
            border-radius: 15px;
            overflow: hidden;
        }

        .contact-icon {
            font-size: 2.5rem;
            color: #0d6efd;
            margin-bottom: 1rem;
        }

        .floating {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
            border-color: #0d6efd;
        }
    </style>
</head>
<body>
<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="../index1.php">
            ✈️ AeroLine
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">

                <li class="nav-item">
                    <a class="nav-link active" href="#">Contacto</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Header -->
<header class="contact-header text-center">
    <div class="container">
        <h1 class="display-4">Contáctanos</h1>
        <p class="lead">Estamos aquí para ayudarte con cualquier consulta o sugerencia</p>
    </div>
</header>

<!-- Contact Information Cards -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card contact-card shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-phone contact-icon floating"></i>
                        <h3>Teléfono</h3>
                        <p>Atención al cliente 24/7</p>
                        <a class="btn btn-outline-primary">+1 234 567 890</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card contact-card shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-envelope contact-icon floating"></i>
                        <h3>Email</h3>
                        <p>Respuesta en 24 horas</p>
                        <a class="btn btn-outline-primary">info@aeroline.com</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card contact-card shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-location-dot contact-icon floating"></i>
                        <h3>Ubicación</h3>
                        <p>Oficina Central</p>
                        <address class="mb-0">
                            13630 Aeropuerto de Socuellamos<br>
                            Ciudad Aeropuerto Socuellamos, CP 13630
                        </address>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <h2 class="mb-4">Envíanos un mensaje</h2>
                <form id="contactForm" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre completo</label>
                        <input type="text" class="form-control" id="name" required>
                        <div class="invalid-feedback">
                            Por favor ingresa tu nombre
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" required>
                        <div class="invalid-feedback">
                            Por favor ingresa un email válido
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="subject" class="form-label">Asunto</label>
                        <input type="text" class="form-control" id="subject" required>
                        <div class="invalid-feedback">
                            Por favor ingresa el asunto
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Mensaje</label>
                        <textarea class="form-control" id="message" rows="5" required></textarea>
                        <div class="invalid-feedback">
                            Por favor ingresa tu mensaje
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Enviar mensaje</button>
                </form>
            </div>
            <div class="col-lg-6">
                <h2 class="mb-4">Nuestra ubicación</h2>
                <div class="map-container">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d9528.885506936715!2d-2.781757654848354!3d39.284242774896!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1ses!2ses!4v1738841047596!5m2!1ses!2ses"
                            width="600"
                            height="450"
                            style="border:0;"
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</section>



<!-- Footer -->
<footer class="bg-dark text-light py-4">
    <div class="container text-center">
        <p>&copy; 2025 AeroLine - Sistema de Gestión de Vuelos</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

