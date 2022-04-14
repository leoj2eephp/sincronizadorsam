<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "gasto_chipax".
 *
 * @property int $id
 * @property string $descripcion
 * @property string $fecha
 * @property int $moneda_id
 * @property int $monto
 * @property string $num_documento
 * @property string $proveedor
 * @property string|null $responsable
 * @property string|null $tipo_cambio
 * @property int|null $usuario_id
 *
 * @property ProrrataChipax[] $prorrataChipax
 * @property GastoCompleta $gastoCompleta
 */
class GastoChipax extends \yii\db\ActiveRecord {

    public $sincronizado;

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'gasto_chipax';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['id', 'descripcion', 'fecha', 'moneda_id', 'monto', 'num_documento', 'proveedor'], 'required'],
            [['id', 'moneda_id', 'monto', 'usuario_id'], 'integer'],
            [['fecha', 'sincronizado'], 'safe'],
            [['proveedor', 'responsable'], 'string', 'max' => 100],
            [['descripcion'], 'string', 'max' => 200],
            [['num_documento', 'tipo_cambio'], 'string', 'max' => 45],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'descripcion' => 'Descripcion',
            'fecha' => 'Fecha',
            'moneda_id' => 'Moneda ID',
            'monto' => 'Monto',
            'num_documento' => 'Num Documento',
            'proveedor' => 'Proveedor',
            'responsable' => 'Responsable',
            'tipo_cambio' => 'Tipo Cambio',
            'usuario_id' => 'Usuario ID',
        ];
    }

    /**
     * Gets query for [[ProrrataChipax]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProrrataChipax() {
        return $this->hasMany(ProrrataChipax::class, ['gasto_chipax_id' => 'id']);
    }

    /**
     * Gets query for [[GastoCompleta]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGastoCompleta() {
        return $this->hasMany(GastoCompleta::class, ['nro_documento' => 'num_documento']);
    }
}
