<?php

class Shopify_model extends My_Model {
    /*     * *********************************************************** */
    /*     * *************STORE************ */

    public function obtenerTiendas($id = false) {
        $this->db->select('s.*');
        $this->db->from('ecommerce.store s');
        $this->db->where('s.estado', ESTADO_ACTIVO);
        if ($id) {
            $this->db->where('id', $id);
            return $this->retornarUno();
        } else {
            return $this->retornarMuchosSinPaginacion();
        }
    }

    public function obtenerTiendasSel() {
        $tiendas = $this->obtenerTiendas();
        return $this->retornarSel($tiendas, "store_name");
    }

    /*     * *********************************************************** */
    /*     * *********************************************************** */

    public function obtenerProductosVariants($idProducto) {
        $this->db->select('pv.*');
        $this->db->from('ecommerce.shopify_product_variant pv');
        $this->db->where('shopify_product_id', $idProducto);
        return $this->retornarMuchosSinPaginacion();
    }

    public function obtenerProductos($store_id, $busqueda = '') {
        $this->db->select('p.*');
        $this->db->from('ecommerce.shopify_product p');
        $this->db->where('store_id', $store_id);

        if (!empty($busqueda)) {
            $this->db->like('p.title', $busqueda);
            $this->db->or_like('handle', $busqueda);
        }

        $conteo = $this->retornarConteo();

        $arrProd = $this->retornarMuchos();
        if ($arrProd) {
            foreach ($arrProd as $prod) {
                $variantes = $this->obtenerProductosVariants($prod->id);
                if (!$variantes) {
                    $variantes = array();
                }
                $prod->variants = $variantes;
            }
        }

        return array($arrProd, $conteo);
    }

    public function existeShopifyProduct($id) {
        $this->db->select('p.*');
        $this->db->from('ecommerce.shopify_product p');
        $this->db->where('id', $id);

        return $this->retornarUno();
    }

    public function existeShopifyProductVariant($id) {
        $this->db->select('pv.*');
        $this->db->from('ecommerce.shopify_product_variant pv');
        $this->db->where('id', $id);

        return $this->retornarUno();
    }

    public function persistenciaShopifyProduct($data) {
        $obj = array(
            "id" => $data['id'],
            "title" => $data['title'],
            "vendor" => $data['vendor'],
            "product_type" => $data['product_type'],
            "handle" => $data['handle'],
            "tags" => $data['tags'],
            "created_at" => $data['created_at'],
            "updated_at" => $data['updated_at'],
            "body_html" => $data['body_html'],
            "template_suffix" => $data['template_suffix'],
            "published_scope" => $data['published_scope'],
            "image" => json_encode($data['image']),
            "store_id" => $data['store_id'],
            "estado" => ESTADO_ACTIVO);

        if (!$this->existeShopifyProduct($data['id'])) {
            $nu = 1;
            $cab = $this->ingresarNuevoShopifyProduct($obj);
        } else {
            $nu = 2;
            $cab = $this->actualizarShopifyProduct($obj);
        }
        if ($cab) {
            $resp = true;
            foreach ($data['variants'] as $variant) {
                $det = $this->persistenciaShopifyProductVariant($variant);
                if (!$det[1]) {
                    $resp = false;
                    break;
                }
            }
        } else {
            $resp = false;
        }
        return array($nu, $resp);
    }

    public function persistenciaShopifyProductVariant($data) {
        $obj = array(
            "id" => $data['id'],
            "shopify_product_id" => $data['product_id'],
            "title" => $data['title'],
            "price" => $data['price'],
            "sku" => $data['sku'],
//            "position" => $data['position'],
//            "inventory_policy" => $data['inventory_policy'],
//            "compare_at_price" => $data['compare_at_price'],
//            "fulfillment_service" => $data['fulfillment_service'],
//            "inventory_management" => $data['inventory_management'],
            "option1" => $data['option1'],
            "option2" => json_encode($data['option2']),
            "option3" => $data['option3'],
            "created_at" => $data['created_at'],
            "updated_at" => $data['updated_at'],
//            "taxable" => $data['taxable'],
//            "barcode" => $data['barcode'],
//            "grams" => $data['grams'],
//            "image_id" => $data['image_id'],
            "weight" => $data['weight'],
            "weight_unit" => $data['weight_unit'],
            "inventory_item_id" => $data['inventory_item_id'],
            "inventory_quantity" => $data['inventory_quantity'],
            "old_inventory_quantity" => $data['old_inventory_quantity'],
            "requires_shipping" => $data['requires_shipping'],
            "estado" => ESTADO_ACTIVO);

        if (!$this->existeShopifyProductVariant($data['id'])) {
            return array(1, $this->ingresarNuevoShopifyProductVariant($obj));
        } else {
            return array(2, $this->actualizarShopifyProductVariant($obj));
        }
    }

