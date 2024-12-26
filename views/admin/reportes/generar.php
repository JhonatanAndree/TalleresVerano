<?php
/**
 * Vista de generación de reportes
 * Ruta: views/admin/reportes/generar.php
 */
$page_title = "Generar Reporte";
require_once __DIR__ . '/../../layout/header.php';

$tipo = $_GET['tipo'] ?? 'personalizado';
?>

<div class="container mx-auto px-6 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-semibold text-gray-800">
            Generar Reporte: <?php echo ucfirst($tipo); ?>
        </h1>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form id="reportForm" class="space-y-6">
            <input type="hidden" name="tipo" value="<?php echo htmlspecialchars($tipo); ?>">
            
            <!-- Filtros comunes -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Fecha Fin</label>
                    <input type="date" name="fecha_fin" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
            </div>

            <!-- Filtros específicos según tipo -->
            <?php if ($tipo === 'inscripciones' || $tipo === 'asistencia'): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Sede</label>
                    <select name="sede_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Todas las sedes</option>
                        <?php foreach ($sedes as $sede): ?>
                        <option value="<?php echo $sede['id']; ?>">
                            <?php echo htmlspecialchars($sede['nombre']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Taller</label>
                    <select name="taller_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Todos los talleres</option>
                    </select>
                </div>
            </div>
            <?php endif; ?>

            <!-- Opciones de visualización -->
            <div class="border-t pt-6">
                <h4 class="text-lg font-medium mb-4">Opciones de visualización</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex items-center">
                        <input type="checkbox" name="mostrar_graficos" id="mostrar_graficos" 
                               class="rounded border-gray-300 text-blue-600 shadow-sm">
                        <label for="mostrar_graficos" class="ml-2">Incluir gráficos</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="agrupar_por_sede" id="agrupar_por_sede" 
                               class="rounded border-gray-300 text-blue-600 shadow-sm">
                        <label for="agrupar_por_sede" class="ml-2">Agrupar por sede</label>
                    </div>
                </div>
            </div>

            <!-- Formato de exportación -->
            <div class="border-t pt-6">
                <h4 class="text-lg font-medium mb-4">Formato de exportación</h4>
                <div class="flex space-x-4">
                    <label class="inline-flex items-center">
                        <input type="radio" name="formato" value="pdf" checked 
                               class="text-blue-600 border-gray-300">
                        <span class="ml-2">PDF</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="formato" value="excel" 
                               class="text-blue-600 border-gray-300">
                        <span class="ml-2">Excel</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="formato" value="csv" 
                               class="text-blue-600 border-gray-300">
                        <span class="ml-2">CSV</span>
                    </label>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="flex justify-end space-x-4 pt-6">
                <button type="button" id="previewBtn"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Vista Previa
                </button>
                <button type="submit"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                    Generar Reporte
                </button>
            </div>
        </form>
    </div>

    <!-- Vista previa del reporte -->
    <div id="reportPreview" class="mt-8 bg-white rounded-lg shadow-md p-6 hidden">
        <h2 class="text-xl font-semibold mb-4">Vista Previa</h2>
        <div id="previewContent"></div>
    </div>
</div>

<script src="/public/js/Admin/reports.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cargar talleres según sede seleccionada
    const sedeSelect = document.querySelector('select[name="sede_id"]');
    const tallerSelect = document.querySelector('select[name="taller_id"]');
    
    if (sedeSelect && tallerSelect) {
        sedeSelect.addEventListener('change', async function() {
            const sedeId = this.value;
            if (sedeId) {
                const response = await fetch(`/admin/talleres/por-sede/${sedeId}`);
                const talleres = await response.json();
                
                tallerSelect.innerHTML = '<option value="">Todos los talleres</option>';
                talleres.forEach(taller => {
                    tallerSelect.innerHTML += `
                        <option value="${taller.id}">${taller.nombre}</option>
                    `;
                });
            }
        });
    }

    // Vista previa
    document.getElementById('previewBtn').addEventListener('click', async function() {
        const formData = new FormData(document.getElementById('reportForm'));
        formData.append('preview', '1');
        
        try {
            const response = await fetch('/admin/reportes/preview', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            if (data.success) {
                document.getElementById('previewContent').innerHTML = data.html;
                document.getElementById('reportPreview').classList.remove('hidden');
            }
        } catch (error) {
            console.error('Error en vista previa:', error);
        }
    });
});
</script>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>