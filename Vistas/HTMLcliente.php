<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AeroLine - Reservas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* pongo imagen en el header y para poder meterle mejor los estilos */
        .header {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)),
            url('../img/avion.jpg') no-repeat center center;
            background-size: cover;
            color: white;
            padding: 40px 0;
            text-align: center;
        }


        .main-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 80vh;
            padding: 15px;
        }


        .form-container {
            max-width: 500px;
            width: 100%;
            margin: auto;
        }

        /* nombre logo */
        .airline-logo h2 {
            font-size: 28px;
            font-family: 'Arial', sans-serif;
            font-weight: bold;
            color: #ff0000;
            text-transform: uppercase;
            position: relative;
            display: inline-block;
            margin-top: 15px;
        }

        .airline-logo h2::before {
            content: '✈️';
            position: absolute;
            top: -5px;
            left: -30px;
            font-size: 30px;
            animation: fly 2s infinite linear;
        }

        /* muevo avion */
        @keyframes fly {
            0% { transform: translateX(-10px); }
            50% { transform: translateX(10px); }
            100% { transform: translateX(-10px); }
        }
    </style>
</head>
<body>
<!-- Barra de navegación -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid d-flex justify-content-between">
        <a class="navbar-brand" href="../index.php">✈️ AeroLine</a>
        <a class="nav-link text-white" href="../index.php">Cerrar Sesión</a>
    </div>
</nav>

<!-- Encabezado -->
<header class="header">
    <h1>Bienvenido a AeroLine</h1>
    <p>Sistema de Reservas de Vuelos</p>
</header>

<!-- Contenedor principal -->
<div class="container main-container">
    <div class="card form-container p-3">
        <div class="card-body">
            <h3 class="card-title text-center">Nueva Reserva</h3>
            <form id="bookingForm">
                <div class="mb-3">
                    <label class="form-label">Ciudad Origen</label>
                    <input type="text" class="form-control" name="origin" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ciudad Destino</label>
                    <input type="text" class="form-control" name="destination" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Fecha del Vuelo</label>
                    <input type="date" class="form-control" name="departureDate" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Clase</label>
                    <select class="form-control" name="flightClass" required>
                        <option value="Economico">Económica</option>
                        <option value="Clase media">Clase media</option>
                        <option value="Primera Clase">Primera Clase</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100">Reservar Vuelo</button>
            </form>

            <!-- Mensaje de éxito -->
            <div class="success-message alert alert-success" id="successMessage" style="display: none;">
                <h4>¡Reserva realizada con éxito!</h4>
                <p>Tu ticket se descargará automáticamente.</p>
                <button class="btn btn-success" id="downloadPdfBtn">Descargar Ticket PDF</button>
            </div>

            <div class="text-center airline-logo">
                <h2>AeroLine</h2>
            </div>
        </div>
    </div>
</div>

<!-- Pie de página -->
<footer class="bg-dark text-light py-3 text-center">
    <p>&copy; 2025 AeroLine - Sistema de Gestión de Vuelos</p>
</footer>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // solo fechas actuales no del pasado
        document.querySelector('input[name="departureDate"]').min = new Date().toISOString().split('T')[0];

        // envio formulario
        document.getElementById('bookingForm').onsubmit = function(e) {
            e.preventDefault(); // no recargo
            var form = e.target;
            var submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true; // deshabilito el boton
            var formData = new FormData(form);

            // lo envio con post
            fetch('../php/reserva.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json()) // paso a JSON
                .then(data => {
                    if (data.exito) {
                        form.style.display = 'none'; // oculto formulario
                        document.getElementById('successMessage').style.display = 'block'; // pongo exito
                        document.getElementById('downloadPdfBtn').onclick = function() {
                            window.open(data.url_pdf, '_blank'); // se me descarga pdf
                        };
                        setTimeout(() => {
                            window.open(data.url_pdf, '_blank');
                        }, 1000);
                    } else {
                        alert(data.mensaje); // errores
                    }
                })
                .catch(error => {
                    alert('Error al procesar la reserva'); // erreoes
                    console.error(error);
                })
                .finally(() => {
                    submitBtn.disabled = false; // habiloto botones
                });
        };
    });
</script>
</body>
</html>
