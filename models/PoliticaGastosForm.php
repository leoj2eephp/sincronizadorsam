<?php

namespace app\models;

use app\components\Helper;
use Exception;
use Yii;
use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class PoliticaGastosForm extends Model {

    const SERVER = "www.otzi.cl";
    //const SERVER = "localhost";
    //const SERVER = "cristhianmoya.com";

    public $id;
    public $nombre;
    public $nro_campos;
    public $fecha;
    public $tipo_combustibles = array(); // Arreglo de datos que se llena llamando a la API de Sam
    public $tipo_combustible_id;
    public $vehiculos = array();
    public $vehiculos_seleccionados = array();  // Arreglo de instancias de VehiculoChipax
    public $valores_vehiculos = array();    // Arreglo con el valor asociado a los vehículos
    public $unidades = array();
    public $unidad_seleccionada;
    public $tipoDocumento = array();
    public $tipo_documento_seleccionado;
    public $rut_proveedor;
    public $nombre_proveedor;
    public $nro_documento;
    public $folio;  // esto es para aquellos datos que tienen un folio extra
    public $cantidad;
    public $rendidor = array();
    public $rendidor_seleccionado;
    public $faena = array();
    public $faena_seleccionada;
    public $nota;   // equivale a la descripción de Chipax
    public $carguio;
    public $categoria;
    public $categoria_id;
    public $linea_negocio;
    public $linea_negocio_id;
    public $detalle;
    public $neto;
    public $html_factura;
    public $operador_id;
    public $operadores_id;

    /**
     * @return array the validation rules.
     */
    public function rules() {
        return [
            [["nombre", "rut_proveedor", "nombre_proveedor", "categoria", "linea_negocio", "html_factura"], "string"],
            [["nota",], "string", 'max' => 250],
            [[
                "vehiculos_seleccionados", "valores_vehiculos", "unidad_seleccionada", "tipo_documento_seleccionado", "tipo_combustibles",
                "rendidor_seleccionado", "faena_seleccionada", "cantidad", "nro_documento", "fecha", "neto", "folio",
                "tipo_combustible_id", "categoria_id", "linea_negocio_id", "carguio", "operador_id", "operadores_id"
            ], "safe"],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels() {
        return [
            'vehiculo_seleccionado' => 'Vehículo o Equipo',
            'unidad_seleccionada' => 'Unidad',
            'tipo_documento_seleccionado' => 'Tipo de Documento',
            'rendidor_seleccionado' => 'Nombre de quien Rinde',
            'faena_seleccionada' => 'Centro de Costo / Faena',
            'tipo_combustible_id' => "Tipo de Combustible",
            'carguio' => "Carguío",
        ];
    }

    public function sendData() {
        $ch = curl_init();
        //$this->detalle = "TESTING API!!";
        $jsonData = json_encode($this);

        //$url = "http://" . self::SERVER . "/SAMQA/index.php/chipax/add?hash=" . $this->getSamHash();
        $url = "https://" . self::SERVER . "/SAM/index.php/chipax/add?hash=" . $this->getSamHash();
        curl_setopt($ch, CURLOPT_URL, $url);
        // Attach encoded JSON string to the POST fields
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        // Set the content type to application/json
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        // Return response instead of outputting
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = array();
        //$headers[] = "Authorization: Bearer " . $this->getSamHash();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        //return transaction 
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            //Catch cUrl Error
            $result = 'Error:' . curl_error($ch);
        }

        //Close cUrl
        curl_close($ch);

        return $result;
    }

    public function saveRemuneraciones() {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($this->vehiculos_seleccionados as $vehiculo) {
                $rs = Yii::$app->db->createCommand('select max(id) as id from gasto')->queryOne();
                $maxid = 0;
                $maxid = $rs["id"];
                $nextid = 1000000000;
                if ((int)$maxid >= 1000000000) {
                    $nextid = $maxid + 1;
                }

                $gasto = new Gasto();
                $gasto->id = $nextid;
                $gasto->status = 1;
                $gasto->tax = 0;
                $gasto->other_taxes = 0;
                $gasto->category_group = "";
                $gasto->category_code = "";
                $gasto->report_id = null;
                $gasto->expense_policy_id = null;
                $gasto->chipax = 1;

                $gastoImagen = new GastoImagen();
                $gastoImagen->gasto_id = $gasto->id;

                $gastoCompleta = new GastoCompleta();
                $gastoCompleta->gasto_id = $gasto->id;
                $gastoCompleta->vehiculo_equipo = $vehiculo->nombre;
                $gasto->net = $vehiculo->valor;
                $gasto->total = $vehiculo->valor;
                $gastoCompleta->monto_neto = $vehiculo->valor;
                $gastoCompleta->total_calculado = $vehiculo->valor;

                $gasto->issue_date = $this->fecha;
                $gasto->supplier = $this->nombre_proveedor;
                $gasto->category = $this->categoria;
                $gasto->note = $this->nota;
                $gastoCompleta->cantidad = $this->cantidad;

                $faena = $this->faena_seleccionada != "" ? Faena::find($this->faena_seleccionada) : null;
                if (isset($faena)) {
                    $gastoCompleta->centro_costo_faena = $faena->nombre;
                } else {
                    $gastoCompleta->centro_costo_faena = "-- NO ASIGNADA --";
                }

                $gastoCompleta->nombre_quien_rinde = $this->rendidor_seleccionado;
                $gastoCompleta->nro_documento = $this->nro_documento;
                $gastoCompleta->rut_proveedor = $this->rut_proveedor;
                $gastoCompleta->tipo_documento = $this->tipo_documento_seleccionado;
                $gastoCompleta->unidad = $this->unidad_seleccionada;
                $gastoImagen->file_name = $this->html_factura;
                $gastoCompleta->tipoCombustible_id = (int) $this->tipo_combustible_id;

                if (!$gasto->save()) {
                    throw new Exception("No se pudo crear el gasto");
                }
                if (!$gastoCompleta->save()) {
                    throw new Exception("No se pudo crear el gasto completo");
                }
                if (!$gastoImagen->save()) {
                    throw new Exception("No se pudo crear la imagen del gasto");
                }

                // VERIFICAR TIPO DE VEHICULO..
                $equipo_camion = "";
                $v = VehiculoRindegasto::find()->where(["vehiculo" => $vehiculo->nombre])->one();
                if (isset($v->camionarrendado_id)) {
                    $equipo_camion = "CA";
                } else if (isset($v->camionpropio_id)) {
                    $equipo_camion = "CP";
                } else if (isset($v->equipoarrendado_id)) {
                    $equipo_camion = "EA";
                } else if (isset($v->equipopropio_id)) {
                    $equipo_camion = "EP";
                }

                $remuneracion = new RemuneracionesSam();
                $remuneracion->tipo_equipo_camion = $equipo_camion;
                $remuneracion->descripcion = "Remuneración del Mes";
                $remuneracion->neto = $gastoCompleta->monto_neto;
                $remuneracion->documento = substr($gastoCompleta->nro_documento, 0, 100);
                $remuneracion->cantidad = (float) $gastoCompleta->cantidad;
                $remuneracion->unidad = Helper::convertUnidad($gastoCompleta->unidad);
                $remuneracion->fecha_rendicion = $gasto->issue_date;
                $remuneracion->faena_id = $this->faena_seleccionada;
                if (strlen($gastoCompleta->nombre_quien_rinde) > 100) {
                    $remuneracion->nombre = substr($gastoCompleta->nombre_quien_rinde, 0, 100);
                } else {
                    $remuneracion->nombre = $gastoCompleta->nombre_quien_rinde;
                }
                $remuneracion->rut_rinde = " ";
                $remuneracion->cuenta = $gasto->category_code . " - " . $gasto->category;
                $remuneracion->nombre_proveedor = $gasto->supplier;
                $remuneracion->rut_proveedor = $gastoCompleta->rut_proveedor;
                $remuneracion->observaciones = "Registro de Chipax";
                $remuneracion->tipo_documento = Helper::traducirTipoDocumento($gastoCompleta->tipo_documento);
                $remuneracion->rindegastos = 0;
                if ($equipo_camion == "EP") {
                    $remuneracion->equipoPropio_id = $v->equipopropio_id;
                    $remuneracion->operador_id = $vehiculo->operador_id;
                } else if ($equipo_camion == "EA") {
                    $remuneracion->equipoArrendado_id = $v->equipoarrendado_id;
                    $remuneracion->operador_id = $vehiculo->operador_id;
                } else if ($equipo_camion == "CP") {
                    $remuneracion->camionPropio_id = $v->camionpropio_id;
                    $remuneracion->chofer_id = $vehiculo->operador_id;
                } else if ($equipo_camion == "CA") {
                    $remuneracion->camionArrendado_id = $v->camionarrendado_id;
                    $remuneracion->chofer_id = $vehiculo->operador_id;
                }
                $remuneracion->gasto_id = $gasto->id;

                if (!$remuneracion->save()) {
                    throw new Exception(join(", ", $remuneracion->getFirstErrors()));
                }
            }
            $transaction->commit();
        } catch (Exception $ex) {
            $transaction->rollBack();
            return $ex->getMessage();
        }

        $transaction->rollBack();
        return "OK";
    }

    public static function fillData() {
        $form = new PoliticaGastosForm();
        $camionPropio = (new \yii\db\Query())
            ->select(['vehiculo_rindegasto.id', 'vehiculo', 'camionarrendado_id', 'camionpropio_id', 'equipopropio_id', 'equipoarrendado_id'])
            ->from('vehiculo_rindegasto')
            ->join("INNER JOIN", "camionPropio", 'camionPropio.id = vehiculo_rindegasto.camionpropio_id AND camionPropio.vigente = "SÍ"');

        $camionArrendado = (new \yii\db\Query())
            ->select(['vehiculo_rindegasto.id', 'vehiculo', 'camionarrendado_id', 'camionpropio_id', 'equipopropio_id', 'equipoarrendado_id'])
            ->from('vehiculo_rindegasto')
            ->join("INNER JOIN", "camionArrendado", 'camionArrendado.id = vehiculo_rindegasto.camionarrendado_id AND camionArrendado.vigente = "SÍ"');

        $equipoPropio = (new \yii\db\Query())
            ->select(['vehiculo_rindegasto.id', 'vehiculo', 'camionarrendado_id', 'camionpropio_id', 'equipopropio_id', 'equipoarrendado_id'])
            ->from('vehiculo_rindegasto')
            ->join("INNER JOIN", "equipoPropio", 'equipoPropio.id = vehiculo_rindegasto.equipopropio_id AND equipoPropio.vigente = "SÍ"');

        $equipoArrendado = (new \yii\db\Query())
            ->select(['vehiculo_rindegasto.id', 'vehiculo', 'camionarrendado_id', 'camionpropio_id', 'equipopropio_id', 'equipoarrendado_id'])
            ->from('vehiculo_rindegasto')
            ->join("INNER JOIN", "equipoArrendado", 'equipoArrendado.id = vehiculo_rindegasto.equipoarrendado_id AND equipoArrendado.vigente = "SÍ"');

        $form->vehiculos = (new \yii\db\Query())
                        ->select("*")
                        ->from($camionPropio->union($camionArrendado)->union($equipoPropio)->union($equipoArrendado))
                        ->orderBy("vehiculo")->all();

        $form->unidades = (new \yii\db\Query())
            ->select(['id', 'nombre'])
            ->from('unidad')
            ->orderBy("nombre")
            ->all();
        /*         $form->tipoDocumento = (new \yii\db\Query())
            ->select(['id', 'vehiculo'])
            ->from('vehiculo_rindegasto')
            ->all(); */
        $form->rendidor = (new \yii\db\Query())
            ->select(['id', 'nombre'])
            ->from('rendidor')
            ->orderBy("nombre")
            ->all();
        $form->faena = (new \yii\db\Query())
            ->select(['id', 'nombre'])
            ->from('faena')
            ->orderBy("nombre")
            ->where('vigente = "SÍ"')
            ->all();
        $form->tipo_combustibles = (new \yii\db\Query())
            ->select(['id', 'nombre'])
            ->from('tipoCombustible')
            ->where('vigente = "SÍ"')
            ->orderBy("nombre")
            ->all();

        return $form;
    }

    //  Se le envía: { "categoria": "nombre_categoria" }
    //  Recibe: { "status": "OK", "faenas": [ { "id": 100, "nombre": "Faena perrito",  "id": 101, "nombre": "Faena perrito 2"} ] }
    //  Si hay un error: {"status": "ERROR"}    
    public function getCentrosCostosFaenas($categoria) {
        $ch = curl_init();
        //$this->detalle = "TESTING API!!";
        $objCategoria = ['categoria' => $categoria];
        $jsonData = json_encode($objCategoria);
        $url = "https://" . self::SERVER . "/SAM/index.php/chipax/getFaenas?hash=" . $this->getSamHash();
        curl_setopt($ch, CURLOPT_URL, $url);
        // Attach encoded JSON string to the POST fields
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        // Set the content type to application/json
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        // Return response instead of outputting
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = array();
        //$headers[] = "Authorization: Bearer " . $this->getSamHash();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        //return transaction 
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            //Catch cUrl Error
            $result = 'Error:' . curl_error($ch);
        }

        //Close cUrl
        curl_close($ch);
        return $result;
    }

    public function getTiposCombustibles() {
        $ch = curl_init();
        $url = "https://" . self::SERVER . "/SAM/index.php/chipax/getTiposCombustibles?hash=" . $this->getSamHash();
        curl_setopt($ch, CURLOPT_URL, $url);
        // Attach encoded JSON string to the POST fields
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        // Set the content type to application/json
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        // Return response instead of outputting
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = array();
        //$headers[] = "Authorization: Bearer " . $this->getSamHash();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        //return transaction 
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            //Catch cUrl Error
            $result = 'Error:' . curl_error($ch);
        }

        //Close cUrl
        curl_close($ch);
        return $result;
    }

    public function getSamHash() {
        $secret = date("Y-m-d i") . "chipax-mogly-secret";
        return md5($secret);
    }
}
