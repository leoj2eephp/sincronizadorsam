<?php

use \kartik\form\ActiveForm;

$this->title = 'Subir archivo DTE del SII';
?>
<div class="profesional-create">
    <?php
    $form = ActiveForm::begin([
                "id" => "uploadDTE",
                'type' => ActiveForm::TYPE_HORIZONTAL,
                'formConfig' => ['labelSpan' => 3, 'deviceSize' => ActiveForm::SIZE_SMALL],
                'options' => ['enctype' => 'multipart/form-data']
    ]);
    ?>
    <?php
    echo \kartik\file\FileInput::widget([
        'model' => $model,
        'language' => 'es',
        'attribute' => 'file[]',
        'options' => ['multiple' => true],
        'pluginOptions' => [
            'allowedFileExtensions' => ['xml'],
        ]
    ]);
    ?>
    <?php
    ActiveForm::end();
    ?>
</div>
