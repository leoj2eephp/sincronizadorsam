<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "gasto_completa_rindegastos".
 *
 * @property int $id
 * @property int $gasto_rindegastos_id
 * @property string|null $retenido
 * @property string|null $cantidad
 * @property string|null $centro_costo_faena
 * @property string|null $departamento
 * @property string|null $faena
 * @property string|null $impuesto_especifico
 * @property string|null $iva
 * @property string|null $km_carguio
 * @property float|null $litros_combustible
 * @property string|null $monto_neto
 * @property string|null $nombre_quien_rinde
 * @property string|null $nro_documento
 * @property string|null $periodo_planilla
 * @property string|null $rut_proveedor
 * @property string|null $supervisor_combustible
 * @property string|null $tipo_documento
 * @property string|null $unidad
 * @property string|null $vehiculo_equipo
 * @property string|null $vehiculo_oficina_central
 * @property int|null $total_calculado
 *
 * @property GastoRindegastos $gastoRindegastos
 */
class GastoCompletaRindegastos extends \yii\db\ActiveRecord {
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'gasto_completa_rindegastos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['gasto_rindegastos_id'], 'required'],
            [['gasto_rindegastos_id', 'total_calculado'], 'integer'],
            [['retenido', 'cantidad', 'centro_costo_faena', 'departamento', 'faena', 'impuesto_especifico', 'iva', 'km_carguio', 'monto_neto', 'nombre_quien_rinde', 'nro_documento', 'periodo_planilla', 'rut_proveedor', 'supervisor_combustible', 'tipo_documento', 'unidad', 'vehiculo_equipo', 'vehiculo_oficina_central'], 'string'],
            [['litros_combustible'], 'number'],
            [['gasto_rindegastos_id'], 'exist', 'skipOnError' => true, 'targetClass' => GastoRindegastos::class, 'targetAttribute' => ['gasto_rindegastos_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'gasto_rindegastos_id' => 'Gasto Rinde Gastos ID',
            'retenido' => 'Retenido',
            'cantidad' => 'Cantidad',
            'centro_costo_faena' => 'Centro Costo Faena',
            'departamento' => 'Departamento',
            'faena' => 'Faena',
            'impuesto_especifico' => 'Impuesto Especifico',
            'iva' => 'Iva',
            'km_carguio' => 'Km Carguio',
            'litros_combustible' => 'Litros Combustible',
            'monto_neto' => 'Monto Neto',
            'nombre_quien_rinde' => 'Nombre Quien Rinde',
            'nro_documento' => 'Nro Documento',
            'periodo_planilla' => 'Periodo Planilla',
            'rut_proveedor' => 'Rut Proveedor',
            'supervisor_combustible' => 'Supervisor Combustible',
            'tipo_documento' => 'Tipo Documento',
            'unidad' => 'Unidad',
            'vehiculo_equipo' => 'Vehiculo Equipo',
            'vehiculo_oficina_central' => 'Vehiculo Oficina Central',
            'total_calculado' => 'Total Calculado',
        ];
    }

    /**
     * Gets query for [[gastoRindegastos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGastoRindegastos() {
        return $this->hasOne(GastoRindegastos::class, ['id' => 'gasto_rindegastos_id']);
    }
}