    public function ingresarNuevoShopifyProduct($datos) {
        $datos["estado"] = ESTADO_ACTIVO;
        return $this->ingresar("ecommerce.shopify_product", $datos);
    }

    public function actualizarShopifyProduct($datos) {
        return $this->actualizar("ecommerce.shopify_product", $datos);
    }

    public function ingresarNuevoShopifyProductVariant($datos) {
        $datos["estado"] = ESTADO_ACTIVO;
        return $this->ingresar("ecommerce.shopify_product_variant", $datos);
    }

    public function actualizarShopifyProductVariant($datos) {
        return $this->actualizar("ecommerce.shopify_product_variant", $datos);
    }

    /*     * *********************************************************** */
    /*     * *********************************************************** */

    public function existeShopifyOrder($id) {
        $this->db->select('o.*');
        $this->db->from('ecommerce.shopify_order o');
        $this->db->where('shopify_order_id', $id);

        return $this->retornarUno();
    }

    public function existeShopifyOrderItem($id) {
        $this->db->select('oi.*');
        $this->db->from('ecommerce.shopify_order_item oi');
        $this->db->where('shopify_order_item_id', $id);

        return $this->retornarUno();
    }

    public function persistenciaShopifyOrder($data) {
        $obj = array(
            "shopify_order_id" => $data['id'],
            "order_number" => $data['order_number'],
            "financial_status" => $data['financial_status'],
            "fulfillment_status" => $data['fulfillment_status'],
            "note" => $data['note'],
            "subtotal_price" => $data['subtotal_price'],
            "discount_code" => json_encode($data['discount_codes']),
            "total_discounts" => $data['total_discounts'],
            //PENDIENTE "total_shipping" => $data['total_shipping_price_set']['shop_money']['amount'],
            "total_tax" => $data['total_tax'],
            "total_price" => $data['total_price'],
            //PENDIENTE "total_refund" => $data['vendor'],
            "contact_email" => $data['contact_email'],
            "customer_id" => $data['customer']['id'],
            "customer_email" => $data['customer']['email'],
            "customer_first_name" => $data['customer']['first_name'],
            "customer_last_name" => $data['customer']['last_name'],
            "customer_company" => (isset($data['customer']['default_address']) ? $data['customer']['default_address']['company'] : '-'),
            "customer_address1" => (isset($data['customer']['default_address']) ? $data['customer']['default_address']['address1'] : '-'),
            "customer_address2" => (isset($data['customer']['default_address']) ? $data['customer']['default_address']['address2'] : '-'),
            "customer_city" => (isset($data['customer']['default_address']) ? $data['customer']['default_address']['city'] : '-'),
            "customer_province" => (isset($data['customer']['default_address']) ? $data['customer']['default_address']['province'] : '-'),
            "customer_country" => (isset($data['customer']['default_address']) ? $data['customer']['default_address']['country'] : '-'),
            "customer_zip" => (isset($data['customer']['default_address']) ? $data['customer']['default_address']['zip'] : '-'),
            "customer_phone" => (isset($data['customer']['default_address']) ? $data['customer']['default_address']['phone'] : '-'),
            "customer_province_code" => (isset($data['customer']['default_address']) ? $data['customer']['default_address']['province_code'] : '-'),
            "customer_country_code" => (isset($data['customer']['default_address']) ? $data['customer']['default_address']['country_code'] : '-'),
            /*
             * "customer_accepts_marketing
             * "orders_count
             * "state
             * "total_spent
             * "verified_email
             */
            "shipping_first_name" => $data['shipping_address']['first_name'],
            "shipping_last_name" => $data['shipping_address']['last_name'],
            "shipping_company" => $data['shipping_address']['company'],
            "shipping_address1" => $data['shipping_address']['address1'],
            "shipping_address2" => $data['shipping_address']['address2'],
            "shipping_city" => $data['shipping_address']['city'],
            "shipping_province" => $data['shipping_address']['province'],
            "shipping_country" => $data['shipping_address']['country'],
            "shipping_province_code" => $data['shipping_address']['province_code'],
            "shipping_country_code" => $data['shipping_address']['country_code'],
            "shipping_zip" => $data['shipping_address']['zip'],
            "shipping_phone" => $data['shipping_address']['phone'],
//            "shipping_name" => $data['shipping_address']['name'],
//            "shipping_latitude" => $data['shipping_address']['latitude'],
//            "shipping_longitude" => $data['shipping_address']['longitude'],
            "gateway" => $data['gateway'],
            "created_at" => $data['created_at'],
            "updated_at" => $data['updated_at'],
            "store_id" => $data['store_id']);

        if (!$this->existeShopifyOrder($data['id'])) {
            $nu = 1;
            $cab = $this->ingresarNuevoShopifyOrder($obj);
        } else {
            $nu = 2;
            $cab = $this->actualizarShopifyOrder($obj);
        }
        if ($cab) {
            $resp = true;
            $linea_numero = 0;
            foreach ($data['line_items'] as $item) {
                $item['shopify_order_id'] = $data['id'];
                $item['linea_numero'] = $linea_numero;
                $linea_numero++;
                $det = $this->persistenciaShopifyOrderItem($item);
                if (!$det[1]) {
                    $resp = false;
                    break;
                }
            }
        } else {
            $resp = false;
        }
        return array($nu, $resp);
    }

