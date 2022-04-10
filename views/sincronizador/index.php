<?php

use yii\helpers\Html;
use app\components\Helper;
use app\models\GastoCompleta;
use app\models\InformeGasto;
use kartik\date\DatePicker;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $searchModel app\models\CursoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Gastos en sistema Chipax';
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
                'action' => ['sincronizador/index']
            ])
            ?>
            <div class="card-header bg-cyan">
                Parámetros de Búsqueda
                <i class="fa fa-check text-success"></i>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
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
                    <div class="col-md-6">
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
                    <div class="col-md-2">
                        <h5>Solo sincronizados</h5>
                        <div class="custom-control custom-switch">
                            <label class="switch">
                                <input type="checkbox" id="chkSincronizados">
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <h5>Solo Chipax</h5>
                        <div class="custom-control custom-switch">
                            <label class="switch">
                                <input type="checkbox" id="chkChipax">
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <h5>Solo RindeGastos</h5>
                        <div class="custom-control custom-switch">
                            <label class="switch">
                                <input type="checkbox" id="chkRinde">
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <?= Html::submitButton("Buscar <i class='fa fa-search'></i>", ["class" => "btn btn-primary"]) ?>
                        <?=
                        Html::button('Subir DTEs <i class="fa fa-file-upload"></i>', [
                            'class' => 'showModalButton btn btn-success pull-center',
                            'title' => "Subir archivo XML del SII",
                            'value' => \yii\helpers\Url::to(["/modal/upload-dte"]), 'data-toggle' => 'modal', 'data-target' => '#modalvote'
                        ])
                        ?>
                        <?=
                        Html::button("Generar Excel <i class='fa fa-file-excel'></i>", ["class" => "btn btn-success", "id" => "syncExcel"])
                        ?>
                        <div class="col-md-5 float-right">
                            <h5>Seleccionar Todos Rinde Gastos</h5>
                            <div class="custom-control custom-switch">
                                <label class="switch">
                                    <input type="checkbox" id="chkRindeGastosAll">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <!--<button type="submit" class="btn btn-primary">Buscar <i class="fa fa-search"></i></button>-->
                    </div>
                </div>
            </div>
            <?php ActiveForm::end() ?>
        </div>

        <div class="card-body">
            <?= app\components\Alert::widget() ?>
            <p>
                <?php // Html::a('Crear Curso', ['create'], ['class' => 'btn btn-success'])     
                ?>
            </p>

            <!--<input type="search" placeholder="" aria-controls="DataTables_Table_0" id="busquedaOculta">-->
            <table class="table table-bordered table-striped">
                <thead>
                    <tr class="bg-info">
                        <th style="text-overflow: ellipsis; width: 250px;">Razón Social</th>
                        <th style="">Rut Emisor</th>
                        <th style="">Folio</th>
                        <th style="min-width: 84px !important; width: 9% !important;">Fecha Emisión</th>
                        <th style="">N° Doc</th>
                        <th style="">Neto</th>
                        <th style="max-width: 250px !important;">Descripción</th>
                        <th style="">Tipo Movimiento</th>
                        <th style="">Algo</th>
                        <th class="sorting_disabled">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $indice = 0;
                    if (count($model->compras) > 0) {
                        foreach ($model->compras as $compra) :
                            $rindeSincronizado = GastoCompleta::isSincronizedWithChipax($compra->folio, $compra->fecha_emision);
                            /* if (count($rindeSincronizado) == 0) {
                                        $rindeSincronizado = app\models\RindeGastos::getCombustibleExpenseByNumDoc($combustibles, trim($compra->folio));
                                    } */
                            $mostrado = array();
                            foreach ($compra->prorrataChipax as $p) :
                                $cantidad_registros++;
                                $color = "bg-info-light";
                    ?>
                                <tr <?php
                                    if (count($rindeSincronizado) > 0) {
                                        $compra->sincronizado = true;
                                        $rindeGastosSincronizados[] = $rindeSincronizado[0]->nro_documento;
                                        $cantidad_sincronizados++;
                                        echo 'data-toggle="tooltip" data-html="true"
                                                    title="' . "<div class='bg-info text-uppercase text-bold'>" . $rindeSincronizado[0]->gasto->supplier .
                                            ' (' . $rindeSincronizado[0]->rut_proveedor . ')</div>';
                                        $total_montos = 0;  // esto es solo para los casos en los que Chipax tiene desglosado un registro que es único en RindeGastos
                                        foreach ($rindeSincronizado as $rinde) :
                                            $total_montos += $rinde->gasto->net;
                                        endforeach;
                                        foreach ($rindeSincronizado as $i => $rinde) :
                                            if ($rinde->gasto->net == $p->monto || $rinde->gasto->total == $p->monto || $rinde->gasto->net == $total_montos) {
                                                $color = "bg-info-light";
                                                $css_totales = "text-info font-weight-bold";
                                            } else {
                                                $color = "bg-warning";
                                                $css_totales = "text-danger font-weight-bold";
                                            }
                                            if (!isset($mostrado[$i])) {    // esto para los casos en que haya más de un gasto asociado a un mismo folio..
                                                echo '<div><b>Fecha: </b>' . Helper::formatToLocalDate($rinde->gasto->issue_date) . '</div>
                                                <div class=' . "'" . $css_totales . "'" . '><b>Neto: </b>' . number_format($rinde->gasto->net, 0, ",", ".") . '</div>
                                                <div class=' . "'" . $css_totales . "'" . '><b>Total: </b>' . number_format($rinde->gasto->total, 0, ",", ".") . '</div>
                                                <div><b>Categoría: </b>' . $rinde->gasto->category . '</div>
                                                <div><b>Nota: </b>' . Helper::removeSlashes($rinde->gasto->note) . '</div>
                                                <div><b>Centro de Costo: </b>' . $rinde->centro_costo_faena . '</div>
                                                <div><b>Vehículo: </b>' . $rinde->vehiculo_equipo . '</div>
                                                <br />';
                                            } else {
                                                continue;   // esto para los casos en que haya más de un gasto asociado a un mismo folio..
                                            }

                                            if ($rinde->gasto->net != $total_montos) { // cuando hay desglose en Chipax, que es solo uno para RindeGastos
                                                $mostrado[$i] = $rinde->gasto->net;
                                            }
                                            break;   // esto para los casos en que haya más de un gasto asociado a un mismo folio..
                                        endforeach;
                                        echo '"';
                                        echo ' class="' . $color . '"';
                                    }
                                    ?>>
                                    <td style="text-overflow: ellipsis; width: 250px;"><?= $compra->razon_social ?></td>
                                    <td><?= $compra->rut_emisor ?></td>
                                    <td></td>
                                    <td style="min-width: 84px !important;" data-sort="<?= Helper::formatToLocalDate($compra->fecha_emision) ?>">
                                        <?= Helper::formatToLocalDate($compra->fecha_emision) ?></td>
                                    <td><?= $compra->folio ?></td>
                                    <td><?= isset($p) ? number_format($p->monto, 0, ",", ".") : "?" ?></td>
                                    <td><?= isset($model->descripcion) ? $model->descripcion . ' (' . $compra->razon_social . ')' : "" ?></td>
                                    <td>Compra</td>
                                    <td><?= $compra->sincronizado ? "sync" : "mogli" ?></td>
                                    <!--<td><? $color === "bg-warning" ? '<a href="#"><i class="fa fa-sync"></i></a>' : '' ?></td>-->
                                    <td><?php
                                        if ($compra->sincronizado) {
                                            echo "";
                                        } else {
                                            echo Html::button('<i class="fa fa-sync"></i>', [
                                                'class' => 'showModalButton btn btn-sm btn-primary',
                                                'title' => "Sincronizar con SAM", "id" => "sync_" . $indice,
                                                'value' => Url::to([
                                                    "/modal/sync-sam", "id" => $compra->id, "tipo" => "compra", "i" => $indice
                                                ]),
                                                'data-toggle' => 'modal', 'data-target' => '#modalvote'
                                            ]);
                                            //                                                        \yii\helpers\Html::a('<i class="fa fa-sync"></i>', ["modal/sync-sam"],
                                            //                                                                ['title' => 'Sincronizar con SAM', 'data-pjax' => '0', "class" => "showModalButton"]) : ''
                                        }
                                        ?>
                                    </td>
                                </tr>
                        <?php
                                $indice++;
                            endforeach;
                        endforeach;
                        ?>
                        <?php
                    }
                    if (count($model->gastos) > 0) {
                        foreach ($model->gastos as $gastos) :
                            $rindeSincronizado = GastoCompleta::isSincronizedWithChipax($gastos->num_documento, $gastos->fecha);
                            /* if (count($rindeSincronizado) == 0) {
                                        $rindeSincronizado = app\models\RindeGastos::getCombustibleExpenseByNumDoc($combustibles, trim($gastos->num_documento));
                                    } */
                            $gastoMostrado = array();
                            foreach ($gastos->prorrataChipax as $p) :
                                $cantidad_registros++;
                                $color = "bg-info-light";
                        ?>
                                <tr <?php
                                    if (count($rindeSincronizado) > 0) {
                                        $gastos->sincronizado = true;
                                        $rindeGastosSincronizados[] = $rindeSincronizado[0]->nro_documento;
                                        $cantidad_sincronizados++;
                                        foreach ($rindeSincronizado as $i => $rinde) :
                                            if ($rinde->gasto->net == $p->monto || $rinde->gasto->total == $p->monto) {
                                                $color = "bg-info-light";
                                                $css_totales = "text-info font-weight-bold";
                                            } else {
                                                $color = "bg-warning";
                                                $css_totales = "text-danger font-weight-bold";
                                            }

                                            if (!isset($gastoMostrado[$i])) {    // esto para los casos en que haya más de un gasto asociado a un mismo folio..
                                                echo 'data-toggle="tooltip" data-html="true"
                                                    title="' . "<div class='bg-info text-uppercase text-bold'>" . $rinde->gasto->supplier . ' (' . $rinde->rut_proveedor . ')</div>
                                                        <div><b>Fecha: </b>' . Helper::formatToLocalDate($rinde->gasto->issue_date) . '</div>
                                                        <div class=' . "'" . $css_totales . "'" . '><b>Neto: </b>' . number_format($rinde->gasto->net, 0, ",", ".") . '</div>
                                                        <div class=' . "'" . $css_totales . "'" . '><b>Total: </b>' . number_format($rinde->gasto->total, 0, ",", ".") . '</div>
                                                        <div><b>Categoría: </b>' . $rinde->gasto->category . '</div>
                                                        <div><b>Nota: </b>' . Helper::removeSlashes($rinde->gasto->note) . '</div>
                                                        <div><b>Centro de Costo: </b>' . $rinde->centro_costo_faena . '</div>
                                                        <div><b>Vehículo: </b>' . $rinde->vehiculo_equipo . '</div>
                                                        <br />';
                                            } else {
                                                continue;   // esto para los casos en que haya más de un gasto asociado a un mismo folio..
                                            }

                                            $gastoMostrado[$i] = true;
                                            break;   // esto para los casos en que haya más de un gasto asociado a un mismo folio..
                                        endforeach;
                                        echo '"';
                                        echo ' class="' . $color . '"';
                                    }
                                    ?>>
                                    <td style="text-overflow: ellipsis; width: 250px;"><?= $gastos->proveedor ?></td>
                                    <td><?= isset($gastos->proveedor) ? $gastos->proveedor : "" ?></td>
                                    <td></td>
                                    <td style="min-width: 84px !important;" data-sort="<?= Helper::formatToLocalDate($gastos->fecha) ?>">
                                        <?= Helper::formatToLocalDate($gastos->fecha) ?></td>
                                    <td><?= $gastos->num_documento ?></td>
                                    <td><?= isset($p) ? number_format($p->monto, 0, ",", ".") : "?" ?></td>
                                    <td><?= isset($gastos->descripcion) ? $gastos->descripcion : "" ?></td>
                                    <td>Gasto</td>
                                    <td><?= $gastos->sincronizado ? "sync" : "mogli" ?></td>
                                    <!--                                            <td><? $color === "bg-warning" ? '<a href="#"><i class="fa fa-sync"></i></a>' : '' ?></td>-->
                                    <td><?php
                                        if ($gastos->sincronizado) {
                                            echo "";
                                        } else {
                                            //echo !\app\models\SamSincro::isSamSincronizedFolio($gastos->num_documento, $gastos->proveedor) ?
                                            echo true ?
                                                Html::button('<i class="fa fa-sync"></i>', [
                                                    'class' => 'showModalButton btn btn-sm btn-primary',
                                                    'title' => "Sincronizar con SAM", 'id' => 'sync_' . $indice,
                                                    'value' => \yii\helpers\Url::to([
                                                        "/modal/sync-sam", "prorrata" => $p, "model" => $model,
                                                        "gasto" => $gastos, "descripcion" => $gastos->descripcion, "i" => $indice
                                                    ]),
                                                    'data-toggle' => 'modal', 'data-target' => '#modalvote'
                                                ]) : "";
                                            //                                                        \yii\helpers\Html::a('<i class="fa fa-sync"></i>', ["modal/sync-sam"],
                                            //                                                                ['title' => 'Sincronizar con SAM', 'data-pjax' => '0', "class" => "showModalButton"]) : ''
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php
                                $indice++;
                            endforeach;
                        endforeach;
                    }
                    if (count($model->honorarios) > 0) {
                        foreach ($model->honorarios as $honorarios) :
                            $rindeSincronizado = GastoCompleta::isSincronizedWithChipax($honorarios->numero_boleta, $honorarios->fecha_emision);
                            $mostrado = array();
                            foreach ($honorarios->prorrataChipax as $p) :
                                $color = "bg-info-light";
                                $cantidad_registros++;
                            ?>
                                <tr <?php
                                    if (count($rindeSincronizado) > 0) {
                                        $honorarios->sincronizado = true;
                                        $rindeGastosSincronizados[] = $rindeSincronizado[0]->nro_documento;
                                        $cantidad_sincronizados++;
                                        // Filtrar aquí si ha habido algún cambio
                                        foreach ($rindeSincronizado as $i => $rinde) :
                                            if ($rinde->gasto->net == $p->monto || $rinde->gasto->total == $p->monto) {
                                                $color = "bg-info-light";
                                                $css_totales = "text-info font-weight-bold";
                                            } else {
                                                $color = "bg-warning";
                                                $css_totales = "text-danger font-weight-bold";
                                            }
                                            if (!isset($mostrado[$i])) {    // esto para los casos en que haya más de un gasto asociado a un mismo folio..
                                                echo 'data-toggle="tooltip" data-html="true"
                                                    title="' . "<div class='bg-info text-uppercase text-bold'>" . $rinde->gasto->supplier . ' (' . $rinde->gasto->issue_date . ')</div>
                                                        <div><b>Fecha: </b>' . Helper::formatToLocalDate($rinde->gasto->issue_date) . '</div>
                                                        <div class=' . "'" . $css_totales . "'" . '><b>Neto: </b>' . number_format($rinde->gasto->net, 0, ",", ".") . '</div>
                                                        <div class=' . "'" . $css_totales . "'" . '><b>Total: </b>' . number_format($rinde->gasto->total, 0, ",", ".") . '</div>
                                                        <div><b>Categoría: </b>' . $rinde->gasto->category . '</div>
                                                        <div><b>Nota: </b>' . Helper::removeSlashes($rinde->gasto->note) . '</div>
                                                        <div><b>Centro de Costo: </b>' . $rinde->centro_costo_faena . '</div>
                                                        <div><b>Vehículo: </b>' . $rinde->vehiculo_equipo . '</div>
                                                        <br />';
                                            } else {
                                                continue;   // esto para los casos en que haya más de un gasto asociado a un mismo folio..
                                            }

                                            $mostrado[$i] = true;
                                            break;   // esto para los casos en que haya más de un gasto asociado a un mismo folio..
                                        endforeach;
                                        echo '"';
                                        echo ' class="' . $color . '"';
                                    }
                                    ?>>
                                    <td style="text-overflow: ellipsis; width: 250px;"><?= $honorarios->nombre_emisor ?></td>
                                    <td><?= $honorarios->rut_emisor ?></td>
                                    <td></td>
                                    <td style="min-width: 84px !important;" data-sort="<?= Helper::formatToLocalDate($honorarios->fecha_emision) ?>">
                                        <?= Helper::formatToLocalDate($honorarios->fecha_emision) ?></td>
                                    <td><?= $honorarios->numero_boleta ?></td>
                                    <td><?= isset($p) ? number_format($p->monto, 0, ",", ".") : "?" ?></td>
                                    <td><?= isset($model->descripcion) ? $model->descripcion : "" ?></td>
                                    <td>Honorarios</td>
                                    <td><?= $honorarios->sincronizado ? "sync" : "mogli" ?></td>
                                    <!--<td><? $color === "bg-warning" ? '<a href="#"><i class="fa fa-sync"></i></a>' : '' ?></td>-->
                                    <td><?php
                                        if ($honorarios->sincronizado) {
                                            echo "";
                                        } else {
                                            //echo !\app\models\SamSincro::isSamSincronizedFolio($honorarios->numero_boleta, $honorarios->nombre_emisor) ?
                                            echo true ?
                                                Html::button('<i class="fa fa-sync"></i>', [
                                                    'class' => 'showModalButton btn btn-sm btn-primary', 'title' => "Sincronizar con SAM",
                                                    'value' => \yii\helpers\Url::to([
                                                        "/modal/sync-sam", "prorrata" => $p,
                                                        "descripcion" => $model->descripcion, "honorario" => $honorarios, "i" => $indice
                                                    ]),
                                                    'data-toggle' => 'modal', 'data-target' => '#modalvote'
                                                ]) : "";
                                            //                                                        \yii\helpers\Html::a('<i class="fa fa-sync"></i>', ["modal/sync-sam"],
                                            //                                                                ['title' => 'Sincronizar con SAM', 'data-pjax' => '0', "class" => "showModalButton"]) : ''
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php
                                $indice++;
                            endforeach;
                        endforeach;
                    }
                    if (count($model->remuneracions) > 0) {
                        foreach ($model->remuneracions as $remuneraciones) :
                            //$rindeSincronizado = app\models\RindeGastos::getExpenseByNumDoc($rindeGastos, trim($remuneraciones->numero_boleta));
                            $mostrado = array();
                            foreach ($remuneraciones->prorrataChipax as $p) :
                                $color = "bg-info-light";
                                $cantidad_registros++;
                            ?>
                                <tr>
                                    <td style="text-overflow: ellipsis; width: 250px;">
                                        <?= $remuneraciones->nombre_empleado . ' ' . $remuneraciones->apellido_empleado ?>
                                    </td>
                                    <td><?= $remuneraciones->rut_empleado ?></td>
                                    <td></td>
                                    <td style="min-width: 84px !important;" data-sort="<?= Helper::formatToLocalDate($remuneraciones->periodo) ?>">
                                        <?= Helper::formatToLocalDate($remuneraciones->periodo) ?></td>
                                    <td><?= "" ?></td>
                                    <td><?= isset($p) ? number_format($p->monto, 0, ",", ".") : "?" ?></td>
                                    <td><?php
                                        /* isset($p->cuenta_id) ?
                                                    \app\models\CategoriasChipax::getCategoriaById($p->cuenta_id)->nombre . " - " .
                                                    \app\models\LineaNegocio::getLineaNegocioById($p->linea_negocio_id)->nombre : "" */
                                        ?></td>
                                    <td>Remuneración</td>
                                    <td><?= $remuneraciones->sincronizado ? "sync" : "mogli" ?></td>
                                    <!--<td><? $color === "bg-warning" ? '<a href="#"><i class="fa fa-sync"></i></a>' : '' ?></td>-->
                                    <td><?php
                                        if ($remuneraciones->sincronizado) {
                                            echo "";
                                        } else {
                                            //echo !\app\models\SamSincro::isSamSincronizedFolio($remuneraciones->id, $remuneraciones->empleado->rut) ?
                                            echo true ?
                                                Html::button('<i class="fa fa-sync"></i>', [
                                                    'class' => 'showModalButton btn btn-sm btn-primary', 'title' => "Sincronizar con SAM",
                                                    'value' => \yii\helpers\Url::to([
                                                        "/modal/sync-sam", "prorrata" => $p,
                                                        "descripcion" => $model->descripcion, "remuneracion" => $remuneraciones, "i" => $indice
                                                    ]),
                                                    'data-toggle' => 'modal', 'data-target' => '#modalvote'
                                                ]) : "";
                                            //                                                        \yii\helpers\Html::a('<i class="fa fa-sync"></i>', ["modal/sync-sam"],
                                            //                                                                ['title' => 'Sincronizar con SAM', 'data-pjax' => '0', "class" => "showModalButton"]) : ''
                                        }
                                        ?>
                                    </td>
                                </tr>
                    <?php
                                $indice++;
                            endforeach;
                        endforeach;
                    }
                    ?>

                    <!-- GASTOS DE RINDEGASTOS TRAÍDOS DIRECTAMENTE DE SAM -->
                    <?php
                    foreach ($rindeGastos as $rinde) :
                        $informe = InformeGasto::findOne($rinde->report_id);
                        $nro_informe = isset($informe) ? $informe->numero : "";
                    ?>
                        <tr>
                            <td style="text-overflow: ellipsis; width: 250px;"><?= $rinde->supplier ?></td>
                            <td><?= $rinde->gastoCompleta[0]->rut_proveedor ?></td>
                            <td><?= $nro_informe ?></td>
                            <td style="min-width: 84px !important;" data-sort="<?= Helper::formatToLocalDate($rinde->issue_date) ?>">
                                <?= Helper::formatToLocalDate($rinde->issue_date) ?></td>
                            <td><?= $rinde->gastoCompleta[0]->nro_documento ?></td>
                            <td><?= isset($rinde) ? number_format($rinde->net, 0, ",", ".") : "?" ?></td>
                            <td><?= isset($rinde->note) ? Helper::removeSlashes($rinde->note) : "" ?></td>
                            <td>RindeGastos</td>
                            <td>rinde</td>
                            <td>
                                <input type="hidden" name="ForExcel[Rindegastos][fecha]" value="<?= $rinde->issue_date ?>" />
                                <input type="hidden" name="ForExcel[Rindegastos][centro_costo]" value="<?= $rinde->gastoCompleta[0]->centro_costo_faena ?>" />
                                <input type="hidden" name="ForExcel[Rindegastos][cuenta]" value="<?= $rinde->category ?>" />
                                <input type="hidden" name="ForExcel[Rindegastos][linea_negocio]" value="<?= $rinde->expense_policy_id ?>" />
                                <input type="hidden" name="ForExcel[Rindegastos][responsable]" value="<?= $rinde->gastoCompleta[0]->nombre_quien_rinde ?>" />
                                <input type="hidden" name="ForExcel[Rindegastos][tipo_documento]" value="<?= $rinde->gastoCompleta[0]->tipo_documento ?>" />
                                <input type="hidden" name="ForExcel[Rindegastos][proveedor]" value="<?= $rinde->supplier ?>" />
                                <input type="hidden" name="ForExcel[Rindegastos][num_documento]" value="<?= $rinde->gastoCompleta[0]->nro_documento ?>" />
                                <input type="hidden" name="ForExcel[Rindegastos][nro_informe]" value="<?= $nro_informe ?>" />
                                <input type="hidden" name="ForExcel[Rindegastos][descripcion]" value="<?= $rinde->note ?>" />
                                <input type="hidden" name="ForExcel[Rindegastos][monto]" value="<?= $rinde->total ?>" />
                                <input type="hidden" name="ForExcel[Rindegastos][moneda]" value="<?= 1000 ?>" />
                                <?php
                                /* if ($posible_duplicado) {
                                    echo Html::a("<i class='fa fa-exclamation-triangle text-light'></i>", ["#"], ["type" => "button"]);
                                } else { */
                                //echo Html::checkbox("cargaMasiva", false, ["class" => "", "id" => "cargaMasiva"]);
                                //echo '<input type="checkbox" value="">';
                                $rindeGastosParaExcel = $rindeGastos;
                                echo '<div class="custom-control custom-switch" style="padding: 0px !important;">
                                                <label class="switch-sm">
                                                    <input type="checkbox" class="cargaMasiva">
                                                    <span class="slider-sm round"></span>
                                                </label>
                                                <label style="font-weight: normal">Sincronizar Excel</label>
                                            </div>';
                                //echo Html::a("<i class='fa fa-sync'></i>", ["#"], ["type" => "button"]);
                                // }
                                ?>
                            </td>
                        </tr>
                    <?php
                    endforeach;
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
                rindeGastos.moneda = $(input).children()[11].value;
        
                excelData.push(rindeGastos);
            });
        });
        
        $.ajax({
            url: "/sincronizador/sincronizar-con-chipax",
            type: "post",
            data: JSON.stringify(excelData),
            dataType: "json",
            success: function (data) {
                $("#syncExcel>i").removeClass("fa-spin");
                $("#syncExcel>i").removeClass("fa-spinner");
                $("#syncExcel>i").addClass("fa-file-excel");
                $("#syncExcel").attr("disabled", false);
                window.open("/chipax/web/chipax/download-excel", "_blank");
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
        
    $("#chkSincronizados").click(function(){
        if (this.checked) {
            if ($("#chkChipax").is(":checked"))
                $("#chkChipax").click();
            if ($("#chkRinde").is(":checked"))
                $("#chkRinde").click();
            tabla.DataTable().columns(8).search("sync").draw();
        } else {
            tabla.DataTable().columns(8).search("").draw();
        }
    });
    $("#chkChipax").click(function(){
        if (this.checked) {
            if ($("#chkSincronizados").is(":checked"))
                $("#chkSincronizados").click();
        if ($("#chkRinde").is(":checked"))
                $("#chkRinde").click();
            tabla.DataTable().columns(8).search("mogli").draw();
        } else {
            tabla.DataTable().columns(8).search("").draw();
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