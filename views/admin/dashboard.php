<?php
/**
 * Vista del Dashboard
 * Ruta: views/admin/dashboard.php
 */
$page_title = "Dashboard";
require_once __DIR__ . '/../layout/header.php';
?>

<div class="p-6">
    <!-- Estadísticas Generales -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700">Total Estudiantes</h3>
            <p class="text-3xl font-bold text-indigo-600" id="totalEstudiantesStat">-</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700">Ingresos Totales</h3>
            <p class="text-3xl font-bold text-green-600" id="totalIngresosStat">-</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700">Talleres Activos</h3>
            <p class="text-3xl font-bold text-blue-600" id="talleresActivosStat">-</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700">Asistencia Promedio</h3>
            <p class="text-3xl font-bold text-purple-600" id="asistenciaPromedioStat">-</p>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Inscripciones por Taller -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Inscripciones por Taller</h3>
            <div class="h-80">
                <canvas id="inscripcionesChart"></canvas>
            </div>
        </div>

        <!-- Ingresos Mensuales -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Ingresos Mensuales</h3>
            <div class="h-80">
                <canvas id="ingresosChart"></canvas>
            </div>
        </div>

        <!-- Distribución por Sede -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Distribución por Sede</h3>
            <div class="h-80">
                <canvas id="sedesChart"></canvas>
            </div>
        </div>

        <!-- Asistencia Semanal -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Asistencia Semanal</h3>
            <div class="h-80">
                <canvas id="asistenciaChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="/public/js/Admin/dashboard.js"></script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>