<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel"><span id="modal_edicion_master">Usuario</span></h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <?php
    $perfiles_seleccionados = array();
    if ($perfil) {
        foreach ($perfil as $k => $field2) {
            $perfiles_seleccionados[] = $field2->perfil_id;
        }
    }
    $fincas_seleccionadas = array();
    if (isset($finca)) {
        if($finca){
        foreach ($finca as $k => $field3) {
            $fincas_seleccionadas[] = $field3->finca_id;
        }
        }
    }

    $fields = array();
    foreach ($usuario as $field => $value) {
        if (!(strpos(strtoupper($field), 'CREACION_') === 0 || strpos(strtoupper($field), 'ACTUALIZACION_') === 0 || strpos(strtoupper($field), 'INFO_') === 0)) {
            $fields[$field] = array(
                "id" => $field,
                "name" => $field,
                "value" => $value,
                "tipo" => 'input',
            );
        }
    }

    if (key_exists('id', $fields)) {
        $fields['id']['tipo'] = 'hidden';
        // si contrasena es true, entonces muestro el campo contrasena y oculto los demas
        if ($contrasena) {
            $perfil = false;
            $fields['password']['tipo'] = 'password';
            unset($fields['nombre']);
            unset($fields['usuario']);
            unset($fields['correo']);
            unset($fields['estado']);
            unset($fields['foto']);
            unset($fields['finca_id']);
            unset($fields['perfil_id']);
        } else {
            // si no, entonces se que es para editar y muestro los campos menos la contrasena
//            unset($fields['perfil_id']);
            
            $fields['password']['tipo'] = 'hidden';
            $fields['estado']['tipo'] = 'select';
            $fields['estado']['sel'] = array(ESTADO_ACTIVO => 'Activo', ESTADO_INACTIVO => 'Inactivo');
        }
    } else {
        // aqui es para crear y muestro todos los campos
        $fields['finca_id']['tipo'] = 'select_multiple';
        $fields['finca_id']['sel'] = $fincas;
        $fields['estado']['tipo'] = 'select';
        $fields['estado']['sel'] = array(ESTADO_ACTIVO => 'Activo', ESTADO_INACTIVO => 'Inactivo');
        $fields['perfil_id']['tipo'] = 'select_multiple';
        $fields['perfil_id']['sel'] = $perfiles;
        $fields['password']['tipo'] = 'password';
    }


    //oculto foto -- hasta mas despues
    if (key_exists('foto', $fields)) {
        $fields['foto']['tipo'] = 'hidden';
    }
    ?>
    <?= form_open(base_url() . "seguridad/usuario/usuario_guardar", array("id" => "form_modal_edicion")); ?>
    <div class="form-horizontal col-12 row">
        <?php
        foreach ($fields as $k => $field) {
            echo item_formulario_vertical($field);
        }

        if ($perfil) {
            ?>  
                <?php
                $arr['class'] = " form-control col-12 select2";
                $arr['name'] = "perfil_id";
                $arr['id'] = "perfil_id";
                $arr['tipo'] = "select_multiple";
                $arr['sel'] = $perfiles;
                $arr['value'] = $perfiles_seleccionados;
                echo item_formulario_vertical($arr);
            }
        
         if (isset($finca)) {
             if($finca){
            ?>
        
                <?php
                $arr2['class'] = " form-control col-12 select2";
                $arr2['name'] = "finca_id";
                $arr2['id'] = "finca_id";
                $arr2['tipo'] = "select_multiple";
                $arr2['sel'] = $fincas;
                $arr2['value'] = $fincas_seleccionadas;
                echo item_formulario_vertical($arr2);
            }
         }
            ?>
      
    </div>
    <?= form_close(); ?>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary btn-salir-modal">Salir</button>
    <button type="button" class="btn btn-primary btn-guardar-modal">Guardar Cambios</button>


    <script>
        function recargarUsuario() {
            loadUsuario(<?= isset($usuario->id) ? $usuario->id : 0 ?>);
        }

        $(document).on('click', '#modalEdicion .btn-salir-modal', function () {
            $('#modalEdicion').modal("hide");
        });

        $(document).on('click', '.btn-guardar-modal', function (e) {
            e.preventDefault();
            $("#modalEdicion #form_modal_edicion").submit();
        });

        $(document).on('submit', '#modalEdicion #form_modal_edicion', function (e) {

            console.log("En el submit");
            e.preventDefault();
            e.stopImmediatePropagation();
            if (!unsoloclick()) {
                console.log("No hacemos el submit");
                return false;
            }

            var form = $(this);
            var url = form.attr('action');

            $.ajax({
                type: "POST",
                url: url,
                cache: false,
                data: form.serialize(), // serializes the form's elements.
                success: function (data) {
                    console.log(data);

                    if (data.error) {
                        mostrarError(data.respuesta);
                    } else {
                        mostrarExito(data.respuesta);
                        $("#modalEdicion").modal("hide");
                        recargarPrincipal();
                    }
                }
            });
        });
    </script>

</div>

<!-- Modal -->
<div class="modal" id="modalEdicionDetalle" tabindex="-1" role="dialog" aria-labelledby="wsanchez" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">

        </div>
    </div>
</div>