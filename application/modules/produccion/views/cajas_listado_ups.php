<table class="table table-striped">
    <thead>
        <tr>            
            <th>Companyshipto</th>
            <th>contactshipto</th>
            <th>address1shipto</th>
            <th>address2shipto</th>
            <th>address3shipto</th>
            <th>cityshipto</th>
            <th>stateshipto</th>
            <th>zipshipto</th>
            <th>Countryshipto</th>
            <th>phoneshipto</th>
            <th>Reference1</th>
            <th>Reference2</th>
            <th>Quantity</th>
            <th>Item</th>
            <th>ProdDesc</th>
            <th>Length</th>
            <th>width</th>
            <th>height</th>
            <th>WeightKg</th>
            <th>DclValue</th>
            <th>Service</th>
            <th>PkgType</th>
            <th>GenDesc</th>
            <th>Currency</th>
            <th>Origin</th>
            <th>UOM</th>
            <th>TPComp</th>
            <th>TPAttn</th>
            <th>TPAdd1</th>
            <th>TPCity</th>
            <th>TPState</th>
            <th>TPCtry</th>
            <th>TPZip</th>
            <th>TPPhone</th>
            <th>TPAcct</th>
            <th>SatDlv</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($cajas) {
            foreach ($cajas as $caja) {
                ?>
                <tr>                    
                    <td><?= (isset($caja->destinatario_company) ? $caja->destinatario_company : '') . ' ' . $caja->destinatario_nombre . " " . $caja->destinatario_apellido ?></td>
                    <td><?= $caja->destinatario_nombre . " " . $caja->destinatario_apellido ?></td>
                    <td><?= $caja->address_1 ?></td>
                    <td><?= $caja->address_2 ?></td>
                    <td></td>
                    <td><?= $caja->city ?></td>
                    <td><?= $caja->state_code ?></td>
                    <td><?= $caja->zip_code ?></td>
                    <td><?= $caja->country_code ?></td>
                    <td><?= $caja->phone ?></td>
                    <td><?= $caja->store_alias . "-" . $caja->referencia_order_number . "-" . $caja->orden_caja_id ?></td>
                    <td></td>
                    <td><?= 1 ?></td>
                    <td><?= $logistica[$caja->store_id][$caja->grupo]->ups_item ?></td>
                    <td><?= $logistica[$caja->store_id][$caja->grupo]->ups_prod_desc ?></td>
                    <td><?= str_replace(".", ",", $caja->length) ?></td>
                    <td><?= str_replace(".", ",", $caja->width) ?></td>
                    <td><?= str_replace(".", ",", $caja->height) ?></td>
                    <td><?= str_replace(".", ",", $caja->weight) ?></td>
                    <td><?= str_replace(".", ",", $caja->precio) ?></td>
                    <td><?= $logistica[$caja->store_id][$caja->grupo]->ups_service ?> </td>
                    <td><?= $logistica[$caja->store_id][$caja->grupo]->ups_pkg_type ?></td>
                    <td><?= $logistica[$caja->store_id][$caja->grupo]->ups_gen_desc ?></td>
                    <td><?= $logistica[$caja->store_id][$caja->grupo]->ups_currency ?></td>
                    <td><?= $logistica[$caja->store_id][$caja->grupo]->ups_origin ?></td>
                    <td><?= $logistica[$caja->store_id][$caja->grupo]->ups_uom ?></td>
                    <td><?= $logistica[$caja->store_id][$caja->grupo]->ups_tp_comp ?></td>
                    <td><?= $logistica[$caja->store_id][$caja->grupo]->ups_tp_attn ?></td>
                    <td><?= $logistica[$caja->store_id][$caja->grupo]->ups_tp_add1 ?></td>
                    <td><?= $logistica[$caja->store_id][$caja->grupo]->ups_tp_city ?></td>
                    <td><?= $logistica[$caja->store_id][$caja->grupo]->ups_tp_state ?></td>
                    <td><?= $logistica[$caja->store_id][$caja->grupo]->ups_tp_ctry ?></td>
                    <td><?= $logistica[$caja->store_id][$caja->grupo]->ups_tp_zip ?></td>
                    <td><?= $logistica[$caja->store_id][$caja->grupo]->ups_tp_phone ?></td>
                    <td><?= $logistica[$caja->store_id][$caja->grupo]->ups_tp_acct ?></td>
                    <td><?= $logistica[$caja->store_id][$caja->grupo]->ups_sat_dlv ?></td>

                </tr>
                <?php
            }
        }
        ?>
        <tr>
        </tr>
    </tbody>
</table>