const BASE_URL = 'https://muniporvenir.gob.pe/talleresdeverano';

document.getElementById('consultaForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
        const response = await fetch(`${BASE_URL}/controllers/ConsultaController.php`, {
            method: 'POST',
            body: new FormData(e.target)
        });

        const data = await response.json();
        if (data.success) {
            mostrarDatosEstudiante(data.data);
        } else {
            mostrarError(data.message);
        }
    } catch (error) {
        mostrarError('Error en la consulta');
    }
});

function mostrarDatosEstudiante(datos) {
    document.getElementById('nombre_estudiante').textContent = `${datos.nombre} ${datos.apellido}`;
    document.getElementById('sede').textContent = datos.sede_nombre;
    document.getElementById('taller').textContent = datos.taller_nombre;
    document.getElementById('docente').textContent = datos.docente_nombre;
    document.getElementById('horario').textContent = datos.intervalo_tiempo;
    document.getElementById('turno').textContent = datos.turno;

    document.getElementById('resultados').classList.remove('d-none');
}