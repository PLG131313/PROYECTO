<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Cliente - AeroLine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .registration-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 80px;
            margin-bottom: 40px;
        }
        .password-requirements {
            font-size: 0.875rem;
            color: #6c757d;
        }
        .requirement-item.met {
            color: #0400ed;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="../index1.php">✈️ AeroLine</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="HTMLcontacto.php ">Contacto</a></li>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('registrationForm');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirmPassword');

        function validatePassword() {
            const pwd = password.value;
            document.getElementById('lengthRequirement').classList.toggle('met', pwd.length >= 8);
            document.getElementById('upperRequirement').classList.toggle('met', /[A-Z]/.test(pwd));
            document.getElementById('lowerRequirement').classList.toggle('met', /[a-z]/.test(pwd));
            document.getElementById('numberRequirement').classList.toggle('met', /[0-9]/.test(pwd));
            document.getElementById('specialRequirement').classList.toggle('met', /[^A-Za-z0-9]/.test(pwd));
        }

        password.addEventListener('input', validatePassword);

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            if (password.value !== confirmPassword.value) {
                alert('Las contraseñas no coinciden');
                return;
            }

            if (document.querySelectorAll('.requirement-item.met').length !== 5) {
                alert('La contraseña no cumple con todos los requisitos.');
                return;
            }

            const formData = new FormData(this);
            const data = new URLSearchParams(formData).toString();

            fetch('../php/registro_cliente.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: data
            })
                .then(response => response.text())
                .then(data => {
                    if (data.trim() === '0') {
                        alert("Se registro correctamente")
                        window.location.href = '../index1.php';
                    } else {
                        alert(data);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error en el registro');
                });
        });
    });
</script>
</body>
</html>
