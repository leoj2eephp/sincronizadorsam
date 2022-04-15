<?php

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;

use app\models\ChipaxApiService;
use app\models\LineaNegocioChipax;

class ChipaxController extends Controller {

	public function actionIndex() {
		set_time_limit(0);
		$chipaxApiService = new ChipaxApiService();
		$lineasNegocio = $chipaxApiService->getLineasNegocio();
		LineaNegocioChipax::sincronizarDatos($lineasNegocio);
		$chipaxApiService->sincronizarCategorias();
		$chipaxApiService->sincronizarChipaxData();

		return ExitCode::OK;
	}
}
