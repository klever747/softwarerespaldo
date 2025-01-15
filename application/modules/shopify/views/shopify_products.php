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
                            <li class="breadcrumb-item">Shopify</li>
                            <li class="breadcrumb-item">Productos</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <section class="content">
            <div class="container-fluid">                

                <div class="card card-default" id="soloLectura">
                    <div class="card-header">
                        <h3 class="card-title">Listado de Productos</h3>

                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                    <?= filtroBusqueda("shopify/productos", array("texto_busqueda" => $texto_busqueda, "store_id" => $store_id, "sel_store" => $sel_store, "regpp" => $regpp)); ?>

                    <div class="card-body text-center"> 
                        <?= isset($itemsPaginacion) ? ($itemsPaginacion) : ''; ?>
                    </div>
                    <div class="card-body text-center">
                        <div class="row">
                            <?php
                            $item_row_max = 3;
                            $item_row_count = 0;
                            foreach ($productos as $producto) {
                                $item_row_count++;

//                            if ($item_row_count == 1) {
//                                echo '<div class="row ' . $item_row_count . '">';
//                            }

                                echo '<div class="col-sm-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 ' . $item_row_count . '">';
                                print_r($producto->card);
                                echo '</div>';

//                            if ($item_row_count == $item_row_max) {
//                                echo '</div>';
//                                $item_row_count = 0;
//                            }
                            }
                            ?>
                        </div>
                    </div>
                    <div class="card-body justify-content-md-center align-items-start"> 
                        <?= isset($itemsPaginacion) ? ($itemsPaginacion) : ''; ?>
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