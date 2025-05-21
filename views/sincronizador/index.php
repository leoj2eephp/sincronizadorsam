<?php

use yii\helpers\Html;
use app\components\Helper;
use app\models\ComentariosSincronizador;
use app\models\EmpresaChipax;
use app\models\FlujoCajaCartola;
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
                    <div class="col-md-2">
                        <h5>Solo Otzi</h5>
                        <div class="custom-control custom-switch">
                            <label class="switch">
                                <input type="checkbox" id="chkOtzi">
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <h5>Solo COMA</h5>
                        <div class="custom-control custom-switch">
                            <label class="switch">
                                <input type="checkbox" id="chkComa">
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                    <!-- <div class="col-md-3">
                        <h5>Solo RindeGastos</h5>
                        <div class="custom-control custom-switch">
                            <label class="switch">
                                <input type="checkbox" id="chkRinde">
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div> -->
                    <div class="col-md-4">
                        <?= Html::submitButton("Buscar <i class='fa fa-search'></i>", ["class" => "btn btn-primary"]) ?>
                        <?=
                        Html::button('Subir DTEs <i class="fa fa-file-upload"></i>', [
                            'class' => 'showModalButton btn btn-success pull-center',
                            'title' => "Subir archivo XML del SII",
                            'value' => \yii\helpers\Url::to(["/modal/upload-dte"]), 'data-toggle' => 'modal', 'data-target' => '#modalvote'
                        ])
                        ?>
                        <?php
                        //Html::button("Generar Excel <i class='fa fa-file-excel'></i>", ["class" => "btn btn-success", "id" => "syncExcel"])
                        ?>
                        <!-- <div class="col-md-5 float-right">
                            <h5>Seleccionar Todos Rinde Gastos</h5>
                            <div class="custom-control custom-switch">
                                <label class="switch">
                                    <input type="checkbox" id="chkRindeGastosAll">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div> -->
                        <!--<button type="submit" class="btn btn-primary">Buscar <i class="fa fa-search"></i></button>-->
                    </div>
                    <div class="offset-2"></div>
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
                        <th style="text-overflow: ellipsis;">Empresa</th>
                        <th style="text-overflow: ellipsis; width: 250px;">Razón Social</th>
                        <th>Rut Emisor</th>
                        <th>Folio</th>
                        <th style="min-width: 84px !important; width: 9% !important;">Fecha Emisión</th>
                        <th>N° Doc</th>
                        <th>Neto</th>
                        <th style="max-width: 250px !important;">Descripción</th>
                        <th>Tipo Movimiento</th>
                        <th>Comentario</th>
                        <th>Algo</th>
                        <th class="sorting_disabled">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $indice = 0;
                    if (count($model->compras) > 0) {
                        foreach ($model->compras as $compra) :
                            //$rindeSincronizado = GastoCompleta::isSincronizedWithChipax($compra->folio, $compra->fecha_emision);
                            /* if (count($rindeSincronizado) == 0) {
                                        $rindeSincronizado = app\models\RindeGastos::getCombustibleExpenseByNumDoc($combustibles, trim($compra->folio));
                                    } */
                            $mostrado = array();
                            foreach ($compra->spProrrataChipax as $p) :
                                $cantidad_registros++;
                                $color = "bg-info-light";
                                $gastoCompletaCompra = count($compra->gastoCompleta) > 0 ? $compra->gastoCompleta[0] : null;
                    ?>
                                <tr nroDcto="<?= $compra->folio ?>" fecha="<?= $compra->fecha_emision ?>" <?php
                                    if ($compra->sincronizado) {
                                        //$compra->sincronizado = true;
                                        if (!isset($gastoCompletaCompra)) {
                                            // Esto solo sucede para los folios con ceros adelante.. como ya los marqué como sincronizados
                                            // ahora tengo que darles el objeto gastoCompleta..
                                            /* $gastoCompletaCompra = GastoCompleta::find()
                                                ->innerJoin("prorrata_chipax", "prorrata_chipax.compra_chipax_id = :compraId", [":compraId" => $compra->id])
                                                ->where(["like", "nro_documento", "%000" . $compra->folio, false])
                                                ->andWhere($combustiblesCondition)
                                                ->one(); */
                                        }

                                        $rindeGastosSincronizados[] = $gastoCompletaCompra->nro_documento;
                                        $cantidad_sincronizados++;
                                        echo 'data-toggle="tooltip" data-html="true"
                                                    title="' . "<div class='bg-info text-uppercase text-bold'>" . $gastoCompletaCompra->gasto->supplier .
                                            ' (' . $gastoCompletaCompra->rut_proveedor . ')</div>';
                                        $total_montos = 0;  // esto es solo para los casos en los que Chipax tiene desglosado un registro que es único en RindeGastos
                                        foreach ($compra->gastoCompleta as $rinde) :
                                            $total_montos += $rinde->gasto->net;
                                        endforeach;
                                        foreach ($compra->gastoCompleta as $i => $rinde) :
                                            $montoProrrata = $p->monto_sumado > 0 ? $p->monto_sumado : $p->monto;
                                            // VALIDACIÓN para casos donde viene asociado un GastoCompleta que no corresponde (cuando el nro_documento es igual pero no corresponde)
                                            if (!array_key_exists($p->cuenta_id, FlujoCajaCartola::CATEGORIAS_REMUNERACIONES_CHIPAX)) {
                                                // COMPRA
                                                if (
                                                    $rinde->monto_neto != $montoProrrata ||
                                                    $compra->fecha_gasto != $compra->fecha_emision ||
                                                    $rinde->nro_documento != $compra->folio
                                                ) continue;
                                            } else {
                                                // REMUNERACIÓN
                                                if (
                                                    $rinde->total_calculado != $compra->monto_total ||
                                                    $compra->fecha_gasto != $compra->fecha_emision ||
                                                    $rinde->nro_documento != $compra->folio
                                                ) continue;
                                            }

                                            $color = $p->monto_sumado > 0 ? "bg-warning" : "bg-info-light";
                                            $css_totales = "text-info font-weight-bold";
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
                                        if ($compra->rindeGastoDividido) {
                                            $color = "bg-warning-light";
                                            $css_totales = "text-info font-weight-bold";
                                            foreach ($compra->rindeGastoData as $rinde) {
                                                echo '<div><b>Fecha: </b>' . Helper::formatToLocalDate($rinde->gasto->issue_date) . '</div>
                                                    <div class=' . "'" . $css_totales . "'" . '><b>Neto: </b>' . number_format($rinde->gasto->net, 0, ",", ".") . '</div>
                                                    <div class=' . "'" . $css_totales . "'" . '><b>Total: </b>' . number_format($rinde->gasto->total, 0, ",", ".") . '</div>
                                                    <div><b>Centro de Costo: </b>' . $rinde->centro_costo_faena . '</div>
                                                    <div><b>Vehículo: </b>' . $rinde->vehiculo_equipo . '</div>
                                                    <br />';
                                            }
                                        }
                                        echo '"';
                                        echo ' class="' . $color . '"';
                                    }
                                    ?>>
                                    <td style="text-overflow: ellipsis;"><?= EmpresaChipax::getName($compra->empresa_chipax_id)?></td>
                                    <td style="text-overflow: ellipsis; width: 250px;"><?= $compra->razon_social ?></td>
                                    <td><?= $compra->rut_emisor ?></td>
                                    <td></td>
                                    <td style="min-width: 84px !important;" data-sort="<?= Helper::formatToLocalDate($compra->fecha_emision) ?>">
                                        <?= Helper::formatToLocalDate($compra->fecha_emision) ?></td>
                                    <td><?= $compra->folio ?></td>
                                    <td><?php
                                        if (isset($p)) {
                                            echo $p->monto_sumado > 0 ? number_format($p->monto_sumado, 0, ",", ".") : number_format($p->monto, 0, ",", ".");
                                        } ?></td>
                                    <td><?= isset($model->descripcion) ? $model->descripcion . ' (' . $compra->razon_social . ')' : "" ?></td>
                                    <td>
                                        <?= (!array_key_exists($p->cuenta_id, FlujoCajaCartola::CATEGORIAS_REMUNERACIONES_CHIPAX)) ?
                                            "Compra" : "Remuneración" ?>
                                    </td>
                                    <td>
                                        <?php
                                        $comentario = ComentariosSincronizador::find()->where(
                                            "monto = :m AND fecha = :f AND nro_documento = :n",
                                            [
                                                ":m" => $compra->monto_total, ":f" => $compra->fecha_emision,
                                                ":n" => isset($gastoCompletaCompra) ? $gastoCompletaCompra->nro_documento : ""
                                            ]
                                        )->one();
                                        if (isset($comentario)) :
                                        ?>
                                            <textarea class="form-control comentario" idComentario="<?= $comentario->id ?>" rows="3"><?= $comentario->comentario ?></textarea>
                                        <?php
                                        else :
                                        ?>
                                            <input type="text" class="form-control comentario" monto="<?= $compra->monto_total ?>" fecha="<?= $compra->fecha_emision ?>" nroDoc="<?= isset($gastoCompletaCompra) ? $gastoCompletaCompra->nro_documento : "" ?>" />
                                        <?php
                                        endif;
                                        ?>
                                    </td>
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
                                                    "/modal/sync-sam", "id" => $p->id, "i" => $indice,
                                                    "tipo" => "compra",
                                                    "monto_sumado" => $p->monto_sumado > 0 ? $p->monto_sumado : $p->monto,
                                                    "es_remu" => (array_key_exists($p->cuenta_id, FlujoCajaCartola::CATEGORIAS_REMUNERACIONES_CHIPAX))
                                                ]),
                                                'data-toggle' => 'modal', 'data-target' => '#modalvote'
                                            ]);
                                        }

                                        if (isset($gastoCompletaCompra))
                                            echo Html::button('<i class="fa fa-trash"></i>', [
                                                "class" => "btn btn-sm btn-danger delete-gasto",
                                                'title' => "Eliminar",
                                                'value' => Url::to([
                                                    "/modal/delete-gasto", "id" => $gastoCompletaCompra["id"],
                                                ]),
                                            ]);
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
                            //$rindeSincronizado = GastoCompleta::isSincronizedWithChipax($gastos->num_documento, $gastos->fecha);
                            /* if (count($rindeSincronizado) == 0) {
                                        $rindeSincronizado = app\models\RindeGastos::getCombustibleExpenseByNumDoc($combustibles, trim($gastos->num_documento));
                                    } */
                            $gastoMostrado = array();
                            foreach ($gastos->spProrrataChipax as $p) :
                                $cantidad_registros++;
                                $color = "bg-info-light";
                        ?>
                                <tr <?php
                                    if ($gastos->sincronizado) {
                                        $gastos->sincronizado = true;
                                        $rindeGastosSincronizados[] = $gastos->gastoCompleta[0]->nro_documento;
                                        $cantidad_sincronizados++;
                                        foreach ($gastos->gastoCompleta as $i => $rinde) :
                                            // VALIDACIÓN para casos donde viene asociado un GastoCompleta que no corresponde (cuando el nro_documento es igual pero no corresponde)
                                            if (!array_key_exists($p->cuenta_id, FlujoCajaCartola::CATEGORIAS_REMUNERACIONES_CHIPAX)) {
                                                // COMPRA
                                                if (
                                                    $rinde->monto_neto != $p->monto ||
                                                    $gastos->fecha_gasto != $gastos->fecha ||
                                                    $rinde->nro_documento != $gastos->num_documento
                                                ) continue;
                                            } else {
                                                // REMUNERACIÓN
                                                if (
                                                    $rinde->total_calculado != $gastos->monto_total ||
                                                    $gastos->fecha_gasto != $gastos->fecha ||
                                                    $rinde->nro_documento != $gastos->num_documento
                                                ) continue;
                                            }
                                            $color = "bg-info-light";
                                            $css_totales = "text-info font-weight-bold";
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
                                    <td style="text-overflow: ellipsis;"><?= EmpresaChipax::getName($gastos->empresa_chipax_id)?></td>
                                    <td style="text-overflow: ellipsis; width: 250px;"><?= $gastos->proveedor ?></td>
                                    <td><?= isset($gastos->proveedor) ? $gastos->proveedor : "" ?></td>
                                    <td></td>
                                    <td style="min-width: 84px !important;" data-sort="<?= Helper::formatToLocalDate($gastos->fecha) ?>">
                                        <?= Helper::formatToLocalDate($gastos->fecha) ?></td>
                                    <td><?= $gastos->num_documento ?></td>
                                    <td><?= isset($p) ? number_format($p->monto, 0, ",", ".") : "?" ?></td>
                                    <td><?= isset($gastos->descripcion) ? $gastos->descripcion : "" ?></td>
                                    <td>
                                        <?= (!array_key_exists($p->cuenta_id, FlujoCajaCartola::CATEGORIAS_REMUNERACIONES_CHIPAX)) ?
                                            "Gasto" : "Remuneración" ?>
                                    </td>
                                    <td>
                                        <?php
                                        $comentario = ComentariosSincronizador::find()->where(
                                            "monto = :m AND fecha = :f AND nro_documento = :n",
                                            [
                                                ":m" => $gastos->monto, ":f" => $gastos->fecha,
                                                ":n" => isset($gastos->gastoCompleta[0]) ? $gastos->gastoCompleta[0]->nro_documento : ""
                                            ]
                                        )->one();
                                        if (isset($comentario)) :
                                        ?>
                                            <textarea class="form-control comentario" idComentario="<?= $comentario->id ?>" rows="3"><?= $comentario->comentario ?></textarea>
                                        <?php
                                        else :
                                        ?>
                                            <input type="text" class="form-control comentario" monto="<?= $gastos->monto ?>" fecha="<?= $gastos->fecha ?>" nroDoc="<?= isset($gastos->gastoCompleta[0]) ? $gastos->gastoCompleta[0]->nro_documento : "" ?>" />
                                        <?php
                                        endif;
                                        ?>
                                    </td>
                                    <td><?= $gastos->sincronizado ? "sync" : "mogli" ?></td>
                                    <!--                                            <td><? $color === "bg-warning" ? '<a href="#"><i class="fa fa-sync"></i></a>' : '' ?></td>-->
                                    <td><?php
                                        if ($gastos->sincronizado) {
                                            echo "";
                                        } else {
                                            echo Html::button('<i class="fa fa-sync"></i>', [
                                                'class' => 'showModalButton btn btn-sm btn-primary',
                                                'title' => "Sincronizar con SAM", 'id' => 'sync_' . $indice,
                                                'value' => Url::to([
                                                    "/modal/sync-sam", "id" => $p->id, "i" => $indice,
                                                    "tipo" => "gasto",
                                                    "es_remu" => (array_key_exists($p->cuenta_id, FlujoCajaCartola::CATEGORIAS_REMUNERACIONES_CHIPAX))
                                                ]),
                                                'data-toggle' => 'modal', 'data-target' => '#modalvote'
                                            ]);
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
                            //$rindeSincronizado = GastoCompleta::isSincronizedWithChipax($honorarios->numero_boleta, $honorarios->fecha_emision);
                            $mostrado = array();
                            foreach ($honorarios->spProrrataChipax as $p) :
                                $color = "bg-info-light";
                                $cantidad_registros++;
                            ?>
                                <tr <?php
                                    if ($honorarios->sincronizado) {
                                        $honorarios->sincronizado = true;
                                        $rindeGastosSincronizados[] = $honorarios->gastoCompleta[0]->nro_documento;
                                        $cantidad_sincronizados++;
                                        // Filtrar aquí si ha habido algún cambio
                                        foreach ($honorarios->gastoCompleta as $i => $rinde) :
                                            $color = "bg-info-light";
                                            $css_totales = "text-info font-weight-bold";
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
                                    <td style="text-overflow: ellipsis;"><?= EmpresaChipax::getName($honorarios->empresa_chipax_id)?></td>
                                    <td style="text-overflow: ellipsis; width: 250px;"><?= $honorarios->nombre_emisor ?></td>
                                    <td><?= $honorarios->rut_emisor ?></td>
                                    <td></td>
                                    <td style="min-width: 84px !important;" data-sort="<?= Helper::formatToLocalDate($honorarios->fecha_emision) ?>">
                                        <?= Helper::formatToLocalDate($honorarios->fecha_emision) ?></td>
                                    <td><?= $honorarios->numero_boleta ?></td>
                                    <td><?= isset($p) ? number_format($p->monto, 0, ",", ".") : "?" ?></td>
                                    <td><?= isset($model->descripcion) ? $model->descripcion : "" ?></td>
                                    <td>Honorarios</td>
                                    <td>
                                        <?php
                                        $comentario = ComentariosSincronizador::find()->where(
                                            "monto = :m AND fecha = :f AND nro_documento = :n",
                                            [
                                                ":m" => $honorarios->monto_liquido, ":f" => $honorarios->fecha_emision,
                                                ":n" => isset($honorarios->gastoCompleta[0]) ? $honorarios->gastoCompleta[0]->nro_documento : ""
                                            ]
                                        )->one();
                                        if (isset($comentario)) :
                                        ?>
                                            <textarea class="form-control comentario" idComentario="<?= $comentario->id ?>" rows="3"><?= $comentario->comentario ?></textarea>
                                        <?php
                                        else :
                                        ?>
                                            <input type="text" class="form-control comentario" monto="<?= $honorarios->monto_liquido ?>" fecha="<?= $honorarios->fecha_emision ?>" nroDoc="<?= isset($honorarios->gastoCompleta[0]) ? $honorarios->gastoCompleta[0]->nro_documento : "" ?>" />
                                        <?php
                                        endif;
                                        ?>
                                    </td>
                                    <td><?= $honorarios->sincronizado ? "sync" : "mogli" ?></td>
                                    <!--<td><? $color === "bg-warning" ? '<a href="#"><i class="fa fa-sync"></i></a>' : '' ?></td>-->
                                    <td><?php
                                        // TODO: aplicar mismo cambio realizado en compras y gastos, relacionado con las cuentas_id de remuneraciones
                                        if ($honorarios->sincronizado) {
                                            echo "";
                                        } else {
                                            echo Html::button('<i class="fa fa-sync"></i>', [
                                                'class' => 'showModalButton btn btn-sm btn-primary', 'title' => "Sincronizar con SAM",
                                                'id' => 'sync_' . $indice,

                                                'value' => Url::to([
                                                    "/modal/sync-sam", "id" => $p->id, "i" => $indice,
                                                    "tipo" => "honorario",
                                                    "es_remu" => (array_key_exists($p->cuenta_id, FlujoCajaCartola::CATEGORIAS_REMUNERACIONES_CHIPAX))
                                                ]),
                                                /* 'value' => Url::to([
                                                    "/modal/sync-sam", "id" => $p->id, "tipo" => "honorario", "i" => $indice
                                                ]), */
                                                'data-toggle' => 'modal', 'data-target' => '#modalvote'
                                            ]);
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
                            $mostrado = array();
                            foreach ($remuneraciones->spProrrataChipax as $p) :
                                $color = "bg-info-light";
                                $cantidad_registros++;
                            ?>
                                <tr>
                                <td style="text-overflow: ellipsis;"><?= EmpresaChipax::getName($remuneraciones->empresa_chipax_id)?></td>
                                    <td style="text-overflow: ellipsis; width: 250px;">
                                        <?= $remuneraciones->nombre_empleado . ' ' . $remuneraciones->apellido_empleado ?>
                                    </td>
                                    <td><?= $remuneraciones->rut_empleado ?></td>
                                    <td></td>
                                    <td style="min-width: 84px !important;" data-sort="<?= Helper::formatToLocalDate($remuneraciones->periodo) ?>">
                                        <?= Helper::formatToLocalDate($remuneraciones->periodo) ?></td>
                                    <td><?= $remuneraciones->id ?></td>
                                    <td><?= isset($p) ? number_format($p->monto, 0, ",", ".") : "?" ?></td>
                                    <td></td>
                                    <td>Remuneración</td>
                                    <td>
                                        <?php
                                        $comentario = ComentariosSincronizador::find()->where(
                                            "monto = :m AND fecha = :f AND nro_documento = :n",
                                            [
                                                ":m" => $remuneraciones->monto_liquido, ":f" => $remuneraciones->periodo,
                                                ":n" => isset($remuneraciones->gastoCompleta[0]) ? $remuneraciones->gastoCompleta[0]->nro_documento : ""
                                            ]
                                        )->one();
                                        if (isset($comentario)) :
                                        ?>
                                            <textarea class="form-control comentario" idComentario="<?= $comentario->id ?>" rows="3"><?= $comentario->comentario ?></textarea>
                                        <?php
                                        else :
                                        ?>
                                            <input type="text" class="form-control comentario" monto="<?= $remuneraciones->monto_liquido ?>" fecha="<?= $remuneraciones->periodo ?>" nroDoc="<?= isset($remuneraciones->gastoCompleta[0]) ? $remuneraciones->gastoCompleta[0]->nro_documento : "" ?>" />
                                        <?php
                                        endif;
                                        ?>
                                    </td>
                                    <td><?= $remuneraciones->sincronizado ? "sync" : "mogli" ?></td>
                                    <!--<td><? $color === "bg-warning" ? '<a href="#"><i class="fa fa-sync"></i></a>' : '' ?></td>-->
                                    <td><?php
                                        if ($remuneraciones->sincronizado) {
                                            echo "";
                                        } else {
                                            echo Html::button('<i class="fa fa-sync"></i>', [
                                                'class' => 'showModalButton btn btn-sm btn-primary', 'title' => "Sincronizar con SAM",
                                                'id' => 'sync_' . $indice,
                                                'value' => Url::to([
                                                    "/modal/sync-sam", "id" => $p->id, "tipo" => "remuneracion", "i" => $indice
                                                ]),
                                                'data-toggle' => 'modal', 'data-target' => '#modalvote'
                                            ]);
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
        
    var tabla = $('table').dataTable({  
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
                "targets": [10],
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
            tabla.DataTable().columns(10).search("sync").draw();
        } else {
            tabla.DataTable().columns(10).search("").draw();
        }
    });
    $("#chkChipax").click(function(){
        if (this.checked) {
            if ($("#chkSincronizados").is(":checked"))
                $("#chkSincronizados").click();
        if ($("#chkRinde").is(":checked"))
                $("#chkRinde").click();
            tabla.DataTable().columns(10).search("mogli").draw();
        } else {
            tabla.DataTable().columns(10).search("").draw();
        }
    });
    $("#chkOtzi").click(function(){
        if (this.checked) {
            if ($("#chkComa").is(":checked"))
                $("#chkComa").click();
            tabla.DataTable().columns(0).search("Otzi").draw();
        } else {
            tabla.DataTable().columns(0).search("").draw();
        }
    });
    $("#chkComa").click(function(){
        if (this.checked) {
            if ($("#chkOtzi").is(":checked"))
                $("#chkOtzi").click();
            tabla.DataTable().columns(0).search("SPA").draw();
        } else {
            tabla.DataTable().columns(0).search("").draw();
        }
    });
        
    // aplicando estilos al add
    $('[data-toggle="tooltip"]').tooltip();
});
JS;
$this->registerJs($script);
?>