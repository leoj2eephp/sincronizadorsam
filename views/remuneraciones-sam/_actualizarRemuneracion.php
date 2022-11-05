<?php

use kartik\form\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
?>
<?php
$form = ActiveForm::begin([
    'id' => 'remuneraciones-form',
    'action' => ['remuneraciones-sam/eliminar-remuneracion']
])
?>
<?= $form->field($model, "id", [])->hiddenInput()->label(false) ?>
<div class="col col-sm-6 offset-sm-3">
    ¿Está seguro de que desea eliminar este registro de Remuneración?

    <br />
    <br />
    
    <?= Html::button('Eliminar <i class="fa fa-trash"></i>', ['class' => 'btn btn-danger text-center', 'id' => 'delete-remuneracion']) ?>
</div>
<?php ActiveForm::end() ?>