<?php

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;

use app\models\GastoRindegastos;
use app\models\RindeGastosApiService;
use Yii;

class RindeGastosController extends Controller {

	public function actionIndex() {
		set_time_limit(0);
		Yii::$app->db->createCommand("SET FOREIGN_KEY_CHECKS = 0")->execute();
        Yii::$app->db->createCommand()->truncateTable("gasto_rindegastos")->execute();
        Yii::$app->db->createCommand()->truncateTable("gasto_completa_rindegastos")->execute();
        Yii::$app->db->createCommand("SET FOREIGN_KEY_CHECKS = 1")->execute();
        $json = $this->getExpenses();
        $header = $json->Records;
        for ($i = 1; $i <= $header->Pages; $i++) {
            if ($i == 1) {
                GastoRindegastos::sincronizarGastos($json);
            } else {
                $otherjson = $this->getExpenses($i);
                GastoRindegastos::sincronizarGastos($otherjson);
            }
        }

		return ExitCode::OK;
	}

	private function getExpenses($page = 1) {
        $rindeApi = new RindeGastosApiService(Yii::$app->params["rindeGastosToken"]);
        //$params["Since"] = "2020-01-01";
        $params["Status"] = 1;
        $params["Page"] = $page;
        return json_decode($rindeApi->getExpenses($params));
    }
}
