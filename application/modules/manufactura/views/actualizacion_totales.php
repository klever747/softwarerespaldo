<div class="modal-header">
    <div class="modal-title" id="exampleModalLabel">
        <b>Totales por Variante</b>
    </div>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div> 
<div class="modal-body" style="font-size: 0.75rem">
    <div class="wrapper">
        <div class="row col-12 text-center font-weight-bold">
            <div class="row col-12">
                <div class="col-2"></div>
                <div class="col-2">Total</div>
                <div class="col-2">Listo</div>
                <div class="col-2">Agregar</div>
                <div class="col-2">Restante</div>
                <div class="col-2">Presentacion</div>
                <div class="col-12"><hr></div>
            </div>
        </div>
        <div class="row col-12 text-right" style="<?= (($perfil == PANTALLA_MANUFACTURA) || ($perfil == PANTALLA_PREPARACION)) ? "display:block" : "display:none" ?>">
            <div class="row col-12">
                <div class="col-2 text-left">Bonchado</div>
                <div class="col-2 pr-4"><p id="totalItemsPedidos"><?= $totalItemsPedidos * ($presentacion[3] / $presentacion[0]) ?></p></div>
                <div class="col-2 pr-4"><p id="totalItemsPedidosB"><?= $totalItemsPedidosB * ($presentacion[3] / $presentacion[0]) ?></p></div>
                <div class="col-2 pr-4">
                    <input type="text" class="form-control soloNumeros ingresoPreparado text-right" id="ingresoB" name="ingresoB" placeholder="texto busqueda" value="0" step="1">
                </div>
                <div class="col-2 pr-4"><p id="restanteB" class="text-right">0</p></div>
                <div class="col-2 text-center"><?= $presentacion[1] ?></div>
            </div>
            <div class="row col-12"><hr></div>
        </div>


        <div class="row col-12 text-right" style="<?= (($perfil == PANTALLA_MANUFACTURA) || ($perfil == PANTALLA_TERMINACION)) ? "display:block" : "display:none" ?>">
            <div class="row col-12">
                <div class="col-4 text-left">Vestidos</div>
            </div>
            <div class="row col-12">
                <div class="col-1 offset-1 text-left">Luxury</div>
                <div class="col-2 pr-4"><p id="totalLuxury"><?= $totalLuxury * ($presentacion[3] / $presentacion[0]) ?></p></div>
                <div class="col-2 pr-4"><p id="totalLuxuryV"><?= $totalLuxuryV * ($presentacion[3] / $presentacion[0]) ?></p></div>
                <div class="col-2 pr-4">
                    <input type="text" class="form-control soloNumeros ingresoPreparado text-right" id="ingresoL" name="ingresoB" placeholder="texto busqueda" value="0">
                </div>
                <div class="col-2 pr-4"><p id="restanteL" class="text-right">0</p></div>                
                <div class="col-2 text-center"><?= $presentacion[1] ?></div>
            </div>
            <div class="row col-12">
                <div class="col-1 offset-1 text-left">Standard</div>
                <div class="col-2 pr-4"><p id="totalStandard"><?= $totalStandard * ($presentacion[3] / $presentacion[0]) ?></p></div>
                <div class="col-2 pr-4"><p id="totalStandardV"><?= $totalStandardV * ($presentacion[3] / $presentacion[0]) ?></p></div>
                <div class="col-2 pr-4">
                    <input type="text" class="form-control soloNumeros ingresoPreparado text-right" id="ingresoS" name="ingresoB" placeholder="texto busqueda" value="0">
                </div>
                <div class="col-2 pr-4"><p id="restanteS" class="text-right">0</p></div>
                <div class="col-2 text-center"><?= $presentacion[1] ?></div>
            </div>
            <div class="row col-12">
                <div class="col-1 offset-1 text-left">Sin Wrap</div>
                <div class="col-2 pr-4"><p id="totalSin"><?= $totalSin * ($presentacion[3] / $presentacion[0]) ?></p></div>
                <div class="col-2 pr-4"><p id="totalSinV"><?= $totalSinV * ($presentacion[3] / $presentacion[0]) ?></p></div>
                <div class="col-2 pr-4">
                    <input type="text" class="form-control soloNumeros ingresoPreparado text-right" id="ingresoN" name="ingresoB" placeholder="texto busqueda" value="0">
                </div>
                <div class="col-2 pr-4"><p id="restanteN" class="text-right">0</p></div>
                <div class="col-2 text-center"><?= $presentacion[1] ?></div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary btn-salir-modal">Salir</button>
    <button type="button" class="btn btn-primary btn_actualizar_totales" id="btn_actualizar_totales" data-variante_id='<?= $variante->id ?>'>Actualizar Totales</button>
</div>
