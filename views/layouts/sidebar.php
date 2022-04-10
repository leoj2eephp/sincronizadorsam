<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="<?= \yii\helpers\Url::home() ?>" class="brand-link">
        <img src="/img/logo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
             style="opacity: .8">
        <span class="brand-text font-weight-light">Rinde/Chipax</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">

            <?php
            echo \hail812\adminlte3\widgets\Menu::widget([
                'items' => [
                    //['label' => 'Iniciar Sesión', 'url' => '/site/login', 'visible' => Yii::$app->user->isGuest],
                    [
                        'label' => 'Asociación de Datos',
                        'header' => true,
                        "url" => ["site/index"],
                        //"visible" => Yii::$app->user->can("admin") || Yii::$app->user->can("profesor")
                    ],
                    ['label' => 'Gastos Chipax', 'url' => ['chipax/index'], 'iconStyle' => 'far',
                        //"visible" => Yii::$app->user->can("admin") || Yii::$app->user->can("profesor")
                    ],
                    ['label' => 'Rinde Gastos', 'url' => ['rinde-gastos/index'], 'iconStyle' => 'far',
                        //"visible" => Yii::$app->user->can("admin") || Yii::$app->user->can("profesor")
                    ],
                    //['label' => 'Comparativa', 'url' => ['compare/index'], 'iconStyle' => 'far',],
                ]
            ]);
            ?>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>