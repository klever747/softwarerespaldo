<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Tienda</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Ecommerce</a></li>
                            <li class="breadcrumb-item active">Tiendas</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card card-default">                    
                    <div class="card-header">
                        <h3 class="card-title">Stores</h3>
                    </div>

                    <?= filtroBusqueda("ecommerce/store/stores", array("texto_busqueda" => $texto_busqueda, "estado_id" => $estado_id, "regpp" => $regpp)); ?>

                    <div class="card-body text-center"> 
                        <?= isset($itemsPaginacion) ? ($itemsPaginacion) : ''; ?>
                    </div>
                    <div class="card-body centrado">
                        <div class="row">
                            <?php
                            if ($stores) {
                                ?>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Nombre Tienda</th>
                                            <th scope="col">URL</th>
                                            <th scope="col">Ecommerce</th>
                                            <th scope="col">Estado</th>
                                            <th scope="col">Alias</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($stores as $k => $store) {
                                            ?>
                                            <tr>
                                                <th scope="row"><?= $k + 1 ?></th>
                                                <td class="text-left"><?= $store->store_name ?></td>
                                                <td class="text-left"><?= $store->url ?></td>
                                                <td class="text-justify"><?= $store->ecommerce ?></td>
                                                <td><?= ($store->estado === ESTADO_ACTIVO ? 'Activo' : 'Inactivo') ?></td>
                                                <td class="text-justify"><?= $store->alias ?></td>
                                                <td>
                                                    <button type = "submit" class="btn btn-accion btn-tool" data-id="<?= $store->id ?>" value="eliminar"><i class="far fa-trash-alt"></i></button>
                                                    <button type = "submit" class="btn btn-accion btn-tool" data-id="<?= $store->id ?>" value="editar"><i class="fas fa-pencil-alt"></i></button>
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

    var producto_actual = 0;
    function recargarPrincipal() {
        console.log("recargarPrincipal, Tiendas");
        $("#btn_buscar").trigger("click");
    }

    function mostrarEdicion(r) {
        console.log("mostrarEdicion en tiendas.php linea 132");
        if (r.error) {
            mostrarError("No existe informaci&oacute;n disponible en estos momentos");
        } else {
            $("#modalEdicion .modal-content").html(r.respuesta);
            $("#modalEdicion").modal("show");
        }
    }

    function mostrarEliminacion(r) {
        if (r.error) {
            mostrarError("Hubo un problema durante la eliminaci&oacute;n");
        } else {
            recargarPrincipal();
        }
    }

    function loadTienda() {
        llamadaAjax(false, '<?= base_url() ?>ecommerce/store/store_obtener', {"id": tienda_actual}, mostrarEdicion);
    }



    $(document).ready(function () {

        /*************** ACCIONES PANTALLA PRINCIPAL *********************/
        $(".btn-accion").on('click', function () {
            unsoloclick('.btn-accion');
            if ($(this).val() === "eliminar") {
                llamadaAjax(false, '<?= base_url() ?>ecommerce/store/store_eliminar', {"id": $(this).data('id')}, mostrarEliminacion);
            } else if ($(this).val() === "editar") {
                tienda_actual = $(this).data('id');
                loadTienda();
            } else if ($(this).val() === "agregar") {
                llamadaAjax(false, '<?= base_url() ?>ecommerce/store/store_nuevo', false, mostrarEdicion);
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