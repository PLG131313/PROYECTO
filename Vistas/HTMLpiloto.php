<!DOCTYPE html>
<html>
<head>
    <title>AeroLine - Panel del Piloto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .header { background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)),
        url('https://images.unsplash.com/photo-1436491865332-7a61a109cc05') center/cover; color: white;
            padding: 100px 0 50px; text-align: center; margin-bottom: 30px; }
        .card { border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card-header { background-color: #0d6efd; color: white; font-weight: bold; padding: 15px; }
        .center-content { display: flex; justify-content: center; align-items: center; height: 20vh; }
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
    <div class="row center-content">
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
        <?= $htmlPaginacion ?>
    </div>
</div>

<footer class="bg-dark text-light py-4 text-center mt-5">
    <div class="container"><p>&copy; 2025 AeroLine - Sistema de Gestión de Vuelos</p></div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>