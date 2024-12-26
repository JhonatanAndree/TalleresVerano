document.addEventListener('DOMContentLoaded', () => {
    cargarBackups();
 });
 
 async function cargarBackups() {
    try {
        const response = await fetch(`${BASE_URL}/controllers/BackupController.php?action=list`);
        const data = await response.json();
        if (data.success) {
            actualizarTablaBackups(data.backups);
        }
    } catch (error) {
        mostrarError('Error al cargar backups');
    }
 }
 
 async function generarBackup() {
    try {
        const response = await fetch(`${BASE_URL}/controllers/BackupController.php`, {
            method: 'POST',
            body: JSON.stringify({ action: 'generate' })
        });
        const data = await response.json();
        if (data.success) {
            mostrarExito('Backup generado exitosamente');
            cargarBackups();
        }
    } catch (error) {
        mostrarError('Error al generar backup');
    }
 }
 
 async function restaurarBackup(filename) {
    if (!confirm('¿Está seguro de restaurar este backup? Los datos actuales serán reemplazados.')) {
        return;
    }
 
    try {
        const formData = new FormData();
        formData.append('action', 'restore');
        formData.append('filename', filename);
 
        const response = await fetch(`${BASE_URL}/controllers/BackupController.php`, {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.success) {
            mostrarExito('Backup restaurado exitosamente');
            cargarBackups();
        }
    } catch (error) {
        mostrarError('Error en la restauración');
    }
 }
 
 async function guardarConfiguracion(e) {
    e.preventDefault();
    try {
        const formData = new FormData(e.target);
        formData.append('action', 'saveConfig');
 
        const response = await fetch(`${BASE_URL}/controllers/BackupController.php`, {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.success) {
            mostrarExito('Configuración guardada');
            $('#modalConfig').modal('hide');
        }
    } catch (error) {
        mostrarError('Error al guardar configuración');
    }
 }
 
 function generarClave() {
    const bytes = new Uint8Array(32);
    window.crypto.getRandomValues(bytes);
    const clave = Array.from(bytes).map(b => b.toString(16).padStart(2, '0')).join('');
    document.querySelector('[name="clave_cifrado"]').value = clave;
 }
 
 function actualizarTablaBackups(backups) {
    const tabla = document.getElementById('tablaBackups');
    tabla.innerHTML = backups.map(backup => `
        <tr>
            <td>${formatearFecha(backup.fecha)}</td>
            <td>${formatearTamano(backup.tamano)}</td>
            <td>
                <span class="badge ${backup.estado === 'success' ? 'bg-success' : 'bg-warning'}">
                    ${backup.estado}
                </span>
            </td>
            <td>
                <button onclick="restaurarBackup('${backup.filename}')" 
                        class="btn btn-sm btn-warning me-2">
                    Restaurar
                </button>
                <a href="${BASE_URL}/backups/${backup.filename}" 
                   class="btn btn-sm btn-secondary">
                    Descargar
                </a>
            </td>
        </tr>
    `).join('');
 }
 
 function formatearFecha(fecha) {
    return new Date(fecha).toLocaleString('es-PE', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
 }
 
 function formatearTamano(bytes) {
    const unidades = ['B', 'KB', 'MB', 'GB'];
    let i = 0;
    while (bytes >= 1024 && i < unidades.length - 1) {
        bytes /= 1024;
        i++;
    }
    return `${bytes.toFixed(2)} ${unidades[i]}`;
 }