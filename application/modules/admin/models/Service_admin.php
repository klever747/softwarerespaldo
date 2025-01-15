<?php

class Service_admin extends My_Model {

    public function ejecutarScript($arr, $txt, $op) {
        $out = "";

        switch ($op) {
            case '1':
                $out = $this->ejecutarScript1($arr, $txt);
                break;
            case '2':
                $out = $this->ejecutarScript2($arr, $txt);
                break;
            case '3':
                $out = $this->ejecutarScript3($arr, $txt);
                break;
            case '4':
                $out = $this->ejecutarScript4($arr, $txt);
                break;
        }

        return $out;
    }

    public function ejecutarScript1($arr, $txt) {
        $sql = "";
        if (is_string($arr['sku_producto'])) {
            /* $sql = "Select ecommerce.update_products_variants(
              1,
              '{$arr['sku_producto']}','{$arr['nombre_producto']}','{$arr['descripcion_producto']}','{$arr['estado_producto']}',
              '','','',
              '$txt'
              ) as actualizar_todo;"; */
            $sql = "Select ecommerce.update_products_variants(
                1,
                '{$arr['sku_producto']}','{$arr['nombre_producto']}','{$arr['descripcion_producto']}','A',
                '','','',
                '$txt'
                ) as actualizar_todo;";
            try {
                $res = @$this->db->query($sql);
                if (!$res) {
                    // si es null
                    $sql = "Error! Parametros incorrectos para el script";
                }
            } catch (Exception $e) {
                $sql = "Error! Parametros incorrectos en el archivo CSV no soportados por el script";
            }
        }
        return $sql;
    }

    public function ejecutarScript2($arr, $txt) {
        $sql = "";
        if (is_string($arr['sku_variante'])) {
            /* $sql = "Select ecommerce.update_products_variants(
              2,
              '','','','',
              '{$arr['sku_variante']}','{$arr['nombre_producto_variante']}','{$arr['estado_variante']}',
              '$txt'
              ) as actualizar_todo;"; */
            $sql = "Select ecommerce.update_products_variants(
                2,
                '','','','',
                '{$arr['sku_variante']}','{$arr['nombre_producto_variante']}','A',
                '$txt'
                ) as actualizar_todo;";
            try {
                $res = @$this->db->query($sql);
                if (!$res) {
                    // si es null
                    $sql = "Error! Parametros incorrectos para el script";
                }
            } catch (Exception $e) {
                $sql = "Error! Parametros incorrectos en el archivo CSV no soportados por el script";
            }
        }
        return $sql;
    }

    public function ejecutarScript3($arr, $txt) {
        $sql = "";
        if (is_string($arr['sku_producto']) && is_string($arr['sku_variante'])) {
            /* $sql = "Select ecommerce.update_products_variants(
              3,
              '{$arr['sku_producto']}','{$arr['nombre_producto']}','{$arr['descripcion_producto']}','{$arr['estado_producto']}',
              '{$arr['sku_variante']}','{$arr['nombre_producto_variante']}','{$arr['estado_variante']}',
              '$txt'
              ) as actualizar_todo;"; */
            $sql = "Select ecommerce.update_products_variants(
                3,
                '{$arr['sku_producto']}','{$arr['nombre_producto']}','{$arr['descripcion_producto']}','A',
                '{$arr['sku_variante']}','{$arr['nombre_producto_variante']}','A',
                '$txt'
                ) as actualizar_todo;";
            try {
                $res = @$this->db->query($sql);
                if (!$res) {
                    // si es null
                    $sql = "Error! Parametros incorrectos para el script";
                }
            } catch (Exception $e) {
                $sql = "Error! Parametros incorrectos en el archivo CSV no soportados por el script";
            }
        }
        return $sql;
    }

    public function ejecutarScript4($arr, $txt) {
        $sql = "";
        if (is_numeric($arr['ingrediente_id'])) {
            /* $sql = "Select produccion.update_ingredients(
              {$arr['ingrediente_id']}, '{$arr['nombre_ingrediente']}','{$arr['descripcion_ingrediente']}','{$arr['estado_ingrediente']}',
              {$arr['costo']}, {$arr['costo_40']}, {$arr['costo_50']}, {$arr['costo_60']}, {$arr['costo_70']},
              {$arr['costo_80']}, {$arr['costo_90']}, {$arr['costo_100']},
              '$txt'
              ) as actualizar_todo;";
             */
            $sql = "Select produccion.update_ingredients(
                {$arr['ingrediente_id']}, '{$arr['nombre_ingrediente']}','{$arr['descripcion_ingrediente']}','A',
                {$arr['costo']}, {$arr['costo_40']}, {$arr['costo_50']},  {$arr['costo_60']}, {$arr['costo_70']}, 
                {$arr['costo_80']}, {$arr['costo_90']}, {$arr['costo_100']},
                '$txt'
                ) as actualizar_todo;";

            try {
                $res = @$this->db->query($sql);
                if (!$res) {
                    // si es null
                    $sql = "Error! Parametros incorrectos para el script";
                }
            } catch (Exception $e) {
                $sql = "Error! Parametros incorrectos en el archivo CSV no soportados por el script";
            }
        }
        return $sql;
    }

    private function query($sql) {
        $dbConnexion = pg_connect("host=127.0.0.1 dbname=agrinag user=postgres password=admin");
        $result = pg_query($dbConnexion, $sql);
        /*
          while ($row = pg_fetch_row($result)) {
          var_dump($row);
          }
         */
        pg_close($dbConnexion);
    }

}
