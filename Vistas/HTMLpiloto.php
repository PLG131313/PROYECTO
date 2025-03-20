<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AeroLine - Panel del Piloto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            background: #f0f0f5;
            font-family: Arial, sans-serif;
        }

        /* pongo aqui la imagen como parte del footer */
        .header {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)),
            url('../img/fotopiloto.jpeg') center/cover;
            color: white;
            padding: 80px 0;
            margin-bottom: 30px;
        }


        .container {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }


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


        .pagination-container {
            display: flex;
            justify-content: center;
            margin-bottom: 10px;
        }


        footer {
            width: 100%;
            background-color: #343a40;
            color: white;
            text-align: center;
            padding: 15px 0;
            margin-top: auto;
        }

        /* Ajustes para pantallas pequeñas */
        @media (max-width: 576px) {
            .header {
                padding: 50px 10px;
            }

            .container {
                padding: 10px;
            }

            .card {
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="../index.php">✈️ AeroLine</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link text-white" href="../index.php">Cerrar Sesión</a>
                </li>
            </ul>
        </div>
    </div>
</nav>


<header class="header">
    <div class="container">
        <h1>Bienvenido, <?= htmlspecialchars($nombrePiloto) ?></h1>
        <p class="lead">Panel de control del piloto</p>
    </div>
</header>

<!-- Contenido Principal -->
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card p-3 text-center">
                <h2><?= $totalReservas ?></h2>
                <p>Total de Vuelos</p>
            </div>
        </div>
    </div>

    <!-- Lista de vuelos -->
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

<!-- Footer -->
<footer>
    <div class="container">
        <p>&copy; 2025 AeroLine - Sistema de Gestión de Vuelos</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
