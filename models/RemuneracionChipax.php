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
            [['id', 'empresa_id', 'usuario_id', 'empleado_id', 'monto_liquido', 'moneda_id'], 'integer'],
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
            'empresa_id' => 'Empresa ID',
            'usuario_id' => 'Usuario ID',
            'periodo' => 'Periodo',
            'empleado_id' => 'Empleado ID',
            'monto_liquido' => 'Monto Liquido',
            'moneda_id' => 'Moneda ID',
            'liquidacion' => 'Liquidacion',
            'nombre_empleado' => 'Nombre Empleado',
            'apellido_empleado' => 'Apellido Empleado',
            'rut_empleado' => 'Rut Empleado',
            'email_empleado' => 'Email Empleado',
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
        $honorarios = [];
        
        foreach ($spResult as $fila) {
            $honorario = new RemuneracionChipax();
            $honorario->id = $fila["remuId"];
            $honorario->empresa_id = $fila["empresa_id"];
            $honorario->usuario_id = $fila["usuario_id"];
            $honorario->periodo = $fila["periodo"];
            $honorario->empleado_id = $fila["empleado_id"];
            $honorario->monto_liquido = $fila["monto_liquido"];
            $honorario->moneda_id = $fila["moneda_id"];
            $honorario->liquidacion = $fila["liquidacion"];
            $honorario->nombre_empleado = $fila["nombre_empleado"];
            $honorario->apellido_empleado = $fila["apellido_empleado"];
            $honorario->rut_empleado = $fila["rut_empleado"];
            $honorario->email_empleado = $fila["email_empleado"];
            
            $pro = new ProrrataChipax();
            $pro->id = $fila["prorrataId"];
            $pro->cuenta_id = $fila["cuenta_id"];
            $pro->filtro_id = $fila["filtro_id"];
            $pro->linea_negocio = $fila["linea_negocio"];
            $pro->modelo = $fila["modelo"];
            $pro->monto = $fila["monto"];
            $pro->periodo = $fila["periodo"];
            $pro->gasto_chipax_id = $fila["gasto_chipax_id"];
            
            $honorario->spProrrataChipax[] = $pro;
            $honorarios[] = $honorario;
        }
        
        return $honorarios;
    }

}
