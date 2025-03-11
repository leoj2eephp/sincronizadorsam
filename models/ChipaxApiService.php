<?php

namespace app\models;

use Exception;
use Yii;
use yii\httpclient\Client;

class ChipaxApiService {

    public $otziToken;
    public $comaToken;

    public function limpiarTablas() {
        Yii::$app->db->createCommand("SET FOREIGN_KEY_CHECKS = 0")->execute();
        Yii::$app->db->createCommand()->truncateTable("prorrata_chipax")->execute();
        Yii::$app->db->createCommand()->truncateTable("compra_chipax")->execute();
        Yii::$app->db->createCommand()->truncateTable("gasto_chipax")->execute();
        Yii::$app->db->createCommand()->truncateTable("honorario_chipax")->execute();
        Yii::$app->db->createCommand()->truncateTable("remuneracion_chipax")->execute();
        Yii::$app->db->createCommand("SET FOREIGN_KEY_CHECKS = 1")->execute();
    }

    public function sincronizarChipaxData($empId) {
        $client = new Client(["baseUrl" => "https://api.chipax.com/flujo-caja/cartolas"]);

        $fecha_desde = date("Y-m-d", strtotime("2022-01-01"));
        $fecha_hasta = date("Y-m-d");
        $request = $client->createRequest()
            ->setHeaders(['content-type' => 'application/json'])
            ->addHeaders(['Authorization' => 'JWT ' . $this->getToken($empId)["token"]])
            //->setData(["startDate" => '2021-06-01T00:00:00.000Z'])
            ->setData(["startDate" => $fecha_desde . 'T00:00:00.000Z', "endDate" => $fecha_hasta . 'T23:59:59.000Z'])
            //->setData(["startDate" => date('Y-m-d\TH:i:s')])
            ->send();

        for ($i = 1; $i <= $request->getData()["pages"]; $i++) {
            $request = $client->createRequest()
                ->setHeaders(['content-type' => 'application/json'])
                ->addHeaders(['Authorization' => 'JWT ' . $this->getToken($empId)["token"]])
                ->setData([
                    "startDate" => $fecha_desde . 'T00:00:00.000Z',
                    "endDate" => $fecha_hasta . 'T23:59:59.000Z',
                    "page" => $i
                ])->send();
            try {
                //FlujoCajaCartola::convert2Model($request->getData()["docs"]);
                FlujoCajaCartola::convertAll2Model($request->getData()["docs"], $empId);
            } catch (Exception $ex) {
                echo $ex->getMessage();
            }
        }
    }

    public function sincronizarComprasData($empId) {
        $client = new Client(["baseUrl" => "https://api.chipax.com/v2/compras"]);
        $request = $client->createRequest()
            ->setHeaders(['content-type' => 'application/json'])
            ->addHeaders(['Authorization' => 'JWT ' . $this->getToken($empId)["token"]])
            ->send();

        for ($i = 1; $i <= $request->getData()["paginationAttributes"]["totalPages"]; $i++) {
            $request = $client->createRequest()
                ->setHeaders(['content-type' => 'application/json'])
                ->addHeaders(['Authorization' => 'JWT ' . $this->getToken($empId)["token"]])
                ->setData(["page" => $i])->send();
            try {
                CompraChipax::convertToModel($request->getData()["items"], $empId);
            } catch (Exception $ex) {
                echo $ex->getMessage();
            }
        }
    }

    public function getLineasNegocio($empId) {
        $client = new Client(["baseUrl" => "https://api.chipax.com/lineas-negocio"]);
        $request = $client->createRequest()
            ->setHeaders(['content-type' => 'application/json'])
            ->addHeaders(['Authorization' => 'JWT ' . $this->getToken($empId)["token"]])
            ->send();

        $resultado = array();
        foreach ($request->getData() as $linea) {
            $lineaNegocio = new LineaNegocioChipax();

            $lineaNegocio->id = $linea["id"];
            $lineaNegocio->nombre = $linea["nombre"];
            $lineaNegocio->default = $linea["default"];
            $lineaNegocio->cerrada = isset($linea["cerrada"]) ? date("Y-m-d", strtotime($linea["cerrada"])) : null;
            $lineaNegocio->deleted = $linea["deleted"];
            $lineaNegocio->empresa_chipax_id = $empId;

            $resultado[] = $lineaNegocio;
        }
        return $resultado;
    }

    public function sincronizarCategorias($empId) {
        $client = new Client(["baseUrl" => "https://api.chipax.com/flujo-caja/categorias"]);
        $request = $client->createRequest()
            ->setHeaders(['content-type' => 'application/json'])
            ->addHeaders(['Authorization' => 'JWT ' . $this->getToken($empId)["token"]])
            ->send();

        foreach ($request->getData() as $categoria) {
            if (!is_string($categoria["id"])) {
                $cat = new CategoriaChipax();
                $cat->id = $categoria["id"];
                $cat->nombre = $categoria["nombre"];
                $cat->parent_id = isset($categoria["parent_id"]) ? $categoria["parent_id"] : -1;
                $cat->tipo_cuenta_id = isset($categoria["tipo_cuenta_id"]) ? $categoria["tipo_cuenta_id"] : -1;
                $cat->model_name = isset($categoria["modelName"]) ? $categoria["modelName"] : "";
                $cat->parent_model_name = isset($categoria["parentModelName"]) ? $categoria["parentModelName"] : "";
                $cat->comp_id = isset($categoria["compId"]) ? $categoria["compId"] : -1;
                $cat->parent_comp_id = isset($categoria["parentCompId"]) ? $categoria["parentCompId"] : -1;
                $cat->depth = isset($categoria["depth"]) ? $categoria["depth"] : -1;
                $cat->has_children = isset($categoria["hasChildren"]) ? $categoria["hasChildren"] : false;
                $cat->empresa_chipax_id = $empId;

                if (!$cat->save()) {
                    print_r("Hubo un error al intentar insertar una categoría.. ");
                    echo "Error: " . join(", ", $cat->getFirstErrors());
                }
            }
        }
    }

    private function getToken($empId) {
        if ($empId == 1) {
            if (!isset($this->otziToken) || empty($this->otziToken)) {
                $this->otziToken = $this->generateOtziToken($empId);
            }
            return $this->otziToken;
        } else {
            if (!isset($this->comaToken) || empty($this->comaToken)) {
                $this->comaToken = $this->generateComaToken($empId);
            }
            return $this->comaToken;
        }
    }

    private function generateOtziToken() {
        $client = new Client();
        $this->otziToken = $client->createRequest()
            ->setMethod("POST")
            ->setUrl("https://api.chipax.com/login")
            ->setData(["app_id" => \Yii::$app->params["app_id"], "secret_key" => \Yii::$app->params["secret_key"]])
            ->send();

        return $this->otziToken->getData();
    }

    private function generateComaToken() {
        $client = new Client();
        $this->comaToken = $client->createRequest()
            ->setMethod("POST")
            ->setUrl("https://api.chipax.com/login")
            ->setData(["app_id" => \Yii::$app->params["app_id_conejero_spa"], "secret_key" => \Yii::$app->params["secret_key_conejero_spa"]])
            ->send();

        return $this->comaToken->getData();
    }
}
