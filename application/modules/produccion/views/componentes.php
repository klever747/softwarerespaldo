<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4>Listado de Ordenes</h4>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                            <li class="breadcrumb-item">Ecommerce</li>
                            <li class="breadcrumb-item">Ordenes</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card card-default" id="soloLectura">
                    <?php
                    $filtroActual = array("rango_busqueda" => $rango_busqueda, "tipo_calendario" => $tipo_calendario,
                        "store_id" => $store_id, "sel_store" => $sel_store,
                        "agrupado_descripcion" => $agrupado_descripcion,
                        "mostrar_tinturados" => $mostrar_tinturados,
                        "mostrar_naturales" => $mostrar_naturales,
                        "mostrar_accesorios" => $mostrar_accesorios,
                        "uso_calendario" => 3,
                        "exportar_xls" => true);
                    $filtroActual["session_finca"] = $session_finca;
                     $arrayfinca = explode(",", $session_finca);
                    if (in_array(FINCA_ROSAHOLICS_ID,$arrayfinca)) {
                        $filtroActual["finca_id"] = $finca_id;
                        $filtroActual["sel_finca"] = $sel_finca;
                        //$filtroActual["sel_tipo_caja"] = $sel_tipo_caja;
                    }else{
                        if(count($arrayfinca) > 1){
                        $filtroActual["finca_id"] = $finca_id;
                        $filtroActual["sel_finca"] = $sel_finca;
                        }
                    }

                    echo filtroBusqueda($url_busqueda, $filtroActual);
                    ?>
                    <div class="card-body text-center small">
                        <?= $componentes_listado ?>
                    </div>
                </div>
            </div>
    </div>
</section>
</div>
<!-- /.content-wrapper -->
</div>

<script>
    var filtroActual = <?= json_encode($filtroActual) ?>;
    $("#exportar_excel").on('click', function () {
        unsoloclick('#exportar_excel');
        var form = $("#form_busqueda");
        console.log(form.serialize());
//            llamadaAjax('exportar_excel', '<?= base_url() ?>produccion/logistica/cajasPorFecha_xls', form.serialize(), mostrarResultadoNuevaVenta);
        window.open('<?= base_url() ?>produccion/componentes/resumenXLS?filtro=' + JSON.stringify(filtroActual), '_blank');
    });
</script>