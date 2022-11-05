<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
?>

<table class="table table-bordered" id="tabla-detalle">
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Máquina</th>
            <th>Descripción</th>
            <th>Documento</th>
            <th>Neto</th>
            <th>Faena</th>
            <th>Proveedor</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($remuneraciones as $remu) : ?>
            <tr id="remu_<?= $remu->id ?>">
                <td><?= $remu->fecha_rendicion ?></td>
                <td><?= $remu->getCamionEquipoNombre() ?></td>
                <td><?= $remu->descripcion ?></td>
                <td><?= $remu->documento ?></td>
                <td><?= number_format($remu->neto, 0, ",", ".") ?></td>
                <td><?= isset($remu->faena) ? $remu->faena->nombre : "" ?></td>
                <td><?= $remu->nombre_proveedor ?></td>
                <td>
                    <?php Html::button('<i class="fa fa-edit"></i>', [
                        'class' => 'showModalButton btn btn-sm btn-warning text-white',
                        'title' => "Actualización de Remuneración",
                        'value' => Url::to([
                            "/remuneraciones-sam/actualizar-remuneracion", "id" => $remu->id
                        ]),
                        'data-toggle' => 'modal', 'data-target' => '#modalvote'
                    ]) ?>
                    <?= Html::button('<i class="fa fa-trash"></i>', [
                        'class' => 'showModalButton btn btn-sm btn-danger',
                        'title' => "Confirmación de Eliminación",
                        'value' => Url::to([
                            "/remuneraciones-sam/eliminar-remuneracion", "id" => $remu->id
                        ]),
                        'data-toggle' => 'modal', 'data-target' => '#modalvote'
                    ]) ?>
                </td>
            </tr>
        <?php
        endforeach;
        ?>
    </tbody>
</table>