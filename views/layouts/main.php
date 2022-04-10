<?php
/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;

app\assets\AppAsset::register($this);
\hail812\adminlte3\assets\FontAwesomeAsset::register($this);
\hail812\adminlte3\assets\AdminLteAsset::register($this);
$this->registerCssFile('https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700');
$this->registerCssFile('https://kit.fontawesome.com/8ced7a5d16.js');

$assetDir = Yii::$app->assetManager->getPublishedUrl('@vendor/almasaeed2010/adminlte/dist');
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>

<body>
    <?php $this->beginBody() ?>
    <?= $this->render('content', ['content' => $content, 'assetDir' => $assetDir]) ?>

    <?php
    \yii\bootstrap4\Modal::begin([
        'title' => '<div id="modalHeader"></div>',
        'id' => 'modal',
        'closeButton' => ['id' => 'close-button'],
        'size' => 'modal-xl',
        'options' => [
            'tabindex' => false // important for Select2 to work properly
        ],
    ]);
    echo "<div id='modalContent'>
                <center><span class='fa fa-spinner fa-spin fa-3x text-info'></span></center>
            </div>";
    \yii\bootstrap4\Modal::end();
    ?>

    <?php
    $this->registerJsFile(
        '@web/js/jquery.dataTables.min.js',
        ['depends' => [\yii\web\JqueryAsset::class]]
    );

    $this->registerJsFile(
        "@web/js/ajax-modal-popup.js",
        ['depends' => [\yii\web\JqueryAsset::class]]
    );

    $this->endBody();
    ?>
</body>

</html>
<?php $this->endPage() ?>