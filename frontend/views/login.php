<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Vital Drops Infusion</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        [x-cloak] { display: none !important; }
        .fade-out {
            animation: fadeOut 3s forwards;
        }
        @keyframes fadeOut {
            0% { opacity: 1; }
            70% { opacity: 1; } /* Mantiene el mensaje visible por 2.1s */
            100% { opacity: 0; }
        }
        body {
            background-color: #f8f9fa;
        }
        .auth-card {
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }
        .input-field {
            background-color: #f8f9fa;
            border: 1px solid #e5e7eb;
            color: #1f2937;
            transition: all 0.2s ease;
        }
        .input-field:focus {
            background-color: white;
            border-color: #9ca3af;
            box-shadow: 0 0 0 2px rgba(156, 163, 175, 0.1);
        }
        .primary-button {
            background-color: #1f2937;
            color: white;
            transition: all 0.2s ease;
        }
        .primary-button:hover {
            background-color: #111827;
            transform: translateY(-1px);
        }
        .primary-button:active {
            transform: translateY(0);
        }
        .secondary-button {
            background-color: white;
            border: 1px solid #e5e7eb;
            color: #4b5563;
            transition: all 0.2s ease;
        }
        .secondary-button:hover {
            background-color: #f9fafb;
            color: #1f2937;
        }
        .logo-container {
            max-width: 300px;
            margin-bottom: 2rem;
        }
        .logo-container img {
            width: 100%;
            height: auto;
        }
    </style>
</head>
<body class="h-full" x-data="{ showError: true, showSuccess: true }">
    <div class="min-h-screen grid place-items-center p-4">
        <div class="w-full max-w-sm space-y-6 text-center">
            <!-- Logo -->
            <div class="logo-container mx-auto ">
                <img src="/public/assets/img/logo.jpg" alt="Vital Drops Infusion" class="mx-auto rounded-lg">
            </div>

            <div class="auth-card p-6 space-y-6">
                <div class="space-y-2">
                    <div class="text-center">
                        <h1 class="text-xl font-semibold text-gray-900">Iniciar Sesión</h1>
                        <p class="text-sm text-gray-600">Ingrese sus credenciales para acceder al sistema</p>
                    </div>
                </div>

                <?php if (isset($_GET['password_changed'])): ?>
                <div x-show="showSuccess" 
                     x-init="setTimeout(() => { showSuccess = false }, 3000)"
                     class="rounded-md bg-green-50 px-4 py-3 text-sm text-green-600 border border-green-100 fade-out">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-check-circle"></i>
                        <span>Contraseña actualizada correctamente. Por favor, inicie sesión con su nueva contraseña.</span>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                <div x-show="showError" 
                     x-init="setTimeout(() => { showError = false }, 3000)"
                     class="rounded-md bg-red-50 px-4 py-3 text-sm text-red-500 border border-red-100 fade-out">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-exclamation-circle"></i>
                        <span>Credenciales incorrectas. Por favor, intente de nuevo.</span>
                    </div>
                </div>
                <?php endif; ?>

                <form method="POST" action="procesar_login.php" class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-900" for="email">
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
                        <label class="text-sm font-medium text-gray-900" for="password">
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
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
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

                <div class="pt-4 border-t border-gray-100">
                    <a href="index.php" class="secondary-button w-full h-10 rounded-md font-medium flex items-center justify-center gap-2">
                        <i class="bi bi-arrow-left"></i>
                        Volver al Inicio
                    </a>
                </div>
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