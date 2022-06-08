<?php

use yii\helpers\Html;
use app\components\Helper;
use app\models\InformeGastoRindegastos;
use kartik\date\DatePicker;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $searchModel app\models\CursoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Gastos en sistema RindeGastos';
$cantidad_sincronizados = 0;
$cantidad_registros = 0;
$rindeGastosSincronizados = array();
$rindeGastosParaExcel = array();
?>
<div class="curso-index">

    <div class="card card-info">
        <div class="card m-2">
            <?php
            $form = ActiveForm::begin([
                'id' => 'chipax-form',
                'action' => ['sincronizador/rinde-gastos']
            ])
            ?>
            <div class="card-header bg-cyan">
                Parámetros de Búsqueda
                <i class="fa fa-check text-success"></i>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="offset-2"></div>
                    <div class="col-md-4">
                        <h5 class="card-title">Fecha Desde</h5>
                        <p class="card-text">
                            <?php
                            echo DatePicker::widget([
                                'name' => 'fecha_desde',
                                'value' => Helper::backDateFormat($fecha_desde),
                                'options' => ['placeholder' => 'Seleccione fecha desde...', 'class' => 'form-control'],
                                'language' => 'es',
                                'pluginOptions' => [
                                    'format' => 'dd-mm-yyyy',
                                    'todayHighlight' => true
                                ]
                            ]);
                            ?>
                        </p>
                    </div>
                    <div class="col-md-4">
                        <h5 class="card-title">Fecha Hasta</h5>
                        <p class="card-text">
                            <?php
                            echo DatePicker::widget([
                                'name' => 'fecha_hasta',
                                'value' => Helper::backDateFormat($fecha_hasta),
                                'options' => ['placeholder' => 'Seleccione fecha hasta...', 'class' => 'form-control'],
                                'language' => 'es',
                                'pluginOptions' => [
                                    'format' => 'dd-mm-yyyy',
                                    'todayHighlight' => true
                                ]
                            ]);
                            ?>
                        </p>
                    </div>
                    <div class="offset-2"></div>
                </div>
                <div class="row">
                    <div class="offset-2"></div>
                    <div class="col-md-8">
                        <?= Html::submitButton("Buscar <i class='fa fa-search'></i>", ["class" => "btn btn-primary"]) ?>
                        <?php   /*
                        Html::button('Subir DTEs <i class="fa fa-file-upload"></i>', [
                            'class' => 'showModalButton btn btn-success pull-center',
                            'title' => "Subir archivo XML del SII",
                            'value' => \yii\helpers\Url::to(["/modal/upload-dte"]), 'data-toggle' => 'modal', 'data-target' => '#modalvote'
                        ])  */
                        ?>
                        <?=
                        Html::button("Generar Excel <i class='fa fa-file-excel'></i>", ["class" => "btn btn-success", "id" => "syncExcel"])
                        ?>
                        <div class="col-md-4 float-right">
                            <h5>Seleccionar Todos Rinde Gastos</h5>
                            <div class="custom-control custom-switch">
                                <label class="switch">
                                    <input type="checkbox" id="chkRindeGastosAll">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="offset-2"></div>
                </div>
            </div>
            <?php $form->end(); ?>
        </div>

        <div class="card-body">
            <?= app\components\Alert::widget() ?>
            <!--<input type="search" placeholder="" aria-controls="DataTables_Table_0" id="busquedaOculta">-->
            <table class="table table-bordered table-striped">
                <thead>
                    <tr class="bg-info">
                        <th style="text-overflow: ellipsis; width: 250px;">Razón Social</th>
                        <th>Rut Emisor</th>
                        <th>Informe</th>
                        <th style="min-width: 84px !important; width: 9% !important;">Fecha Emisión</th>
                        <th>N° Doc</th>
                        <th>Neto</th>
                        <th style="max-width: 250px !important;">Descripción</th>
                        <th>Faena</th>
                        <th>Algo</th>
                        <th class="sorting_disabled">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $indice = 0;
                    if (count($model) > 0) :
                        foreach ($model as $rinde) :
                            $informe = InformeGastoRindegastos::findOne($rinde->report_id);
                            $nro_informe = isset($informe) ? $informe->numero : $rinde->report_id;
                    ?>
                            <tr>
                                <td style="text-overflow: ellipsis; width: 250px;"><?= $rinde->supplier ?></td>
                                <td><?= $rinde->gastoCompletaRindegastos[0]->rut_proveedor ?></td>
                                <td><?= $nro_informe ?></td>
                                <td style="min-width: 84px !important;" data-sort="<?= Helper::formatToLocalDate($rinde->issue_date) ?>">
                                    <?= Helper::formatToLocalDate($rinde->issue_date) ?></td>
                                <td><?= $rinde->gastoCompletaRindegastos[0]->nro_documento ?></td>
                                <td><?= isset($rinde) ? number_format($rinde->net, 0, ",", ".") : "?" ?></td>
                                <td><?= isset($rinde->note) ? Helper::removeSlashes($rinde->note) : "" ?></td>
                                <td><?= $rinde->gastoCompletaRindegastos[0]->centro_costo_faena ?></td>
                                <td>rinde</td>
                                <td>
                                    <input type="hidden" name="ForExcel[Rindegastos][fecha]" value="<?= $rinde->issue_date ?>" />
                                    <input type="hidden" name="ForExcel[Rindegastos][centro_costo]" value="<?= $rinde->gastoCompletaRindegastos[0]->centro_costo_faena ?>" />
                                    <input type="hidden" name="ForExcel[Rindegastos][cuenta]" value="<?= $rinde->category ?>" />
                                    <input type="hidden" name="ForExcel[Rindegastos][linea_negocio]" value="<?= $rinde->expense_policy_id ?>" />
                                    <input type="hidden" name="ForExcel[Rindegastos][responsable]" value="<?= $rinde->gastoCompletaRindegastos[0]->nombre_quien_rinde ?>" />
                                    <input type="hidden" name="ForExcel[Rindegastos][tipo_documento]" value="<?= $rinde->gastoCompletaRindegastos[0]->tipo_documento ?>" />
                                    <input type="hidden" name="ForExcel[Rindegastos][proveedor]" value="<?= $rinde->supplier ?>" />
                                    <input type="hidden" name="ForExcel[Rindegastos][num_documento]" value="<?= $rinde->gastoCompletaRindegastos[0]->nro_documento ?>" />
                                    <input type="hidden" name="ForExcel[Rindegastos][nro_informe]" value="<?= $nro_informe ?>" />
                                    <input type="hidden" name="ForExcel[Rindegastos][descripcion]" value="<?= $rinde->note ?>" />
                                    <input type="hidden" name="ForExcel[Rindegastos][monto]" value="<?= $rinde->total ?>" />
                                    <input type="hidden" name="ForExcel[Rindegastos][moneda]" value="<?= 1000 ?>" />
                                    <?php
                                    $rindeGastosParaExcel = $rinde;
                                    echo '<div class="custom-control custom-switch" style="padding: 0px !important;">
                                                <label class="switch-sm">
                                                    <input type="checkbox" class="cargaMasiva">
                                                    <span class="slider-sm round"></span>
                                                </label>
                                                <label style="font-weight: normal">Sincronizar Excel</label>
                                            </div>';
                                    ?>
                                </td>
                            </tr>
                    <?php
                        endforeach;
                    endif;
                    ?>
                </tbody>
            </table>
        </div>

        <div class="col-md-12 bg-success">
            Cantidad de Registros <b>Procesados: <?= $cantidad_sincronizados ?></b>
        </div>
        <div class="col-md-12 bg-warning">
            Cantidad de Registros <b>Por Procesar: <?= $cantidad_registros - $cantidad_sincronizados ?></b>
        </div>
    </div>