    public function persistenciaShopifyOrderItem($data) {
        $obj = array(
            "shopify_order_item_id" => $data['id'],
            "shopify_order_id" => $data['shopify_order_id'],
            "shopify_product_id" => $data['product_id'],
            "shopify_product_variant_id" => $data['variant_id'],
            "title" => $data['title'],
            "variant_title" => $data['variant_title'],
            "name" => $data['name'],
            "quantity" => $data['quantity'],
            "price" => $data['price'],
            "total_discount" => $data['total_discount'],
            "properties" => json_encode($data['properties']),
            "sku" => $data['sku'],
//            "vendor" => $data['vendor'],
//            "fulfillment_service" => $data['fulfillment_service'],
//            "requires_shipping" => $data['requires_shipping'],
//            "taxable" => $data['taxable'],
            "gift_card" => $data['gift_card'],
//            "variant_inventory_management" => $data['variant_inventory_management'],
//            "product_exists" => $data['product_exists'],
//            "fulfillable_quantity" => $data['fulfillable_quantity'],
            "grams" => $data['grams'],
            "linea_numero" => $data['linea_numero'],
            "estado" => ESTADO_ACTIVO);

        if (!$this->existeShopifyOrderItem($data['id'])) {
            return array(1, $this->ingresarNuevoShopifyOrderItem($obj));
        } else {
            return array(2, $this->actualizarShopifyOrderItem($obj));
        }
    }

    public function ingresarNuevoShopifyOrder($datos) {
        $datos["estado"] = ESTADO_ACTIVO;
        return $this->ingresar("ecommerce.shopify_order", $datos);
    }

    public function actualizarShopifyOrder($datos) {
        return $this->actualizar("ecommerce.shopify_order", $datos, "shopify_order_id");
    }

    public function ingresarNuevoShopifyOrderItem($datos) {
        $datos["estado"] = ESTADO_ACTIVO;
        return $this->ingresar("ecommerce.shopify_order_item", $datos);
    }

    public function actualizarShopifyOrderItem($datos) {
        return $this->actualizar("ecommerce.shopify_order_item", $datos, "shopify_order_item_id");
    }

    public function obtenerOrdenesNoConvertidas() {
        $this->paginacion = array("limit" => 500, "offset" => 0, "pagina" => 0);
        $this->db->from('ecommerce.shopify_order o');
        $this->db->where('o.estado', ESTADO_ACTIVO);
        $this->db->order_by('o.order_number', 'DESC');
        return $this->retornarMuchos();
    }

