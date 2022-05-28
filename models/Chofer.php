<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "chofer".
 *
 * @property int $id
 * @property string $nombre
 * @property string $rut
 * @property string $vigente
 *
 * @property Rcamionarrendado[] $rcamionarrendados
 * @property Rcamionpropio[] $rcamionpropios
 */
class Chofer extends \yii\db\ActiveRecord {
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'chofer';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['nombre', 'rut'], 'required'],
            [['nombre'], 'string', 'max' => 200],
            [['rut'], 'string', 'max' => 15],
            [['vigente'], 'string', 'max' => 2],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre',
            'rut' => 'Rut',
            'vigente' => 'Vigente',
        ];
    }

    /**
     * Gets query for [[Rcamionarrendados]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRcamionarrendados() {
        return $this->hasMany(Rcamionarrendado::className(), ['chofer_id' => 'id']);
    }

    /**
     * Gets query for [[Rcamionpropios]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRcamionpropios() {
        return $this->hasMany(Rcamionpropio::className(), ['chofer_id' => 'id']);
    }
}
