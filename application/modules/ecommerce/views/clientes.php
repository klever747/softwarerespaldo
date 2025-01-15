<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Ecommerce</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                            <li class="breadcrumb-item">Ecommerce</li>
                            <li class="breadcrumb-item">Clientes</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card card-default">
                    <div class="card-header">
                        <h3 class="card-title">Listado de Clientes</h3>
                    </div>

                    <?= filtroBusqueda("ecommerce/cliente/clientes", array("texto_busqueda" => $texto_busqueda, "store_id" => $store_id, "estado_id" => $estado_id, "sel_store" => $sel_store, "regpp" => $regpp)); ?>

                    <div class="card-body text-center">
                        <?= isset($itemsPaginacion) ? ($itemsPaginacion) : ''; ?>
                    </div>
                    <div class="card-body text-center">
                        <div class="row">
                            <?php
                            if ($clientes) {
                                ?>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Nombres</th>
                                            <th scope="col">Apellidos</th>
                                            <th scope="col">Nombre Comercial</th>
                                            <th scope="col">Email</th>
                                            <th scope="col">Telefono</th>
                                            <th scope="col">Estado</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($clientes as $k => $cliente) {
                                            ?>
                                            <tr>
                                                <th scope="row"><?= $k + 1 ?></th>
                                                <td class="text-left"><?= $cliente->nombres ?></td>
                                                <td class="text-left"><?= $cliente->apellidos ?></td>
                                                <td class="text-left"><?= $cliente->nombre_comercial ?></td>
                                                <td class="text-left"><?= $cliente->email ?></td>
                                                <td class="text-left"><?= $cliente->phone ?></td>
                                               <!--  <td class="text-left"><?= $cliente->estado ?></td> -->
                                                <td class="text-left">
                                                    <?= mostrarEstilos($cliente->estado); ?>
                                                </td>
                                                <td>
                                                    <button type = "button" class="btn btn-accion btn-tool" data-id="<?= $cliente->id ?>" value="editar"><i class="fas fa-pencil-alt"></i></button>
                                                    <?php
                                                    if ($cliente->estado == 'A') {
                                                        ?>
                                                        <button type = "button" class="btn btn-accion btn-tool" data-id="<?= $cliente->id ?>" value="eliminar"><i class="far fa-trash-alt"></i></button>
                                                        <button type = "button" class="btn btn-accion btn-tool" data-id="<?= $cliente->id ?>" value="agregar_orden"><i class="fas fa-cart-plus"></i></button>
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
    /*************** Acciones en Pantalla Principal *****************/
    /**
     *
     * @param {type} id
     * @returns {undefined}
     */
    function eliminarDetalle(id) {
        //
        swal_modal('¿Est&aacute; seguro de eliminar el registro?',
                'Si',
                'No',
                '<?= base_url() ?>ecommerce/cliente/cliente_eliminar',
                {"id": id},
                mostrarEliminacion);
        //
        // llamadaAjax(false, '<?= base_url() ?>ecommerce/cliente/cliente_eliminar', {"id": id}, mostrarEliminacion);
    }
    function obtenerDetalle(id) {
        console.log("obtenerDetalle en clientes.php linea 114");
        llamadaAjax(false, '<?= base_url() ?>ecommerce/cliente/cliente_obtener', {"id": id, "mostrar_direccion_envio": 1}, mostrarEdicion);
    }
    function agregarDetalle() {
        llamadaAjax(false, '<?= base_url() ?>ecommerce/cliente/cliente_nuevo', false, mostrarEdicion);
    }

    function recargarPrincipal() {
        console.log("recargarPrincipal, 121 en clientes");
        $("#btn_buscar").trigger("click");
    }
    /************* Resultado Pantalla Principal **********************/
    /**
     *
     * @param {type} r
     * @returns {undefined}
     */
    function mostrarEdicion(r) {
        console.log("mostrarEdicion en clientes.php linea 132");
        if (r.error) {
            mostrarError("No existe informaci&oacute;n disponible en estos momentos");
        } else {
            $("#modalEdicion .modal-content").html(r.respuesta);
            $("#modalEdicion").modal("show");
        }
    }

    function mostrarEliminacion(r) {
        console.log(r);
        if (r.error) {
            // mostrarError("Hubo un problema durante la eliminaci&oacute;n");
            mostrarError(r.respuesta);
        } else {
            mostrarExito(r.respuesta);
            recargarPrincipal();
        }
    }
    /****************************** EDICION ***************************/

    function mostrarOrdenCliente(r) {
        window.location.href = '<?= base_url() . "ecommerce/ordenes/" ?>' + r.id;
    }

    $(document).ready(function () {

        /*************** ACCIONES PANTALLA PRINCIPAL *********************/
        $(".btn-accion").on('click', function () {
            unsoloclick('.btn-accion');
            if ($(this).val() === "eliminar") {
                eliminarDetalle($(this).data('id'));
            } else if ($(this).val() === "editar") {
                obtenerDetalle($(this).data('id'));
            } else if ($(this).val() === "agregar") {
                agregarDetalle();
            } else if ($(this).val() === "agregar_orden") {
                llamadaAjax(false, '<?= base_url() ?>ecommerce/orden_nueva_cliente', {"cliente_id": $(this).data('id')}, mostrarOrdenCliente);
            }
        });

        $("#modalEdicion").on('shown.bs.modal', function () {
        });

        /*************** ACCIONES MODAL *****************/

        /***************** MODAL EDICION *************************/
//        $("body").delegate("#modalEdicion .btn-guardar-modal", "click", function () {

        /***************** MODAL EDICION DETALLE *********************/


//        $("body").delegate("#modalEdicion .btn-guardar-modal", "click", function () {
//            $(this).html(loadingBtn);
//            $(this).attr('disabled', true);
//            $("#modalEdicion #form_modal_edicion").submit();
//        });



        /***************** ACCIONES MODAL DETALLE *******************/

        $("#texto_busqueda").on('keypress', function (e) {
            if (e.which === 13) {
                $("#btn_buscar").trigger("click");
            }
        });

    });
</script>