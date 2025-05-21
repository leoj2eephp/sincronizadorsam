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
     * Busca y retorna el objeto SimpleXML del documento que coincide con el Folio y RutProveedor.
     * Devuelve null si no se encuentra.
     */
    private function findFacturaXml($Folio, $RutProveedor) {
        $xmls = scandir(Yii::$app->basePath . DIRECTORY_SEPARATOR . self::PATH);
        foreach ($xmls as $xml) {
            if ($xml == "." || $xml == "..") {
                continue;
            }
            $rutadoc = pathinfo($xml);
            if (isset($rutadoc['extension']) && $rutadoc['extension'] == "xml") {
                $lector = simplexml_load_file(realpath(Yii::$app->basePath . DIRECTORY_SEPARATOR . self::PATH . DIRECTORY_SEPARATOR . $xml));
                foreach ($lector as $dte) {
                    $documento = $dte->Documento;
                    $encabezado = $documento->Encabezado;
                    if ($encabezado->IdDoc->Folio == $Folio && strtoupper($encabezado->Emisor->RUTEmisor) == strtoupper($RutProveedor)) {
                        return $documento;
                    }
                }
            }
        }
        return null;
    }

    public function xmlExist($Folio, $RutProveedor) {
        return $this->findFacturaXml($Folio, $RutProveedor) !== null;
    }

    private function generateHtml($Folio, $RutProveedor, $categoria_id = null) {
        $output = "";
        $documento = $this->findFacturaXml($Folio, $RutProveedor);
        if ($documento === null) {
            return $output;
        }
        $encabezado = $documento->Encabezado;

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
    }
}
