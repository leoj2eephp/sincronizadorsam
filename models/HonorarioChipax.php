<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "honorario_chipax".
 *
 * @property int $id
 * @property string $fecha_emision
 * @property int $moneda_id
 * @property int $monto_liquido
 * @property int $numero_boleta
 * @property string $nombre_emisor
 * @property string $rut_emisor
 * @property int|null $usuario_id
 * @property int $empresa_chipax_id
 *
 * @property ProrrataChipax[] $prorrataChipax
 * @property GastoCompleta $gastoCompleta
 */
class HonorarioChipax extends \yii\db\ActiveRecord {

    public $sincronizado;
    public $spProrrataChipax;

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'honorario_chipax';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['id', 'fecha_emision', 'moneda_id', 'monto_liquido', 'numero_boleta', 'nombre_emisor', 'rut_emisor'], 'required'],
            [['id', 'moneda_id', 'monto_liquido', 'numero_boleta', 'usuario_id', "empresa_chipax_id"], 'integer'],
            [['fecha_emision', 'sincronizado', 'spProrrataChipax'], 'safe'],
            [['nombre_emisor'], 'string', 'max' => 45],
            [['rut_emisor'], 'string', 'max' => 12],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'fecha_emision' => 'Fecha Emision',
            'moneda_id' => 'Moneda ID',
            'monto_liquido' => 'Monto Liquido',
            'numero_boleta' => 'Numero Boleta',
            'nombre_emisor' => 'Nombre Emisor',
            'rut_emisor' => 'Rut Emisor',
            'usuario_id' => 'Usuario ID',
            'empresa_chipax_id' => 'Empresa Chipax',
        ];
    }

    /**
     * Gets query for [[ProrrataChipaxes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProrrataChipax() {
        return $this->hasMany(ProrrataChipax::class, ['honorario_chipax_id' => 'id']);
    }

    /**
     * Gets query for [[GastoCompleta]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGastoCompleta() {
        return $this->hasMany(GastoCompleta::class, ['nro_documento' => 'numero_boleta']);
    }

    public static function convertSPResultToArrayModel($spResult) {
        $honorarios = [];
        
        foreach ($spResult as $fila) {
            $honorario = new HonorarioChipax();
            $honorario->id = $fila["gastoId"];
            $honorario->fecha_emision = $fila["fecha_emision"];
            $honorario->moneda_id = $fila["moneda_id"];
            $honorario->monto_liquido = $fila["monto_liquido"];
            $honorario->numero_boleta = $fila["numero_boleta"];
            $honorario->nombre_emisor = $fila["nombre_emisor"];
            $honorario->rut_emisor = $fila["rut_emisor"];
            $honorario->usuario_id = $fila["usuario_id"];
            // Este nuevo flag identificará la empresa de la que proviene el gasto de Chipax.
            // 1. Otzi
            // 2. Conejero Maquinarias SPA
            $honorario->empresa_chipax_id = $fila["empresa_chipax_id"];
            
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
            
            $honorario->spProrrataChipax[] = $pro;
            $honorarios[] = $honorario;
        }
        
        return $honorarios;
    }
    
}
