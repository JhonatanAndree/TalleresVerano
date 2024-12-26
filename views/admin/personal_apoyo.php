<?php 
$page_title = 'Personal de Apoyo';
require_once '../layout/header.php';
checkPermission('Administrador');
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../layout/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between pt-3 pb-2 mb-3 border-bottom">
                <h1>Personal de Apoyo</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPersonal">
                    Nuevo Personal
                </button>
            </div>

            <!-- Tabla Personal -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tablaPersonal">
                            <thead>
                                <tr>
                                    <th>Nombres</th>
                                    <th>Apellidos</th>
                                    <th>DNI</th>
                                    <th>Sede</th>
                                    <th>Turno</th>
                                    <th>Contacto</th>
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

<!-- Modal Personal -->
<div class="modal fade" id="modalPersonal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Personal de Apoyo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formPersonal">
                <div class="modal-body">
                    <input type="hidden" name="id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Nombres</label>
                            <input type="text" class="form-control" name="nombres" required maxlength="100">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Apellidos</label>
                            <input type="text" class="form-control" name="apellidos" required maxlength="100">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>DNI</label>
                            <input type="text" class="form-control" name="dni" required pattern="[0-9]{8}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Celular</label>
                            <input type="tel" class="form-control" name="celular" required pattern="[0-9]{9}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Dirección</label>
                        <input type="text" class="form-control" name="direccion" required>
                    </div>
                    <div class="mb-3">
                        <label>Contacto Familiar</label>
                        <input type="tel" class="form-control" name="contacto_familiar" required pattern="[0-9]{9}">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Sede</label>
                            <select class="form-select" name="id_sede" required></select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Turno</label>
                            <select class="form-select" name="turno" required>
                                <option value="Mañana">Mañana</option>
                                <option value="Tarde">Tarde</option>
                            </select>
                        </div>
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

<script src="<?= BASE_URL ?>/public/js/admin/personal_apoyo.js"></script>
<?php include '../layout/footer.php'; ?>