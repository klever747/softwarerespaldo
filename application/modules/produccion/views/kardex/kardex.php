<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Kardex de Cajas</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                            <li class="breadcrumb-item">Producci&oacute;n</li>
                            <li class="breadcrumb-item">Kardex</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card card-default" id="soloLectura">
                    <?=
                    filtroBusqueda($url_busqueda, array("rango_busqueda" => $rango_busqueda, "tipo_calendario" => $tipo_calendario, "uso_calendario" => 1,
                        "store_id" => $store_id, "sel_store" => $sel_store, "tipo_caja" => $tipo_caja, "sel_tipo_caja" => $sel_tipo_caja,
                        "tracking_number" => $tracking_number,
                        "exportar_pdf" => true, "exportar_xls" => true)
                    );
                    ?>
                    <div class="card-body text-center">
                        Total Cajas en kardex: <?= $cuantos ?>
                    </div>
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
        $("#tracking_number").focus();
        /*************** ACCIONES PANTALLA PRINCIPAL *********************/
        $("#exportar_excel").on('click', function () {
            unsoloclick('#exportar_excel');
            var form = $("#form_busqueda");
            console.log(form.serialize());
//            llamadaAjax('exportar_excel', '<?= base_url() ?>produccion/logistica/cajasPorFecha_xls', form.serialize(), mostrarResultadoNuevaVenta);
            window.open('<?= base_url() ?>produccion/logistica/cajasPorFecha_xls?' + form.serialize(), '_blank');
        });
    });

</script>