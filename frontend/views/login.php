<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Panel del Doctor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #0a0a0a;
        }
        .auth-card {
            background-color: #141414;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .input-field {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.2s ease;
        }
        .input-field:focus {
            background-color: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.2);
        }
        .primary-button {
            background-color: white;
            color: black;
            transition: all 0.2s ease;
        }
        .primary-button:hover {
            opacity: 0.9;
        }
        .secondary-button {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.2s ease;
        }
        .secondary-button:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="h-full">
    <div class="min-h-screen grid place-items-center p-4">
        <div class="w-full max-w-sm auth-card p-6 space-y-6">
            <div class="space-y-2">
                <div class="flex justify-between items-center">
                    <h1 class="text-xl font-semibold text-white">Panel del Doctor</h1>
                </div>
                <p class="text-sm text-gray-400">Ingrese sus credenciales para acceder al sistema</p>
            </div>

            <?php if (isset($_GET['error'])): ?>
            <div class="rounded-md bg-red-900/20 px-4 py-3 text-sm text-red-400">
                <div class="flex items-center gap-2">
                    <i class="bi bi-exclamation-circle"></i>
                    <span>Credenciales incorrectas. Por favor, intente de nuevo.</span>
                </div>
            </div>
            <?php endif; ?>

            <form method="POST" action="procesar_login.php" class="space-y-4">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-white/90" for="email">
                        Correo Electrónico
                    </label>
                    <div class="relative">
                        <input
                            type="email"
                            id="email"
                            name="email"
                            required
                            class="input-field w-full h-10 px-3 rounded-md text-sm focus:outline-none pl-9"
                            placeholder="Ingrese su correo electrónico"
                        >
                        <i class="bi bi-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-white/90" for="password">
                        Contraseña
                    </label>
                    <div class="relative">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            class="input-field w-full h-10 px-3 rounded-md text-sm focus:outline-none pl-9"
                            placeholder="Ingrese su contraseña"
                        >
                        <i class="bi bi-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <button 
                            type="button"
                            onclick="togglePassword()"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white transition-colors"
                        >
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="space-y-3 pt-2">
                    <button type="submit" class="primary-button w-full h-10 rounded-md font-medium flex items-center justify-center gap-2">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Iniciar Sesión
                    </button>
                </div>
            </form>

            <div class="pt-4 border-t border-white/10">
                <a href="index.php" class="secondary-button w-full h-10 rounded-md font-medium flex items-center justify-center gap-2">
                    <i class="bi bi-arrow-left"></i>
                    Volver al Inicio
                </a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }
    </script>
</body>
</html> 