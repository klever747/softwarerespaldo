<?php

class Service_ecommerce_producto extends My_Model {

    public function obtenerNuevoProducto() {
        return (object) [
                    'titulo' => '',
                    'descripcion' => '',
                    'sku_prefijo' => '',
                    'tags' => '',
                    'estado' => ESTADO_ACTIVO,
        ];
    }

    public function existeProducto($objProducto) {
        $this->db->select('o.*');
        $this->db->from('ecommerce.producto o');
        $this->db->where('sku_prefijo', $objProducto['sku_prefijo']);

        return $this->retornarUno();
    }

    public function obtenerProducto($id = false, $estado = false, $texto_busqueda = false) {
        $this->db->select('p.*');
        $this->db->from('ecommerce.producto p');

        if ($id) {
            $this->db->where('p.id', $id);
            return $this->retornarUno();
        }

        if ($estado) {
            $this->db->where('p.estado', $estado);
        }
        if ($texto_busqueda) {
            $this->db->where(" (UPPER(p.titulo) LIKE '%" . strtoupper($texto_busqueda) . "%' "
                    . "OR UPPER(p.descripcion) LIKE '%" . strtoupper($texto_busqueda) . "%' "
                    . "OR UPPER(p.sku_prefijo) LIKE '%" . strtoupper($texto_busqueda) . "%')");
        }
        $this->db->order_by('p.titulo', 'ASC');

        $conteo = $this->retornarConteo();
        $arr = $this->retornarMuchos();

        return array($arr, $conteo);
    }

    public function obtenerProductos($estado = false) {
        $this->db->select('o.*');
        $this->db->from('ecommerce.producto o');
        if ($estado) {
            $this->db->where('estado', $estado);
        }
        $conteo = $this->retornarConteo();
        $arr = $this->retornarMuchos();

        return array($arr, $conteo);
    }

    public function crearProducto($obj) {
        error_log("Vamos a crear Producto");
        $id = $this->ingresar("ecommerce.producto", $obj, true, true);
        if ($id) {
            $dato_log = array(
                "producto_id" => $id,
                "accion" => "creacion de producto" . json_encode($obj),
            );
            $this->registrarLog("ecommerce.producto_log", $dato_log);
        }
        return $id;
    }

    public function actualizarProducto($obj) {
        $id = $this->actualizar("ecommerce.producto", $obj, "id", true);
        if ($id) {
            $dato_log = array(
                "producto_id" => $obj['id'],
                "accion" => "actualizacion de producto" . json_encode($obj),
            );
            $this->registrarLog("ecommerce.producto_log", $dato_log);
        }
        return $id;
    }

    public function persistenciaProducto($obj) {
        $id = false;
        $producto = $this->existeProducto($obj);
//        error_log(print_r($producto,true));
        if (!$producto) {
            $nu = 1;
            $obj["estado"] = ESTADO_ACTIVO;
            $id = $this->crearProducto($obj, true);
        } else {
            $nu = 2;
            $id = $producto->id;
//            $obj['id'] = $producto->id;
//            unset($obj['titulo']);
//            unset($obj['descripcion']);
//            if ($this->actualizarProducto($obj)) {
//                $id = $producto->id;
//            }
        }
        return array($nu, $id);
    }

    public function obtenerSelProductos($producto_seleccionado_id = false, $texto = false) {
        $this->db->select("p.id, p.titulo || ' ' || p.sku_prefijo as descripcion");
        $this->db->from('ecommerce.producto p');
        $this->db->where('estado', ESTADO_ACTIVO);
        if ($texto) {
            $this->db->where(" (UPPER(p.titulo) LIKE '%" . strtoupper($texto) . "%'  "
                    . "OR UPPER(p.descripcion) LIKE '%" . strtoupper($texto) . "%' "
                    . "OR UPPER(p.sku_prefijo) LIKE '" . strtoupper($texto) . "%' )");
        }
        $this->db->order_by("p.titulo", "ASC");
        $arrDatos = $this->retornarMuchosSinPaginacion();
        return $this->retornarSel($arrDatos, "descripcion", false);
    }

    /*     * ******************* PRODUCTO VARIANTES ***************** */

    public function obtenerNuevoProductoVariante($producto_id) {
        return (object) [
                    'producto_id' => $producto_id,
                    'titulo' => '',
                    'sku' => '',
                    'cantidad' => '',
                    'largo_cm' => '',
                    'unidad' => '',
                    'estado' => ESTADO_ACTIVO
        ];
    }

