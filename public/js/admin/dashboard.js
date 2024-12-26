class DashboardManager {
    constructor() {
        this.charts = {};
        this.updateInterval = 30000; // 30 segundos
        this.initializeCharts();
        this.startRealTimeUpdates();
    }

    async initializeCharts() {
        const data = await this.fetchDashboardData();
        this.createCharts(data);
    }

    async fetchDashboardData() {
        try {
            const response = await fetch('/admin/dashboard/data');
            return await response.json();
        } catch (error) {
            console.error('Error fetching dashboard data:', error);
            return null;
        }
    }

    createCharts(data) {
        if (!data) return;

        // Inscripciones por taller
        this.charts.inscripciones = new Chart(
            document.getElementById('inscripcionesChart'),
            {
                type: 'bar',
                data: {
                    labels: data.inscripciones.map(item => item.taller),
                    datasets: [{
                        label: 'Estudiantes Inscritos',
                        data: data.inscripciones.map(item => item.total),
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            }
        );

        // Ingresos mensuales
        this.charts.ingresos = new Chart(
            document.getElementById('ingresosChart'),
            {
                type: 'line',
                data: {
                    labels: data.ingresos.map(item => item.mes),
                    datasets: [{
                        label: 'Ingresos Mensuales (S/.)',
                        data: data.ingresos.map(item => item.total),
                        borderColor: 'rgba(75, 192, 192, 1)',
                        tension: 0.1,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            }
        );

        // Distribución por sede
        this.charts.sedes = new Chart(
            document.getElementById('sedesChart'),
            {
                type: 'doughnut',
                data: {
                    labels: data.sedes.map(item => item.sede),
                    datasets: [{
                        data: data.sedes.map(item => item.total),
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.5)',
                            'rgba(54, 162, 235, 0.5)',
                            'rgba(255, 206, 86, 0.5)',
                            'rgba(75, 192, 192, 0.5)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            }
        );

        // Asistencia semanal
        this.charts.asistencia = new Chart(
            document.getElementById('asistenciaChart'),
            {
                type: 'line',
                data: {
                    labels: data.asistencia.map(item => item.fecha),
                    datasets: [{
                        label: 'Asistencia',
                        data: data.asistencia.map(item => item.porcentaje),
                        borderColor: 'rgba(153, 102, 255, 1)',
                        tension: 0.1,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            min: 0,
                            max: 100,
                            ticks: {
                                callback: value => value + '%'
                            }
                        }
                    }
                }
            }
        );
    }

    startRealTimeUpdates() {
        setInterval(async () => {
            const data = await this.fetchDashboardData();
            if (data) {
                this.updateCharts(data);
            }
        }, this.updateInterval);
    }

    updateCharts(data) {
        Object.keys(this.charts).forEach(chartKey => {
            const chart = this.charts[chartKey];
            const newData = data[chartKey];

            if (newData) {
                chart.data.labels = newData.map(item => item[Object.keys(item)[0]]);
                chart.data.datasets[0].data = newData.map(item => item[Object.keys(item)[1]]);
                chart.update('none');
            }
        });

        this.updateStatistics(data.statistics);
    }

    updateStatistics(statistics) {
        Object.keys(statistics).forEach(key => {
            const element = document.getElementById(key + 'Stat');
            if (element) {
                element.textContent = statistics[key];
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.dashboardManager = new DashboardManager();
});