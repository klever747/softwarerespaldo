<?php


function item_input($arr) {
    $input = false;
    $dd_js = array();
    if (key_exists('js', $arr)) {
        $dd_js = $arr['js'];
    }
    $dd_js['id'] = $arr['id'];
    $dd_js['class'] = key_exists('clase', $arr) ? $arr['clase'] : '';
    $dd_js['maxlength'] = key_exists('maxlength', $arr) ? $arr['maxlength'] : '';
    $dd_js['aria-describedby'] = "basic-" . $dd_js['id'];

    switch (strtolower($arr['tipo'])) {
        case 'input':
            $input = form_input($arr['name'], $arr['value'], $dd_js);
            break;
        case 'email':
            $dd_js['class'] .= " soloEmail";
            $dd_js['type'] = "email";
            $input = form_input($arr['name'], $arr['value'], $dd_js);
            break;
        case 'number':
            $dd_js['class'] .= " soloNumeros";
            $dd_js['type'] = "number";
            $input = form_input($arr['name'], $arr['value'], $dd_js);          
            $input = str_replace('type="text"', 'type="number"' , $input); //wsanchez implementacion pendiente
            break;
        case 'hidden':
            echo form_hidden($arr['name'], $arr['value']);
            break;
        case 'textarea':
            $dd_js['style'] = "width:90%";
            $input = form_textarea($arr['name'], $arr['value'], $dd_js);
            break;
        case 'select':
            $dd_js['class'] .= " form-control select2";      
            $input = form_dropdown($arr['name'], $arr['sel'], $arr['value'],$dd_js);
            break;
        case 'select_multiple':
            $dd_js['class'] .= " form-control select2"; 
            $input =  form_multiselect($arr['name'].'[]', $arr['sel'], $arr['value'], $dd_js);
            break;
        case 'password':
            $dd_js['type'] = "password";
            $input = form_password($arr['name'], $arr['value'], $dd_js);
            break;
        case 'label':
            $dd_js['readonly'] = 'true';
            $input = form_input($arr['name'], $arr['value'], $dd_js);
            break;
        case 'file':
            $dd_js['type'] = 'file'; 
            $arr_input = array('name' => $arr['name'], 'accept' => 'application/pdf');
            $input = form_upload($arr_input, '', $dd_js);
            break;
        case 'select_disable':
            $dd_js['disabled'] = true;    
            $input = form_dropdown($arr['name'], $arr['sel'], $arr['value'],$dd_js);
            break;
        default:
            break;
    }
    return $input;
}

function linea_formulario_edicion($arr_input) {
    $clase2 = "col-12 col-md-6 col-lg-4";
    if ($arr_input) {
        ?>
        <div class = "form-group row col-6">
            <label for = "nombres" class = "col-12 col-md-3 col-lg-2 flex-shrink-0">Documento</label>
            <?= $arr_input['input'] ?>        
        </div>
        <?php
    }
}

function item_formulario_vertical($arr, $num_items = false) {
    if(array_key_exists('clase', $arr)){
        $arr['clase'] .= ' col-12 col-md-8 col-lg-8';
    }else{
        $arr['clase'] = 'col-12 col-md-8 col-lg-8';
    }
    $arr['dis']='enabled';
    $input = item_input($arr);
    
    if ($input) {
        ?>
        <div class = "form-group col-12 col-lg-6">
            <div class="input-group mb-0">
                <div class="input-group-prepend ajustar_altura col-12 col-md-4 col-lg-4 justify-content-end">
                    <span class="input-group-text-modal-edicion" id="basic-<?= $arr['name'] ?>"><?php
                        if (key_exists('label', $arr)) {
                            $etiqueta = $arr['label'];
                        } else {
                            $etiqueta = "";
                            $arr = explode("_", $arr['name']);
                            foreach ($arr as $a) {
                                $etiqueta .= ucfirst($a) . " ";
                            }
                            if (empty($etiqueta)) {
                                $etiqueta = ucfirst($arr['name']);
                            }
                        }
                        echo $etiqueta;
                        ?>
                    </span>
                </div>
                <?= $input; ?>
            </div>
        </div>
        <?php
    }
}

//function linea_formulario_vertical($arr_input) {
//    $items = sizeof($arr_input);
//
//    if (is_array($arr_input)) {
//        die("es arreglo");
//        foreach ($arr_input as $k => $arr) {
//            item_formulario_vertical($arr, $items);
//        }
//    } else {
//        die("es unico");
//        item_formulario_vertical($arr_input);
//    }
//}
