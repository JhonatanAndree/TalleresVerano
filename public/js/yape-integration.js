class YapePayment {
    constructor(config) {
        this.config = {
            endpoint: config.endpoint || '/api/yape',
            checkInterval: config.checkInterval || 5000,
            maxAttempts: config.maxAttempts || 60,
            onSuccess: config.onSuccess || function() {},
            onError: config.onError || function() {},
            onExpired: config.onExpired || function() {},
            csrf_token: config.csrf_token
        };
        this.attempts = 0;
        this.checkInterval = null;
    }

    async initializePayment(data) {
        try {
            const response = await fetch(`${this.config.endpoint}/initialize`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.config.csrf_token
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                this.showQRCode(result.qr_data);
                this.startPaymentCheck(result.transaction_id);
            } else {
                this.config.onError(result.error);
            }
        } catch (error) {
            this.config.onError('Error iniciando pago');
        }
    }

    showQRCode(qrData) {
        const qr = new QRCode(document.getElementById("yapeQRContainer"), {
            text: qrData,
            width: 256,
            height: 256
        });
    }

    startPaymentCheck(transactionId) {
        this.checkInterval = setInterval(async () => {
            this.attempts++;

            if (this.attempts >= this.config.maxAttempts) {
                this.stopPaymentCheck();
                this.config.onExpired();
                return;
            }

            try {
                const response = await fetch(`${this.config.endpoint}/verify`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.config.csrf_token
                    },
                    body: JSON.stringify({ transaction_id: transactionId })
                });

                const result = await response.json();

                if (result.success) {
                    if (result.status === 'completed') {
                        this.stopPaymentCheck();
                        this.config.onSuccess(result);
                    }
                } else {
                    this.stopPaymentCheck();
                    this.config.onError(result.error);
                }
            } catch (error) {
                this.stopPaymentCheck();
                this.config.onError('Error verificando pago');
            }
        }, this.config.checkInterval);
    }

    stopPaymentCheck() {
        if (this.checkInterval) {
            clearInterval(this.checkInterval);
            this.checkInterval = null;
        }
    }

    updatePaymentStatus(status) {
        const statusContainer = document.getElementById('yapePaymentStatus');
        if (statusContainer) {
            statusContainer.textContent = status;
        }
    }

    showError(message) {
        const errorContainer = document.getElementById('yapeErrorContainer');
        if (errorContainer) {
            errorContainer.textContent = message;
            errorContainer.classList.remove('hidden');
        }
    }

    hideError() {
        const errorContainer = document.getElementById('yapeErrorContainer');
        if (errorContainer) {
            errorContainer.classList.add('hidden');
        }
    }

    reset() {
        this.stopPaymentCheck();
        this.attempts = 0;
        this.hideError();
        const qrContainer = document.getElementById("yapeQRContainer");
        if (qrContainer) {
            qrContainer.innerHTML = '';
        }
    }
}

// Ejemplo de uso
document.addEventListener('DOMContentLoaded', function() {
    const yapePay = new YapePayment({
        endpoint: '/api/yape',
        csrf_token: document.querySelector('meta[name="csrf-token"]').content,
        onSuccess: function(result) {
            // Actualizar UI y redirigir
            window.location.href = '/pago/confirmacion/' + result.transaction_id;
        },
        onError: function(error) {
            // Mostrar error al usuario
            alert('Error en el pago: ' + error);
        },
        onExpired: function() {
            // Mostrar mensaje de expiración
            alert('El tiempo para realizar el pago ha expirado');
        }
    });

    // Botón para iniciar pago
    const pagoBtn = document.getElementById('iniciarPagoYape');
    if (pagoBtn) {
        pagoBtn.addEventListener('click', function() {
            yapePay.initializePayment({
                estudiante_id: this.dataset.estudianteId,
                taller_id: this.dataset.tallerId,
                monto: this.dataset.monto
            });
        });
    }
});