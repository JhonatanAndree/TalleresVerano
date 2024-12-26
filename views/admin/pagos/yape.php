<?php require_once __DIR__ . '/../../layout/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-lg mx-auto bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-6">Pago con Yape</h2>
        
        <!-- Detalles del pago -->
        <div class="mb-6">
            <p class="text-gray-700"><strong>Estudiante:</strong> <?php echo htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellido']); ?></p>
            <p class="text-gray-700"><strong>Taller:</strong> <?php echo htmlspecialchars($taller['nombre']); ?></p>
            <p class="text-gray-700"><strong>Monto:</strong> S/. <?php echo number_format($monto, 2); ?></p>
        </div>

        <!-- Contenedor QR -->
        <div id="yapeQRContainer" class="flex justify-center mb-6">
            <!-- El QR se generará aquí -->
        </div>

        <!-- Estado del pago -->
        <div class="text-center mb-6">
            <p id="yapePaymentStatus" class="text-lg font-medium">Esperando pago...</p>
        </div>

        <!-- Mensajes de error -->
        <div id="yapeErrorContainer" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
        </div>

        <!-- Botón de inicio -->
        <button 
            id="iniciarPagoYape"
            data-estudiante-id="<?php echo $estudiante['id']; ?>"
            data-taller-id="<?php echo $taller['id']; ?>"
            data-monto="<?php echo $monto; ?>"
            class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
            Iniciar Pago
        </button>

        <!-- Instrucciones -->
        <div class="mt-6 text-sm text-gray-600">
            <h3 class="font-bold mb-2">Instrucciones:</h3>
            <ol class="list-decimal list-inside">
                <li>Abre tu aplicación Yape</li>
                <li>Escanea el código QR</li>
                <li>Verifica el monto a pagar</li>
                <li>Confirma el pago en tu aplicación</li>
                <li>Espera la confirmación en esta página</li>
            </ol>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="/public/js/yape-integration.js"></script>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>