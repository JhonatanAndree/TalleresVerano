<?php 
$page_title = 'Gestión de Sedes';
require_once '../layout/header.php';
checkPermission('Administrador');
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../layout/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1>Sedes</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalSede">
                    Nueva Sede
                </button>
            </div>

            <!-- Tabla de Sedes -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="tablaSedes">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Dirección</th>
                                    <th>Talleres Activos</th>
                                    <th>Capacidad Total</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal Sede -->
<div class="modal fade" id="modalSede" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSedeTitle">Nueva Sede</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formSede">
                <div class="modal-body">
                    <input type="hidden" name="sede_id" id="sede_id">
                    <div class="mb-3">
                        <label class="form-label">Nombre de la Sede</label>
                        <input type="text" class="form-control" name="nombre" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dirección</label>
                        <input type="text" class="form-control" name="direccion" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Capacidad Máxima</label>
                        <input type="number" class="form-control" name="capacidad_maxima" required min="1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Horario de Atención</label>
                        <div class="row">
                            <div class="col">
                                <input type="time" class="form-control" name="hora_apertura" required>
                            </div>
                            <div class="col">
                                <input type="time" class="form-control" name="hora_cierre" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contacto de Emergencia</label>
                        <input type="tel" class="form-control" name="telefono_emergencia" 
                               pattern="[0-9]{9}" maxlength="9" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/public/js/admin/sedes.js"></script>
<?php include '../layout/footer.php'; ?>