<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Configuración</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Configuración</a></li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card card-default">
                    <div class="card-header">
                        <h3 class="card-title">Lista de menu disponibles</h3>
                    </div>

                    <?= filtroBusqueda("Configuracion/index", array("texto_busqueda" => $texto_busqueda, "estado_id" => $estado_id)); ?>
                    <div class="card-body text-center">
                        <?= isset($itemsPaginacion) ? ($itemsPaginacion) : ''; ?>
                    </div>
                    <div class="card-body centrado">
                        <div class="row">
                            <?php
                            if ($menues) {
                                ?>
                                <?=
                                crearBoxes($menues, $menueshijos, 0);
                            }
                            ?>
                        </div>
                        <div class="row">

                        </div>
                        <!-- FIN ROW -->
                    </div>

                </div>
            </div>
        </section>
    </div>
</div>

<button type="button" class="float btn-accion" data-id="" value="agregar">
    <i class="fa fa-plus my-float"></i>
</button>

<div class="modal" id="modalEdicion" tabindex="-1" role="dialog" aria-labelledby="wsanchez" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">

        </div>
    </div>
</div>

<script>

    var menu_actual = 0;
    function recargarPrincipal() {
        console.log("recargarPrincipal, productos");
        $("#btn_buscar").trigger("click");
    }

    function mostrarEdicion(r) {
        console.log("mostrarEdicion en configuracion.php linea 132");
        if (r.error) {
            mostrarError("No existe informaci&oacute;n disponible en estos momentos");
        } else {
            $("#modalEdicion .modal-content").html(r.respuesta);
            $("#modalEdicion").modal("show");
        }
    }

    function mostrarEliminacion(r) {

        if (r.error) {
            mostrarError(r.respuesta);
        } else {
            mostrarExito(r.respuesta);
            recargarPrincipal();
        }
    }

    function loadMenus() {
        llamadaAjax(false, '<?= base_url() ?>configuracion/obtenerMenu', {"id": menu_actual}, mostrarEdicion);
        console.log('Pruebas');
    }
    $(document).ready(function () {

        /*************** ACCIONES PANTALLA PRINCIPAL *********************/
        $(".btn-accion").on('click', function () {
            unsoloclick('.btn-accion');
            if ($(this).val() === "eliminar") {
                console.log('Boton de eliminar');
                swal_modal('¿Est&aacute; seguro de eliminar el registro?',
                        'Si',
                        'No',
                        '<?= base_url() ?>configuracion/menu_eliminar',
                        {"id": $(this).data('id')},
                        mostrarEliminacion);
                //llamadaAjax(false, '<?= base_url() ?>configuracion/menu_eliminar', {"id": $(this).data('id')}, mostrarEliminacion);
            } else if ($(this).val() === "editar") {
                console.log('Boton de editar');
                menu_actual = $(this).data('id');
                loadMenus();
            } else if ($(this).val() === "agregar") {
                console.log('Boton de agregar');
                var ruta = '<?= base_url() ?>configuracion/menu_nuevo';
                console.log(ruta);
                llamadaAjax(false, ruta, false, mostrarEdicion);

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