<?php

namespace app\models;

use Exception;
use Yii;

/**
 * Description of FlujoCajaCartola
 *
 * @author leand
 */
class FlujoCajaCartola {

    public $abono;
    public $cargo;
    public $descripcion;
    public $fecha;
    public $id;
    public $cuenta_corriente_id;
    public $tipo_cartola_id;
    public $ots = array();
    public $dtes = array();
    public $cartolaHija = array();
    public $cartolaMadre = array();
    public $compras = array();
    public $gastos = array();
    public $honorarios = array();
    public $boletaTerceros = array();
    public $remuneracions = array();
    public $previreds = array();
    public $impuestos = array();
    public $validacionesSaldos = array();
    public $saldos = array();
    //public $sincronizado = false;   // atributo que indicará si se encuentra coincidencia con los datos de RindeGastos
    //public $sincronizacion_id_anterior; // ID de la sicronización anterior, para hacer la comparación y saber si hubo modificación

    const CATEGORIAS_COMBUSTIBLES_CHIPAX = [
        77859 => '01 Bencina',
        77469 => '02 Petróleo',
        106808 => '02.a Gas Licuado Vehicular',
        75820 => 'Cop. 01 Bencina',
        124960 => 'Cop. 02 Petróleo',
        142332 => 'Cop. 02.a Gas Licuado Vehicular',
        77470 => 'Cop. 03.1 Parafina',
        77858 => 'CG.- 01 Bencina',
        86680 => 'CG.- 02 Petróleo',
    ];

    const CATEGORIAS_REMUNERACIONES_CHIPAX = [
        75818 => '13 Remuneraciónes del Personal',
        95432 => '13.2 Remuneraciones Personal externo',
        77468 => 'Cop. 13.1 Remuneraciones del Personal Indirecto',
        95433 => 'Cop. 13.2 Remuneraciones Personal externo',
        //77478 => '14 Honorarios Profesionales',
        //'14.1 Honorarios Profesionales Abogados',
        //'14.2 Honorarios Profesionales Notarios, CBR',
        //95286 => '14.3 Honorarios Técnicos, Profesionales, Otros',
        //77477 => 'Cop. 14 Honorarios Profesionales',
        //'Cop. 14.1 Profesionales Abogados',
        //94856 => 'Cop. 14.2 Honorarios Técnicos, Profesionales, Otro',
        //'Cop. 14.3 Honorarios Profesionales Notarios, CBR',
        //75827 => 'CG.- 13 Remuneraciones del Personal',
        //75833 => 'CG.-Honorarios Profesionales',
        //'CG.- 14 Honorarios Profesionales Abogados',
        //94860 => 'CG.- 14.2_Honorarios Técnicos, Profesionales Otros',
        //'CG.- 14.3 Honorarios Profesionales Notarios, CBR',
    ];

    const CATEGORIAS_COMBUSTIBLES_RINDEGASTOS = [
        44639 => "Combustibles",
        54937 => "011-Petorca Cordillera Combustible"
    ];

