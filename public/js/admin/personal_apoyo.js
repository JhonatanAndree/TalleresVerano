let tablaPersonal;

document.addEventListener('DOMContentLoaded', () => {
    inicializarTabla();
    cargarSedes();
    setupEventListeners();
});

function inicializarTabla() {
    tablaPersonal = new DataTable('#tablaPersonal', {
        ajax: {
            url: `${BASE_URL}/controllers/PersonalApoyoController.php?action=listar`,
            dataSrc: 'data'
        },
        columns: [
            { data: 'nombres' },
            { data: 'apellidos' },
            { data: 'dni' },
            { data: 'sede_nombre' },
            { data: 'turno' },
            { data: 'celular' },
            {
                data: 'activo',
                render: data => `<span class="badge ${data ? 'bg-success' : 'bg-danger'}">
                    ${data ? 'Activo' : 'Inactivo'}</span>`
            },
            {
                data: 'id',
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-sm btn-warning" onclick="editarPersonal(${data})">
                            Editar
                        </button>
                        <button class="btn btn-sm btn-info" onclick="registrarPago(${data})">
                            Pago
                        </button>
                    `;
                }
            }
        ]
    });
}

async function cargarSedes() {
    try {
        const response = await fetch(`${BASE_URL}/controllers/SedeController.php?action=listar`);
        const data = await response.json();
        const select = document.querySelector('[name="id_sede"]');
        select.innerHTML = '<option value="">Seleccione Sede</option>' +
            data.sedes.map(s => `<option value="${s.id}">${s.nombre}</option>`).join('');
    } catch (error) {
        mostrarError('Error al cargar sedes');
    }
}

function setupEventListeners() {
    document.getElementById('formPersonal').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const response = await fetch(`${BASE_URL}/controllers/PersonalApoyoController.php`, {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                mostrarExito('Personal guardado exitosamente');
                $('#modalPersonal').modal('hide');
                tablaPersonal.ajax.reload();
            } else {
                mostrarError(data.message);
            }
        } catch (error) {
            mostrarError('Error al guardar personal');
        }
    });
}