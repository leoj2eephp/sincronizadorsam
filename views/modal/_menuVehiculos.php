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
        <select name="PoliticaGastosForm[operador_id]" class="vehiculo select-style" style="background-color: white;" id="operador">
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
                    let option = "<option value='" + ope.id + "' rut='" + ope.rut + "'>"
                                    + ope.nombre + " - " + ope.rut + "</option>";
                    operador.innerHTML += option;
                });
            } else if ($(tipoVehiculo).attr("tipo") == "camion") {
                operador.innerHTML = "<option value=0>NO ASIGNADO</option>";
                listaChoferes.forEach((ope, index) => {
                    let option = "<option value='" + ope.id + "' rut='" + ope.rut + "'>"
                                    + ope.nombre + " - " + ope.rut + "</option>";
                    operador.innerHTML += option;
                });
            } else {
                operador.innerHTML = "<option value=0>NO ASIGNADO</option>";
            }
        });
        
        $("#operador").on("change", function() {
            let opeSelected = this.options[this.selectedIndex];
            if ($("#politicagastosform-rut_proveedor").val().toUpperCase() != $(opeSelected).attr("rut").toUpperCase()) {
                $("#alertaDiferencia").removeClass("d-none");
            } else {
                $("#alertaDiferencia").addClass("d-none");
            }
        });

        $("#vehis").change();
    });
   
JS;
$this->registerJs($script);
?>