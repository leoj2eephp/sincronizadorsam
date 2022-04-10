<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "gasto".
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
 * @property ExtraGasto[] $extraGastos
 * @property GastoCompleta[] $gastoCompleta
 * @property GastoImagen[] $gastoImagens
 */
class Gasto extends \yii\db\ActiveRecord {
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'gasto';
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
     * Gets query for [[ExtraGastos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getExtraGastos() {
        return $this->hasMany(ExtraGasto::class, ['gasto_id' => 'id']);
    }

    /**
     * Gets query for [[GastoCompleta]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGastoCompleta() {
        return $this->hasMany(GastoCompleta::class, ['gasto_id' => 'id']);
    }

    /**
     * Gets query for [[GastoImagens]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGastoImagens() {
        return $this->hasMany(GastoImagen::class, ['gasto_id' => 'id']);
    }
}
