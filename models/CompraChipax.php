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
            [['fecha_emision', "sincronizado"], 'safe'],
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
}
