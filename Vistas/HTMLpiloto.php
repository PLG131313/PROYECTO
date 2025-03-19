<!DOCTYPE html>
<html>
<head>
    <title>AeroLine - Panel del Piloto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>

        body {
            background: #f0f0f5;
            font-family: Arial, sans-serif;
        }

        /* pongo imagen aqui para tener poder tocer mejor sus estilos */
        .header {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)),
            url('../img/fotopiloto.jpeg') center/cover;
            color: white;
            padding: 80px 0;
            text-align: center;
            margin-bottom: 30px;
        }

        /* Tarjetas de información */
        .card {
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #007bff;
            color: white;
            font-weight: bold;
            padding: 10px;
        }

        /* Centrando los botones de paginación y subiéndolos ligeramente */
        .pagination-container {
            display: flex;
            justify-content: center;
            margin-bottom: 20px; /* Reducido de 20px a 10px para subir un poco los botones */
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="../index.php">✈️ AeroLine</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
</nav>
<header class="header">
    <div class="container">
        <h1>Bienvenido, <?= htmlspecialchars($nombrePiloto) ?></h1>
        <p class="lead">Panel de control del piloto</p>
    </div>
</header>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card p-3 text-center">
                <h2><?= $totalReservas ?></h2>
                <p>Total de Vuelos</p>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><i class="fas fa-plane me-2"></i>Tus Vuelos Programados</div>
        <div class="card-body p-3">
            <?= $htmlReservas ?>
        </div>
        <div class="pagination-container">
            <?= $htmlPaginacion ?>
        </div>
    </div>
</div>

<footer class="bg-dark text-light py-4 text-center mt-5">
    <div class="container"><p>&copy; 2025 AeroLine - Sistema de Gestión de Vuelos</p></div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>