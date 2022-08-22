<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "gasto_chipax".
 *
 * @property int $id
 * @property string $descripcion
 * @property string $fecha
 * @property int $moneda_id
 * @property int $monto
 * @property string $num_documento
 * @property string $proveedor
 * @property string|null $responsable
 * @property string|null $tipo_cambio
 * @property int|null $usuario_id
 *
 * @property ProrrataChipax[] $prorrataChipax
 * @property GastoCompleta $gastoCompleta
 */
class GastoChipax extends \yii\db\ActiveRecord {

    public $sincronizado;
    public $spProrrataChipax = [];
    public $fecha_gasto;

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'gasto_chipax';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['id', 'descripcion', 'fecha', 'moneda_id', 'monto', 'proveedor'], 'required'],
            [['id', 'moneda_id', 'monto', 'usuario_id'], 'integer'],
            [['fecha', 'sincronizado', 'spProrrataChipax', 'fecha_gasto'], 'safe'],
            [['proveedor', 'responsable'], 'string', 'max' => 100],
            [['descripcion'], 'string', 'max' => 200],
            [['num_documento', 'tipo_cambio'], 'string', 'max' => 45],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'descripcion' => 'Descripcion',
            'fecha' => 'Fecha',
            'moneda_id' => 'Moneda ID',
            'monto' => 'Monto',
            'num_documento' => 'Num Documento',
            'proveedor' => 'Proveedor',
            'responsable' => 'Responsable',
            'tipo_cambio' => 'Tipo Cambio',
            'usuario_id' => 'Usuario ID',
        ];
    }

    /**
     * Gets query for [[ProrrataChipax]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProrrataChipax() {
        return $this->hasMany(ProrrataChipax::class, ['gasto_chipax_id' => 'id']);
    }

    /**
     * Gets query for [[GastoCompleta]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGastoCompleta() {
        return $this->hasMany(GastoCompleta::class, ['nro_documento' => 'num_documento']);
    }

    public static function convertSPResultToArrayModel($spResult) {
        $gastos = [];

        foreach ($spResult as $fila) {
            $gastito = new GastoChipax();
            $gastito->id = $fila["gastoId"];
            $gastito->descripcion = $fila["descripcion"];
            $gastito->fecha = $fila["fecha"];
            $gastito->moneda_id = $fila["moneda_id"];
            $gastito->monto = $fila["monto"];
            $gastito->num_documento = $fila["num_documento"];
            $gastito->proveedor = $fila["proveedor"];
            $gastito->responsable = $fila["responsable"];
            $gastito->tipo_cambio = $fila["tipo_cambio"];
            $gastito->usuario_id = $fila["usuario_id"];
            $gastito->fecha_gasto = $fila["fecha_gasto"];

            $pro = new ProrrataChipax();
            $pro->id = $fila["prorrataId"];
            $pro->cuenta_id = $fila["cuenta_id"];
            $pro->filtro_id = $fila["filtro_id"];
            $pro->linea_negocio = $fila["linea_negocio"];
            $pro->modelo = $fila["modelo"];
            $pro->monto = $fila["monto"];
            $pro->periodo = $fila["periodo"];
            $pro->gasto_chipax_id = $fila["gasto_chipax_id"];

            $gastito->spProrrataChipax[] = $pro;
            $gastos[] = $gastito;
        }

        return $gastos;
    }
}
