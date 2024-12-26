<?php
/**
 * Vista de solicitud de restablecimiento
 * Ruta: views/auth/request-reset.php
 */
$page_title = "Recuperar Contraseña";
require_once __DIR__ . '/../layout/header.php';
?>

<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Recuperar Contraseña
            </h2>
        </div>
        <form class="mt-8 space-y-6" action="/request-reset" method="POST" id="resetForm">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="email" class="sr-only">Correo electrónico</label>
                    <input id="email" name="email" type="email" required 
                           class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                           placeholder="Correo electrónico">
                </div>
            </div>

            <div id="messageContainer" class="hidden">
                <p class="text-sm text-center"></p>
            </div>

            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Enviar instrucciones
                </button>
            </div>

            <div class="text-sm text-center">
                <a href="/login" class="font-medium text-indigo-600 hover:text-indigo-500">
                    Volver al inicio de sesión
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('resetForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    try {
        const response = await fetch('/request-reset', {
            method: 'POST',
            body: new FormData(e.target)
        });
        
        const data = await response.json();
        const messageContainer = document.getElementById('messageContainer');
        const messageParagraph = messageContainer.querySelector('p');
        
        messageContainer.classList.remove('hidden');
        if (data.success) {
            messageContainer.classList.remove('text-red-600');
            messageContainer.classList.add('text-green-600');
            messageParagraph.textContent = data.message;
        } else {
            messageContainer.classList.remove('text-green-600');
            messageContainer.classList.add('text-red-600');
            messageParagraph.textContent = data.error;
        }
    } catch (error) {
        console.error('Error:', error);
    }
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>