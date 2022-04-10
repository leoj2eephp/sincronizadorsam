<div class="row pb-2">
    <div class="col col-sm-6">
        <h4>Vehículos Seleccionados</h4>
    </div>
    <div class="col col-sm-4 centrar-vertical-horizontal">
        <label>Agregar Vehículo</label>
    </div>
    <div class="col col-sm-2 centrar-vertical-horizontal">
        <button type="button" class="addCar btn btn-success"><i class="fas fa-plus" ></i></button>
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
        <select name="PoliticaGastosForm[vehiculos_seleccionados][]" class="vehiculo select-style" style="background-color: white;">
            <?php
            foreach ($model->vehiculos as $vehi) {
                echo "<option value='$vehi->value'>$vehi->value</option>";
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
        <input type="number" class="form-control valor" name="PoliticaGastosForm[valores_vehiculos][]" placeholder="Valor"
               value="<?= $model->neto ?>"/>
    </div>
</div>