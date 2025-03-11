<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "compra_response".
 *
 * @property int $id
 * @property int $idEmpresa
 * @property int $idUsuario
 * @property int $idParent
 * @property int $idPatriarch
 * @property int $tipo
 * @property int $folio
 * @property string $razonSocial
 * @property string $rutEmisor
 * @property string $fechaEmision
 * @property string $fechaVencimiento
 * @property string $fechaPagoInterna
 * @property string $periodo
 * @property string $estado
 * @property int $montoNeto
 * @property int $montoExento
 * @property int $iva
 * @property int $montoTotal
 * @property int $descuento
 * @property string|null $referencias
 * @property string|null $archivo
 * @property string $created
 * @property string $modified
 * @property int $montoCorregido
 * @property string $tipoCompra
 * @property string $fechaRecepcion
 * @property string $fechaAcuse
 * @property int $montoIvaRecuperable
 * @property int $montoIvaNoRecuperable
 * @property int|null $montoImpSinCredito
 * @property string $codigoIvaNoRecuperable
 * @property int $montoNetoActivoFijo
 * @property int $ivaActivoFijo
 * @property int $ivaUsoComun
 * @property string $impuestoVehiculo
 * @property int|null $montoActivoFijo
 * @property int|null $montoCodigoIvaNoRecuperable
 * @property int|null $montoIvaActivoFijo
 * @property int $impuestoSinDerechoCredito
 * @property int $ivaNoRetenido
 * @property int $tabacosPuros
 * @property string $detTipoTransaccion
 * @property string $tipoImpuesto
 * @property string $dhdrCodigo
 * @property int|null $dinrMontoIvaNoRec
 * @property int $tabacosCigarrillos
 * @property int $tabacosElaborados
 * @property int|null $nceNdeSobreFactCompra
 * @property int|null $codigoOtroImpuesto
 * @property int $valorOtroImpuesto
 * @property int|null $tasaOtroImpuesto
 * @property int $idMoneda
 * @property string $urlXml
 * @property string $urlPdf
 * @property int $cambiarTipoTran
 * @property int $dcvCodigo
 * @property int|null $dcvEstadoContab
 * @property string $tipoTransaccion
 * @property int|null $anulado
 * @property int $detCodigo
 * @property int $emisorNota
 * @property string $eventoReceptor
 * @property string $eventoReceptorLeyenda
 * @property string|null $fechaReclamo
 * @property int|null $ivaRetenidoTotal
 * @property int|null $ivaRetenidoParcial
 * @property int|null $ivaPropio
 * @property int|null $ivaTerceros
 * @property int|null $netoComisionLiquidoFactura
 * @property int|null $exentoComisionLiquidoFactura
 * @property int|null $ivaComisionLiquidoFactura
 * @property int|null $ivaFueraPlazo
 * @property string|null $numeroIdentificacionReceptorExtranjero
 * @property string|null $nacionalidadReceptorExtranjero
 * @property int|null $creditoEmpresaConstructora
 * @property int|null $impuestoZonaFranca
 * @property int|null $garantiaDepEnvases
 * @property int|null $indicadorVentaSinCosto
 * @property int $editable
 * @property string|null $ultimaActualizacionXml
 * @property int $estadoCompraId
 * @property int $agregadoManualmente
 * @property int $comentarioCount
 * @property int $tipoDocumentoReferencia
 * @property int|null $folioDocumentoReferencia
 */
class CompraResponse extends ActiveRecord {
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'compra_response'; // Cambia esto si tu tabla tiene otro nombre
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['id', 'idEmpresa', 'idUsuario', 'idParent', 'idPatriarch', 'tipo', 'folio', 'montoNeto', 'montoExento', 'iva', 'montoTotal', 'descuento', 'montoCorregido', 'montoIvaRecuperable', 'montoIvaNoRecuperable', 'idMoneda', 'cambiarTipoTran', 'dcvCodigo', 'detCodigo', 'emisorNota', 'editable', 'estadoCompraId', 'agregadoManualmente', 'comentarioCount', 'tipoDocumentoReferencia'], 'integer'],
            [['razonSocial', 'rutEmisor', 'estado', 'tipoCompra', 'detTipoTransaccion', 'tipoImpuesto', 'dhdrCodigo', 'eventoReceptor', 'eventoReceptorLeyenda', 'codigoIvaNoRecuperable', 'impuestoVehiculo', 'tipoTransaccion'], 'string', 'max' => 255],
            [['fechaEmision', 'fechaVencimiento', 'fechaPagoInterna', 'created', 'modified', 'fechaRecepcion', 'fechaAcuse', 'fechaReclamo'], 'safe'],
            [['urlXml', 'urlPdf', 'referencias', 'archivo', 'ultimaActualizacionXml', 'numeroIdentificacionReceptorExtranjero', 'nacionalidadReceptorExtranjero'], 'string', 'max' => 500],
            [['montoImpSinCredito', 'montoActivoFijo', 'montoCodigoIvaNoRecuperable', 'montoIvaActivoFijo', 'dcvEstadoContab', 'anulado', 'ivaRetenidoTotal', 'ivaRetenidoParcial', 'ivaPropio', 'ivaTerceros', 'netoComisionLiquidoFactura', 'exentoComisionLiquidoFactura', 'ivaComisionLiquidoFactura', 'ivaFueraPlazo', 'creditoEmpresaConstructora', 'impuestoZonaFranca', 'garantiaDepEnvases', 'indicadorVentaSinCosto'], 'default', 'value' => null],
            [['valorOtroImpuesto'], 'integer', 'min' => 0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'idEmpresa' => 'Empresa',
            'idUsuario' => 'Usuario',
            'folio' => 'Folio',
            'razonSocial' => 'Razón Social',
            'rutEmisor' => 'RUT Emisor',
            'fechaEmision' => 'Fecha Emisión',
            'estado' => 'Estado',
            'montoNeto' => 'Monto Neto',
            'montoTotal' => 'Monto Total',
            'tipoCompra' => 'Tipo de Compra',
            'fechaRecepcion' => 'Fecha Recepción',
            'urlXml' => 'XML Documento',
            'urlPdf' => 'PDF Documento',
            'editable' => 'Editable',
            'estadoCompraId' => 'Estado Compra',
        ];
    }

    /**
     * Método para llenar el modelo desde un JSON
     */
    public static function fromJson($json) {
        $data = json_decode($json, true);
        $model = new self();
        foreach ($data as $key => $value) {
            if ($model->hasAttribute($key)) {
                $model->$key = $value;
            }
        }
        return $model;
    }
}
