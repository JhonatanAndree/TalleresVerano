<!DOCTYPE html>
<html>
<head>
    <title>Consulta para Padres</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="/public/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-body">
                        <h3 class="text-center mb-4">Consulta de Talleres</h3>
                        <form id="consultaForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <input type="text" class="form-control" name="dni_estudiante" 
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

                        <div id="resultados" class="mt-4" style="display: none;">
                            <div id="datosEstudiante"></div>
                            <hr>
                            <div class="row mt-3">
                                <div class="col-md-6 mb-2">
                                    <button onclick="descargarFicha()" class="btn btn-secondary w-100">
                                        Descargar Ficha de Inscripción
                                    </button>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <button onclick="descargarCardID()" class="btn btn-info w-100">
                                        Descargar CARD ID
                                    </button>
                                </div>
                            </div>
                            <button onclick="mostrarFormularioCorreccion()" 
                                    class="btn btn-outline-primary w-100 mt-2">
                                Solicitar correcciones
                            </button>
                        </div>

                        <div id="formCorreccion" class="mt-4" style="display: none;">
                            <form id="correccionForm">
                                <input type="hidden" id="dni_estudiante_correccion">
                                <div class="form-group">
                                    <textarea class="form-control" name="descripcion" rows="4" 
                                              required placeholder="Describe las correcciones necesarias"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 mt-3">
                                    Enviar solicitud
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="/public/js/consulta_padres.js"></script>
</body>
</html>