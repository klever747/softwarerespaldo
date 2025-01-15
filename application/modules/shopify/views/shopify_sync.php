<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Shopify</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                            <li class="breadcrumb-item active">Shopify</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <section class="content">
            <div class="container-fluid">                

                <div class="card card-default" id="soloLectura">
                    <div class="card-header">
                        <h3 class="card-title">Sincronizaci&oacute;n</h3>

                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                    <div class="card-body tabulado1">                        
                        <div class="row">
                            <div class="col-sm-9">
                                <div class="input-group input-group-sm">
                                    <label>Tienda &nbsp;</label>
                                    <span class="input-group-append">
                                        <div class="form-group">
                                            <?php
                                            $js = array(
                                                "id" => "store_id",
                                                "class" => "form-control select2",
                                            );
                                            ?>
                                            <?= form_dropdown("store_id", $sel_store, $store_id, $js); ?>
                                        </div>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-1">Productos</div>
                            <div class="col-sm-1">
                                <button type = "button" id="syncProductos" class = "btn btn-primary btn-block"><i class="fas fa-sync"></i></button>
                            </div>
                            <div class="col-sm-1 offset-2">Clientes</div>
                            <div class="col-sm-1">
                                <button type = "button" id="syncClientes" class = "btn btn-primary btn-block" disabled=""><i class="fas fa-sync"></i></button>
                            </div>
                            <div class="col-sm-1 offset-2">Ordenes</div>
                            <div class="col-sm-1">
                                <button type = "button" id="syncOrdenes" class = "btn btn-primary btn-block"><i class="fas fa-sync"></i></button>
                            </div>
                        </div>
                        <div>&nbsp;</div>
                        <div class="row">
                            <div class="col-sm-2" id="resultadoProductos">
                            </div>                        
                            <div class="col-sm-2 offset-2" id="resultadoClientes">
                            </div>
                            <div class="col-sm-2 offset-2" id="resultadoOrdenes">
                            </div>
                        </div>
                        <?php /* foreach ($productos as $prod) { ?>                            
                          <div class="row">
                          <?php
                          print_r($prod->card);
                          ?>
                          </div>
                          <?php } */ ?>
                    </div>
                </div>
            </div>
        </section>

    </div>
    <!-- /.content-wrapper -->
</div>

<script>

    function analizarRespuestaSincronizacion(r) {
        var x = '<ul class="nav flex-column">' +
                '<li class="nav-item">' +
                'Total <span class="float-right badge bg-primary">' + r.total + '</span>' +
                '</li>' +
                '<li class="nav-item">' +
                'Nuevos<span class="float-right badge bg-info">' + r.creados + '</span>' +
                '</li>' +
                '<li class="nav-item">' +
                'Actualizados<span class="float-right badge bg-info">' + r.actualizados + '</span>' +
                '</li>' +
                '<li class="nav-item">' +
                'Errores<span class="float-right badge bg-info">' + r.errores + '</span>' +
                '</li>' +
                '</ul>';
        return x;
    }
    function callbackSyncProductos(r) {
        $("#resultadoProductos").html(analizarRespuestaSincronizacion(r));
    }
    function callbackSyncClientes(r) {
        $("#resultadoClientes").html(analizarRespuestaSincronizacion(r));
    }
    function callbackSyncOrdenes(r) {
        $("#resultadoOrdenes").html(analizarRespuestaSincronizacion(r));
    }
    function parametrosSincronizacion() {
        return data = {
            "btn_value": $('#store_id').val()
        };
    }
    $(document).ready(function () {
        btnLlamadaAjax("syncProductos", '<?= base_url() ?>shopify/syncProductos', parametrosSincronizacion, callbackSyncProductos);
        btnLlamadaAjax("syncClientes", '<?= base_url() ?>shopify/syncClientes', parametrosSincronizacion, callbackSyncClientes);
        btnLlamadaAjax("syncOrdenes", '<?= base_url() ?>shopify/syncOrdenes', parametrosSincronizacion, callbackSyncOrdenes);
    });
</script>
