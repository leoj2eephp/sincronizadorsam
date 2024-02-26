<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "linea_negocio_chipax".
 *
 * @property int $id
 * @property string $nombre
 * @property int|null $default
 * @property string|null $cerrada
 * @property int|null $deleted
 */
class LineaNegocioChipax extends \yii\db\ActiveRecord {
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'linea_negocio_chipax';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['id', 'nombre'], 'required'],
            [['id', 'default', 'deleted'], 'integer'],
            [['cerrada'], 'safe'],
            [['nombre'], 'string', 'max' => 60],
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
            'default' => 'Default',
            'cerrada' => 'Cerrada',
            'deleted' => 'Deleted',
        ];
    }

    public static function sincronizarDatos($jsonData) {
        // LineaNegocioChipax::deleteAll();
        Yii::$app->db->createCommand()->truncateTable("linea_negocio_chipax")->execute();
        foreach ($jsonData as $linea) {
            if (!$linea->save()) {
                echo "Hubo un error al sincronizar las líneas de negocio.";
            }
        }
    }
}
