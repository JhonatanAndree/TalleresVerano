<?php 
$page_title = 'Gestión Personal de Apoyo';
require_once '../layout/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include '../layout/sidebar.php'; ?>
        
        <!-- Contenido principal -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1>Personal de Apoyo</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRegistroPersonal">
                    Nuevo Personal
                </button>
            </div>

            <!-- Tabla Personal -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Nombres</th>
                            <th>DNI</th>
                            <th>Sede</th>
                            <th>Turno</th>
                            <th>Contacto</th>
                            <th>Pago</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaPersonal">
                        <!-- Datos dinámicos -->
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<!-- Modal Registro -->
<div class="modal fade" id="modalRegistroPersonal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Personal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formPersonal">
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="text" class="form-control" name="nombres" required placeholder="Nombres">
                    </div>
                    <div class="mb-3">
                        <input type="text" class="form-control" name="apellidos" required placeholder="Apellidos">
                    </div>
                    <div class="mb-3">
                        <input type="text" class="form-control" name="dni" pattern="[0-9]{8}" required placeholder="DNI">
                    </div>
                    <div class="mb-3">
                        <input type="tel" class="form-control" name="celular" required placeholder="Celular">
                    </div>
                    <div class="mb-3">
                        <input type="text" class="form-control" name="direccion" required placeholder="Dirección">
                    </div>
                    <div class="mb-3">
                        <input type="tel" class="form-control" name="contacto_familiar" required placeholder="Contacto Familiar">
                    </div>
                    <div class="mb-3">
                        <select class="form-select" name="id_sede" required>
                            <option value="">Seleccione Sede</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <select class="form-select" name="turno" required>
                            <option value="Mañana">Mañana</option>
                            <option value="Tarde">Tarde</option>
                        </select>
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

<?php include '../layout/footer.php'; ?>