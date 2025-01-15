<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Master Shipping</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                            <li class="breadcrumb-item">Producci&oacute;n</li>
                            <li class="breadcrumb-item">Master Shipping</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card card-default" id="soloLectura">
                    <?php
                    $filtroActual = array("fecha_busqueda" => $fecha_busqueda,
                        "tipo_calendario_unico" => $tipo_calendario_unico,
                        "uso_calendario_unico" => 6,
                        "regpp" => $regpp,
                        "finca_id" => $finca_id,
                        "sel_finca" => $sel_finca);
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
                    <div class="card-body centrado">
                        <div class="row">
                            <?php
                            if ($listadoMaster) {
                                ?>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Nombre Finca</th>
                                            <th scope="col">Nombre Master Shipping</th>
                                            <th scope="col">Numero de Guia</th>
                                            <th scope="col">Fecha Carguera</th>
                                            <th scope="col">Estado</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($listadoMaster as $k => $listMaster) {
                                            ?>
                                            <tr>
                                                <th scope="row"><?= $k + 1 ?></th>
                                                <td class="text-left"><?= $listMaster->nombre ?></td>
                                                <td class="text-left"><?= $listMaster->nombre_master ?></td>
                                                <td class="text-left"><?= $listMaster->numero_guia ?></td>
                                                <td class="text-left"><?= convertirDateString(date_create($listMaster->fecha_carguera), "Y-m-d") ?></td>
                                                <!--  <td class="text-left"><?= $listMaster->estado ?></td> -->
                                                <td class="text-left">
                                                    <?= mostrarEstilos($listMaster->estado); ?>
                                                </td>
                                                <td>
                                                    <button type = "button" class="btn btn-accion btn-tool" data-id="<?= $listMaster->nombre_master ?>" value="visualizar"><i class="far fa-eye"></i></button>
                                                    <?php
                                                    if ($listMaster->estado == 'A') {
                                                        ?>
                                                        <button type = "button" class="btn btn-accion btn-tool" data-id="<?= $listMaster->id ?>" value="eliminar"><i class="far fa-trash-alt"></i></button>
                                                        <?php
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                    </tbody>
                                </table>
                                <?php
                            }
                            ?>
                        </div>
                        <div class="row">
                        </div>
                    </div>
                    <div class="card-body text-center"> 
                        <?= isset($itemsPaginacion) ? ($itemsPaginacion) : ''; ?>
                    </div>
                </div>
        </section>
    </div>
    <!-- /.content-wrapper -->
</div>
<button type="button" class="float btn-accion" data-id="" value="agregar">
    <i class="fa fa-plus my-float"></i>
</button>
<!-- Modal -->
<div class="modal" id="modalEdicion" tabindex="-1" role="dialog" aria-labelledby="wsanchez" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">

        </div>
    </div>
</div>
<script>




    function recargarPrincipal() {
        console.log("Recargar Principal en caja_resumen");
        //loadOrden();
        $("#btn_buscar").trigger("click");
    }

    function mostrarEdicion(r) {
        console.log("mostrarEdicion en ingrediente.php linea 132");
        if (r.error) {
            mostrarError("No existe informaci&oacute;n disponible en estos momentos");
        } else {
            $("#modalEdicion .modal-content").html(r.respuesta);
            $("#modalEdicion").modal("show");
            var nowDate = new Date();
            var today = new Date(nowDate.getFullYear(), nowDate.getMonth(), nowDate.getDate(), 0, 0, 0, 0);
            $('.select_fecha').daterangepicker({
                singleDatePicker: true,
                locale: {
                    format: '<?= FORMATO_FECHA_DATEPICKER_JS ?>'
                },
                minDate: today,
                maxDate: '2050-12-13',
                autoApply: true
            });
        }
    }

    function mostrarEliminacion(r) {
        if (r.error) {
            mostrarError("Hubo un problema durante la eliminaci&oacute;n");
        } else {
            recargarPrincipal();
        }
    }

    function analizarRespuesta(r) {
        if (r.error) {
            mostrarError(r.mensaje);
            return false;
        } else {
            mostrarExito(r.mensaje);
            return true;
        }
        return false;
    }
    function respuestaGenTarjMen(r) {
        if (analizarRespuesta(r) && (r.ruta_pdf != '')) {
            console.log("ruta a abrir es " + r.ruta_pdf);
            window.open(r.ruta_pdf);

        }
    }
    $(document).ready(function () {

        /*************** ACCIONES PANTALLA PRINCIPAL *********************/
        $(".btn-accion").on('click', function () {
            unsoloclick('.btn-accion');
            if ($(this).val() === "eliminar") {
                llamadaAjax(false, '<?= base_url() ?>produccion/MasterShipping/master_shipping_eliminar', {"id": $(this).data('id')}, mostrarEliminacion);
            } else if ($(this).val() === "visualizar") {
                llamadaAjax(false, '<?= base_url() ?>produccion/MasterShipping/json_imprimir_master_shipping', {"id": $(this).data('id')}, respuestaGenTarjMen);

            } else if ($(this).val() === "agregar") {
                llamadaAjax(false, '<?= base_url() ?>produccion/MasterShipping/master_shipping_nuevo', false, mostrarEdicion);
            }
        });

        $("#modalEdicion").on('shown.bs.modal', function () {
        });
        $("#modalEdicion").on('hide.bs.modal', function () {
            //recargarPrincipal();
        });
        /***************** ACCIONES MODAL DETALLE *******************/

        $("#texto_busqueda").on('keypress', function (e) {
            if (e.which === 13) {
                $("#btn_buscar").trigger("click");
            }
        });

    });

</script>