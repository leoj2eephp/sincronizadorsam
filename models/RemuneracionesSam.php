<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "remuneraciones_sam".
 *
 * @property int $id
 * @property string $tipo_equipo_camion
 * @property string $descripcion
 * @property int $neto
 * @property string|null $guia
 * @property string|null $documento
 * @property float $cantidad
 * @property string $unidad
 * @property int|null $faena_id
 * @property string|null $numero
 * @property string|null $nombre
 * @property string|null $fecha_rendicion
 * @property string $rut_rinde
 * @property string $cuenta
 * @property string $nombre_proveedor
 * @property string $rut_proveedor
 * @property string $observaciones
 * @property string $tipo_documento
 * @property int $rindegastos
 * @property int|null $equipoPropio_id
 * @property int|null $equipoArrendado_id
 * @property int|null $camionPropio_id
 * @property int|null $camionArrendado_id
 *
 * @property Camionarrendado $camionArrendado
 * @property Camionpropio $camionPropio
 * @property Equipoarrendado $equipoArrendado
 * @property Equipopropio $equipoPropio
 * @property Operador $operador
 * @property Chofer $chofer
 * @property Faena $faena
 */
class RemuneracionesSam extends \yii\db\ActiveRecord {
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'remuneraciones_sam';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['tipo_equipo_camion', 'descripcion', 'neto', 'rut_rinde', 'cuenta', 'nombre_proveedor', 'rut_proveedor', 'observaciones', 'tipo_documento'], 'required'],
            [['neto', 'faena_id', 'rindegastos', 'equipoPropio_id', 'equipoArrendado_id', 'camionPropio_id', 'camionArrendado_id'], 'integer'],
            [['cantidad'], 'number'],
            [['cuenta', 'observaciones'], 'string'],
            [['tipo_equipo_camion', 'unidad'], 'string', 'max' => 2],
            [['descripcion'], 'string', 'max' => 200],
            [['guia', 'documento'], 'string', 'max' => 45],
            [['numero', 'fecha_rendicion'], 'string', 'max' => 20],
            [['nombre', 'nombre_proveedor'], 'string', 'max' => 100],
            [['rut_rinde', 'rut_proveedor'], 'string', 'max' => 15],
            [['tipo_documento'], 'string', 'max' => 40],
            [['camionArrendado_id'], 'exist', 'skipOnError' => true, 'targetClass' => Camionarrendado::class, 'targetAttribute' => ['camionArrendado_id' => 'id']],
            [['camionPropio_id'], 'exist', 'skipOnError' => true, 'targetClass' => Camionpropio::class, 'targetAttribute' => ['camionPropio_id' => 'id']],
            [['equipoArrendado_id'], 'exist', 'skipOnError' => true, 'targetClass' => Equipoarrendado::class, 'targetAttribute' => ['equipoArrendado_id' => 'id']],
            [['equipoPropio_id'], 'exist', 'skipOnError' => true, 'targetClass' => Equipopropio::class, 'targetAttribute' => ['equipoPropio_id' => 'id']],
            [['faena_id'], 'exist', 'skipOnError' => true, 'targetClass' => Faena::class, 'targetAttribute' => ['faena_id' => 'id']],
            [['operador_id'], 'exist', 'skipOnError' => true, 'targetClass' => Operador::class, 'targetAttribute' => ['operador_id' => 'id']],
            [['chofer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Chofer::class, 'targetAttribute' => ['chofer_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'descripcion' => 'Descripción',
            'neto' => 'Neto',
            'guia' => 'Guia',
            'documento' => 'Documento',
            'cantidad' => 'Cantidad',
            'unidad' => 'Unidad',
            'faena_id' => 'Faena',
            'numero' => 'Numero',
            'nombre' => 'Nombre',
            'fecha_rendicion' => 'Fecha Rendición',
            'rut_rinde' => 'Rut Rendidor',
            'cuenta' => 'Cuenta',
            'nombre_proveedor' => 'Nombre Proveedor',
            'rut_proveedor' => 'Rut Proveedor',
            'observaciones' => 'Observaciones',
            'tipo_documento' => 'Tipo Documento',
            'rindegastos' => 'Rindegastos',
            'equipoPropio_id' => 'Equipo Propio',
            'equipoArrendado_id' => 'Equipo Arrendado',
            'camionPropio_id' => 'Camión Propio',
            'camionArrendado_id' => 'Camión Arrendado',
        ];
    }

    /**
     * Gets query for [[CamionArrendado]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCamionArrendado() {
        return $this->hasOne(Camionarrendado::class, ['id' => 'camionArrendado_id']);
    }

    /**
     * Gets query for [[CamionPropio]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCamionPropio() {
        return $this->hasOne(Camionpropio::class, ['id' => 'camionPropio_id']);
    }

    /**
     * Gets query for [[EquipoArrendado]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEquipoArrendado() {
        return $this->hasOne(Equipoarrendado::class, ['id' => 'equipoArrendado_id']);
    }

    /**
     * Gets query for [[EquipoPropio]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEquipoPropio() {
        return $this->hasOne(Equipopropio::class, ['id' => 'equipoPropio_id']);
    }

    /**
     * Gets query for [[Faena]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOperador() {
        return $this->hasOne(Operador::class, ['id' => 'operador_id']);
    }

    /**
     * Gets query for [[Faena]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChofer() {
        return $this->hasOne(Chofer::class, ['id' => 'chofer_id']);
    }

    /**
     * Gets query for [[Faena]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFaena() {
        return $this->hasOne(Faena::class, ['id' => 'faena_id']);
    }

    public function getCamionEquipoNombre() {
        switch ($this->tipo_equipo_camion) {
            case "EP":
                return $this->equipoPropio->nombre;
            case "EA":
                return $this->equipoArrendado->nombre;
            case "CP":
                return $this->camionPropio->nombre;
            case "CA":
                return isset($this->camionArrendado) ? $this->camionArrendado->nombre : "";
        }
    }

}
