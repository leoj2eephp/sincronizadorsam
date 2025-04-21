<?php

namespace app\models;

use Yii;

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

    public function print($Folio, $RutProveedor) {
        $this->output = "";

        $xmls = scandir(Yii::$app->basePath . DIRECTORY_SEPARATOR . self::PATH);
        $continue = true;
        foreach ($xmls as $xml) {
            if (!$continue) {
                break;
            }
            if ($xml == "." || $xml == ".") {
                continue;
            }
            $rutadoc = pathinfo($xml);
            $extension = $rutadoc['extension'];
            if ($extension == "xml") {
                $lector = simplexml_load_file(realpath(Yii::$app->basePath . DIRECTORY_SEPARATOR . self::PATH . DIRECTORY_SEPARATOR . $xml));
                foreach ($lector as $dte) {
                    $documento = $dte->Documento;
                    $encabezado = $documento->Encabezado;
                    if ($encabezado->IdDoc->Folio != $Folio || strtoupper($encabezado->Emisor->RUTEmisor) != strtoupper($RutProveedor)) {
                        continue;
                    }
                    $this->output .= "<table class='table'>";
                    $this->output .=      "<tr>" .
                        "<td>Folio</td>" .
                        "<td>Fecha emisión</td>" .
                        "<td>Fecha vencimiento</td>" .
                        "<td>Monto total</td>" .
                        "</tr>";
                    $this->output .=      "<tr>" .
                        "<td>" . $encabezado->IdDoc->Folio . "</td>" .
                        "<td>" . $encabezado->IdDoc->FchEmis . "</td>" .
                        "<td>" . $encabezado->IdDoc->FchVenc . "</td>" .
                        "<td>" . "$" . number_format((float)$encabezado->Totales->MntTotal, 0, '', '.') . "</td>" .
                        "</tr>";
                    $this->output .= "</table>";
                    $this->output .= "<table class='table'>";
                    $this->output .=      "<tr>" .
                        "<th>Proveedor</th>" .
                        "<td>" . $encabezado->Emisor->RUTEmisor . "</td>" .
                        "<td>" . $encabezado->Emisor->RznSoc . "</td>" .
                        "</tr>";
                    $this->output .= "</table>";

                    $this->output .= "</table>";
                    $this->output .= "<table class='table'>";
                    $this->output .=      "<tr>" .
                        "<th>Patente</th>" .
                        "<td>" . $encabezado->Transporte->Patente . "</td>" .
                        "<th>Nro Guia</th>" .
                        "<td>" . $documento->Referencia[0]->FolioRef . "</td>" .
                        "</tr>" .
                        "<tr>" .
                        "<th> Litros </th>" .
                        "<td>" . $documento->Detalle[2]->DscItem . "</td>" .
                        "</tr>";
                    $this->output .= "</table>";

                    if (count($documento->Detalle) > 0) {
                        $this->output .= "<b>Detalles</b>";
                        $this->output .= "<div style='max-height:250px;width:100%;overflow:auto;'>";
                        $this->output .= "    <table class='table'>";
                        $this->output .=          "<tr>" .
                            "<td>Descripción</td>" .
                            "<td>Cantidad</td>" .
                            "<td>Precio</td>" .
                            "<td>Total</td>" .
                            "</tr>";
                        foreach ($documento->Detalle as $detalle) {
                            $this->output .=      "<tr>" .
                                "<td>" . $detalle->NmbItem . "</td>" .
                                "<td>" . $detalle->QtyItem . "</td>" .
                                "<td>" . "$" . number_format((float)$detalle->PrcItem, 0, '', '.') . "</td>" .
                                "<td>" . "$" . number_format((float)$detalle->MontoItem, 0, '', '.') . "</td>" .
                                "</tr>";
                        }
                        $this->output .= "    </table>";
                        $this->output .= "</div>";
                    }

                    $this->output .= "<table class='table'>";
                    $this->output .=      "<tr>" .
                        "<th>Monto Neto</th>" .
                        "<th>IVA</th>" .
                        "<th>Total</th>" .
                        "</tr>";
                    $this->output .=      "<tr>" .
                        "<td>" . "$" . number_format((float)$encabezado->Totales->MntNeto, 0, '', '.') . "</td>" .
                        "<td>" . "$" . number_format((float)$encabezado->Totales->IVA, 0, '', '.') . "</td>" .
                        "<td>" . "$" . number_format((float)$encabezado->Totales->MntTotal, 0, '', '.') . "</td>" .
                        "</tr>";
                    $this->output .= "</table>";
                    $continue = false;
                }
            }
        }
        echo $this->output;
    }
}
