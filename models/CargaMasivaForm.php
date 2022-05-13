<?php

namespace app\models;

use Exception;
use Yii;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Description of Pagos
 *
 * @author leand
 */
class CargaMasivaForm extends \yii\base\Model {

    const PATH = "documents" . DIRECTORY_SEPARATOR;
    const FILE_NAME = "CargaMasiva.xls";
    const ORIGINAL_FILE_NAME = "ExportTemplate.xlsx";
    const COMPLETE_FILE_PATH = self::PATH . self::FILE_NAME;

    public function generarExcel($datos) {
        $plantillaOriginal = Yii::getAlias("@app") . DIRECTORY_SEPARATOR . "documents" . DIRECTORY_SEPARATOR . static::ORIGINAL_FILE_NAME;
        $reader = IOFactory::createReaderForFile($plantillaOriginal);
        $reader->setReadDataOnly(false);
        $spreadsheet = $reader->load($plantillaOriginal);
        $hoja = $spreadsheet->getActiveSheet();

        foreach ($datos as $indice => $fila) {
            $i = $indice + 6;
            $nro_informe = isset($fila->nro_informe) ? $fila->nro_informe : "";
            $hoja->getCellByColumnAndRow(1, $i, true)->setValue($fila->fecha);
            $hoja->getCellByColumnAndRow(2, $i, true)->setValue(date("Y-m", strtotime($fila->fecha)));
            if ($fila->centro_costo === "Gastos Generales Taller") {
                $hoja->getCellByColumnAndRow(3, $i, true)->setValue("Cop. " . $fila->cuenta);
            } else {
                $hoja->getCellByColumnAndRow(3, $i, true)->setValue($fila->cuenta);
            }

            if (array_key_exists($fila->linea_negocio, FlujoCajaCartola::CATEGORIAS_COMBUSTIBLES_RINDEGASTOS)) {
                $polit = "Centro de Costo/Faena";
            } else {
                $rindeApi = new RindeGastosApiService(Yii::$app->params["rindeGastosToken"]);
                $params['Id'] = $fila->linea_negocio;
                $politica = json_decode($rindeApi->getExpensePolicy($params));

                $polit = $politica->Id != 0 ? $politica->Name : "";
            }
            $hoja->getCellByColumnAndRow(4, $i, true)->setValue($polit);
            $hoja->getCellByColumnAndRow(5, $i, true)->setValue(isset($fila->responsable) ? $fila->responsable : "");
            $hoja->getCellByColumnAndRow(6, $i, true)->setValue(isset($fila->tipo_documento) ? $fila->tipo_documento : "");
            $hoja->getCellByColumnAndRow(7, $i, true)->setValue($fila->proveedor . ". Rendición Folio: " . $nro_informe);
            $hoja->getCellByColumnAndRow(8, $i, true)->setValue($fila->num_documento);
            $hoja->getCellByColumnAndRow(9, $i, true)->setValue($fila->descripcion . ". Rendición Folio: " . $nro_informe);
            $hoja->getCellByColumnAndRow(10, $i, true)->setValue($fila->monto);
            $hoja->getCellByColumnAndRow(11, $i, true)->setValue($fila->moneda);
        }

        $this->saveFile($spreadsheet);
    }

    private function saveFile($spreadsheet) {
        try {
            $folderPath = \Yii::getAlias("@app") . DIRECTORY_SEPARATOR . static::PATH;
            //. $cierreMes->anyo . DIRECTORY_SEPARATOR . $cierreMes->mes . DIRECTORY_SEPARATOR;
            $path = $folderPath . static::FILE_NAME;

            if (!is_dir($folderPath)) {
                mkdir($folderPath, 0777, true);
            } else {
                if (file_exists($path)) {
                    unlink($path);
                }
            }

            $writer = IOFactory::createWriter($spreadsheet, 'Xls');
            $writer->save($path);

            return true;
        } catch (Exception $ex) {
            return false;
        }
    }
}
