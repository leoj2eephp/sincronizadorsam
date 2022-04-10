<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "prorrata_chipax".
 *
 * @property int $id
 * @property int|null $cuenta_id
 * @property int|null $filtro_id
 * @property string $linea_negocio
 * @property string $modelo
 * @property int $monto
 * @property string $periodo
 * @property int|null $compra_chipax_id
 * @property int|null $gasto_chipax_id
 * @property int|null $honorario_chipax_id
 * @property int|null $remuneracion_chipax_id
 *
 * @property CompraChipax $compraChipax
 * @property GastoChipax $gastoChipax
 * @property HonorarioChipax $honorarioChipax
 * @property RemuneracionChipax $remuneracionChipax
 */
class ProrrataChipax extends \yii\db\ActiveRecord {
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'prorrata_chipax';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['id', 'linea_negocio', 'modelo', 'monto', 'periodo'], 'required'],
            [['id', 'cuenta_id', 'filtro_id', 'monto', 'compra_chipax_id', 'gasto_chipax_id', 'honorario_chipax_id', 'remuneracion_chipax_id'], 'integer'],
            [['periodo'], 'safe'],
            [['linea_negocio', 'modelo'], 'string', 'max' => 45],
            [['id'], 'unique'],
            [['compra_chipax_id'], 'exist', 'skipOnError' => true, 'targetClass' => CompraChipax::className(), 'targetAttribute' => ['compra_chipax_id' => 'id']],
            [['gasto_chipax_id'], 'exist', 'skipOnError' => true, 'targetClass' => GastoChipax::className(), 'targetAttribute' => ['gasto_chipax_id' => 'id']],
            [['honorario_chipax_id'], 'exist', 'skipOnError' => true, 'targetClass' => HonorarioChipax::className(), 'targetAttribute' => ['honorario_chipax_id' => 'id']],
            [['remuneracion_chipax_id'], 'exist', 'skipOnError' => true, 'targetClass' => RemuneracionChipax::className(), 'targetAttribute' => ['remuneracion_chipax_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'cuenta_id' => 'Cuenta ID',
            'filtro_id' => 'Filtro ID',
            'linea_negocio' => 'Linea Negocio',
            'modelo' => 'Modelo',
            'monto' => 'Monto',
            'periodo' => 'Periodo',
            'compra_chipax_id' => 'Compra Chipax ID',
            'gasto_chipax_id' => 'Gasto Chipax ID',
            'honorario_chipax_id' => 'Honorario Chipax ID',
            'remuneracion_chipax_id' => 'Remuneracion Chipax ID',
        ];
    }

    /**
     * Gets query for [[CompraChipax]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompraChipax() {
        return $this->hasOne(CompraChipax::className(), ['id' => 'compra_chipax_id']);
    }

    /**
     * Gets query for [[GastoChipax]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGastoChipax() {
        return $this->hasOne(GastoChipax::className(), ['id' => 'gasto_chipax_id']);
    }

    /**
     * Gets query for [[HonorarioChipax]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHonorarioChipax() {
        return $this->hasOne(HonorarioChipax::className(), ['id' => 'honorario_chipax_id']);
    }

    /**
     * Gets query for [[RemuneracionChipax]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRemuneracionChipax() {
        return $this->hasOne(RemuneracionChipax::className(), ['id' => 'remuneracion_chipax_id']);
    }
}
