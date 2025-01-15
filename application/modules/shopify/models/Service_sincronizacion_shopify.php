<?php

class Service_sincronizacion_shopify extends My_Model {

    private $reg_parametro = null;

    function __construct() {
        parent::__construct();
        $this->load->model("generales/service_ws");
        $this->load->model("shopify/shopify_model");
    }

    public function obtenerParametros($store_id) {
        $this->db->select('p.*');
        $this->db->from('ecommerce.shopify_parametros p');

        $this->db->where('store_id', $store_id);
        $this->db->where('estado', ESTADO_ACTIVO);

        $this->reg_parametro = $this->retornarUno();
        if (!$this->reg_parametro) {
            return false;
        }
        error_log(print_r($this->reg_parametro, true));

        $this->reg_parametro->item_por_pagina = 100;

        if ($this->reg_parametro->max_nro_dias_info >= 0) {
            $fecha_actual = new \Datetime("now");

            $fecha_nueva = date_modify($fecha_actual, '-' . $this->reg_parametro->max_nro_dias_info . ' day');

            //$updated_at_min = $fecha_nueva->format('Y-m-dT00:00:00P');
            $updated_at_min = $fecha_nueva->format('Y-m-d');

            $this->reg_parametro->condicion_updated_at_min = '&updated_at_min=' . $updated_at_min . 'ECT00:00:00P';
        } else {
            $this->reg_parametro->condicion_updated_at_min = '';
        }
        $this->reg_parametro->dominio = "https://" . $this->reg_parametro->store . "." . $this->reg_parametro->domain;
        $this->reg_parametro->url_shop = "https://" . $this->reg_parametro->api_key . ":" . $this->reg_parametro->password . "@" . $this->reg_parametro->store . "." . $this->reg_parametro->domain;
        return true;
    }

    public function shopifyObtenerSiguienteLink($link) {
        $url = false;
        if (isset($link)) {
            $arrLink = explode(">; rel=", $link);
            if (sizeof($arrLink) > 2) {
                $arrLink = explode($this->reg_parametro->dominio, $arrLink[1]);
                $url = $this->reg_parametro->url_shop . $arrLink[1];
            } else if (substr($arrLink[1], 1, 4) == "next") {
                $url = $this->reg_parametro->url_shop . substr($arrLink[0], strlen($this->reg_parametro->dominio) + 1);
            } else if (substr($arrLink[1], 1, 8) == "previous") {
                
            }
        }
        return $url;
    }

    public function consultarTotal($url) {
        $response = $this->service_ws->consumirWS($url, false, true);
        print_r($url);
        $body = json_decode($response->body);
        print_r($body);
        $total = $body->count;
        return $total;
    }

    public function procesarOrdenShopify($orden) {
        $orden->store_id = $this->reg_parametro->store_id;
        $order = json_decode(json_encode($orden), true);
        $respuesta = $this->shopify_model->persistenciaShopifyOrder($order);
        return $respuesta;
    }

    public function procesarProductoShopify($producto) {
        $producto->store_id = $this->reg_parametro->store_id;
        $prod = json_decode(json_encode($producto), true);
        $respuesta = $this->shopify_model->persistenciaShopifyProduct($prod);
        return $respuesta;
    }

    public function sincronizar($store_id, $tipo) {
        set_time_limit(-1);
        $hayParametros = $this->obtenerParametros($store_id);
        if (!$hayParametros) {
            return array(
                "total" => 0,
                "errores" => 1,
                "creados" => 0,
                "actualizados" => 0,
                "erroresSistema" => 1);
        }

        switch ($tipo) {
            case "productos":
                $analizar = "products";
                $urlTotal = $this->reg_parametro->url_shop . "/admin/api/" . $this->reg_parametro->api_version . "/products/count.json?" . $this->reg_parametro->condicion_updated_at_min;
                $condiciones = $this->reg_parametro->condicion_updated_at_min . "&limit=" . $this->reg_parametro->item_por_pagina;
                $url = $this->reg_parametro->url_shop . "/admin/api/" . $this->reg_parametro->api_version . "/products.json?" . $condiciones;
                $funcion = "procesarProductoShopify";
                break;
            case "clientes":
                break;
            case "ordenes":
                $analizar = "orders";
                $urlTotal = $this->reg_parametro->url_shop . "/admin/api/" . $this->reg_parametro->api_version . "/orders/count.json?" . $this->reg_parametro->condicion_updated_at_min . "&status=any";
                $condiciones = $this->reg_parametro->condicion_updated_at_min . "&status=any&limit=" . $this->reg_parametro->item_por_pagina;
                $url = $this->reg_parametro->url_shop . "/admin/api/" . $this->reg_parametro->api_version . "/orders.json?" . $condiciones;
                $funcion = "procesarOrdenShopify";
                break;

            default:
                break;
        }

        $total = $this->consultarTotal($urlTotal);

        $erroresSistema = $errores = $creados = $actualizados = 0;
        $max_paginas = ceil($total / $this->reg_parametro->item_por_pagina);
        $i = 0;
        $fechaInicioProcesamiento = new \Datetime("now");
        error_log("Max paginas " . $max_paginas);
        error_log($url);
        while ($url && ($i < $max_paginas * 2)) {
            error_log("Numero de ciclo " . $i);
            $i++;
            $response = $this->service_ws->consumirWS($url, false, true);
            $url = false;
            if (!$response->estado) {
                $erroresSistema++;
                continue;
            }
            $body = json_decode($response->body);
            $header = $response->header;

            foreach ($body->{$analizar} as $obj) {
                $resp = $this->$funcion($obj);
                if (!$resp[1]) {
                    $errores++;
                } else if ($resp[0] == 1) {
                    $creados++;
                } else {
                    $actualizados++;
                }
            }


            if (isset($header->Link)) {
                $url = $this->shopifyObtenerSiguienteLink($header->Link);
            }
        }
        $fechaFinProcesamiento = new \Datetime("now");
        error_log("Procesamiento de " . $analizar);
        //error_log("Inicio Procesamiento ws " . $fechaInicioProcesamiento->format(FORMATO_FECHA_COMPLETO));
        //error_log("Fin Procesamiento ws " . $fechaFinProcesamiento->format(FORMATO_FECHA_COMPLETO));
        error_log("Tiempo de ejecucion: " . date_diff($fechaInicioProcesamiento, $fechaFinProcesamiento)->format('%i Minute %s Seconds'));
        return array(
            "total" => $total,
            "errores" => $errores,
            "creados" => $creados,
            "actualizados" => $actualizados,
            "erroresSistema" => $erroresSistema);
    }

    public function sincronizarProductos($store_id) {
        return $this->sincronizar($store_id, "productos");
    }

    public function sincronizarOrdenes($store_id) {
        return $this->sincronizar($store_id, "ordenes");
    }

}

?>