    public static function convert2Model($jsonArreglo) {
        //$data = array();
        $sw = false;
        $flujoCajaCartola = null;
        $folios = array();   // para verificar si existe algún folio repetido
        $gastosFolios = array();
        $honoFolios = array();
        $remuFolios = array();

        foreach ($jsonArreglo as $json) {
            $flujoCajaCartola = new FlujoCajaCartola();
            $flujoCajaCartola->abono = $json["abono"];
            $flujoCajaCartola->cargo = $json["cargo"];
            $flujoCajaCartola->descripcion = $json["descripcion"];
            $flujoCajaCartola->fecha = $json["fecha"];
            $flujoCajaCartola->id = $json["id"];
            $flujoCajaCartola->cuenta_corriente_id = $json["cuenta_corriente_id"];
            $flujoCajaCartola->tipo_cartola_id = $json["tipo_cartola_id"];

            foreach ($json["Compras"] as $c) {
                try {   // este bloque evitará que haya un folio duplicado mostrándose
                    if (array_search($c["folio"], $folios) !== false) {
                        continue;
                    }
                    if ($c["tipo"] == 52) {
                        continue;
                    }
                } catch (\yii\base\ErrorException $ex) {
                    echo "<pre>";
                    print_r($ex);
                    break;
                }

                $compras = new CompraChipax();
                $compras->fecha_emision = $c["fecha_emision"];
                $compras->folio = $c["folio"];
                $compras->id = $c["id"];
                $compras->moneda_id = $c["moneda_id"];
                $compras->monto_total = $c["monto_total"];
                $compras->razon_social = $c["razon_social"];
                $compras->rut_emisor = $c["rut_emisor"];
                $compras->tipo = $c["tipo"];

                try {
                    foreach ($c["Prorratas"] as $pro) {
                        if ($pro["linea_negocio_id"] != 5671) {
                            if (!array_key_exists($pro["cuenta_id"], self::CATEGORIAS_COMBUSTIBLES_CHIPAX)) continue;
                        }
                        if ($compras->save()) {
                            $prorrata = new ProrrataChipax();
                            $prorrata->cuenta_id = $pro["cuenta_id"];
                            $prorrata->filtro_id = $pro["filtro_id"];
                            $prorrata->id = $pro["id"];
                            $linea_negocio = LineaNegocioChipax::findOne($pro["linea_negocio_id"]);
                            $prorrata->linea_negocio = $linea_negocio->nombre;
                            $prorrata->compra_chipax_id = $pro["foreign_key"];
                            $prorrata->modelo = $pro["modelo"];
                            $prorrata->monto = $pro["monto"];
                            $prorrata->periodo = $pro["periodo"];
                            if (!$prorrata->save()) {
                                echo "Hubo un error al insertar las prorratas";
                                echo join(", ", $prorrata->getFirstErrors());
                            }
                        } else {
                            // echo "Hubo en error al insertar Compra.";
                            //echo join(",", $compras->getFirstErrors());
                            continue;
                        }
                    }
                } catch (Exception $ex) {
                    Yii::error("Error al insertar en CompraChipax");
                    Yii::error($ex->getMessage());
                }

                //$flujoCajaCartola->compras[] = $compras;
                $folios[] = $c["folio"];
            }

            foreach ($json["Gastos"] as $g) {
                try {   // este bloque evitará que haya un folio duplicado mostrándose
                    if (array_search($g["num_documento"], $gastosFolios) !== false) {
                        continue;
                    }
                } catch (\yii\base\ErrorException $ex) {
                    echo "<pre>";
                    print_r($ex);
                    break;
                }

                $gasto = new GastoChipax();
                $gasto->descripcion = $g["descripcion"];
                $gasto->fecha = $g["fecha"];
                $gasto->id = $g["id"];
                $gasto->moneda_id = $g["moneda_id"];
                $gasto->monto = $g["monto"];
                $gasto->num_documento = $g["num_documento"];
                $gasto->proveedor = $g["proveedor"];
                $gasto->responsable = $g["responsable"];
                $gasto->tipo_cambio = $g["tipo_cambio"];
                $gasto->usuario_id = $g["usuario_id"];

                try {
                    foreach ($g["Prorratas"] as $pro) {
                        if ($pro["linea_negocio_id"] != 5671) {
                            if (!array_key_exists($pro["cuenta_id"], self::CATEGORIAS_COMBUSTIBLES_CHIPAX)) continue;
                        }
                        if ($gasto->save()) {
                            $prorrata = new ProrrataChipax();
                            $prorrata->cuenta_id = $pro["cuenta_id"];
                            $prorrata->filtro_id = $pro["filtro_id"];
                            $prorrata->gasto_chipax_id = $pro["foreign_key"];
                            $prorrata->id = $pro["id"];
                            $linea_negocio = LineaNegocioChipax::findOne($pro["linea_negocio_id"]);
                            $prorrata->linea_negocio = $linea_negocio->nombre;
                            $prorrata->modelo = $pro["modelo"];
                            $prorrata->monto = $pro["monto"];
                            $prorrata->periodo = $pro["periodo"];

                            //$gasto->prorratas[] = $prorrata;
                            if (!$prorrata->save()) {
                                echo "Hubo un error al insertar las prorratas";
                                echo join(", ", $prorrata->getFirstErrors());
                            }
                        } else {
                            // echo "Hubo en error al insertar Gasto.";
                            //echo join(",", $gasto->getFirstErrors());
                            continue;
                        }
                    }
                } catch (Exception $ex) {
                    Yii::error("Error al insertar en GastoChipax");
                    Yii::error($ex->getMessage());
                }

                // $flujoCajaCartola->gastos[] = $gasto;
                $folios[] = $g["num_documento"];
            }

            foreach ($json["Honorarios"] as $h) {
                try {   // este bloque evitará que haya un folio duplicado mostrándose
                    if (array_search($h["numero_boleta"], $honoFolios) !== false) {
                        continue;
                    }
                } catch (\yii\base\ErrorException $ex) {
                    echo "<pre>";
                    print_r($ex);
                    break;
                }

                $honorario = new HonorarioChipax();
                $honorario->fecha_emision = $h["fecha_emision"];
                $honorario->id = $h["id"];
                $honorario->moneda_id = $h["moneda_id"];
                $honorario->monto_liquido = $h["monto_liquido"];
                $honorario->numero_boleta = $h["numero_boleta"];
                $honorario->nombre_emisor = $h["nombre_emisor"];
                $honorario->rut_emisor = $h["rut_emisor"];
                $honorario->usuario_id = $h["usuario_id"];

                try {
                    foreach ($h["Prorratas"] as $pro) {
                        if ($pro["linea_negocio_id"] != 5671) {
                            if (!array_key_exists($pro["cuenta_id"], self::CATEGORIAS_COMBUSTIBLES_CHIPAX)) continue;
                        }
                        if ($honorario->save()) {
                            if ($pro["linea_negocio_id"] == 5671 || array_key_exists($pro["cuenta_id"], self::CATEGORIAS_COMBUSTIBLES_CHIPAX)) {
                                $prorrata = new ProrrataChipax();
                                $prorrata->cuenta_id = $pro["cuenta_id"];
                                $prorrata->filtro_id = $pro["filtro_id"];
                                $prorrata->honorario_chipax_id = $pro["foreign_key"];
                                $prorrata->id = $pro["id"];
                                $linea_negocio = LineaNegocioChipax::findOne($pro["linea_negocio_id"]);
                                $prorrata->linea_negocio = $linea_negocio->nombre;
                                $prorrata->modelo = $pro["modelo"];
                                $prorrata->monto = $pro["monto"];
                                $prorrata->periodo = $pro["periodo"];

                                //$honorario->prorratas[] = $prorrata;
                                if (!$prorrata->save()) {
                                    echo "Hubo un error al insertar las prorratas";
                                    echo join(", ", $prorrata->getFirstErrors());
                                }
                            }
                        } else {
                            // echo "Hubo en error al insertar Honorario.";
                            //echo join(",", $honorario->getFirstErrors());
                            continue;
                        }
                    }
                } catch (Exception $ex) {
                    Yii::error("Error al insertar en HonorarioChipax");
                    Yii::error($ex->getMessage());
                }

                //$flujoCajaCartola->honorarios[] = $honorario;
                $honoFolios[] = $h["numero_boleta"];
            }

            foreach ($json["Remuneracions"] as $r) {
                try {   // este bloque evitará que haya un folio duplicado mostrándose
                    if (array_search($r["id"], $remuFolios) !== false) {
                        continue;
                    }
                } catch (\yii\base\ErrorException $ex) {
                    echo "<pre>";
                    print_r($ex);
                    break;
                }

                $remuneracion = new RemuneracionChipax();
                $remuneracion->id = $r["id"];
                $remuneracion->empresa_id = $r["empresa_id"];
                $remuneracion->usuario_id = $r["usuario_id"];
                $remuneracion->periodo = $r["periodo"];
                $remuneracion->empleado_id = $r["empleado_id"];
                $remuneracion->monto_liquido = $r["monto_liquido"];
                $remuneracion->moneda_id = $r["moneda_id"];
                $remuneracion->liquidacion = $r["liquidacion"];

                $remuneracion->nombre_empleado = $r["Empleado"]["nombre"];
                $remuneracion->apellido_empleado = $r["Empleado"]["apellido"];
                $remuneracion->rut_empleado = $r["Empleado"]["rut"];
                $remuneracion->email_empleado = $r["Empleado"]["email"];

                try {
                    foreach ($r["Prorratas"] as $pro) {
                        if ($pro["linea_negocio_id"] != 5671) {
                            if (!array_key_exists($pro["cuenta_id"], self::CATEGORIAS_COMBUSTIBLES_CHIPAX)) continue;
                        }
                        if ($remuneracion->save()) {
                            if ($pro["linea_negocio_id"] == 5671 || array_key_exists($pro["cuenta_id"], self::CATEGORIAS_COMBUSTIBLES_CHIPAX)) {
                                $prorrata = new ProrrataChipax();
                                $prorrata->cuenta_id = $pro["cuenta_id"];
                                $prorrata->filtro_id = $pro["filtro_id"];
                                $prorrata->remuneracion_chipax_id = $pro["foreign_key"];
                                $prorrata->id = $pro["id"];
                                $linea_negocio = LineaNegocioChipax::findOne($pro["linea_negocio_id"]);
                                $prorrata->linea_negocio = $linea_negocio->nombre;
                                $prorrata->modelo = $pro["modelo"];
                                $prorrata->monto = $pro["monto"];
                                $prorrata->periodo = $pro["periodo"];

                                //$remuneracion->prorratas[] = $prorrata;
                                if (!$prorrata->save()) {
                                    echo "Hubo un error al insertar las prorratas";
                                    echo join(", ", $prorrata->getFirstErrors());
                                }
                            }
                        } else {
                            //echo "Hubo en error al insertar Remuneración.";
                            //echo join(",", $remuneracion->getFirstErrors());
                            continue;
                        }
                    }
                } catch (Exception $ex) {
                    Yii::error("Error al insertar en RemuneracionChipax");
                    Yii::error($ex->getMessage());
                }

                //$flujoCajaCartola->remuneracions[] = $remuneracion;
                $remuFolios[] = $r["id"];
            }

            $sw = false;    // para que vuelva a buscar solo si la linea de negocio es departamento de maquinaria
            //array_push($data, $flujoCajaCartola);
            //$data[] = $flujoCajaCartola;
        }

        return true;
    }

    private function getLineasNegocio() {
    }
}
