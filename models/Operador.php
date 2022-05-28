<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "operador".
 *
 * @property int $id
 * @property string $nombre
 * @property string $rut
 * @property string $vigente
 *
 * @property Requipoarrendado[] $requipoarrendados
 * @property Requipopropio[] $requipopropios
 */
class Operador extends \yii\db\ActiveRecord {
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'operador';
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
     * Gets query for [[Requipoarrendados]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRequipoarrendados() {
        return $this->hasMany(Requipoarrendado::className(), ['operador_id' => 'id']);
    }

    /**
     * Gets query for [[Requipopropios]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRequipopropios() {
        return $this->hasMany(Requipopropio::className(), ['operador_id' => 'id']);
    }
}
