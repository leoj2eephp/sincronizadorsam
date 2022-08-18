<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "faena".
 *
 * @property int $id
 * @property string $nombre
 * @property string $vigente
 * @property int $por_horas
 * @property int $combustible
 *
 * @property Cargacombcamionpropio[] $cargacombcamionpropios
 * @property Cargacombequipoarrendado[] $cargacombequipoarrendados
 * @property Cargacombequipopropio[] $cargacombequipopropios
 * @property CombustibleRindegasto[] $combustibleRindegastos
 * @property Comprarepuestocamionarrendado[] $comprarepuestocamionarrendados
 * @property Comprarepuestocamionpropio[] $comprarepuestocamionpropios
 * @property Comprarepuestoequipoarrendado[] $comprarepuestoequipoarrendados
 * @property Comprarepuestoequipopropio[] $comprarepuestoequipopropios
 * @property Expediciones[] $expediciones
 * @property Expedicionportiempoarr[] $expedicionportiempoarrs
 * @property Expedicionportiempoeqarr[] $expedicionportiempoeqarrs
 * @property Expedicionportiempoeq[] $expedicionportiempoeqs
 * @property Expedicionportiempo[] $expedicionportiempos
 * @property FaenaRindegasto[] $faenaRindegastos
 * @property Informegastocombustible[] $informegastocombustibles
 * @property NocombustibleRindegasto[] $nocombustibleRindegastos
 * @property Observaciones[] $observaciones
 * @property OrigendestinoFaena[] $origendestinoFaenas
 * @property RemuneracionRindegasto[] $remuneracionRindegastos
 * @property Remuneracioncamionarrendado[] $remuneracioncamionarrendados
 * @property Remuneracioncamionpropio[] $remuneracioncamionpropios
 * @property Remuneracionequipoarrendado[] $remuneracionequipoarrendados
 * @property Remuneracionequipopropio[] $remuneracionequipopropios
 * @property RemuneracionesSam[] $remuneracionesSams
 * @property UnidadfaenaEquipo[] $unidadfaenaEquipos
 * @property Unidadfaena[] $unidadfaenas
 * @property Viajecamionarrendado[] $viajecamionarrendados
 * @property Viajecamionpropio[] $viajecamionpropios
 */
class Faena extends \yii\db\ActiveRecord {
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'faena';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['nombre'], 'required'],
            [['por_horas', 'combustible'], 'integer'],
            [['nombre'], 'string', 'max' => 200],
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
            'vigente' => 'Vigente',
            'por_horas' => 'Por Horas',
            'combustible' => 'Combustible',
        ];
    }
    
}
