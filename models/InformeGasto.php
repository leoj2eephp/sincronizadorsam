<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "informe_gasto".
 *
 * @property int $id
 * @property string|null $titulo
 * @property int|null $numero
 * @property string|null $fecha_envio
 * @property string|null $fecha_cierre
 * @property string|null $nombre_empleado
 * @property string|null $rut_empleado
 * @property string|null $aprobado_por
 * @property int|null $politica_id
 * @property string|null $politica
 * @property int|null $estado
 * @property int|null $total
 * @property int|null $total_aprobado
 * @property int|null $nro_gastos
 * @property int|null $nro_gastos_aprobados
 * @property int|null $nro_gastos_rechazados
 * @property string|null $nota
 */
class InformeGasto extends \yii\db\ActiveRecord {
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'informe_gasto';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['id'], 'required'],
            [['id', 'numero', 'politica_id', 'estado', 'total', 'total_aprobado', 'nro_gastos', 'nro_gastos_aprobados', 'nro_gastos_rechazados'], 'integer'],
            [['titulo', 'nota'], 'string'],
            [['fecha_envio', 'fecha_cierre'], 'string', 'max' => 10],
            [['nombre_empleado', 'aprobado_por'], 'string', 'max' => 300],
            [['rut_empleado'], 'string', 'max' => 20],
            [['politica'], 'string', 'max' => 200],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'titulo' => 'Titulo',
            'numero' => 'Numero',
            'fecha_envio' => 'Fecha Envio',
            'fecha_cierre' => 'Fecha Cierre',
            'nombre_empleado' => 'Nombre Empleado',
            'rut_empleado' => 'Rut Empleado',
            'aprobado_por' => 'Aprobado Por',
            'politica_id' => 'Politica ID',
            'politica' => 'Politica',
            'estado' => 'Estado',
            'total' => 'Total',
            'total_aprobado' => 'Total Aprobado',
            'nro_gastos' => 'Nro Gastos',
            'nro_gastos_aprobados' => 'Nro Gastos Aprobados',
            'nro_gastos_rechazados' => 'Nro Gastos Rechazados',
            'nota' => 'Nota',
        ];
    }
}
