<?php

namespace app\models;
use Yii;
use app\models\FlujoCajaCartola;

/**
 * Description of LectorFactura
 *
 * @author crmoya
 */
class LectorFactura {

    /**
     * USO
     * $lector = new LectorFactura();
     * $lector->print("Folio","RUT_PROVEEDOR");
     */

    const PATH = "documents";
    public $output;

    public function print($Folio, $RutProveedor, $categoria_id = null) {
        $html = $this->generateHtml($Folio, $RutProveedor, $categoria_id);
        echo $html;
    }

    public function getHtml($Folio, $RutProveedor, $categoria_id = null) {
        return $this->generateHtml($Folio, $RutProveedor, $categoria_id);
    }

    /**
     * Busca una factura en los XMLs y ejecuta un callback cuando encuentra coincidencia exacta de folio y RUT.
     * El callback recibe ($documento, $encabezado, $filePath)
     */
    private function findFactura($Folio, $RutProveedor, callable $callback) {
        $pattern = Yii::$app->basePath . DIRECTORY_SEPARATOR . self::PATH . DIRECTORY_SEPARATOR . "*.xml";
        $xmlFiles = glob($pattern);
        $rutProveedorUpper = strtoupper($RutProveedor);
        $folioPattern = '/(^|[^0-9])' . preg_quote($Folio, '/') . '([^0-9]|$)/';
        foreach ($xmlFiles as $filePath) {
            $filename = pathinfo($filePath, PATHINFO_FILENAME);
            if (preg_match($folioPattern, $filename) === 1) {
                $lector = simplexml_load_file($filePath);
                foreach ($lector as $dte) {
                    $documento = $dte->Documento;
                    $encabezado = $documento->Encabezado;
                    if (
                        (string)$encabezado->IdDoc->Folio === (string)$Folio &&
                        strtoupper((string)$encabezado->Emisor->RUTEmisor) === $rutProveedorUpper
                    ) {
                        $result = $callback($documento, $encabezado, $filePath);
                        if ($result !== null) {
                            return $result;
                        }
                    }
                }
            }
        }
        return null;
    }

    public function xmlExist($Folio, $RutProveedor) {
        return $this->findFactura($Folio, $RutProveedor, function() {
            return true;
        }) === true;
    }

    private function generateHtml($Folio, $RutProveedor, $categoria_id = null) {
        return $this->findFactura($Folio, $RutProveedor, function($documento, $encabezado) use ($categoria_id) {
            $output = "";
            $output .= "<table class='table'>";
            $output .=      "<tr>" .
                "<td>Folio</td>" .
                "<td>Fecha emisión</td>" .
                "<td>Fecha vencimiento</td>" .
                "<td>Monto total</td>" .
                "</tr>";
            $output .=      "<tr>" .
                "<td>" . $encabezado->IdDoc->Folio . "</td>" .
                "<td>" . $encabezado->IdDoc->FchEmis . "</td>" .
                "<td>" . $encabezado->IdDoc->FchVenc . "</td>" .
                "<td>" . "$" . number_format((float)$encabezado->Totales->MntTotal, 0, '', '.') . "</td>" .
                "</tr>";
            $output .= "</table>";
            $output .= "<table class='table'>";
            $output .=      "<tr>" .
                "<th>Proveedor</th>" .
                "<td>" . $encabezado->Emisor->RUTEmisor . "</td>" .
                "<td>" . $encabezado->Emisor->RznSoc . "</td>" .
                "</tr>";
            $output .= "</table>";

            if (
                array_key_exists($categoria_id, FlujoCajaCartola::CATEGORIAS_COMBUSTIBLES_CHIPAX) || 
                array_key_exists($categoria_id, FlujoCajaCartola::CATEGORIAS_COMBUSTIBLES_CHIPAX_SPA) ||
                array_key_exists($categoria_id, FlujoCajaCartola::CATEGORIAS_COMBUSTIBLES_RINDEGASTOS)
            ) {
                $output .= "<table class='table'>";
                $output .=      "<tr>" .
                    "<th>Patente</th>" .
                    "<td>" . $encabezado->Transporte->Patente . "</td>" .
                    "<th>Nro Guia</th>" .
                    "<td>" . $documento->Referencia[0]->FolioRef . "</td>" .
                    "</tr>" .
                    "<tr>" .
                    "<th> Litros </th>" .
                    "<td>" . $documento->Detalle[2]->DscItem . "</td>" .
                    "</tr>";
                $output .= "</table>";
            }

            if (count($documento->Detalle) > 0) {
                $output .= "<b>Detalles</b>";
                $output .= "<div style='max-height:250px;width:100%;overflow:auto;'>";
                $output .= "    <table class='table'>";
                $output .=          "<tr>" .
                    "<td>Descripción</td>" .
                    "<td>Cantidad</td>" .
                    "<td>Precio</td>" .
                    "<td>Total</td>" .
                    "</tr>";
                foreach ($documento->Detalle as $detalle) {
                    $output .=      "<tr>" .
                        "<td>" . $detalle->NmbItem . "</td>" .
                        "<td>" . $detalle->QtyItem . "</td>" .
                        "<td>" . "$" . number_format((float)$detalle->PrcItem, 0, '', '.') . "</td>" .
                        "<td>" . "$" . number_format((float)$detalle->MontoItem, 0, '', '.') . "</td>" .
                        "</tr>";
                }
                $output .= "    </table>";
                $output .= "</div>";
            }

            $output .= "<table class='table'>";
            $output .=      "<tr>" .
                "<th>Monto Neto</th>" .
                "<th>IVA</th>" .
                "<th>Total</th>" .
                "</tr>";
            $output .=      "<tr>" .
                "<td>" . "$" . number_format((float)$encabezado->Totales->MntNeto, 0, '', '.') . "</td>" .
                "<td>" . "$" . number_format((float)$encabezado->Totales->IVA, 0, '', '.') . "</td>" .
                "<td>" . "$" . number_format((float)$encabezado->Totales->MntTotal, 0, '', '.') . "</td>" .
                "</tr>";
            $output .= "</table>";
            return $output;
        }) ?? "";
    }
}
