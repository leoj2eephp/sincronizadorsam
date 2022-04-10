<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * MateriaPublica Upload Document form
 */
class DocumentoAttachForm extends Model {

    /**
     * @var UploadedFile
     */
    public $file;
    public $files = [];
    public $description;
    public $description2;

    const PATH = 'documents' . DIRECTORY_SEPARATOR;
    const FILE_NAME = "loaded.xml";

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['file'], 'file', 'skipOnEmpty' => true,],
            [['files'], 'file', 'skipOnEmpty' => true,],
            [['file', 'file2'], 'file', 'maxSize' => 1024 * 1024 * 100],
            [['description', 'file2', 'description2'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'file' => 'Documento',
            'description' => 'Descripción',
            'file2' => 'Documento',
            'description2' => 'Descripción',
        ];
    }

    public function saveDocument() {
        $folderPath = Yii::getAlias("@app") . DIRECTORY_SEPARATOR . static::PATH;
        foreach ($this->file as $archivo) :
            $path = $folderPath . $archivo->name;

            if (!is_dir($folderPath)) {
                mkdir($folderPath, 0777, true);
            } else {
                if (file_exists($path)) {
                    unlink($path);
                }
            }
            if (!$archivo->saveAs($path)) {
                return false;
            }
        endforeach;

        return true;
    }
}
