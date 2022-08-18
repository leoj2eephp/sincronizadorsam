<?php

namespace app\controllers;

use app\components\Helper;
use app\models\RemuneracionesSam;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * RemuneracionesSamController implements the CRUD actions for RemuneracionesSam model.
 */
class RemuneracionesSamController extends Controller {
    /**
     * @inheritDoc
     */
    public function behaviors() {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all RemuneracionesSam models.
     *
     * @return string
     */
    public function actionIndex() {
        $fecha_desde = date("Y-01-01");
        $fecha_hasta = date("Y-m-d");
        $agrupado = "";

        if (Yii::$app->request->isPost) {
            $fecha_desde = Helper::formatToDBDate(null !== (\Yii::$app->request->post("fecha_desde")) ? \Yii::$app->request->post("fecha_desde") : "");
            $fecha_hasta = Helper::formatToDBDate(null !== (\Yii::$app->request->post("fecha_hasta")) ? \Yii::$app->request->post("fecha_hasta") : "");
            $agrupado = Yii::$app->request->post("agrupado");
        }

        $group_by = "";
        if ($agrupado == 1)
            $group_by = "equipoPropio.id, equipoArrendado.id, camionPropio.id, camionArrendado.id"; // Aquí tiene que ser por vehículo
        else if ($agrupado == 2)
            $group_by = "nombre_proveedor";
        else if ($agrupado == 3)
            $group_by = "faena.nombre";
        else if ($agrupado == 4)
            $group_by = "faena.nombre, equipoPropio.id, equipoArrendado.id, camionPropio.id, camionArrendado.id";
        else if ($agrupado == 5)
            $group_by = "faena.nombre, nombre_proveedor";
        else if ($agrupado == 6)
            $group_by = "nombre_proveedor, equipoPropio.id, equipoArrendado.id, camionPropio.id, camionArrendado.id";

        $paramsFecha = [":desde" => $fecha_desde, ":hasta" => $fecha_hasta];
        $remuneraciones = RemuneracionesSam::find()
            ->joinWith(["camionArrendado", "camionPropio", "equipoArrendado", "equipoPropio"])  //, "operador", "chofer"])
            ->select($group_by != "" ? "remuneraciones_sam.*, faena.nombre, SUM(neto) AS neto" : "")
            ->leftJoin("faena", "faena.id = remuneraciones_sam.faena_id AND faena.vigente = :si", [":si" => "SÍ"])
            /* ->leftJoin("operador", "operador.id = remuneraciones_sam.operador_id")
            ->leftJoin("chofer", "chofer.id = remuneraciones_sam.chofer_id") */
            /* ->leftJoin("equipopropio ep", "ep.id = remuneraciones_sam.equipoPropio_id")
            ->leftJoin("equipoarrendado ea", "ea.id = remuneraciones_sam.equipoArrendado_id")
            ->leftJoin("camionpropio cp", "cp.id = remuneraciones_sam.camionPropio_id")
            ->leftJoin("camionarrendado ca", "ca.id = remuneraciones_sam.camionArrendado_id") */
            ->where("fecha_rendicion >= :desde AND fecha_rendicion <= :hasta", $paramsFecha)
            ->groupBy("neto" . $group_by != "" ? (", " . $group_by) : "")
            ->all();
        /*
        echo "<pre>";
        print_r($remuneraciones);
        die;
*/
        return $this->render('index', [
            "fecha_desde" => $fecha_desde,
            "fecha_hasta" => $fecha_hasta,
            "agrupado" => $agrupado,
            'remuneraciones' => $remuneraciones,
        ]);
    }

    public function actionDetail() {
        $data = isset($_POST["data"]) ? $_POST["data"] : file_get_contents("php://input");
        $c = json_decode($data);

        $fecha_desde = isset($c->fecha_desde) && $c->fecha_desde != "" ? Helper::formatToDBDate($c->fecha_desde) : "";
        $fecha_hasta = isset($c->fecha_hasta) && $c->fecha_hasta != "" ? Helper::formatToDBDate($c->fecha_hasta) : "";

        switch ($c->agrupado) {
            case 1:
                if ($c->data[4] == "EP")
                    $condition = ["equipoPropio_id" => $c->data[7]];
                else if ($c->data[4] == "EA")
                    $condition = ["equipoArrendado_id" => $c->data[7]];
                else if ($c->data[4] == "CP")
                    $condition = ["camionPropio_id" => $c->data[7]];
                else if ($c->data[4] == "CA")
                    $condition = ["camionArrendado_id" => $c->data[7]];
                break;
            case 2:
                if ($c->data[5] != "")
                    $condition = ["chofer_id" => $c->data[5]];
                else if ($c->data[6] != "")
                    $condition = ["operador_id" => $c->data[6]];
                break;
            case 3:
                if ($c->data[8] != "")
                    $condition = ["faena_id" => $c->data[8]];
                else
                    $condition = "faena_id IS NULL";
                break;
            case 6:
                if ($c->data[4] == "EP")
                    $param1 = ["equipoPropio_id" => $c->data[7]];
                else if ($c->data[4] == "EA")
                    $param1 = ["equipoArrendado_id" => $c->data[7]];
                else if ($c->data[4] == "CP")
                    $param1 = ["camionPropio_id" => $c->data[7]];
                else if ($c->data[4] == "CA")
                    $param1 = ["camionArrendado_id" => $c->data[7]];

                if ($c->data[5] != "")
                    $param2 = ["chofer_id" => $c->data[5]];
                else if ($c->data[6] != "")
                    $param2 = ["operador_id" => $c->data[6]];

                $condition = array_merge($param1, $param2);
                break;
            default:
                $condition = ["remuneraciones_sam.id" => $c->data[9]];
                break;
        }

        $remus = RemuneracionesSam::find()
            ->select("remuneraciones_sam.*, faena.nombre")
            ->leftJoin("faena", "faena.id = remuneraciones_sam.faena_id AND faena.vigente = :si", [":si" => "SÍ"])
            ->where(
                "fecha_rendicion >= :desde AND fecha_rendicion <= :hasta",
                [":desde" => $fecha_desde, ":hasta" => $fecha_hasta]
            )
            ->andWhere($condition)
            ->all();

        Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;
        if (isset($remus)) {
            $vista = "_detalleRemuneracion";
        } else {
            $vista = "_error";
        }
        $contenido = $this->renderPartial($vista, [
            "remuneraciones" => $remus
        ]);

        return $contenido;
    }

    /**
     * Finds the RemuneracionesSam model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return RemuneracionesSam the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = RemuneracionesSam::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
