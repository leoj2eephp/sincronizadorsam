<?php

namespace app\controllers;

use app\components\Helper;
use app\models\ChipaxApiService;
use app\models\CompraChipax;
use app\models\FlujoCajaCartola;
use app\models\Gasto;
use app\models\GastoChipax;
use app\models\HonorarioChipax;
use app\models\LineaNegocioChipax;
use app\models\RemuneracionChipax;
use Yii;
use yii\base\Controller;
use yii\filters\VerbFilter;

class SincronizadorController extends Controller {

    public $token;

    /**
     * {@inheritdoc}
     */
    public function behaviors() {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex($hash = null) {
        $fecha_desde = date("Y-m-01");
        $fecha_hasta = date("Y-m-d");

        if (Yii::$app->request->isPost) {
            $fecha_desde = Helper::formatToDBDate(null !== (\Yii::$app->request->post("fecha_desde")) ? \Yii::$app->request->post("fecha_desde") : "");
            $fecha_hasta = Helper::formatToDBDate(null !== (\Yii::$app->request->post("fecha_hasta")) ? \Yii::$app->request->post("fecha_hasta") : "");
        }

        $model = new FlujoCajaCartola();
        $model->compras = CompraChipax::find()->with("prorrataChipax")->where(
            "fecha_emision >= :desde AND fecha_emision <= :hasta",
            [":desde" => $fecha_desde, ":hasta" => $fecha_hasta]
        )->all();
        $model->gastos = GastoChipax::find()->with("prorrataChipax")->where(
            "fecha >= :desde AND fecha <= :hasta",
            [":desde" => $fecha_desde, ":hasta" => $fecha_hasta]
        )->all();
        $model->honorarios = HonorarioChipax::find()->with("prorrataChipax")->where(
            "fecha_emision >= :desde AND fecha_emision <= :hasta",
            [":desde" => $fecha_desde, ":hasta" => $fecha_hasta]
        )->all();
        $model->remuneracions = RemuneracionChipax::find()->with("prorrataChipax")->where(
            "periodo >= :desde AND periodo <= :hasta",
            [":desde" => $fecha_desde, ":hasta" => $fecha_hasta]
        )->all();

        $rindeGastos = Gasto::find()->joinWith("gastoCompleta")->where(
            "issue_date > :desde AND issue_date <= :hasta",
            [":desde" => $fecha_desde, ":hasta" => $fecha_hasta]
        )->all();

        /* echo "<pre>";
        print_r($rindeGastos);
        die; */

        return $this->render("index", [
            "fecha_desde" => $fecha_desde,
            "fecha_hasta" => $fecha_hasta,
            "model" => $model,
            "rindeGastos" => $rindeGastos,
            "combustibles" => []
        ]);
    }

    public function actionSincronizar() {
        $chipaxApiService = new ChipaxApiService();
        $lineasNegocio = $chipaxApiService->getLineasNegocio();
        LineaNegocioChipax::sincronizarDatos($lineasNegocio);
        $chipaxApiService->sincronizarCategorias();
        $chipaxApiService->sincronizarChipaxData();
    }
}
