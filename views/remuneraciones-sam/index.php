<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\Helper;
use kartik\date\DatePicker;
use yii\widgets\ActiveForm;

$this->title = 'Remuneraciones SAM';
?>
<div class="curso-index">
    <div class="card card-info">
        <div class="card m-2">
            <?php
            $form = ActiveForm::begin([
                'id' => 'chipax-form',
                'action' => ['remuneraciones-sam/index']
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
                                'options' => ["id" => "fecha_desde", 'placeholder' => 'Seleccione fecha desde...', 'class' => 'form-control'],
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
                                'options' => ["id" => "fecha_hasta", 'placeholder' => 'Seleccione fecha hasta...', 'class' => 'form-control'],
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
                    <div class="col-md-4">
                        <h6>Agrupar Por:</h6>
                        <select class="form-control" name="agrupado" id="agrupado">
                            <option value="-1">Sin Agrupación</option>
                            <option value="1" <?= $agrupado == "1" ? "selected" : "" ?>>Máquina</option>
                            <option value="2" <?= $agrupado == "2" ? "selected" : "" ?>>Operador</option>
                            <option value="3" <?= $agrupado == "3" ? "selected" : "" ?>>Centro de Gestión</option>
                            <option value="4" <?= $agrupado == "4" ? "selected" : "" ?>>Centro de Gestión y Máquina</option>
                            <option value="5" <?= $agrupado == "5" ? "selected" : "" ?>>Centro de Gestión y Operador</option>
                            <option value="6" <?= $agrupado == "6" ? "selected" : "" ?>>Operador y Máquina</option>
                        </select>
                    </div>
                    <div class="col-md-4 mt-4 pt-1">
                        <?= Html::submitButton("Buscar <i class='fa fa-search'></i>", ["class" => "btn btn-primary"]) ?>
                        <?=
                        Html::button("Generar Excel <i class='fa fa-file-excel'></i>", ["class" => "btn btn-success", "id" => "syncExcel"])
                        ?>
                        <?=
                        Html::button('Remuneración Manual <i class="fa fa-sync"></i>', [
                            'class' => 'showModalButton btn btn-warning text-white', 'title' => "Agregar Remuneración Manualmente",
                            'id' => 'sync-remu-manual',
                            'value' => Url::to([
                                "/remuneraciones-sam/manual", "tipo" => "remuneracion"
                            ]),
                            'data-toggle' => 'modal', 'data-target' => '#modalvote'
                        ]);
                        ?>
                    </div>
                </div>
            </div>
            <?php $form->end(); ?>
        </div>

        <div class="card-body">
            <?= app\components\Alert::widget() ?>
            <!--<input type="search" placeholder="" aria-controls="DataTables_Table_0" id="busquedaOculta">-->
            <table class="table table-bordered table-striped" id="tabla">
                <thead>
                    <tr class="bg-info">
                        <th>Máquina/Camión</th>
                        <th>Operador/Chofer</th>
                        <th>Centro Gestión</th>
                        <th>Gasto ($)</th>
                        <th>Equipo Camión</th>
                        <th>Operador</th>
                        <th>Chofer</th>
                        <th>ID Máquina</th>
                        <th>ID Faena</th>
                        <th>ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($remuneraciones as $remu) : ?>
                        <tr>
                            <td>
                                <?= $remu->getCamionEquipoNombre() ?>
                            </td>
                            <td><?= $remu->nombre_proveedor ?></td>
                            <td><?= isset($remu->faena) ? $remu->faena->nombre : "" ?></td>
                            <td><?= number_format($remu->neto, 0, ",", ".") ?></td>
                            <td><?= $remu->tipo_equipo_camion ?></td>
                            <td><?= $remu->chofer_id ?></td>
                            <td><?= $remu->operador_id ?></td>
                            <td>
                                <?php
                                if (isset($remu->equipoPropio_id))
                                    echo $remu->equipoPropio_id;
                                else if (isset($remu->equipoArrendado_id))
                                    echo $remu->equipoArrendado_id;
                                else if (isset($remu->camionPropio_id))
                                    echo $remu->camionPropio_id;
                                else if (isset($remu->camionArrendado_id))
                                    echo $remu->camionArrendado_id;
                                ?>
                            </td>
                            <td><?= isset($remu->faena) ? $remu->faena->id : "" ?></td>
                            <td><?= $remu->id ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
<?php
$script = <<< JS
$(document).ready(function() {        
    const tabla = $('#tabla').dataTable({  
        "columnDefs": [
            /* {
                targets: 0,
                className: 'dt-control',
                orderable: false,
                data: null,
                defaultContent: '',
            }, */
           {   targets: 4, visible: false },
           {   targets: 5, visible: false },
           {   targets: 6, visible: false },
           {   targets: 7, visible: false },
           {   targets: 8, visible: false },
           {   targets: 9, visible: false },
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
    /*
    $(tabla).on('click', 'tr', function () {
        var data = tabla.DataTable().row(this).data();
        console.log(data);
        alert('You clicked on ' + data[1] + "'s row");
    });
    */
    // Add event listener for opening and closing details
    $(tabla).on('click', 'tr', function () {
        var tr = $(this).closest('tr');
        var row = tabla.DataTable().row(tr);

        //rowData = $(row.data()).serialize();
        rowData = row.data();
        const agrupado = $("#agrupado").val();
        data = {
            "agrupado": agrupado,
            "fecha_desde": $("#fecha_desde").val(),
            "fecha_hasta": $("#fecha_hasta").val(),
            "data": rowData
        }

        $.ajax({
            url: "/sincronizadorsam/web/remuneraciones-sam/detail",
            type: "post",
            data: JSON.stringify(data),
            dataType: "html",
        })
        .done(function(data) {
            if (row.child.isShown()) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
            } else {
                // Open this row
                row.child($(data).html()).show();
                tr.addClass('shown');
            }
        })
        .fail(function() {
            /* Swal.fire(
                "Sin Datos!",
                "No se encontraron más datos asociados a este registro",
                "warning"
            ); */
        });
    });
    
});
JS;
$this->registerJs($script);
?>