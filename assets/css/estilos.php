<?php header("Content-type: text/css"); ?>
/*
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
*/
/*
    Created on : 11/08/2020, 8:09:36
    Author     : Agrinag
*/

.login-page {
    background-color: white;
}
.no_padding {
    padding: 0 !important;
    margin: 0 !important;
}
.ajustar_altura {
    height: calc(2.25rem + 2px);
    margin-left: 0 !important;
    padding-left: 7px !important;
}
/********************* AJUSTES BOOTSTRAP ***********************/
.select2-selection {
    height: calc(2.25rem + 2px) !important;
    padding: 0.25em !important;
}

.login-page .copyright, .login-page .copyright a {
    color:coral;
}
.login-page .btn-primary{
    background-color: coral;
}
.copyright {
    text-align: center;
    margin: 1 auto;
}

.table{
    margin:0 !important;
}

.table td {
    /*border: none;*/
}
.table th {
    vertical-align: middle;
}
.table .thead-dark th {
    vertical-align: middle;
}
dl {
    margin: 0;
}

.form-control{
    font-size: 0.8rem;
}
/********************* PAGINACION **************************/
/***********************************************************/
ul.pagination {
    display: inline-block;
    padding: 0;
    margin: 0;
}

ul.pagination li {display: inline;}

ul.pagination li .page-item {
    color: black;
    float: left;
    padding: 8px 16px;
    text-decoration: none;
}

ul.pagination li .page-item.active {
    background-color: #4CAF50;
    color: white;
}

ul.pagination li a:hover:not(.active) {background-color: #ddd;}
/***********************************************************/
/***********************************************************/


label {
    text-overflow: ellipsis;
    display: inline-block;
    overflow: hidden;
    white-space: nowrap;
    margin: 0.25em;
}

.form-group {
    padding-top: 0 !important;
    margin-left: 0em;
    margin-right: 0em;
    margin-bottom: 0em !important;
    margin-top: 1em !important;
}

.input-group-prepend {
    margin-left: 7px;
}

/***********************************************************/
/***********************************************************/
.card-body.card-busqueda {
    background-color: gray;
    padding-top: 1em;
}

.input-group-prepend {
    /*width: 3em;*/
}

@media (max-width: 750px) {
    .form-group {
        margin-top: 0 !important;
    }
    .filtro_busqueda_general {
        margin: 1em 0 !important;
    }
}

.filtro_busqueda_general {
    padding-left: 0 !important;
}

.p-2 {
    margin-right: 1.00em;
}


/*******************************************************/
.card-title {
    margin-right: 1em;
}

#modalEdicionOrden .card-title {
    margin-right: 1em;
    width: 90%;
}

/***************************************/
/************ Menu de Navegacion ********/
.nav-sidebar .nav-treeview > .nav-item > .nav-link > .nav-icon {
    padding-left: 0.5em;
}

/************************** CONFIGURACION ******************/
.cmp_configuracion{
    background-color: #73797f;
    margin: 1em auto;
    border: #3300cc solid 1px;
    width: 100%;
    padding-bottom: 1em;
}


/***************** FLOAT BUTTON ********************/

.float{
    position:fixed;
    width:60px;
    height:60px;
    bottom:40px;
    right:40px;
    background-color:#0C9;
    color:#FFF;
    border-radius:50px;
    text-align:center;
    box-shadow: 2px 2px 3px #999;
}

.my-float{
    margin-top:6px;
}

/************************** ***********************/
.input-group-text-modal-edicion {
    text-ajustify-content: end;
}


.color_par{
    background-color: threedface !important;
}
.color_impar{
    background-color: window !important;
}

.color_par_2{
    background-color: slategrey !important;
}
.color_impar_2{
    background-color: bisque !important;
}

.color_par_3{
    background-color: dodgerblue !important;
}
.color_impar_3{
    background-color: darksalmon !important;
}
.color_logistica_par {
    background-color:  #acded4 !important;
}
.color_logistica_impar {
    background-color:  #48897c !important;
}


/*.card-orden-item {
    background-color: lightgrey !important;
}*/

/*.card-orden-item .card-orden-item-header .card-tools{
    float: right;
    margin-right: -.625rem;
}*/



#orden_detalle_item_propiedades{
    /*background-color: #317c5b !important;*/
}
#orden_detalle_item_propiedades .card-header{
    /*background-color: #317c5b !important;*/
}
#det_items .tabla_propiedades td{
    padding-left: 1em;
    padding-top: 0.25em !important;
    padding-bottom: 0.25em !important;
}

/************** orden_card ************/
.info-box {
    padding: 0.25em;
}
.info-box-content{
<!--    box-shadow: 0 0 1px rgba(0,0,0,.125),0 1px 3px rgba(0,0,0,.2);
    border-radius: .25rem;-->
}
.color_tienda_1{
    background-color: #ff776f !important;
    border-color: black;
    border-width: 1px;
    border-style: solid;

}
.color_tienda_2{
    background-color: #b49ea9 !important;
    border-color: black;
    border-width: 1px;
    border-style: solid;

}
.color_tienda_3{
    background-color: #ffffff !important;
    border-color: black;
    border-width: 1px;
    border-style: solid;

}



/******************* ESTADO ORDEN ITEM *****************/
.listo{
    background-color: green !important;
    color: white;
    border-color: black;
    border-width: 1px;
    border-style: solid;
}

.incompleto {
    background-color: #ffe599 !important;
    color: white;
    border-color: black;
    border-width: 1px;
    border-style: solid;
}

.pendiente{
    background-color: purple !important;
    color: white;
    border-color: black;
    border-width: 1px;
    border-style: solid;
}


.orden_item_preparado{
    background-color: #ffe599 !important;
    color: black;
    border-color: black;
    border-width: 1px;
    border-style: solid;
}

.orden_item_terminado{
    background-color: purple !important;
    color: black;
    border-color: black;
    border-width: 1px;
    border-style: solid;
}

.orden_cancelada {
    background-color: black !important;
}
.orden_cancelada *{
    color: white !important;
}

.orden_no_activa {
    background-color: grey !important;
}
.orden_no_activa *{
    color: white !important;
}

.texto-oculto-expandible {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.texto-oculto-expandible:hover{
    overflow: visible;
    white-space: normal;
}

/*
.col-2, .col-3, .col-4, .col-6, .col-12 {
border: 1px solid;
}
*/

.btn-tool {
    color: rgba(100, 0, 100, 0.4);
    margin: unset;
}

.modal-backdrop {
    display:none;
}


/**************SELECT RANGE PICKER*************/
.select2-container--default .select2-selection--single{
    border-radius:unset;
}

span .select2-selection__rendered{
    margin-top: unset !important;
}

.visible_no_editable{
    display: none;
}
.celda_moneda{
    text-align: right;
    padding-right: 5px;
}