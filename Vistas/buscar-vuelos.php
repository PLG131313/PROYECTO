<?php
session_start();

// Verificar si el usuario ha iniciado sesión como cliente
if (!isset($_SESSION['idusuario']) || $_SESSION['tipo_usuario'] !== 'cliente') {
    header('Location: ../index.php');
    exit;
}

require_once('../php/conexion.php');

// Obtener ciudades únicas para los selectores
$consultaCiudades = "SELECT DISTINCT origen FROM vuelos UNION SELECT DISTINCT destino FROM vuelos ORDER BY origen";
$resultadoCiudades = mysqli_query($conexion, $consultaCiudades);
$ciudades = [];
while ($ciudad = mysqli_fetch_assoc($resultadoCiudades)) {
    $ciudades[] = $ciudad['origen'];
}

mysqli_close($conexion);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AeroLine - Buscar Vuelos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .search-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin: 30px 0;
        }

        .flight-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            padding: 20px;
            transition: transform 0.3s;
        }

        .flight-card:hover {
            transform: translateY(-5px);
        }

        .price-tag {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
            font-size: 1.2em;
        }

        .flight-route {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 15px 0;
        }

        .city {
            font-size: 1.5em;
            font-weight: bold;
            color: #333;
        }

        .flight-arrow {
            color: #667eea;
            font-size: 1.5em;
        }

        .navbar-brand {
            font-size: 1.5em;
            font-weight: bold;
        }

        .btn-search {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            color: white;
            font-weight: bold;
        }

        .btn-book {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: 20px;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 50px;
        }
    </style>
</head>
<body>

<!-- Barra de navegación -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">✈️ AeroLine</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="panel-cliente.php">
                        <i class="fas fa-user"></i> Mis Vuelos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['nombre_cliente']); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../index.php">
                        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <!-- Formulario de búsqueda -->
    <div class="search-container">
        <h2 class="text-center mb-4">
            <i class="fas fa-search"></i> Buscar Vuelos
        </h2>

        <form id="searchForm">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="origen" class="form-label">Origen</label>
                    <select class="form-select" id="origen" required>
                        <option value="">Seleccionar origen</option>
                        <?php foreach ($ciudades as $ciudad): ?>
                            <option value="<?php echo htmlspecialchars($ciudad); ?>">
                                <?php echo htmlspecialchars($ciudad); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <label for="destino" class="form-label">Destino</label>
                    <select class="form-select" id="destino" required>
                        <option value="">Seleccionar destino</option>
                        <?php foreach ($ciudades as $ciudad): ?>
                            <option value="<?php echo htmlspecialchars($ciudad); ?>">
                                <?php echo htmlspecialchars($ciudad); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <label for="fecha" class="form-label">Fecha</label>
                    <input type="date" class="form-control" id="fecha" required>
                </div>

                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-search w-100">
                        <i class="fas fa-search"></i> Buscar Vuelos
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Indicador de carga -->
    <div class="loading" id="loading">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
        <p class="mt-3">Buscando vuelos disponibles...</p>
    </div>

    <!-- Resultados de búsqueda -->
    <div id="resultados"></div>
</div>

