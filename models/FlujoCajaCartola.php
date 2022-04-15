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

    public static function convert2Model($jsonArreglo, $fecha_desde = null, $fecha_hasta = null) {
        $data = array();
        $sw = false;
        $flujoCajaCartola = null;
        $folios = array();   // para verificar si existe algún folio repetido

        ProrrataChipax::deleteAll();
        CompraChipax::deleteAll();
        GastoChipax::deleteAll();
        HonorarioChipax::deleteAll();
        RemuneracionChipax::deleteAll();

        foreach ($jsonArreglo as $json) {
            foreach ($json["Compras"] as $c) {
                $fecha_emision = \app\components\Helper::formatToDBDate($c["fecha_emision"]);
                if ($fecha_desde !== null) {
                    if ($fecha_emision < $fecha_desde) {
                        $sw = false;
                        break;
                    }
                }
                if ($fecha_hasta !== null) {
                    if ($fecha_emision > $fecha_hasta) {
                        $sw = false;
                        break;
                    }
                }

                foreach ($c["Prorratas"] as $p) {
                    if ($p["linea_negocio_id"] == 5671 || array_key_exists($p["cuenta_id"], self::CATEGORIAS_COMBUSTIBLES_CHIPAX)) {
                        $sw = true;
                        break;
                    }
                }

                if ($sw)
                    break;
            }

            if (!$sw) {
                foreach ($json["Gastos"] as $g) {
                    $fecha_emision = \app\components\Helper::formatToDBDate($g["fecha"]);
                    if ($fecha_desde !== null) {
                        if ($fecha_emision < $fecha_desde) {
                            break;
                        }
                    }
                    if ($fecha_hasta !== null) {
                        if ($fecha_emision > $fecha_hasta) {
                            break;
                        }
                    }

                    foreach ($g["Prorratas"] as $p) {
                        if ($p["linea_negocio_id"] == 5671 || array_key_exists($p["cuenta_id"], self::CATEGORIAS_COMBUSTIBLES_CHIPAX)) {
                            $sw = true;
                            break;
                        }
                    }

                    if ($sw)
                        break;
                }
            }

            if (!$sw) {
                foreach ($json["Honorarios"] as $h) {
                    $fecha_emision = \app\components\Helper::formatToDBDate($h["fecha_emision"]);
                    if ($fecha_desde !== null) {
                        if ($fecha_emision < $fecha_desde) {
                            break;
                        }
                    }
                    if ($fecha_hasta !== null) {
                        if ($fecha_emision > $fecha_hasta) {
                            break;
                        }
                    }

                    foreach ($h["Prorratas"] as $p) {
                        if ($p["linea_negocio_id"] == 5671) {
                            $sw = true;
                            break;
                        }
                    }

                    if ($sw)
                        break;
                }
            }

            if (!$sw) {
                foreach ($json["Remuneracions"] as $r) {
                    $fecha_emision = \app\components\Helper::formatToDBDate($json["fecha"]);
                    if ($fecha_desde !== null) {
                        if ($fecha_emision < $fecha_desde) {
                            break;
                        }
                    }
                    if ($fecha_hasta !== null) {
                        if ($fecha_emision > $fecha_hasta) {
                            break;
                        }
                    }

                    foreach ($r["Prorratas"] as $p) {
                        if ($p["linea_negocio_id"] == 5671 || array_key_exists($p["cuenta_id"], self::CATEGORIAS_COMBUSTIBLES_CHIPAX)) {
                            $sw = true;
                            break;
                        }
                    }

                    if ($sw)
                        break;
                }
            }

            if (!$sw)
                continue;

            $flujoCajaCartola = new FlujoCajaCartola();
            $flujoCajaCartola->abono = $json["abono"];
            $flujoCajaCartola->cargo = $json["cargo"];
            $flujoCajaCartola->descripcion = $json["descripcion"];
            $flujoCajaCartola->fecha = $json["fecha"];
            $flujoCajaCartola->id = $json["id"];
            $flujoCajaCartola->cuenta_corriente_id = $json["cuenta_corriente_id"];
            $flujoCajaCartola->tipo_cartola_id = $json["tipo_cartola_id"];
            //$flujoCajaCartola->sincronizacion_id_anterior = $anterior;

            foreach ($json["Compras"] as $c) {
                try {   // este bloque evitará que haya un folio duplicado mostrándose
                    if (array_search($c["folio"], $folios) !== false) {
                        continue;
                    }
                } catch (\yii\base\ErrorException $ex) {
                    echo "<pre>";
                    print_r($ex);
                    break;
                }

                if ($fecha_desde !== null) {
                    if ($c["fecha_emision"] < $fecha_desde) {
                        $sw = false;
                        break;
                    }
                }
                if ($fecha_hasta !== null) {
                    if ($c["fecha_emision"] > $fecha_hasta) {
                        $sw = false;
                        break;
                    }
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
                    if ($compras->save()) {
                        foreach ($c["Prorratas"] as $pro) {
                            if ($pro["linea_negocio_id"] == 5671 || array_key_exists($p["cuenta_id"], self::CATEGORIAS_COMBUSTIBLES_CHIPAX)) {
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
                                //$compras->prorratas[] = $prorrata;
                                if (!$prorrata->save()) {
                                    echo "Hubo un error al insertar las prorratas";
                                    echo join(", ", $prorrata->getFirstErrors());
                                }
                            }
                        }
                    } else {
                        echo "Hubo en error al insertar Compra.";
                        echo join(",", $compras->getFirstErrors());
                        continue;
                    }
                } catch (Exception $ex) {
                    Yii::error("Error al insertar en CompraChipax");
                    Yii::error($ex->getMessage());
                }

                //$flujoCajaCartola->compras[] = $compras;
                $folios[] = $c["folio"];
            }

            foreach ($json["Gastos"] as $g) {
                if ($fecha_desde !== null) {
                    if ($g["fecha"] < $fecha_desde) {
                        $sw = false;
                        break;
                    }
                }
                if ($fecha_hasta !== null) {
                    if ($g["fecha"] > $fecha_hasta) {
                        $sw = false;
                        break;
                    }
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
                    if ($gasto->save()) {
                        foreach ($g["Prorratas"] as $pro) {
                            if ($pro["linea_negocio_id"] == 5671 || array_key_exists($p["cuenta_id"], self::CATEGORIAS_COMBUSTIBLES_CHIPAX)) {
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
                            }
                        }
                    } else {
                        echo "Hubo en error al insertar Gasto.";
                        echo join(",", $gasto->getFirstErrors());
                        continue;
                    }
                } catch (Exception $ex) {
                    Yii::error("Error al insertar en GastoChipax");
                    Yii::error($ex->getMessage());
                }

                // $flujoCajaCartola->gastos[] = $gasto;
            }

            foreach ($json["Honorarios"] as $h) {
                if ($fecha_desde !== null) {
                    if ($h["fecha_emision"] < $fecha_desde) {
                        $sw = false;
                        break;
                    }
                }
                if ($fecha_hasta !== null) {
                    if ($h["fecha_emision"] > $fecha_hasta) {
                        $sw = false;
                        break;
                    }
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
                    if ($honorario->save()) {
                        foreach ($h["Prorratas"] as $pro) {
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
                        }
                    } else {
                        echo "Hubo en error al insertar Honorario.";
                        echo join(",", $honorario->getFirstErrors());
                        continue;
                    }
                } catch (Exception $ex) {
                    Yii::error("Error al insertar en HonorarioChipax");
                    Yii::error($ex->getMessage());
                }

                //$flujoCajaCartola->honorarios[] = $honorario;
            }

            foreach ($json["Remuneracions"] as $r) {
                if ($fecha_desde !== null) {
                    if ($json["fecha"] < $fecha_desde) {
                        $sw = false;
                        break;
                    }
                }
                if ($fecha_hasta !== null) {
                    if ($json["fecha"] > $fecha_hasta) {
                        $sw = false;
                        break;
                    }
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
                    if ($remuneracion->save()) {
                        foreach ($r["Prorratas"] as $pro) {
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
                        }
                    } else {
                        echo "Hubo en error al insertar Remuneración.";
                        echo join(",", $remuneracion->getFirstErrors());
                        continue;
                    }
                } catch (Exception $ex) {
                    Yii::error("Error al insertar en RemuneracionChipax");
                    Yii::error($ex->getMessage());
                }

                //$flujoCajaCartola->remuneracions[] = $remuneracion;
            }

            $sw = false;    // para que vuelva a buscar solo si la linea de negocio es departamento de maquinaria
            //array_push($data, $flujoCajaCartola);
            $data[] = $flujoCajaCartola;
        }

        return $data;
    }

    private function getLineasNegocio() {
    }
}
