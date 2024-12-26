<?php
$page_title = 'Consulta para Padres';
$additional_css = ['consulta.css'];
$additional_js = ['consulta_padres.js', 'download.js'];
require_once '../includes/constants.php';
require_once 'layout/header.php';
?>

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body">
                    <h3 class="text-center mb-4">Consulta de Talleres</h3>
                    <form id="consultaForm" action="<?= BASE_URL ?>/controllers/consulta_controller.php" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <input type="text" class="form-control" id="dni_estudiante" name="dni_estudiante" 
                                       pattern="[0-9]{8}" maxlength="8" required 
                                       placeholder="DNI del Estudiante">
                            </div>
                            <div class="col-md-6 mb-3">
                                <input type="text" class="form-control" name="dni_padre" 
                                       pattern="[0-9]{8}" maxlength="8" required 
                                       placeholder="DNI del Padre/Madre">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Consultar</button>
                    </form>

                    <div id="resultados" class="mt-4 d-none">
                        <div class="datos-estudiante">
                            <h4>Datos del Estudiante</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Nombre:</strong> <span id="nombre_estudiante"></span></p>
                                    <p><strong>Sede:</strong> <span id="sede"></span></p>
                                    <p><strong>Taller:</strong> <span id="taller"></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Docente:</strong> <span id="docente"></span></p>
                                    <p><strong>Horario:</strong> <span id="horario"></span></p>
                                    <p><strong>Turno:</strong> <span id="turno"></span></p>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <button onclick="descargarFicha(document.getElementById('dni_estudiante').value)" 
                                        class="btn btn-secondary w-100">
                                    Descargar Ficha de Inscripción
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button onclick="descargarCardID(document.getElementById('dni_estudiante').value)" 
                                        class="btn btn-info w-100">
                                    Descargar CARD ID
                                </button>
                            </div>
                        </div>
                        <button onclick="mostrarFormularioCorreccion()" 
                                class="btn btn-outline-primary w-100 mt-3">
                            Solicitar correcciones
                        </button>
                    </div>

                    <div id="formCorreccion" class="mt-4 d-none">
                        <h4>Solicitud de Correcciones</h4>
                        <form id="correccionForm">
                            <input type="hidden" id="dni_estudiante_correccion" name="dni_estudiante">
                            <div class="form-group">
                                <textarea class="form-control" name="descripcion" rows="4" 
                                          required placeholder="Describe las correcciones necesarias"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mt-3">
                                Enviar solicitud
                            </button>
                            <button type="button" onclick="ocultarFormularioCorreccion()" 
                                    class="btn btn-secondary w-100 mt-2">
                                Cancelar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>