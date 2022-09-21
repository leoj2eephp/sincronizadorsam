<?php

use kartik\date\DatePicker;
use kartik\form\ActiveForm;
use kartik\select2\Select2Asset;
use yii\helpers\Html;

Select2Asset::register($this);
?>
<div class="row fila-vehiculos mb-1">
    <?php
    $form = ActiveForm::begin([
        "id" => "sam-modal",
        "action" => "sync-sam-remuneraciones"
    ]);
    ?>
    <div class="col col-sm-8 offset-sm-2">
        <?= $form->field($model, "categoria")->hiddenInput()->label(false) ?>
        <?= $form->field($model, "nombre_proveedor")->hiddenInput()->label(false) ?>
        <?= $form->field($model, "rut_proveedor")->hiddenInput()->label(false) ?>
        <?= $form->field($model, "nro_documento")->hiddenInput()->label(false) ?>
        <?= $form->field($model, "nota")->hiddenInput()->label(false) ?>
        <?= $form->field($model, "fecha")->hiddenInput()->label(false) ?>
        <?= $form->field($model, "tipo_combustible_id")->hiddenInput()->label(false) ?>
        <h5 class="card-title">Fecha Remuneración</h5>
        <p class="card-text">
            <?php
            echo DatePicker::widget([
                'name' => 'fecha',
                'options' => ['placeholder' => 'Seleccione fecha remuneración', 'class' => 'form-control'],
                'language' => 'es',
                'pluginOptions' => [
                    'format' => 'dd-mm-yyyy',
                    'todayHighlight' => true
                ]
            ]);
            ?>
        </p>
        <!-- <label for="vehis" class="card-title">Seleccione Vehículo</label> -->
        <h5 class="card-title pb-2">Seleccione Vehículo</h5>
        <select name="PoliticaGastosForm[vehiculos_seleccionados][]" class="vehiculo select-style" style="background-color: white;" id="vehis">
            <?php
            foreach ($model->vehiculos as $vehi) {
                $tipo = null;
                if (isset($vehi["camionarrendado_id"]) || isset($vehi["camionpropio_id"])) {
                    $tipo = "camion";
                } else if (isset($vehi["equipopropio_id"]) || isset($vehi["equipoarrendado_id"])) {
                    $tipo = "equipo";
                }
                echo "<option value='" . $vehi["vehiculo"] . "' tipo='" . $tipo . "'>" . $vehi["vehiculo"] . "</option>";
            }
            ?>
        </select>

        <!-- <label for="operador" class="card-title">Operador / Chofer</label> -->
        <h5 class="card-title pb-2 pt-3">Operador / Chofer</h5>
        <select name="PoliticaGastosForm[operador_id]" class="vehiculo select-style" style="background-color: white;" id="operador">
        </select>

        <h5 class="card-title pb-2 pt-3">Monto Neto</h5>
        <?= $form->field($model, 'valores_vehiculos[]')->input("number", ["id" => "montoNeto"])->label(false); ?>

        <input type="hidden" id="total" />

        <div class="box-footer col-sm-12">
            <?= Html::button('Sincronizar', [
                'class' => 'btn btn-primary pull-right', 'name' => 'action',
                'value' => 'sync', 'id' => 'sync-remuneracion'
            ]) ?>
        </div>
    </div>
    <?php
    ActiveForm::end();
    ?>
</div>
<?php

foreach ($operadores as $o) {
    $ops[] = $o->toArray();
}
foreach ($choferes as $c) {
    $chfs[] = $c->toArray();
}

$opes = json_encode($ops);
$chofs = json_encode($chfs);

$script = <<< JS

    $(document).ready(function() {
        const listaOperadores = $opes;
        const listaChoferes = $chofs;

        $("#vehis").on("change", function() {
            let tipoVehiculo = vehis.options[this.selectedIndex];
            $("#operador").empty();
            if ($(tipoVehiculo).attr("tipo") == "equipo") {
                operador.innerHTML = "<option value=0>NO ASIGNADO</option>";
                listaOperadores.forEach((ope, index) => {
                    let option = "<option value='" + ope.id + "' rut='" + ope.rut + "' nombre='" + ope.nombre + "'>"
                                    + ope.nombre + " - " + ope.rut + "</option>";
                    operador.innerHTML += option;
                });
            } else if ($(tipoVehiculo).attr("tipo") == "camion") {
                operador.innerHTML = "<option value=0>NO ASIGNADO</option>";
                listaChoferes.forEach((ope, index) => {
                    let option = "<option value='" + ope.id + "' rut='" + ope.rut + "' nombre='" + ope.nombre + "'>"
                                    + ope.nombre + " - " + ope.rut + "</option>";
                    operador.innerHTML += option;
                });
            } else {
                operador.innerHTML = "<option value=0>NO ASIGNADO</option>";
            }
        });

        $("#montoNeto").on("blur", function() {
            $("#total").val($(this).val());
        });

        $("#operador").on("change", function() {
            let opeSelected = this.options[this.selectedIndex];
            $("#politicagastosform-rut_proveedor").val($(opeSelected).attr("rut").toUpperCase());
            $("#politicagastosform-nombre_proveedor").val($(opeSelected).attr("nombre").toUpperCase());
        });
        
        $("#vehis").change();
    });
   
JS;
$this->registerJs($script);
?>