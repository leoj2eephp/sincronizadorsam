<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "gasto_imagen".
 *
 * @property int $id
 * @property string|null $file_name
 * @property string|null $extension
 * @property string|null $original
 * @property string|null $large
 * @property string|null $medium
 * @property string|null $small
 * @property int $gasto_id
 *
 * @property Gasto $gasto
 */
class GastoImagen extends \yii\db\ActiveRecord {
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'gasto_imagen';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['file_name', 'original', 'large', 'medium', 'small'], 'string'],
            [['gasto_id'], 'required'],
            [['gasto_id'], 'integer'],
            [['extension'], 'string', 'max' => 10],
            [['gasto_id'], 'exist', 'skipOnError' => true, 'targetClass' => Gasto::className(), 'targetAttribute' => ['gasto_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'file_name' => 'File Name',
            'extension' => 'Extension',
            'original' => 'Original',
            'large' => 'Large',
            'medium' => 'Medium',
            'small' => 'Small',
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
