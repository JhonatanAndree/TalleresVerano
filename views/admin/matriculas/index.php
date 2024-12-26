<?php
/**
 * Vista de listado de matrículas
 * Ruta: views/admin/matriculas/index.php
 */
$page_title = "Matrículas";
require_once __DIR__ . '/../../layout/header.php';
?>

<div class="container mx-auto px-6 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-semibold text-gray-800">Matrículas</h1>
        <a href="/admin/matriculas/create" 
           class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
            Nueva Matrícula
        </a>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form id="filtrosForm" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Sede</label>
                <select name="sede_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Todas las sedes</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Taller</label>
                <select name="taller_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Todos los talleres</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Estado</label>
                <select name="estado" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Todos</option>
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Búsqueda</label>
                <input type="text" name="busqueda" 
                       placeholder="DNI o nombre del estudiante"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
        </form>
    </div>

    <!-- Tabla de matrículas -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estudiante
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            DNI
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Taller
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Sede
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Horario
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estado
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="matriculasTableBody">
                    <!-- Los datos se cargarán dinámicamente -->
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-700">
                    Mostrando <span id="paginationStart">0</span> a <span id="paginationEnd">0</span> de <span id="paginationTotal">0</span> resultados
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                        <button id="prevPage" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            Anterior
                        </button>
                        <button id="nextPage" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            Siguiente
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variables de paginación
    let currentPage = 1;
    const itemsPerPage = 10;

    // Cargar datos iniciales
    loadMatriculas();
    loadSedes();

    // Event listeners para filtros
    document.getElementById('filtrosForm').addEventListener('change', function() {
        currentPage = 1;
        loadMatriculas();
    });

    // Event listeners para paginación
    document.getElementById('prevPage').addEventListener('click', function() {
        if (currentPage > 1) {
            currentPage--;
            loadMatriculas();
        }
    });

    document.getElementById('nextPage').addEventListener('click', function() {
        currentPage++;
        loadMatriculas();
    });

    // Funciones de carga
    async function loadMatriculas() {
        try {
            const formData = new FormData(document.getElementById('filtrosForm'));
            formData.append('page', currentPage);
            formData.append('per_page', itemsPerPage);

            const response = await fetch('/admin/matriculas/list?' + new URLSearchParams(formData));
            const data = await response.json();

            renderMatriculas(data.matriculas);
            updatePagination(data.pagination);
        } catch (error) {
            console.error('Error loading matriculas:', error);
        }
    }

    async function loadSedes() {
        try {
            const response = await fetch('/admin/sedes/list');
            const sedes = await response.json();
            
            const sedeSelect = document.querySelector('select[name="sede_id"]');
            sedes.forEach(sede => {
                const option = new Option(sede.nombre, sede.id);
                sedeSelect.add(option);
            });
        } catch (error) {
            console.error('Error loading sedes:', error);
        }
    }

    function renderMatriculas(matriculas) {
        const tbody = document.getElementById('matriculasTableBody');
        tbody.innerHTML = '';

        matriculas.forEach(matricula => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">
                        ${matricula.estudiante_nombre} ${matricula.estudiante_apellido}
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${matricula.estudiante_dni}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${matricula.taller_nombre}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${matricula.sede_nombre}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${matricula.horario}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                ${matricula.estado === 'activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                        ${matricula.estado}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <a href="/admin/matriculas/${matricula.id}" class="text-indigo-600 hover:text-indigo-900 mr-3">Ver</a>
                    <a href="/admin/matriculas/${matricula.id}/edit" class="text-yellow-600 hover:text-yellow-900 mr-3">Editar</a>
                    <button onclick="desactivarMatricula(${matricula.id})" class="text-red-600 hover:text-red-900">
                        Desactivar
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    function updatePagination(pagination) {
        document.getElementById('paginationStart').textContent = pagination.from;
        document.getElementById('paginationEnd').textContent = pagination.to;
        document.getElementById('paginationTotal').textContent = pagination.total;

        document.getElementById('prevPage').disabled = currentPage === 1;
        document.getElementById('nextPage').disabled = currentPage === pagination.total_pages;
    }
});

async function desactivarMatricula(id) {
    if (confirm('¿Está seguro de que desea desactivar esta matrícula?')) {
        try {
            const response = await fetch(`/admin/matriculas/${id}/deactivate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            const result = await response.json();
            if (result.success) {
                location.reload();
            } else {
                alert('Error al desactivar la matrícula');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        }
    }
}
</script>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>