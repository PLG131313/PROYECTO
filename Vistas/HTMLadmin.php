<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AeroLine - Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        /* Estilos generales consistentes con el index */
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .content-wrapper {
            flex: 1;
            padding-top: 56px; /* Altura de la navbar */
        }

        /* Estilos para las tarjetas de resumen */
        .summary-card {
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .summary-card:hover {
            transform: translateY(-5px);
        }

        /* Estilos para las tablas */
        .table-container {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        /* Estilos para las pestañas */
        .nav-tabs .nav-link.active {
            font-weight: bold;
            color: #0d6efd;
            border-bottom: 2px solid #0d6efd;
        }

        .nav-tabs .nav-link {
            color: #495057;
        }

        /* Estilos para los formularios */
        .form-container {
            background: #fff;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        /* Estilos para los botones de acción */
        .btn-action {
            margin-right: 5px;
        }

        /* Estilos para la paginación */
        .pagination-info {
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 0.9em;
            color: #6c757d;
        }

        .pagination-container {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .pagination-info-bottom {
            font-size: 0.9em;
            color: #6c757d;
        }

        @media (max-width: 768px) {
            .pagination-container {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>

<!-- Barra de navegación -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="#">✈️ AeroLine - Administración</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['nombre_admin'] ?? 'Administrador'); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../index.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="content-wrapper">
    <div class="container py-4">
        <!-- Pestañas de navegación -->
        <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard">
                    <i class="fas fa-tachometer-alt"></i> Panel Admin
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="flights-tab" data-bs-toggle="tab" data-bs-target="#flights">
                    <i class="fas fa-plane-departure"></i> Gestión de Vuelos
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="pilots-tab" data-bs-toggle="tab" data-bs-target="#pilots">
                    <i class="fas fa-user-tie"></i> Gestión de Pilotos
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Dashboard -->
            <div class="tab-pane fade show active" id="dashboard">
                <h2 class="mb-4">Panel de Control</h2>

                <!-- Tarjetas de resumen -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="card summary-card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Vuelos Activos</h6>
                                        <h2 class="mb-0"><?php echo $vuelosActivos ?? 0; ?></h2>
                                    </div>
                                    <i class="fas fa-plane fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card summary-card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Reservas Nuevas</h6>
                                        <h2 class="mb-0"><?php echo $reservasNuevas ?? 0; ?></h2>
                                    </div>
                                    <i class="fas fa-ticket-alt fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card summary-card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Pilotos Disponibles</h6>
                                        <h2 class="mb-0"><?php echo $pilotosDisponibles ?? 0; ?></h2>
                                    </div>
                                    <i class="fas fa-user-tie fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tablas de resumen -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="table-container">
                            <h5 class="mb-3"><i class="fas fa-plane-departure"></i> Próximos Vuelos</h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th>Vuelo</th>
                                        <th>Origen</th>
                                        <th>Destino</th>
                                        <th>Fecha</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (!empty($proximosVuelos)): ?>
                                        <?php
                                        // Definir ciudades para mostrar nombres completos
                                        $ciudades = [
                                            'Madrid' => 'Madrid',
                                            'Barcelona' => 'Barcelona',
                                            'Valencia' => 'Valencia',
                                            'Sevilla' => 'Sevilla',
                                            'Málaga' => 'Málaga',
                                            'Bilbao' => 'Bilbao',
                                            'Las Palmas' => 'Las Palmas',
                                            'Palma de Mayorca' => 'Palma de Mayorca'
                                        ];
                                        ?>
                                        <?php foreach ($proximosVuelos as $vuelo): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($vuelo['codigo']); ?></td>
                                                <td><?php echo htmlspecialchars(isset($ciudades[$vuelo['origen']]) ? $ciudades[$vuelo['origen']] : $vuelo['origen']); ?></td>
                                                <td><?php echo htmlspecialchars(isset($ciudades[$vuelo['destino']]) ? $ciudades[$vuelo['destino']] : $vuelo['destino']); ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($vuelo['fecha_hora'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">No hay vuelos programados</td>
                                        </tr>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="table-container">
                            <h5 class="mb-3"><i class="fas fa-ticket-alt"></i> Últimas Reservas</h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Vuelo</th>
                                        <th>Estado</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (!empty($ultimasReservas)): ?>
                                        <?php foreach ($ultimasReservas as $reserva): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($reserva['idReserva']); ?></td>
                                                <td><?php echo htmlspecialchars($reserva['nombre_cliente'] ?? 'Cliente #'.$reserva['idCliente']); ?></td>
                                                <td><?php echo htmlspecialchars($reserva['codigo_vuelo'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <?php
                                                    $estadoClass = ($reserva['estado'] ?? 'confirmada') === 'confirmada' ? 'bg-success' : 'bg-danger';
                                                    $estadoTexto = ucfirst($reserva['estado'] ?? 'confirmada');
                                                    ?>
                                                    <span class="badge <?php echo $estadoClass; ?>"><?php echo $estadoTexto; ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">No hay reservas recientes</td>
                                        </tr>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gestión de Vuelos -->
            <div class="tab-pane fade" id="flights">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Gestión de Vuelos</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newFlightModal">
                        <i class="fas fa-plus"></i> Nuevo Vuelo
                    </button>
                </div>

                <div class="table-container">
                    <!-- Información de paginación -->
                    <?php if (($totalVuelos ?? 0) > 0): ?>
                        <div class="pagination-info">
                            <i class="fas fa-info-circle"></i>
                            Mostrando <?php echo ((($pagina_vuelos ?? 1) - 1) * ($registros_por_pagina ?? 10)) + 1; ?> -
                            <?php echo min(($pagina_vuelos ?? 1) * ($registros_por_pagina ?? 10), $totalVuelos ?? 0); ?>
                            de <?php echo $totalVuelos ?? 0; ?> vuelos
                            (Página <?php echo $pagina_vuelos ?? 1; ?> de <?php echo $totalPaginasVuelos ?? 1; ?>)
                        </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>Código</th>
                                <th>Origen</th>
                                <th>Destino</th>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Piloto</th>
                                <th>Estado</th>
                            </tr>
                            </thead>
                            <tbody id="tablaVuelos">
                            <?php echo $htmlTablaVuelos ?? '<tr><td colspan="7" class="text-center">No hay vuelos disponibles</td></tr>'; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <?php if (!empty($paginacionVuelos)): ?>
                        <div class="pagination-container">
                            <div class="pagination-info-bottom">
                                Total: <?php echo $totalVuelos ?? 0; ?> vuelos
                            </div>
                            <?php echo $paginacionVuelos; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Gestión de Pilotos -->
            <div class="tab-pane fade" id="pilots">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Gestión de Pilotos</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newPilotModal">
                        <i class="fas fa-plus"></i> Nuevo Piloto
                    </button>
                </div>

                <div class="table-container">
                    <!-- Información de paginación -->
                    <?php if (($totalPilotos ?? 0) > 0): ?>
                        <div class="pagination-info">
                            <i class="fas fa-info-circle"></i>
                            Mostrando <?php echo ((($pagina_pilotos ?? 1) - 1) * ($registros_por_pagina ?? 10)) + 1; ?> -
                            <?php echo min(($pagina_pilotos ?? 1) * ($registros_por_pagina ?? 10), $totalPilotos ?? 0); ?>
                            de <?php echo $totalPilotos ?? 0; ?> pilotos
                            (Página <?php echo $pagina_pilotos ?? 1; ?> de <?php echo $totalPaginasPilotos ?? 1; ?>)
                        </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Licencia</th>
                                <th>Teléfono</th>
                                <th>Estado</th>
                            </tr>
                            </thead>
                            <tbody id="tablaPilotos">
                            <?php echo $htmlTablaPilotos ?? '<tr><td colspan="6" class="text-center">No hay pilotos disponibles</td></tr>'; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <?php if (!empty($paginacionPilotos)): ?>
                        <div class="pagination-container">
                            <div class="pagination-info-bottom">
                                Total: <?php echo $totalPilotos ?? 0; ?> pilotos
                            </div>
                            <?php echo $paginacionPilotos; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nuevo Vuelo -->
<div class="modal fade" id="newFlightModal" tabindex="-1" aria-labelledby="newFlightModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newFlightModalLabel">Crear Nuevo Vuelo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="newFlightForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="flightCode" class="form-label">Código de Vuelo</label>
                            <input type="text" class="form-control" id="flightCode" placeholder="Ej: AE1234" required>
                        </div>
                        <div class="col-md-6">
                            <label for="flightDate" class="form-label">Fecha</label>
                            <input type="date" class="form-control" id="flightDate" required>
                            <div class="form-text">Seleccione la fecha para ver pilotos disponibles</div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="flightPilot" class="form-label">Piloto</label>
                            <select class="form-select" id="flightPilot" required disabled>
                                <option selected disabled value="">Seleccione fecha primero</option>
                            </select>
                            <div id="pilotLoading" class="d-none">
                                <div class="spinner-border spinner-border-sm text-primary mt-2" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <span class="ms-2">Cargando pilotos disponibles...</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="flightTime" class="form-label">Hora</label>
                            <input type="time" class="form-control" id="flightTime" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="flightOrigin" class="form-label">Origen</label>
                            <select class="form-select" id="flightOrigin" required>
                                <option selected disabled value="">Seleccionar origen</option>
                                <option value="Madrid">Madrid</option>
                                <option value="Barcelona">Barcelona</option>
                                <option value="Valencia">Valencia</option>
                                <option value="Sevilla">Sevilla</option>
                                <option value="Málaga">Málaga</option>
                                <option value="Bilbao">Bilbao</option>
                                <option value="Las Palmas">Las Palmas (Canarias)</option>
                                <option value="Palma de Mallorca">Palma de Mallorca (Baleares)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="flightDestination" class="form-label">Destino</label>
                            <select class="form-select" id="flightDestination" required>
                                <option selected disabled value="">Seleccionar destino</option>
                                <option value="Madrid">Madrid</option>
                                <option value="Barcelona">Barcelona</option>
                                <option value="Valencia">Valencia</option>
                                <option value="Sevilla">Sevilla</option>
                                <option value="Málaga">Málaga</option>
                                <option value="Bilbao">Bilbao</option>
                                <option value="Las Palmas">Las Palmas (Canarias)</option>
                                <option value="Palma de Mallorca">Palma de Mallorca (Baleares)</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="flightCapacity" class="form-label">Capacidad</label>
                            <input type="number" class="form-control" id="flightCapacity" placeholder="Número de asientos" required min="1">
                        </div>
                        <div class="col-md-6">
                            <label for="flightPrice" class="form-label">Precio Base</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="flightPrice" placeholder="Precio por asiento" required min="0" step="0.01">
                                <span class="input-group-text">€</span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="flightNotes" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="flightNotes" rows="3"></textarea>
                    </div>
                    <div id="flightFormMessage" class="alert d-none"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveNewFlight()">Guardar Vuelo</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nuevo/Editar Piloto -->
<div class="modal fade" id="newPilotModal" tabindex="-1" aria-labelledby="newPilotModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newPilotModalLabel">Añadir Nuevo Piloto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="newPilotForm">
                    <input type="hidden" id="pilotId" name="id" value="0">
                    <div class="mb-3">
                        <label for="pilotName" class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" id="pilotName" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="pilotEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="pilotEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="pilotLicense" class="form-label">Número de Licencia</label>
                        <input type="text" class="form-control" id="pilotLicense" name="licencia" placeholder="Ej: LIC-12345" required>
                    </div>
                    <div class="mb-3">
                        <label for="pilotPassword" class="form-label">Contraseña <span id="passwordHint" class="text-muted small">(Dejar en blanco para mantener la actual)</span></label>
                        <input type="password" class="form-control" id="pilotPassword" name="contrasena">
                    </div>
                    <div class="mb-3">
                        <label for="pilotPhone" class="form-label">Teléfono de Contacto</label>
                        <input type="tel" class="form-control" id="pilotPhone" name="telefono">
                    </div>
                    <div class="mb-3">
                        <label for="pilotStatus" class="form-label">Estado</label>
                        <select class="form-select" id="pilotStatus" name="estado" required>
                            <option value="disponible">Disponible</option>
                            <option value="en_vuelo">En vuelo</option>
                            <option value="descanso">Descanso</option>
                            <option value="baja">Baja</option>
                        </select>
                    </div>
                    <div id="pilotFormMessage" class="alert d-none"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="savePilotBtn" onclick="saveNewPilot()">Guardar Piloto</button>
            </div>
        </div>
    </div>
</div>

<!-- Pie de página -->
<footer class="bg-dark text-light py-4 mt-auto">
    <div class="container text-center">
        <p>&copy; 2025 AeroLine - Sistema de Gestión de Vuelos</p>
    </div>
</footer>

<!-- Scripts de Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/reservas.js"></script>
<script>
    // Función para cambiar de página
    function cambiarPagina(seccion, pagina) {
        // Construir URL con el parámetro de página correspondiente
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('pagina_' + seccion, pagina);

        // Mantener la pestaña activa
        const activeTab = document.querySelector('.nav-link.active').id;
        sessionStorage.setItem('activeTab', '#' + activeTab);

        // Redirigir con los nuevos parámetros
        window.location.href = window.location.pathname + '?' + urlParams.toString();
    }

    function saveNewPilot() {
        console.log('Función saveNewPilot llamada');

        const form = document.getElementById('newPilotForm');
        const formData = new FormData(form);

        const nombre = formData.get('nombre');
        const email = formData.get('email');
        const licencia = formData.get('licencia');
        const contrasena = formData.get('contrasena');

        // Validar campos obligatorios
        if (!nombre || !email || !licencia || !contrasena) {
            mostrarMensajePiloto('Todos los campos obligatorios deben ser completados', 'danger');
            return;
        }

        const saveBtn = document.getElementById('savePilotBtn');
        const originalText = saveBtn.textContent;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...';

        fetch('../php/guardar_piloto.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                saveBtn.disabled = false;
                saveBtn.textContent = originalText;

                if (data.success) {
                    mostrarMensajePiloto(data.message, 'success');
                    setTimeout(() => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('newPilotModal'));
                        modal.hide();
                        window.location.reload();
                    }, 1500);
                } else {
                    mostrarMensajePiloto(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error en la petición:', error);
                saveBtn.disabled = false;
                saveBtn.textContent = originalText;
                mostrarMensajePiloto('Error al procesar la solicitud', 'danger');
            });
    }

    // Función para mostrar mensajes en el formulario de piloto
    function mostrarMensajePiloto(mensaje, tipo) {
        const mensajeElement = document.getElementById('pilotFormMessage');
        mensajeElement.textContent = mensaje;
        mensajeElement.className = `alert alert-${tipo}`;
        mensajeElement.classList.remove('d-none');
    }

    // Cuando se abre el modal para añadir un nuevo piloto
    document.getElementById('newPilotModal').addEventListener('show.bs.modal', function (event) {
        document.getElementById('newPilotForm').reset();
        document.getElementById('pilotId').value = '0';
        document.getElementById('newPilotModalLabel').textContent = 'Añadir Nuevo Piloto';
        document.getElementById('passwordHint').style.display = 'none';
        document.getElementById('pilotPassword').required = true;
        document.getElementById('pilotFormMessage').classList.add('d-none');
    });

    // Función para cargar pilotos disponibles según la fecha seleccionada
    document.getElementById('flightDate').addEventListener('change', function() {
        const fecha = this.value;
        const selectPiloto = document.getElementById('flightPilot');
        const loadingIndicator = document.getElementById('pilotLoading');

        // Validar que se haya seleccionado una fecha
        if (!fecha) {
            selectPiloto.innerHTML = '<option selected disabled value="">Seleccione fecha primero</option>';
            selectPiloto.disabled = true;
            return;
        }

        // Mostrar indicador de carga
        loadingIndicator.classList.remove('d-none');
        selectPiloto.disabled = true;
        selectPiloto.innerHTML = '<option selected disabled value="">Cargando...</option>';

        // Obtener pilotos disponibles para la fecha seleccionada
        fetch(`../php/obtener_pilotos_disponibles.php?fecha=${fecha}`)
            .then(response => response.json())
            .then(data => {
                // Ocultar indicador de carga
                loadingIndicator.classList.add('d-none');

                if (data.success) {
                    // Llenar el selector con los pilotos disponibles
                    selectPiloto.innerHTML = '';

                    if (data.pilotos.length === 0) {
                        selectPiloto.innerHTML = '<option selected disabled value="">No hay pilotos disponibles</option>';
                        selectPiloto.disabled = true;
                    } else {
                        selectPiloto.innerHTML = '<option selected disabled value="">Seleccionar piloto</option>';
                        data.pilotos.forEach(piloto => {
                            const option = document.createElement('option');
                            option.value = piloto.id;
                            option.textContent = `${piloto.nombre} (${piloto.licencia})`;
                            selectPiloto.appendChild(option);
                        });
                        selectPiloto.disabled = false;
                    }
                } else {
                    // Mostrar mensaje de error
                    selectPiloto.innerHTML = '<option selected disabled value="">Error al cargar pilotos</option>';
                    selectPiloto.disabled = true;
                    console.error('Error:', data.message);
                }
            })
            .catch(error => {
                // Ocultar indicador de carga y mostrar mensaje de error
                loadingIndicator.classList.add('d-none');
                selectPiloto.innerHTML = '<option selected disabled value="">Error al cargar pilotos</option>';
                selectPiloto.disabled = true;
                console.error('Error:', error);
            });
    });

    // Función para mostrar mensajes en el formulario de vuelo
    function mostrarMensajeVuelo(mensaje, tipo) {
        const mensajeElement = document.getElementById('flightFormMessage');
        mensajeElement.textContent = mensaje;
        mensajeElement.className = `alert alert-${tipo}`;
        mensajeElement.classList.remove('d-none');
    }

    // Función para guardar un nuevo vuelo
    function saveNewFlight() {
        // Obtener los valores del formulario
        const codigo = document.getElementById('flightCode').value;
        const piloto = document.getElementById('flightPilot').value;
        const origen = document.getElementById('flightOrigin').value;
        const destino = document.getElementById('flightDestination').value;
        const fecha = document.getElementById('flightDate').value;
        const hora = document.getElementById('flightTime').value;
        const capacidad = document.getElementById('flightCapacity').value;
        const precio = document.getElementById('flightPrice').value;
        const observaciones = document.getElementById('flightNotes').value;

        // Validar campos obligatorios
        if (!codigo || !piloto || !origen || !destino || !fecha || !hora || !capacidad || !precio) {
            mostrarMensajeVuelo('Todos los campos obligatorios deben ser completados', 'danger');
            return;
        }

        // Validar que origen y destino no sean iguales
        if (origen === destino) {
            mostrarMensajeVuelo('El origen y destino no pueden ser iguales', 'danger');
            return;
        }

        // Crear objeto FormData para enviar los datos
        const formData = new FormData();
        formData.append('codigo', codigo);
        formData.append('piloto', piloto);
        formData.append('origen', origen);
        formData.append('destino', destino);
        formData.append('fecha', fecha);
        formData.append('hora', hora);
        formData.append('capacidad', capacidad);
        formData.append('precio', precio);
        formData.append('observaciones', observaciones);

        // Enviar datos al servidor mediante AJAX
        fetch('../php/guardar_vuelo.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mostrar mensaje de éxito
                    mostrarMensajeVuelo(data.message, 'success');

                    // Cerrar el modal y recargar la página después de 1.5 segundos
                    setTimeout(() => {
                        document.getElementById('newFlightModal').querySelector('.btn-close').click();
                        window.location.reload(); // Recargar la página para mostrar los datos actualizados
                    }, 1500);
                } else {
                    // Mostrar mensaje de error
                    mostrarMensajeVuelo(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarMensajeVuelo('Error al procesar la solicitud', 'danger');
            });
    }

    // Cuando se abre el modal para añadir un nuevo vuelo
    document.getElementById('newFlightModal').addEventListener('show.bs.modal', function (event) {
        // Resetear el formulario
        document.getElementById('newFlightForm').reset();
        document.getElementById('flightFormMessage').classList.add('d-none');
        document.getElementById('flightPilot').disabled = true;
        document.getElementById('flightPilot').innerHTML = '<option selected disabled value="">Seleccione fecha primero</option>';

        // Establecer fecha mínima como hoy
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('flightDate').min = today;
    });

    // Cargar reservas al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        // Verificar si hay una pestaña guardada en sessionStorage
        const activeTab = sessionStorage.getItem('activeTab');
        if (activeTab && activeTab !== '#reservations-tab') {
            // Activar la pestaña guardada (excepto reservas que ya no existe)
            const tab = document.querySelector(activeTab);
            if (tab) {
                const bsTab = new bootstrap.Tab(tab);
                bsTab.show();
            }
        }

        // Guardar la pestaña activa cuando se cambia
        const tabs = document.querySelectorAll('button[data-bs-toggle="tab"]');
        tabs.forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(event) {
                sessionStorage.setItem('activeTab', '#' + event.target.id);
            });
        });
    });
</script>

</body>
</html>