<!-- Modal de confirmación de compra -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Compra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="flightDetails"></div>
                <div class="mt-3">
                    <label for="numPasajeros" class="form-label">Número de pasajeros</label>
                    <select class="form-select" id="numPasajeros">
                        <option value="1">1 pasajero</option>
                        <option value="2">2 pasajeros</option>
                        <option value="3">3 pasajeros</option>
                        <option value="4">4 pasajeros</option>
                        <option value="5">5 pasajeros</option>
                    </select>
                </div>
                <div class="mt-3">
                    <strong>Total: <span id="totalPrice"></span>€</strong>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="confirmarCompra()">
                    <i class="fas fa-credit-card"></i> Confirmar Compra
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    let selectedFlight = null;

    // Establecer fecha mínima como hoy
    document.getElementById('fecha').min = new Date().toISOString().split('T')[0];

    // Manejar envío del formulario de búsqueda
    document.getElementById('searchForm').addEventListener('submit', function(e) {
        e.preventDefault();
        buscarVuelos();
    });

    // Actualizar precio total cuando cambia el número de pasajeros
    document.getElementById('numPasajeros').addEventListener('change', function() {
        if (selectedFlight) {
            const numPasajeros = parseInt(this.value);
            const total = selectedFlight.precio * numPasajeros;
            document.getElementById('totalPrice').textContent = total.toFixed(2);
        }
    });

    function buscarVuelos() {
        const origen = document.getElementById('origen').value;
        const destino = document.getElementById('destino').value;
        const fecha = document.getElementById('fecha').value;

        if (!origen || !destino || !fecha) {
            alert('Por favor, complete todos los campos');
            return;
        }

        if (origen === destino) {
            alert('El origen y destino no pueden ser iguales');
            return;
        }

        // Mostrar indicador de carga
        document.getElementById('loading').style.display = 'block';
        document.getElementById('resultados').innerHTML = '';

        // Realizar búsqueda
        const formData = new FormData();
        formData.append('origen', origen);
        formData.append('destino', destino);
        formData.append('fecha', fecha);

        fetch('../php/buscar_vuelos.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                document.getElementById('loading').style.display = 'none';
                mostrarResultados(data);
            })
            .catch(error => {
                document.getElementById('loading').style.display = 'none';
                console.error('Error:', error);
                alert('Error al buscar vuelos');
            });
    }

    function mostrarResultados(data) {
        const resultadosDiv = document.getElementById('resultados');

        if (!data.success) {
            resultadosDiv.innerHTML = `
            <div class="alert alert-warning text-center">
                <i class="fas fa-exclamation-triangle"></i> ${data.message}
            </div>
        `;
            return;
        }

        if (data.vuelos.length === 0) {
            resultadosDiv.innerHTML = `
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle"></i> No se encontraron vuelos para los criterios seleccionados
            </div>
        `;
            return;
        }

        let html = '<h3 class="text-center mb-4">Vuelos Disponibles</h3>';

        data.vuelos.forEach(vuelo => {
            html += `
            <div class="flight-card">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="flight-route">
                            <div class="city">${vuelo.origen}</div>
                            <div class="flight-arrow">
                                <i class="fas fa-plane"></i>
                            </div>
                            <div class="city">${vuelo.destino}</div>
                        </div>
                        <div class="flight-info">
                            <p class="mb-1">
                                <i class="fas fa-calendar"></i>
                                ${new Date(vuelo.fecha_hora).toLocaleDateString('es-ES')}
                            </p>
                            <p class="mb-1">
                                <i class="fas fa-clock"></i>
                                ${new Date(vuelo.fecha_hora).toLocaleTimeString('es-ES', {hour: '2-digit', minute: '2-digit'})}
                            </p>
                            <p class="mb-1">
                                <i class="fas fa-plane"></i>
                                Vuelo ${vuelo.codigo}
                            </p>
                            <p class="mb-0">
                                <i class="fas fa-users"></i>
                                ${vuelo.asientos_disponibles} asientos disponibles
                            </p>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="price-tag">
                            ${parseFloat(vuelo.precio).toFixed(2)}€
                        </div>
                        <small class="text-muted">por persona</small>
                    </div>
                    <div class="col-md-3 text-center">
                        <button class="btn btn-book" onclick="seleccionarVuelo(${JSON.stringify(vuelo).replace(/"/g, '&quot;')})">
                            <i class="fas fa-shopping-cart"></i> Reservar
                        </button>
                    </div>
                </div>
            </div>
        `;
        });

        resultadosDiv.innerHTML = html;
    }

    function seleccionarVuelo(vuelo) {
        selectedFlight = vuelo;

        // Mostrar detalles del vuelo en el modal
        document.getElementById('flightDetails').innerHTML = `
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Vuelo ${vuelo.codigo}</h6>
                <p class="card-text">
                    <strong>${vuelo.origen}</strong> → <strong>${vuelo.destino}</strong><br>
                    <i class="fas fa-calendar"></i> ${new Date(vuelo.fecha_hora).toLocaleDateString('es-ES')}<br>
                    <i class="fas fa-clock"></i> ${new Date(vuelo.fecha_hora).toLocaleTimeString('es-ES', {hour: '2-digit', minute: '2-digit'})}<br>
                    <i class="fas fa-euro-sign"></i> ${parseFloat(vuelo.precio).toFixed(2)}€ por persona
                </p>
            </div>
        </div>
    `;

        // Calcular precio inicial
        const numPasajeros = parseInt(document.getElementById('numPasajeros').value);
        const total = vuelo.precio * numPasajeros;
        document.getElementById('totalPrice').textContent = total.toFixed(2);

        // Mostrar modal
        const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
        modal.show();
    }

    function confirmarCompra() {
        if (!selectedFlight) return;

        const numPasajeros = parseInt(document.getElementById('numPasajeros').value);
        const total = selectedFlight.precio * numPasajeros;

        const formData = new FormData();
        formData.append('vuelo_id', selectedFlight.id);
        formData.append('num_pasajeros', numPasajeros);
        formData.append('total', total);

        fetch('../php/comprar_vuelo.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('¡Compra realizada con éxito! Se ha enviado la confirmación a su email.');
                    // Cerrar modal
                    bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();
                    // Redirigir al panel del cliente
                    window.location.href = 'panel-cliente.php';
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la compra');
            });
    }
</script>

</body>
</html>
