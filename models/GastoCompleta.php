<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "gasto_completa".
 *
 * @property int $id
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
 * @property int $gasto_id
 * @property int|null $total_calculado
 * @property int|null $tipoCombustible_id
 *
 * @property CombustibleRindegasto[] $combustibleRindegastos
 * @property Gasto $gasto
 * @property NocombustibleRindegasto[] $nocombustibleRindegastos
 * @property RemuneracionRindegasto[] $remuneracionRindegastos
 * @property TipoCombustible $tipoCombustible
 * @property CompraChipax $compraChipax
 * @property GastoChipax $gastoChipax
 * @property HonorarioChipax $honorarioChipax
 * @property RemuneracionChipax $remuneracionChipax
 */
class GastoCompleta extends \yii\db\ActiveRecord {
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'gasto_completa';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['retenido', 'cantidad', 'centro_costo_faena', 'departamento', 'faena', 'impuesto_especifico', 'iva', 'km_carguio', 'monto_neto', 'nombre_quien_rinde', 'nro_documento', 'periodo_planilla', 'rut_proveedor', 'supervisor_combustible', 'tipo_documento', 'unidad', 'vehiculo_equipo', 'vehiculo_oficina_central'], 'string'],
            [['litros_combustible'], 'number'],
            [['gasto_id'], 'required'],
            [['gasto_id', 'total_calculado', 'tipoCombustible_id'], 'integer'],
            [['gasto_id'], 'exist', 'skipOnError' => true, 'targetClass' => Gasto::className(), 'targetAttribute' => ['gasto_id' => 'id']],
            //[['tipoCombustible_id'], 'exist', 'skipOnError' => true, 'targetClass' => TipoCombustible::className(), 'targetAttribute' => ['tipoCombustible_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
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
            'gasto_id' => 'Gasto ID',
            'total_calculado' => 'Total Calculado',
            'tipoCombustible_id' => 'Tipo Combustible ID',
        ];
    }

    /**
     * Gets query for [[Gasto]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGasto() {
        return $this->hasOne(Gasto::class, ['id' => 'gasto_id']);
    }

    /**
     * Gets query for [[CompraChipax]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompraChipax() {
        return $this->hasOne(CompraChipax::class, ['folio' => 'nro_documento']);
    }

    /**
     * Gets query for [[GastoChipax]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGastoChipax() {
        return $this->hasOne(GastoChipax::class, ['num_documento' => 'nro_documento']);
    }

    /**
     * Gets query for [[HonorarioChipax]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHonorarioChipax() {
        return $this->hasOne(HonorarioChipax::class, ['numero_boleta' => 'nro_documento']);
    }

    /**
     * Gets query for [[RemuneracionChipax]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRemuneracionChipax() {
        return $this->hasOne(RemuneracionChipax::class, ['folio' => 'remuneracion_chipax_id']);
    }

    public static function isSincronizedWithChipax($folio_chipax, $fecha) {
        return GastoCompleta::find()->joinWith("gasto")->where(
            "nro_documento = :folio AND issue_date = :fecha",
            [":folio" => $folio_chipax, ":fecha" => $fecha]
        )->all();
    }
}
