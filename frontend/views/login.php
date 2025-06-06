<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Panel del Doctor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .mesh-background {
            background-color: #f8fafc;
            background-image: 
                radial-gradient(at 40% 20%, rgba(79, 70, 229, 0.1) 0px, transparent 50%),
                radial-gradient(at 80% 0%, rgba(236, 72, 153, 0.1) 0px, transparent 50%),
                radial-gradient(at 0% 50%, rgba(59, 130, 246, 0.1) 0px, transparent 50%),
                radial-gradient(at 80% 50%, rgba(147, 51, 234, 0.1) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(79, 70, 229, 0.1) 0px, transparent 50%),
                radial-gradient(at 80% 100%, rgba(236, 72, 153, 0.1) 0px, transparent 50%);
            position: relative;
        }
        .mesh-background::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%234338ca' fill-opacity='0.02'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.5;
            pointer-events: none;
        }
    </style>
</head>
<body class="h-full mesh-background">
    <div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8 relative">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <img class="mx-auto h-16 w-auto rounded-full shadow-lg bg-white p-2" src="/public/assets/img/logo.jpg" alt="Logo">
            <h2 class="mt-8 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">
                Iniciar Sesión
            </h2>
        </div>

        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white/80 backdrop-blur-sm px-8 py-10 shadow-xl rounded-xl border border-gray-100">
                <?php if (isset($_GET['error'])): ?>
                <div class="mb-6 rounded-lg bg-red-50 p-4 text-sm text-red-800 border border-red-100">
                    <?php 
                    $error = $_GET['error'];
                    switch($error) {
                        case 'invalid':
                            echo 'Correo o contraseña incorrectos';
                            break;
                        case 'inactive':
                            echo 'Su cuenta está desactivada';
                            break;
                        default:
                            echo 'Error al iniciar sesión';
                    }
                    ?>
                </div>
                <?php endif; ?>

                <form class="space-y-6" method="POST" action="procesar_login.php">
                    <div>
                        <label for="email" class="block text-sm font-medium leading-6 text-gray-900">
                            Correo Electrónico
                        </label>
                        <div class="mt-2">
                            <input
                                id="email"
                                name="email"
                                type="email"
                                required
                                class="block w-full rounded-lg border border-gray-200 py-2 px-3 text-gray-900 shadow-sm placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm sm:leading-6 bg-white/50 backdrop-blur-sm"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium leading-6 text-gray-900">
                            Contraseña
                        </label>
                        <div class="mt-2">
                            <input
                                id="password"
                                name="password"
                                type="password"
                                required
                                class="block w-full rounded-lg border border-gray-200 py-2 px-3 text-gray-900 shadow-sm placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm sm:leading-6 bg-white/50 backdrop-blur-sm"
                            >
                        </div>
                    </div>

                    <div>
                        <button
                            type="submit"
                            class="flex w-full justify-center rounded-lg bg-indigo-600 px-3 py-2.5 text-sm font-semibold leading-6 text-white shadow-lg hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all duration-200 hover:shadow-xl hover:scale-[1.02]"
                        >
                            Iniciar Sesión
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 