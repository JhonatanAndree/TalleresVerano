<?php
/**
 * Vista de restablecimiento de contraseña
 * Ruta: views/auth/reset-password.php
 */
$page_title = "Restablecer Contraseña";
require_once __DIR__ . '/../layout/header.php';
?>

<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Restablecer Contraseña
            </h2>
        </div>
        <form class="mt-8 space-y-6" action="/reset-password/<?php echo htmlspecialchars($token); ?>" method="POST" id="resetPasswordForm">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="rounded-md shadow-sm -space-y-px">
                <div class="mb-4">
                    <label for="password" class="sr-only">Nueva contraseña</label>
                    <input id="password" name="password" type="password" required 
                           class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                           placeholder="Nueva contraseña">
                </div>
                <div>
                    <label for="confirm_password" class="sr-only">Confirmar contraseña</label>
                    <input id="confirm_password" name="confirm_password" type="password" required 
                           class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                           placeholder="Confirmar contraseña">
                </div>
            </div>

            <div class="text-sm text-gray-600">
                La contraseña debe contener:
                <ul class="list-disc list-inside mt-2">
                    <li>Al menos 8 caracteres</li>
                    <li>Al menos una letra mayúscula</li>
                    <li>Al menos un número</li>
                    <li>Al menos un carácter especial</li>
                </ul>
            </div>

            <div id="messageContainer" class="hidden">
                <p class="text-sm text-center"></p>
            </div>

            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cambiar contraseña
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('resetPasswordForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    try {
        const response = await fetch(e.target.action, {
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
            messageParagraph.textContent = 'Contraseña actualizada exitosamente';
            setTimeout(() => window.location.href = '/login', 2000);
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