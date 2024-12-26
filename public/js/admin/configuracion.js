const BASE_URL = 'https://muniporvenir.gob.pe/talleresdeverano';

document.addEventListener('DOMContentLoaded', () => {
    cargarConfiguracion();
    cargarAnosFiscales();

    setupFormListeners();
});

async function cargarConfiguracion() {
    try {
        const response = await fetch(`${BASE_URL}/controllers/ConfiguracionController.php?action=getConfig`);
        const data = await response.json();
        if (data.success) {
            Object.entries(data.config).forEach(([key, value]) => {
                const input = document.querySelector(`[name="${key}"]`);
                if (input) input.value = value;
            });
        }
    } catch (error) {
        mostrarError('Error al cargar configuración');
    }
}

async function cargarAnosFiscales() {
    try {
        const response = await fetch(`${BASE_URL}/controllers/ConfiguracionController.php?action=getAnosFiscales`);
        const data = await response.json();
        if (data.success) {
            const tabla = document.getElementById('tablaAnosFiscales');
            tabla.innerHTML = data.anos.map(ano => `
                <tr>
                    <td>${ano.ano}</td>
                    <td>
                        <span class="badge ${ano.activo ? 'bg-success' : 'bg-secondary'}">
                            ${ano.activo ? 'Activo' : 'Inactivo'}
                        </span>
                    </td>
                    <td>
                        ${!ano.activo ? `
                            <button class="btn btn-sm btn-primary" 
                                    onclick="activarAnoFiscal(${ano.ano})">
                                Activar
                            </button>
                        ` : ''}
                    </td>
                </tr>
            `).join('');
        }
    } catch (error) {
        mostrarError('Error al cargar años fiscales');
    }
}

function setupFormListeners() {
    // Configuración General
    document.getElementById('formConfigGeneral').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const response = await fetch(`${BASE_URL}/controllers/ConfiguracionController.php`, {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                mostrarExito('Configuración actualizada');
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            mostrarError(error.message);
        }
    });

    // Año Fiscal
    document.getElementById('formAnoFiscal').addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!confirm('¿Está seguro de cambiar el año fiscal? Esta acción es irreversible.')) {
            return;
        }

        const formData = new FormData(e.target);
        try {
            const response = await fetch(`${BASE_URL}/controllers/ConfiguracionController.php`, {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                mostrarExito('Año fiscal actualizado');
                cargarAnosFiscales();
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            mostrarError(error.message);
        }
    });

    // Configuración de Pagos
    document.getElementById('formConfigPagos').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const response = await fetch(`${BASE_URL}/controllers/ConfiguracionController.php`, {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                mostrarExito('Configuración de pagos actualizada');
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            mostrarError(error.message);
        }
    });
}

async function activarAnoFiscal(ano) {
    if (!confirm(`¿Está seguro de activar el año fiscal ${ano}?`)) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('action', 'activarAnoFiscal');
        formData.append('ano', ano);

        const response = await fetch(`${BASE_URL}/controllers/ConfiguracionController.php`, {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.success) {
            mostrarExito('Año fiscal activado');
            cargarAnosFiscales();
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        mostrarError(error.message);
    }
}

function mostrarExito(mensaje) {
    Swal.fire({
        icon: 'success',
        title: 'Éxito',
        text: mensaje,
        timer: 2000
    });
}

function mostrarError(mensaje) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: mensaje
    });
}