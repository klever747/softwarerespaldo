<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Producto extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model("ecommerce/service_ecommerce");
        $this->load->model("ecommerce/service_ecommerce_cliente");
        $this->load->model("ecommerce/service_ecommerce_orden");
        $this->load->model("ecommerce/service_ecommerce_producto");
        $this->load->model("ecommerce/service_ecommerce_logistica");
        $this->load->model("ecommerce/service_ecommerce_formula");
    }

    /*     * ******************* PRODUCTO ********************* */

    public function productos() {
        $texto_busqueda = "";
        $listadoProductos = false;
        $estado_id = false;
        $cuantos = 0;
        if ($this->input->post('btn_buscar') != null) {
            $texto_busqueda = $this->input->post('texto_busqueda');
            $estado_id = $this->input->post('estado_id');

            List($listadoProductos, $cuantos) = $this->service_ecommerce_producto->obtenerProducto(false, $estado_id, $texto_busqueda);
        }
        $data['estado_id'] = $estado_id;
        $data['productos'] = $listadoProductos;
        $data['cuantos'] = $cuantos;
        $data['ingredientes_receta'] = $this->service_ecommerce_producto->obtenerIngredientes(true);
        $data['texto_busqueda'] = $texto_busqueda;

        $this->mostrarVista('productos.php', $data);
    }

    public function producto_nuevo() {
        $this->producto_obtener();
    }

    public function producto_obtener() {
        $error = false;
        $sku = false;
        $data['sku'] = false;
        if ($this->input->post('opcion')) {
            $data['sku'] = true;
            $sku = true;
        }
        if ($this->input->post('id') != null) {
            $id = $this->input->post('id');
            $data['operacion'] = '<i class="fa fa-pencil-alt my-float"></i> Editar opción';
            $data['producto'] = $this->service_ecommerce_producto->obtenerProducto($id);
            $data['variantes'] = $this->service_ecommerce_producto->obtenerVariantesProducto($data['producto']->id, ESTADO_ACTIVO);
            $data['variantes'] = $this->load->view('producto_variante_listado.php', $data, true);
        } else {
            $data['operacion'] = '<i class="fa fa-plus my-float"></i> Registro de nuevo producto';
            $data['producto'] = $this->service_ecommerce_producto->obtenerNuevoProducto();
            $data['variantes'] = false;
        }

        $producto_det = $this->load->view('producto_edicion.php', $data, true);

        $respuesta = array("error" => (!$data['producto'] ? true : false), "respuesta" => $producto_det);

        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function producto_guardar() {
        $actualizacion = false;
        if ($this->input->post('id') != null) {
            $id = $this->input->post('id');
            $obj = $this->service_ecommerce_producto->obtenerProducto($id);
        } else {
            $obj = $this->service_ecommerce_producto->obtenerNuevoProducto();
        }
        $arr = array();
        if ($obj) {

            foreach ($obj as $field => $value) {
                if (!(strpos(strtoupper($field), 'CREACION_') === 0 || strpos(strtoupper($field), 'ACTUALIZACION_') === 0 || strpos(strtoupper($field), 'INFO_') === 0)) {
                    $arr[$field] = $this->input->post($field);
                }
            }
            if ($this->input->post('id') != null) {
                $actualizacion = $this->service_ecommerce_producto->actualizarProducto($arr, true);
                if (!$actualizacion) {
                    $respuesta = 'Existe un problema durante la actualizaci&oacute;n';
                } else {
                    $respuesta = 'Registro actualizado';
                }
            } else {
                $actualizacion = $this->service_ecommerce_producto->crearProducto($arr, true);
                if (!$actualizacion) {
                    $respuesta = 'Existe un problema durante la creaci&oacute;n';
                } else {
                    $respuesta = 'Registro creado';
                }
            }
        } else {
            $respuesta = 'No se encuentra el registro';
        }

        $respuesta = array("error" => !$actualizacion, "respuesta" => $respuesta);
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function producto_eliminar() {

        $id = $this->input->post('id');
        $actualizacion = $this->service_ecommerce_producto->actualizarProducto(array("id" => $id, "estado" => ESTADO_INACTIVO), true);
        if (!$actualizacion) {
            $respuesta = 'Existe un problema durante la inactivaci&oacute;n';
        } else {
            $respuesta = 'Registro inactivado';
        }
        $respuesta = array("error" => !$actualizacion, "respuesta" => (!$actualizacion ? 'Existe un problema durante la inactivaci&oacute;n' : 'Registro inactivado'));
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    /*     * ****************** VARIANTE ********************* */

    public function variante_nuevo() {
        $this->variante_obtener();
    }

    public function variante_obtener() {
        $error = false;

        $data['producto_id'] = $this->input->post('producto_id');

        if ($this->input->post('id') != null) {
            $id = $this->input->post('id');
            $data['variante'] = $this->service_ecommerce_producto->obtenerProductoVariante($id);
            //llamar a la funcion para traer las recetas con su ingrediente
            // $obj_ingrediente_receta= $this->migracionMasivaRecetas();
        } else {
            $data['variante'] = $this->service_ecommerce_producto->obtenerNuevoProductoVariante($data['producto_id']);
        }
        $producto = $this->service_ecommerce_producto->obtenerProducto($data['producto_id']);
        $data['sku_prefijo'] = $producto->sku_prefijo;
        $variante = $this->load->view('producto_variante_edicion.php', $data, true);

        $respuesta = array("error" => (!$data['variante'] ? true : false), "respuesta" => $variante);

        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function receta_obtener() {
        $error = false;

        $data['producto_id'] = $this->input->post('producto_id');

        if ($this->input->post('id') != null) {
            $id = $this->input->post('id');
            $data['variante'] = $this->service_ecommerce_producto->obtenerProductoVariante($id);
            //llamar a la funcion para traer las recetas con su ingrediente
            // $obj_ingrediente_receta= $this->migracionMasivaRecetas();
        } else {
            $data['variante'] = $this->service_ecommerce_producto->obtenerNuevoProductoVariante($data['producto_id']);
        }
        $data['recetas'] = $this->service_ecommerce_producto->obtenerReceta($data['variante']->sku);
        $producto = $this->service_ecommerce_producto->obtenerProducto($data['producto_id']);
        $data['sku_prefijo'] = $producto->sku_prefijo;
        $data['ingredientes_receta'] = $this->service_ecommerce_producto->obtenerIngredientes(true);
        $variante = $this->load->view('producto_variante_edicion_receta.php', $data, true);

        $respuesta = array("error" => (!$data['variante'] ? true : false), "respuesta" => $variante);

        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function variante_guardar() {
        $actualizacion = false;
        $data['producto_id'] = $this->input->post('producto_id');
        if ($this->input->post('id') != null) {
            $id = $this->input->post('id');
            $obj = $this->service_ecommerce_producto->obtenerProductoVariante($id);
        } else {
            $obj = $this->service_ecommerce_producto->obtenerNuevoProductoVariante($data['producto_id']);
        }
        $arr = array();
        if ($obj) {
            unset($obj->tipo_algoritmo);
            foreach ($obj as $field => $value) {
                if (!(strpos(strtoupper($field), 'CREACION_') === 0 || strpos(strtoupper($field), 'ACTUALIZACION_') === 0 || strpos(strtoupper($field), 'INFO_') === 0)) {
                    $arr[$field] = $this->input->post($field);
                }
            }
            if ($this->input->post('id') != null) {
                $actualizacion = $this->service_ecommerce_producto->actualizarProductoVariante($arr, true);
                if (!$actualizacion) {
                    $respuesta = 'Existe un problema durante la actualizaci&oacute;n';
                } else {
                    $respuesta = 'Registro actualizado';
                }
            } else {
                $actualizacion = $this->service_ecommerce_producto->crearProductoVariante($arr, true);
                if (!$actualizacion) {
                    $respuesta = 'Existe un problema durante la creaci&oacute;n';
                } else {
                    $respuesta = 'Registro creado';
                }
            }
        } else {
            $respuesta = 'No se encuentra el registro';
        }

        $respuesta = array("error" => !$actualizacion, "respuesta" => $respuesta);
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function borrar_variante() {
        $id_variante = $this->input->post('id');
        $receta_actualizar = array("id" => $id_variante, "estado" => ESTADO_INACTIVO);
        $actualizacion = $this->service_ecommerce_producto->actualizarReceta($receta_actualizar);
        if (!$actualizacion) {
            $respuesta = 'Existe un problema durante la actualizaci&oacute;n';
        } else {
            $respuesta = 'Registro actualizado';
        }
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function variante_receta_guardar() {
        $actualizacion = false;
        $data['producto_id'] = $this->input->post('producto_id');
        //actualizar receta en caso de que se requiera actualizar
        $data_update['ingrediente_actualizar_id'] = $this->input->post('ingrediente_actualizar_id');
        $data_update['valor_actualizar'] = $this->input->post('valor_actualizar');
        //obtener datos de la nueva receta que quiera agregar
        $data_add['ingrediente_id'] = $this->input->post('ingrediente_id');
        $data_add['cantidad_stems'] = $this->input->post('cantidad_stems');

        if ($this->input->post('id') != null) {
            $id = $this->input->post('id');
            $obj = $this->service_ecommerce_producto->obtenerProductoVariante($id);
        } else {
            $obj = $this->service_ecommerce_producto->obtenerNuevoProductoVariante($data['producto_id']);
        }
        $arr = array();

        $receta_sku = $this->service_ecommerce_producto->obtenerReceta($obj->sku);
        if ($this->input->post('ingrediente_actualizar_id') != null && $this->input->post('valor_actualizar') != null && $this->input->post('ingrediente_id') == null && $this->input->post('cantidad_stems') == null) {
            $receta_add = array();

            //comparar los datos de la tabla receta para actualizar o para crear una nueva receta
            //para actualizar una receta              
            foreach ($data_update['ingrediente_actualizar_id'] as $k => $rec) {
                if ($receta_sku[$k]->cantidad != $data_update['valor_actualizar'][$k]) {
                    foreach ($data_update['ingrediente_actualizar_id'] as $k => $rec) {
                        //inactivar las recetas
                        $receta_actualizar = array("id" => $rec, "cantidad" => $receta_sku[$k]->cantidad, "estado" => ESTADO_INACTIVO);
                        $actualizacion = $this->service_ecommerce_producto->actualizarReceta($receta_actualizar);
                        //crear la receta
                        $receta_add = array("sku" => $obj->sku, "ingrediente_id" => $receta_sku[$k]->ingrediente_id, "cantidad" => $data_update['valor_actualizar'][$k], "estado" => ESTADO_ACTIVO);
                        $creacion = $this->service_ecommerce_producto->crearReceta($receta_add);
                    }
                    break;
                } else {
                    $receta_actualizar = array("id" => $rec, "cantidad" => $data_update['valor_actualizar'][$k]);
                    $actualizacion = $this->service_ecommerce_producto->actualizarReceta($receta_actualizar);
                }
            }

            if (!$actualizacion) {
                $respuesta = 'Existe un problema durante la actualizaci&oacute;n';
            } else {
                $respuesta = 'Registro actualizado';
            }
        } else {
            //para actualizar una receta  
            if ($data_update['ingrediente_actualizar_id']) {
                foreach ($data_update['ingrediente_actualizar_id'] as $k => $rec) {
                    //inactivar las recetas
                    $receta_actualizar = array("id" => $rec, "cantidad" => $receta_sku[$k]->cantidad, "estado" => ESTADO_INACTIVO);
                    $actualizacion = $this->service_ecommerce_producto->actualizarReceta($receta_actualizar);
                    //crear la receta
                    $receta_add = array("sku" => $obj->sku, "ingrediente_id" => $receta_sku[$k]->ingrediente_id, "cantidad" => $data_update['valor_actualizar'][$k], "estado" => ESTADO_ACTIVO);
                    $creacion = $this->service_ecommerce_producto->crearReceta($receta_add);
                }
            } else {
                $actualizacion = true;
            }
            //para agregar una nueva receta

            foreach ($data_add['ingrediente_id'] as $k => $ingrediente) {
                $receta_add = array("sku" => $obj->sku, "ingrediente_id" => $ingrediente, "cantidad" => $data_add['cantidad_stems'][$k], "estado" => ESTADO_ACTIVO);
                $creacion = $this->service_ecommerce_producto->crearReceta($receta_add);
            }
            if (!$actualizacion && !$creacion) {
                $respuesta = 'Existe un problema durante la actualizaci&oacute;n';
            } else {
                $respuesta = 'Registro actualizado';
            }
            // $actualizacion = $this->service_ecommerce_producto->crearProductoVariante($arr, true);
//            if (!$actualizacion) {
//                $respuesta = 'Existe un problema durante la creaci&oacute;n';
//            } else {
//                $respuesta = 'Registro creado';
//            }
        }


        $respuesta = array("error" => !$actualizacion, "respuesta" => $respuesta);
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function variante_eliminar() {
        $id = $this->input->post('id');
        $actualizacion = $this->service_ecommerce_producto->actualizarProductoVariante(array("id" => $id, "estado" => ESTADO_INACTIVO), true);
        $respuesta = array("error" => !$actualizacion, "respuesta" => (!$actualizacion ? 'Existe un problema durante la inactivaci&oacute;n' : 'Registro inactivado'));
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    /*     * ***********Ingredientes Recetas*************** */

    public function migracionMasivaRecetas() {

        $data = $this->service_ecommerce_producto->obtenerVariantes(true);
        foreach ($data as $k => $value) {
            $valor = $this->migracionIngredienteReceta($value->sku);
            if (!$valor) {
                $arr[$k] = $value->sku;
            }
        }

        $data = $this->service_ecommerce_producto->obtenerPropiedades(true);
        foreach ($data as $k => $value) {
            $valor = $this->migracionIngredienteReceta($value->nombre);
            if (!$valor) {
                $arr[$k] = $value->sku;
            }
        }
    }

    public function migracionIngredienteReceta($sku) {
        $largo_cm = "40";
        $arr = explode("_", $sku);
        error_log(print_r($arr, true)); //die;
        if ((sizeof($arr) == 5) && is_numeric($arr[4]) && ($arr[4] >= 40)) {
            $largo_cm = $arr[4] * 1;
        }
        $data = $this->service_ecommerce_producto->obtenerIngredientesRecetas($sku);
        if (!$data) {
            return false;
        }
        foreach ($data as $k => $datos) {

            //verificar que no exista un ingrediente previamente creado
            $resp = $this->service_ecommerce_producto->buscarRepetido($datos->nombre, $datos->descripcion, $datos->tipo, $largo_cm);
            //$resp = $this->service_ecommerce_producto->buscarRepetido('PLAYA BLANCA','PLAYA BLANCA','N'); 
            if (!$resp) {
                $ingrediente_[$k] = array("nombre" => $datos->nombre,
                    "descripcion" => $datos->descripcion,
                    "tipo" => $datos->tipo,
                    "longitud" => $largo_cm,
                    "estado" => ESTADO_ACTIVO);
                //guardando cada ingrediente en la bd
                $ingrediente_id = $this->service_ecommerce_producto->guardarIngrediente($ingrediente_[$k]);
                $receta_[$k] = array("sku" => $datos->sku,
                    "ingrediente_id" => $ingrediente_id,
                    "cantidad" => $datos->cantidad,
                    "estado" => ESTADO_ACTIVO);
                //guardo la receta correspondiente a un ingrediente
                $receta = $this->service_ecommerce_producto->guardarReceta($receta_[$k]);
            } else {
                $receta_[$k] = array("sku" => $datos->sku,
                    "ingrediente_id" => $resp->id,
                    "cantidad" => $datos->cantidad,
                    "estado" => ESTADO_ACTIVO);
                //guardo la receta correspondiente a un ingrediente
                $receta = $this->service_ecommerce_producto->guardarReceta($receta_[$k]);
            }
        }
        $data_nueva = $this->service_ecommerce_producto->obtenerIngredientesRecetasNuevo($sku);

        return $data_nueva;
    }

    /*     * ************* PROPIEDADES ****************** */

    public function propiedades() {
        $texto_busqueda = "";
        $listadoPropiedades = false;
        $estado_id = false;
        $cuantos = 0;
        if ($this->input->post('btn_buscar') != null) {
            $texto_busqueda = $this->input->post('texto_busqueda');
            $estado_id = $this->input->post('estado_id');

            List($listadoPropiedades, $cuantos) = $this->service_ecommerce_producto->obtenerPropiedad(false, $estado_id, $texto_busqueda, false, true);
        }
        $data['estado_id'] = $estado_id;
        $data['propiedades'] = $listadoPropiedades;
        $data['cuantos'] = $cuantos;

        $data['texto_busqueda'] = $texto_busqueda;

        $this->mostrarVista('propiedades.php', $data);
    }

    public function propiedad_nuevo() {
        $this->propiedad_obtener();
    }

    public function propiedad_obtener() {
        $error = false;

        if ($this->input->post('id') != null) {
            $id = $this->input->post('id');
            $data['propiedad'] = $this->service_ecommerce_producto->obtenerPropiedad($id);
            $data['propiedad_valores'] = $this->service_ecommerce_producto->obtenerPropiedadValores($data['propiedad']->id, ESTADO_ACTIVO);
            $data['operacion'] = '<i class="fa fa-pencil-alt my-float"></i> Editar información';
        } else {
            $data['operacion'] = '<i class="fa fa-plus-alt my-float"></i> Registro propiedad';
            $data['propiedad'] = $this->service_ecommerce_producto->obtenerNuevaPropiedad();
            $data['propiedad_valores'] = false;
        }

        $propiedad_det = $this->load->view('propiedad_edicion.php', $data, true);

        $respuesta = array("error" => (!$data['propiedad'] ? true : false), "respuesta" => $propiedad_det);

        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function propiedad_guardar() {
        $actualizacion = false;
        if ($this->input->post('id') != null) {
            $id = $this->input->post('id');
            $obj = $this->service_ecommerce_producto->obtenerPropiedad($id);
        } else {
            $obj = $this->service_ecommerce_producto->obtenerNuevaPropiedad();
        }
        $arr = array();
        if ($obj) {

            foreach ($obj as $field => $value) {
                if (!(strpos(strtoupper($field), 'CREACION_') === 0 || strpos(strtoupper($field), 'ACTUALIZACION_') === 0 || strpos(strtoupper($field), 'INFO_') === 0)) {
                    $arr[$field] = $this->input->post($field);
                }
            }
            if ($this->input->post('id') != null) {
                $actualizacion = $this->service_ecommerce_producto->actualizarPropiedad($arr, true);
                if (!$actualizacion) {
                    $respuesta = 'Existe un problema durante la actualizaci&oacute;n';
                } else {
                    $respuesta = 'Registro actualizado';
                }
            } else {
                $arr['editable'] = 1;
                $actualizacion = $this->service_ecommerce_producto->crearPropiedad($arr, true);
                if (!$actualizacion) {
                    $respuesta = 'Existe un problema durante la creaci&oacute;n';
                } else {
                    $respuesta = 'Registro creado';
                }
            }
        } else {
            $respuesta = 'No se encuentra el registro';
        }

        $respuesta = array("error" => !$actualizacion, "respuesta" => $respuesta);
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function propiedad_eliminar() {

        $id = $this->input->post('id');
        $actualizacion = $this->service_ecommerce_producto->actualizarPropiedad(array("id" => $id, "estado" => ESTADO_INACTIVO), true);
        if (!$actualizacion) {
            $respuesta = 'Existe un problema durante la inactivaci&oacute;n';
        } else {
            $respuesta = 'Registro inactivado';
        }
        $respuesta = array("error" => !$actualizacion, "respuesta" => (!$actualizacion ? 'Existe un problema durante la inactivaci&oacute;n' : 'Registro inactivado'));
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function propiedades_select() {
        $texto = $this->input->post('texto');
        $orden_item_id = $this->input->post('orden_item_id');
        $propiedades = $this->service_ecommerce->obtenerOrdenItemPropiedades($orden_item_id, ESTADO_ACTIVO);
        $no_estas_propiedades = false;
        if ($propiedades) {
            $no_estas_propiedades = "";
            foreach ($propiedades as $propiedad) {
                $no_estas_propiedades .= "," . $propiedad->propiedad_id;
            }
            $no_estas_propiedades = substr($no_estas_propiedades, 1);
        }
        $arr = $this->service_ecommerce_producto->obtenerSelPropiedades($no_estas_propiedades);
//        die(print_r($arr, true));
        header('Content-Type: application/json');
        echo json_encode($arr);
    }

}