    public function obtenerOrdenes($store_id, $rango_busqueda, $order_number = '', $busqueda = '') {
        $this->db->select('o.*');
        $this->db->from('ecommerce.shopify_order o');
        $this->db->where('store_id', $store_id);

        if (!empty($order_number)) {
            $this->db->like('CAST(o.order_number as TEXT)', $order_number, 'after');
        } else {

            if (!empty($rango_busqueda)) {
                //rango_busqueda espera "dd/mm/YYYY - dd/mm/YYYY"
                $arrRango = explode(" - ", $rango_busqueda);
                $fechaIni = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[0])), 'Y-m-dT00:00:00');
                $fechaFin = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[1])), 'Y-m-dT23:59:59');
                if (sizeof($arrRango) != 2) {
                    //siempre debe de ser 2, si no es un error y vamos a devolver un error
                    return array(false, -1);
                }
                $arrSelect = array('o.created_at > ' => $fechaIni, 'o.created_at < ' => $fechaFin);
                $this->db->where($arrSelect);
//            error_log("rango_busqueda");
//            error_log(print_r($rango_busqueda, true));
            }

            if (!empty($busqueda)) {
                $this->db->like('o.contact_email', $busqueda);
                $this->db->or_like('o.gateway', $busqueda);

                $this->db->or_like('o.customer_email', $busqueda);
                $this->db->or_like('o.customer_first_name', $busqueda);
                $this->db->or_like('o.customer_last_name', $busqueda);
                $this->db->or_like('o.customer_city', $busqueda);
                $this->db->or_like('o.customer_province', $busqueda);

                $this->db->or_like('o.shipping_first_name', $busqueda);
                $this->db->or_like('o.shipping_last_name', $busqueda);
                $this->db->or_like('o.shipping_city', $busqueda);
                $this->db->or_like('o.shipping_province', $busqueda);
            }
        }
        $conteo = $this->retornarConteo();

        $arrOrd = $this->retornarMuchos();
        error_log(print_r($this->db->last_query(), true));
        if ($arrOrd) {
            foreach ($arrOrd as $order_number) {
                $items = $this->obtenerOrdenItems($order_number->shopify_order_id);
//                error_log(print_r($this->db->last_query(), true));
                if (!$items) {
                    $items = array();
                }
                $order_number->items = $items;
            }
        }

        return array($arrOrd, $conteo);
    }

    public function obtenerOrdenItems($idOrden) {
        $this->db->select('oi.shopify_order_item_id as id, oi.*');
        $this->db->from('ecommerce.shopify_order_item oi');
        $this->db->where('shopify_order_id', $idOrden);
        $this->db->order_by('oi.linea_numero', 'ASC');
        return $this->retornarMuchosSinPaginacion();
    }

    /*     * ********************************* */

    public function existeShopifyOrderMigracion($id) {
        $this->db->select('o.*');
        $this->db->from('migracion.shopify_order o');
        $this->db->where('shopify_order_id', $id);

        return $this->retornarUno();
    }

    public function existeShopifyOrderItemsMigracion($id) {
        $this->db->select('oi.*');
        $this->db->from('migracion.shopify_order_item oi');
        $this->db->where('shopify_order_item_id', $id);

        return $this->retornarUno();
    }

    public function obtenerOrdenItemsMigracion($idOrden) {
        $this->db->select('oi.shopify_order_item_id as id, sv.sku_id as sku, oi.*');
        $this->db->from('migracion.shopify_order_item oi');
        $this->db->join('migracion.shopify_variant sv', 'oi.shopify_variant_id = sv.shopify_variant_id', 'left');
        $this->db->where('shopify_order_id', $idOrden);
//        $this->db->order_by('oi.linea_numero', 'ASC');
        return $this->retornarMuchosSinPaginacion();
    }

    public function actualizarShopifyOrderMigracion($datos) {
        return $this->actualizar("migracion.shopify_order", $datos, "shopify_order_id");
    }

    public function obtenerOrdenesNoConvertidasMigracion() {
        $this->paginacion = array("limit" => 10000, "offset" => 0, "pagina" => 0);
        $this->db->from('migracion.shopify_order o');
        $this->db->where('o.estado', ESTADO_ACTIVO);
        $this->db->order_by('o.order_number', 'DESC');
        return $this->retornarMuchos();
    }

}

?>