    public function existeProductoVariante($objProductoVariante) {
        $this->db->select('o.*');
        $this->db->from('ecommerce.producto_variante o');
        $this->db->where('sku', $objProductoVariante['sku']);

        return $this->retornarUno();
    }

    public function obtenerProductoVariante($id = false, $estado = false, $texto_busqueda = false) {

        $this->db->from('ecommerce.producto_variante pv');
        if ($id) {
            $this->db->where('pv.id', $id);
            return $this->retornarUno();
        }
        $this->db->select('pv.*,s.tipo_algoritmo');
        $this->db->join('ecommerce.sku_algoritmo s', "s.sku = pv.sku AND s.estado = '" . ESTADO_ACTIVO . "'", 'left');
        if ($estado) {
            $this->db->where('pv.estado', $estado);
        }
        if ($texto_busqueda) {
            $this->db->where(" (UPPER(pv.titulo) LIKE '%" . strtoupper($texto_busqueda) . "%' "
                    . "OR UPPER(pv.sku) LIKE '%" . strtoupper($texto_busqueda) . "%')");
        }
        $this->db->order_by('pv.titulo', 'ASC');
        $conteo = $this->retornarConteo();
        $arr = $this->retornarMuchos();

        return array($arr, $conteo);
    }

    public function crearProductoVariante($obj) {
        $id = $this->ingresar("ecommerce.producto_variante", $obj, true, true);
        if ($id) {
            $dato_log = array(
                "producto_variante_id" => $id,
                "accion" => "creacion de producto variante" . json_encode($obj),
            );
            $this->registrarLog("ecommerce.producto_variante_log", $dato_log);
        }
        return $id;
    }

    public function actualizarProductoVariante($obj) {
        $id = $this->actualizar("ecommerce.producto_variante", $obj, "id", true);
        if ($id) {
            $dato_log = array(
                "producto_variante_id" => $obj['id'],
                "accion" => "actualizacion de producto variante" . json_encode($obj),
            );
            $this->registrarLog("ecommerce.producto_variante_log", $dato_log);
        }
        return $id;
    }

    public function persistenciaProductoVariante($objProductoVariante) {
        $id = false;
        $producto_variante = $this->existeProductoVariante($objProductoVariante);
        if (!$producto_variante) {
            $nu = 1;
            $objProductoVariante["estado"] = ESTADO_ACTIVO;
            $id = $this->crearProductoVariante($objProductoVariante, true);
        } else {
            $nu = 2;
            $id = $producto_variante->id;
//            $objProductoVariante['id'] = $producto_variante->id;
//            unset($objProductoVariante['titulo']);
//            if ($this->actualizarProductoVariante($objProductoVariante)) {
//                $id = $producto_variante->id;
//            }
        }
        return array($nu, $id);
    }

    public function obtenerVariantesProducto($producto_id, $estado = false) {
        $this->db->select('pv.*,s.tipo_algoritmo');
        $this->db->from('ecommerce.producto_variante pv');
        $this->db->join('ecommerce.sku_algoritmo s', "s.sku = pv.sku AND s.estado = '" . ESTADO_ACTIVO . "'", 'left');
        if ($producto_id) {
            $this->db->where('pv.producto_id', $producto_id);
        }
        if ($estado) {
            $this->db->where('pv.estado', $estado);
        }
        $this->db->order_by('pv.titulo', 'ASC');
        return $this->retornarMuchosSinPaginacion();
    }

    public function obtenerSelProductoVariantes($producto_id = false, $variante_id = false, $texto = false) {
        $this->db->select("pv.id, pv.producto_id, pv.titulo || ' ' || pv.sku as descripcion");
        $this->db->from('ecommerce.producto_variante pv');
        if ($variante_id) {
            $this->db->where('pv.id', $variante_id);
        }
        if ($producto_id) {
            $this->db->where('pv.producto_id', $producto_id);
        }
        $this->db->where('estado', ESTADO_ACTIVO);
        if ($texto) {
            $this->db->where(" (UPPER(pv.titulo) LIKE '%" . strtoupper($texto) . "%'  "
                    . "OR UPPER(pv.sku) LIKE '" . strtoupper($texto) . "%' )");
        }
        $this->db->order_by("pv.sku", "ASC");
        $arrDatos = $this->retornarMuchosSinPaginacion();
        return $this->retornarSel($arrDatos, "descripcion", false);
    }

    /*     * **************************** PROPIEDADES ************************ */

    public function obtenerNuevaPropiedad() {
        return (object) [
                    'nombre' => '',
                    'descripcion' => '',
                    'estado' => ESTADO_ACTIVO,
        ];
    }

