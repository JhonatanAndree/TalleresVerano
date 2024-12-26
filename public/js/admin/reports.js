class ReportManager {
    constructor(config = {}) {
        this.config = {
            baseUrl: '/admin/reports',
            container: '#reportContainer',
            filters: '#reportFilters',
            ...config
        };
        this.initializeEvents();
    }

    initializeEvents() {
        const filtersForm = document.querySelector(this.config.filters);
        if (filtersForm) {
            filtersForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.generateReport();
            });
        }

        document.querySelectorAll('.export-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                this.exportReport(btn.dataset.format);
            });
        });
    }

    async generateReport() {
        const filters = new FormData(document.querySelector(this.config.filters));
        const params = new URLSearchParams(filters);

        try {
            const response = await fetch(`${this.config.baseUrl}/generate?${params}`);
            const data = await response.json();

            if (data.success) {
                this.renderReport(data.report);
                this.updateCharts(data.charts);
            } else {
                throw new Error(data.error);
            }
        } catch (error) {
            this.showError('Error generando reporte: ' + error.message);
        }
    }

    async exportReport(format) {
        const filters = new FormData(document.querySelector(this.config.filters));
        filters.append('format', format);
        const params = new URLSearchParams(filters);

        try {
            const response = await fetch(`${this.config.baseUrl}/export?${params}`);
            
            if (format === 'pdf') {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `reporte_${new Date().toISOString()}.pdf`;
                a.click();
            } else if (format === 'excel') {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `reporte_${new Date().toISOString()}.xlsx`;
                a.click();
            } else if (format === 'csv') {
                const text = await response.text();
                const blob = new Blob([text], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `reporte_${new Date().toISOString()}.csv`;
                a.click();
            }
        } catch (error) {
            this.showError('Error exportando reporte: ' + error.message);
        }
    }

    renderReport(data) {
        const container = document.querySelector(this.config.container);
        if (container) {
            container.innerHTML = this.generateTableHTML(data);
        }
    }

    updateCharts(chartsData) {
        if (!chartsData) return;

        Object.keys(chartsData).forEach(chartId => {
            const chartContainer = document.getElementById(chartId);
            if (chartContainer) {
                this.renderChart(chartContainer, chartsData[chartId]);
            }
        });
    }

    renderChart(container, data) {
        new Chart(container, {
            type: data.type,
            data: data.data,
            options: data.options
        });
    }

    generateTableHTML(data) {
        if (!data.rows || !data.rows.length) {
            return '<p class="text-center">No hay datos para mostrar</p>';
        }

        let html = '<table class="min-w-full divide-y divide-gray-200">';
        
        // Headers
        html += '<thead class="bg-gray-50"><tr>';
        Object.keys(data.rows[0]).forEach(header => {
            html += `<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                ${this.formatHeader(header)}
            </th>`;
        });
        html += '</tr></thead>';

        // Body
        html += '<tbody class="bg-white divide-y divide-gray-200">';
        data.rows.forEach(row => {
            html += '<tr>';
            Object.values(row).forEach(cell => {
                html += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${this.formatCell(cell)}
                </td>`;
            });
            html += '</tr>';
        });
        html += '</tbody></table>';

        // Paginación si existe
        if (data.pagination) {
            html += this.generatePaginationHTML(data.pagination);
        }

        return html;
    }

    formatHeader(header) {
        return header.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    formatCell(cell) {
        if (cell === null || cell === undefined) return '-';
        if (typeof cell === 'boolean') return cell ? 'Sí' : 'No';
        if (typeof cell === 'number') return this.formatNumber(cell);
        return cell;
    }

    formatNumber(number) {
        if (Number.isInteger(number)) return number.toString();
        return number.toFixed(2);
    }

    generatePaginationHTML(pagination) {
        let html = '<div class="flex items-center justify-between px-4 py-3 bg-white border-t border-gray-200 sm:px-6">';
        
        html += `<div class="flex justify-between flex-1 sm:hidden">
            <button ${pagination.current_page === 1 ? 'disabled' : ''} 
                    class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                Anterior
            </button>
            <button ${pagination.current_page === pagination.total_pages ? 'disabled' : ''} 
                    class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                Siguiente
            </button>
        </div>`;

        return html;
    }

    showError(message) {
        const alert = document.createElement('div');
        alert.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative';
        alert.role = 'alert';
        alert.innerHTML = message;
        
        const container = document.querySelector(this.config.container);
        if (container) {
            container.prepend(alert);
            setTimeout(() => alert.remove(), 5000);
        }
    }
}

// Inicialización
document.addEventListener('DOMContentLoaded', () => {
    window.reportManager = new ReportManager();
});