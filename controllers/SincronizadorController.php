<?php

namespace app\controllers;

use app\commands\ChipaxController;
use app\components\Helper;
use app\models\ComentariosSincronizador;
use app\models\CompraChipax;
use app\models\FlujoCajaCartola;
use app\models\GastoChipax;
use app\models\GastoCompleta;
use app\models\GastoRindegastos;
use app\models\HonorarioChipax;
use app\models\RemuneracionChipax;
use app\models\RindeGastosApiService;
use app\models\User;
use Yii;
use yii\base\Controller;
use yii\filters\VerbFilter;
use app\models\InformeGastoRindegastos;

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
        $result = Yii::$app->db->createCommand("CALL compras_maquinarias_combustibles(:from_date, :to_date)")
            ->bindValue(':from_date', $fecha_desde)
            ->bindValue(':to_date', $fecha_hasta)
            ->queryAll();
        $model->compras = CompraChipax::convertSPResultToArrayModel($result);

        foreach ($model->compras as $compra) {
            // FIX! Este bloque es para aquellos casos que por SQL no pude traer a la vez datos que sí tenían asociados gastos, pero que
            // por la condición LEFT JOIN nunca pude traerlos. Sucede en pocos casos en que los gastos de rinde gastos vienen divididos
            if (!isset($compra->fecha_gasto) && count($compra->gastoCompleta) > 0) {
                $gastoCompletaReintento = GastoCompleta::find()
                    ->innerJoin("gasto", "gasto.id = gasto_completa.gasto_id")
                    ->where([
                        "gasto_completa.nro_documento" => $compra->folio,
                        "gasto.issue_date" => $compra->fecha_emision
                    ])->all();
                $sumaGastoCompleta = 0;
                foreach ($gastoCompletaReintento as $gastoCompleta) {
                    $sumaGastoCompleta += $gastoCompleta->monto_neto + $gastoCompleta->impuesto_especifico;
                }

                foreach ($compra->spProrrataChipax as $p) {
                    if (Helper::diferenciaOchoPesos($p->monto, $sumaGastoCompleta)) {
                        $compra->sincronizado = 1;
                        $compra->rindeGastoDividido = 1;
                        $compra->rindeGastoData = $gastoCompletaReintento;
                        break;
                    }
                }
                // Si spProrrataChipax no trae los valores divididos, pero sí sumados, entonces comparo por el valor sumado
                if (count($compra->spProrrataChipax) == 1) {
                    if (Helper::diferenciaOchoPesos($compra->spProrrataChipax[0]->monto_sumado, $sumaGastoCompleta)) {
                        $compra->sincronizado = 1;
                        $compra->rindeGastoDividido = 1;
                        $compra->rindeGastoData = $gastoCompletaReintento;
                    }
                }
            } else if (count($compra->gastoCompleta) > 0) {
                // BUSCO dentro de cada Prorrata asociada para encontrar asociaciones erróneas de datos sincronizados
                foreach ($compra->spProrrataChipax as $p) {
                    // Hay ocasiones en que el monto sumado está tomando en cuenta gastos de diferentes fechas. Así que reviso
                    // cuando hay monto sumado que todas sus fechas apunten al mismo día.
                    if ($p->monto_sumado > 0) {
                        foreach ($compra->spProrrataChipax as $p2) {
                            if ($compra->fecha_emision == $p2->periodo) {
                                $p->monto_sumado += $p2->monto;
                            }
                        }
                    }

                    // BUSCO todas las coincidencias registradas en GastoCompleta (que podrían ser erróneas por el nro_documento)
                    foreach ($compra->gastoCompleta as $gastoCompleta) {
                        if (!array_key_exists($p->cuenta_id, FlujoCajaCartola::CATEGORIAS_REMUNERACIONES_CHIPAX)) {
                            // COMPRA
                            // Esto es para los casos en que en chipax viene el monto dividido en 2 registros..
                            //$montoProrrata = $p->monto_sumado > 0 ? $p->monto_sumado : $p->monto;
                            $valor = 0;
                            if ($p->monto_sumado > 0) {
                                if (
                                    // ($gastoCompleta->monto_neto >= $p->monto_sumado - 2
                                    //     && $gastoCompleta->monto_neto <= $p->monto_sumado + 2
                                    // ) &&
                                    Helper::diferenciaOchoPesos($gastoCompleta->monto_neto, $p->monto_sumado)
                                    && $gastoCompleta->nro_documento == $compra->folio
                                ) {
                                    $compra->sincronizado = 1;
                                    break;
                                }
                            } else {
                                $valor = $p->neto_impuesto > 0 ? $p->neto_impuesto : $p->monto;
                                if (
                                    // ($valor >= $p->monto - 2
                                    //     && $valor <= $p->monto + 2
                                    // ) &&
                                    Helper::diferenciaOchoPesos($p->monto, $valor) &&
                                    $compra->fecha_gasto == $compra->fecha_emision &&
                                    $gastoCompleta->nro_documento == $compra->folio
                                ) {
                                    $compra->sincronizado = 1;
                                    break;
                                }
                            }

                            // Si aún no se marca como sincronizada, busco si coincide con el valor sumado
                            $sumadoRindeGasto = GastoCompleta::find()
                                ->innerJoin("gasto", "gasto.id = gasto_completa.gasto_id")
                                ->where(["nro_documento" => $gastoCompleta->nro_documento, "gasto.issue_date" => $compra->fecha_gasto])
                                ->sum("monto_neto");
                            $sincDobleRindeGastos = 0;
                            // if ($sumadoRindeGasto >= $p->monto_sumado - 2 && $sumadoRindeGasto <= $p->monto_sumado + 2) {
                            if (Helper::diferenciaOchoPesos($sumadoRindeGasto, $p->monto_sumado)) {
                                $sincDobleRindeGastos = 1;
                                // } else if ($sumadoRindeGasto >= $valor - 2 && $sumadoRindeGasto <= $valor + 2) {
                            } else if (Helper::diferenciaOchoPesos($sumadoRindeGasto, $valor)) {
                                $sincDobleRindeGastos = 1;
                            }

                            $compra->sincronizado = $sincDobleRindeGastos;
                            $compra->rindeGastoDividido = $sincDobleRindeGastos;
                            if ($sincDobleRindeGastos) {
                                $gastoCompleta = GastoCompleta::find()
                                    ->innerJoin("gasto", "gasto.id = gasto_completa.gasto_id")
                                    ->where(["nro_documento" => $gastoCompleta->nro_documento, "gasto.issue_date" => $compra->fecha_gasto])->all();
                                $compra->rindeGastoData = $gastoCompleta;
                            }
                            break;
                        } else {
                            // REMUNERACIÓN
                            if (
                                $gastoCompleta->total_calculado != $compra->monto_total ||
                                $compra->fecha_gasto != $compra->fecha_emision ||
                                $gastoCompleta->nro_documento != $compra->folio
                            ) {
                                $compra->sincronizado = 1;
                                break;
                            }
                        }
                    }
                }
            } else {
                // Aquí valido cuando un folio fue ingresado con ceros adelante.. simplemente le digo que sí está sincronizado, pero
                // en el index le agrego el objeto gastoCompleta, ya que desde aquí no puedo modificarlo
                // $gasto = GastoCompleta::find()->where(["like", "nro_documento", "%000" . $compra->folio, false])->one();
                // $compra->sincronizado = isset($gasto) ? 1 : 0;
                $compra->sincronizado = 0;
            }
        }

        $result = Yii::$app->db->createCommand("CALL gastos_maquinarias_combustibles(:from_date, :to_date)")
            ->bindValue(':from_date', $fecha_desde)
            ->bindValue(':to_date', $fecha_hasta)
            ->queryAll();
        $model->gastos = GastoChipax::convertSPResultToArrayModel($result);
        foreach ($model->gastos as $gasto) {
            if (count($gasto->gastoCompleta) > 0) {
                // BUSCO dentro de cada Prorrata asociada para encontrar asociaciones erróneas de datos sincronizados
                foreach ($gasto->spProrrataChipax as $p) {
                    // BUSCO todas las coincidencias registradas en GastoCompleta (que podrían ser erróneas por el nro_documento)
                    foreach ($gasto->gastoCompleta as $gastoCompleta) {
                        if (!array_key_exists($p->cuenta_id, FlujoCajaCartola::CATEGORIAS_REMUNERACIONES_CHIPAX)) {
                            // COMPRA
                            if (
                                ($gastoCompleta->monto_neto >= $p->monto - 2
                                    && $gastoCompleta->monto_neto <= $p->monto + 2
                                ) &&
                                $gasto->fecha_gasto == $gasto->fecha &&
                                $gastoCompleta->nro_documento == $gasto->num_documento
                            ) {
                                $gasto->sincronizado = 1;
                                break;
                            }
                        } else {
                            // REMUNERACIÓN
                            if (
                                $gastoCompleta->total_calculado != $gasto->monto ||
                                $gasto->fecha_gasto != $gasto->fecha ||
                                $gastoCompleta->nro_documento != $gasto->num_documento
                            ) {
                                $compra->sincronizado = 1;
                                break;
                            }
                        }
                    }
                }
            } else {
                $gasto->sincronizado = 0;
            }
        }

        $result = Yii::$app->db->createCommand("CALL honorarios_maquinarias_combustibles(:from_date, :to_date)")
            ->bindValue(':from_date', $fecha_desde)
            ->bindValue(':to_date', $fecha_hasta)
            ->queryAll();
        $model->honorarios = HonorarioChipax::convertSPResultToArrayModel($result);
        foreach ($model->honorarios as $honorario) {
            if (count($honorario->gastoCompleta) > 0) {
                $honorario->sincronizado = 1;
            } else {
                $honorario->sincronizado = 0;
            }
        }

        $result = Yii::$app->db->createCommand("CALL remuneracion_maquinarias_combustibles(:from_date, :to_date)")
            ->bindValue(':from_date', $fecha_desde)
            ->bindValue(':to_date', $fecha_hasta)
            ->queryAll();
        $model->remuneracions = RemuneracionChipax::convertSPResultToArrayModel($result);
        foreach ($model->remuneracions as $remu) {
            //$gasto = GastoCompleta::find()->where("nro_documento LIKE :id", [":id" => $remu->id])->one();
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
                            AND compra_chipax.monto_total = gasto_rindegastos.total
							AND compra_chipax.fecha_emision = gasto_rindegastos.issue_date")
            ->leftJoin("gasto_chipax", "gasto_completa_rindegastos.nro_documento = gasto_chipax.num_documento
                        AND gasto_chipax.monto = gasto_rindegastos.total
                        AND gasto_chipax.fecha = gasto_rindegastos.issue_date")
            ->leftJoin("honorario_chipax", "honorario_chipax.numero_boleta = gasto_completa_rindegastos.nro_documento
                        AND honorario_chipax.monto_liquido = gasto_rindegastos.total
                        AND honorario_chipax.fecha_emision = gasto_rindegastos.issue_date")
            ->leftJoin("remuneracion_chipax", "remuneracion_chipax.id LIKE gasto_completa_rindegastos.nro_documento", [])
            ->where(
                "issue_date >= :desde AND issue_date <= :hasta",
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

        $mostrarConInforme = Yii::$app->request->post('mostrar_con_informe') === '1';
        if($mostrarConInforme) {
            $rindeGastos = array_filter($rindeGastos, function ($rinde) {
                $informe = InformeGastoRindegastos::findOne($rinde->report_id);
                return isset($informe);
            });
        }
        
        // $rindeGastosFiltrados = array_filter($rindeGastos, function ($rinde) use ($mostrarConInforme) {
        //     $informe = InformeGastoRindegastos::findOne($rinde->report_id);
        //     $tieneInforme = isset($informe);
        //     return $mostrarConInforme ? $tieneInforme : true;
        // });

        return $this->render("rinde-gastos", [
            "fecha_desde" => $fecha_desde,
            "fecha_hasta" => $fecha_hasta,
            "model" => $rindeGastos,
            "mostrarConInforme" => $mostrarConInforme
        ]);
    }

    public function actionSincronizarChipaxData() {
        // if (Yii::$app->user->can("administrador")) {
        Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;
        if (Yii::$app->request->isPost) {
            set_time_limit(0);
            $mensaje = "";
            // $commandChipax = 'php ' . Yii::getAlias('@app/yii') . ' chipax';
            $commandChipax = '/usr/local/bin/php ' . Yii::getAlias('@app/yii') . ' chipax';
            $output = [];
            $exitCode = null;
            exec($commandChipax, $output, $exitCode);
            if ($exitCode === 0) {
                return '<p ok="ok" class="text-center text-xl">
                                Sincronización con Chipax completada! <i class="fa fa-10x fa-check text-success"></i>
                            </p>';
            } else {
                return '<p ok="ok" class="text-center text-xl">
                                Error al sincronizar con Chipax! <i class="fa fa-10x fa-warning text-danger"></i>
                            </p>';
            }
        } else {
            // return $this->render("_sincronizando");
            return "error";
        }
    }

    public function actionSincronizarRindeGastosData() {
        Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;
        if (Yii::$app->request->isPost) {
            set_time_limit(0);
            $commandRindeGastos = '/usr/local/bin/php ' . Yii::getAlias('@app/yii') . ' rinde-gastos';
            $output = [];
            $exitCode = null;
            exec($commandRindeGastos, $output, $exitCode);
            if ($exitCode === 0) {
                return '<p ok="ok" class="text-center text-xl">
                                Sincronización con Rinde Gastos completada! <i class="fa fa-10x fa-check text-success"></i>
                            </p>';
            } else {
                return '<p ok="ok" class="text-center text-xl">
                                Error al sincronizar con Rinde Gastos! <i class="fa fa-10x fa-warning text-danger"></i>
                            </p>';
            }
        }
    }

    public function actionSincronizarInformesData() {
        Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;
        if (Yii::$app->request->isPost) {
            set_time_limit(0);
            $commandInforme = '/usr/local/bin/php ' . Yii::getAlias('@app/yii') . ' informe';
            $output = [];
            $exitCode = null;
            exec($commandInforme, $output, $exitCode);
            if ($exitCode === 0) {
                return '<p ok="ok" class="text-center text-xl">
                                Sincronización de Informes completada! <i class="fa fa-10x fa-check text-success"></i>
                            </p>';
            } else {
                return '<p ok="ok" class="text-center text-xl">
                                Error al sincronizar Informes! <i class="fa fa-10x fa-warning text-danger"></i>
                            </p>';
            }
        }
    }

    private function getExpenses($page = 1) {
        $rindeApi = new RindeGastosApiService(Yii::$app->params["rindeGastosToken"]);
        //$params["Since"] = "2020-01-01";
        $params["Status"] = 1;
        $params["Page"] = $page;
        return json_decode($rindeApi->getExpenses($params));
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
