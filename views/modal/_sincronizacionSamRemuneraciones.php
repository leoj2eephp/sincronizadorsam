<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<div class="profesional-create">
    <?php
    $form = ActiveForm::begin([
        "id" => "sam-modal",
        "action" => "sync-sam-remuneraciones"
    ]);
    ?>

    <p>
        Llene el siguiente formulario para sincronizar la información con SAM
    </p>

    <div class="row">
        <div class="col col-sm-6" style="border-right: 1px solid #ccc; padding-right: 10px;">
            <?= $form->field($model, "categoria")->hiddenInput()->label(false) ?>
            <?= $form->field($model, "nombre_proveedor")->hiddenInput()->label(false) ?>
            <?= $form->field($model, "rut_proveedor")->hiddenInput()->label(false) ?>
            <?= $form->field($model, "nro_documento")->hiddenInput()->label(false) ?>
            <?= $form->field($model, "nota")->hiddenInput()->label(false) ?>
            <?= $form->field($model, "fecha")->hiddenInput()->label(false) ?>
            <?= $form->field($model, "tipo_combustible_id")->hiddenInput()->label(false) ?>
            <input type="hidden" id="indiceTabla" value="<?= $indice ?>" />
            <!-- EN CASO DE SER REMUNERACIÓN O GASTO SE UTILIZARÁ ESTE FORMULARIO MÁS BREVE -->
            <div class="row">
                <div class="col col-sm-6">
                    <?php
                    if ($model->nombre_proveedor != "Previred") {
                        echo $form->field($model, 'nombre_proveedor')->input("text", ["readonly" => "readonly"]);
                    } else {
                        echo "<h3>Monto Previred</h3>";
                        echo "<h3>$ " . number_format($model->neto, 0, ",", ".") . "</h3>";
                    }
                    ?>
                </div>
                <div class="col col-sm-6">
                    <?= $form->field($model, 'fecha')->input("text", ["readonly" => "readonly"]); ?>
                </div>
            </div>
            <div class="row">
                <div class="col col-sm-6">
                    <?= $form->field($model, 'categoria')->input("text", ["readonly" => "readonly"]); ?>
                </div>
                <div class="col col-sm-6">
                    <?= $form->field($model, 'linea_negocio')->input("text", ["readonly" => "readonly"]); ?>
                </div>
            </div>
            <?= $form->field($model, "neto")->hiddenInput(["id" => "montoNeto"])->label(false) ?>
            <?php // $form->field($model, 'neto')->input("text", ["readonly" => "readonly"]); 
            ?>
        </div>
        <div class="col col-sm-6" id="form-column">
            <?php
            echo $this->render("_menuVehiculos", [
                "model" => $model,
                "form" => $form,
                "operadores" => $operadores, "choferes" => $choferes
            ]);
            ?>
        </div>
    </div>

    <div class="box-footer col-sm-12">
        <?php //Html::submitButton('Reagendar Hora', ['class' => 'btn btn-success pull-left', 'name' => 'action', 'value' => 'reagendar'])         
        ?>
        <?= Html::button('Sincronizar', ['class' => 'btn btn-primary pull-right', 'name' => 'action', 'value' => 'sync', 'id' => 'sync-remuneracion']) ?>
        <span id="spanSubtotal" class="pl-8" style="font-weight: bold;">SUMA SUBTOTALES: $ <?= number_format($model->neto, 0, ",", ".") ?></span>
        <span id="alertaDiferencia" class="text-warning text-bold pl-2 d-none">
            <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
            El Operador parece no ser igual al Proveedor
        </span>
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
        // $("#vehis").select2({dropdownCssClass : 'bigdrop'});
        // $("#operador").select2({dropdownCssClass : 'bigdrop'});
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        let montoNeto = parseInt($("#total").val());
        
        newAction = "";
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
        
            let ultimaPosicion = $(".vehiculo").length - 2;
            $($(".fila-vehiculos").find(".porcentaje")[ultimaPosicion]).removeAttr("readonly");
            $($(".fila-vehiculos").find(".valor")[ultimaPosicion]).removeAttr("readonly");
            $($(".fila-vehiculos").find(".porcentaje")[ultimaPosicion]).val(0);
            $($(".fila-vehiculos").find(".valor")[ultimaPosicion]).val(0);
            $($(".fila-vehiculos").find(".delete-vehiculo")[ultimaPosicion]).css("display", "block");
            refrescarSubtotales();
            refreshSelect2Dataset();
        });
    });
        
JS;
$this->registerJs($script);
?>