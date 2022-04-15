<?php

return [
    'class' => 'yii\db\Connection',
    // DESARROLLO !!
    /* 'dsn' => 'mysql:host=otzi.cl;dbname=cot27290_SAMQA',
    'username' => 'cot27290_SAMQA',
    'password' => '?)ZkEI(O]nDl',
    'charset' => 'utf8', */

    //PRODUCCIÓN !!
    'dsn' => 'mysql:host=127.0.0.1;dbname=cot27290_SAM',
    'emulatePrepare' => true,
    'username' => 'cot27290_SAM',
    'password' => '?)ZkEI(O]nDl',
    'charset' => 'utf8',
    'enableParamLogging' => true,

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
