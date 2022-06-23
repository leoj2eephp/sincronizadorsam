<?php

namespace app\controllers;

use app\components\Helper;
use app\models\ChipaxApiService;
use app\models\ComentariosSincronizador;
use app\models\CompraChipax;
use app\models\FlujoCajaCartola;
use app\models\GastoChipax;
use app\models\GastoCompleta;
use app\models\GastoRindegastos;
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
        /* $session = Yii::$app->session;
        if ($hash == null) {
            if ($session->has('hash')) {
                $hash = $session->get('hash');
            }
        } else {
            if ($session->has('hash')) {
                $session->remove('hash');
            }
        }
        $local_hash = Helper::chipaxSecret(0);
        $intentos = 1;
        while ($local_hash != $hash && $intentos <= 30) {
            $local_hash = Helper::chipaxSecret($intentos);
            $intentos++;
        }
        if ($hash != $local_hash) {
            die("Hash incorrecto");
        } */
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
                $gasto = GastoCompleta::find()->where(["like", "nro_documento", "%000" . $compra->id, false])->one();
                if ((isset($gasto))) {
                    $compra->sincronizado = 1;
                }
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
            $gasto = GastoCompleta::find()->where("nro_documento LIKE :id", [":id" => $remu->id])->one();
            if (isset($gasto)) {
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

    public function actionRindeGastos($hash = null) {
        /* $session = Yii::$app->session;
        if ($hash == null) {
            if ($session->has('hash')) {
                $hash = $session->get('hash');
            }
        } else {
            if ($session->has('hash')) {
                $session->remove('hash');
            }
        }
        $local_hash = Helper::chipaxSecret(0);
        $intentos = 1;
        while ($local_hash != $hash && $intentos <= 30) {
            $local_hash = Helper::chipaxSecret($intentos);
            $intentos++;
        }
        if ($hash != $local_hash) {
            die("Hash incorrecto");
        } */

        $fecha_desde = date("Y-m-01");
        $fecha_hasta = date("Y-m-d");

        if (Yii::$app->request->isPost) {
            $fecha_desde = Helper::formatToDBDate(null !== (\Yii::$app->request->post("fecha_desde")) ? \Yii::$app->request->post("fecha_desde") : "");
            $fecha_hasta = Helper::formatToDBDate(null !== (\Yii::$app->request->post("fecha_hasta")) ? \Yii::$app->request->post("fecha_hasta") : "");
        }

        /* $rindeGastos = Gasto::find()
            ->joinWith([
                "gastoCompleta", "gastoCompleta.compraChipax", //"gastoCompleta.gastoChipax",
                "gastoCompleta.honorarioChipax", //"gastoCompleta.remuneracionChipax"
            ]) */
        $rindeGastos = GastoRindegastos::find()->joinWith(["gastoCompletaRindegastos"])
            //->innerJoin("gasto_completa_rindegastos", "gasto_completa_rindegastos.gasto_rindegastos_id = gasto_rindegastos.id")
            ->leftJoin("compra_chipax", "compra_chipax.folio = gasto_completa_rindegastos.nro_documento
                            AND compra_chipax.monto_total = gasto_rindegastos.net
							AND compra_chipax.fecha_emision = gasto_rindegastos.issue_date")
            ->leftJoin("gasto_chipax", "gasto_completa_rindegastos.nro_documento = gasto_chipax.num_documento
                        AND gasto_chipax.monto = gasto_rindegastos.net
                        AND gasto_chipax.fecha = gasto_rindegastos.issue_date")
            ->leftJoin("honorario_chipax", "honorario_chipax.numero_boleta = gasto_completa_rindegastos.nro_documento
                        AND honorario_chipax.monto_liquido = gasto_rindegastos.net
                        AND honorario_chipax.fecha_emision = gasto_rindegastos.issue_date")
            ->leftJoin("remuneracion_chipax", "remuneracion_chipax.id LIKE gasto_completa_rindegastos.nro_documento", [])
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
            ->andWhere("remuneracion_chipax.id IS NULL AND compra_chipax.id IS NULL AND gasto_chipax.id IS NULL AND honorario_chipax.id IS NULL")
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

    public function actionSetComentario() {
        $json = isset($_POST["comentario"]) ? $_POST["comentario"] : file_get_contents("php://input");
        $c = json_decode($json);

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        // Si viene el ID entonces solo actualizo
        if (isset($c->id)) {
            $id = $c->id;
            $valor = $c->valor;

            $comentario = ComentariosSincronizador::findOne($id);
            if (isset($comentario)) {
                $comentario->comentario = trim($valor);
                return $comentario->save() ? "OK" : "NOT OK";
            }
        } else {
            // Si no viene el ID pueden pasar 2 cosas
            // Opción 1: Acaba de agregar un comentario y se da cuenta de que quiere modificarlo. No viene el ID, pero sí es una actualización
            $comentario = ComentariosSincronizador::find()->where(
                "monto = :m AND fecha = :f AND nro_documento = :n",
                [":m" => $c->monto, ":f" => $c->fecha, ":n" => $c->nroDoc]
            )->one();
            if (isset($comentario)) {
                $comentario->comentario = trim($c->valor);
            } else {
                // Opción 2: se trata de un nuevo registro
                $comentario = new ComentariosSincronizador();
                $comentario->nro_documento = $c->nroDoc;
                $comentario->monto = $c->monto;
                $comentario->fecha = $c->fecha;
                $comentario->comentario = trim($c->valor);
            }

            return $comentario->save() ? "OK" : "NOT OK";
        }

        return "NOT OK";
    }

    public function actionNewComentario() {
        $data = isset($_POST["comentario"]) ? $_POST["comentario"] : file_get_contents("php://input");
        $c = json_decode($data);

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if (isset($data)) {
            $comentario = new ComentariosSincronizador();
            $comentario->nro_documento = $c->nroDoc;
            $comentario->monto = $c->monto;
            $comentario->fecha = $c->fecha;
            $comentario->comentario = $c->valor;

            return $comentario->save() ? "OK" : "NOT OK";
        } else
            return "NOT OK";
    }
}
