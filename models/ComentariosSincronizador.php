<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "comentarios_sincronizador".
 *
 * @property int $id
 * @property string $comentario
 * @property string|null $nro_documento
 * @property string $fecha
 * @property int $monto
 */
class ComentariosSincronizador extends \yii\db\ActiveRecord {
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'comentarios_sincronizador';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['comentario', 'fecha', 'monto'], 'required'],
            [['comentario', 'nro_documento'], 'string'],
            [['fecha'], 'safe'],
            [['monto'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'comentario' => 'Comentario',
            'nro_documento' => 'Nro Documento',
            'fecha' => 'Fecha',
            'monto' => 'Monto',
        ];
    }
}
