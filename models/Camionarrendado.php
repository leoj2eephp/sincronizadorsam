<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "camionarrendado".
 *
 * @property int $id
 * @property string $nombre
 * @property float $capacidad
 * @property string $pesoOVolumen
 * @property float $consumoPromedio
 * @property float $coeficienteDeTrato
 * @property float $produccionMinima
 * @property float $horasMin
 * @property string $vigente
 * @property int $odometro_en_millas
 *
 * @property CombustibleRindegasto[] $combustibleRindegastos
 * @property NocombustibleRindegasto[] $nocombustibleRindegastos
 * @property Rcamionarrendado[] $rcamionarrendados
 * @property RemuneracionRindegasto[] $remuneracionRindegastos
 * @property RemuneracionesSam[] $remuneracionesSams
 * @property Unidadfaena[] $unidadfaenas
 * @property VehiculoRindegasto[] $vehiculoRindegastos
 */
class Camionarrendado extends \yii\db\ActiveRecord {
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'camionarrendado';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['nombre', 'capacidad', 'pesoOVolumen', 'consumoPromedio', 'coeficienteDeTrato', 'produccionMinima', 'horasMin'], 'required'],
            [['capacidad', 'consumoPromedio', 'coeficienteDeTrato', 'produccionMinima', 'horasMin'], 'number'],
            [['odometro_en_millas'], 'integer'],
            [['nombre'], 'string', 'max' => 100],
            [['pesoOVolumen'], 'string', 'max' => 1],
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
            'capacidad' => 'Capacidad',
            'pesoOVolumen' => 'Peso O Volumen',
            'consumoPromedio' => 'Consumo Promedio',
            'coeficienteDeTrato' => 'Coeficiente De Trato',
            'produccionMinima' => 'Produccion Minima',
            'horasMin' => 'Horas Min',
            'vigente' => 'Vigente',
            'odometro_en_millas' => 'Odometro En Millas',
        ];
    }

    /**
     * Gets query for [[RemuneracionesSams]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRemuneracionesSams() {
        return $this->hasMany(RemuneracionesSam::class, ['camionArrendado_id' => 'id']);
    }
}
