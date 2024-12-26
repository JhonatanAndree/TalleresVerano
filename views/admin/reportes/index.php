<?php
/**
 * Vista principal de reportes
 * Ruta: views/admin/reportes/index.php
 */
$page_title = "Reportes";
require_once __DIR__ . '/../../layout/header.php';
?>

<div class="container mx-auto px-6 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-semibold text-gray-800">Reportes</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Reporte de Inscripciones -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Inscripciones</h3>
            <p class="text-gray-600 mb-4">Reporte detallado de inscripciones por taller, sede y período.</p>
            <a href="/admin/reportes/generar?tipo=inscripciones" 
               class="inline-block bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Generar Reporte
            </a>
        </div>

        <!-- Reporte de Ingresos -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Ingresos</h3>
            <p class="text-gray-600 mb-4">Análisis de ingresos, pagos y transacciones financieras.</p>
            <a href="/admin/reportes/generar?tipo=ingresos" 
               class="inline-block bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                Generar Reporte
            </a>
        </div>

        <!-- Reporte de Asistencia -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Asistencia</h3>
            <p class="text-gray-600 mb-4">Control de asistencia por taller y estudiante.</p>
            <a href="/admin/reportes/generar?tipo=asistencia" 
               class="inline-block bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">
                Generar Reporte
            </a>
        </div>

        <!-- Reporte de Docentes -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Docentes</h3>
            <p class="text-gray-600 mb-4">Desempeño y carga horaria de docentes.</p>
            <a href="/admin/reportes/generar?tipo=docentes" 
               class="inline-block bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                Generar Reporte
            </a>
        </div>

        <!-- Reporte de Sedes -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Sedes</h3>
            <p class="text-gray-600 mb-4">Distribución y ocupación por sede.</p>
            <a href="/admin/reportes/generar?tipo=sedes" 
               class="inline-block bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                Generar Reporte
            </a>
        </div>

        <!-- Reporte Personalizado -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Personalizado</h3>
            <p class="text-gray-600 mb-4">Crea un reporte con parámetros específicos.</p>
            <a href="/admin/reportes/generar?tipo=personalizado" 
               class="inline-block bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                Crear Reporte
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>