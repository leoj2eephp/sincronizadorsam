<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "honorario_chipax".
 *
 * @property int $id
 * @property string $fecha_emision
 * @property int $moneda_id
 * @property int $monto_liquido
 * @property int $numero_boleta
 * @property string $nombre_emisor
 * @property string $rut_emisor
 * @property int|null $usuario_id
 *
 * @property ProrrataChipax[] $prorrataChipax
 */
class HonorarioChipax extends \yii\db\ActiveRecord {

    public $sincronizado;

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'honorario_chipax';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['id', 'fecha_emision', 'moneda_id', 'monto_liquido', 'numero_boleta', 'nombre_emisor', 'rut_emisor'], 'required'],
            [['id', 'moneda_id', 'monto_liquido', 'numero_boleta', 'usuario_id'], 'integer'],
            [['fecha_emision'], 'safe'],
            [['nombre_emisor'], 'string', 'max' => 45],
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
            'moneda_id' => 'Moneda ID',
            'monto_liquido' => 'Monto Liquido',
            'numero_boleta' => 'Numero Boleta',
            'nombre_emisor' => 'Nombre Emisor',
            'rut_emisor' => 'Rut Emisor',
            'usuario_id' => 'Usuario ID',
        ];
    }

    /**
     * Gets query for [[ProrrataChipaxes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProrrataChipax() {
        return $this->hasMany(ProrrataChipax::class, ['honorario_chipax_id' => 'id']);
    }
}
