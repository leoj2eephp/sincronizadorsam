<?php

namespace app\models;

use Exception;
use Yii;

/**
 * This is the model class for table "compra_chipax".
 *
 * @property int $id
 * @property string $fecha_emision
 * @property int $folio
 * @property int $moneda_id
 * @property int $monto_total
 * @property string|null $razon_social
 * @property string $rut_emisor
 * @property int|null $tipo
 * @property int $empresa_chipax_id
 *
 * @property ProrrataChipax[] $prorrataChipax
 * @property GastoCompleta $gastoCompleta
 */
class CompraChipax extends \yii\db\ActiveRecord {

    public $sincronizado;
    public $spProrrataChipax = [];
    public $fecha_gasto;
    public $rindeGastoDividido = false;
    public $rindeGastoData = [];

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'compra_chipax';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['id', 'fecha_emision', 'folio', 'moneda_id', 'monto_total', 'rut_emisor'], 'required'],
            [['id', 'folio', 'moneda_id', 'monto_total', 'tipo', 'empresa_chipax_id'], 'integer'],
            [['fecha_emision', "sincronizado", "spProrrataChipax", "fecha_gasto", "rindeGastoDividido"], 'safe'],
            [['razon_social'], 'string', 'max' => 100],
            [['rut_emisor'], 'string', 'max' => 12],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'fecha_emision' => 'Fecha Emision',
            'folio' => 'Folio',
            'moneda_id' => 'Moneda ID',
            'monto_total' => 'Monto Total',
            'razon_social' => 'Razon Social',
            'rut_emisor' => 'Rut Emisor',
            'tipo' => 'Tipo',
            'empresa_chipax_id' => 'Empresa Chipax',
        ];
    }

    /**
     * Gets query for [[ProrrataChipax]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProrrataChipax() {
        return $this->hasMany(ProrrataChipax::class, ['compra_chipax_id' => 'id']);
    }

    /**
     * Gets query for [[GastoCompleta]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGastoCompleta() {
        return $this->hasMany(GastoCompleta::class, ['nro_documento' => 'folio']);
    }

    public static function convertSPResultToArrayModel($spResult) {
        $compras = [];

        foreach ($spResult as $fila) {
            $comprita = new CompraChipax();
            $comprita->id = $fila["chipaxId"];
            $comprita->fecha_emision = $fila["fecha_emision"];
            $comprita->folio = $fila["folio"];
            $comprita->moneda_id = $fila["moneda_id"];
            $comprita->monto_total = $fila["monto_total"];
            $comprita->razon_social = $fila["razon_social"];
            $comprita->rut_emisor = $fila["rut_emisor"];
            $comprita->tipo = $fila["tipo"];
            $comprita->fecha_gasto = $fila["fecha_gasto"];
            // Este nuevo flag identificará la empresa de la que proviene el gasto de Chipax.
            // 1. Otzi
            // 2. Conejero Maquinarias SPA
            $comprita->empresa_chipax_id = $fila["empresa_chipax_id"];

            $pro = new ProrrataChipax();
            $pro->id = $fila["prorrataId"];
            $pro->cuenta_id = $fila["cuenta_id"];
            $pro->filtro_id = $fila["filtro_id"];
            $pro->linea_negocio = $fila["linea_negocio"];
            $pro->modelo = $fila["modelo"];
            $pro->monto = $fila["monto"];
            $pro->neto_impuesto = $fila["neto_impuesto"];
            $pro->monto_sumado = $fila["monto_sumado"];
            $pro->periodo = $fila["periodo"];
            $pro->compra_chipax_id = $fila["compra_chipax_id"];
            $pro->empresa_chipax_id = $fila["empresa_chipax_id"];

            $comprita->spProrrataChipax[] = $pro;
            $compras[] = $comprita;
        }

        return $compras;
    }

    static function convertToModel($jsonArreglo, $empId) {
        $folios = array();   // para verificar si existe algún folio repetido

        foreach ($jsonArreglo as $c) {
            try {   // este bloque evitará que haya un folio duplicado mostrándose
                if (array_search($c["folio"], $folios) !== false) {
                    continue;
                }
                if ($c["tipo"] == 52) {
                    continue;
                }
            } catch (\yii\base\ErrorException $ex) {
                echo "<pre>";
                print_r($ex);
                break;
            }

            try {
                $compras = new CompraChipax();
                $compras->fecha_emision = $c["fechaEmision"] ?? "";
                $compras->folio = $c["folio"];
                $compras->id = $c["id"];
                $compras->moneda_id = $c["idMoneda"];
                $compras->monto_total = $c["montoTotal"];
                $compras->razon_social = $c["razonSocial"];
                $compras->rut_emisor = $c["rutEmisor"];
                $compras->tipo = $c["tipo"];
                $compras->empresa_chipax_id = $empId;

                if ($compras->save()) {
                    foreach ($c["categorias"] as $pro) {
                        $prorrata = new ProrrataChipax();
                        $prorrata->id = $pro["id"];
                        $prorrata->cuenta_id = $pro["idCuenta"];
                        $prorrata->filtro_id = null;
                        $linea_negocio = LineaNegocioChipax::findOne($pro["idLineaNegocio"]);
                        $prorrata->linea_negocio = $linea_negocio->nombre;
                        $prorrata->compra_chipax_id = $compras->id;
                        $prorrata->modelo = "Compra";
                        $prorrata->monto = $pro["monto"];
                        $prorrata->periodo = $pro["periodo"];
                        $prorrata->empresa_chipax_id = $empId;

                        if (!$prorrata->save()) {
                            echo "Hubo un error al insertar las prorratas";
                            echo join(", ", $prorrata->getFirstErrors());
                        }
                    }
                } else {
                    Yii::error("Error al insertar en CompraChipax");
                    Yii::error($compras->getErrors());
                }
            } catch (Exception $ex) {
                $log = new LogError();
                $log->mensaje = $ex->getMessage();
                $log->compra_chipax_id = $compras->id;
                $log->save();
                Yii::error("Error al insertar en CompraChipax");
                Yii::error($ex->getMessage());
            }

            $folios[] = $c["folio"];
            $comprasData[] = $compras;
        }
        return $comprasData;
    }
}
