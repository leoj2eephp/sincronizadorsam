<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "equipoarrendado".
 *
 * @property int $id
 * @property string $nombre
 * @property int|null $precioUnitario
 * @property float|null $horasMin
 * @property int $valorHora
 * @property float $consumoEsperado
 * @property int $propietario_id
 * @property float $coeficienteDeTrato
 * @property string $vigente
 *
 * @property CombustibleRindegasto[] $combustibleRindegastos
 * @property NocombustibleRindegasto[] $nocombustibleRindegastos
 * @property Propietario $propietario
 * @property RemuneracionRindegasto[] $remuneracionRindegastos
 * @property RemuneracionesSam[] $remuneracionesSams
 * @property Requipoarrendado[] $requipoarrendados
 * @property UnidadfaenaEquipo[] $unidadfaenaEquipos
 * @property VehiculoRindegasto[] $vehiculoRindegastos
 */
class Equipoarrendado extends \yii\db\ActiveRecord {
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'equipoArrendado';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['nombre', 'valorHora', 'consumoEsperado', 'propietario_id', 'coeficienteDeTrato'], 'required'],
            [['precioUnitario', 'valorHora', 'propietario_id'], 'integer'],
            [['horasMin', 'consumoEsperado', 'coeficienteDeTrato'], 'number'],
            [['nombre'], 'string', 'max' => 100],
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
            'precioUnitario' => 'Precio Unitario',
            'horasMin' => 'Horas Min',
            'valorHora' => 'Valor Hora',
            'consumoEsperado' => 'Consumo Esperado',
            'propietario_id' => 'Propietario ID',
            'coeficienteDeTrato' => 'Coeficiente De Trato',
            'vigente' => 'Vigente',
        ];
    }

    /**
     * Gets query for [[RemuneracionesSams]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRemuneracionesSams() {
        return $this->hasMany(RemuneracionesSam::class, ['equipoArrendado_id' => 'id']);
    }
}