</div>
<?php
$script = <<< JS
$(document).ready(function() {        
    $("#syncExcel").on("click", function() {
        $(this).attr("disabled", true);
        $("#syncExcel>i").removeClass("fa-file-excel");
        $("#syncExcel>i").addClass("fa-spin");
        $("#syncExcel>i").addClass("fa-spinner");
        
        var excelData = [];
        $(".cargaMasiva:checked").each(function(index, obj) {            
            $($(obj).parents()[2]).each(function(index, input) {
                let rindeGastos = new Object();
                rindeGastos.fecha = $(input).children()[0].value;
                rindeGastos.centro_costo = $(input).children()[1].value;
                rindeGastos.cuenta = $(input).children()[2].value;
                rindeGastos.linea_negocio = $(input).children()[3].value;
                rindeGastos.responsable = $(input).children()[4].value;
                rindeGastos.tipo_documento = $(input).children()[5].value;
                rindeGastos.proveedor = $(input).children()[6].value;
                rindeGastos.num_documento = $(input).children()[7].value;
                rindeGastos.nro_informe = $(input).children()[8].value;
                rindeGastos.descripcion = $(input).children()[9].value;
                rindeGastos.monto = $(input).children()[10].value;
                rindeGastos.moneda = "CLP";
        
                excelData.push(rindeGastos);
            });
        });
        
        $.ajax({
            url: "/sincronizadorsam/web/sincronizador/sincronizar-con-chipax",
            type: "post",
            data: JSON.stringify(excelData),
            dataType: "json",
            success: function (data) {
                $("#syncExcel>i").removeClass("fa-spin");
                $("#syncExcel>i").removeClass("fa-spinner");
                $("#syncExcel>i").addClass("fa-file-excel");
                $("#syncExcel").attr("disabled", false);
                window.open("/sincronizadorsam/web/sincronizador/download-excel", "_blank");
            }
        });
    });
        
    let tabla = $('table').dataTable({  
        "columnDefs": [
//            {   targets: 0, "searchable": true, width: "110px" },
//            {   targets: 1, "searchable": true, width: "100px" },
//            {   targets: 2, "searchable": true, width: "100px" },
//            {   targets: 3, "searchable": true, width: "100px" },
//            {   targets: 4, "searchable": true, width: "100px" },
//            {   targets: 5, "searchable": true, width: "150px" },
//            {   targets: 6, "searchable": true, width: "150px" },
//            {   targets: 7, "searchable": true, width: "150px" },
            {
                "targets": [8],
                //searchable: true,
                "visible": false
            },
//            {   targets: 9, width: "100px" }
        ],
        "pagingType": "simple_numbers",
        "pageLength": 10,
        "order": [[2, "asc"]],
        "selector": '[data-toggle="tooltip"]',
        "container": 'body',
        "language": {
            "processing":    "Procesando...",
            "lengthMenu":    "Mostrar _MENU_ registros",
            "zeroRecords":   "No se encontraron resultados",
            "emptyTable":    "Ningún dato disponible en esta tabla",
            "info":          "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "inforEmpty":     "Mostrando registros del 0 al 0 de un total de 0 registros",
            "infoFiltered":  "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix":   "",
            "search":        "Buscar:",
            "thousands":  ".",
            "loadingRecords": "Cargando...",
            "paginate": {
                "first":    "Primero",
                "last":    "Último",
                "next":    "Siguiente",
                "previus": "Anterior"
            },
            "aria": {
                "sortAscending":  ": Activar para ordenar la columna de manera ascendente",
                "sortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        },
        "fnDrawCallback": function (oSettings) {
            $('[data-toggle="tooltip"]').tooltip();
        }
    });

    $("#chkRinde").click(function(){
        if (this.checked) {
            if ($("#chkSincronizados").is(":checked"))
                $("#chkSincronizados").click();
            if ($("#chkChipax").is(":checked"))
                $("#chkChipax").click();
            tabla.DataTable().columns(8).search("rinde").draw();
        } else {
            tabla.DataTable().columns(8).search("").draw();
        }
    });
    $("#chkRindeGastosAll").click(function(){
        let estado = $("#chkRindeGastosAll").prop("checked");
        if (estado === true && !$("#chkRinde").prop("checked")) {
            $("#chkRinde").click();
        }
        $(".cargaMasiva").each(function(index, obj) {
            $(obj).prop("checked", estado);
        });
    });
        
    // aplicando estilos al add
    $('[data-toggle="tooltip"]').tooltip();
});
JS;
$this->registerJs($script);
?>