    public function devolverOcrearPropiedad($nombre) {
        //si la propiedad no existe, la crea y la devuelve
        $propiedad = $this->obtenerPropiedad(false, false, false, $nombre);

        if ($propiedad) {
            return $propiedad;
        }
        //deberian aplicarse reglas para que cuando entre un componente para assemble 
        //se agrupe en los valores disponibles para un producto assemble
        $datos['nombre'] = trim(strtoupper($nombre));
        $datos['descripcion'] = $nombre;
        $datos['estado'] = ESTADO_ACTIVO;
        if (strpos($datos['nombre'], "AGR_") === 0) {
            $datos['editable'] = 1;
        }
        $id = $this->crearPropiedad($datos);
        return $this->obtenerPropiedad($id);
    }

    public function obtenerPropiedad($id = false, $estado = false, $texto_busqueda = false, $nombre_exacto = false, $editable = false) {
        $this->db->select('p.*');
        $this->db->from('ecommerce.propiedad p');

        if ($id) {
            $this->db->where('p.id', $id);
            return $this->retornarUno();
        }
        if ($nombre_exacto != false) {
            $this->db->where('p.nombre', trim(strtoupper($nombre_exacto)));
            return $this->retornarUno();
        }
        if ($estado) {
            $this->db->where('p.estado', $estado);
        }
        if ($texto_busqueda) {
            $this->db->where(" (UPPER(p.nombre) LIKE '%" . strtoupper($texto_busqueda) . "%' "
                    . "OR UPPER(p.descripcion) LIKE '%" . strtoupper($texto_busqueda) . "%')");
        }
        if ($editable) {
            $this->db->where('p.editable', 1);
        }
        $this->db->order_by('p.nombre', 'ASC');

        $conteo = $this->retornarConteo();
        $arr = $this->retornarMuchos();

        return array($arr, $conteo);
    }

    public function crearPropiedad($obj) {
        $id = $this->ingresar("ecommerce.propiedad", $obj, true, true);
        if ($id) {
            $dato_log = array(
                "propiedad_id" => $id,
                "accion" => "creacion de propiedad" . json_encode($obj),
            );
            $this->registrarLog("ecommerce.propiedad_log", $dato_log);
        }
        return $id;
    }

    public function actualizarPropiedad($obj) {
        $id = $this->actualizar("ecommerce.propiedad", $obj, "id", true);
        if ($id) {
            $dato_log = array(
                "propiedad_id" => $obj['id'],
                "accion" => "actualizacion de propiedad" . json_encode($obj),
            );
            $this->registrarLog("ecommerce.propiedad_log", $dato_log);
        }
        return $id;
    }

    public function obtenerSelPropiedades($no_estas_propiedades = false) {
        $this->db->select("p.id, p.descripcion,p.nombre");
        $this->db->from('ecommerce.propiedad p');
        $this->db->where('estado', ESTADO_ACTIVO);
        $this->db->where('editable', 1);
        if ($no_estas_propiedades) {
            $this->db->where('p.id NOT IN (' . $no_estas_propiedades . ')');
        }
        //$this->db->where('UPPER(p.descripcion) NOT LIKE \'DATE%\'');        
        $this->db->order_by('p.descripcion', 'ASC');
        $arrDatos = $this->retornarMuchosSinPaginacion();
        return $this->retonarSelConData($arrDatos, "descripcion", false);
    }

    /*     * ***************** Propiedad Valores ********************* */

    public function obtenerPropiedadValores($propiedad_id, $estado) {
        $this->db->select('pv.*');
        $this->db->from('ecommerce.propiedad_valor pv');

        $this->db->where('pv.propiedad_id', $propiedad_id);
        if ($estado) {
            $this->db->where('pv.estado', $estado);
        }
        $this->db->order_by('pv.descripcion', 'ASC');

        return $this->retornarMuchos();
    }

    public function obtenerPropiedadValor($id = false, $estado = false) {
        $this->db->select('pv.*');
        $this->db->from('ecommerce.propiedad_valor pv');

        if ($id) {
            $this->db->where('pv.id', $id);
            return $this->retornarUno();
        }
        if ($estado) {
            $this->db->where('pv.estado', $estado);
        }
        $this->db->order_by('p.descripcion', 'ASC');

        return $this->retornarMuchos();
    }

    /*     * ***********************************Recetas Nuevo **************************************** */

