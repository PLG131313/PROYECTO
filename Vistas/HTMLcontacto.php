<!DOCTYPE html>
<html>
<head>
    <title>Contacto - AeroLine</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        /*pongo imagen para poder meterle mejor los estilos */
        .contact-header {
            background: rgba(0, 0, 0, 0.6) url('../img/avion.jpg') center/cover;
            color: white;
            padding: 60px 0;
            text-align: center;
        }


        .contact-card {
            border-radius: 10px;
            transition: transform 0.3s;/*se mueve hacia arriba*/
            height: 100%;
        }

        /*se mueve hacia arriba*/
        .contact-card:hover {
            transform: translateY(-5px);
        }


        .contact-icon {
            font-size: 2rem;/*tamaño letra*/
            color: #0d6efd;
            margin-bottom: 10px;
        }

        /* formulario */
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 5px rgba(13, 110, 253, 0.5);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="../index.php">✈️ AeroLine</a>
    </div>
</nav>


<header class="contact-header">
    <div class="container">
        <h1>Contáctanos</h1>
        <p>Estamos aquí para ayudarte con cualquier consulta o sugerencia</p>
    </div>
</header>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <!-- Teléfono -->
            <div class="col-md-4">
                <div class="card contact-card shadow-sm text-center p-3">
                    <i class="fas fa-phone contact-icon"></i>
                    <h3>Teléfono</h3>
                    <p>Atención 24/7</p>
                    <a class="btn btn-outline-primary">+1 234 567 890</a>
                </div>
            </div>

            <!-- Mail -->
            <div class="col-md-4">
                <div class="card contact-card shadow-sm text-center p-3">
                    <i class="fas fa-envelope contact-icon"></i>
                    <h3>Email</h3>
                    <p>Respuesta en 24 horas</p>
                    <a class="btn btn-outline-primary">info@aeroline.com</a>
                </div>
            </div>

            <!-- Ubi -->
            <div class="col-md-4">
                <div class="card contact-card shadow-sm text-center p-3">
                    <i class="fas fa-location-dot contact-icon"></i>
                    <h3>Ubicación</h3>
                    <p>Oficina Central</p>
                    <address>13630 Aeropuerto de Socuéllamos, España</address>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- formulario -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <h2>Envíanos un mensaje</h2>
                <form id="contactForm" action="../php/procesar_contacto.php" method="POST">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre completo</label>
                        <input type="text" class="form-control" id="name" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="subject" class="form-label">Asunto</label>
                        <input type="text" class="form-control" id="subject" name="asunto" required>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Mensaje</label>
                        <textarea class="form-control" id="message" name="mensaje" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Enviar mensaje</button>
                </form>
            </div>

            <!-- codigo google maps -->
            <div class="col-lg-6">
                <h2>Nuestra ubicación</h2>
                <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d9528.885506936715!2d-2.781757654848354!3d39.284242774896!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1ses!2ses!4v1738841047596!5m2!1ses!2ses" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </div>
</section>

<footer class="bg-dark text-light py-4 text-center">
    <div class="container">
        <p>&copy; 2025 AeroLine - Sistema de Gestión de Vuelos</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
