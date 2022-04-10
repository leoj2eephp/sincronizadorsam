<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "extra_gasto".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $value
 * @property string|null $code
 * @property int $gasto_id
 *
 * @property Gasto $gasto
 */
class ExtraGasto extends \yii\db\ActiveRecord {
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'extra_gasto';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['name', 'value', 'code'], 'string'],
            [['gasto_id'], 'required'],
            [['gasto_id'], 'integer'],
            [['gasto_id'], 'exist', 'skipOnError' => true, 'targetClass' => Gasto::className(), 'targetAttribute' => ['gasto_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'value' => 'Value',
            'code' => 'Code',
            'gasto_id' => 'Gasto ID',
        ];
    }

    /**
     * Gets query for [[Gasto]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGasto() {
        return $this->hasOne(Gasto::className(), ['id' => 'gasto_id']);
    }
}
