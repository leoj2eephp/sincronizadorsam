<?php

namespace app\controllers;

use app\models\CategoriaChipax;
use app\models\CompraChipax;
use app\models\GastoChipax;
use app\models\HonorarioChipax;
use app\models\LineaNegocioChipax;
use app\models\PoliticaGastosForm;
use app\models\ProrrataChipax;
use app\models\RemuneracionChipax;
use Exception;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;

class ModalController extends Controller {

    public function actionIndex() {
        return $this->render('/chipax/_sincronizacionSam');
    }

    public function actionSyncSam() {
        $tipo = $_GET["tipo"];
        $prorrata = null;
        $compra = null;
        $gasto = null;
        $honorario = null;
        $remuneracion = null;
        switch ($tipo) {
            case "compra":
                $compra = CompraChipax::find()->one();
                $prorrata = ProrrataChipax::find()->where("compra_chipax_id = :compra_id", [":compra_id" => $compra->id])->one();
                break;
            case "gasto":
                $compra = GastoChipax::find()->one();
                $prorrata = ProrrataChipax::find()->where("gasto_chipax_id = :compra_id", [":compra_id" => $compra->id])->one();
                break;
            case "honorario":
                $compra = HonorarioChipax::find()->one();
                $prorrata = ProrrataChipax::find()->where("honorario_chipax_id = :compra_id", [":compra_id" => $compra->id])->one();
                break;
            case "remuneracion":
                $compra = RemuneracionChipax::find()->one();
                $prorrata = ProrrataChipax::find()->where("remuneracion_chipax_id = :compra_id", [":compra_id" => $compra->id])->one();
                break;
            default:
                $tipo = "";
                break;
        }
        $remu = false;  // indica si la visualización será tipo remuneración o no
        // Está obteniendo la información que está en sesión (para cargarla una sola vez), sobre los gastos
        $model = PoliticaGastosForm::fillData();
        $categoria = CategoriaChipax::findOne($prorrata->cuenta_id);
        // Llamar a la API de SAM para obtener los centros de costos
        /* $faenas = $model->getCentrosCostosFaenas($categoria->nombre);
        $faenas_decoded = json_decode($faenas);
        if (isset($faenas_decoded)) {
            $model->faena = json_decode($faenas)->faenas;
        }
        */
        // Llamo a la API de Sam para obtener los tipos de de combustibles
        /* $tipo_combustibles_sam = $model->getTiposCombustibles();
        $tc_decoded = json_decode($tipo_combustibles_sam);
        if (isset($tc_decoded) && $tc_decoded->status == "OK") {
            $model->tipo_combustibles = $tc_decoded->tipos_combustibles;
        } */

        $model->categoria_id = $categoria->id;
        $model->categoria = $categoria->nombre;
        $model->nota = "";
        //$model->nota = "TESTING API!!";
        $model->neto = $prorrata->monto;

        if (null !== $compra) {
            $model->nombre_proveedor = $compra->razon_social;
            $model->rut_proveedor = $compra->rut_emisor;
            $model->nro_documento = $compra->folio;
            $model->fecha = $compra->fecha_emision;
            if ($compra->tipo == 33) {
                $model->tipo_documento_seleccionado = "Factura Afecta";
            } else {
                $model->tipo_documento_seleccionado = "Factura Exenta";
            }
        } else if (null !== $gasto) {
            $model->rut_proveedor = "";
            $model->nombre_proveedor = $gasto["proveedor"];
            $model->nro_documento = $gasto["num_documento"];
            $model->fecha = $gasto["fecha"];
            $model->tipo_documento_seleccionado = "Otros";
        } else if (null !== $honorario) {
            $model->nro_documento = $honorario["numero_boleta"];
            $model->rut_proveedor = $honorario["rut_emisor"];
            $model->nombre_proveedor = $honorario["nombre_emisor"];
            $model->fecha = $honorario["fecha_emision"];
            $model->tipo_documento_seleccionado = "Otros";
        } else if (null !== $remuneracion) {
            $model->nro_documento = $remuneracion["id"];
            $model->nombre_proveedor = $remuneracion["empleado"]["nombre"] . " " . $remuneracion["empleado"]["apellido"];
            $model->fecha = $remuneracion["periodo"];
            $model->categoria = CategoriaChipax::findOne($remuneracion["prorratas"][0]["cuenta_id"])->nombre;
            $model->linea_negocio = LineaNegocioChipax::findOne($remuneracion["prorratas"][0]["linea_negocio_id"])->nombre;
            $model->neto = $remuneracion["prorratas"][0]["monto"];
            $remu = true;
        }

        Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;
        if (!$remu) {
            $vista = "_sincronizacionSam";
        } else {
            $vista = "_sincronizacionSamRemuneraciones";
        }
        return $this->renderAjax($vista, [
            "model" => $model,
            "indice" => $_GET["i"]
        ]);
    }

    public function actionSyncSamPost() {
        try {
            $model = new \app\models\PoliticaGastosForm();
            $model->load(Yii::$app->request->post());

            $vehiculosValores = array();
            foreach ($model->vehiculos_seleccionados as $i => $v) {
                $vehiculo = new \app\models\VehiculoChipax();
                $vehiculo->nombre = $v;
                $vehiculo->valor = $model->valores_vehiculos[$i];

                $vehiculosValores[] = $vehiculo;
            }

            $model->vehiculos_seleccionados = $vehiculosValores;

            $result = $model->sendData();
            $respuesta = json_decode($result);
            Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;

            if ($respuesta->status == "OK") {
                return $this->renderAjax('_sincroOK', [
                    "message" => "OK",
                ]);
            } else if ($respuesta->status == "ERROR") {
                return $this->renderAjax('_sincroError', [
                    "message" => $respuesta->message,
                ]);
            } else {
                return $this->renderAjax('_sincroError', [
                    "message" => $result,
                ]);
            }
        } catch (Exception $ex) {
            echo $ex->message;
            die;
        }
    }

    public function actionUploadDte() {
        $model = new \app\models\DocumentoAttachForm();
        if (Yii::$app->request->isPost) {
            $model->file = \yii\web\UploadedFile::getInstances($model, 'file');
            if ($model->file != null && count($model->file) > 0) {
                if ($model->saveDocument()) {
                    return $this->renderAjax('_sincroOK', [
                        "message" => "Se subieron los archivos DTE exitosamente",
                    ]);
                } else {
                    return $this->renderAjax('_sincroError', [
                        "message" => join(",", $model->getFirstErrors()),
                    ]);
                }
            } else {
                echo 'ERROR!!';
            }
        }

        Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;
        return $this->renderAjax('_uploadDTE', [
            "model" => $model
        ]);
    }
}