    public function obtenerReceta($sku) {
        $this->db->select('ig.id as ingrediente_id,ig.nombre,ig.descripcion,re.cantidad, ig.longitud,re.id');
        $this->db->from('produccion.ingrediente ig', 'left');
        $this->db->join('produccion.receta re', 'ig.id = re.ingrediente_id');
        $this->db->where('re.sku', $sku);
        $this->db->where('re.estado', ESTADO_ACTIVO);
        $this->db->order_by('re.id', 'ASC');
        $arr = $this->retornarMuchosSinPaginacion();
        return $arr;
    }

    public function crearReceta($obj) {
        error_log("Vamos a crear una Receta");
        $id = $this->ingresar("produccion.receta", $obj, true, true);
        if ($id) {
            $dato_log = array(
                "receta_id" => $id,
                "accion" => "creacion de una nueva receta" . json_encode($obj),
            );
            $this->registrarLog("produccion.receta_log", $dato_log);
        }
        return $id;
    }

    public function actualizarReceta($obj) {
        $id = $this->actualizar("produccion.receta", $obj, "id", true);
        if ($id) {
            $dato_log = array(
                "receta_id" => $obj['id'],
                "accion" => "actualizacion de receta" . json_encode($obj),
            );
            $this->registrarLog("produccion.receta_log", $dato_log);
        }
        return $id;
    }

    /*     * **************************Ingredientes Recetas Migracion********************************** */

    public function obtenerIngredientes() {
        $this->db->select('ig.id,ig.nombre, ig.descripcion, ig.tipo, ig.longitud');
        $this->db->from('produccion.ingrediente ig');
        $this->db->order_by('ig.nombre', 'ASC');
        $this->db->order_by('ig.descripcion', 'ASC');
        $this->db->order_by('ig.longitud', 'ASC');
        $arr = $this->retornarMuchosSinPaginacion();
        return $arr;
    }

    public function obtenerIngredientesRecetas($sku) {
        $this->db->select('re.sku,re.ingrediente_id,re.cantidad, ig.nombre,ig.descripcion, ig.tipo');
        $this->db->from('produccion.receta_old re', 'left');
        $this->db->join('produccion.ingrediente_old ig', "on re.ingrediente_id = ig.id");
        $this->db->where('re.sku', $sku);
        $arr = $this->retornarMuchosSinPaginacion();
        return $arr;
    }

    public function obtenerPropiedades() {
        $this->db->select('p.nombre');
        $this->db->from('ecommerce.propiedad p ');
        $this->db->where("p.nombre LIKE 'AGR_%'");
        $this->db->where('p.estado', ESTADO_ACTIVO);
        $arr = $this->retornarMuchosSinPaginacion();
        return $arr;
    }

    public function buscarRepetido($nombre, $descripcion, $tipo, $largo_cm) {
        $this->db->select('ig.id,ig.nombre,ig.descripcion,ig.tipo');
        $this->db->from('produccion.ingrediente ig');
        $this->db->where('ig.nombre', $nombre);
        $this->db->where('ig.descripcion', $descripcion);
        $this->db->where('ig.tipo', $tipo);
        $this->db->where('ig.longitud', $largo_cm);
        $arr = $this->retornarUno();
        return $arr;
    }

    public function guardarIngrediente($obj) {
        $id = $this->ingresar("produccion.ingrediente", $obj, true, true);
        if ($id) {
            $dato_log = array(
                "ingrediente_id" => $id,
                "accion" => "creacion de nuevo ingrediente" . json_encode($obj),
            );
            $this->registrarLog("produccion.ingrediente_log", $dato_log);
        }
        return $id;
    }

    public function guardarReceta($obj) {
        $id = $this->ingresar("produccion.receta", $obj, true, true);
        if ($id) {
            $dato_log = array(
                "receta_id" => $id,
                "accion" => "creacion de una nueva receta" . json_encode($obj),
            );
            $this->registrarLog("produccion.receta_log", $dato_log);
        }
        return $id;
    }

    public function obtenerIngredientesRecetasNuevo($sku) {
        $this->db->select('re.sku,re.ingrediente_id,re.cantidad, ig.nombre,ig.descripcion, ig.tipo');
        $this->db->from('produccion.receta re', 'left');
        $this->db->join('produccion.ingrediente ig', "on re.ingrediente_id = ig.id");
        $this->db->where('re.sku', $sku);
        $arr = $this->retornarMuchosSinPaginacion();
        return $arr;
    }

    public function obtenerVariantes($obtener_todos = false) {

        $this->db->select('*');
        $this->db->from('ecommerce.producto_variante');

        if ($obtener_todos) {
            $arr = $this->retornarMuchosSinPaginacion();
        } else {
            $arr = $this->retornarMuchos(true);
        }
        return $arr;
    }

}
