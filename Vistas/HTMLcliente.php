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
        .success-message {
            display: none;
            margin-top: 20px;
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

<!-- Formulario de Reserva -->
<div class="container mb-5">
    <div class="card">
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
                            <option value="ECONOMY">Económica</option>
                            <option value="BUSINESS">Ejecutiva</option>
                            <option value="FIRST">Primera Clase</option>
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
        </div>
    </div>
</div>
<footer class="bg-dark text-light py-4">
    <div class="container text-center">
        <p>&copy; 2025 AeroLine - Sistema de Gestión de Vuelos</p>
    </div>
</footer>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Establecer fecha mínima
        const dateInput = document.querySelector('input[name="departureDate"]');
        dateInput.min = new Date().toISOString().split('T')[0];

        // Manejar envío del formulario
        document.getElementById('bookingForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;

            try {
                const response = await fetch('../php/reserva.php', {
                    method: 'POST',
                    body: new FormData(form)
                });

                const data = await response.json();

                if (data.exito) {
                    // Mostrar mensaje de éxito
                    form.style.display = 'none';
                    document.getElementById('successMessage').style.display = 'block';

                    // Configurar botón de descarga
                    const downloadBtn = document.getElementById('downloadPdfBtn');
                    downloadBtn.onclick = () => window.open(data.url_pdf, '_blank');

                    // Descargar PDF automáticamente
                    setTimeout(() => window.open(data.url_pdf, '_blank'), 1000);
                } else {
                    alert(data.mensaje);
                }
            } catch (error) {
                alert('Error al procesar la reserva');
                console.error(error);
            }

            submitBtn.disabled = false;
        });
    });
</script>
</body>
</html>

