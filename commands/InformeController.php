<?php

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;

use app\models\ChipaxApiService;
use app\models\InformeGastoRindegastos;
use app\models\LineaNegocioChipax;
use app\models\RindeGastosApiService;
use Yii;

class InformeController extends Controller {

	public function actionIndex() {
		set_time_limit(0);
		Yii::$app->db->createCommand("SET FOREIGN_KEY_CHECKS = 0")->execute();
		Yii::$app->db->createCommand()->truncateTable("informe_gasto_rindegastos")->execute();
		Yii::$app->db->createCommand("SET FOREIGN_KEY_CHECKS = 1")->execute();
		$json = $this->getInforme();
		$header = $json->Records;
		for ($i = 1; $i <= $header->Pages; $i++) {
			if ($i == 1) {
				InformeGastoRindegastos::sincronizarInformes($json);
			} else {
				$otherjson = $this->getInforme($i);
				InformeGastoRindegastos::sincronizarInformes($otherjson);
			}
		}

		return ExitCode::OK;
	}

	private function getInforme($page = 1) {
		$rindeApi = new RindeGastosApiService(Yii::$app->params["rindeGastosToken"]);
		//$params["Since"] = "2020-01-01";
		//$params["Status"] = 1;
		$params["Page"] = $page;
		return json_decode($rindeApi->getExpenseReports($params));
	}
}
