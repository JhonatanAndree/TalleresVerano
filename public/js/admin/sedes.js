let tablaSedes;

document.addEventListener('DOMContentLoaded', () => {
    inicializarTabla();
    document.getElementById('formSede').addEventListener('submit', manejarSubmitSede);
});

function inicializarTabla() {
    tablaSedes = new DataTable('#tablaSedes', {
        ajax: {
            url: `${BASE_URL}/controllers/SedeController.php?action=listar`,
            dataSrc: 'data'
        },
        columns: [
            { data: 'nombre' },
            { data: 'direccion' },
            { data: 'talleres_activos' },
            { data: 'capacidad_total' },
            { 
                data: 'activo',
                render: data => `<span class="badge ${data ? 'bg-success' : 'bg-danger'}">
                    ${data ? 'Activo' : 'Inactivo'}</span>`
            },
            {
                data: 'id',
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-sm btn-warning" onclick="editarSede(${data})">
                            Editar
                        </button>
                        <button class="btn btn-sm btn-${row.activo ? 'danger' : 'success'}" 
                                onclick="cambiarEstado(${data})">
                            ${row.activo ? 'Desactivar' : 'Activar'}
                        </button>
                    `;
                }
            }
        ]
    });
}