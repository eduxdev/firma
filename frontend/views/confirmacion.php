<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario Enviado - Confirmación</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
</head>
<body class="h-full bg-gradient-to-b from-blue-50 to-white">
    <!-- Navbar -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="container mx-auto px-6 py-3">
            <div class="flex items-center justify-between">
                <img src="/public/assets/img/logo.jpg" alt="Logo" class="h-12 object-contain">
                <a href="/" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2 hover:bg-gray-100 rounded-md">
                    <i class="bi bi-house-door me-2"></i>
                    Volver al Inicio
                </a>
            </div>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <div class="container mx-auto px-4 py-16">
        <div class="max-w-2xl mx-auto">
            <!-- Tarjeta de Confirmación -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden animate__animated animate__fadeInUp">
                <div class="p-8">
                    <!-- Ícono de Éxito -->
                    <div class="flex justify-center mb-8">
                        <div class="rounded-full bg-green-100 p-3">
                            <div class="rounded-full bg-green-200 p-3">
                                <i class="bi bi-check-circle-fill text-4xl text-green-600"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Mensaje Principal -->
                    <div class="text-center space-y-4">
                        <h2 class="text-3xl font-bold text-gray-900">¡Formulario Enviado!</h2>
                        <p class="text-gray-600 text-lg max-w-md mx-auto">
                            Tu formulario ha sido recibido correctamente y será revisado por nuestro equipo médico a la brevedad posible.
                        </p>
                    </div>

                    <!-- Detalles Adicionales -->
                    <div class="mt-8 bg-blue-50 rounded-xl p-6">
                        <div class="flex items-start space-x-3">
                            <i class="bi bi-info-circle-fill text-blue-600 text-xl mt-0.5"></i>
                            <div>
                                <h3 class="font-semibold text-blue-900 mb-1">¿Qué sigue?</h3>
                                <p class="text-blue-800 text-sm">
                                    Nuestro equipo médico revisará tu información y procederá con el proceso correspondiente. Te mantendremos informado sobre el estado de tu solicitud.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botón de Acción -->
            <div class="mt-8 text-center animate__animated animate__fadeIn animate__delay-1s">
                <a href="/" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-6 py-3 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 transition-all duration-200">
                    <i class="bi bi-arrow-left me-2"></i>
                    Volver al Inicio
                </a>
            </div>
        </div>
    </div>
</body>
</html> 