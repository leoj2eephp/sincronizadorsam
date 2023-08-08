<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "log_error".
 *
 * @property int $id
 * @property int|null $gasto_completa_id
 * @property int|null $gasto_id
 * @property int|null $compra_chipax_id
 * @property int|null $gasto_chipax_id
 * @property int|null $honorario_chipax_id
 * @property int|null $remuneracion_chipax_id
 * @property string $mensaje
 *
 * @property CompraChipax $compraChipax
 * @property Gasto $gasto
 * @property GastoChipax $gastoChipax
 * @property GastoCompleta $gastoCompleta
 * @property HonorarioChipax $honorarioChipax
 * @property RemuneracionChipax $remuneracionChipax
 */
class LogError extends \yii\db\ActiveRecord {
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'log_error';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['gasto_completa_id', 'gasto_id', 'compra_chipax_id', 'gasto_chipax_id', 'honorario_chipax_id', 'remuneracion_chipax_id'], 'integer'],
            [['mensaje'], 'required'],
            [['mensaje'], 'string', 'max' => 250],
            [['compra_chipax_id'], 'exist', 'skipOnError' => true, 'targetClass' => CompraChipax::className(), 'targetAttribute' => ['compra_chipax_id' => 'id']],
            [['gasto_id'], 'exist', 'skipOnError' => true, 'targetClass' => Gasto::className(), 'targetAttribute' => ['gasto_id' => 'id']],
            [['gasto_chipax_id'], 'exist', 'skipOnError' => true, 'targetClass' => GastoChipax::className(), 'targetAttribute' => ['gasto_chipax_id' => 'id']],
            [['gasto_completa_id'], 'exist', 'skipOnError' => true, 'targetClass' => GastoCompleta::className(), 'targetAttribute' => ['gasto_completa_id' => 'id']],
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
            'gasto_completa_id' => 'Gasto Completa ID',
            'gasto_id' => 'Gasto ID',
            'compra_chipax_id' => 'Compra Chipax ID',
            'gasto_chipax_id' => 'Gasto Chipax ID',
            'honorario_chipax_id' => 'Honorario Chipax ID',
            'remuneracion_chipax_id' => 'Remuneracion Chipax ID',
            'mensaje' => 'Mensaje',
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
     * Gets query for [[Gasto]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGasto() {
        return $this->hasOne(Gasto::className(), ['id' => 'gasto_id']);
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
     * Gets query for [[GastoCompleta]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGastoCompleta() {
        return $this->hasOne(GastoCompleta::className(), ['id' => 'gasto_completa_id']);
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
