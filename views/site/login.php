<?php

use yii\helpers\Html;
?>
<div class="card col-sm-8 offset-sm-2">
    <div class="card-body login-card-body">
        <div class="offset-lg-4 col-lg-8 pl-5">
            <img src="/img/logo.jpeg" class="mx-auto" style="width: 200px;">
        </div>
        <p class="login-box-msg mr-3">Ingresa tus credenciales para iniciar sesión</p>
        <?php $form = \yii\bootstrap4\ActiveForm::begin(['id' => 'login-form']) ?>

        <?=
                $form->field($model, 'username', [
                    'options' => ['class' => 'form-group has-feedback'],
                    'inputTemplate' => '{input}<div class="input-group-append"><div class="input-group-text"><span class="fas fa-envelope"></span></div></div>',
                    'template' => '{beginWrapper}{input}{error}{endWrapper}',
                    'wrapperOptions' => ['class' => 'input-group mb-3']
                ])
                ->label(false)
                ->textInput(['placeholder' => $model->getAttributeLabel('username')])
        ?>

        <?=
                $form->field($model, 'password', [
                    'options' => ['class' => 'form-group has-feedback'],
                    'inputTemplate' => '{input}<div class="input-group-append"><div class="input-group-text"><span class="fas fa-lock"></span></div></div>',
                    'template' => '{beginWrapper}{input}{error}{endWrapper}',
                    'wrapperOptions' => ['class' => 'input-group mb-3']
                ])
                ->label(false)
                ->passwordInput(['placeholder' => $model->getAttributeLabel('password')])
        ?>

        <div class="row">
            <div class="col-8">
                <?=
                $form->field($model, 'rememberMe')->checkbox([
                    'template' => '<div class="icheck-primary">{input}{label}</div>',
                    'labelOptions' => [
                        'class' => ''
                    ],
                    'uncheck' => null
                ])
                ?>
            </div>
            <div class="col-4">
                <?= Html::submitButton('Iniciar Sesión', ['class' => 'btn btn-primary btn-block']) ?>
            </div>
        </div>

        <?php \yii\bootstrap4\ActiveForm::end(); ?>

        <p class="mb-1">
            <a href="forgot-password.html">Olvidé mi Contraseña</a>
        </p>
    </div>
    <!-- /.login-card-body -->
</div>