<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Listado de Cajas</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                            <li class="breadcrumb-item">Ecommerce</li>
                            <li class="breadcrumb-item">Cajas</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card card-default" id="soloLectura">

                    <?php
                    $filtroActual = array("rango_busqueda" => $rango_busqueda, "tipo_calendario" => $tipo_calendario, "uso_calendario" => 5,
                        "rango_busqueda_full" => $rango_busqueda_full, "tipo_calendario_full" => $tipo_calendario_full, "uso_calendario_full" => 0,
                        "store_id" => $store_id, "sel_store" => $sel_store,
                        "con_tracking_number" => $con_tracking_number, "con_kardex" => $con_kardex,
                        "carga_trackingnumber" => true, "exportar_xls" => true);
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

                    <div class="card-body text-center"> 
                        <?= isset($itemsPaginacion) ? ($itemsPaginacion) : ''; ?>
                    </div>
                    <div class="card-body text-center">                        
                        <div class="row">                                
                            <?= (isset($tabla_datos) ? $tabla_datos : '') ?>
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
    function mostrarResultadoNuevaVenta(data) {
        console.log(data);
        var mywindow = window.open('', '_blank');
        mywindow.document.write(data);
//        var $a = $("<a>");
//        $a.attr("href", data.file);
//        $("body").append($a);
//        $a.attr("download", "file.xls");
//        $a[0].click();
//        $a.remove();
    }
    $(document).ready(function () {

        /*************** ACCIONES PANTALLA PRINCIPAL *********************/
        $("#exportar_excel").on('click', function () {
            unsoloclick('#exportar_excel');
            var form = $("#form_busqueda");
            console.log(form.serialize());
//            llamadaAjax('exportar_excel', '<?= base_url() ?>produccion/logistica/cajasPorFecha_xls', form.serialize(), mostrarResultadoNuevaVenta);
            window.open('<?= base_url() ?>produccion/logistica/cajasPorFecha_xls?' + form.serialize(), '_blank');
        });

        $("#exportar_tracking_carga").on('click', function () {
            unsoloclick('#exportar_tracking_carga');
            var form = $("#form_busqueda");
            console.log(form.serialize());
            window.open('<?= base_url() ?>produccion/logistica/trackingCargaPorFecha_xls?' + form.serialize(), '_blank');
        });
        $("#exportar_tracking_carga_completo").on('click', function () {
            unsoloclick('#exportar_tracking_carga_completo');
            var form = $("#form_busqueda");
            console.log(form.serialize());
            window.open('<?= base_url() ?>produccion/logistica/trackingCargaPorFechaCompleto_xls?' + form.serialize(), '_blank');
        });
    });

</script>