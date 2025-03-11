<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "remuneracion_chipax".
 *
 * @property int $id
 * @property int $empresa_id
 * @property int|null $usuario_id
 * @property string $periodo
 * @property int $empleado_id
 * @property int $monto_liquido
 * @property int $moneda_id
 * @property string|null $liquidacion
 * @property string $nombre_empleado
 * @property string $apellido_empleado
 * @property string $rut_empleado
 * @property string|null $email_empleado
 * @property int $empresa_chipax_id
 *
 * @property ProrrataChipax[] $prorrataChipax
 * @property GastoCompleta $gastoCompleta
 */
class RemuneracionChipax extends \yii\db\ActiveRecord {

    public $sincronizado;
    public $spProrrataChipax;

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'remuneracion_chipax';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['id', 'empresa_id', 'periodo', 'empleado_id', 'monto_liquido', 'moneda_id', 'nombre_empleado', 'apellido_empleado', 'rut_empleado'], 'required'],
            [['id', 'empresa_id', 'usuario_id', 'empleado_id', 'monto_liquido', 'moneda_id', "empresa_chipax_id"], 'integer'],
            [['periodo', "sincronizado", "spProrrataChipax"], 'safe'],
            [['liquidacion'], 'string', 'max' => 150],
            [['nombre_empleado', 'apellido_empleado', 'email_empleado'], 'string', 'max' => 45],
            [['rut_empleado'], 'string', 'max' => 12],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'empresa_id' => 'Empresa',
            'usuario_id' => 'Usuario',
            'periodo' => 'Periodo',
            'empleado_id' => 'Empleado',
            'monto_liquido' => 'Monto Liquido',
            'moneda_id' => 'Moneda',
            'liquidacion' => 'Liquidacion',
            'nombre_empleado' => 'Nombre Empleado',
            'apellido_empleado' => 'Apellido Empleado',
            'rut_empleado' => 'Rut Empleado',
            'email_empleado' => 'Email Empleado',
            'empresa_chipax_id' => 'Empresa Chipax',
        ];
    }

    /**
     * Gets query for [[ProrrataChipaxes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProrrataChipax() {
        return $this->hasMany(ProrrataChipax::class, ['remuneracion_chipax_id' => 'id']);
    }

    /**
     * Gets query for [[GastoCompleta]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGastoCompleta() {
        return $this->hasMany(GastoCompleta::class, ['nro_documento' => 'id']);
    }

    public static function convertSPResultToArrayModel($spResult) {
        $remuneraciones = [];
        
        foreach ($spResult as $fila) {
            $remuneracion = new RemuneracionChipax();
            $remuneracion->id = $fila["remuId"];
            $remuneracion->empresa_id = $fila["empresa_id"];
            $remuneracion->usuario_id = $fila["usuario_id"];
            $remuneracion->periodo = $fila["periodo"];
            $remuneracion->empleado_id = $fila["empleado_id"];
            $remuneracion->monto_liquido = $fila["monto_liquido"];
            $remuneracion->moneda_id = $fila["moneda_id"];
            $remuneracion->liquidacion = $fila["liquidacion"];
            $remuneracion->nombre_empleado = $fila["nombre_empleado"];
            $remuneracion->apellido_empleado = $fila["apellido_empleado"];
            $remuneracion->rut_empleado = $fila["rut_empleado"];
            $remuneracion->email_empleado = $fila["email_empleado"];
            // Este nuevo flag identificará la empresa de la que proviene el gasto de Chipax.
            // 1. Otzi
            // 2. Conejero Maquinarias SPA
            $remuneracion->empresa_chipax_id = $fila["empresa_chipax_id"];
            
            $pro = new ProrrataChipax();
            $pro->id = $fila["prorrataId"];
            $pro->cuenta_id = $fila["cuenta_id"];
            $pro->filtro_id = $fila["filtro_id"];
            $pro->linea_negocio = $fila["linea_negocio"];
            $pro->modelo = $fila["modelo"];
            $pro->monto = $fila["monto"];
            $pro->periodo = $fila["periodo"];
            $pro->gasto_chipax_id = $fila["gasto_chipax_id"];
            $pro->empresa_chipax_id = $fila["empresa_chipax_id"];
            
            $remuneracion->spProrrataChipax[] = $pro;
            $remuneraciones[] = $remuneracion;
        }
        
        return $remuneraciones;
    }

}
