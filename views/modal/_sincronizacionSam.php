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
        $litrosText = '';
        $litros = '';
        $notaCombustible = '';
        $tipoCombustibleDetectado = '';
        $tipoCombustibleIdSeleccionado = '';
        $isBIDO = false;
        $lector = new \app\models\LectorFactura();
        $htmlFactura = $lector->getHtml($model->nro_documento, $model->rut_proveedor, $model->categoria_id);
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $xpath = null;
        if(isset($htmlFactura) && $htmlFactura != "") {
            $dom->loadHTML($htmlFactura);
            libxml_clear_errors();
            $xpath = new \DOMXPath($dom);
            
            $litrosNode = $xpath->query("//th[contains(text(),'Litros')]/following-sibling::td[1]");
            if ($litrosNode->length > 0) {
                $litrosText = trim($litrosNode->item(0)->textContent);
                preg_match('/\|\s*([\d.]+)\s*\|L/', $litrosText, $matches);
                $litros = isset($matches[1]) ? $matches[1]: '';
            }
            
            if(isset($xpath)) {
                $patenteNode = $xpath->query("//th[contains(text(),'Patente')]/following-sibling::td[1]");
                $patente = $patenteNode->length > 0 ? trim($patenteNode->item(0)->textContent) : '';

                if($patente && strpos($patente, 'BIDO') !== false) {
                    $isBIDO = true;
                }
     
                $notaCombustible = $litrosText . ' - ' . $patente;
            }

            if (strpos($litrosText, 'GASOLINA') !== false) {
                $tipoCombustibleDetectado = 'Bencina';
            } elseif (strpos($litrosText, 'PETROLEO') !== false || strpos($litrosText, 'PETRÓLEO') !== false) {
                $tipoCombustibleDetectado = 'Petróleo';
            }
    
            foreach ($model->tipo_combustibles as $tipoComb) {
                if (stripos($tipoComb['nombre'], $tipoCombustibleDetectado) !== false) {
                    $tipoCombustibleIdSeleccionado = $tipoComb['id'];
                    break;
                }
            }
        }
    ?>

    <h4>Llene el siguiente formulario para sincronizar la información con SAM</h4>

    <div class="row">
        <div class="col col-sm-6" id="form-column" style="border-right: 1px solid #ccc; padding-right: 10px;">
            <?= $form->field($model, "categoria")->hiddenInput()->label(false) ?>
            <?= $form->field($model, "nombre_proveedor")->hiddenInput()->label(false) ?>
            <?= $form->field($model, "rut_proveedor")->hiddenInput()->label(false) ?>
            <?= $form->field($model, "nro_documento")->hiddenInput()->label(false) ?>
            <?= $form->field($model, "tipo_documento_seleccionado")->hiddenInput()->label(false) ?>
            <?= $form->field($model, "nota")->hiddenInput()->label(false) ?>
            <?= $form->field($model, "neto")->hiddenInput(["id" => "montoNeto"])->label(false) ?>
            <?= $form->field($model, "fecha")->hiddenInput()->label(false) ?>
            <input type="hidden" id="indiceTabla" value="<?= $indice ?>" />
            <?php
            $selectedFaenaId = '';
            if (isset($model->linea_negocio) && !empty($model->linea_negocio)) {
                if (strpos($model->linea_negocio, 'Departamento Maquinaria') !== false && strpos($model->categoria, 'Cop') !== false) {
                    foreach ($model->faena as $faena) {
                        if ($faena['nombre'] === 'Taller Central') {
                            $selectedFaenaId = $faena['id'];
                            break;
                        }
                    }
                } else if (strpos($model->linea_negocio, 'Departamento Maquinaria') !== false && strpos($model->categoria, 'Cop') === false) {
                    foreach ($model->faena as $faena) {
                        if ($faena['nombre'] === 'Taller Central Gastos Generales') {
                            $selectedFaenaId = $faena['id'];
                            break;
                        }
                    }
                }
                
                if(!isset($selectedFaenaId) || empty($selectedFaenaId)) {
                    foreach ($model->faena as $faena) {
                        if ($faena['nombre'] === 'Taller Central') {
                            $selectedFaenaId = $faena['id'];
                            break;
                        }
                    }
                }
            }

            // Widget con valor seleccionado
            echo $form->field($model, 'faena_seleccionada')->widget(\kartik\select2\Select2::class, [
                'data' => ArrayHelper::map($model->faena, "id", "nombre"),
                'options' => [
                    'placeholder' => 'Centro de Costo / Faena',
                    'id' => 'faena',
                    'value' => $selectedFaenaId,
                ],
                'theme' => 'default',
                //'size' => 'sm',
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ])->label(false);
            ?>
            <?php if ($isBIDO) { ?>
                <div class="alert alert-info" role="alert">
                    Esto es un Bidón. El llenado debe ser manual en algunos campos
                </div>
            <?php } ?>
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
                    <?php
                    $selectedVehiculo = '';
                    if (isset($patente) && !empty($patente)) {
                        foreach ($model->vehiculos as $vehiculo) {
                            if (strpos($vehiculo['vehiculo'], $patente) !== false) {
                                $selectedVehiculo = $vehiculo['vehiculo'];
                                break;
                            }
                        }
                    }

                    echo \kartik\select2\Select2::widget([
                        'name' => 'PoliticaGastosForm[vehiculos_seleccionados][nombres][]',
                        'data' => ArrayHelper::map($model->vehiculos, 'vehiculo', 'vehiculo'),
                        'value' => $selectedVehiculo,
                        'options' => [
                            'placeholder' => 'Seleccione vehículo',
                            'class' => 'vehiculo',
                            'id' => 'vehis'
                        ],
                        'theme' => 'default',
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]);
                    ?>
                    <input type="text" name="PoliticaGastosForm[vehiculos_seleccionados][notas][]" class="notas select-style bg-white" placeholder="Nota">
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
                    <input type="number" class="form-control valor" name="PoliticaGastosForm[vehiculos_seleccionados][valores][]" placeholder="Valor" value="<?= $model->neto ?>" />
                </div>
                <!-- NUEVO!! -->
                <div class="col col-sm-6">
                    <input type="number" name="PoliticaGastosForm[vehiculos_seleccionados][cantidad][]" class="cantidad select-style bg-white" placeholder="Cantidad">
                </div>
                <div class="col col-sm-6">
                    <select name="PoliticaGastosForm[vehiculos_seleccionados][unidad_seleccionada][]" class="unidad_seleccionada select-style bg-white" placeholder="Unidad">
                        <?php
                        foreach ($model->unidades as $unidad) {
                            echo "<option value='" . $unidad["nombre"] . "'>" . $unidad["nombre"] . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <?php if (
                    array_key_exists($model->categoria_id, app\models\FlujoCajaCartola::CATEGORIAS_COMBUSTIBLES_CHIPAX) || 
                    array_key_exists($model->categoria_id, app\models\FlujoCajaCartola::CATEGORIAS_COMBUSTIBLES_CHIPAX_SPA)
                ) : ?>
                    <div class="col col-sm-6">
                        <select name="PoliticaGastosForm[vehiculos_seleccionados][tipo_combustible_id][]" class="tipo_combustible select-style bg-white" placeholder="Unidad">
                        <?php
                            foreach ($model->tipo_combustibles as $tipoComb) {
                                $selected = ($tipoComb['id'] == $tipoCombustibleIdSeleccionado) ? "selected" : "";
                                echo "<option value='{$tipoComb["id"]}' $selected>{$tipoComb["nombre"]}</option>";
                            }
                        ?>
                        </select>
                    </div>
                    <div class="col col-sm-6">
                        <input type="text" name="PoliticaGastosForm[vehiculos_seleccionados][carguio][]" class="carguio select-style bg-white" placeholder="Carguío">
                    </div>
                <?php else : ?>
                    <input type="hidden" name="PoliticaGastosForm[vehiculos_seleccionados][tipo_combustible_id][]">
                    <input type="hidden" name="PoliticaGastosForm[vehiculos_seleccionados][carguio][]">
                <?php endif; ?>
            </div>
        </div>
        <div class="col col-sm-6">
            <?php
            $lector->print($model->nro_documento, $model->rut_proveedor, $model->categoria_id);
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
        $(".notas").first().val("$notaCombustible");
        $(".cantidad").first().val("$litros");
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
            // Destruir todos los Select2 existentes
            $('.vehiculo').each(function() {
                if ($(this).data('select2')) {
                    $(this).select2('destroy');
                }
            });

            // Obtener el elemento a clonar y clonarlo
            let original = $($(".fila-vehiculos")[0]);
            let newRow = original.clone(true);
            
            // Limpiar el Select2 del elemento clonado
            newRow.find('.select2').remove();
            newRow.find('.vehiculo')
                .removeAttr('data-select2-id')
                .removeAttr('id');
            
            // Agregar la nueva fila al formulario
            newRow.appendTo("#form-column");
            
            // Reinicializar Select2 en TODOS los elementos
            $('.vehiculo').each(function() {
                $(this).select2({
                    placeholder: 'Seleccione vehículo',
                    allowClear: true,
                    theme: 'default',
                    width: '100%'  // Asegurar que el ancho sea correcto
                });
            });
            
            // si hay más de un vehículo, entonces duplico primero, pero cambio el nombre de la clase de la primera fila
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
        });
        
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