<?php
/**
 * Barra de navegación superior
 * Ruta: views/layout/navbar.php
 */
?>
<nav class="bg-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="flex-shrink-0 flex items-center">
                    <img class="h-8 w-auto" src="/public/img/logo.png" alt="Logo">
                </div>
            </div>
            <div class="flex items-center">
                <div class="ml-3 relative">
                    <div>
                        <button id="userMenuButton" class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300">
                            <img class="h-8 w-8 rounded-full" src="<?php echo $_SESSION['user']['avatar'] ?? '/public/img/default-avatar.png'; ?>" alt="">
                        </button>
                    </div>
                    <div id="userMenu" class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg">
                        <div class="py-1 rounded-md bg-white shadow-xs">
                            <a href="/perfil" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Mi Perfil</a>
                            <a href="/cambiar-contrasena" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Cambiar Contraseña</a>
                            <a href="/logout" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Cerrar Sesión</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>