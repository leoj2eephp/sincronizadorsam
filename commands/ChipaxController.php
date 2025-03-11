<?php

namespace app\commands;

use app\models\CategoriaChipax;
use yii\console\Controller;
use yii\console\ExitCode;

use app\models\ChipaxApiService;
use app\models\EmpresaChipax;
use app\models\LineaNegocioChipax;
use Yii;

class ChipaxController extends Controller {

	public function actionIndex() {
		set_time_limit(0);
		$chipaxApiService = new ChipaxApiService();

		Yii::debug("Iniciando sincronización de datos con Chipax", "chipax");
		Yii::$app->db->createCommand()->truncateTable("linea_negocio_chipax")->execute();
		$lineasNegocio = $chipaxApiService->getLineasNegocio(EmpresaChipax::OTZI);
		$lineasNegocio2 = $chipaxApiService->getLineasNegocio(EmpresaChipax::CONEJERO_MAQUINARIAS_SPA);
		LineaNegocioChipax::sincronizarDatos($lineasNegocio);
		LineaNegocioChipax::sincronizarDatos($lineasNegocio2);
		Yii::debug("Líneas de Negocio sincronizadas", "chipax");
		// Borro todas las categorías para volver a insertar según lo obtenido desde la API
		Yii::$app->db->createCommand()->truncateTable("categoria_chipax")->execute();
		Yii::debug("Sincronizando categorías", "chipax");
		$chipaxApiService->sincronizarCategorias(EmpresaChipax::OTZI);
		$chipaxApiService->sincronizarCategorias(EmpresaChipax::CONEJERO_MAQUINARIAS_SPA);
		Yii::debug("Categorías sincronizadas", "chipax");

		Yii::debug("Sincronizando datos de Chipax", "chipax");
		$chipaxApiService->limpiarTablas();
		$chipaxApiService->sincronizarChipaxData(EmpresaChipax::OTZI);
		$chipaxApiService->sincronizarChipaxData(EmpresaChipax::CONEJERO_MAQUINARIAS_SPA);
		$chipaxApiService->sincronizarComprasData(EmpresaChipax::CONEJERO_MAQUINARIAS_SPA);
		Yii::debug("Datos sincronizados", "chipax");

		return ExitCode::OK;
	}
}
