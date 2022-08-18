<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "equipopropio".
 *
 * @property int $id
 * @property string $nombre
 * @property string $codigo
 * @property int|null $precioUnitario
 * @property float|null $horasMin
 * @property float $consumoEsperado
 * @property int $valorHora
 * @property float $coeficienteDeTrato
 * @property string $vigente
 *
 * @property CombustibleRindegasto[] $combustibleRindegastos
 * @property NocombustibleRindegasto[] $nocombustibleRindegastos
 * @property RemuneracionRindegasto[] $remuneracionRindegastos
 * @property RemuneracionesSam[] $remuneracionesSams
 * @property Requipopropio[] $requipopropios
 * @property UnidadfaenaEquipo[] $unidadfaenaEquipos
 * @property VehiculoRindegasto[] $vehiculoRindegastos
 */
class Equipopropio extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'equipopropio';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre', 'codigo', 'consumoEsperado', 'valorHora', 'coeficienteDeTrato'], 'required'],
            [['precioUnitario', 'valorHora'], 'integer'],
            [['horasMin', 'consumoEsperado', 'coeficienteDeTrato'], 'number'],
            [['nombre'], 'string', 'max' => 100],
            [['codigo'], 'string', 'max' => 45],
            [['vigente'], 'string', 'max' => 2],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre',
            'codigo' => 'Codigo',
            'precioUnitario' => 'Precio Unitario',
            'horasMin' => 'Horas Min',
            'consumoEsperado' => 'Consumo Esperado',
            'valorHora' => 'Valor Hora',
            'coeficienteDeTrato' => 'Coeficiente De Trato',
            'vigente' => 'Vigente',
        ];
    }

    /**
     * Gets query for [[RemuneracionesSams]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRemuneracionesSams()
    {
        return $this->hasMany(RemuneracionesSam::class, ['equipoPropio_id' => 'id']);
    }
    
}
