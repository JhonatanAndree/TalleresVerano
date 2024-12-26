<?php
/**
 * Vista de pago Yape
 * Ruta: views/payment/yape.php
 */
$page_title = "Pago con Yape";
require_once __DIR__ . '/../layout/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-xl mx-auto">
        <!-- Encabezado -->
        <div class="bg-white rounded-t-lg shadow-sm p-6 border-b">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">Pago con Yape</h1>
                <img src="/public/img/yape-logo.png" alt="Yape" class="h-8">
            </div>
        </div>

        <!-- Detalles del Pago -->
        <div class="bg-white shadow-sm p-6">
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Monto a pagar:</span>
                    <span class="text-2xl font-bold text-indigo-600">S/. <?php echo number_format($monto, 2); ?></span>
                </div>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="block text-gray-500">Estudiante</span>
                        <span class="font-medium"><?php echo htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellido']); ?></span>
                    </div>
                    <div>
                        <span class="block text-gray-500">DNI</span>
                        <span class="font-medium"><?php echo htmlspecialchars($estudiante['dni']); ?></span>
                    </div>
                    <div>
                        <span class="block text-gray-500">Taller</span>
                        <span class="font-medium"><?php echo htmlspecialchars($taller['nombre']); ?></span>
                    </div>
                    <div>
                        <span class="block text-gray-500">Sede</span>
                        <span class="font-medium"><?php echo htmlspecialchars($taller['sede']); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenedor QR y Estado -->
        <div class="bg-white p-6 flex space-x-6">
            <div class="flex-1">
                <div id="qrContainer" class="bg-gray-50 rounded-lg p-4 flex items-center justify-center" style="height: 240px">
                    <div class="text-gray-400">Generando código QR...</div>
                </div>
            </div>
            <div class="flex-1 flex flex-col justify-between">
                <div>
                    <div id="statusMessage" class="text-lg font-medium text-gray-900 mb-2">
                        Esperando pago...
                    </div>
                    <div id="timer" class="text-sm text-gray-500">
                        Expira en: <span class="font-medium text-gray-900">15:00</span>
                    </div>
                </div>
                <div class="space-y-4">
                    <button id="btnReintentar" class="hidden w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Reintentar
                    </button>
                    <a href="/matricula/<?php echo $matricula['id']; ?>" class="block text-center w-full px-4 py-2 text-indigo-600 hover:text-indigo-900">
                        Cancelar
                    </a>
                </div>
            </div>
        </div>

        <!-- Instrucciones -->
        <div class="bg-white rounded-b-lg shadow-sm p-6 border-t">
            <h3 class="font-medium text-gray-900 mb-4">Instrucciones de pago</h3>
            <div class="space-y-3 text-sm">
                <div class="flex items-start">
                    <div class="flex-shrink-0 h-5 w-5 relative mt-1">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-xs font-medium text-indigo-600">1</span>
                        </div>
                    </div>
                    <p class="ml-2 text-gray-500">Abre tu aplicación Yape</p>
                </div>
                <div class="flex items-start">
                    <div class="flex-shrink-0 h-5 w-5 relative mt-1">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-xs font-medium text-indigo-600">2</span>
                        </div>
                    </div>
                    <p class="ml-2 text-gray-500">Selecciona la opción "Pagar con QR"</p>
                </div>
                <div class="flex items-start">
                    <div class="flex-shrink-0 h-5 w-5 relative mt-1">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-xs font-medium text-indigo-600">3</span>
                        </div>
                    </div>
                    <p class="ml-2 text-gray-500">Escanea el código QR mostrado</p>
                </div>
                <div class="flex items-start">
                    <div class="flex-shrink-0 h-5 w-5 relative mt-1">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-xs font-medium text-indigo-600">4</span>
                        </div>
                    </div>
                    <p class="ml-2 text-gray-500">Verifica el monto y confirma el pago</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notifications -->
<div id="toast" class="fixed bottom-4 right-4 transform transition-all duration-300 translate-y-full">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden max-w-sm">
        <div id="toastContent" class="p-4"></div>
    </div>
</div>

<!-- Modal de Confirmación -->
<div id="confirmationModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="rounded-full bg-green-100 p-3">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-lg font-medium text-center mb-2">¡Pago Confirmado!</h3>
                <p class="text-gray-500 text-center mb-6">Tu pago ha sido procesado exitosamente.</p>
                <div class="flex justify-center">
                    <a href="/matricula/comprobante/<?php echo $matricula['id']; ?>" 
                       class="inline-flex justify-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Ver Comprobante
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js"></script>
<script src="/public/js/payment/yape.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const yapePayment = new YapePayment({
        matriculaId: <?php echo $matricula['id']; ?>,
        monto: <?php echo $monto; ?>,
        csrf_token: '<?php echo $csrf_token; ?>',
        timeoutMinutes: 15,
        onSuccess: function(response) {
            document.getElementById('confirmationModal').classList.remove('hidden');
        },
        onError: function(error) {
            showToast(error, 'error');
            document.getElementById('btnReintentar').classList.remove('hidden');
        },
        onExpired: function() {
            showToast('El tiempo para realizar el pago ha expirado', 'warning');
            document.getElementById('btnReintentar').classList.remove('hidden');
        }
    });

    yapePayment.initialize();
});

function showToast(message, type = 'info') {
    const toast = document.getElementById('toast');
    const toastContent = document.getElementById('toastContent');
    
    const bgColors = {
        'success': 'bg-green-100 text-green-800',
        'error': 'bg-red-100 text-red-800',
        'warning': 'bg-yellow-100 text-yellow-800',
        'info': 'bg-blue-100 text-blue-800'
    };

    toastContent.className = `p-4 ${bgColors[type]}`;
    toastContent.textContent = message;
    
    toast.classList.remove('translate-y-full');
    setTimeout(() => {
        toast.classList.add('translate-y-full');
    }, 5000);
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>