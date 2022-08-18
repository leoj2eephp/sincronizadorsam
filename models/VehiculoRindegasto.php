<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "vehiculo_rindegasto".
 *
 * @property int $id
 * @property string $vehiculo
 * @property int|null $camionarrendado_id
 * @property int|null $camionpropio_id
 * @property int|null $equipopropio_id
 * @property int|null $equipoarrendado_id
 *
 * @property Camionarrendado $camionarrendado
 * @property Camionpropio $camionpropio
 * @property Equipoarrendado $equipoarrendado
 * @property Equipopropio $equipopropio
 */
class VehiculoRindegasto extends \yii\db\ActiveRecord {
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'vehiculo_rindegasto';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['vehiculo'], 'required'],
            [['camionarrendado_id', 'camionpropio_id', 'equipopropio_id', 'equipoarrendado_id'], 'integer'],
            [['vehiculo'], 'string', 'max' => 500],
            [['vehiculo'], 'unique'],
            [['camionarrendado_id'], 'exist', 'skipOnError' => true, 'targetClass' => Camionarrendado::className(), 'targetAttribute' => ['camionarrendado_id' => 'id']],
            [['camionpropio_id'], 'exist', 'skipOnError' => true, 'targetClass' => Camionpropio::className(), 'targetAttribute' => ['camionpropio_id' => 'id']],
            [['equipoarrendado_id'], 'exist', 'skipOnError' => true, 'targetClass' => Equipoarrendado::className(), 'targetAttribute' => ['equipoarrendado_id' => 'id']],
            [['equipopropio_id'], 'exist', 'skipOnError' => true, 'targetClass' => Equipopropio::className(), 'targetAttribute' => ['equipopropio_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'vehiculo' => 'Vehiculo',
            'camionarrendado_id' => 'Camionarrendado ID',
            'camionpropio_id' => 'Camionpropio ID',
            'equipopropio_id' => 'Equipopropio ID',
            'equipoarrendado_id' => 'Equipoarrendado ID',
        ];
    }

    /**
     * Gets query for [[Camionarrendado]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCamionarrendado() {
        return $this->hasOne(Camionarrendado::className(), ['id' => 'camionarrendado_id']);
    }

    /**
     * Gets query for [[Camionpropio]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCamionpropio() {
        return $this->hasOne(Camionpropio::className(), ['id' => 'camionpropio_id']);
    }

    /**
     * Gets query for [[Equipoarrendado]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEquipoarrendado() {
        return $this->hasOne(Equipoarrendado::className(), ['id' => 'equipoarrendado_id']);
    }

    /**
     * Gets query for [[Equipopropio]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEquipopropio() {
        return $this->hasOne(Equipopropio::className(), ['id' => 'equipopropio_id']);
    }
}
