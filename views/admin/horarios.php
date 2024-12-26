<?php 
$page_title = 'Gestión de Horarios';
require_once '../layout/header.php';
checkPermission('Administrador');
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../layout/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1>Horarios</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalHorario">
                    Nuevo Horario
                </button>
            </div>

            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-body">
                    <form id="filtrosHorario" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Sede</label>
                            <select class="form-select" id="filtroSede" required>
                                <option value="">Seleccione Sede</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Taller</label>
                            <select class="form-select" id="filtroTaller">
                                <option value="">Seleccione Taller</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Turno</label>
                            <select class="form-select" id="filtroTurno">
                                <option value="">Todos</option>
                                <option value="Mañana">Mañana</option>
                                <option value="Tarde">Tarde</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary d-block">Filtrar</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Calendario/Horario -->
            <div class="card">
                <div class="card-body">
                    <div id="calendarioHorarios"></div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal Horario -->
<div class="modal fade" id="modalHorario" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo Horario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formHorario">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Taller</label>
                        <select class="form-select" name="taller_id" required>
                            <option value="">Seleccione Taller</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Turno</label>
                        <select class="form-select" name="turno" required>
                            <option value="Mañana">Mañana</option>
                            <option value="Tarde">Tarde</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hora Inicio</label>
                        <input type="time" class="form-control" name="hora_inicio" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hora Fin</label>
                        <input type="time" class="form-control" name="hora_fin" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Días</label>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="dias[]" value="1">
                            <label class="form-check-label">Lunes</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="dias[]" value="2">
                            <label class="form-check-label">Martes</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="dias[]" value="3">
                            <label class="form-check-label">Miércoles</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="dias[]" value="4">
                            <label class="form-check-label">Jueves</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="dias[]" value="5">
                            <label class="form-check-label">Viernes</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="dias[]" value="6">
                            <label class="form-check-label">Sábado</label>
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

<script src="<?= BASE_URL ?>/public/js/fullcalendar.js"></script>
<script src="<?= BASE_URL ?>/public/js/admin/horarios.js"></script>
<?php include '../layout/footer.php'; ?>