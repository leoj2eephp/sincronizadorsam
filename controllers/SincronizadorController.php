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
        $model->compras = CompraChipax::find()->with(["prorrataChipax", "gastoCompleta"])->where(
            "fecha_emision >= :desde AND fecha_emision <= :hasta",
            [":desde" => $fecha_desde, ":hasta" => $fecha_hasta]
        )->all();

        foreach ($model->compras as $compra) {
            if (count($compra->gastoCompleta) > 0) {
                $compra->sincronizado = 1;
            } else {
                $compra->sincronizado = 0;
            }
        }

        $model->gastos = GastoChipax::find()->with(["prorrataChipax", "gastoCompleta"])->where(
            "fecha >= :desde AND fecha <= :hasta",
            [":desde" => $fecha_desde, ":hasta" => $fecha_hasta]
        )->all();

        foreach ($model->gastos as $gasto) {
            if (count($gasto->gastoCompleta) > 0) {
                $gasto->sincronizado = 1;
            } else {
                $gasto->sincronizado = 0;
            }
        }

        $model->honorarios = HonorarioChipax::find()->with(["prorrataChipax", "gastoCompleta"])->where(
            "fecha_emision >= :desde AND fecha_emision <= :hasta",
            [":desde" => $fecha_desde, ":hasta" => $fecha_hasta]
        )->all();

        foreach ($model->honorarios as $honorario) {
            if (count($honorario->gastoCompleta) > 0) {
                $honorario->sincronizado = 1;
            } else {
                $honorario->sincronizado = 0;
            }
        }

        $model->remuneracions = RemuneracionChipax::find()->with(["prorrataChipax", "gastoCompleta"])->where(
            "periodo >= :desde AND periodo <= :hasta",
            [":desde" => $fecha_desde, ":hasta" => $fecha_hasta]
        )->all();

        foreach ($model->remuneracions as $remu) {
            if (count($remu->gastoCompleta) > 0) {
                $remu->sincronizado = 1;
            } else {
                $remu->sincronizado = 0;
            }
        }

        return $this->render("index", [
            "fecha_desde" => $fecha_desde,
            "fecha_hasta" => $fecha_hasta,
            "model" => $model,
            //"rindeGastos" => $rindeGastos,
            "combustibles" => []
        ]);
    }

    public function actionRindeGastos() {
        $fecha_desde = date("Y-m-01");
        $fecha_hasta = date("Y-m-d");

        if (Yii::$app->request->isPost) {
            $fecha_desde = Helper::formatToDBDate(null !== (\Yii::$app->request->post("fecha_desde")) ? \Yii::$app->request->post("fecha_desde") : "");
            $fecha_hasta = Helper::formatToDBDate(null !== (\Yii::$app->request->post("fecha_hasta")) ? \Yii::$app->request->post("fecha_hasta") : "");
        }

        $rindeGastos = Gasto::find()
            ->joinWith([
                "gastoCompleta", "gastoCompleta.compraChipax", "gastoCompleta.gastoChipax",
                "gastoCompleta.honorarioChipax", //"gastoCompleta.remuneracionChipax"
            ])
            ->leftJoin("remuneracion_chipax", "remuneracion_chipax.id LIKE gasto_completa.nro_documento", [])
            ->where(
                "issue_date > :desde AND issue_date <= :hasta",
                [":desde" => $fecha_desde, ":hasta" => $fecha_hasta]
            )
            ->andFilterWhere(['not like', 'tipo_documento', "factura"])
            ->andFilterWhere(['not like', 'tipo_documento', "Honorarios"])
            ->andFilterWhere(['not like', 'tipo_documento', "Nota de credito"])
            ->andFilterWhere(['not like', 'tipo_documento', "Remunera"])
            // QUITAR TAMBIÉN Declaración de Importación 
            ->andFilterWhere(['not like', 'tipo_documento', "Declaraci"])
            ->all();

        return $this->render("rinde-gastos", [
            "fecha_desde" => $fecha_desde,
            "fecha_hasta" => $fecha_hasta,
            "model" => $rindeGastos
        ]);
    }

    private function actionSincronizar() {
        set_time_limit(0);
        $chipaxApiService = new ChipaxApiService();
        $lineasNegocio = $chipaxApiService->getLineasNegocio();
        LineaNegocioChipax::sincronizarDatos($lineasNegocio);
        $chipaxApiService->sincronizarCategorias();
        $chipaxApiService->sincronizarChipaxData();
    }

    public function actionSincronizarConChipax() {
        $json = file_get_contents("php://input");
        $data = json_decode($json);

        $carga = new \app\models\CargaMasivaForm();
        $carga->generarExcel($data);

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return "ok";
    }

    public function actionDownloadExcel() {
        $full_path = \Yii::getAlias("@app") . DIRECTORY_SEPARATOR . \app\models\CargaMasivaForm::COMPLETE_FILE_PATH;
        \app\components\Helper::download_file($full_path);
    }
}
