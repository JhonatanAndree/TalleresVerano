<?php 
$page_title = 'Gestión de Backups';
require_once '../layout/header.php';
checkPermission('SuperAdmin');
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../layout/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between pt-3 pb-2 mb-3 border-bottom">
                <h1>Backups del Sistema</h1>
                <div>
                    <button onclick="generarBackup()" class="btn btn-primary me-2">
                        Backup Manual
                    </button>
                    <button onclick="mostrarConfiguracion()" class="btn btn-secondary">
                        Configuración
                    </button>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h5>Backups Disponibles</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tamaño</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaBackups"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal Configuración -->
<div class="modal fade" id="modalConfig">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Configuración de Backups</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formConfigBackup">
                    <div class="mb-3">
                        <label>Hora programada (UTC-5)</label>
                        <input type="time" class="form-control" name="hora_backup" value="19:00" required>
                    </div>
                    <div class="mb-3">
                        <label>Retención (días)</label>
                        <input type="number" class="form-control" name="dias_retencion" min="1" max="90" value="7">
                    </div>
                    <div class="mb-3">
                        <label>Clave de cifrado (AES-256)</label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="clave_cifrado">
                            <button type="button" class="btn btn-outline-secondary" onclick="generarClave()">
                                Generar
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/public/js/admin/backups.js"></script>
<?php include '../layout/footer.php'; ?>