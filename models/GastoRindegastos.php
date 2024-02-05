<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "gasto_rindegastos".
 *
 * @property int $id
 * @property string|null $supplier
 * @property string|null $issue_date
 * @property int|null $net
 * @property int|null $total
 * @property string|null $category
 * @property string|null $category_code
 * @property string|null $category_group
 * @property string|null $note
 * @property int|null $expense_policy_id
 * @property int|null $report_id
 * @property int|null $status
 * @property int|null $tax
 * @property int|null $other_taxes
 * @property bool $chipax
 *
 * @property GastoCompletaRindegastos[] $gastoCompletaRindegastos
 */
class GastoRindegastos extends \yii\db\ActiveRecord {
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'gasto_rindegastos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['id'], 'required'],
            [['id', 'net', 'total', 'expense_policy_id', 'report_id', 'status', 'tax', 'other_taxes'], 'integer'],
            [['supplier', 'category', 'category_code', 'category_group', 'note'], 'string'],
            [['issue_date'], 'safe'],
            [['chipax'], 'boolean'],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'supplier' => 'Supplier',
            'issue_date' => 'Issue Date',
            'net' => 'Net',
            'total' => 'Total',
            'category' => 'Category',
            'category_code' => 'Category Code',
            'category_group' => 'Category Group',
            'note' => 'Note',
            'expense_policy_id' => 'Expense Policy ID',
            'report_id' => 'Report ID',
            'status' => 'Status',
            'tax' => 'Tax',
            'other_taxes' => 'Other Taxes',
            'chipax' => 'Chipax',
        ];
    }

    /**
     * Gets query for [[GastoCompletaRindegastos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGastoCompletaRindegastos() {
        return $this->hasMany(GastoCompletaRindegastos::class, ['gasto_rindegastos_id' => 'id']);
    }

    public static function sincronizarGastos($json) {
        $transaction = Yii::$app->db->beginTransaction();
        // BORRAR todos los datos anteriores..
        foreach ($json->Expenses as $gasto) {
            try {
                $gastoRindeGastos = new GastoRindegastos();
                $gastoRindeGastos->id = $gasto->Id;
                $gastoRindeGastos->supplier = $gasto->Supplier;
                $gastoRindeGastos->issue_date = $gasto->IssueDate;
                $gastoRindeGastos->net = $gasto->Net;
                $gastoRindeGastos->total = $gasto->Total;
                $gastoRindeGastos->category = $gasto->Category;
                $gastoRindeGastos->category_code = $gasto->CategoryCode;
                $gastoRindeGastos->note = $gasto->Note;
                $gastoRindeGastos->expense_policy_id = $gasto->ExpensePolicyId;
                $gastoRindeGastos->report_id = $gasto->ReportId;
                $gastoRindeGastos->status = $gasto->Status;
                $gastoRindeGastos->tax = $gasto->Tax;
                $gastoRindeGastos->other_taxes = $gasto->OtherTaxes;

                if ($gastoRindeGastos->save()) {
                    $gastoCompletaRG = new GastoCompletaRindegastos();
                    $gastoCompletaRG->gasto_rindegastos_id = $gastoRindeGastos->id;
                    $gastoCompletaRG->retenido = "" . $gasto->Retention;
                    $gastoCompletaRG->iva = "" . $gasto->Tax;
                    $gastoCompletaRG->total_calculado = $gasto->Total;

                    if (isset($gasto->ExtraFields)) {
                        foreach ($gasto->ExtraFields as $extra) {
                            switch (trim($extra->Name)) {
                                case "Centro de Costo / Faena":
                                    $gastoCompletaRG->centro_costo_faena = $extra->Value;
                                    break;
                                case "Km.Carguío":
                                    $gastoCompletaRG->km_carguio = $extra->Value;
                                    break;
                                case "Litros Combustible":
                                    $gastoCompletaRG->litros_combustible = $extra->Value;
                                    break;
                                case "Nombre quien rinde":
                                    $gastoCompletaRG->nombre_quien_rinde = $extra->Value;
                                    break;
                                case "Vehiculo o Equipo":
                                    $gastoCompletaRG->vehiculo_equipo = $extra->Value;
                                    break;
                                case "RUT proveedor":
                                    $gastoCompletaRG->rut_proveedor = $extra->Value;
                                    break;
                                case "Tipo de Documento":
                                    $gastoCompletaRG->tipo_documento = $extra->Value;
                                    break;
                                case "Número de Documento":
                                    $gastoCompletaRG->nro_documento = $extra->Value;
                                    break;
                                case "Cantidad ":
                                    $gastoCompletaRG->cantidad = $extra->Value;
                                    break;
                                case "Unidad":
                                    $gastoCompletaRG->unidad = $extra->Value;
                                    break;
                            }
                        }
                    }
                    if (!$gastoCompletaRG->save()) {
                        $ndoc = "";
                        if (isset($gastoCompletaRG->nro_documento))
                            $ndoc = "Nro Documento: $gastoCompletaRG->nro_documento";
                        throw new \Exception("ERROR al sincronizar GastoCompleta ($ndoc): " . join(", ", $gastoCompletaRG->getFirstErrors()));
                    }
                } else {
                    throw new \Exception("ERROR al sincronizar Gasto: " . join(", ", $gastoRindeGastos->getFirstErrors()));
                }
            } catch (\Exception $e) {
                echo 'Se produjo un error: ' . $e->getMessage();
                $transaction->rollBack();
                continue;
            }
        }
        $transaction->commit();
    }
}
