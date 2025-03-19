<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Cliente - AeroLine</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        /* pequeño degradado del body */
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh; /* Altura mínima de la pantalla */
        }

        /* Estilos para el contenedor del formulario de registro */
        .registration-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 80px;
            margin-bottom: 40px;
        }

        /* Estilo para los requisitos de contraseña */
        .password-requirements {
            font-size: 0.875rem;/*medida de la letra*/
            color: #6c757d;
        }

        /* azul si se cumple requisito */
        .requirement-item.met {
            color: #082ea1;
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
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="HTMLcontacto.php">Contacto</a></li>
            </ul>
        </div>
    </div>
</nav>


<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="registration-container">
                <h2 class="text-center mb-4">Registro de Cliente</h2>
                <form id="registrationForm">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                        <label for="nombre">Nombre</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="tel" class="form-control" id="telefono" name="telefono" required>
                        <label for="telefono">Teléfono</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="email" name="email" required>
                        <label for="email">Correo Electrónico</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" id="password" name="contrasena" required>
                        <label for="password">Contraseña</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" id="confirmPassword" required>
                        <label for="confirmPassword">Confirmar Contraseña</label>
                    </div>

                    <!-- Lista de requisitos de contraseña -->
                    <div class="password-requirements mb-3">
                        <p>La contraseña debe contener:</p>
                        <div id="lengthRequirement" class="requirement-item">✓ Mínimo 8 caracteres</div>
                        <div id="upperRequirement" class="requirement-item">✓ Al menos una mayúscula</div>
                        <div id="lowerRequirement" class="requirement-item">✓ Al menos una minúscula</div>
                        <div id="numberRequirement" class="requirement-item">✓ Al menos un número</div>
                        <div id="specialRequirement" class="requirement-item">✓ Al menos un carácter especial</div>
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        <button type="submit" class="btn btn-primary">Registrarse</button>
                    </div>
                </form>
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
    // pillo el evento
    document.addEventListener('DOMContentLoaded', function() {
        // formulario
        const form = document.getElementById('registrationForm');
        //contraseña y confirmación de contraseña
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirmPassword');

        // valido
        function validatePassword() {
            const pwd = password.value; // valor de la contraseña

            // Validar si la contraseña tiene al menos 8 caracteres
            document.getElementById('lengthRequirement').classList.toggle('met', pwd.length >= 8);
            // Validar si contiene al menos una letra mayúscula
            document.getElementById('upperRequirement').classList.toggle('met', /[A-Z]/.test(pwd));
            // Validar si contiene al menos una letra minúscula
            document.getElementById('lowerRequirement').classList.toggle('met', /[a-z]/.test(pwd));
            // Validar si contiene al menos un número
            document.getElementById('numberRequirement').classList.toggle('met', /[0-9]/.test(pwd));
            // Validar si contiene al menos un carácter especial
            document.getElementById('specialRequirement').classList.toggle('met', /[^A-Za-z0-9]/.test(pwd));
        }

        // llamo a la validacion de arriba
        password.addEventListener('input', validatePassword);

        // Manejo el envío del formulario
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // envio del formulario

            // compruebo si son iguales las contraseñas
            if (password.value !== confirmPassword.value) {
                alert('Las contraseñas no coinciden'); // Mostrar alerta si no coinciden
                return;
            }

            // requisitos de la contraseña
            if (document.querySelectorAll('.requirement-item.met').length !== 5) {
                alert('La contraseña no cumple con todos los requisitos.');
                return;
            }

            //FormData con los datos del formulario
            const formData = new FormData(this);
            //convierto lo de arriba en urll para el envio
            const data = new URLSearchParams(formData).toString();

            // Enviar los datos al servidor mediante fetch
            fetch('../php/registro_cliente.php', {
                method: 'POST', //lo pillo por post
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: data
            })
                .then(response => response.text()) // Convertir la respuesta a texto
                .then(data => {
                    if (data.trim() === '0') { // si hay 0 existo
                        alert("Se registró correctamente"); // Mostrar mensaje de éxito
                        window.location.href = '../index.php'; // Redirigir a la página principal
                    } else {
                        alert(data); // error si no es 0
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error en el registro'); // Mostrar mensaje de error al usuario
                });
        });
    });
</script>
</body>
</html>
