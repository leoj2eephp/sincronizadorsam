<?php

namespace app\models;

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
 *
 * @property ProrrataChipax[] $prorrataChipax
 * @property GastoCompleta $gastoCompleta
 */
class CompraChipax extends \yii\db\ActiveRecord {

    public $sincronizado;
    public $spProrrataChipax = [];
    public $fecha_gasto;

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
            [['id', 'folio', 'moneda_id', 'monto_total', 'tipo'], 'integer'],
            [['fecha_emision', "sincronizado", "spProrrataChipax", "fecha_gasto"], 'safe'],
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
            
            $comprita->spProrrataChipax[] = $pro;
            $compras[] = $comprita;
        }
        
        return $compras;
    }
}
