<?php

use yii\helpers\Html;
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
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($remuneraciones as $remu) : ?>
            <tr>
                <td><?= $remu->fecha_rendicion ?></td>
                <td><?= $remu->getCamionEquipoNombre() ?></td>
                <td><?= $remu->descripcion ?></td>
                <td><?= $remu->documento ?></td>
                <td><?= number_format($remu->neto, 0, ",", ".") ?></td>
                <td></td>
                <td><?= $remu->nombre_proveedor ?></td>
            </tr>
        <?php
        endforeach;
        ?>
    </tbody>
</table>