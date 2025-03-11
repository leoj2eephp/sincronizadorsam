<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "categoria_chipax".
 *
 * @property int $id
 * @property string $nombre
 * @property int|null $parent_id
 * @property int|null $tipo_cuenta_id
 * @property string|null $model_name
 * @property string|null $parent_model_name
 * @property string|null $comp_id
 * @property string|null $parent_comp_id
 * @property int|null $depth
 * @property int|null $has_children
 * @property int $empresa_chipax_id
 */
class CategoriaChipax extends \yii\db\ActiveRecord {
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'categoria_chipax';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['id', 'nombre'], 'required'],
            [['id', 'depth', 'empresa_chipax_id'], 'integer'],
            [['nombre'], 'string', 'max' => 100],
            [['model_name'], 'string', 'max' => 45],
            [['has_children', 'parent_model_name', 'comp_id', 'parent_comp_id', 'parent_id', 'tipo_cuenta_id'], "safe"],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre',
            'parent_id' => 'Parent',
            'tipo_cuenta_id' => 'Tipo Cuenta',
            'model_name' => 'Model Name',
            'parent_model_name' => 'Parent Model Name',
            'comp_id' => 'Comp',
            'parent_comp_id' => 'Parent Comp',
            'depth' => 'Depth',
            'has_children' => 'Has Children',
            'empresa_chipax_id' => 'Empresa Chipax',
        ];
    }
}
