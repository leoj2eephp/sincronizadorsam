<?php

namespace app\models;

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
    public $categoria;
    public $categoria_id;
    public $linea_negocio;
    public $linea_negocio_id;
    public $detalle;
    public $neto;
    public $html_factura;

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
                "tipo_combustible_id", "categoria_id", "linea_negocio_id"
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
        ];
    }

    public function sendData() {
        $ch = curl_init();
        //$this->detalle = "TESTING API!!";
        $jsonData = json_encode($this);

        //$url = "http://" . self::SERVER . "/SAMQA/index.php/chipax/add?hash=" . $this->getSamHash();
        $url = "http://" . self::SERVER . "/SAM/index.php/chipax/add?hash=" . $this->getSamHash();
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

    public static function fillData() {
        $form = new PoliticaGastosForm();
        $form->vehiculos = (new \yii\db\Query())
            ->select(['id', 'vehiculo'])
            ->from('vehiculo_rindegasto')
            ->all();
        $form->unidades = (new \yii\db\Query())
            ->select(['id', 'nombre'])
            ->from('unidad')
            ->all();
        /*         $form->tipoDocumento = (new \yii\db\Query())
            ->select(['id', 'vehiculo'])
            ->from('vehiculo_rindegasto')
            ->all(); */
        $form->rendidor = (new \yii\db\Query())
            ->select(['id', 'nombre'])
            ->from('rendidor')
            ->all();
        $form->faena = (new \yii\db\Query())
            ->select(['id', 'nombre'])
            ->from('faena')
            ->all();
        $form->tipo_combustibles = (new \yii\db\Query())
            ->select(['id', 'nombre'])
            ->from('tipoCombustible')
            ->where('vigente = "SÍ"')
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
        $url = "http://" . self::SERVER . "/SAM/index.php/chipax/getFaenas?hash=" . $this->getSamHash();
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
        $url = "http://" . self::SERVER . "/SAM/index.php/chipax/getTiposCombustibles?hash=" . $this->getSamHash();
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
