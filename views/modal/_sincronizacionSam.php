<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
?>
<div class="profesional-create">
    <?php
    $form = ActiveForm::begin([
        "id" => "sam-modal",
    ]);
    ?>

    <p>
        Llene el siguiente formulario para sincronizar la información con SAM
    </p>

    <div class="row">
        <div class="col col-sm-6" id="form-column" style="border-right: 1px solid #ccc; padding-right: 10px;">
            <?= $form->field($model, "categoria")->hiddenInput()->label(false) ?>
            <?= $form->field($model, "nombre_proveedor")->hiddenInput()->label(false) ?>
            <?= $form->field($model, "rut_proveedor")->hiddenInput()->label(false) ?>
            <?= $form->field($model, "nro_documento")->hiddenInput()->label(false) ?>
            <?= $form->field($model, "nota")->hiddenInput()->label(false) ?>
            <?= $form->field($model, "neto")->hiddenInput(["id" => "montoNeto"])->label(false) ?>
            <?= $form->field($model, "fecha")->hiddenInput()->label(false) ?>
            <input type="hidden" id="indiceTabla" value="<?= $indice ?>" />
            <?=
            $form->field($model, 'faena_seleccionada')->widget(\kartik\select2\Select2::class, [
                'data' => ArrayHelper::map($model->faena, "id", "nombre"),
                'options' => ['placeholder' => 'Centro de Costo / Faena', "id" => "faena"],
                'theme' => 'default',
                //'size' => 'sm',
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ]);
            ?>
            <?php
            if (array_key_exists($model->categoria_id, app\models\FlujoCajaCartola::CATEGORIAS_COMBUSTIBLES_CHIPAX)) : ?>
                <div class="row">
                    <div class="col col-sm-6">
                        <?= $form->field($model, 'tipo_combustible_id')->widget(\kartik\select2\Select2::class, [
                            'data' => ArrayHelper::map($model->tipo_combustibles, "id", "nombre"),
                            'options' => ['placeholder' => 'Tipo de Combustible', "id" => "tipoCombustible"],
                            'theme' => 'default',
                            //'size' => 'sm',
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ]) ?>
                    </div>
                    <div class="col col-sm-6">
                        <?= $form->field($model, "carguio")->textInput(["type" => "number"]) ?>
                    </div>
                </div>
            <?php
            else :
                echo $form->field($model, "tipo_combustible_id")->hiddenInput()->label(false);
                echo $form->field($model, "carguio")->hiddenInput()->label(false);
            endif;
            ?>
            <?php /*
              $form->field($model, 'nro_documento')->input("text", []);
              $form->field($model, 'rut_proveedor')->input("text", []); */
            ?>
            <div class="row">
                <div class="col col-sm-6">
                    <?=
                    $form->field($model, 'cantidad')->input("number", []);
                    ?>
                </div>
                <div class="col col-sm-6">
                    <?=
                    $form->field($model, 'unidad_seleccionada')->widget(\kartik\select2\Select2::class, [
                        'data' => ArrayHelper::map($model->unidades, "nombre", "nombre"),
                        'options' => ['placeholder' => 'Unidad', "id" => "unidad"],
                        'theme' => 'default',
                        //'size' => 'sm',
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]);
                    ?>
                </div>
            </div>
            <?php /*
              $form->field($model, 'tipo_documento_seleccionado')->widget(\kartik\select2\Select2::classname(), [
              'data' => ArrayHelper::map($model->tipoDocumento, "value", "value"),
              'options' => ['placeholder' => 'Tipo de Documento', "id" => "tipoDocumento"],
              'theme' => 'default',
              //'size' => 'sm',
              'pluginOptions' => [
              'allowClear' => true,
              ],
              ]); */
            ?>
            <?=
            $form->field($model, 'nota')->textarea(['maxlength' => true, "class" => "form-control text-uppercase"])
            ?>
            <div class="row pb-2">
                <div class="col col-sm-6">
                    <h4>Vehículos Seleccionados</h4>
                </div>
                <div class="col col-sm-4 centrar-vertical-horizontal">
                    <label>Agregar Vehículo</label>
                </div>
                <div class="col col-sm-2 centrar-vertical-horizontal">
                    <button type="button" class="addCar btn btn-success"><i class="fas fa-plus"></i></button>
                </div>
            </div>
            <div class="row fila-vehiculos mb-1">
                <div class="col col-sm-6">
                    <?php /*
                      $form->field($model, 'vehiculos_seleccionados[]')->widget(\kartik\select2\Select2::classname(), [
                      'data' => ArrayHelper::map($model->vehiculos, "value", "value"),
                      'options' => ['placeholder' => 'Vehículo o Equipo', "class" => "vehiculo"],
                      'theme' => 'default',
                      //'size' => 'sm',
                      'pluginOptions' => [
                      'allowClear' => true,
                      ],
                      ])->label(false); */
                    ?>
                    <select name="PoliticaGastosForm[vehiculos_seleccionados][]" class="vehiculo select-style bg-white" id="vehis">
                        <?php
                        foreach ($model->vehiculos as $vehi) {
                            echo "<option value='" . $vehi["vehiculo"] . "'>" . $vehi["vehiculo"] . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col col-sm-6">
                    <div class="row">
                        <div class="col col-sm-9">
                            <div class="input-group">
                                <input type="number" class="form-control porcentaje" min="1" max="100" placeholder="Porcentaje" />
                                <div class="input-group-append">
                                    <span class="input-group-text" id="basic-addon2">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col col-sm-3">
                            <button type="button" class="delete-vehiculo btn btn-danger" style="display: none;">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <input type="number" class="form-control valor" name="PoliticaGastosForm[valores_vehiculos][]" placeholder="Valor" value="<?= $model->neto ?>" />
                </div>
            </div>
        </div>
        <div class="col col-sm-6">
            <?php
            $lector = new \app\models\LectorFactura();
            $lector->print($model->nro_documento, $model->rut_proveedor);
            $model->html_factura = $lector->output;
            ?>
            <?= $form->field($model, "html_factura")->hiddenInput()->label(false) ?>
        </div>
    </div>

    <div class="box-footer col-sm-12">
        <?php //Html::submitButton('Reagendar Hora', ['class' => 'btn btn-success pull-left', 'name' => 'action', 'value' => 'reagendar'])      
        ?>
        <?= Html::button('Sincronizar', ['class' => 'btn btn-primary pull-right', 'name' => 'action', 'value' => 'sync', 'id' => 'sync']) ?>
        <span id="spanSubtotal" class="pl-8" style="font-weight: bold;">SUMA SUBTOTALES: $ <?= number_format($model->neto, 0, ",", ".") ?></span>
        <span id="spanTotal" class="pl-8 float-right" style="font-weight: bold;">MONTO TOTAL: $ <?= number_format($model->neto, 0, ",", ".") ?></span>
        <input type="hidden" id="total" value="<?= $model->neto ?>" />
        <label id="errorMsg" style="color: red;"></label>
    </div>
    <?php
    ActiveForm::end();
    ?>
</div>
<?php
$script = <<< JS
        
    $(document).ready(function() {
        //$("#vehis").select2({dropdownCssClass : 'bigdrop'});
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        let montoNeto = parseInt($("#total").val());
        
        action = $("#sam-modal").attr("action");
        argumentsIndex = action.indexOf("?");
        if (argumentsIndex != -1) {
            newAction = action.substring(0, argumentsIndex);
        }
        
        $("#sam-modal").attr("action", newAction);
        $(".porcentaje").val(100);
        $(".porcentaje").attr("readonly", "readonly");
        $(".valor").val(montoNeto);
        $(".valor").attr("readonly", "readonly");
        
        $(".addCar").on("click", function() {
            $($(".fila-vehiculos")[0]).clone().appendTo("#form-column");
            // si hay más de un vehículo, entonces duplico primero, pero cambio el nombre de la clase de la primera fila
            // para dejarla intocable.. así evito estar agregando y quitando readonly para la primera fila a cada rato
            if ($(".primera-fila-vehiculos").length == 0) {
                $($(".fila-vehiculos")[0]).addClass("primera-fila-vehiculos");
                $($(".fila-vehiculos")[0]).removeClass("fila-vehiculos");
            }
        
            $(".fila-vehiculos").find(".porcentaje").removeAttr("readonly");
            $(".fila-vehiculos").find(".valor").removeAttr("readonly");
            $(".fila-vehiculos").find(".porcentaje").val(0);
            $(".fila-vehiculos").find(".valor").val(0);
            $(".fila-vehiculos").find(".delete-vehiculo").css("display", "block");
            refrescarSubtotales();
            refreshSelect2Dataset();
        });
        
        function refreshSelect2Dataset() {
            $(".vehiculo").each(function(index, obj) {
                let dataKrajee = eval($(obj).data('krajee-select2'));
                $(obj).attr("id", "id_" + index);
        
                delete obj.dataset.select2Id;
                obj.dataset.select2Id = "id_" + index;
        
                //$(obj).select2(dataKrajee); Esta línea estaba provocando un error al clonar más de 1 vez
            });
        }
        
        $(document).on("change", ".porcentaje", function() {
            let porcentaje = $(this).val();
        
            total = (montoNeto * porcentaje) / 100;
            // Se cambia esta línea, porque se agregaron más estilos y elementos, lo que hacía inútil el uso de next()
            //$(this).next(".valor").val(Math.round(total));
            $(this).closest(".row").next(".valor").val(Math.round(total));
        
            refrescarPrimerPorcentaje();
            calcularTotal();
        });
        
        $(document).on("change", ".valor", function() {
            let valor = $(this).val();
            
            let porcentaje = (valor * 100) / montoNeto;
            console.log(porcentaje);
            //$(this).prev(".porcentaje").val(Math.round(porcentaje));
            $(this).closest(".row").find(".porcentaje").val(Math.round(porcentaje));
        
            refrescarPrimerPorcentaje();
            calcularTotal();
        });
        
        $(document).on("click", ".delete-vehiculo", function() {
            $(this).closest("div.row.fila-vehiculos").remove();
            if ($(".fila-vehiculos").length == 0) {
                $(".primera-fila-vehiculos").find(".porcentaje").val(100);
                $(".primera-fila-vehiculos").find(".valor").val($("#total").val());
        
                $($(".primera-fila-vehiculos")[0]).addClass("fila-vehiculos");
                $($(".primera-fila-vehiculos")[0]).removeClass("primera-fila-vehiculos");
            } else {
                refrescarPrimerPorcentaje();
            }
        });
        
        function refrescarPrimerValor() {
            if ($(".primera-fila-vehiculos").length == 1) {
                var subtotal = 0;
                $(".fila-vehiculos").find(".valor").each(function(index, obj) {
                    subtotal += parseInt($(obj).val());
                });
        
                $(".primera-fila-vehiculos").find(".valor").val(montoNeto - subtotal);
            }
        }
        
        function refrescarPrimerPorcentaje() {
            if ($(".primera-fila-vehiculos").length == 1) {
                var subtotal = 0;
                $(".fila-vehiculos").find(".porcentaje").each(function(index, obj) {
                    subtotal += parseInt($(obj).val());
                });
        
                $(".primera-fila-vehiculos").find(".porcentaje").val(100 - subtotal);
                refrescarPrimerValor();
            }
        }
        
        function refrescarSubtotales() {
            let cantidadVehiculos = $(".vehiculo").length;
            let subNetos = Math.trunc(montoNeto / cantidadVehiculos);
        
            $(".fila-vehiculos").find(".valor").val(subNetos);
            let restanteDivision = parseInt(montoNeto - (subNetos * cantidadVehiculos));
            $(".primera-fila-vehiculos").find(".valor").val(subNetos + restanteDivision);
        
            let porcentajeDividido = Math.trunc(100 / cantidadVehiculos);
            $(".fila-vehiculos").find(".porcentaje").val(porcentajeDividido);
            let sumaPorcentajes = porcentajeDividido * (cantidadVehiculos - 1);
            $(".primera-fila-vehiculos").find(".porcentaje").val(100 - sumaPorcentajes);
        
            calcularTotal();
        }
        
        function calcularTotal() {
            let total = 0;
            $(".valor").each(function(index, obj) {
                total += parseInt($(obj).val());
            });
        
            $("#spanTotal").html("TOTAL: $ " + total.toLocaleString('de-DE', { style: 'currency', currency: 'CLP' }));
            $("#total").val(total);
            validaTotal();
        }
        /*
        $("#total").on("DOMSubtreeModified", function() {
            if (!validaTotal()) {
                $("#total").css("color", "red");
            } else {
                $("#total").css("color", "black");
            }
        });
        */
    });
        
JS;
$this->registerJs($script);
?>