<?php
/**
 * Vista de creación de matrícula
 * Ruta: views/admin/matriculas/create.php
 */
$page_title = "Nueva Matrícula";
require_once __DIR__ . '/../../layout/header.php';
?>

<div class="container mx-auto px-6 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-semibold text-gray-800">Nueva Matrícula</h1>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form id="matriculaForm" class="space-y-6">
            <!-- Datos del Estudiante -->
            <div>
                <h3 class="text-lg font-medium mb-4">Datos del Estudiante</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">DNI Estudiante</label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <input type="text" name="dni_estudiante" id="dni_estudiante" required
                                   class="flex-1 block w-full rounded-md border-gray-300 shadow-sm"
                                   pattern="[0-9]{8}">
                            <button type="button" id="buscarEstudiante"
                                    class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                Buscar
                            </button>
                        </div>
                    </div>
                    <div id="datosEstudiante" class="hidden">
                        <p class="text-sm text-gray-600">
                            <span class="font-medium">Nombre:</span> <span id="nombre_estudiante"></span><br>
                            <span class="font-medium">Apellidos:</span> <span id="apellidos_estudiante"></span><br>
                            <span class="font-medium">Edad:</span> <span id="edad_estudiante"></span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Selección de Taller -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-medium mb-4">Selección de Taller</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Sede</label>
                        <select name="sede_id" id="sede_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Seleccione una sede</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Taller</label>
                        <select name="taller_id" id="taller_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" disabled>
                            <option value="">Seleccione un taller</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Turno</label>
                        <select name="turno" id="turno" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" disabled>
                            <option value="">Seleccione un turno</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Horario -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-medium mb-4">Horario</h3>
                <div id="horarios_disponibles" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Los horarios se cargarán dinámicamente -->
                </div>
            </div>

            <!-- Verificación de Pago -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-medium mb-4">Verificación de Pago</h3>
                <div class="bg-yellow-50 p-4 rounded-md mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Verificación de pago pendiente</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p>Por favor, asegúrese de que el pago ha sido procesado antes de completar la matrícula.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Código de Pago</label>
                    <input type="text" name="codigo_pago" id="codigo_pago" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="flex justify-end space-x-4 pt-6">
                <a href="/admin/matriculas" 
                   class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                    Registrar Matrícula
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cargar sedes
    fetch('/admin/sedes/activas')
        .then(response => response.json())
        .then(sedes => {
            const sedeSelect = document.getElementById('sede_id');
            sedes.forEach(sede => {
                const option = new Option(sede.nombre, sede.id);
                sedeSelect.add(option);
            });
        });

    // Cargar talleres al seleccionar sede
    document.getElementById('sede_id').addEventListener('change', function() {
        const tallerSelect = document.getElementById('taller_id');
        const turnoSelect = document.getElementById('turno');
        
        tallerSelect.disabled = false;
        tallerSelect.innerHTML = '<option value="">Seleccione un taller</option>';
        turnoSelect.disabled = true;
        turnoSelect.innerHTML = '<option value="">Seleccione un turno</option>';
        
        if (this.value) {
            fetch(`/admin/talleres/por-sede/${this.value}`)
                .then(response => response.json())
                .then(talleres => {
                    talleres.forEach(taller => {
                        const option = new Option(taller.nombre, taller.id);
                        tallerSelect.add(option);
                    });
                });
        }
    });

    // Cargar turnos al seleccionar taller
    document.getElementById('taller_id').addEventListener('change', function() {
        const turnoSelect = document.getElementById('turno');
        turnoSelect.disabled = false;
        turnoSelect.innerHTML = '<option value="">Seleccione un turno</option>';
        
        if (this.value) {
            fetch(`/admin/turnos/por-taller/${this.value}`)
                .then(response => response.json())
                .then(turnos => {
                    turnos.forEach(turno => {
                        const option = new Option(turno.nombre, turno.id);
                        turnoSelect.add(option);
                    });
                });
        }
    });

    // Cargar horarios al seleccionar turno
    document.getElementById('turno').addEventListener('change', function() {
        const tallerId = document.getElementById('taller_id').value;
        if (this.value && tallerId) {
            fetch(`/admin/horarios/disponibles/${tallerId}/${this.value}`)
                .then(response => response.json())
                .then(horarios => {
                    const container = document.getElementById('horarios_disponibles');
                    container.innerHTML = '';
                    
                    horarios.forEach(horario => {
                        const div = document.createElement('div');
                        div.className = 'border rounded p-4';
                        div.innerHTML = `
                            <label class="flex items-center space-x-3">
                                <input type="radio" name="horario_id" value="${horario.id}" required
                                       class="form-radio text-blue-600">
                                <span>${horario.hora_inicio} - ${horario.hora_fin}</span>
                            </label>
                        `;
                        container.appendChild(div);
                    });
                });
        }
    });

    // Buscar estudiante
    document.getElementById('buscarEstudiante').addEventListener('click', function() {
        const dni = document.getElementById('dni_estudiante').value;
        if (dni) {
            fetch(`/admin/estudiantes/buscar/${dni}`)
                .then(response => response.json())
                .then(estudiante => {
                    if (estudiante) {
                        document.getElementById('nombre_estudiante').textContent = estudiante.nombre;
                        document.getElementById('apellidos_estudiante').textContent = estudiante.apellido;
                        document.getElementById('edad_estudiante').textContent = estudiante.edad;
                        document.getElementById('datosEstudiante').classList.remove('hidden');
                    } else {
                        alert('Estudiante no encontrado');
                    }
                });
        }
    });

    // Enviar formulario
    document.getElementById('matriculaForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        try {
            const response = await fetch('/admin/matriculas/create', {
                method: 'POST',
                body: new FormData(this)
            });
            
            const result = await response.json();
            if (result.success) {
                window.location.href = `/admin/matriculas/${result.id}`;
            } else {
                alert(result.error);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al procesar la matrícula');
        }
    });
});
</script>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>