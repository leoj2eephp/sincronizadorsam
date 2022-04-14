<?php

namespace app\models;

use phpDocumentor\Reflection\Types\String_;
use Yii;
use yii\httpclient\Client;

class ChipaxApiService {


    public function sincronizarChipaxData() {
        $client = new Client(["baseUrl" => "https://api.chipax.com/flujo-caja/cartolas"]);

        /*         $fecha_desde = null !== $start_date ? $start_date : date("Y-m-d", strtotime(date("Y-m-d") . " - 1 month"));
        $fecha_hasta = null !== $end_date ? $end_date : date("Y-m-d"); */
        $fecha_desde = date("Y-m-d", strtotime("2022-03-01"));
        $fecha_hasta = date("Y-m-d");
        $request = $client->createRequest()
            ->setHeaders(['content-type' => 'application/json'])
            ->addHeaders(['Authorization' => 'JWT ' . $this->getToken()["token"]])
            //->setData(["startDate" => '2021-06-01T00:00:00.000Z'])
            ->setData(["startDate" => $fecha_desde . 'T00:00:00.000Z', "endDate" => $fecha_hasta . 'T23:59:59.000Z'])
            //->setData(["startDate" => date('Y-m-d\TH:i:s')])
            ->send();

        if ($request->getData()["pages"] > 1) {
            for ($i = 1; $i <= $request->getData()["pages"]; $i++) {
                $request = $client->createRequest()
                    ->setHeaders(['content-type' => 'application/json'])
                    ->addHeaders(['Authorization' => 'JWT ' . $this->getToken()["token"]])
                    ->setData([
                        "startDate" => $fecha_desde . 'T00:00:00.000Z', "endDate" => $fecha_hasta . 'T23:59:59.000Z',
                        "page" => $i
                    ])->send();
                $flujo[] = FlujoCajaCartola::convert2Model($request->getData()["docs"], $fecha_desde, $fecha_hasta);
            }
        } else {
            $flujo[] = FlujoCajaCartola::convert2Model($request->getData()["docs"], $fecha_desde, $fecha_hasta);
        }
    }

    public function getLineasNegocio() {
        $client = new Client(["baseUrl" => "https://api.chipax.com/lineas-negocio"]);
        $request = $client->createRequest()
            ->setHeaders(['content-type' => 'application/json'])
            ->addHeaders(['Authorization' => 'JWT ' . $this->getToken()["token"]])
            ->send();

        $resultado = array();
        foreach ($request->getData() as $linea) {
            $lineaNegocio = new LineaNegocioChipax();

            $lineaNegocio->id = $linea["id"];
            $lineaNegocio->nombre = $linea["nombre"];
            $lineaNegocio->default = $linea["default"];
            $lineaNegocio->cerrada = $linea["cerrada"];
            $lineaNegocio->deleted = $linea["deleted"];

            $resultado[] = $lineaNegocio;
        }
        return $resultado;
    }

    public function sincronizarCategorias() {
        CategoriaChipax::deleteAll();

        $client = new Client(["baseUrl" => "https://api.chipax.com/flujo-caja/categorias"]);
        $request = $client->createRequest()
            ->setHeaders(['content-type' => 'application/json'])
            ->addHeaders(['Authorization' => 'JWT ' . $this->getToken()["token"]])
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

                if (!$cat->save()) {
                    print_r("Hubo un error al intentar insertar una categoría.. ");
                    echo "Error: " . join(", ", $cat->getFirstErrors());
                }
            }
        }
    }

    private function getToken() {
        if (!isset($this->token) || empty($this->token)) {
            $this->token = $this->generateToken();
        }

        return $this->token;
    }

    private function generateToken() {
        $client = new Client();
        $this->token = $client->createRequest()
            ->setMethod("POST")
            ->setUrl("https://api.chipax.com/login")
            ->setData(["app_id" => \Yii::$app->params["app_id"], "secret_key" => \Yii::$app->params["secret_key"]])
            ->send();

        return $this->token->getData();
    }
}
