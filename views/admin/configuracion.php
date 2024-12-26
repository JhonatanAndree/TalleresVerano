<?php 
$page_title = 'Configuración del Sistema';
require_once '../layout/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../layout/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1>Configuración del Sistema</h1>
            </div>

            <div class="row">
                <!-- Configuración General -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Configuración General</h5>
                        </div>
                        <div class="card-body">
                            <form id="formConfigGeneral">
                                <div class="mb-3">
                                    <label>Nombre del Sistema</label>
                                    <input type="text" class="form-control" name="nombre_sistema" required>
                                </div>
                                <div class="mb-3">
                                    <label>Celular Soporte</label>
                                    <input type="tel" class="form-control" name="celular_soporte" required>
                                </div>
                                <div class="mb-3">
                                    <label>Dirección</label>
                                    <input type="text" class="form-control" name="direccion" required>
                                </div>
                                <div class="mb-3">
                                    <label>Email</label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label>Logotipo</label>
                                    <input type="file" class="form-control" name="logotipo" accept="image/jpeg,image/png">
                                </div>
                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Año Fiscal -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Año Fiscal</h5>
                        </div>
                        <div class="card-body">
                            <form id="formAnoFiscal">
                                <div class="mb-3">
                                    <label>Año Fiscal Actual</label>
                                    <input type="number" class="form-control" name="ano_fiscal" min="2024" max="2100" required>
                                </div>
                                <button type="submit" class="btn btn-warning">Cambiar Año Fiscal</button>
                            </form>
                            <div class="mt-4">
                                <h6>Historial de Años Fiscales</h6>
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Año</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tablaAnosFiscales"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Configuración de Pagos -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Configuración de Pagos</h5>
                        </div>
                        <div class="card-body">
                            <form id="formConfigPagos">
                                <div class="mb-3">
                                    <label>Moneda</label>
                                    <select class="form-select" name="moneda" required>
                                        <option value="PEN">Soles (PEN)</option>
                                        <option value="USD">Dólares (USD)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>Posición Símbolo</label>
                                    <select class="form-select" name="posicion_moneda" required>
                                        <option value="izquierda">Izquierda (S/ 100.00)</option>
                                        <option value="derecha">Derecha (100.00 S/)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>Token Yape</label>
                                    <input type="password" class="form-control" name="token_yape">
                                </div>
                                <button type="submit" class="btn btn-primary">Guardar Configuración</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="<?= BASE_URL ?>/public/js/admin/configuracion.js"></script>
<?php include '../layout/footer.php'; ?>