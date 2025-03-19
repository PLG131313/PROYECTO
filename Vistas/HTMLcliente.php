<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>AeroLine - Reservas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .header {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)),
            url('../img/avion.jpg') no-repeat center center;
            background-size: cover;
            color: white;
            padding: 60px 0;
            margin-bottom: 30px;
        }

        .main-container {
            display: flex;
            flex-direction: column;
            min-height: 80vh; /* 80% de la pantalla */
            justify-content: space-between;
        }

        .success-message {
            display: none;
            margin-top: 20px;
        }

        .form-container {
            flex-grow: 1;
        }

        footer {
            position: relative;
            bottom: 0;
        }

        .additional-info {
            margin-top: 50px;
            text-align: center;
        }

        /* Estilo para el texto de AeroLine con el avión */
        .airline-logo h2 {
            font-size: 36px;
            font-family: 'Arial', sans-serif;
            font-weight: bold;
            color: #ff0000; /* Azul brillante */
            letter-spacing: 2px;
            text-transform: uppercase;
            display: inline-block;
            margin-top: 20px;
            position: relative;
        }

        /* Estilo para el avión (emoji) con un pequeño efecto */
        .airline-logo h2::before {
            content: '✈️'; /* Avión antes del texto */
            position: absolute;
            top: -10px;
            left: -40px;
            font-size: 40px;
            animation: fly 2s infinite linear;
        }

        /* Animación para el avión */
        @keyframes fly {
            0% {
                transform: translateX(-20px);
            }
            50% {
                transform: translateX(20px);
            }
            100% {
                transform: translateX(-20px);
            }
        }

    </style>
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="../index.php">✈️ AeroLine</a>
        <a class="nav-link text-white" href="../index.php">Cerrar Sesión</a>
    </div>
</nav>

<!-- Header -->
<header class="header text-center">
    <h1>Bienvenido a AeroLine</h1>
    <p>Sistema de Reservas de Vuelos</p>
</header>

<!-- Contenedor principal con Flexbox -->
<div class="container mb-5 main-container">
    <div class="card form-container">
        <div class="card-body">
            <h3 class="card-title mb-4">Nueva Reserva</h3>

            <form id="bookingForm">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Ciudad Origen</label>
                        <input type="text" class="form-control" name="origin" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ciudad Destino</label>
                        <input type="text" class="form-control" name="destination" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fecha del Vuelo</label>
                        <input type="date" class="form-control" name="departureDate" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Clase</label>
                        <select class="form-control" name="flightClass" required>
                            <option value="Economico">Económica</option>
                            <option value="Clase media">Clase media</option>
                            <option value="Primera Clase">Primera Clase</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-4">Reservar Vuelo</button>
            </form>

            <!-- Mensaje de éxito -->
            <div class="success-message alert alert-success" id="successMessage">
                <h4>¡Reserva realizada con éxito!</h4>
                <p>Tu ticket se descargará automáticamente.</p>
                <button class="btn btn-success" id="downloadPdfBtn">
                    Descargar Ticket PDF
                </button>
            </div>

            <!-- Nombre y Logo de la aerolínea -->
            <div class="additional-info">
                <div class="airline-logo">
                    <h2>AeroLine</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="bg-dark text-light py-4">
    <div class="container text-center">
        <p>&copy; 2025 AeroLine - Sistema de Gestión de Vuelos</p>
    </div>
</footer>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Establecer fecha mínima para el campo de fecha
        document.querySelector('input[name="departureDate"]').min = new Date().toISOString().split('T')[0];

        // Manejar el envío del formulario
        document.getElementById('bookingForm').onsubmit = function(e) {
            e.preventDefault(); // Evitar que se recargue la página

            var form = e.target;  // Obtener el formulario
            var submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true; // Desactivar el botón de enviar

            var formData = new FormData(form); // Recoger los datos del formulario

            // Enviar los datos al servidor usando fetch
            fetch('../php/reserva.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json()) // lo paso a JSON
                .then(data => {
                    if (data.exito) {
                        // Si la reserva es exitosa
                        form.style.display = 'none'; // Ocultar el formulario
                        document.getElementById('successMessage').style.display = 'block'; // Muestro mensaje de exito

                        //el boton de pdf
                        document.getElementById('downloadPdfBtn').onclick = function() {
                            window.open(data.url_pdf, '_blank');
                        };

                        // Descargo pdf
                        setTimeout(function() {
                            window.open(data.url_pdf, '_blank');
                        }, 1000);
                    } else {
                        // Si hubo un error
                        alert(data.mensaje);
                    }
                })
                .catch(error => {
                    // Manejo errores
                    alert('Error al procesar la reserva');
                    console.error(error);
                })
                .finally(() => {
                    submitBtn.disabled = false; // Rehabilitar el botón de enviar
                });
        };
    });
</script>
</body>
</html>
