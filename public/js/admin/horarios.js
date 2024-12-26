let calendario;

document.addEventListener('DOMContentLoaded', () => {
    inicializarCalendario();
    cargarFiltros();
    setupEventListeners();
});

function inicializarCalendario() {
    const calendarEl = document.getElementById('calendarioHorarios');
    calendario = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        slotMinTime: '07:00:00',
        slotMaxTime: '19:00:00',
        allDaySlot: false,
        locale: 'es',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'timeGridWeek,timeGridDay'
        },
        events: cargarHorarios,
        eventClick: mostrarDetallesHorario,
        eventOverlap: false,
        slotEventOverlap: false
    });
    calendario.render();
}

async function cargarHorarios(info, successCallback) {
    try {
        const response = await fetch(`${BASE_URL}/controllers/HorarioController.php?action=listar`);
        const data = await response.json();
        successCallback(data.horarios.map(h => ({
            id: h.id,
            title: h.taller_nombre,
            start: h.hora_inicio,
            end: h.hora_fin,
            backgroundColor: h.turno === 'Mañana' ? '#4CAF50' : '#2196F3'
        })));
    } catch (error) {
        console.error(error);
        mostrarError('Error al cargar horarios');
    }
}

function setupEventListeners() {
    document.getElementById('formHorario').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const response = await fetch(`${BASE_URL}/controllers/HorarioController.php`, {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                mostrarExito('Horario guardado exitosamente');
                $('#modalHorario').modal('hide');
                calendario.refetchEvents();
            } else {
                mostrarError(data.message);
            }
        } catch (error) {
            mostrarError('Error al guardar horario');
        }
    });
}