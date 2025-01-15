<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Propiedades</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Ecommerce</a></li>
                            <li class="breadcrumb-item active">Propiedades</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card card-default">
                    <div class="card-header">
                        <h3 class="card-title">Propiedades</h3>
                    </div>

                    <?= filtroBusqueda("ecommerce/producto/propiedades", array("texto_busqueda" => $texto_busqueda, "estado_id" => $estado_id, "regpp" => $regpp)); ?>

                    <div class="card-body text-center">
                        <?= isset($itemsPaginacion) ? ($itemsPaginacion) : ''; ?>
                    </div>
                    <div class="card-body centrado">
                        <div class="row">
                            <?php
                            if ($propiedades) {
                                ?>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Nombre</th>
                                            <th scope="col">Descripci&oacute;n</th>
                                            <th scope="col">Estado</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($propiedades as $k => $propiedad) {
                                            ?>
                                            <tr>
                                                <th scope="row"><?= $k + 1 ?></th>
                                                <td class="text-left"><?= $propiedad->nombre ?></td>
                                                <td class="text-left"><?= $propiedad->descripcion ?></td>
                                              <!--   <td><?= ($propiedad->estado === ESTADO_ACTIVO ? 'Activo' : 'Inactivo') ?></td> -->
                                                <td class="text-left">
                                                    <?= mostrarEstilos($propiedad->estado); ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    if ($propiedad->estado == 'A') {
                                                        ?>
                                                        <button type = "submit" class="btn btn-accion btn-tool" data-id="<?= $propiedad->id ?>" value="eliminar"><i class="far fa-trash-alt"></i></button>
                                                        <?php
                                                    }
                                                    ?>
                                                    <button type = "submit" class="btn btn-accion btn-tool" data-id="<?= $propiedad->id ?>" value="editar"><i class="fas fa-pencil-alt"></i></button>
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

    function recargarPrincipal() {
        console.log("recargarPrincipal, propiedades");
        $("#btn_buscar").trigger("click");
    }

    function mostrarEdicion(r) {
        console.log("mostrarEdicion en propiedades.php linea 132");
        console.log(r);
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

    function loadPropiedad(id) {
        llamadaAjax(false, '<?= base_url() ?>ecommerce/producto/propiedad_obtener', {"id": id}, mostrarEdicion);
    }



    $(document).ready(function () {

        /*************** ACCIONES PANTALLA PRINCIPAL *********************/
        $(".btn-accion").on('click', function () {
            unsoloclick('.btn-accion');
            if ($(this).val() === "eliminar") {
                swal_modal('¿Est&aacute; seguro de eliminar el registro?',
                        'Si',
                        'No',
                        '<?= base_url() ?>ecommerce/producto/propiedad_eliminar',
                        {"id": $(this).data('id')},
                        mostrarEliminacion);
                // llamadaAjax(false, '<?= base_url() ?>ecommerce/producto/propiedad_eliminar', {"id": $(this).data('id')}, mostrarEliminacion);
            } else if ($(this).val() === "editar") {
                loadPropiedad($(this).data('id'));
            } else if ($(this).val() === "agregar") {
                llamadaAjax(false, '<?= base_url() ?>ecommerce/producto/propiedad_nuevo', false, mostrarEdicion);
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